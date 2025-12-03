<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Employer;
use App\Models\LearnerAptitudeResult;
use App\Models\User;
use App\Services\AdminLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Str;

class LogInController extends Controller
{

    protected $logService;



    public function __construct(AdminLogService $logService)
    {
        $this->logService = $logService;
    }



    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login User",
     *     description="Authenticates users and returns a JWT token. All users can log in using this API.",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="boolean", example=200),
     *             @OA\Property(property="message", type="string", example="User logged in successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="1|abc123tokenvalue"),
     *                 @OA\Property(property="aptitude_check", type="boolean", example=false),  
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="middle_name", type="string", example=null),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="mobile", type="string", example="255712345678"),
     *                     @OA\Property(property="email_verified_at", type="string", example="2025-08-30T04:20:21.000000Z"),
     *                     @OA\Property(property="profile_pic", type="string", example="profile/profilepic.png"),
     *                     @OA\Property(property="date_of_birth", type="string", example="2025-08-07 07:46:00"),
     *                     @OA\Property(property="provider", type="string", example="email"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="learner_points", type="integer", example=0),
     *                     @OA\Property(
     *                             property="roles",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="learner"),
     *                             )
     *                      )
     *                 ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials or validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="boolean", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid account credentials."),
     *            
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="boolean", example=404),
     *             @OA\Property(property="message", type="string", example="Account does not exist."),
     *            
     *         )
     *     ),
     *    @OA\Response(response=422, ref="#/components/responses/422"),
     * )
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                'Validation Error.',
                422
            );
        }
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return
                ResponseHelper::error([], 'Account does not exist.', 404);
        }

        if ($user->status !== 'active') {
            return ResponseHelper::error([], "Your account is " . ucfirst($user->status), 400);
        }


        if (!Hash::check($request->password, $user->password)) {
            return ResponseHelper::error(
                [],
                'Invalid account credentials.',
                401
            );
        }
        $success['token'] = $user->createToken('TayariToken')->plainTextToken;

        $action = $this->logService->getActionByCode(1);
        $userType = ucfirst($user->roles->pluck('name')->first());

        if ($userType == 'Learner') {
            $success['aptitude_check'] = LearnerAptitudeResult::where('user_id', $user->id)->exists();
        }

        $success['user'] = $user;
        if ($userType == 'employer') {
            $success['user']['company_id'] = Employer::where('user_id', $user->id)->first()->company_id;
        }
        $this->logService->record($user->id, $action, $userType . ' dashboard access');
        return ResponseHelper::success($success, 'User login successful.');
    }



    /**
     * @OA\Get(
     *     path="/auth/google",
     *     summary="Redirect to Google OAuth",
     *     description="Starts the Google OAuth flow by redirecting the user to Google's login/consent screen.",
     *     operationId="googleRedirect",
     *     tags={"Authentication"},
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirect response to Google login page",
     *         @OA\JsonContent(
     *             @OA\Property(property="redirect_url", type="string", example="https://accounts.google.com/o/oauth2/auth?..."),
     *         )
     *     )
     * )
     */
    public function redirect(Request $request)
    {

        return Socialite::driver('google')->stateless()->redirect();
    }






    /**
     * @OA\Get(
     *     path="/auth/google/callback",
     *     summary="Google OAuth callback",
     *     description="Handles the Google OAuth callback after successful Google authentication. 
     *         Creates or updates the user record, generates an access token, 
     *         and redirects the user to the frontend with token and aptitude info.",
     *     operationId="googleCallback",
     *     tags={"Authentication"},
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Authorization code returned by Google OAuth",
     *         required=true,
     *         @OA\Schema(type="string", example="4/0Ab32j93ksjQLfriVeleqyXTaKaTvWwPrDfNQ0ReJKcS9rJwq1h3DlIbS2RTio3ulFkHrYQ")
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to the frontend with token and aptitude status",
     *         @OA\Header(
     *             header="Location",
     *             description="Frontend URL where the user is redirected, including token and aptitude query params",
     *             @OA\Schema(
     *                 type="string",
     *                 example="https://tayari.work/login?token=223%7CCF5Uz2UtQRKW2tqPBOkSM6GEJ9LX8YFpniYdmG2Y371b3536&aptitude=false"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired authorization code"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error during Google authentication"
     *     )
     * )
     */

    public function callback(Request $request)
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()], // search by email
            [
                'first_name' => $googleUser->user['given_name'] ?? '',
                'last_name' => $googleUser->user['family_name'] ?? '',
                'profile_pic' => $googleUser->getAvatar(),
                'google_id' => $googleUser->getId(),
                'type' => 'learner',
                'password' => bcrypt(Str::random(16)),
                'email_verified_at' => Carbon::now()
            ]
        );

        if (!$user->google_id) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'profile_pic' => $googleUser->getAvatar(),
            ]);
        }

        $token = $user->createToken('TayariToken')->plainTextToken;


        $action = $this->logService->getActionByCode(1);
        $userType = ucfirst($user->roles->pluck('name')->first());
        $aptitudeCheck = null;
        $companyId = null;
        if ($userType == 'Learner') {
            $aptitudeCheck = LearnerAptitudeResult::where('user_id', $user->id)->exists();
        }
        $this->logService->record($user->id, $action, $userType . ' dashboard access');

        if ($userType == 'employer') {
            $companyId = Employer::where('user_id', $user->id)->first()->company_id;
        }

        $redirectUrl = 'https://tayari.work/login?token=' . urlencode($token) . '?aptitude=' . $aptitudeCheck . '?company=' . $companyId;
        return redirect($redirectUrl);
    }

}
