<?php

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\FileController;
use App\Http\Controllers\OssController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UploadFileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::fallback(function () {
    return response()->json([
        'status_code'  => HttpStatusCodes::HTTP_NOT_FOUND,
        'error'   => true,
        'message' => 'URL Not Found'
    ], HttpStatusCodes::HTTP_NOT_FOUND);
});

Route::get('/healthz', function () {
    return 1;
});

Route::post('login/internal', [AuthController::class, 'login']);
Route::post('login/company', [AuthController::class, 'loginCompany']);
// Route::post('logout', [AuthController::class, 'logout']);
Route::post('register', [RegisterController::class, 'register']);

Route::group(['middleware' => 'check.token', 'auth.jwt'], function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('test', function () {
        return response()->json([
            'status_code'  => HttpStatusCodes::HTTP_OK,
            'error'   => false,
            'message' => 'Welcome to ESMK API'
        ], HttpStatusCodes::HTTP_OK);
    });

    Route::controller(FileController::class)->group(function () {
        Route::group(['prefix' => 'file'], function () {
            Route::post('/upload', 'uploadFile');
        });
    });
});

Route::controller(OssController::class)->group(function () {
    Route::group(['prefix' => 'oss'], function () {
        Route::get('/inquery-nib', 'inqueryNib');
    });
});

