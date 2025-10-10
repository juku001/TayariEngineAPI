<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Str;

class CourseController extends Controller
{



    protected $logService;



    public function __construct(AdminLogService $logService)
    {
        $this->logService = $logService;
    }




    /**
     * @OA\Get(
     *     path="/courses",
     *     operationId="getCourses",
     *     tags={"Courses"},
     *     summary="Fetch all courses",
     *     description="Public endpoint. Returns a list of courses with instructor details, skills, lesson counts, total duration, and enrollment status.",
     **     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="Filter courses by category ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="category_name",
     *         in="query",
     *         required=false,
     *         description="Filter courses by category name (partial match allowed)",
     *         @OA\Schema(type="string", example="Programming")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of courses"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Intro to Programming"),
     *                     @OA\Property(property="description", type="string", example="Learn the basics of programming with hands-on examples."),
     *                     @OA\Property(property="price", type="number", format="float", example=49.99),
     *                     @OA\Property(property="rating", type="number", format="float", example=4.5),
     *                     @OA\Property(property="cover_image", type="string", example="https://cdn.example.com/images/course1.jpg"),
     *                     @OA\Property(property="videos_count", type="integer", example=12),
     *                     @OA\Property(property="total_duration", type="integer", example=3600, description="Total duration in seconds"),
     *                     @OA\Property(
     *                         property="instructor",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="John Doe")
     *                     ),
     *                     @OA\Property(
     *                         property="skills",
     *                         type="array",
     *                         @OA\Items(type="string", example="PHP")
     *                     ),
     *                     @OA\Property(property="is_enrolled", type="boolean", example=false),
     *                     @OA\Property(property="progress", type="integer", nullable=true, example=null)
     *                 )
     *             ),
     *             example={
     *                 "status": true,
     *                 "message": "List of courses",
     *                 "code": 200,
     *                 "data": {
     *                     {
     *                         "id": 1,
     *                         "name": "Intro to Programming",
     *                         "description": "Learn the basics of programming with hands-on examples.",
     *                         "price": 49.99,
     *                         "rating": 4.5,
     *                         "cover_image": "https://cdn.example.com/images/course1.jpg",
     *                         "videos_count": 12,
     *                         "total_duration": 3600,
     *                         "instructor": {
     *                             "id": 5,
     *                             "name": "John Doe"
     *                         },
     *                         "skills": {"PHP","Laravel"},
     *                         "is_enrolled": false,
     *                         "progress": null
     *                     },
     *                     {
     *                         "id": 2,
     *                         "name": "Advanced UI/UX",
     *                         "description": "Master advanced design systems and UX best practices.",
     *                         "price": 79.99,
     *                         "rating": 4.7,
     *                         "cover_image": "https://cdn.example.com/images/course2.jpg",
     *                         "videos_count": 20,
     *                         "total_duration": 5400,
     *                         "instructor": {
     *                             "id": 6,
     *                             "name": "Jane Smith"
     *                         },
     *                         "skills": {"UI/UX","Design Thinking"},
     *                         "is_enrolled": true,
     *                         "progress": 40
     *                     }
     *                 }
     *             }
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

    public function index(Request $request)
    {
        $query = Course::with([
            'instructorUser',
            'skills',
            'modules.lessons',
            'enrollments',
            'category'
        ]);

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('category_name') && !empty($request->category_name)) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->category_name}%");
            });
        }

        $courses = $query->get()->map(function ($course) use ($request) {
            $lessons = $course->modules->flatMap->lessons;
            $videoCount = $lessons->count();
            $totalDuration = $lessons->sum('duration');

            $progress = null;
            $isEnrolled = false;

            $authUser = null;
            if ($token = $request->bearerToken()) {
                if ($accessToken = PersonalAccessToken::findToken($token)) {
                    $authUser = $accessToken->tokenable;
                }
            }

            if ($authUser) {
                $enrollment = $course->enrollments()
                    ->where('user_id', $authUser->id)
                    ->first();

                if ($enrollment) {
                    $isEnrolled = true;
                    $progress = $enrollment->progress;
                }
            }

            return [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'price' => $course->price,
                'rating' => $course->avg_rating,
                'cover_image' => $course->cover_image,
                'videos_count' => $videoCount,
                'total_duration' => $totalDuration,
                'instructor' => [
                    'id' => optional($course->instructorUser)->id,
                    'name' => optional($course->instructorUser)->id != null
                        ? optional($course->instructorUser)->first_name . ' ' . optional($course->instructorUser)->last_name
                        : null,
                ],
                'skills' => $course->skills->pluck('name'),
                'is_enrolled' => $isEnrolled,
                'progress' => $progress,
                'category' => [
                    'id' => optional($course->category)->id,
                    'name' => optional($course->category)->name,
                ],
            ];
        });

        return ResponseHelper::success($courses, 'Courses retrieved successfully');
    }




    /**
     * @OA\Get(
     *     path="/admin/courses/stats",
     *     tags={"Admin"},
     *     summary="Get course statistics",
     *     security={{"bearerAuth":{}}},
     *     description="Returns aggregated counts of courses by status (draft, published, archived) along with total count. Useful for admin course management overview.",
     *     @OA\Response(
     *         response=200,
     *         description="Details on Courses management",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Details on Courses management"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=120),
     *                 @OA\Property(property="draft", type="integer", example=35),
     *                 @OA\Property(property="published", type="integer", example=70),
     *                 @OA\Property(property="archived", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     )
     * )
     */


