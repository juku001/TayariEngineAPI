<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;

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


      
        LessonProgress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['completed' => true]
        );

     
        $courseId = $lesson->module->course_id;
        $totalLessons = Lesson::whereHas('module', fn($q) => $q->where('course_id', $courseId))->count();
        $completedLessons = LessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', Lesson::whereHas('module', fn($q) => $q->where('course_id', $courseId))->pluck('id'))
            ->where('completed', true)
            ->count();

        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

   
        Enrollment::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['progress' => $progress]
        );

        return ResponseHelper::success(['progress' => $progress], 'Lesson completed');
    }
}
