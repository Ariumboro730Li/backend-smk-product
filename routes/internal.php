<?php

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\OssController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterData\CityController;
use App\Http\Controllers\MasterData\DirJenController;
use App\Http\Controllers\Internal\PerusahaanController;
use App\Http\Controllers\MasterData\ProvinceController;
use App\Http\Controllers\MasterData\WorkUnitController;
use App\Http\Controllers\Internal\YearlyReportController;
use App\Http\Controllers\MasterData\SmkElementController;
use App\Http\Controllers\Internal\LaporanTahunanController;
use App\Http\Controllers\Internal\JadwalInterviewController;
use App\Http\Controllers\Internal\HistoryPengajuanController;
use App\Http\Controllers\Internal\PengesahanDokumenController;
use App\Http\Controllers\Internal\DisposisiPengajuanController;
use App\Http\Controllers\Internal\PenilaianPengajuanController;
use App\Http\Controllers\MasterData\MonitoringElementController;
use App\Http\Controllers\Internal\PengajuanSMKPerusahaanController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Internal\BeritaAcaraController;
use App\Http\Controllers\Internal\SignerController;
use App\Http\Controllers\MasterData\AssessorController;
use App\Http\Controllers\MasterData\MasterKbliController;
use App\Http\Controllers\MasterData\SkNumberController;

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