    public function stats()
    {
        $stats = Course::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $data = [
            "total" => $stats->sum(),
            "draft" => $stats->get('draft', 0),
            "published" => $stats->get('published', 0),
            "archived" => $stats->get('archived', 0),
        ];

        return ResponseHelper::success($data, "Details on Courses management");
    }




    /**
     * @OA\Post(
     *     path="/admin/courses",
     *     tags={"Admin"},
     *     summary="Create a new course",
     *     description="Creates a new course with thumbnail, modules, lessons (with video uploads), and quizzes including questions. Only accessible by admins.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title","description","thumbnail","status","modules"},
     *             @OA\Property(property="title", type="string", example="Introduction to Web Development"),
     *             @OA\Property(property="description", type="string", example="Learn the basics of web development including HTML, CSS, and JavaScript."),
     *             @OA\Property(property="thumbnail", type="string", format="binary", description="Thumbnail image file"),
     *             @OA\Property(property="status", type="string", enum={"draft","published"}, example="draft"),
     *             @OA\Property(
     *                 property="modules",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"title","lessons","quiz"},
     *                     @OA\Property(property="title", type="string", example="Module 1: HTML Basics"),
     *                     @OA\Property(property="description", type="string", example="Learn about HTML structure."),
     *                     @OA\Property(
     *                         property="lessons",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             required={"title","video"},
     *                             @OA\Property(property="title", type="string", example="Lesson 1: HTML Tags"),
     *                             @OA\Property(property="description", type="string", example="Introduction to common HTML tags."),
     *                             @OA\Property(property="video", type="string", format="binary", description="Video file upload")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="quiz",
     *                         type="object",
     *                         required={"title","questions"},
     *                         @OA\Property(property="title", type="string", example="Quiz 1: HTML Basics"),
     *                         @OA\Property(
     *                             property="questions",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 required={"question_text","type","option_a","option_b","option_c","option_d","correct_option"},
     *                                 @OA\Property(property="question_text", type="string", example="What does HTML stand for?"),
     *                                 @OA\Property(property="type", type="string", enum={"mcq","true_false","short_answer"}, example="mcq"),
     *                                 @OA\Property(property="option_a", type="string", example="Hyper Text Markup Language"),
     *                                 @OA\Property(property="option_b", type="string", example="Home Tool Markup Language"),
     *                                 @OA\Property(property="option_c", type="string", example="Hyperlinks and Text Markup Language"),
     *                                 @OA\Property(property="option_d", type="string", example="Hyper Tool Multi Language"),
     *                                 @OA\Property(property="correct_option", type="string", example="option_a")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation errors"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create course",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create course"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE[23000]: Integrity constraint violation ...")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:draft,published',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.lessons' => 'required|array|min:1',
            'modules.*.lessons.*.title' => 'required|string|max:255',
            'modules.*.lessons.*.video' => 'required|file|mimes:mp4,mov,avi|max:1024000',
            'modules.*.quiz' => 'required|array',
            'modules.*.quiz.title' => 'required|string|max:255',
            'modules.*.quiz.questions' => 'required|array|min:1',
            'modules.*.quiz.questions.*.question_text' => 'required|string',
            'modules.*.quiz.questions.*.type' => 'sometimes|in:mcq,true_false,short_answer',
            'modules.*.quiz.questions.*.option_a' => 'required|string',
            'modules.*.quiz.questions.*.option_b' => 'required|string',
            'modules.*.quiz.questions.*.option_c' => 'required|string',
            'modules.*.quiz.questions.*.option_d' => 'required|string',
            'modules.*.quiz.questions.*.correct_option' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }


        $uploadedFiles = [];

        DB::beginTransaction();
        try {

            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $uploadedFiles[] = $thumbnailPath;

            $course = Course::create([
                'name' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'cover_image' => $thumbnailPath,
                'created_by' => auth()->user()->id,
                'status' => $request->status,
            ]);


            foreach ($request->modules as $moduleData) {
                $module = $course->modules()->create([
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'] ?? null
                ]);


                foreach ($moduleData['lessons'] as $lessonData) {
                    $videoPath = $lessonData['video']->store('videos', 'public');
                    $uploadedFiles[] = $videoPath;

                    $module->lessons()->create([
                        'title' => $lessonData['title'],
                        'course_id' => $course->id,
                        'description' => $lessonData['description'] ?? null,
                        'content_url' => $videoPath
                    ]);
                }


                $quiz = $module->quizzes()->create([
                    'title' => $moduleData['quiz']['title']
                ]);


                foreach ($moduleData['quiz']['questions'] as $qData) {
                    $quiz->questions()->create([
                        'question_text' => $qData['question_text'],
                        'type' => $qData['type'] ?? 'mcq',
                        'option_a' => $qData['option_a'] ?? null,
                        'option_b' => $qData['option_b'] ?? null,
                        'option_c' => $qData['option_c'] ?? null,
                        'option_d' => $qData['option_d'] ?? null,
                        'correct_option' => $qData['correct_option']
                    ]);
                }
            }

            DB::commit();

            $authId = auth()->user()->id;
            $action = $this->logService->getActionByCode(8);
            $this->logService->record($authId, $action, "Added " . $course->name);
            return response()->json([
                'status' => true,
                'message' => 'Course created successfully',
                'code' => 201,
                'data' => $course->load('modules.lessons', 'modules.quizzes.questions')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();


            foreach ($uploadedFiles as $filePath) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json([
                'status' => false,
                'message' => 'Failed to create course',
                'code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }















    /**
     * @OA\Patch(
     *     path="/admin/courses/{id}",
     *     tags={"Admin"},
     *     summary="Update an existing course",
     *     security={{"bearerAuth":{}}},
     *     description="Updates an existing course including its thumbnail, modules, lessons, quizzes, and questions. Supports partial updates.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Updated Course Title"),
     *             @OA\Property(property="description", type="string", example="Updated course description."),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, example="published"),
     *             @OA\Property(property="thumbnail", type="string", format="binary", description="Thumbnail image file"),
     *             @OA\Property(
     *                 property="modules",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="title", type="string", example="Module 1 - Basics"),
     *                     @OA\Property(property="description", type="string", example="Introduction to the basics"),
     *                     @OA\Property(
     *                         property="lessons",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=100),
     *                             @OA\Property(property="title", type="string", example="Lesson 1 - Getting Started"),
     *                             @OA\Property(property="description", type="string", example="Lesson intro"),
     *                             @OA\Property(property="video", type="string", format="binary", description="Video file")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="quiz",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=200),
     *                         @OA\Property(property="title", type="string", example="Quiz 1"),
     *                         @OA\Property(
     *                             property="questions",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=300),
     *                                 @OA\Property(property="question_text", type="string", example="What is PHP?"),
     *                                 @OA\Property(property="type", type="string", enum={"mcq", "true_false", "short_answer"}, example="mcq"),
     *                                 @OA\Property(property="option_a", type="string", example="Programming Language"),
     *                                 @OA\Property(property="option_b", type="string", example="Database"),
     *                                 @OA\Property(property="option_c", type="string", example="Web Server"),
     *                                 @OA\Property(property="option_d", type="string", example="Operating System"),
     *                                 @OA\Property(property="correct_option", type="string", example="A")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course updated successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 description="Updated course with modules, lessons, quizzes, and questions"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation errors"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update course",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update course"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE[...]: ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     )
     * )
     */


