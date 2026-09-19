<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IncentiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncentiveApiController extends Controller
{
    /**
     * Get active incentives, milestones progress, and reward history for the driver.
     */
    public function getIncentives(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $data = IncentiveService::getDriverIncentivesPayload($user);
        return response()->json($data);
    }

    /**
     * Get driver incentive reward history.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $data = IncentiveService::getDriverIncentivesPayload($user);
        return response()->json([
            'success' => true,
            'reward_history' => $data['reward_history'] ?? [],
            'wallet_summary' => $data['wallet_summary'] ?? [],
        ]);
    }
}
