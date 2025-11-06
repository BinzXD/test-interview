<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Move;
use App\Http\Requests\ListRequest;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use App\Helpers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\MoveRequest;
use App\Models\MoveItem;
use App\Models\StockLocation;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MoveController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $perPage = $params['per_page'] ?? 10;


            $move = Move::query()->select(
                'move.id',
                'move.code',
                'move.created_at as date',
                'move.description',
                'users.name as user'
            )
                ->join('users', 'users.id', '=', 'move.user_id')
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('move.code', 'like', "%$search%")
                )
                ->orderBy('move.created_at', 'desc')
                ->paginate($perPage);

            return Api::send($move, 200);
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
        } catch (\Exception $e) {
            dd($e);
            $code = is_numeric($e->getCode()) ? (int) $e->getCode() : 500;

            return Api::send([
                'errors' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ]
            ], $code);
        }
    }

    public function show(string $id)
    {
        try {
            $move = DB::table('move as m')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->select(
                    'm.id',
                    'm.code',
                    'm.created_at as date',
                    'm.description',
                    'u.name as user'
                )
                ->where('m.id', $id)
                ->first();

            if (!$move) {
                throw new \Exception('Data Not Found', 404);
            }

            $items = DB::table('move_items as mi')
                ->join('products as p', 'p.id', '=', 'mi.product_id')
                ->join('category as c', 'c.id', '=', 'p.category_id')
                ->select(
                    'mi.id',
                    'p.id as product_id',
                    'p.name as product_name',
                    'p.code as product_code',
                    'mi.qty',
                    'p.unit',
                    'c.name as category'
                )
                ->where('mi.move_id', $id)
                ->get();

            $result = (array) $move;
            $result['items'] = $items;

            return Api::send($result, 200);
        } catch (\Exception $e) {
            $code = is_numeric($e->getCode()) ? (int) $e->getCode() : 500;

            return Api::send([
                'errors' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ]
            ], $code);
        }
    }

    public function store(MoveRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth('api')->user();
            $today = Carbon::now();
            $prefix = $today->format('Ymd');
            $params = $request->validated();
            if ($params['source_location_id'] == $params['destination_location_id']) {
                throw new \Exception('Location Cannot Same', 404);
            }
            $lastRecord = Move::whereRaw("DATE_FORMAT(created_at, '%m%Y') = ?", [$today->format('mY')])
                ->orderBy('created_at', 'desc')
                ->first();

            $nextIncrement = 1;
            if ($lastRecord && isset($lastRecord->code)) {
                $lastIncrement = (int) substr($lastRecord->code, -3);
                $nextIncrement = $lastIncrement + 1;
            }
            $newCode = sprintf("MOV-%s-%03d", $prefix, $nextIncrement);
            $moveData = collect($params)->except('items')->toArray();
            $moveData['code'] = $newCode;
            $moveData['user_id'] = $user->id;
            $data = Move::create($moveData);


            $moveItems = [];
            foreach ($params['items'] as $item) {
                $chekStok = StockLocation::where('product_id', $item['product_id'])->where('location_id', $params['source_location_id'])->first();
                if (!$chekStok) {
                    throw new \Exception('Stock Not Found', 404);
                }
                if ($chekStok->qty < $item['qty']) {
                    throw new \Exception('Stock Not Enough', 404);
                }
                $chekStok->qty = $chekStok->qty - $item['qty'];
                $chekStok->save();

                $chekStok = StockLocation::where('product_id', $item['product_id'])->where('location_id', $params['destination_location_id'])->first();
                if (!$chekStok) {
                    StockLocation::create([
                        'product_id' => $item['product_id'],
                        'location_id' => $params['destination_location_id'],
                        'qty' => $item['qty']
                    ]);
                } else {
                    $chekStok->qty = $chekStok->qty + $item['qty'];
                    $chekStok->save();
                }

                $moveItems[] = [
                    'id' => Str::uuid()->toString(),
                    'move_id' => $data->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            MoveItem::insert($moveItems);
            DB::commit();
            return Api::send($params, 200);
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $code = is_numeric($e->getCode()) ? (int) $e->getCode() : 500;

            return Api::send([
                'errors' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ]
            ], $code);
        }
    }
}
