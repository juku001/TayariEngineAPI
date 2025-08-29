<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\AptitudeQuestion;
use App\Models\Category;
use App\Models\Course;
use App\Models\JobPostType;
use App\Models\Level;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AptitudeOption;
use App\Models\LearnerAptitudeResult;
use Illuminate\Support\Facades\DB;

class AptitudeController extends Controller
{




    /**
     * @OA\Get(
     *     path="/aptitude/start",
     *     operationId="getAptitudeQuestions",
     *     tags={"Aptitude"},
     *     summary="Fetch all aptitude questions for learners",
     *     description="Requires authentication. Only users with the role 'learner' can access this endpoint. Returns a list of aptitude questions with their options.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             example={
     *                 "status": true,
     *                 "message": "Starting aptitude tests",
     *                 "code": 200,
     *                 "data": {
     *                     {
     *                         "id": 1,
     *                         "title": "What interests you most?",
     *                         "sub_title": "Select all areas you'd like to learn about",
     *                         "qn_type": "multiple",
     *                         "created_at": "2025-08-28T19:46:10.000000Z",
     *                         "updated_at": "2025-08-28T19:46:10.000000Z",
     *                         "options": {
     *                             {
     *                                 "id": 1,
     *                                 "question_id": 1,
     *                                 "title": "Technology & Programming",
     *                                 "key": "Tech",
     *                                 "sub_title": null,
     *                                 "icon": "fa fa-business",
     *                                 "color": "#ff0000",
     *                                 "created_at": "2025-08-28T19:46:10.000000Z",
     *                                 "updated_at": "2025-08-28T19:46:10.000000Z"
     *                             },
     *                             {
     *                                 "id": 2,
     *                                 "question_id": 1,
     *                                 "title": "Digital Marketing",
     *                                 "key": "Marketing",
     *                                 "sub_title": null,
     *                                 "icon": "fa fa-stocks",
     *                                 "color": "#ff2e20",
     *                                 "created_at": "2025-08-28T19:46:10.000000Z",
     *                                 "updated_at": "2025-08-28T19:46:10.000000Z"
     *                             }
     *                         }
     *                     },
     *                     {
     *                         "id": 2,
     *                         "title": "What's your current skill level?",
     *                         "sub_title": "Be honest - this helps us recommend the right courses",
     *                         "qn_type": "single",
     *                         "created_at": "2025-08-28T19:46:10.000000Z",
     *                         "updated_at": "2025-08-28T19:46:10.000000Z",
     *                         "options": {
     *                             {
     *                                 "id": 5,
     *                                 "question_id": 2,
     *                                 "title": "Beginner",
     *                                 "key": "beginner",
     *                                 "sub_title": "Just getting started",
     *                                 "icon": null,
     *                                 "color": null,
     *                                 "created_at": "2025-08-28T19:46:10.000000Z",
     *                                 "updated_at": "2025-08-28T19:46:10.000000Z"
     *                             }
     *                         }
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Only learners can access this resource",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only learners can access this"),
     *             @OA\Property(property="code", type="integer", example=403),
     *         )
     *     )
     * )
     */


    public function index()
    {
        $aptQuestions = AptitudeQuestion::with('options')->get();


        return ResponseHelper::success($aptQuestions, "Starting aptitude tests");

    }





    /**
     * @OA\Post(
     *     path="/aptitude/submit",
     *     operationId="storeAptitudeAnswers",
     *     tags={"Aptitude"},
     *     summary="Submit learner aptitude test answers",
     *     description="Stores learner aptitude test answers, evaluates skill level, career goals, and recommends courses. Requires authentication (role: learner).",
     *     security={{"bearerAuth": {}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"answers"},
     *             @OA\Property(
     *                 property="answers",
     *                 type="object",
     *                 required={"question_1","question_2","question_3"},
     *                 @OA\Property(
     *                     property="question_1",
     *                     type="array",
     *                     @OA\Items(type="string", example="tech")
     *                 ),
     *                 @OA\Property(
     *                     property="question_2",
     *                     type="string",
     *                     example="beginner"
     *                 ),
     *                 @OA\Property(
     *                     property="question_3",
     *                     type="array",
     *                     @OA\Items(type="string", example="full-time")
     *                 )
     *             ),
     *             example={
     *                 "answers": {
     *                     "question_1": {"tech", "design"},
     *                     "question_2": "beginner",
     *                     "question_3": {"full-time", "internship"}
     *                 }
     *             }
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Aptitude test results successfully stored",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Aptitude test results"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="interests",
     *                     type="array",
     *                     @OA\Items(type="string", example="tech")
     *                 ),
     *                 @OA\Property(property="skill_level", type="string", example="Beginner"),
     *                 @OA\Property(
     *                     property="career_goal",
     *                     type="array",
     *                     @OA\Items(type="string", example="Full-time Employment")
     *                 ),
     *                 @OA\Property(
     *                     property="courses",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Intro to Programming")
     *                     )
     *                 )
     *             ),
     *             example={
     *                 "status": true,
     *                 "message": "Aptitude test results",
     *                 "code": 200,
     *                 "data": {
     *                     "interests": {"tech","design"},
     *                     "skill_level": "Beginner",
     *                     "career_goal": {"Full-time Employment","Internship"},
     *                     "courses": {
     *                         {"id":1,"name":"Intro to Programming"},
     *                         {"id":2,"name":"UI/UX Basics"}
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation errors"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="errors", type="object")
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.question_1' => 'required|array|min:1',
            'answers.question_1.*' => 'required|string',
            'answers.question_2' => 'required|string',
            'answers.question_3' => 'required|array|min:1',
            'answers.question_3.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                'Validation errors',
                422
            );
        }

        try {
            $answers = $request->input('answers');

            $authId = auth()->user()->id;
            $skillLevel = null;
            $careerGoal = null;
            $recommendedCourses = [];

            $query = Course::query();
            if (!empty($answers['question_1'])) {
                $categories = Category::whereIn('slug', $answers['question_1'])->get();
                if ($categories->isNotEmpty()) {
                    $query->whereIn('category_id', $categories->pluck('id'));
                }
            }

            if (!empty($answers['question_2'])) {
                $level = Level::where('slug', $answers['question_2'])->first();

                if ($level) {
                    $skillLevel = $level->name;
                    $query->where('level_id', $level->id);
                }
            }
            if (!empty($answers['question_3'])) {
                $goals = JobPostType::whereIn('slug', $answers['question_3'])->get();
                if ($goals->isNotEmpty()) {
                    $careerGoal = $goals->pluck('name')->toArray() ?? [];
                }
            }

            $recommendedCourses = $query->select('id', 'name')->get();
            $dt = [
                'user_id' => $authId,
                'skill_level' => $skillLevel,
                'interests' => json_encode($categories->pluck('name')->toArray() ?? []),
                'career_goals' => json_encode($careerGoal),
                'answers' => json_encode($answers),
                'recommended_courses' => $recommendedCourses
            ];
            // return $dt;
            LearnerAptitudeResult::create($dt);

            return ResponseHelper::success([
                'interests' => $categories->pluck('slug')->toArray() ?? [],
                'skill_level' => $skillLevel,
                'career_goal' => $careerGoal,
                'courses' => $recommendedCourses
            ], 'Aptitude test results');

        } catch (Exception $e) {
            return ResponseHelper::error(
                [],
                "Error: " . $e->getMessage(),
                500
            );
        }
    }


}
