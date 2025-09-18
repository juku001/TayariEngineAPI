<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Mail\InstructorApplicationStatusMail;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\InstructorApplication;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Mail;
use Str;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{



    /**
     * @OA\Get(
     *     path="/instructor/applications",
     *     tags={"Instructor"},
     *     summary="Get all instructor applications",
     *     security = {{ "bearerAuth":{} }},
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
     *     security={{"bearerAuth":{} }},
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
     *             @OA\Property(property="status", type="string", enum={"approve","reject"}, example="approve"),
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
            'status' => 'required|in:approve,reject',
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
            $status = $request->status == "approve" ? "approved" : "rejected";

            if ($status == $application->status) {
                return ResponseHelper::success(
                    [],
                    "The application was already " . $status
                );
            }

            $application->status = $status;
            $application->admin_notes = $request->admin_notes ?? null;
            $application->save();

            if ($status === 'approved') {

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

                $roleModel = Role::where('name', 'instructor')->first();
                $user->roles()->attach($roleModel->id);




                $application->user_id = $user->id;
                $application->save();
            }
            Mail::to($application->email)
                ->send(new InstructorApplicationStatusMail($application, $status, $password));


            DB::commit();

            return ResponseHelper::success(
                $application,
                "Application has been {$request->status} successfully.",
                200
            );

        } catch (QueryException $e) {
            DB::rollBack();
            return ResponseHelper::error([], 'Database failed to save approval.', 500);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], 'An unexpected error occurred.' . $e->getMessage(), 500);
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
     *     
     *     @OA\Response(
     *       response=400,
     *       description="Bad Request",
     *       @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You already have a pending application. Please wait for the admin to process."),
     *             @OA\Property(property="code", type="integer", example=400),
     *       )
     *     ),
     *     
     *     
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         ref="#/components/responses/500"
     *     )
     * )
     */


    public function store(Request $request)
    {
        $pendingFields = [];

        if (InstructorApplication::where('email', $request->email)->where('status', 'pending')->exists()) {
            $pendingFields[] = 'email';
        }

        if (InstructorApplication::where('phone_number', $request->phone_number)->where('status', 'pending')->exists()) {
            $pendingFields[] = 'phone_number';
        }

        if (!empty($pendingFields)) {
            return ResponseHelper::error([], "You already have a pending application. Please wait for them to be processed.", 400);
        }

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email',
            'current_profession' => 'required|string',
            'years_of_experience' => 'required|string|in:1-2,3-5,6-10,10+',
            'courses_of_interest' => 'required|string|max:500',
            'phone_number' => [
                'required',
                function ($attribute, $value, $fail) {
                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $numberProto = $phoneUtil->parse($value, null); // expects full intl format
                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail("The $attribute is not a valid international phone number.");
                        }
                    } catch (NumberParseException $e) {
                        $fail("The $attribute is not in valid format.");
                    }
                },
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

            // Send raw email to admin
            Mail::raw(
                "A new instructor application has been submitted.\n\n" .
                "Name: {$application->name}\n" .
                "Email: {$application->email}\n" .
                "Phone: {$application->phone_number}\n" .
                "Profession: {$application->profession}\n" .
                "Experience: {$application->experience}\n" .
                "Interests: {$application->interests}\n" .
                "Additional Info: {$application->additional_info}",
                function ($message) {
                    $message->to('info@tayari.work')
                        ->subject('New Instructor Application');
                }
            );

            return ResponseHelper::success(
                $application,
                'Application submitted successfully. Please wait for admin review.',
                201
            );

        } catch (Exception $e) {
            return ResponseHelper::error([], "Error : {$e->getMessage()}", 500);
        }
    }





    /**
     * @OA\Post(
     *     path="/admin/courses/assign",
     *     tags={"Admin"},
     *     summary="Assign an instructor to a course",
     *     description="Assigns an instructor to a specific course by updating the course's instructor_id.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id","instructor_id"},
     *             @OA\Property(property="course_id", type="integer", example=1, description="ID of the course"),
     *             @OA\Property(property="instructor_id", type="integer", example=2, description="ID of the instructor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Instructor assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Instructor assigned successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Organic Chemistry"),
     *                 @OA\Property(property="instructor_id", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course or Instructor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course or Instructor not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Validation error",
     *         ref="#/components/responses/401"
     *     ),
     * )
     */
    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
            'instructor_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                "Failed to validate fields",
                422
            );
        }

        $course = Course::find($request->course_id);
        $instructor = User::find($request->instructor_id);

        if (!$course || !$instructor) {
            return ResponseHelper::error([], "Course or Instructor not found", 404);
        }

        // Assign the instructor
        $course->instructor = $instructor->id; // or 'user_id' if that's your column
        $course->save();

        return ResponseHelper::success($course, "Instructor assigned successfully");
    }


}
