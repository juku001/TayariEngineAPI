<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Certificate;
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

}
