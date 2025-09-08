<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{


    /**
     * @OA\Post(
     *     path="/auth/verify-email",
     *     summary="Send verification email",
     *     description="Sends a verification email to the authenticated user.",
     *     operationId="sendEmailVerification",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email verification sent successful"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *       response=401, 
     *       ref="#/components/responses/401"
     *     ),
     *     
     * )
     */
    public function index(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return ResponseHelper::success(
            [],
            'Email verification sent successful'
        );
    }


    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return ResponseHelper::error(
                [],
                "Invalid verification link",
                404
            );
        }

        if ($user->hasVerifiedEmail()) {
            return ResponseHelper::error(
                [],
                "Email already verified.",
                400
            );
        }

        $user->markEmailAsVerified();
        event(new Verified($user));



        $userRole = $user->roles->pluck('name')->first();
        if ($userRole == 'employer') {
            return redirect()->away('https://tayari.work/dashboard/employer');
        } else {
            return redirect()->away('https://tayari.work/assessments/aptitude-test');
        }

    }
}
