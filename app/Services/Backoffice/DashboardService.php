<?php

namespace App\Services\Backoffice;

use App\Models\Company;
use App\Models\CertificateRequest;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkUnit;
use App\Models\WorkUnitHasService;
use Illuminate\Support\Facades\Auth;
use App\Models\YearlyReportLog;
use DataTables;
use Carbon\Carbon;


class DashboardService
{
    public function __construct($workUnit, $dateFrom, $dateTo)
    {
        $this->dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
        $this->dateTo   = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
        $this->userWorkUnit = $workUnit;
        $this->workUnitDetail = WorkUnit::find($workUnit);
        $this->coverageService = WorkUnitHasService::select()
            ->where('work_unit_id', $workUnit)
            ->get()
            ->pluck('service_type_id')
            ->toArray();
    }

    public function company()
    {
        $coverageService = $this->coverageService;
        $queryCompany = Company::with('serviceTypes')
        ->whereHas('serviceTypes', function($subQuery) use ($coverageService) {
            $subQuery->wherein('service_type_id', $coverageService);
        });
        $queryCompany->whereBetween('created_at',[$this->dateFrom, $this->dateTo]);

        if ($this->workUnitDetail->level === 'Level 2') {
            $queryCompany->where('province_id', $this->workUnitDetail->province_id);
        }

        if ($this->workUnitDetail->level === 'Level 3') {
            $queryCompany->where('city_id', $this->workUnitDetail->city_id);
        }
        
        return $queryCompany->get();
    }

    public function certificateRequest()
    {
        
        $filterProvince = $this->workUnitDetail->province_id;
        $filterCity = $this->workUnitDetail->city_id;

        $queryCertificateRequest = CertificateRequest::with('company')
        ->whereHas('company.serviceTypes', function($subQuery) {
            $subQuery->wherein('service_type_id', $this->coverageService);
        })
        ->whereHas('company', function($subQuery) use ($filterProvince, $filterCity) {
            if ($this->workUnitDetail->level === 'Level 2') {
                return $subQuery->where('province_id', $filterProvince);
            }

            if ($this->workUnitDetail->level === 'Level 3') {
                return $subQuery->where('city', $filterCity);
            }
        })
        ->whereBetween('certificate_requests.created_at',[$this->dateFrom, $this->dateTo])
        ->where('certificate_requests.status', '!=', 'draft')
        ->get();

        return $queryCertificateRequest;
        
    }

    public function serviceType()
    {
        $filterProvince = $this->workUnitDetail->province_id;
        $filterCity = $this->workUnitDetail->city_id;
        $dateFrom   = $this->dateFrom;
        $dateTo     = $this->dateTo;

        $query =  ServiceType::with([
            'companies' => function($subQuery) use ($filterProvince, $filterCity, $dateFrom, $dateTo) {
                if ($this->workUnitDetail->level === 'Level 2') {
                    $subQuery->where('province_id', $filterProvince);
                }
                if ($this->workUnitDetail->level === 'Level 3') {
                    $subQuery->where('city', $filterCity);
                }
                return $subQuery->whereBetween('companies.created_at',[$dateFrom, $dateTo]);
            }
        ])
        ->whereIn('service_types.id', $this->coverageService)
        ->select()
        ->orderBy('service_types.name', 'asc')
        ->get();

        return $query;
    }

    public function userAssessor()
    {
        $dateFrom   = $this->dateFrom;
        $dateTo     = $this->dateTo;
        $queryUser = User::select(
                        "name",
                        "work_unit_id"
                    )
                    ->with("workUnit")
                    ->withCount([
                        'certificate_request_disposisition' => function($query) use($dateFrom, $dateTo) {
                            return $query->whereBetween('created_at',[$dateFrom, $dateTo]);
                        },
                        'certificate_request_disposition_process' => function($query) use($dateFrom, $dateTo) {
                            return $query->whereBetween('created_at',[$dateFrom, $dateTo]);
                        },
                        'certificate_request_completed' => function($query) use($dateFrom, $dateTo) {
                            return $query->whereBetween('created_at',[$dateFrom, $dateTo]);
                        }
                    ])
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'Assessor');
                    });

        if ($this->workUnitDetail->level !== 'Level 1') {
            $queryUser = $queryUser->where('work_unit_id', $this->userWorkUnit);
        }

        return DataTables::eloquent($queryUser)
            ->toJson();
    }

    public function yearlyReport()
    {
        $filterProvince = $this->workUnitDetail->province_id;
        $filterCity = $this->workUnitDetail->city_id;
        $currentDate = Carbon::now()->subMonths(1)->format('Y-m-d');

        $yearlyReport = YearlyReportLog::with('company')
        ->whereHas('company.serviceTypes', function($subQuery) {
            $subQuery->wherein('service_type_id', $this->coverageService);
        })
        ->whereHas('company', function($subQuery) use ($filterProvince, $filterCity) {
            if ($this->workUnitDetail->level === 'Level 2') {
                return $subQuery->where('province_id', $filterProvince);
            }

            if ($this->workUnitDetail->level === 'Level 3') {
                return $subQuery->where('city', $filterCity);
            }
        })
        ->whereDate('due_date', '<', $currentDate)
        ->where('is_completed', false)
        ->get();

        return $yearlyReport;
    }
}
