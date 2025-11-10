<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * API Endpoint: Get authenticated user data by token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserByToken(Request $request)
    {
        Log::info('getUserByToken called.');

        // Extract token from Authorization header
        $header = $request->header('Authorization', '');
        Log::debug('Authorization header:', ['header' => $header]);

        $plainToken = preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)
            ? $matches[1]
            : trim($header);

        Log::debug('Extracted token:', ['token' => $plainToken]);

        if (empty($plainToken)) {
            Log::warning('Authorization token is missing.');
            return Response::json([
                'success' => false,
                'message' => 'Authorization token is missing',
                'data' => null
            ], 401);
        }

        // Find token in database
        $hashedToken = hash('sha256', $plainToken);
        Log::debug('Hashed token:', ['hashed_token' => $hashedToken]);

        $token = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->first();

        if (!$token) {
            Log::warning('Token not found or expired.', ['hashed_token' => $hashedToken]);
            return Response::json([
                'success' => false,
                'message' => 'Invalid or expired token',
                'data' => null
            ], 401);
        }

        Log::info('Token found.', ['tokenable_type' => $token->tokenable_type, 'tokenable_id' => $token->tokenable_id]);

        // Determine user type and table
        $userType = strtolower(class_basename($token->tokenable_type));
        $userId = (int) $token->tokenable_id;

        Log::debug('Determined user type and ID.', ['user_type' => $userType, 'user_id' => $userId]);

        $tableName = match ($userType) {
            'admin' => 'admin',
            'mentor' => 'mentors',
            'student' => 'students',
            default => null,
        };

        if (!$tableName) {
            Log::error('Unsupported user type.', ['user_type' => $userType]);
            return Response::json([
                'success' => false,
                'message' => 'Unsupported user type',
                'data' => null
            ], 403);
        }

        // Fetch user data
        $userData = DB::table($tableName)
            ->where('id', $userId)
            ->first();

        if (!$userData) {
            Log::error('User data not found.', ['table' => $tableName, 'user_id' => $userId]);
            return Response::json([
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        Log::info('User data retrieved successfully.', ['user_id' => $userId]);

        // Return successful response with user data
        return Response::json([
            'success' => true,
            'message' => 'User data retrieved successfully',
            'data' => [
                'user_type' => $userType,
                'user_id' => $userId,
                'user_data' => $userData
            ]
        ], 200);
    }
}
