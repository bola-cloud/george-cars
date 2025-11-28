<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function brokerIp(Request $request)
    {
        $value = env('BROKER_IP');
        return response()->json(['broker_ip' => $value], 200);
    }
}
