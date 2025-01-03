<?php

use App\Constants\HttpStatusCodes;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\SertifikatSMKController;
use App\Http\Controllers\Company\LaporanTahunanController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\PengajuanSertifikatController;
use App\Http\Controllers\FileController;

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

Route::group(['prefix' => 'documents'], function () {

    // submission certificate SMK
    Route::controller(PengajuanSertifikatController::class)->group(function () {
        Route::group(['prefix' => 'submission'], function () {
            Route::get('/detail', 'detail');
            Route::get('/index', 'index');
            Route::put('/update', 'update');
            Route::post('/store', 'store');
            Route::get('/active-submmision', 'getCertifiateActive');
        });
    });

    // get certificate
    Route::get('/certificate', [SertifikatSMKController::class, 'getSmkCertificate']);
    // get smk element
    Route::get('/smk-element', [SertifikatSMKController::class, 'getSmkElement']);
    // upload file
    Route::post('/upload-file', [FileController::class, 'uploadFile']);
});

Route::group(['prefix' => 'dashboard'], function () {

    // submission certificate SMK
    Route::controller(DashboardController::class)->group(function () {
        Route::group(['prefix' => 'company'], function () {
            Route::get('/getuser', 'getUserDetails');
            Route::get('/perusahaan', 'perusahaan');
            Route::get('/getsmk', 'getsmk');
            Route::post('/syncOss', 'syncOss');
        });
    });
    // get certificate
    Route::get('/certificate', [SertifikatSMKController::class, 'getSmkCertificate']);
});

Route::controller(LaporanTahunanController::class)->group(function () {
    Route::group(['prefix' => 'laporan-tahunan'], function () {
        Route::get('/monitoring-element', 'index');
        Route::get('/detail', 'show');
        Route::post('/store', 'store');
        Route::put('/update', 'update');
        Route::get('/get-monitoring-element', 'getMonitoringElements');
        Route::get('/latest', 'getLatestReport');
        Route::post('/upload-file', 'uploadFile');
        Route::get('/getView', 'getFileUrlToBase64');
    });
});
