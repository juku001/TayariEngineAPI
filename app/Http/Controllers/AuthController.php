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







    /**
     * @OA\Get(
     *     path="/is_auth",
     *     tags={"Authentication"},
     *     summary="Check if the current user is authenticated",
     *     description="Validates the Sanctum token and confirms whether the user is authenticated or not.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User is authenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authenticated"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - invalid or missing token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     )
     * )
     */

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




    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Get logged-in user details",
     *     description="Returns the details of the currently authenticated user based on the Sanctum bearer token.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged in user details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged in user details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="middle_name", type="string", nullable=true, example=null),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe89@gmail.com"),
     *                 @OA\Property(property="mobile", type="string", nullable=true, example=null),
     *                 @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="profile_pic", type="string", nullable=true, example=null),
     *                 @OA\Property(property="date_of_birth", type="string", nullable=true, example=null),
     *                 @OA\Property(property="provider", type="string", example="email"),
     *                 @OA\Property(property="google_id", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_by", type="string", nullable=true, example=null),
     *                 @OA\Property(property="deleted_by", type="string", nullable=true, example=null),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="archive", type="string", nullable=true, example=null),
     *                 @OA\Property(property="deleted_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", example="2025-09-16T20:19:02.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-09-17T12:16:16.000000Z"),
     *                 @OA\Property(property="learner_points", type="integer", example=0),
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="learner"),
     *                         @OA\Property(property="description", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", example="2025-09-16T14:57:05.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2025-09-16T14:57:05.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - missing or invalid token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     )
     * )
     */

    public function me()
    {
        $authId = auth()->user()->id;

        $user = User::with('roles')->find($authId);

        return ResponseHelper::success($user, 'Logged in user details');
    }


    /**
     * @OA\Patch(
     *     path="/me",
     *     operationId="updateMe",
     *     tags={"Authentication"},
     *     summary="Update logged-in user profile",
     *     description="Allows an authenticated user to update their profile information such as name, email, mobile, date of birth, and profile picture.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="+255712345678"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1998-05-12"),
     *                 @OA\Property(
     *                     property="profile_pic",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile picture (jpg, jpeg, png)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="+255712345678"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date"),
     *                 @OA\Property(property="profile_pic", type="string", example="profile_pics/avatar.png"),
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="freelancer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The email has already been taken."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    public function updateMe(Request $request)
    {
        $authId = auth()->user()->id;

        $user = User::findOrFail($authId);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $authId,
            'mobile' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
            'profile_pic' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ✅ Handle profile picture upload
        if ($request->hasFile('profile_pic')) {
            $path = $request->file('profile_pic')->store('profile_pics', 'public');
            $validated['profile_pic'] = $path;
        }

        $user->update($validated);

        return ResponseHelper::success(
            $user->fresh()->load('roles'),
            'Profile updated successfully'
        );
    }

}
