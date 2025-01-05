<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getModel(Request $request) {

        $payload = JWTAuth::parseToken()->getPayload();
        if ($payload->get('role') != 'company') {
            return auth()->user()->id;
        } else {
            return auth('company')->user()->id;
        }
    }
}
