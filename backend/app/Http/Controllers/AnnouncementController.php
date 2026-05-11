<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    public function index()
    {
        Log::info('AnnouncementController@index called');
        $query = Announcement::query();

if ($from = request('from')) {

    $query->where(function ($q) use ($from) {

    $q->whereDate('created_at', '>=', $from)
      ->where(function ($q2) {
          $q2->whereNull('send_at')
             ->orWhere('send_at', '<=', now());
      })
      ->orWhere('important', true);
});
}

$announcements =
    $query
    ->latest()
    ->get();

    Log::info('AnnouncementController@index called');

        return response()->json($announcements);
    }

    // public function show($id)　後々indexでbody取得をやめるかも。その時のために残しておく。
    // {
    //     $announcement = Announcement::findOrFail($id);

    //     return response()->json($announcement);
    // }
}