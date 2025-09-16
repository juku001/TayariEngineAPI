<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\PointService;
use Illuminate\Http\Request;

class QuizController extends Controller
{




    /**
     * @OA\Post(
     *     path="/quiz/check/{quizId}",
     *     operationId="checkQuizAnswer",
     *     tags={"Quizzes"},
     *     summary="Check if the submitted answer is correct",
     *     description="Validates the provided answer against the correct option for a given quiz.",
     *
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         description="ID of the quiz",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"answer"},
     *             @OA\Property(property="answer", type="string", example="A")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Answer checked successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Answer checked"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="quiz_id", type="integer", example=1),
     *                 @OA\Property(property="selected_option", type="string", example="A"),
     *                 @OA\Property(property="is_correct", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (e.g., missing `answer`)"
     *     )
     * )
     */

    public function checkAnswer(Request $request, $quizId)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        $quiz = Quiz::find($quizId);

        if (!$quiz) {
            return ResponseHelper::error([], 'Quiz not found', 404);
        }

        $isCorrect = $request->answer === $quiz->correct_option;

        return ResponseHelper::success(
            [
                'quiz_id' => $quiz->id,
                'selected_option' => $request->answer,
                'is_correct' => $isCorrect,
            ],
            'Answer checked'
        );
    }




    /**
     * @OA\Post(
     *     path="/quiz/submit/{quizId}",
     *     tags={"Quizzes"},
     *     summary="Submit a quiz attempt",
     *     description="Records a learner’s attempt for a specific quiz. Learner must be enrolled in the course containing the quiz.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Quiz ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"answer"},
     *             @OA\Property(property="answer", type="string", example="B")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Quiz attempt recorded",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Quiz attempt recorded"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="quiz_id", type="integer", example=5),
     *                 @OA\Property(property="selected_option", type="string", example="B"),
     *                 @OA\Property(property="is_correct", type="boolean", example=true),
     *                 @OA\Property(property="score", type="integer", example=1)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="User not enrolled in course",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not enrolled in this course"),
     *             @OA\Property(property="code", type="integer", example=403),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Course was already dropped",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course was already dropped"),
     *             @OA\Property(property="code", type="integer", example=400),
     *             
     *         )
     *     ), 
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Quiz not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     )
     * )
     */


    public function storeAttempt(Request $request, $quizId)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        $userId = auth()->id();

        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            return ResponseHelper::error([], 'Quiz not found', 404);
        }


        $courseId = $quiz->module->course_id;
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return ResponseHelper::error([], 'You are not enrolled in this course', 403);
        }

        if ($enrollment->status == 'dropped') {
            return ResponseHelper::error([], 'This course was already dropped', 400);
        }

        $isCorrect = $request->answer === $quiz->correct_option;

        if ($isCorrect) {
            $pointService = new PointService(auth()->user());
            $pointService->quizCorrect();
        }


        $score = $isCorrect ? 1 : 0;

        $attempt = QuizAttempt::create([
            'enrollment_id' => $enrollment->id,
            'quiz_id' => $quiz->id,
            'selected_option' => $request->answer,
            'is_correct' => $isCorrect,
        ]);

        return ResponseHelper::success(
            [
                'quiz_id' => $quiz->id,
                'selected_option' => $request->answer,
                'is_correct' => $isCorrect,
                'score' => $score,
            ],
            'Quiz attempt recorded'
        );
    }
    //
}
