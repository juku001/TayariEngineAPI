<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use App\Models\Employer;
use App\Models\JobPost;
use App\Models\JobPostType;
use App\Models\JobSkill;
use App\Services\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
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
     *     summary="Get all published jobs with search and filter",
     *     description="Returns a list of jobs. You can search by job title or company name and filter by job type (e.g. full-time, internship).",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for job title or company name",
     *         required=false,
     *         @OA\Schema(type="string", example="developer")
     *     ),
     *     @OA\Parameter(
     *         name="job_type",
     *         in="query",
     *         description="Filter by job type (full-time, internship, etc.)",
     *         required=false,
     *         @OA\Schema(type="string", example="full-time")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of jobs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of jobs"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                     @OA\Property(property="description", type="string", example="Exciting opportunity..."),
     *                     @OA\Property(property="city", type="string", example="Dar es Salaam"),
     *                     @OA\Property(property="country", type="string", example="Tanzania"),
     *                     @OA\Property(property="status", type="string", example="published"),
     *                     @OA\Property(property="currency", type="string", example="TZS"),
     *                     @OA\Property(property="company", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="TechCorp Ltd")
     *                     ),
     *                     @OA\Property(property="job_post_type", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="full-time")
     *                     ),
     *                     @OA\Property(
     *                         property="job_skills",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="skill_id", type="integer", example=2)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No jobs found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No jobs found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */


    public function index(Request $request)
    {
        $query = JobPost::with('jobPostType', 'jobSkills', 'company')
            ->where('status', 'published');


        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }


        if ($request->has('job_type') && !empty($request->job_type)) {
            $jobType = $request->job_type;
            $query->whereHas('jobPostType', function ($q) use ($jobType) {
                $q->where('name', $jobType);
            });
        }

        $jobs = $query->get();

        return ResponseHelper::success(
            $jobs,
            "List of jobs"
        );
    }






    /**
     * @OA\Get(
     *     path="/jobs/companies/{id}",
     *     summary="Get all jobs by companies",
     *     description="Returns a list of jobs posted by a given companies (company).",
     *     operationId="getCompanyJobs",
     *     tags={"Employer"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Company ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all jobs"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="title", type="string", example="Software Engineer"),
     *                     @OA\Property(property="description", type="string", example="Job description here"),
     *                     @OA\Property(
     *                         property="job_post_type",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Full-time")
     *                     ),
     *                     @OA\Property(
     *                         property="job_skills",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="name", type="string", example="PHP")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Company not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Company not found"),
     *              @OA\Property(property="code", type="integer", example=404),
     *            
     *         )
     *     )
     * )
     */

    public function companies($id)
    {

        $company = Company::find($id);
        if (!$company) {
            return ResponseHelper::error([], "Company not found", 404);
        }

        $jobs = JobPost::with('jobPostType', 'jobSkills')->where('company_id', $id)->get();
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
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         ref="#/components/responses/500"
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
        ], [
            'job_type.in' => 'Job type should be full-time,project,flexible-virtual-hire or internship'
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





    /**
     * @OA\Get(
     *     path="/jobs/{id}",
     *     tags={"Employer"},
     *     summary="Get job details by ID",
     *     description="Fetch job details including job type and skills.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Job ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                 @OA\Property(property="description", type="string", example="Get a Job for free my dear."),
     *                 @OA\Property(property="city", type="string", example="Dar es Salaam"),
     *                 @OA\Property(property="country", type="string", example="Tanzania"),
     *                 @OA\Property(property="status", type="string", example="published"),
     *                 @OA\Property(property="currency", type="string", example="TZS"),
     *                 @OA\Property(property="is_remote", type="boolean", example=false),
     *                 @OA\Property(property="deadline", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="views", type="integer", example=0),
     *                 @OA\Property(property="applications_count", type="integer", example=0),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T18:06:51.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T18:06:51.000000Z"),
     *                 @OA\Property(
     *                     property="job_post_type",
     *                     type="object",
     *                     nullable=true,
     *                     description="Related job post type"
     *                 ),
     *                 @OA\Property(
     *                     property="job_skills",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="job_post_id", type="integer", example=1),
     *                         @OA\Property(property="skill_id", type="integer", example=2),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T18:06:51.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T18:06:51.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Job post not found."),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */


    public function show($id)
    {
        $job = JobPost::with('jobPostType', 'jobSkills')->find($id);

        if (!$job) {
            return ResponseHelper::error([], "Job post not found.", 404);
        }

        return ResponseHelper::success($job, 'Job details');
    }

}
