<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|string',
        'target_type' => 'required|string',
        'target_id' => 'required|string',
        'reason' => 'required|string',
        'body' => 'nullable|string',
    ]);

    $exists = Report::where('user_id', $validated['user_id'])
        ->where('target_type', $validated['target_type'])
        ->where('target_id', $validated['target_id'])
        ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'すでに通報済みです'
        ], 409);
    }

    $report = Report::create([
        'user_id' => $validated['user_id'],
        'target_type' => $validated['target_type'],
        'target_id' => $validated['target_id'],
        'reason' => $validated['reason'],
        'body' => $validated['body'] ?? null,
    ]);

    return response()->json([
        'message' => '通報を受け付けました',
        'data' => $report
    ], 201);
}
}