Route::group(['prefix' => 'admin-panel'], function () {
    // smk element
    Route::controller(SmkElementController::class)->group(function () {
        Route::group(['prefix' => 'smk-element'], function () {
            Route::get('/list', 'index');
            Route::get('/status', 'status');
            Route::get('/detail', 'detail');
            Route::post('/create', 'store');
            Route::post('/destroy', 'destroy');
            Route::get('/get-smk-element', 'smkElement');
        });
    });
    // monitoring element
    Route::controller(MonitoringElementController::class)->group(function () {
        Route::group(['prefix' => 'monitoring-element'], function () {
            Route::get('/list', 'index');
            Route::get('/detail', 'show');
            Route::post('/create', 'store');
            Route::post('/destroy', 'destroy');
            Route::get('/status', 'status');
        });
    });
    //province
    Route::controller(ProvinceController::class)->group(function () {
        Route::group(['prefix' => 'provinsi'], function () {
            Route::get('/list', 'index');
            Route::post('/store', 'store');
            Route::post('/update', 'update');
            Route::get('/edit', 'edit');
            Route::post('/destroy', 'destroy');
        });
    });
    //citiees
    Route::controller(CityController::class)->group(function () {
        Route::group(['prefix' => 'kota'], function () {
            Route::get('/list', 'index');
            Route::post('/store', 'store');
            Route::post('/update', 'update');
            Route::get('/edit', 'edit');
            Route::post('/destroy', 'destroy');
            Route::get('/select2', 'select2');
        });
    });
    // direktur jendral
    Route::controller(DirJenController::class)->group(function () {
        Route::group(['prefix' => 'direktur-jendral'], function () {
            Route::get('/list', 'index');
            Route::get('/edit', 'edit');
            Route::get('/detail', 'show');
            Route::post('/create', 'create');
            Route::get('/filterUser', 'filterUserDipilih');
            Route::get('/filterUserEdit', 'filterUser');
            Route::post('/store', 'store');
            Route::post('/update', 'update');
            Route::post('/destroy', 'destroy');
            Route::get('/listUser', 'listUser');
            Route::get('/inactive', 'disable');
            Route::get('/active', 'enable');
        });
    });
    // satuan kerja
    Route::controller(WorkUnitController::class)->group(function () {
        Route::group(['prefix' => 'satuan-kerja'], function () {
            Route::get('/list', 'index');
            Route::get('/inactive', 'disable');
            Route::get('/active', 'enable');
            Route::get('/province', 'province');
            Route::get('/service', 'service');
            Route::get('/city', 'city');
            Route::post('/store', 'store');
            Route::get('/edit', 'edit');
            Route::post('/update', 'update');
            Route::post('/destroy', 'destroy');
        });
    });
    Route::controller(SkNumberController::class)->group(function(){
        Route::group(['prefix' => 'sk-number'], function (){
            Route::get('/list', 'index');
            Route::post('/store', 'store');
            Route::post('/destroy', 'destroy');
            Route::post('/update', 'update');
            Route::get('/status', 'status');
        });
    });

    Route::controller(MasterKbliController::class)->group(function(){
        Route::group(['prefix' => 'master-kbli'], function (){
            Route::get('/list', 'index');
            Route::post('/store', 'store');
            Route::post('/destroy', 'destroy');
            Route::post('/update', 'update');
        });
    });
    //dashboard
    Route::controller(DashboardController::class)->group(function () {
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/listCompany', 'getListCompany');
            Route::get('/listCertificat', 'getCertifikatRequest');
            Route::get('/listServiceTypes', 'getServiceTypes');
            Route::get('/ListYearlyReport', 'getYearlyReport');
            Route::get('/ListYearlyReports', 'getYearly');
            Route::get('/ListAllCompany', 'getAllListCompany');
            Route::get('/dataDashboard', 'getDataDashboard');
            Route::get('/userDetail', 'getUserDetails');
            Route::get('/listAsesor', 'getListAssesor');
            Route::get('/listYearly', 'yearlyReport');
            Route::get('/data', 'data');
        });
    });

    Route::controller(PerusahaanController::class)->group(function () {
        Route::group(['prefix' => 'perusahaan'], function () {
            Route::get('/list', 'index');
            Route::get('/detail', 'show');
            Route::get('/province', 'province');
            Route::get('/service', 'service');
            Route::get('/pengajuan', 'getPengajuan');
            Route::get('/laporan', 'getLaporanTahunan');
        });
    });
    // laporan tahunan perusahaan
    Route::controller(YearlyReportController::class)->group(function () {
        Route::group(['prefix' => 'laporan-tahunan'], function () {
            Route::get('/list', 'index');
            Route::post('/store', 'store');
            Route::get('/detail', 'show');
            Route::get('/getView', 'getFileUrlToBase64');
            Route::post('/update', 'update');
        });
    });
    Route::get('/signer', [SignerController::class, 'index']);
    Route::get('/assessor-list', [AssessorController::class, 'index']);
    Route::post('/upload-file', [FileController::class, 'uploadFile']);
    Route::controller(PengajuanSMKPerusahaanController::class)->group(function () {
        Route::group(['prefix' => 'pengajuan-sertifikat'], function () {
            Route::get('/list', 'index');
            Route::get('/detail', 'detail');
            Route::post('/update', 'update');


            Route::get('/history', [HistoryPengajuanController::class, 'getRequestHistoryByRequestID']);
            Route::post('/store-assesment', [PenilaianPengajuanController::class, 'store']);
            Route::post('/record-of-verification', [BeritaAcaraController::class, 'create']);
            Route::get('/show-record-of-vertification', [BeritaAcaraController::class, 'showRecordOfVerification']);


            Route::controller(PengesahanDokumenController::class)->group(function () {
                Route::group(['prefix' => 'pengesahan-sertifikat'], function () {
                    Route::post('/certificate-release', 'createCertificateRelease');
                    Route::get('/generate-sk', 'getGenerateSK');
                    Route::get('/generate-official-report', 'print');
                });
            });

            Route::controller(JadwalInterviewController::class)->group(function () {
                Route::group(['prefix' => 'jadwal'], function () {
                    Route::post('/updateJadwal   ','update');
                    Route::get('/getJadwal', 'getJadwalInterview');
                    Route::post('/storeJadwal', 'storeAssessmentInterview');
                });
            });
        });
    });

    Route::controller(SettingController::class)->group(function () {
        Route::group(['prefix' => 'setting'], function () {
            Route::get('/list', 'list');
            Route::get('/find', 'get');
            Route::get('/detail', 'get');
            Route::post('/oss', 'oss');
            Route::post('/aplikasi', 'aplikasi');
        });
    });

    Route::controller(OssController::class)->group(function () {
        Route::get('/syncOss', 'syncOssInternal');
    });

    Route::controller(UserManagementController::class)->group(function () {
        Route::group(['prefix' => 'user-management'], function () {
            Route::get('/list', 'list');
            Route::get('/detail', 'detail');
            Route::post('/add', 'store');
            Route::get('/active', 'active');
            Route::get('/inactive', 'inactive');
            Route::post('/active', 'active');
            Route::post('/inactive', 'inactive');
            Route::post('/update', 'update');
            Route::post('/destroy', 'destroy');
        });
    });
});
