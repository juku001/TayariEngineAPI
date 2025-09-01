<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Response;

class RegisterController extends Controller
{

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     description="Registers a new user with role-based validations. Can register as learner, instructor, employer, or admin. Employer can self-register with company details, or be added by an authenticated employer. Only super_admin can create admins.",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","password","password_confirmation","role"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
     *             @OA\Property(property="role", type="string", enum={"learner","employer","admin","instructor"}, example="employer"),
     *
     *             @OA\Property(property="admin_type", type="string", enum={"accountant","support","coordinator"}, example="support"),
     *
     *             @OA\Property(property="company_name", type="string", example="Tech Corp Ltd"),
     *             @OA\Property(property="company_website", type="string", example="https://techcorp.com"),
     *             @OA\Property(property="size_range", type="string", enum={"1-10","11-50","51-200","201+"}, example="11-50"),
     *             @OA\Property(property="company_role", type="string", enum={"owner", "hr","staff", "procurement","recruiter"}, example="hr")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *               property="data", 
     *               type="object",
     *               @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1
     *               ),
     *               @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 example="John"
     *               ),
     *               @OA\Property(
     *                 property="last_name",
     *                 type="string",
     *                 example="Doe"
     *               ),
     *               @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="example@email.com"
     *               ),
     *               @OA\Property(
     *                 property="provider",
     *                 type="string",
     *                 example="email"
     *               ),
     *               @OA\Property(
     *                 property="created_by",
     *                 type="integer",
     *                 example=null
     *               )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to create admin",
     *         @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(
     *             property="message", 
     *             type="string", 
     *             example="Unauthorized to create admin",
     *             description="Only super admin can create admin"
     *           ),
     *           @OA\Property(property="code", type="integer", example=403),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         ref="#/components/responses/500"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|string|in:learner,employer,admin,instructor'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                "Failed to validate fields",
                422
            );
        }

        $role = $request->role;
        $token = $request->bearerToken();
        $authUser = null;
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $authUser = $accessToken->tokenable;
            }
        }
       


        switch ($role) {
            case 'admin':
                if (!$authUser || $authUser->roles->pluck('name')->contains('super_admin') === false) {
                    return ResponseHelper::error([], "Unauthorized to create admin", 403);
                } else {
                    $validator = Validator::make($request->all(), [
                        'admin_type' => 'required|string|in:accountant,support,coordinator'
                    ]);
                    if ($validator->fails()) {
                        return ResponseHelper::error(
                            $validator->errors(),
                            "Identify your admin type",
                            422
                        );
                    }
                }
                break;
            case 'employer':
                if (!$authUser) {
                    $validator = Validator::make($request->all(), [
                        'company_name' => 'required|string',
                        'company_website' => 'required|string',
                        'size_range' => 'required|string|in:1-10,11-50,50-200,201+',
                        'company_role' => 'required|string|in:owner,hr,staff,procurement,recruiter'
                    ], [
                        'size_range.in' => 'Range should be 1-10,11-50,51-200 or 201+',
                        'company_role.in' => 'Roles allowed are owner, hr , staff, recruiter and procurement'
                    ]);

                    if ($validator->fails()) {
                        return ResponseHelper::error(
                            $validator->errors(),
                            "Company details required for employer registration",
                            422
                        );
                    }
                } else {
                    $companyId = $authUser->employer->company_id;
                    if (!$companyId) {
                        return ResponseHelper::error([], "Authenticated employer has no company assigned", 400);
                    }

                    $validator = Validator::make($request->all(), [
                        'company_role' => 'required|string|in:owner,hr,staff,procurement,recruiter'
                    ], [
                        'company_role.in' => 'Roles allowed are owner, hr , staff, recruiter and procurement'
                    ]);

                    if ($validator->fails()) {
                        return ResponseHelper::error(
                            $validator->errors(),
                            "Company Role is required",
                            422
                        );
                    }
                }
                break;

            case 'learner':
            case 'instructor':
                break;
            default:
                return ResponseHelper::error([], "Invalid role", 422);
        }




        try {
            DB::beginTransaction();



            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'provider' => 'email',
                'created_by' => $authUser != null ? $authUser->id : null
            ];

            $companyId = null;
            $user = User::create($userData);

            if ($role === 'employer') {
                $companyRole = null;
                if (!$authUser) {

                    $company = Company::create([
                        'name' => $request->company_name,
                        'website' => $request->company_website,
                        'size_range' => $request->size_range
                    ]);
                    $companyId = $company->id;
                    $companyRole = 'owner';
                } else {


                    if (!$authUser->employer->company_id) {
                        return ResponseHelper::error([], "Authenticated employer has no company assigned", 400);
                    }

                    $role = 'employee';
                    $companyRole = $request->company_role;
                    $companyId = $authUser->employer->company_id;
                }


                Employer::create([
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'role' => $companyRole
                ]);
            }


            if ($role === 'admin') {
                Admin::create([
                    'user_id' => $user->id,
                    'role' => $request->admin_type
                ]);
            }
            $roleModel = Role::where('name', $role)->first();
            $user->roles()->attach($roleModel->id);
            $user->sendEmailVerificationNotification();
            DB::commit();

            return ResponseHelper::success($user, "User registered successfully");

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], "Error: " . $e->getMessage(), 500);
        }

    }

}