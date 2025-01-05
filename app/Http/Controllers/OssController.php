<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class OssController extends Controller
{
    public function inqueryNib(){
        $setting = Setting::where('name', 'oss')->first();
        dd($setting);
    }
}
