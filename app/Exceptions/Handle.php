<?php

namespace App\Exceptions;

use Throwable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ModelNotFoundException::class,
        MethodNotAllowedHttpException::class,
        NotFoundHttpException::class,
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'status'    => false,
                'error'     => true,
                'message'   => 'Mohon maaf, sistem kami tidak dapat menemukan data ini',
                'data'      => null
            ], 404);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'message' => 'Metode permintaan data tidak diizinkan',
                'status' => false,
                'success' => false,
                'data' => null
            ], 405);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => false,
                'success' => false,
                'data' => null
            ], $e->getStatusCode());
        });

        $this->renderable(function (ServerException $e, $request) {
            return response()->json([
                'status'    => false,
                'error'     => true,
                'message'   => 'Terjadi kesalahan pada sistem, mohon coba kembali',
                'data'      => null
            ], 500);
        });

        $this->renderable(function (ClientException $e, $request) {
            return response()->json([
                'status'    => false,
                'error'     => true,
                'message'   => 'Terjadi kesalahan pada sistem, mohon coba kembali',
                'data'      => null
            ], 500);
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => null
            ], $this->isHttpException($exception) ? $exception->getCode() : 500);
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'status'    => false,
                'success'   => false,
                'message'   => 'Mohon maaf, sistem kami tidak dapat menemukan data ini',
                'data'      => null
            ], 404);
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'status'    => false,
                'error'     => true,
                'message'   => 'Mohon maaf, sistem kami tidak dapat menemukan data ini',
                'data'      => null
            ], 404);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json([
                'message' => 'Metode permintaan data tidak diizinkan',
                'status' => false,
                'success' => false,
                'data' => null
            ], 405);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => false,
                'success' => false,
                'data' => null
            ], $exception->getStatusCode());
        }

        if ($exception instanceof \GuzzleHttp\Exception\ClientException) {
            return response()->json([
                'status'    => false,
                'error'     => true,
                'message'   => 'Terjadi kesalahan pada sistem, mohon coba kembali',
                'data'      => null
            ], 500);
        }

        if ($exception instanceof \GuzzleHttp\Exception\ServerException) {
            return response()->json([
                'status'    => false,
                'error'     => true,
                'message'   => 'Terjadi kesalahan pada sistem, mohon coba kembali',
                'data'      => null
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
