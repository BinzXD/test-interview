<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\AllRequest;
use App\Http\Requests\ListRequest;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use App\Helpers\Api;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\UpdateproductRequest;
use App\Helpers\UploadHelper;

class ProductController extends Controller
{
    public function index(ListRequest $request)
    {
        try {
            $params = $request->validated();
            $search = isset($params['q']) ? trim($params['q']) : null;
            $perPage = $params['per_page'] ?? 10;


            $product = Product::query()->select(
                'products.id',
                'products.code',
                'products.name',
                'products.image',
                'products.price',
                'category.name as category'
            )
                ->join('category', 'category.id', '=', 'products.category_id')
                ->when(
                    !is_null($search),
                    fn($q) => $q->where('products.name', 'like', "%$search%")
                        ->orWhere('products.code', 'like', "%$search%")
                )
                ->orderBy('products.created_at', 'desc')
                ->paginate($perPage);

            return Api::send($product, 200);
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

            $product = product::select([
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

            return Api::send($product, 200);
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
            $product = Product::find($id);

            if (!$product) {
                throw new \Exception('Product Not Found', 404);
            }

            return Api::send($product, 200);
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

    public function store(ProductRequest $request)
    {
        try {
            $params = $request->validated();
            $check = Product::where('code', $params['code'])->first();
            if ($check) {
                return Api::send([
                    'errors' => [
                        'code' => 422,
                        'message' => 'Code already exists',
                    ]
                ], 422);
            }
            $existingproduct = Product::withTrashed()->where('code', $params['code'])->first();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = 'assets/product';

                $uploadedPath = UploadHelper::uploadFile($file, $path, 'public');
                $params['image'] = $uploadedPath;
            }

            if ($existingproduct) {
                if ($existingproduct->trashed()) {
                    $existingproduct->restore();
                }

                $existingproduct->fill($params);

                $existingproduct->save();

                return Api::send($existingproduct, 200);
            } else {
                $data = product::create($params);
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

    public function update(UpdateproductRequest $request, string $id)
    {
        try {
            $params = $request->validated();
            $product = product::find($id);
            if (!$product) {
                throw new \Exception('product Not Found', 404);
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = 'assets/product';

                $uploadedPath = UploadHelper::uploadFile($file, $path, 'public');
                $params['image'] = $uploadedPath;
            }

            $product->update($params);

            return Api::send($product, 200);
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

    public function destroy(string $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                throw new \Exception('product Not Found', 404);
            }

            $product->delete();

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
