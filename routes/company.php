<?php

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\OssController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\SertifikatSMKController;
use App\Http\Controllers\Company\LaporanTahunanController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\PengajuanSertifikatController;
use App\Http\Controllers\Company\HistoryPengajuanController;
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
    Route::group(['prefix' => 'submission'], function () {
        Route::controller(PengajuanSertifikatController::class)->group(function () {
            Route::get('/detail', 'detail');
            Route::get('/index', 'index');
            Route::post('/update', 'update');
            Route::post('/store', 'store');
            Route::get('/active-submmision', 'getCertifiateActive');
        });

        Route::get('/history', [HistoryPengajuanController::class, 'getRequestHistoryByRequestID']);
    });

    // get certificate
    Route::get('/certificate', [SertifikatSMKController::class, 'getSmkCertificate']);
    // get smk element
    Route::get('/smk-element', [SertifikatSMKController::class, 'getSmkElement']);
    // upload file
    Route::post('/upload-file', [FileController::class, 'uploadFile']);
});

Route::get('dashboard/company/getuser', [DashboardController::class, 'getUserDetails']);
Route::get('dashboard/company/perusahaan', [DashboardController::class, 'perusahaan']);
Route::get('dashboard/company/getsmk', [DashboardController::class, 'getsmk']);
Route::get('dashboard/certificate', [SertifikatSMKController::class, 'getSmkCertificate']);

Route::get('laporan-tahunan/monitoring-element', [LaporanTahunanController::class, 'index']);
Route::get('laporan-tahunan/detail', [LaporanTahunanController::class, 'show']);
Route::post('laporan-tahunan/store', [LaporanTahunanController::class, 'store']);
Route::post('laporan-tahunan/update', [LaporanTahunanController::class, 'update']);
Route::get('laporan-tahunan/get-monitoring-element', [LaporanTahunanController::class, 'getMonitoringElements']);
Route::get('laporan-tahunan/latest', [LaporanTahunanController::class, 'getLatestReport']);
Route::post('laporan-tahunan/upload-file', [LaporanTahunanController::class, 'uploadFile']);
Route::get('laporan-tahunan/getView', [LaporanTahunanController::class, 'getFileUrlToBase64']);

Route::get('/syncOss', [OssController::class, 'syncOss']);
