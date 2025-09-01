<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AdminLogService;
use Illuminate\Http\Request;

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
}
