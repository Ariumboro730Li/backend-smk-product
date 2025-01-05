<?php

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\OssController;
use App\Http\Controllers\RegisterController;
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
Route::post('register', [RegisterController::class, 'register']);

Route::group(['middleware' => 'check.token', 'auth.jwt'], function () {
    Route::get('auth/me', [AuthController::class, 'me'])->middleware('auth.jwt');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth.jwt');
    Route::get('test', function () {
        return response()->json([
            'status_code'  => HttpStatusCodes::HTTP_OK,
            'error'   => false,
            'message' => 'Welcome to ESMK API'
        ], HttpStatusCodes::HTTP_OK);
    });
});

Route::controller(OssController::class)->group(function () {
    Route::group(['prefix' => 'oss'], function () {
        Route::get('/inquery-nib', 'inqueryNib');
    });
});

