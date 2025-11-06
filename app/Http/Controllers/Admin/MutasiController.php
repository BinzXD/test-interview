<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mutasi;
use App\Http\Requests\ListRequest;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use App\Helpers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\MutasiRequest;
use App\Models\MutasiItem;
use App\Models\StockLocation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\User;

class MutasiController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $perPage = $params['per_page'] ?? 10;


            $mutasi = Mutasi::query()->select(
                'mutasi.id',
                'mutasi.code',
                'mutasi.created_at as date',
                'mutasi.reason',
                'users.name as user'
            )
                ->join('users', 'users.id', '=', 'mutasi.user_id')
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('mutasi.code', 'like', "%$search%")
                )
                ->orderBy('mutasi.created_at', 'desc')
                ->paginate($perPage);

            return Api::send($mutasi, 200);
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
            $mutasi = DB::table('mutasi as m')
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

            if (!$mutasi) {
                throw new \Exception('Product Not Found', 404);
            }

            $items = DB::table('mutasi_items as mi')
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
                ->where('mi.mutasi_id', $id)
                ->get();

            $result = (array) $mutasi;
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

    public function store(MutasiRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth('api')->user();
            $today = Carbon::now();
            $prefix = $today->format('Ymd');
            $params = $request->validated();
            $lastRecord = Mutasi::whereRaw("DATE_FORMAT(created_at, '%m%Y') = ?", [$today->format('mY')])
                ->orderBy('created_at', 'desc')
                ->first();

            $nextIncrement = 1;
            if ($lastRecord && isset($lastRecord->code)) {
                $lastIncrement = (int) substr($lastRecord->code, -3);
                $nextIncrement = $lastIncrement + 1;
            }
            $newCode = sprintf("MUT-%s-%03d", $prefix, $nextIncrement);
            $mutasiData = collect($params)->except('items')->toArray();
            $mutasiData['code'] = $newCode;
            $mutasiData['user_id'] = $user->id;
            $data = Mutasi::create($mutasiData);

            $mutasiItems = [];
            foreach ($params['items'] as $item) {
                $chekStok = StockLocation::where('product_id', $item['product_id'])->where('location_id', $params['location_id'])->first();
                if (!$chekStok && $params['type'] == 'out') {
                    throw new \Exception('Location Dont Have Stock', 400);
                } else if ($chekStok && $params['type'] == 'out') {
                    $chekStok->update([
                        'qty' => $chekStok->qty - $item['qty']
                    ]);
                } else if ($chekStok && $params['type'] == 'in') {
                    $chekStok->update([
                        'qty' => $chekStok->qty + $item['qty']
                    ]);
                } else {
                    StockLocation::create([
                        'product_id' => $item['product_id'],
                        'location_id' => $params['location_id'],
                        'qty' => $item['qty']
                    ]);
                }

                $mutasiItems[] = [
                    'id' => Str::uuid()->toString(),
                    'mutasi_id' => $data->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            MutasiItem::insert($mutasiItems);
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

    public function historyProduct(string $id)
    {
        try {
            $product = Product::query()->select(
                'products.id',
                'products.name',
                'products.code',
                'products.unit',
                'c.name as category',
                'products.image',
            )->join('category as c', 'c.id', '=', 'products.category_id')->where('products.id', $id)->first();

            if (!$product) {
                throw new \Exception('Product Not Found', 404);
            }

            $mutasi = DB::table('mutasi_items as mi')
                ->join('mutasi as m', 'm.id', '=', 'mi.mutasi_id')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->select(
                    'mi.id',
                    'm.code',
                    'm.date',
                    'm.type',
                    'mi.qty',
                    'u.name as user',
                    'm.reason',
                )
                ->where('mi.product_id', $product->id)
                ->orderBy('m.created_at', 'desc')
                ->get();

            $result = $product;
            $result['items'] = $mutasi;

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

    public function historyUser(Request $request)
    {
        try {
            $user_id = $request->input('user_id', auth('api')->user()->id);

            $user = User::select(
                'users.id',
                'users.name',
                'users.phone',
                'users.email',
                'users.avatar',
                'users.gender',
                'users.dob',
                'users.username'
            )
                ->where('users.id', $user_id)
                ->first();

            if (!$user) {
                throw new \Exception('User Not Found', 404);
            }

            $mutasi = DB::table('mutasi_items as mi')
                ->join('mutasi as m', 'm.id', '=', 'mi.mutasi_id')
                ->join('products as p', 'p.id', '=', 'mi.product_id')
                ->join('category as c', 'c.id', '=', 'p.category_id')
                ->select(
                    'mi.id',
                    'm.code',
                    'm.date',
                    'm.type',
                    'p.name as product',
                    'c.name as category',
                    'p.code as product_code',
                    'p.unit as product_unit',
                    'mi.qty',
                    'm.reason',
                )
                ->where('m.user_id', $user->id)
                ->orderBy('m.created_at', 'desc')
                ->get();

            $result = $user;
            $result['items'] = $mutasi;

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
}
