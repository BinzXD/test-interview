<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
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

    // public function resetPassword(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/',
    //             'code' => 'required|string|min:6',
    //             'new_password' => 'required|string|min:8|confirmed',
    //         ], [
    //             'phone.required' => 'Nomor telepon wajib diisi.',
    //             'phone.string' => 'Nomor telepon harus berupa teks.',
    //             'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
    //             'phone.regex' => 'Nomor telepon harus dimulai dengan 62 dan memiliki 9-18 digit.',
    //             'code.required' => 'Kode wajib diisi.',
    //             'code.string' => 'Kode harus berupa teks.',
    //             'code.min' => 'Kode harus memiliki minimal 6 karakter.',
    //             'new_password.required' => 'Kata sandi baru wajib diisi.',
    //             'new_password.string' => 'Kata sandi baru harus berupa teks.',
    //             'new_password.min' => 'Kata sandi baru harus memiliki minimal 8 karakter.',
    //             'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
    //         ]);
    //         $validator->validate();

    //         $otpType = DB::table('otp_types')->where('name', 'forgotPassword')
    //             ->select('id')->first();

    //         $data = DB::table('log_verification_request')
    //             ->where(['phone' => $request->phone, 'otp_type_id' => $otpType->id])
    //             ->first();

    //         if (!$data) {
    //             throw new \Exception('Data tidak ditemukan', 404);
    //         }

    //         if ($data->code !== $request->code) {
    //             throw new \Exception('Kode OTP salah', 400);
    //         }

    //         $user = Customer::where('phone', $request->phone)->firstOrFail();
    //         $user->password = Hash::make($request->new_password);
    //         $user->save();

    //         DB::table('log_verification_request')
    //             ->where('id', $data->id)
    //             ->delete();

    //         DB::commit();

    //         return response()->api(null, 200);
    //     } catch (ValidationException $e) {
    //         $errors = new MessageBag($e->errors());
    //         return response()->api([
    //             'errors' => [
    //                 'code' => 422,
    //                 'message' => $errors->first(),
    //             ]
    //         ], 422);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         $code = $e->getCode();
    //         $statusCode = ($code >= 100 && $code < 600 && !$e instanceof QueryException) ? $code : 500;

    //         return response()->api(['errors' => $e], $statusCode);
    //     }
    // }

    // public function logout()
    // {
    //     try {
    //         auth('api')->logout();

    //         return response()->api(null, 200);
    //     } catch (\Exception $e) {
    //         $code = $e->getCode();
    //         $statusCode = ($code >= 100 && $code < 600 && !$e instanceof QueryException) ? $code : 500;

    //         return response()->api(['errors' => $e], $statusCode);
    //     }
    // }
}
