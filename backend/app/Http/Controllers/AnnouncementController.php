<?php

namespace App\Http\Controllers;

use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $query = Announcement::query();

if ($from = request('from')) {

    $query->where(function ($q) use ($from) {

        $q->whereDate('created_at', '>=', $from)
          ->orWhere('important', true);
    });
}

$announcements =
    $query
    ->latest()
    ->get();

        return response()->json($announcements);
    }

    // public function show($id)　後々indexでbody取得をやめるかも。その時のために残しておく。
    // {
    //     $announcement = Announcement::findOrFail($id);

    //     return response()->json($announcement);
    // }
}