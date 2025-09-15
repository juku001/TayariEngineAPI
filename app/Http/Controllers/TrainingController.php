<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Certificate;
use App\Models\Employer;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class TrainingController extends Controller
{


    /**
     * @OA\Get(
     *     path="/employee/progress",
     *     summary="Get learner progress overview for employer’s company",
     *     description="Returns progress statistics for all learners enrolled in courses under the authenticated employer's company teams.",
     *     tags={"Training"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Progress data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Learner progress overview"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Mike Johnson")
     *                     ),
     *                     @OA\Property(property="courses", type="string", example="2/3 courses", description="Completed / total courses"),
     *                     @OA\Property(property="points", type="integer", example=750, description="Total points earned"),
     *                     @OA\Property(property="certificates", type="integer", example=2, description="Certificates earned"),
     *                     @OA\Property(property="last_active", type="string", example="2 days ago", description="Last active time from lesson progress"),
     *                     @OA\Property(property="progress_percent", type="number", format="float", example=67.5, description="Average course progress percent")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     *     
     * )
     */

    public function progress()
    {
        $authId = auth()->id();
        $employer = Employer::where('user_id', $authId)->first();

        if (!$employer) {
            return ResponseHelper::error([], "Employer not found", 404);
        }

        $companyId = $employer->company_id;

        $enrollments = Enrollment::with(['user', 'team', 'course'])
            ->whereHas('team', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->get();

        $grouped = $enrollments->groupBy('user_id');

        $data = $grouped->map(function ($userEnrollments) {
            $user = $userEnrollments->first()->user;

            $totalCourses = $userEnrollments->count();
            $completedCourses = $userEnrollments->where('progress', 100)->count();

            $totalPoints = $user->points()->sum('points');


            $certificatesCount = Certificate::where('user_id', $user->id)->count();

            // Last Active → from lesson_progress
            $lastProgress = \DB::table('lesson_progress')
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->first();
            $lastActive = $lastProgress ? \Carbon\Carbon::parse($lastProgress->updated_at)->diffForHumans() : "N/A";

            // Avg progress %
            $avgProgress = round($userEnrollments->avg('progress'), 2);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'courses' => "{$completedCourses}/{$totalCourses} courses",
                'points' => $totalPoints,
                'certificates' => $certificatesCount,
                'last_active' => $lastActive,
                'progress_percent' => $avgProgress,
            ];
        })->values();

        return ResponseHelper::success($data, 'Learner progress overview');
    }


    /**
     * @OA\Get(
     *     path="/dashboard/training",
     *     summary="Get employer training dashboard statistics",
     *     description="Returns training performance metrics for the authenticated employer's company (enrollments, completed courses, earned certificates, and average completion time).",
     *     tags={"Training"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Employer dashboard data"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_enrollment", type="integer", example=120, description="Total number of enrollments in company teams"),
     *                 @OA\Property(property="course_completed", type="integer", example=85, description="Number of completed enrollments"),
     *                 @OA\Property(property="certificates_earned", type="integer", example=60, description="Number of certificates earned"),
     *                 @OA\Property(property="avg_completion_time", type="number", format="float", example=4.75, description="Average time to complete a course (in weeks/days depending on your system)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     * )
     */

    public function dashboard()
    {
        $authId = auth()->user()->id;
        $employer = Employer::where('user_id', $authId)->first();

        if (!$employer) {
            return ResponseHelper::error([], "Employer not found", 404);
        }

        $companyId = $employer->company_id;

        $totalEnrollment = Enrollment::whereHas('team', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count();

        $courseCompleted = Enrollment::whereHas('team', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('progress', 100)->count();

        $certificatesEarned = Certificate::whereHas('enrollment.team', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count();

        $avgCompletionTime = Enrollment::whereHas('team', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('progress', 100)
            ->avg('completion_time');

        $data = [
            'total_enrollment' => $totalEnrollment,
            'course_completed' => $courseCompleted,
            'certificates_earned' => $certificatesEarned,
            'avg_completion_time' => round($avgCompletionTime, 2),
        ];

        return ResponseHelper::success($data, 'Employer dashboard data');
    }



    /**
     * @OA\Get(
     *     path="/courses/performance",
     *     summary="Get course performance overview for employer's company",
     *     description="Returns aggregated course statistics (completion %, enrolled learners, completions, rating, average completion time) for all courses under the employer's company teams.",
     *     tags={"Training"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with course performance data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status",type="boolean",example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message",type="string",example="Course performance overview"),
     *             @OA\Property(
     *               property="data",
     *               type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="course", type="string", example="React Fundamentals"),
     *                 @OA\Property(property="completion_percent", type="number", example=75, description="Average completion percentage"),
     *                 @OA\Property(property="enrolled", type="integer", example=8, description="Total learners enrolled"),
     *                 @OA\Property(property="completed", type="integer", example=6, description="Learners who completed the course"),
     *                 @OA\Property(property="rating", type="string", example="4.5/5", description="Average rating of the course"),
     *                 @OA\Property(property="avg_time", type="string", example="4 weeks", description="Average time to complete the course")
     *             )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     * )
     */

    public function courses()
    {
        $authId = auth()->id();
        $employer = Employer::where('user_id', $authId)->first();

        if (!$employer) {
            return ResponseHelper::error([], "Employer not found", 404);
        }

        $companyId = $employer->company_id;

        // Get all enrollments for this company
        $enrollments = Enrollment::with(['course', 'progresses'])
            ->whereHas('team', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->get();

        // Group enrollments by course
        $grouped = $enrollments->groupBy('course_id');

        $data = $grouped->map(function ($courseEnrollments, $courseId) {
            $course = $courseEnrollments->first()->course;

            $totalEnrolled = $courseEnrollments->count();

            // Completed courses → enrollment progress 100
            $completed = $courseEnrollments->where('progress', 100)->count();

            // Avg completion %
            $avgCompletion = round($courseEnrollments->avg('progress'), 2);

            // Average time → difference between enrollment created_at and last completed lesson
            $completionTimes = $courseEnrollments->map(function ($enrollment) {
                $completedAt = $enrollment->progresses()
                    ->where('is_completed', true)
                    ->latest('completed_at')
                    ->value('completed_at');

                return $completedAt
                    ? \Carbon\Carbon::parse($enrollment->created_at)->diffInWeeks($completedAt)
                    : null;
            })->filter();

            $avgTime = $completionTimes->count() > 0 ? round($completionTimes->avg(), 1) . " weeks" : "N/A";

            // Course rating (assuming you have reviews table)
            $rating = \DB::table('course_reviews')
                ->where('course_id', $courseId)
                ->avg('rating');

            return [
                'course' => $course->title,
                'completion_percent' => $avgCompletion,
                'enrolled' => $totalEnrolled,
                'completed' => $completed,
                'rating' => $rating ? round($rating, 1) . "/5" : "N/A",
                'avg_time' => $avgTime,
            ];
        })->values();

        return ResponseHelper::success($data, 'Course performance overview');
    }



}
