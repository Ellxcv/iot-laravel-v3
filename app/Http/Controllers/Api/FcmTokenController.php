<?php

namespace App\Http\Controllers\Api;

use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController
{
    /**
     * Register or update FCM token for current user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'device_type' => 'nullable|in:desktop,mobile,tablet',
        ]);

        try {
            $tokenHash = hash('sha256', $validated['fcm_token']);
            
            // Check if token already exists for this user
            $existingToken = UserFcmToken::where('user_id', Auth::id())
                ->where('token_hash', $tokenHash)
                ->first();

            if ($existingToken) {
                // Update existing token
                $existingToken->update([
                    'device_name' => $validated['device_name'] ?? $existingToken->device_name,
                    'device_type' => $validated['device_type'] ?? $existingToken->device_type,
                    'user_agent' => $request->userAgent(),
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'FCM token updated successfully',
                    'token' => $existingToken,
                ]);
            }

            // Create new token
            $token = UserFcmToken::create([
                'user_id' => Auth::id(),
                'fcm_token' => $validated['fcm_token'],
                'token_hash' => $tokenHash,
                'device_name' => $validated['device_name'] ?? 'Unknown Device',
                'device_type' => $validated['device_type'] ?? 'desktop',
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully',
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all FCM tokens for current user
     */
    public function index(): JsonResponse
    {
        $tokens = UserFcmToken::where('user_id', Auth::id())
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
        ]);
    }

    /**
     * Delete a specific FCM token
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $token = UserFcmToken::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'FCM token deleted successfully',
        ]);
    }

    /**
     * Cleanup stale tokens (not used in 30 days)
     */
    public function cleanup(): JsonResponse
    {
        $deleted = UserFcmToken::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNull('last_used_at')
                    ->orWhere('last_used_at', '<', now()->subDays(30));
            })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deleted} stale token(s)",
            'deleted_count' => $deleted,
        ]);
    }
}
