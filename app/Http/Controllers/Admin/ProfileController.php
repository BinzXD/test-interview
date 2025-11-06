<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\UploadHelper;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Helpers\Api;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function me()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                throw new \Exception('Akun tidak ditemukan', 404);
            }

            $customer = User::query()->select(
                'users.id',
                'users.name',
                'users.phone',
                'users.email',
                'users.avatar',
                'users.gender',
                'users.dob',
                'users.username'
            )
                ->where('users.id', $user->id)
                ->first();

            if (!$customer) {
                throw new \Exception('Akun tidak ditemukan', 404);
            }

            return Api::send($customer, 200);
        } catch (\Exception $e) {
            dd($e);
            $code = $e->getCode();
            $statusCode = ($code >= 100 && $code < 600 && !$e instanceof QueryException) ? $code : 500;
            return Api::send(['errors' => $e], $statusCode);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $data = $request->all();
            /** @var User $user */
            $user = auth('api')->user();
            if (!Hash::check($data['current_password'], $user->password)) {
                throw new \Exception('Current password not match', 400);
            }

            if (Hash::check($data['new_password'], $user->password)) {
                throw new \Exception('New password not match', 400);
            }

            $user->password = Hash::make($data['new_password']);
            $user->last_change_password = now();
            $user->save();

             return Api::send($user, 200);
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

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $params = $request->all();
            $user = auth('api')->user();

            /** @var User $user */
            $user = auth('api')->user();
             if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $path = 'assets/avatar';
                if ($user->avatar) {
                    $filePath = $user->avatar;
                    UploadHelper::deleteFile($filePath);
                }

               $user->avatar = UploadHelper::uploadFile($file, $path);
            }

            $user->update($params);

            return Api::send($user, 200);
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
}
