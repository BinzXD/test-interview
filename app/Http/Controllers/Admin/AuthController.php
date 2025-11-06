<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\Api;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

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

            return Api::send(['errors' => $e], $statusCode);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:150',
                'password' => 'required|string|min:8'
            ]);

            $validator->validate();

            $user = User::where('username', $request->username)
                ->orWhere('email', $request->username)
                ->first();

            if (!$user) {
                throw new \Exception('Account not found', 404);
            }

            // custom response
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ];

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new \Exception('Wrong password', 401);
            }

            $token = JWTAuth::claims($data)->fromUser($user);

            return Api::send($token, 200);
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
