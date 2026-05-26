<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|string',
            'category' => 'required|string',
            'body' => 'required|string|max:1000',
        ]);

        $feedback = Feedback::create($validated);

        return response()->json([
            'message' => 'feedback sent',
            'data' => $feedback
        ], 201);
    }
}
