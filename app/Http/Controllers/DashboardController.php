<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\Controller;
use App\Models\Assessor;
use App\Models\Auth\Role;
use App\Models\CertificateRequest;
use App\Models\Company;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkUnit;
use App\Models\WorkUnitHasService;
use App\Models\YearlyReport;
use App\Models\YearlyReportLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class DashboardController extends Controller
{
    protected $service;
    public function __construct()
    {
        App::setLocale('id');
    }

    public function getDataDashboard(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $request->merge([
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Successfully',
            'status_code' => 200,
            'data' => [
                'companies' => $this->company($request),
                'certificate_requests' => $this->certificateRequest($request),
                'service_types' => $this->serviceTypes($request),
            ]
        ]);
    }


    public function company(Request $request)
    {
        $workUnit = auth()->user()->work_unit_id;
        $coverageService = WorkUnitHasService::select()
            ->where('work_unit_id', $workUnit)
            ->get()
            ->pluck('service_type_id')
            ->toArray();
        $workUnitDetail = WorkUnit::find($workUnit);
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $queryCompany = Company::with('serviceTypes')
            ->whereHas('serviceTypes', function ($subQuery) use ($coverageService) {
                $subQuery->wherein('service_type_id', $coverageService);
            });
        $queryCompany->whereBetween('created_at', [$dateFrom, $dateTo]);
        // dd($queryCompany);

        if ($workUnitDetail->level === 'Level 2') {
            $queryCompany->where('province_id', $workUnitDetail->province_id);
        }

        if ($workUnitDetail->level === 'Level 3') {
            $queryCompany->where('city_id', $workUnitDetail->city_id);
        }

        return $queryCompany->get();
    }

    public function serviceTypes(Request $request)
    {
        $workUnit = auth()->user()->work_unit_id;
        $coverageService = WorkUnitHasService::select()
            ->where('work_unit_id', $workUnit)
            ->get()
            ->pluck('service_type_id')
            ->toArray();
        $workUnitDetail = WorkUnit::find($workUnit);
        $filterProvince = $workUnitDetail->province_id;
        $filterCity = $workUnitDetail->city_id;
        $dateFrom   = $request->dateFrom;
        $dateTo     = $request->dateTo;

        $query =  ServiceType::with([
            'companies' => function ($subQuery) use ($filterProvince, $filterCity, $dateFrom, $dateTo, $workUnitDetail) {
                if ($workUnitDetail->level === 'Level 2') {
                    $subQuery->where('province_id', $filterProvince);
                }
                if ($workUnitDetail->level === 'Level 3') {
                    $subQuery->where('city', $filterCity);
                }
                return $subQuery->whereBetween('companies.created_at', [$dateFrom, $dateTo]);
            }
        ])
            ->whereIn('service_types.id', $coverageService)
            ->select()
            ->orderBy('service_types.name', 'asc');
        return $query->get();
    }

    public function certificateRequest(Request $request)
    {
        $workUnit = auth()->user()->work_unit_id;
        $coverageService = WorkUnitHasService::select()
            ->where('work_unit_id', $workUnit)
            ->get()
            ->pluck('service_type_id')
            ->toArray();
        $workUnitDetail = WorkUnit::find($workUnit);

        $filterProvince = $workUnitDetail->province_id;
        $filterCity = $workUnitDetail->city_id;

        $dateFrom   = $request->dateFrom;
        $dateTo     = $request->dateTo;

        $queryCertificateRequest = CertificateRequest::with('company')
            ->whereHas('company.serviceTypes', function ($subQuery) use ($coverageService) {
                $subQuery->whereIn('service_type_id', $coverageService);
            })
            ->whereHas('company', function ($subQuery) use ($filterProvince, $filterCity, $workUnitDetail) {
                if ($workUnitDetail->level === 'Level 2') {
                    return $subQuery->where('province_id', $filterProvince);
                }

                if ($workUnitDetail->level === 'Level 3') {
                    return $subQuery->where('city', $filterCity);
                }
            })
            ->whereBetween('certificate_requests.created_at', [$dateFrom, $dateTo])
            ->where('certificate_requests.status', '!=', 'draft');
        return $queryCertificateRequest->get();
    }

    public function yearlyReport(Request $request)
    {
        $meta['orderBy'] = $request->ascending ? 'asc' : 'desc';
        $meta['limit'] = $request->limit <= 30 ? $request->limit : 30;
        $workUnit = auth()->user()->work_unit_id;
        $workUnitDetail = WorkUnit::find($workUnit);
        $coverageService = WorkUnitHasService::select()
            ->where('work_unit_id', $workUnit)
            ->get()
            ->pluck('service_type_id')
            ->toArray();

        $filterProvince = $workUnitDetail->province_id;
        $filterCity = $workUnitDetail->city_id;
        $currentDate = Carbon::now()->subMonths(1)->format('Y-m-d');

        // Query utama untuk yearlyReport
        $yearlyReport = YearlyReportLog::with('company')
            ->whereHas('company.serviceTypes', function ($subQuery) use ($coverageService) {
                $subQuery->whereIn('service_type_id', $coverageService);
            })
            ->whereHas('company', function ($subQuery) use ($filterProvince, $filterCity, $workUnitDetail) {
                if ($workUnitDetail->level === 'Level 2') {
                    $subQuery->where('province_id', $filterProvince);
                }

                if ($workUnitDetail->level === 'Level 3') {
                    $subQuery->where('city', $filterCity);
                }
            })
            ->whereDate('due_date', '<', $currentDate)
            ->where('is_completed', false);

        // Menambahkan pencarian
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower(trim($request->search));
            $yearlyReport->whereHas('company', function ($subQuery) use ($searchTerm) {
                $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        $data = $yearlyReport->orderBy('updated_at', $meta['orderBy'])->paginate($meta['limit']);
        return response()->json([
            'error' => false,
            'message' => 'Successfully',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
            ],
        ], HttpStatusCodes::HTTP_OK);
    }


    public function getListAssesor(Request $request)
    {
        // Menentukan sorting dan limit
        $meta['orderBy'] = $request->ascending ? 'asc' : 'desc';
        $meta['limit'] = $request->limit <= 30 ? $request->limit : 30;
        $wotkUnit = auth()->user()->work_unit_id;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $workUnitDetail = WorkUnit::find($wotkUnit);
        $queryUser = User::select(
            "name",
            "work_unit_id"
        )
            ->with("workUnit")
            ->withCount([
                'certificate_request_disposisition' => function ($query) use ($dateFrom, $dateTo) {
                    return $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'certificate_request_disposition_process' => function ($query) use ($dateFrom, $dateTo) {
                    return $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'certificate_request_completed' => function ($query) use ($dateFrom, $dateTo) {
                    return $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
            ])
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Assessor');
            });

        if ($workUnitDetail->level !== 'Level 1') {
            $queryUser = $queryUser->where('work_unit_id',);
        }
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower(trim($request->search));
            $queryUser->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
        }
        if ($request->has('dateFrom') && $request->has('dateTo')) {
            $dateFrom = $request->input('dateFrom');
            $dateTo = $request->input('dateTo');
            $queryUser->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
        }
        $data = $queryUser->orderBy('name', $meta['orderBy'])->paginate($meta['limit']);
        return response()->json([
            'error' => false,
            'message' => 'Successfully',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
            ],
        ], HttpStatusCodes::HTTP_OK);
    }

    public function getUserDetails(Request $request): JsonResponse
    {
        $authAppData = auth();
        $user = User::where('id', $authAppData->user()->id)->first();
        // $userRole = Role::where('id', $user->is_ministry)->first();
        $roles = $user->getRoleNames();
        // dd($roles);auth_app_data


        return response()->json([
            'error' => false,
            'message' => 'User details retrieved successfully',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $roles
                ],
            ],
        ], HttpStatusCodes::HTTP_OK);
    }
}
