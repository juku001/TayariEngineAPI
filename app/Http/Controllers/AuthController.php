<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use App\Services\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{


    protected $logService;



    public function __construct(AdminLogService $logService)
    {
        $this->logService = $logService;
    }



    public function unauthorized()
    {
        return ResponseHelper::error(
            [],
            'Unauthorized',
            401
        );
    }

    public function authorized()
    {
        return ResponseHelper::success(
            [],
            'Authenticated',
            200
        );
    }


    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout user",
     *     description="Revokes the currently authenticated user's access token and logs them out.",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successful."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No valid token provided",
     *         ref="#/components/responses/401"
     *     )
     * )
     */
    public function destroy(Request $request)
    {
        $authId = $request->user()->id;
        $action = $this->logService->getActionByCode(2);
        $this->logService->record($authId, $action, "Session ended");


        $request->user()->currentAccessToken()->delete();

        return ResponseHelper::success(
            [],
            "Logged out successful."
        );
    }








    /**
     * @OA\Post(
     *   path="/is_verified",
     *   tags={"Authentication"},
     *   summary="Check if a user's email is verified",
     *   operationId="isVerified",
     *   description="Validates the provided email and returns whether the user has a non-null email_verified_at.",
     *   requestBody={
     *     "required": true,
     *     "content": {
     *       "application/json": {
     *         "schema": {
     *           "type": "object",
     *           "required": {"email"},
     *           "properties": {
     *             "email": {
     *               "type": "string",
     *               "format": "email",
     *               "example": "user@example.com",
     *               "description": "User's email address"
     *             }
     *           }
     *         }
     *       }
     *     }
     *   },
     *     @OA\Response(
     *         response=200,
     *         description="User verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User verified."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User can't be found."),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not verified."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             
     *         )
     *     )
     * )
     */

    public function verified(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email'
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                'Failed to validate',
                422
            );
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ResponseHelper::error(
                [],
                "User can't be found",
                404
            );
        }

        if ($user->email_verified_at == null) {
            return ResponseHelper::error(
                [],
                'User not verified',
                400
            );
        }
        return ResponseHelper::success([], 'User verified');
    }
}
