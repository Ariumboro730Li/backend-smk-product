<?php

namespace App\Http\Controllers\MasterData;

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class   AssessorController extends Controller
{
    public function index(Request $request)
    {
        $workUnit = $request->auth_app_data->user->work_unit_id;

        $data = User::select('id', 'name')
            ->where('work_unit_id', $workUnit);

        if ($request->role) {
            $data = $data->whereHas('roles', function ($query) use ($request) {
                $query->where('name', $request->role);
            });
        }

        if ($request->keyword) {
            $data = $data->where('name', 'like', '%' . $request->keyword . '%');
        }

        $result = $data->get();
        return response()->json([
            'status' => HttpStatusCodes::HTTP_OK,
            'data'   => $result->toArray(),
        ], HttpStatusCodes::HTTP_OK);
    }
}
