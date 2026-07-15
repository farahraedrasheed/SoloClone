<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\UserAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class UserActionController extends Controller
{
    abstract protected function actionType(): string;

    public function index(Request $request): JsonResponse
    {
        $contents = $request->user()
            ->userActions()
            ->where('action_type', $this->actionType())
            ->with('content')
            ->get()
            ->pluck('content');

        return response()->json($contents);
    }

    public function store(Request $request, Content $content): JsonResponse
    {
        $action = UserAction::firstOrCreate([
            'user_id' => $request->user()->id,
            'content_id' => $content->id,
            'action_type' => $this->actionType(),
        ]);

        return response()->json($action->load('content'), 201);
    }

    public function destroy(Request $request, Content $content): JsonResponse
    {
        $deleted = UserAction::where('user_id', $request->user()->id)
            ->where('content_id', $content->id)
            ->where('action_type', $this->actionType())
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json(['message' => 'Removed successfully.']);
    }
}
