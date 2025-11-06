<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\AllRequest;
use App\Http\Requests\ListRequest;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use App\Helpers\Api;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $perPage = $params['per_page'] ?? 10;
        

            $category = Category::select([
                'id',
                'code',
                'name',
                'status'
            ])
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%")
                )
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return Api::send($category, 200);
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
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

            return Api::send([
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
            ], $statusCode);
        }
    }

    public function all(AllRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $limit = $params['limit'] ?? 10;

            $category = Category::select([
                'id',
                'code',
                'name',
            ])
            ->where('status', 'active')
            ->when(
                !is_null($search),
                fn($q) => $q->where('name', 'like', "%$search%")
            )
            ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();

            return Api::send($category, 200);
        } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

            return Api::send([
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
            ], $statusCode);
        }
    }

    public function show(string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                throw new \Exception('Category Not Found', 404);
            }

            return Api::send($category, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

            return Api::send([
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
            ], $statusCode);
        }
    }

    public function store(CategoryRequest $request)
    {
        try {
            $params = $request->validated();

            $existingCategory = Category::withTrashed()->where('code', $params['code'])->first();

            if ($existingCategory) {
                if ($existingCategory->trashed()) {
                    $existingCategory->restore();
                }

                $existingCategory->fill($params);

                $existingCategory->save();

                return Api::send($existingCategory, 200);
            } else {
                $data = Category::create($params);
                return Api::send($data, 200);
            }
        } catch (ValidationException $e) {
            dd($e);
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
        } catch (\Exception $e) {
            dd($e);
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

            return Api::send([
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
            ], $statusCode);
        }
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $params = $request->validated();
            $category = Category::find($id);
            if (!$category) {
                throw new \Exception('Category Not Found', 404);
            }

            $category->update($params);

            return Api::send($category, 200);
       } catch (ValidationException $e) {
            $errors = new MessageBag($e->errors());
            return Api::send([
                'errors' => [
                    'code' => 422,
                    'message' => $errors->first(),
                ]
            ], 422);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

            return Api::send([
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
            ], $statusCode);
        }
    }

    public function destroy(string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                throw new \Exception('Category Not Found', 404);
            }

            $category->delete();

            return Api::send(null, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

            return Api::send([
                'errors' => [
                    'code' => $statusCode,
                    'message' => $e->getMessage(),
                ]
            ], $statusCode);
        }
    }
}
