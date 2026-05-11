<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
public function status()
{
    return response()->json([
        'required_version' => config('app_status.required_version'),
        'latest_version' => config('app_status.latest_version'),
        'force_update' => config('app_status.force_update'),
        'message' => config('app_status.message'),
    ]);
}
}