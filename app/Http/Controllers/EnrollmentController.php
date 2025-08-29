<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{

    /**
     * @OA\Post(
     *     path="/courses/{id}/enroll",
     *     operationId="enrollCourse",
     *     tags={"Courses"},
     *     summary="Enroll the authenticated user into a course",
     *     description="Enrolls the logged-in learner into the specified course. Requires authentication.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course to enroll in",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully enrolled",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enrolled successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="course_id", type="integer", example=3),
     *                 @OA\Property(property="status", type="string", nullable=true, example=null),
     *                 @OA\Property(property="progress", type="integer", example=0),
     *                 @OA\Property(property="enrollment_type", type="string", nullable=true, example=null),
     *                 @OA\Property(property="team_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="assigned_by", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:30:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:30:00.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Already enrolled",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are already enrolled in this course"),
     *             @OA\Property(property="code", type="integer", example=409),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Something went wrong"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function store(Request $request, string $id)
    {
        $userId = auth()->id();

        // Check if course exists
        $course = Course::find($id);
        if (!$course) {
            return ResponseHelper::error([], 'Course not found', 404);
        }

        // Check if already enrolled
        $alreadyEnrolled = Enrollment::where('user_id', $userId)
            ->where('course_id', $id)
            ->exists();

        if ($alreadyEnrolled) {
            return ResponseHelper::error([], 'You are already enrolled in this course', 409);
        }

        // Enroll user
        $enrollment = Enrollment::create([
            'user_id' => $userId,
            'course_id' => $id,
            'progress' => 0, // start with 0%
        ]);

        return ResponseHelper::success($enrollment, 'Enrolled successfully');
    }



    /**
     * @OA\Get(
     *     path="/courses/{id}/progress",
     *     operationId="getCourseProgress",
     *     tags={"Courses"},
     *     summary="Get progress of the authenticated user in a course",
     *     description="Returns lessons and quizzes completed vs. total, along with overall progress percentage. Requires authentication.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Course progress retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course progress calculated successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="course_id", type="integer", example=3),
     *                 @OA\Property(property="course_name", type="string", example="Organic Chemistry"),
     *                 @OA\Property(property="progress", type="number", format="float", example=65.5),
     *                 @OA\Property(
     *                     property="lessons",
     *                     type="object",
     *                     @OA\Property(property="completed", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=15)
     *                 ),
     *                 @OA\Property(
     *                     property="quizzes",
     *                     type="object",
     *                     @OA\Property(property="passed", type="integer", example=2),
     *                     @OA\Property(property="total", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not enrolled in course",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Not enrolled in this course"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Something went wrong"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function progress($id)
    {
        $user = auth()->user();

       
        $enrollment = Enrollment::with(['course.modules.lessons', 'course.modules.quizzes'])
            ->where('user_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if (!$enrollment) {
            return ResponseHelper::error([], 'Not enrolled in this course', 404);
        }

        $course = $enrollment->course;

        
        $totalLessons = $course->modules->flatMap->lessons->count();

       
        $completedLessons = $user->lessonProgress()
            ->whereIn('lesson_id', $course->modules->flatMap->lessons->pluck('id'))
            ->count();

       
        $totalQuizzes = $course->modules->flatMap->quizzes->count();

      
        $passedQuizzes = $user->quizAttempts()
            ->whereIn('quiz_id', $course->modules->flatMap->quizzes->pluck('id'))
            ->where('score', '>=', 50)
            ->count();

       
        $totalItems = $totalLessons + $totalQuizzes;
        $completedItems = $completedLessons + $passedQuizzes;

        $progress = $totalItems > 0
            ? round(($completedItems / $totalItems) * 100, 2)
            : 0;

        return ResponseHelper::success([
            'course_id' => $course->id,
            'course_name' => $course->name,
            'progress' => $progress,
            'lessons' => [
                'completed' => $completedLessons,
                'total' => $totalLessons
            ],
            'quizzes' => [
                'passed' => $passedQuizzes,
                'total' => $totalQuizzes
            ]
        ], 'Course progress calculated successfully');
    }


}
