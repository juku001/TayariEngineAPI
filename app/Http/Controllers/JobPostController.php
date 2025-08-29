<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Employer;
use App\Models\JobPost;
use App\Models\JobPostType;
use App\Models\JobSkill;
use App\Services\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class JobPostController extends Controller
{

    protected $logService;


    public function __construct(AdminLogService $logService)
    {
        $this->logService = $logService;
    }





    /**
     * @OA\Get(
     *     path="/jobs",
     *     tags={"Employer"},
     *     summary="Get all job posts",
     *     description="Retrieve a list of all job posts with their type and required skills.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of all jobs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all jobs"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=15),
     *                     @OA\Property(property="title", type="string", example="Frontend Developer"),
     *                     @OA\Property(property="description", type="string", example="We are looking for a skilled frontend developer with React experience."),
     *                     @OA\Property(property="city", type="string", example="Berlin"),
     *                     @OA\Property(property="country", type="string", example="Germany"),
     *                     @OA\Property(property="type_id", type="integer", example=2),
     *                     @OA\Property(property="employer_id", type="integer", example=5),
     *                     @OA\Property(property="company_id", type="integer", example=3),
     *                     @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="published"),
     *                     @OA\Property(property="salary_min", type="number", example=1000),
     *                     @OA\Property(property="salary_max", type="number", example=3000),
     *                     @OA\Property(property="currency", type="string", example="EUR"),
     *                     @OA\Property(property="experience_level", type="string", example="mid"),
     *                     @OA\Property(property="education_level", type="string", example="bachelor"),
     *                     @OA\Property(property="is_remote", type="boolean", example=true),
     *                     @OA\Property(property="deadline", type="string", format="date", example="2025-12-31"),
     *                     @OA\Property(property="views", type="integer", example=120),
     *                     @OA\Property(property="applications_count", type="integer", example=10),
     *                     @OA\Property(property="slug", type="string", example="frontend-developer"),
     *
     *                     @OA\Property(
     *                         property="job_post_type",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Full-Time"),
     *                         @OA\Property(property="slug", type="string", example="full-time")
     *                     ),
     *
     *                     @OA\Property(
     *                         property="job_skills",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="job_post_id", type="integer", example=15),
     *                             @OA\Property(property="skill_id", type="integer", example=3)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $jobs = JobPost::with('jobPostType', 'jobSkills')->get();
        return ResponseHelper::success(
            $jobs,
            "List of all jobs"
        );
    }



    /**
     * @OA\Post(
     *     path="/jobs",
     *     tags={"Employer"},
     *     summary="Create a new job post",
     *     description="Allows an authenticated employer to create a job post with details like title, description, location, job type, required skills, and optional salary range.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","city","country","job_type","skills"},
     *             @OA\Property(property="title", type="string", example="Frontend Developer"),
     *             @OA\Property(property="description", type="string", example="We are looking for a skilled frontend developer with React experience."),
     *             @OA\Property(property="city", type="string", example="Berlin"),
     *             @OA\Property(property="country", type="string", example="Germany"),
     *             @OA\Property(
     *                 property="job_type",
     *                 type="string",
     *                 enum={"full-time","project","flexible-virtual-hire","internship"},
     *                 example="full-time"
     *             ),
     *             @OA\Property(
     *                 property="skills",
     *                 type="array",
     *                 @OA\Items(type="integer", example=3),
     *                 description="Array of skill IDs"
     *             ),
     *             @OA\Property(property="min_payment", type="number", example=1000),
     *             @OA\Property(property="max_payment", type="number", example=3000)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Job created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job created successfully."),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="title", type="string", example="Frontend Developer"),
     *                 @OA\Property(property="description", type="string", example="We are looking for a skilled frontend developer with React experience."),
     *                 @OA\Property(property="city", type="string", example="Berlin"),
     *                 @OA\Property(property="country", type="string", example="Germany"),
     *                 @OA\Property(property="type_id", type="integer", example=2),
     *                 @OA\Property(property="min_salary", type="number", example=1000),
     *                 @OA\Property(property="max_salary", type="number", example=3000),
     *                 @OA\Property(property="employer_id", type="integer", example=5),
     *                 @OA\Property(property="company_id", type="integer", example=2),
     *                 @OA\Property(
     *                     property="job_skills",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="job_post_id", type="integer", example=12),
     *                         @OA\Property(property="skill_id", type="integer", example=3)
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
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="The title field is required."))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Database connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'job_type' => 'required|in:full-time,project,flexible-virtual-hire,internship',
            'skills' => 'required|array|min:1',
            'skills.*' => 'int|max:100',

          
            'min_payment' => 'nullable|numeric|min:0',
            'max_payment' => 'nullable|numeric|gte:min_payment',
        ]);


        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                'Failed to validate fields',
                422
            );
        }

        try {
            DB::beginTransaction();

            $authId = auth()->user()->id;
            $employer = Employer::where('user_id', $authId)->first();


            $typeId = null;
            if (isset($request->job_type)) {
                $type = JobPostType::where('slug', $request->job_type)->first();
                if ($type) {
                    $type = $type->id;
                }
            }



            $job = JobPost::create([
                'title' => $request->title,
                'description' => $request->description,
                'city' => $request->city,
                'country' => $request->country,
                'type_id' => $typeId,
                'min_salary' => $request->min_payment,
                'max_salary' => $request->max_payment,
                'employer_id' => $employer->id, 
                'company_id' => $employer->company_id 
            ]);

         
            if ($request->has('skills')) {
                foreach ($request->skills as $skill) {
                    JobSkill::create([
                        'job_post_id' => $job->id,
                        'skill_id' => $skill
                    ]);
                }
            }

            DB::commit();


            $action = $this->logService->getActionByCode(7);
            $this->logService->record($authId, $action, "Posted new job: " . $job->title);
            return ResponseHelper::success(
                $job->load('jobSkills'), 
                'Job created successfully.',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error(
                [],
                "Error: {$e->getMessage()}",
                500
            );
        }
    }

}