    public function update(Request $request, string $id)
    {
        $course = Course::with('modules.lessons', 'modules.quizzes.questions')->find($id);

        if (!$course) {
            return ResponseHelper::error(
                [],
                'Course not found',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'thumbnail' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'sometimes|required|in:draft,published',
            'modules' => 'sometimes|array|min:1',
            'modules.*.id' => 'sometimes|exists:modules,id',
            'modules.*.title' => 'required_with:modules|string|max:255',
            'modules.*.lessons' => 'sometimes|array|min:1',
            'modules.*.lessons.*.id' => 'sometimes|exists:lessons,id',
            'modules.*.lessons.*.title' => 'required_with:modules.*.lessons|string|max:255',
            'modules.*.lessons.*.video' => 'sometimes|file|mimes:mp4,mov,avi|max:1024000',
            'modules.*.quiz' => 'sometimes|array',
            'modules.*.quiz.id' => 'sometimes|exists:quizzes,id',
            'modules.*.quiz.title' => 'required_with:modules.*.quiz|string|max:255',
            'modules.*.quiz.questions' => 'sometimes|array|min:1',
            'modules.*.quiz.questions.*.id' => 'sometimes|exists:questions,id',
            'modules.*.quiz.questions.*.question_text' => 'required_with:modules.*.quiz.questions|string',
            'modules.*.quiz.questions.*.type' => 'required_with:modules.*.quiz.questions|in:mcq,true_false,short_answer',
            'modules.*.quiz.questions.*.option_a' => 'nullable|string',
            'modules.*.quiz.questions.*.option_b' => 'nullable|string',
            'modules.*.quiz.questions.*.option_c' => 'nullable|string',
            'modules.*.quiz.questions.*.option_d' => 'nullable|string',
            'modules.*.quiz.questions.*.correct_option' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), "Failed to validate fields", 422);
        }


