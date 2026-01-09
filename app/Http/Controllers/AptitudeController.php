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
use Illuminate\Validation\ValidationException;

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
     *         ),
     *     ),
     *       @OA\Response(
     *         response=400,
     *         description="Denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User already has aptitude test."),
     *             @OA\Property(property="code", type="integer", example=400),
     *         )
     *     )
     * )
     */


    public function index()
    {

        $userAptitude = LearnerAptitudeResult::where('user_id', auth()->user()->id)->first();
        if ($userAptitude) {
            return ResponseHelper::error([], 'User already has aptitude test.', 400);
        }
        $aptQuestions = AptitudeQuestion::with('options')->get();

        return ResponseHelper::success($aptQuestions, "Starting aptitude test.");

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




    /**
     * @OA\Get(
     *     path="/admin/aptitudes",
     *     operationId="getAllAptitudes",
     *     tags={"Aptitude"},
     *     summary="For Admin to fetch all aptitude questions",
     *     description="Requires authentication. Only users with the role 'admin' can access this endpoint. Returns a list of aptitude questions with their options.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             example={
     *                 "status": true,
     *                 "message": "All aptitude questions.",
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
     *         description="Forbidden - Only admins can access this resource",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only admins can access this"),
     *             @OA\Property(property="code", type="integer", example=403),
     *         ),
     *     )
     * )
     */

    public function getAllAptitudes()
    {
        //     $aptQuestions = AptitudeQuestion::with('options')->orderBy('created_at', 'desc')->get();
        $daptQuestions = AptitudeQuestion::with('options')
            ->latest()
            ->get();

        return ResponseHelper::success($aptQuestions, "All aptitude questions.");
    }






    /**
     * @OA\Post(
     *     path="/admin/aptitudes",
     *     tags={"Aptitude"},
     *     summary="Create a new aptitude question with options",
     *     description="Creates a new aptitude question with its related options.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "qn_type", "options"},
     *             @OA\Property(property="title", type="string", example="What interests you most?"),
     *             @OA\Property(property="sub_title", type="string", nullable=true, example="Select all areas you'd like to learn about"),
     *             @OA\Property(property="qn_type", type="string", enum={"single","multiple"}, example="single"),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"title"},
     *                     @OA\Property(property="title", type="string", example="Technology & Programming"),
     *                     @OA\Property(property="key", type="string", nullable=true, example="Tech"),
     *                     @OA\Property(property="sub_title", type="string", nullable=true, example=null),
     *                     @OA\Property(property="icon", type="string", nullable=true, example=null),
     *                     @OA\Property(property="color", type="string", nullable=true, example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Aptitude question created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Aptitude question created successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="title", type="string", example="What interests you most?"),
     *                 @OA\Property(property="sub_title", type="string", nullable=true, example="Select all areas you'd like to learn about"),
     *                 @OA\Property(property="qn_type", type="string", example="single"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=11),
     *                         @OA\Property(property="question_id", type="integer", example=5),
     *                     @OA\Property(property="title", type="string", example="Technology & Programming"),
     *                     @OA\Property(property="key", type="string", nullable=true, example="Tech"),
     *                         @OA\Property(property="sub_title", type="string", nullable=true, example=null),
     *                         @OA\Property(property="icon", type="string", nullable=true, example=null),
     *                         @OA\Property(property="color", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"title": {"The title field is required."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             
     *         )
     *     )
     * )
     */

    public function addNewAptitude(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'sub_title' => 'nullable|string|max:255',
                'qn_type' => 'required|in:single,multiple',
                'options' => 'required|array|min:1',
                'options.*.title' => 'required|string|max:255',
                'options.*.key' => 'nullable|string|max:10',
                'options.*.sub_title' => 'nullable|string|max:255',
                'options.*.icon' => 'nullable|string|max:255',
                'options.*.color' => 'nullable|string|max:50',
            ], [
                'qn_type.in' => 'Should be single or multiple'
            ]);

            DB::beginTransaction();

            $question = AptitudeQuestion::create([
                'title' => $validated['title'],
                'sub_title' => $validated['sub_title'] ?? null,
                'qn_type' => $validated['qn_type'],
            ]);

            $question->options()->createMany($validated['options']);

            DB::commit();

            return ResponseHelper::success(
                $question->load('options'),
                'Aptitude question created successfully.'
            );
        } catch (ValidationException $e) {
            return ResponseHelper::error($e->errors(), 'Validation failed.', 422);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 'Server Error.');
        }
    }

    /**
     * Update a question and its options
     */



    /**
     * @OA\Put(
     *     path="/admin/aptitudes/{id}",
     *     tags={"Aptitude"},
     *     summary="Update an existing aptitude question and its options",
     *     description="Updates an aptitude question and optionally its related options. 
     *     Existing options can be updated by including their IDs, or new options can be added without an ID.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the aptitude question to update",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="How do you manage your time effectively?"),
     *             @OA\Property(property="sub_title", type="string", nullable=true, example="Pick one that describes your approach."),
     *             @OA\Property(property="qn_type", type="string", enum={"single","multiple"}, example="single"),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", nullable=true, example=11, description="If provided, updates the option instead of creating a new one."),
     *                     @OA\Property(property="title", type="string", example="I plan my day ahead."),
     *                     @OA\Property(property="key", type="string", nullable=true, example=null),
     *                     @OA\Property(property="sub_title", type="string", nullable=true, example=null),
     *                     @OA\Property(property="icon", type="string", nullable=true, example=null),
     *                     @OA\Property(property="color", type="string", nullable=true, example="#0088FF")
     *                 )
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Aptitude question updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Aptitude question updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="title", type="string", example="How do you manage your time effectively?"),
     *                 @OA\Property(property="sub_title", type="string", nullable=true, example="Pick one that describes your approach."),
     *                 @OA\Property(property="qn_type", type="string", example="single"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-24T06:10:45.000000Z"),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=11),
     *                         @OA\Property(property="question_id", type="integer", example=5),
     *                         @OA\Property(property="title", type="string", example="I plan my day ahead."),
     *                         @OA\Property(property="key", type="string", nullable=true, example=null),
     *                         @OA\Property(property="sub_title", type="string", nullable=true, example=null),
     *                         @OA\Property(property="icon", type="string", nullable=true, example=null),
     *                         @OA\Property(property="color", type="string", nullable=true, example="#0088FF"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-24T06:10:45.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"title": {"The title field is required."}}
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Question not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No query results for model [AptitudeQuestion] 999"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'sub_title' => 'nullable|string|max:255',
                'qn_type' => 'sometimes|required|in:single,multiple',
                'options' => 'nullable|array',
                'options.*.id' => 'nullable|exists:aptitude_options,id',
                'options.*.title' => 'required_with:options|string|max:255',
                'options.*.key' => 'nullable|string|max:10',
                'options.*.sub_title' => 'nullable|string|max:255',
                'options.*.icon' => 'nullable|string|max:255',
                'options.*.color' => 'nullable|string|max:50',
            ], [
                'qn_type.in' => 'Should be single or multiple'
            ]);

            DB::beginTransaction();

            $question = AptitudeQuestion::findOrFail($id);
            $question->update($validated);

            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $optionData) {
                    if (isset($optionData['id'])) {
                        $option = AptitudeOption::find($optionData['id']);
                        $option?->update($optionData);
                    } else {
                        $question->options()->create($optionData);
                    }
                }
            }

            DB::commit();

            return ResponseHelper::success(
                $question->load('options'),
                'Aptitude question updated successfully.'
            );
        } catch (ValidationException $e) {
            return ResponseHelper::error($e->errors(), 'Validation failed.', 422);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 'Server Error.');
        }
    }

    /**
     * Show a question with its options
     */
    /**
     * @OA\Get(
     *     path="/admin/aptitudes/{id}",
     *     tags={"Aptitude"},
     *     summary="Fetch a single aptitude question by ID",
     *     description="Returns a specific aptitude question and its related options.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the aptitude question to retrieve",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Aptitude question fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Aptitude question fetched successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="title", type="string", example="How do you master your time?"),
     *                 @OA\Property(property="sub_title", type="string", nullable=true, example=null),
     *                 @OA\Property(property="qn_type", type="string", example="single"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=11),
     *                         @OA\Property(property="question_id", type="integer", example=5),
     *                         @OA\Property(property="title", type="string", example="Easy"),
     *                         @OA\Property(property="key", type="string", nullable=true, example=null),
     *                         @OA\Property(property="sub_title", type="string", nullable=true, example=null),
     *                         @OA\Property(property="icon", type="string", nullable=true, example=null),
     *                         @OA\Property(property="color", type="string", nullable=true, example="#00AAFF"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-24T05:45:16.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Aptitude question not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No query results for model [AptitudeQuestion] 999"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        try {
            $question = AptitudeQuestion::with('options')->findOrFail($id);
            return ResponseHelper::success($question, 'Aptitude question fetched successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 'Server Error.', 404);
        }
    }

    /**
     * Delete a question and all its options (cascade)
     */
    /**
     * @OA\Delete(
     *     path="/admin/aptitudes/{id}",
     *     tags={"Aptitude"},
     *     summary="Delete an aptitude question",
     *     description="Deletes a specific aptitude question and all its related options.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the aptitude question to delete",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Aptitude question deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Aptitude question deleted successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Aptitude question not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No query results for model [AptitudeQuestion] 999"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        try {
            $question = AptitudeQuestion::findOrFail($id);
            $question->delete();
            return ResponseHelper::success(null, 'Aptitude question deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 'Server Error.', 404);
        }
    }

    /**
     * Delete a single option from a question
     */

    /**
     * @OA\Delete(
     *     path="/admin/aptitude/{questionId}/options/{optionId}",
     *     tags={"Aptitude"},
     *     summary="Delete an option from an aptitude question",
     *     description="Deletes a specific option belonging to a particular aptitude question.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="questionId",
     *         in="path",
     *         required=true,
     *         description="ID of the aptitude question",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="optionId",
     *         in="path",
     *         required=true,
     *         description="ID of the option to delete",
     *         @OA\Schema(type="integer", example=11)
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Option deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option deleted successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Option not found or does not belong to question",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No query results for model [AptitudeOption] 99"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             
     *         )
     *     )
     * )
     */

    public function destroyOptions($questionId, $optionId)
    {
        try {
            $option = AptitudeOption::where('question_id', $questionId)
                ->where('id', $optionId)
                ->firstOrFail();

            $option->delete();

            return ResponseHelper::success(null, 'Option deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 'Server Error.', 404);
        }
    }






}
