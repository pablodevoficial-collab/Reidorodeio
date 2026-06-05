<?php

namespace App\Http\Controllers;

use App\Models\Competitor;
use App\Services\CompetitorFollowerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompetitorFollowController extends Controller
{
    public function store(Request $request, Competitor $competitor, CompetitorFollowerService $service): JsonResponse
    {
        $service->follow($request->user(), $competitor);

        return response()->json([
            'success' => true,
            'following' => true,
            'followers_count' => $competitor->followers()->count(),
        ]);
    }

    public function destroy(Request $request, Competitor $competitor, CompetitorFollowerService $service): JsonResponse
    {
        $service->unfollow($request->user(), $competitor);

        return response()->json([
            'success' => true,
            'following' => false,
            'followers_count' => $competitor->followers()->count(),
        ]);
    }
}
