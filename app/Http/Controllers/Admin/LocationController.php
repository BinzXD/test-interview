<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Http\Requests\AllRequest;
use App\Http\Requests\ListRequest;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use App\Helpers\Api;
use App\Http\Requests\LocationRequest;
use App\Http\Requests\UpdateLocationRequest;

class LocationController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $perPage = $params['per_page'] ?? 10;


            $location = location::select([
                'id',
                'code',
                'name',
                'address',
            ])
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%")
                )
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return Api::send($location, 200);
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
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

    public function all(AllRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $limit = $params['limit'] ?? 10;

            $location = location::select([
                'id',
                'code',
                'name',
            ])
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('name', 'like', "%$search%")
                )
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();

            return Api::send($location, 200);
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
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

    public function show(string $id)
    {
        try {
            $location = location::find($id);

            if (!$location) {
                throw new \Exception('location Not Found', 404);
            }

            return Api::send($location, 200);
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

    public function store(LocationRequest $request)
    {
        try {
            $params = $request->validated();

            $existinglocation = Location::withTrashed()->where('code', $params['code'])->first();

            if ($existinglocation) {
                if ($existinglocation->trashed()) {
                    $existinglocation->restore();
                }

                $existinglocation->fill($params);

                $existinglocation->save();

                return Api::send($existinglocation, 200);
            } else {
                $data = Location::create($params);
                return Api::send($data, 200);
            }
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
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

    public function update(UpdatelocationRequest $request, string $id)
    {
        try {
            $params = $request->validated();
            $location = location::find($id);
            if (!$location) {
                throw new \Exception('location Not Found', 404);
            }

            $location->update($params);

            return Api::send($location, 200);
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
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

    public function destroy(string $id)
    {
        try {
            $location = location::find($id);

            if (!$location) {
                throw new \Exception('location Not Found', 404);
            }

            $location->delete();

            return Api::send(null, 200);
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
