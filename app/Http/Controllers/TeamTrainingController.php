<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Employer;
use App\Models\EmployerTeamMember;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamTrainingController extends Controller
{


    /**
     * @OA\Get(
     *     path="/employer/training/overview",
     *     summary="Get company members summary",
     *     description="Returns total members, active learners, and average learner points for the employer's company.",
     *     operationId="getMembersSummary",
     *     tags={"Training"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Members summary retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="active", type="integer", example=14),
     *                 @OA\Property(property="points", type="number", format="float", example=132.75)
     *             ),
     *             @OA\Property(property="message", type="string", example="Company Training Overview")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer has no company",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer has no company")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer has no company', 404);
        }

        $companyId = $employer->company_id;

        // Get all member user IDs for the company
        $memberIds = EmployerTeamMember::where('company_id', $companyId)
            ->pluck('user_id');

        // Total members
        $total = $memberIds->count();

        // Active members (at least one active enrollment)
        $active = Enrollment::where('company_id', $companyId)
            ->whereIn('user_id', $memberIds)
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');

        // Average learner points
        $avgPoints = User::whereIn('id', $memberIds)
            ->avg('learner_points');

        $data = [
            'total' => $total,
            'active' => $active,
            'points' => round($avgPoints ?? 0, 2)
        ];

        return ResponseHelper::success($data, 'Company Training Overview');
    }



    /**
     * @OA\Post(
     *     path="/employer/training/assign",
     *     summary="Assign a course to company members",
     *     description="Assigns a course to one or more company members. Prevents duplicate assignments.",
     *     operationId="assignCourseToMembers",
     *     tags={"Training"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id","members"},
     *             @OA\Property(
     *                 property="course_id",
     *                 type="integer",
     *                 example=5,
     *                 description="ID of the course to assign"
     *             ),
     *             @OA\Property(
     *                 property="members",
     *                 type="array",
     *                 description="Array of user IDs to assign the course to",
     *                 @OA\Items(type="integer", example=12)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Course assigned successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Course assigned to members successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer has no company",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer has no company")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     **/
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
            'members' => 'required|array|min:1',
            'members.*' => 'integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], 'Failed to validate fields', 422);
        }

        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer has no company', 404);
        }

        $companyId = $employer->company_id;
        $members = $request->members;

        foreach ($members as $memberId) {

            // Prevent duplicate assignment
            $exists = Enrollment::where('company_id', $companyId)
                ->where('course_id', $request->course_id)
                ->where('user_id', $memberId)
                ->exists();

            if ($exists) {
                continue; // skip already assigned users
            }

            Enrollment::create([
                'company_id' => $companyId,
                'course_id' => $request->course_id,
                'user_id' => $memberId,
                'enrollment_type' => 'team',
                'assigned_by' => $authId
            ]);
        }

        return ResponseHelper::success([], 'Course assigned to members successfully');
    }


    /**
     * @OA\Get(
     *     path="/employer/training/members",
     *     summary="Get all members of the authenticated employer's company",
     *     description="Returns a list of team members with their basic info, learner points, and completed courses count.",
     *     operationId="getCompanyMembers",
     *     tags={"Training"},
     *     security={{"bearerAuth": {}}}, 
     *
     *     @OA\Response(
     *         response=200,
     *         description="Company members retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Company members retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="points", type="integer", example=150),
     *                     @OA\Property(property="courses", type="integer", example=3)
     *                 )
     *             ),
     *             
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer has no company",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer has no company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         ref="#/components/responses/401"
     *     )
     *     
     * )
     */
    public function members(Request $request)
    {
        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer has no company', 404);
        }
        $companyId = $employer->company_id;

        $employerTeams = EmployerTeamMember::with('user')
            ->where('company_id', $companyId)
            ->get();

        $members = $employerTeams->map(function ($teamMember) use ($companyId) {
            $user = $teamMember->user;

            // Get completed courses count
            $completedCourses = Enrollment::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('status', 'completed')
                ->pluck('course_id');

            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'points' => $user->learner_points,
                'courses' => $completedCourses->count()
            ];
        });

        return ResponseHelper::success($members, 'Company members retrieved successfully');
    }



    /**
     * @OA\Get(
     *     path="/employer/training/courses",
     *     summary="Get company courses with assigned members",
     *     description="Returns all courses assigned by the employer's company and the users enrolled in each course.",
     *     operationId="getCompanyCourses",
     *     tags={"Training"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Company courses retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="course_id", type="integer", example=3),
     *                     @OA\Property(property="name", type="string", example="Laravel Basics"),
     *                     @OA\Property(property="subtitle", type="string", example="Build modern PHP applications"),
     *                     @OA\Property(property="level", type="string", nullable=true, example="Beginner"),
     *                     @OA\Property(
     *                         property="users",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=12),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="email", type="string", example="john@example.com"),
     *                             @OA\Property(property="status", type="string", example="in_progress")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Company courses retrieved successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer has no company",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer has no company")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function courses()
    {
        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer has no company', 404);
        }

        $companyId = $employer->company_id;

        // Get all enrollments for this company with course & user
        $enrollments = Enrollment::with(['course', 'user'])
            ->where('company_id', $companyId)
            ->where('enrollment_type', 'team')
            ->get()
            ->groupBy('course_id');

        $data = $enrollments->map(function ($courseEnrollments) {
            $course = $courseEnrollments->first()->course;

            return [
                'course_id' => $course->id,
                'name' => $course->name,
                'subtitle' => $course->subtitle,
                'level' => optional($course->level)->name,
                'users' => $courseEnrollments->map(function ($enrollment) {
                    return [
                        'id' => $enrollment->user->id,
                        'name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                        'email' => $enrollment->user->email,
                        'status' => $enrollment->status
                    ];
                })->values()
            ];
        })->values();

        return ResponseHelper::success($data, 'Company courses retrieved successfully');
    }





    /**
     * @OA\Get(
     *     path="/employer/training/progress",
     *     summary="Get members course progress",
     *     description="Returns progress, status, and course details for each company member per assigned course.",
     *     operationId="getMembersProgress",
     *     tags={"Training"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Members progress retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Members progress retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="user_id", type="integer", example=12),
     *                     @OA\Property(property="user_name", type="string", example="John Doe"),
     *                     @OA\Property(property="course_id", type="integer", example=3),
     *                     @OA\Property(property="course", type="string", example="Laravel Basics"),
     *                     @OA\Property(property="progress", type="integer", example=75),
     *                     @OA\Property(property="status", type="string", example="in_progress")
     *                 )
     *             ),
     *             
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer has no company",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer has no company")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     * 
     **/
    public function progress(Request $request)
    {
        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer has no company', 404);
        }

        $companyId = $employer->company_id;

        $progress = Enrollment::with(['user', 'course'])
            ->where('company_id', $companyId)
            ->where('enrollment_type', 'team')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'user_id' => $enrollment->user->id,
                    'user_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                    'course_id' => $enrollment->course->id,
                    'course' => $enrollment->course->name,
                    'progress' => (int) $enrollment->progress, // percentage
                    'status' => $enrollment->status
                ];
            });

        return ResponseHelper::success($progress, 'Members progress retrieved successfully');
    }

}
