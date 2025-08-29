<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\InstructorApplication;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Str;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{



    /**
     * @OA\Get(
     *     path="/instructor/applications",
     *     tags={"Instructor"},
     *     summary="Get all instructor applications",
     *     description="Retrieve a list of all instructor applications submitted by users.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of instructor applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of applications"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="phone_number", type="string", example="+14155552671"),
     *                     @OA\Property(property="experience", type="string", example="3-5"),
     *                     @OA\Property(property="profession", type="string", example="Software Engineer"),
     *                     @OA\Property(property="interests", type="string", example="Web Development, AI, Cloud Computing"),
     *                     @OA\Property(property="additional_info", type="string", example="Experienced mentor in coding bootcamps."),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="admin_notes", type="string", example="Reviewed by admin, awaiting decision"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:34:56Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:34:56Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error retrieving applications"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */


    public function index()
    {
        $applications = InstructorApplication::all();
        return ResponseHelper::success($applications, 'List of applications');
    }



    /**
     * @OA\Get(
     *     path="/instructor/applications/{id}",
     *     tags={"Instructor"},
     *     summary="Get instructor application details",
     *     description="Retrieve details of a specific instructor application by its ID.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the instructor application",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Application details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Application details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="phone_number", type="string", example="+14155552671"),
     *                 @OA\Property(property="experience", type="string", example="3-5"),
     *                 @OA\Property(property="profession", type="string", example="Software Engineer"),
     *                 @OA\Property(property="interests", type="string", example="Web Development, AI, Cloud Computing"),
     *                 @OA\Property(property="additional_info", type="string", example="Experienced mentor in coding bootcamps."),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="admin_notes", type="string", example="Reviewed by admin, awaiting decision"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:34:56Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Application not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */

    public function show(int $id)
    {

        $application = InstructorApplication::find($id);
        if (!$application) {
            return ResponseHelper::error([], 'Application not found', 404);
        }
        return ResponseHelper::success(
            $application,
            'Application details',
        );
    }




    /**
     * @OA\Patch(
     *     path="/instructor/applications/{id}",
     *     tags={"Instructor"},
     *     summary="Approve or reject an instructor application (Admin only)",
     *     description="Allows an admin to approve or reject an instructor application. If approved, a new instructor user account is created automatically.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the instructor application",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"approved","rejected"}, example="approved"),
     *             @OA\Property(property="admin_notes", type="string", nullable=true, example="Strong background, approved as instructor.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Application updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Application has been approved successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="email", type="string", example="jane.doe@example.com"),
     *                 @OA\Property(property="phone_number", type="string", example="+14155552671"),
     *                 @OA\Property(property="experience", type="string", example="6-10"),
     *                 @OA\Property(property="profession", type="string", example="Data Scientist"),
     *                 @OA\Property(property="interests", type="string", example="Machine Learning, Data Engineering"),
     *                 @OA\Property(property="additional_info", type="string", example="Published author in AI research."),
     *                 @OA\Property(property="status", type="string", example="approved"),
     *                 @OA\Property(property="admin_notes", type="string", example="Strong background, approved as instructor."),
     *                 @OA\Property(property="user_id", type="integer", example=42),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:40:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="array", @OA\Items(type="string", example="Status can be approved or rejected"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Application not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Database connection lost"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */

    public function update(Request $request, int $id)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000'
        ], [
            'status.in' => 'Status can be approved or rejected'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        DB::beginTransaction();
        try {
            $application = InstructorApplication::find($id);

            if (!$application) {
                return ResponseHelper::error([], 'Application not found', 404);
            }

            $application->status = $request->status;
            $application->admin_notes = $request->admin_notes ?? null;
            $application->save();

            if ($request->status === 'approved') {
            
                $fullNameParts = explode(' ', $application->name, 2);
                $firstName = $fullNameParts[0] ?? '';
                $lastName = $fullNameParts[1] ?? '';

              
                $password = Str::random(10);

               
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $application->email,
                    'mobile' => $application->phone_number,
                    'provider' => 'email',
                    'password' => Hash::make($password),
                ]);

             
                // $user->assignRole('instructor'); 
                $roleModel = Role::where('name', 'instructor')->first();
                $user->roles()->attach($roleModel->id);

             
                $application->user_id = $user->id;
                $application->save();
            }

            DB::commit();

            return ResponseHelper::success(
                $application,
                "Application has been {$request->status} successfully.",
                200
            );

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], "Error : {$e->getMessage()}", 500);
        }
    }





    /**
     * @OA\Post(
     *     path="/instructor/apply",
     *     tags={"Instructor"},
     *     summary="Apply as an instructor",
     *     description="Allows a user to submit an application to become an instructor. The application will remain pending until reviewed by an admin.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fullname","email","current_profession","years_of_experience","courses_of_interest","phone_number"},
     *             @OA\Property(property="fullname", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="current_profession", type="string", example="Software Engineer"),
     *             @OA\Property(property="years_of_experience", type="string", enum={"1-2","3-5","6-10","10+"}, example="3-5"),
     *             @OA\Property(property="courses_of_interest", type="string", example="Web Development, AI, Cloud Computing"),
     *             @OA\Property(property="phone_number", type="string", example="+14155552671"),
     *             @OA\Property(property="additional_info", type="string", example="I have mentored junior devs and created internal training programs.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Application submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Application submitted successfully. Please wait for admin review."),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=15),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="profession", type="string", example="Software Engineer"),
     *                 @OA\Property(property="experience", type="string", example="3-5"),
     *                 @OA\Property(property="interests", type="string", example="Web Development, AI, Cloud Computing"),
     *                 @OA\Property(property="phone_number", type="string", example="+14155552671"),
     *                 @OA\Property(property="additional_info", type="string", example="I have mentored junior devs and created internal training programs."),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken.")),
     *                 @OA\Property(property="phone_number", type="array", @OA\Items(type="string", example="The phone number is not a valid international phone number."))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : SQLSTATE[23000]: Integrity constraint violation ..."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('instructor_applications', 'email')
                    ->where(function ($query) {
                        return $query->where('status', 'pending');
                    }),
            ],
            'current_profession' => 'required|string',
            'years_of_experience' => 'required|string|in:1-2,3-5,6-10,10+',
            'courses_of_interest' => 'required|string|max:500',
            'phone_number' => [
                'required',
                function ($attribute, $value, $fail) {
                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $numberProto = $phoneUtil->parse($value, null); // null = expects full intl format
                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail("The $attribute is not a valid international phone number.");
                        }
                    } catch (NumberParseException $e) {
                        $fail("The $attribute is not in valid format.");
                    }
                },
                Rule::unique('instructor_applications', 'phone_number')
                    ->where(fn($q) => $q->where('status', 'pending')),
            ],
            'additional_info' => 'nullable|string|max:1000'
        ]);


        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {
            $application = InstructorApplication::create([
                'name' => $request->fullname,
                'email' => $request->email,
                'experience' => $request->years_of_experience,
                'profession' => $request->current_profession,
                'interests' => $request->courses_of_interest,
                'phone_number' => $request->phone_number,
                'additional_info' => $request->additional_info,
                'status' => 'pending', 
            ]);

            return ResponseHelper::success(
                $application,
                'Application submitted successfully. Please wait for admin review.',
                201
            );

        } catch (Exception $e) {
            return ResponseHelper::error([], "Error : {$e->getMessage()}", 500);
        }
    }

}
