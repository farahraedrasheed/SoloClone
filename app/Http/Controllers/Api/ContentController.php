<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Content::latest()->get()
        );
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:255'],
        ]);

        $contents = Content::where('title', 'like', "%{$validated['q']}%")
            ->orWhere('category', 'like', "%{$validated['q']}%")
            ->latest()
            ->get();

        return response()->json($contents);
    }

    public function show(Content $content): JsonResponse
    {
        return response()->json($content);
    }
}