        DB::beginTransaction();
        try {
            // Update main course fields
            if ($request->has('title')) {
                $course->name = $request->title;
                $course->slug = Str::slug($request->title);
            }
            if ($request->has('description')) {
                $course->description = $request->description;
            }
            if ($request->has('status')) {
                $course->status = $request->status;
            }

            // Handle new thumbnail upload
            if ($request->hasFile('thumbnail')) {
                if ($course->cover_image) {
                    Storage::disk('public')->delete($course->cover_image);
                }
                $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
                $course->cover_image = $thumbnailPath;
            }

            $course->save();

            // Update or add modules
            if ($request->has('modules')) {
                foreach ($request->modules as $moduleData) {
                    $module = isset($moduleData['id'])
                        ? $course->modules()->find($moduleData['id'])
                        : $course->modules()->create(['title' => $moduleData['title']]);

                    if ($module) {
                        $module->update([
                            'title' => $moduleData['title'] ?? $module->title,
                            'description' => $moduleData['description'] ?? $module->description,
                        ]);
                    }

                    // Update lessons
                    if (!empty($moduleData['lessons'])) {
                        foreach ($moduleData['lessons'] as $lessonData) {
                            $lesson = isset($lessonData['id'])
                                ? $module->lessons()->find($lessonData['id'])
                                : $module->lessons()->create([
                                    'title' => $lessonData['title'],
                                    'course_id' => $course->id,
                                ]);

                            if ($lesson) {
                                if ($request->hasFile("modules.*.lessons.*.video")) {
                                    if ($lesson->content_url) {
                                        Storage::disk('public')->delete($lesson->content_url);
                                    }
                                    $videoPath = $lessonData['video']->store('videos', 'public');
                                    $lesson->content_url = $videoPath;
                                }
                                $lesson->update([
                                    'title' => $lessonData['title'] ?? $lesson->title,
                                    'description' => $lessonData['description'] ?? $lesson->description,
                                ]);
                            }
                        }
                    }

                    // Update quiz
                    if (!empty($moduleData['quiz'])) {
                        $quiz = isset($moduleData['quiz']['id'])
                            ? $module->quizzes()->find($moduleData['quiz']['id'])
                            : $module->quizzes()->create(['title' => $moduleData['quiz']['title']]);

                        if ($quiz) {
                            $quiz->update([
                                'title' => $moduleData['quiz']['title'] ?? $quiz->title,
                            ]);

                            // Update quiz questions
                            if (!empty($moduleData['quiz']['questions'])) {
                                foreach ($moduleData['quiz']['questions'] as $qData) {
                                    $question = isset($qData['id'])
                                        ? $quiz->questions()->find($qData['id'])
                                        : $quiz->questions()->create([
                                            'question_text' => $qData['question_text'],
                                            'type' => $qData['type'],
                                        ]);

                                    if ($question) {
                                        $question->update([
                                            'question_text' => $qData['question_text'] ?? $question->question_text,
                                            'type' => $qData['type'] ?? $question->type,
                                            'option_a' => $qData['option_a'] ?? $question->option_a,
                                            'option_b' => $qData['option_b'] ?? $question->option_b,
                                            'option_c' => $qData['option_c'] ?? $question->option_c,
                                            'option_d' => $qData['option_d'] ?? $question->option_d,
                                            'correct_option' => $qData['correct_option'] ?? $question->correct_option,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            $authId = auth()->user()->id;
            $action = $this->logService->getActionByCode(5);
            $this->logService->record($authId, $action, "Updated " . $course->name);
            return response()->json([
                'status' => true,
                'message' => 'Course updated successfully',
                'data' => $course->load('modules.lessons', 'modules.quizzes.questions')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update course',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/courses/{id}",
     *     operationId="getCourseDetails",
     *     tags={"Courses"},
     *     summary="Fetch course details by ID",
     *     description="Public endpoint. Returns detailed information about a specific course, including its modules, lessons, and quizzes.",
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
     *         description="Course details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course details retrieved successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="name", type="string", example="Organic Chemistry"),
     *                 @OA\Property(property="slug", type="string", example="organic-chemistry"),
     *                 @OA\Property(property="subtitle", type="string", nullable=true, example=null),
     *                 @OA\Property(property="description", type="string", example="This is the organic chemistry topic course"),
     *                 @OA\Property(property="objectives", type="string", nullable=true, example=null),
     *                 @OA\Property(property="requirements", type="string", nullable=true, example=null),
     *                 @OA\Property(property="language", type="string", nullable=true, example=null),
     *                 @OA\Property(property="level_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="category_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="cover_image", type="string", example="thumbnails/GiGazPMb4NtRMzNcGAvAVeoAPHmrAH7ME5vYBrkN.png"),
     *                 @OA\Property(property="cover_video", type="string", nullable=true, example=null),
     *                 @OA\Property(property="price", type="number", nullable=true, example=null),
     *                 @OA\Property(property="is_free", type="integer", example=1),
     *                 @OA\Property(property="is_featured", type="integer", example=0),
     *                 @OA\Property(property="certificate_type", type="string", example="completion"),
     *                 @OA\Property(property="status", type="string", example="draft"),
     *                 @OA\Property(property="tags", type="string", nullable=true, example=null),
     *                 @OA\Property(property="avg_rating", type="string", example="0.0"),
     *                 @OA\Property(property="instructor", type="integer", example=2),
     *                 @OA\Property(property="created_by", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z"),
     *                 @OA\Property(
     *                     property="modules",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="course_id", type="integer", example=3),
     *                         @OA\Property(property="title", type="string", example="First Module"),
     *                         @OA\Property(property="description", type="string", nullable=true, example=null),
     *                         @OA\Property(property="order", type="integer", example=0),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z"),
     *                         @OA\Property(
     *                             property="lessons",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="course_id", type="integer", example=3),
     *                                 @OA\Property(property="module_id", type="integer", example=3),
     *                                 @OA\Property(property="title", type="string", example="What is programming ?"),
     *                                 @OA\Property(property="description", type="string", example="Overview"),
     *                                 @OA\Property(property="content_type", type="string", example="video"),
     *                                 @OA\Property(property="content_url", type="string", example="videos/ztIvHnEwucJi7l4ShuVBSBVnkSRdxTbKm7Wey49q.mp4"),
     *                                 @OA\Property(property="content_text", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="duration", type="integer", nullable=true, example=null),
     *                                 @OA\Property(property="order", type="integer", example=0),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="quizzes",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="module_id", type="integer", example=3),
     *                                 @OA\Property(property="title", type="string", example="Programming Quiz"),
     *                                 @OA\Property(property="passing_score", type="integer", example=50),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-28T20:46:29.000000Z")
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             example={
     *                 "status": true,
     *                 "message": "Course details retrieved successfully",
     *                 "code": 200,
     *                 "data": {
     *                     "id": 3,
     *                     "name": "Organic Chemistry",
     *                     "slug": "organic-chemistry",
     *                     "subtitle": null,
     *                     "description": "This is the organic chemistry topic course",
     *                     "objectives": null,
     *                     "requirements": null,
     *                     "language": null,
     *                     "level_id": null,
     *                     "category_id": null,
     *                     "cover_image": "thumbnails/GiGazPMb4NtRMzNcGAvAVeoAPHmrAH7ME5vYBrkN.png",
     *                     "cover_video": null,
     *                     "price": null,
     *                     "is_free": 1,
     *                     "is_featured": 0,
     *                     "certificate_type": "completion",
     *                     "status": "draft",
     *                     "tags": null,
     *                     "avg_rating": "0.0",
     *                     "instructor": 2,
     *                     "created_by": null,
     *                     "created_at": "2025-08-28T20:46:29.000000Z",
     *                     "updated_at": "2025-08-28T20:46:29.000000Z",
     *                     "modules": {
     *                         {
     *                             "id": 3,
     *                             "course_id": 3,
     *                             "title": "First Module",
     *                             "description": null,
     *                             "order": 0,
     *                             "created_at": "2025-08-28T20:46:29.000000Z",
     *                             "updated_at": "2025-08-28T20:46:29.000000Z",
     *                             "lessons": {
     *                                 {
     *                                     "id": 1,
     *                                     "course_id": 3,
     *                                     "module_id": 3,
     *                                     "title": "What is programming ?",
     *                                     "description": "Overview",
     *                                     "content_type": "video",
     *                                     "content_url": "videos/ztIvHnEwucJi7l4ShuVBSBVnkSRdxTbKm7Wey49q.mp4",
     *                                     "content_text": null,
     *                                     "duration": null,
     *                                     "order": 0,
     *                                     "created_at": "2025-08-28T20:46:29.000000Z",
     *                                     "updated_at": "2025-08-28T20:46:29.000000Z"
     *                                 }
     *                             },
     *                             "quizzes": {
     *                                 {
     *                                     "id": 1,
     *                                     "module_id": 3,
     *                                     "title": "Programming Quiz",
     *                                     "passing_score": 50,
     *                                     "created_at": "2025-08-28T20:46:29.000000Z",
     *                                     "updated_at": "2025-08-28T20:46:29.000000Z"
     *                                 }
     *                             }
     *                         }
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course details not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
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

    public function show(Request $request, $id)
    {
        $token = $request->bearerToken();
        $authUser = null;
        $showFullDetails = false;

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $authUser = $accessToken->tokenable;
            }
        }

        $enrollment = null;
        if ($authUser) {
            $enrollment = Enrollment::where('user_id', $authUser->id)
                ->where('course_id', $id)
                ->first();
        }

        if ($enrollment) {
            $showFullDetails = true;
        }

        if ($showFullDetails) {
            // Full details
            $course = Course::with([
                'modules.lessons',
                'modules.quizzes'
            ])->find($id);
        } else {
            // Slim version
            $course = Course::with(['modules.lessons'])->find($id);

            if ($course) {
                // Calculate totals
                $totalModules = $course->modules->count();
                $totalLessons = $course->modules->sum(fn($m) => $m->lessons->count());

                // Transform response
                $course = [
                    'id' => $course->id,
                    'title' => $course->name,
                    'sub-title' => $course->sub_title,
                    'description' => $course->description,
                    'skills' => $course->skills->map(function ($skill) {

                        return $skill->name;
                    }),
                    'instructor' => $course->instructorUser(),
                    'total_modules' => $totalModules,
                    'total_lessons' => $totalLessons,
                    'content' => $course->modules->map(function ($module) {
                        return [
                            'id' => $module->id,
                            'title' => $module->title,
                            'lessons' => $module->lessons->map(function ($lesson) {
                                return [
                                    'id' => $lesson->id,
                                    'title' => $lesson->title,
                                    'duration' => $lesson->duration,
                                    'description' => $lesson->description,
                                    'content_type' => $lesson->content_type,
                                    'content_url' => $lesson->content_url,
                                    'content_text' => $lesson->content_text,
                                                                        'order' => $lesson->order,
                                ];
                            }),
                        ];
                    }),
                ];
            }
        }

        if (!$course) {
            return ResponseHelper::error([], 'Course details not found', 404);
        }

        return ResponseHelper::success($course, 'Course details retrieved successfully');
    }








    /**
     * @OA\Patch(
     *     path="/admin/courses/{id}/publish",
     *     tags={"Admin"},
     *     summary="Publish a course",
     *     security={{"bearerAuth":{}}},
     *     description="Sets the course status to published",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course published successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course published"),
     *             @OA\Property(property="code", type="integer", example=200),
     * 
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Course already published",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course already published"),
     *             @OA\Property(property="code", type="integer", example=400),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *         )
     *     )
     * )
     */
    public function status(string $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return ResponseHelper::error([], "Course not found", 404);
        }

        if ($course->status === 'published') {
            return ResponseHelper::error([], "Course already published", 400);
        }

        $course->status = 'published';
        $course->save();

        return ResponseHelper::success([], "Course published");
    }




    /**
     * @OA\Get(
     *     path="/admin/courses",
     *     tags={"Admin"},
     *     summary="Get list of courses for admin with filters",
     *     security={{"bearerAuth":{}}},
     *     description="Returns list of courses with title, subtitle, description, instructor info, status, module count, students enrolled, and last updated. Supports search and status filter.",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by course status (draft or published)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft","published"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by course title or instructor name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of admin filtered courses",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of admin filtered courses"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="title", type="string", example="Organic Chemistry"),
     *                     @OA\Property(property="subtitle", type="string", nullable=true, example=null),
     *                     @OA\Property(property="description", type="string", example="This is the organic chemistry topic course"),
     *                     @OA\Property(property="instructor_name", type="string", nullable=true, example="John Doe"),
     *                     @OA\Property(property="instructor_email", type="string", nullable=true, example="john@example.com"),
     *                     @OA\Property(property="status", type="string", example="published"),
     *                     @OA\Property(property="module_count", type="integer", example=3),
     *                     @OA\Property(property="students_enrolled", type="integer", example=10),
     *                     @OA\Property(property="last_updated", type="string", format="date-time", example="2025-08-30 08:28:41")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     * )
     */

    public function admin(Request $request)
    {
        // Get filters from request
        $status = $request->query('status');
        $search = $request->query('search');

        // Query courses
        $query = Course::with(['instructorUser', 'modules', 'enrollments']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('instructorUser', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $courses = $query->get()->map(function ($course) {
            return [
                'title' => $course->name,
                'subtitle' => $course->subtitle ?? null,
                'description' => $course->description,
                'instructor_name' => $course->instructorUser->name ?? null,
                'instructor_email' => $course->instructorUser->email ?? null,
                'status' => $course->status,
                'module_count' => $course->modules->count(),
                'students_enrolled' => $course->enrollments->count(),
                'last_updated' => $course->updated_at->toDateTimeString(),
            ];
        });

        return ResponseHelper::success(
            $courses,
            "List of admin filtered courses"
        );
    }



    /**
     * @OA\Delete(
     *     path="/admin/courses/{id}",
     *     tags={"Admin"},
     *     summary="Delete a course",
     *     security={{ "bearerAuth":{} }},
     *     description="Remove a course by its ID. This will delete the course, module, quiz and all the lessons of that course.  Once deleted, the course  cannot be recovered.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course to delete",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course deleted successful"),
     *             @OA\Property(property="code", type="integer", example=204),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Database connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             
     *         )
     *     )
     * )
     */


    public function destroy($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return ResponseHelper::error([], "Course not found", 404);
        }

        $course->delete();

        return ResponseHelper::success(
            [],
            "Course delted with all its contents.",
            204
        );

    }


}
