<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    AuthController,
    ProfileController,
    CategoryController,
    LocationController,
    ProductController,
    MutasiController,
    MoveController
};

Route::get('/', function () {
    return 'Good Luck';
});

Route::group(['namespace' => 'App\Http\Controllers'], function ($route) {
    $route->group(['prefix' => 'auth'], function ($route) {
        // Auth User
        $route->post('/login', [AuthController::class, 'login']);
        $route->post('/register', [AuthController::class, 'register']);
        $route->post('/reset-password', [AuthController::class, 'resetPassword']);
        $route->post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    });

    $route->group(['prefix' => 'admin', 'middleware' => 'auth:api'], function ($route) {
        // Profile
        $route->group(['prefix' => 'profile'], function ($route) {
            $route->get('/me', [ProfileController::class, 'me']);
            $route->post('/change-password', [ProfileController::class, 'changePassword']);
            $route->post('/change-profile', [ProfileController::class, 'updateProfile']);
        });

        // Category 
        $route->group(['prefix' => 'category'], function ($route) {
            $route->get('/all', [CategoryController::class, 'all']);
            $route->get('/', [CategoryController::class, 'index']);
            $route->post('/', [CategoryController::class, 'store']);
            $route->get('/{id}', [CategoryController::class, 'show']);
            $route->put('/{id}', [CategoryController::class, 'update']);
            $route->delete('/{id}', [CategoryController::class, 'destroy']);
        });

        // Location
        $route->group(['prefix' => 'location'], function ($route) {
            $route->get('/all', [LocationController::class, 'all']);
            $route->get('/', [LocationController::class, 'index']);
            $route->post('/', [LocationController::class, 'store']);
            $route->get('/{id}', [LocationController::class, 'show']);
            $route->put('/{id}', [LocationController::class, 'update']);
            $route->delete('/{id}', [LocationController::class, 'destroy']);
        });
        
        // Product
        $route->group(['prefix' => 'product'], function ($route) {
            $route->get('/all', [ProductController::class, 'all']);
            $route->get('/', [ProductController::class, 'index']);
            $route->post('/', [ProductController::class, 'store']);
            $route->get('/{id}', [ProductController::class, 'show']);
            $route->post('/{id}', [ProductController::class, 'update']);
            $route->delete('/{id}', [ProductController::class, 'destroy']);
        });

        // Mutasi
        $route->group(['prefix' => 'mutasi'], function ($route) {
            $route->get('/', [MutasiController::class, 'index']);
            $route->post('/', [MutasiController::class, 'store']);
            $route->get('/history-product/{id}', [MutasiController::class, 'historyProduct']);
            $route->get('/history-user', [MutasiController::class, 'historyUser']);
            $route->get('/{id}', [MutasiController::class, 'show']);
        });

        // Move
        $route->group(['prefix' => 'move'], function ($route) {
            $route->get('/', [MoveController::class, 'index']);
            $route->post('/', [MoveController::class, 'store']);
            $route->get('/{id}', [MoveController::class, 'show']);
        });
    });
});
