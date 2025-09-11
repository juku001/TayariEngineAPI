<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Certificate;
use App\Services\PointService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\DB;
use Response;

class LessonController extends Controller
{


    /**
     * @OA\Post(
     *     path="/complete/lesson/{id}",
     *     tags={"Courses"},
     *     summary="Mark a lesson as completed",
     *     description="Marks the specified lesson as completed for the authenticated learner and recalculates course progress.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lesson completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lesson completed"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="progress", type="integer", example=60, description="Course progress percentage")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lesson not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */




    public function completeLesson(Request $request, $lessonId)
    {
        $userId = auth()->id();
        $lesson = Lesson::find($lessonId);

        if (!$lesson) {
            return ResponseHelper::error([], 'Lesson not found', 404);
        }


        $progress = LessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->latest()
            ->first();

        if ($progress && $progress->is_completed) {
            return ResponseHelper::error([], "Lesson already completed", 400);
        }

        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $lesson->course_id)
            ->first();

        if (!$enrollment) {
            return ResponseHelper::error([], "Enrollment not found", 404);
        }

        DB::beginTransaction();
        try {

            LessonProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now(),
                ]
            );


            $totalLessons = Lesson::where('course_id', $lesson->course_id)->count();
            $completedLessons = LessonProgress::where('enrollment_id', $enrollment->id)
                ->where('is_completed', true)
                ->count();

            $progressPercent = 0;
            if ($totalLessons > 0) {
                $progressPercent = ($completedLessons / $totalLessons) * 100;
            }
            $enrollment->progress = $progressPercent;
            $enrollment->save();

            // --- Certificate Check ---
            $alreadyHasCertificate = Certificate::where('course_id', $lesson->course_id)
                ->where('user_id', $userId)
                ->exists();

            if ($progressPercent >= 100 && !$alreadyHasCertificate) {
                $certificate_code = "TAYARI-" . now()->timestamp . "-STAGE-" . $lesson->course_id;

                Certificate::create([
                    'course_id' => $lesson->course_id,
                    'user_id' => $userId,
                    'enrollment_id' => $enrollment->id,
                    'certificate_code' => $certificate_code,
                    'issued_at' => Carbon::now()
                ]);
            }

            DB::commit();

            $pointService = new PointService(auth()->user());
            $pointService->lessonCompleted();
            return ResponseHelper::success([
                'progress' => $progressPercent,
                'certificate' => $progressPercent >= 100 ? "Issued" : "Not issued",
            ], 'Lesson completed successfully');

        } catch (QueryException $qe) {
            DB::rollBack();
            return ResponseHelper::error([], 'DB Error : ' . $qe->getMessage(), 500);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], 'Error : ' . $e->getMessage(), 500);
        }
    }







    /**
     * @OA\Get(
     *     path="/learning/progress",
     *     summary="Get learner progress",
     *     description="Returns all active enrollments with progress percentage, completed lessons, total lessons, instructor details, and next lesson name.",
     *     operationId="getLearningProgress",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of active enrollments with progress details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enrollment progress"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="enrollment_id", type="integer", example=5),
     *                     @OA\Property(
     *                         property="course",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="title", type="string", example="Introduction to Laravel"),
     *                         @OA\Property(
     *                             property="instructor",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="name", type="string", example="Jane Doe")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="progress",
     *                         type="object",
     *                         @OA\Property(property="completed_lessons", type="integer", example=4),
     *                         @OA\Property(property="total_lessons", type="integer", example=10),
     *                         @OA\Property(property="percentage", type="number", format="float", example=40.0)
     *                     ),
     *                     @OA\Property(property="next_lesson", type="string", example="Eloquent Basics")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - missing or invalid token",
     *         ref="#/components/responses/401"
     *     )
     * )
     */

    public function progress()
    {
        $authId = auth()->id();

        $enrollments = Enrollment::where('user_id', $authId)
            ->where('status', 'active')
            ->with(['course.lessons', 'course.instructorUser']) // <-- singular
            ->get();

        $data = $enrollments->map(function ($enrollment) use ($authId) {
            $course = $enrollment->course; // <-- singular

            if (!$course) {
                return null; // or skip this enrollment
            }

            $lessons = $course->lessons;
            $totalLessons = $lessons->count();

            $completedLessonIds = LessonProgress::where('enrollment_id', $enrollment->id)
                ->where('user_id', $authId)
                ->pluck('lesson_id')
                ->toArray();

            $completedCount = count($completedLessonIds);

            // calculate progress
            $progressPercent = $totalLessons > 0
                ? round(($completedCount / $totalLessons) * 100, 2)
                : 0;

            // update enrollment progress in DB
            if ($enrollment->progress != $progressPercent) {
                $enrollment->progress = $progressPercent;
                $enrollment->save();
            }

            // next lesson
            $nextLesson = $lessons->whereNotIn('id', $completedLessonIds)->first();
            $nextLessonName = $nextLesson ? $nextLesson->title : null;

            return [
                'enrollment_id' => $enrollment->id,
                'course' => [
                    'id' => $course->id,
                    'title' => $course->name,
                    'instructor' => [
                        'id' => $course->instructorUser->id ?? null,
                        'name' => $course->instructorUser->name ?? null,
                    ],
                ],
                'progress' => [
                    'completed_lessons' => $completedCount,
                    'total_lessons' => $totalLessons,
                    'percentage' => $progressPercent,
                ],
                'next_lesson' => $nextLessonName,
            ];
        })->filter(); // filter out nulls


        return ResponseHelper::success($data, "Enrollment progress");
    }


}
