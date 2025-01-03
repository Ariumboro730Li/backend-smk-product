<?php

namespace App\Http\Controllers\Internal;

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\Controller;
use App\Models\Signer;
use App\Models\WorkUnit;
use Illuminate\Http\Request;

class SignerController extends Controller
{

    public function index(Request $request)
    {

        $dataTable = $this->getDatatable($request);

        return response()->json([
            'error' => false,
            'message' => 'Successfully',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $dataTable,
        ], status: HttpStatusCodes::HTTP_OK);
    }

    public function getDatatable($request)
    {
        $workUnit = $request->auth_app_data->user->work_unit_id;
        $workUnitDetail = WorkUnit::find($workUnit);
        $query = Signer::with('workUnit')->where('is_active', 1)->select();

        if ($workUnitDetail->level != 'Level 1') {
            $query->where('work_unit_id', $workUnit);
        }

        if ($request->workunitonly) {
            $query->where('work_unit_id', $workUnit);
        }

        // Retrieve data without DataTables
        $signers = $query->get();

        // Convert the query result to an array
        $formattedData = $signers->map(function ($signer) {
            return [
                'id' => $signer->id,
                'name' => $signer->name,
                'work_unit' => $signer->workUnit->name,
                // Add other columns as needed
            ];
        });

        return $formattedData;
    }
}
