<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Validator;

class PasswordController extends Controller
{


    /**
     * @OA\Post(
     *     path="/auth/forgot_password",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     summary="Send password reset link",
     *     description="Sends a password reset link to the user's email.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to email."),
     *              @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed (email missing or not found)",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unable to send reset link",
     *         ref="#/components/responses/500"
     *     )
     * )
     */




    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'exists' => 'User account not found'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Validation failed', 422);
        }

        // // Generate and store the reset token
        $token = rand(1000000, 9999999);
        // return response()->json($token);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // // Send email (implement an email notification)
        // Mail::to($request->email)->send(new ResetPasswordMail($token));
        Mail::to($request->email)->send(new ResetPasswordMail($token));

        return ResponseHelper::success([], 'Password reset link sent to email.');
    }






    /**
     * @OA\Post(
     *     path="/auth/verify_code",
     *     operationId="verifyResetCode",
     *     tags={"Authentication"},
     *     summary="Verify password reset code",
     *     description="Checks if the 7-digit reset code is valid and not expired for the given email.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="code", type="string", example="1234567")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token is valid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token is valid"),
     *             @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token has expired."),
     *             @OA\Property(property="code", type="integer", example=400),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         ref="#/components/responses/422"
     *     )
     * )
     */

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'digits:7', 'numeric'],
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Validation failed', 422);
        }

        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->code, $reset->token)) {
            return ResponseHelper::error([], "invalid or expired token..", 400);
        }

        $expirationTime = Carbon::parse($reset->created_at)->addMinutes(2);
        if (Carbon::now()->greaterThan($expirationTime)) {
            return ResponseHelper::error([], "Token has expired.", 400);
        }

        return ResponseHelper::success([], 'Token is valid');
    }







    /**
     * @OA\Post(
     *     path="/auth/reset_password",
     *     operationId="resetPasswordWithCode",
     *     tags={"Authentication"},
     *     summary="Reset password with verified code",
     *     description="Resets the password after a successful code verification.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="newPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newPassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed (missing fields or mismatch)",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to reset password",
     *         ref="#/components/responses/500"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Validation failed', 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return ResponseHelper::success([], 'Password reset successfully');
    }





    /**
     * @OA\Post(
     *     path="/auth/update_password",
     *     operationId="updatePassword",
     *     tags={"Authentication"},
     *     summary="Update the logged-in user's password",
     *     description="Allows an authenticated user to update their password by providing the current password and new password.",
     *     security={{"bearerAuth": {}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldPass123"),
     *             @OA\Property(property="new_password", type="string", example="newPass456"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newPass456")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         ref="#/components/responses/401"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Current password incorrect",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Current password is incorrect."),
     *             @OA\Property(property="code", type="integer", example=403),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         ref="#/components/responses/422"
     *     )
     * )
     */

    public function update(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return ResponseHelper::error([], "User not authenticated.", 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return ResponseHelper::error([], "Current password is incorrect.", 403);
        }

        try {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return ResponseHelper::success([], "Password updated successfully.");
        } catch (\Exception $e) {
            return ResponseHelper::error(['error' => $e->getMessage()], "Failed to update password", 500);
        }
    }

}
