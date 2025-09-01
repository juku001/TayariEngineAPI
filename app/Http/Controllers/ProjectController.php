<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{


    /**
     * @OA\Get(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="Get list of projects with optional search",
     *     description="Retrieve all projects. You can search by project title or company name using the 'search' query parameter.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Project title or company name to search for",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Projects retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Projects retrieved successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                     @OA\Property(property="description", type="string", example="Project description here"),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="employer_id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="projectSkills",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="skill_id", type="integer", example=2)
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T18:39:39.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T18:39:39.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         ref="#/components/responses/500"
     *     )
     * )
     */


    public function index(Request $request)
    {
        try {
            $search = $request->get('search');

            $projects = Project::with('projectSkills')
                ->when($search, function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('company', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                })
                ->get(); // remove pagination

            return ResponseHelper::success($projects, "Projects retrieved successfully");
        } catch (\Exception $e) {
            return ResponseHelper::error([], "Failed to fetch projects: " . $e->getMessage(), 500);
        }
    }










    /**
     * @OA\Post(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="Create a new project",
     *     description="Employer creates a project with optional skills.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title","duration_min","duration_unit"},
     *             @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *             @OA\Property(property="description", type="string", example="Get a Job for free my dear."),
     *             @OA\Property(property="duration_min", type="integer", example=1),
     *             @OA\Property(property="duration_max", type="integer", nullable=true, example=6),
     *             @OA\Property(property="duration_unit", type="string", enum={"days","weeks","months","years"}, example="months"),
     *             @OA\Property(property="salary_min", type="number", format="float", example=500),
     *             @OA\Property(property="salary_max", type="number", format="float", example=1200),
     *             @OA\Property(property="currency", type="string", example="TZS"),
     *             @OA\Property(property="deadline", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(
     *                 property="skills",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Project created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Project created successfully"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                 @OA\Property(property="description", type="string", example="Get a Job for free my dear."),
     *                 @OA\Property(property="duration_min", type="integer", example=1),
     *                 @OA\Property(property="duration_max", type="integer", example=null),
     *                 @OA\Property(property="duration_unit", type="string", example="months"),
     *                 @OA\Property(property="employer_id", type="integer", example=1),
     *                 @OA\Property(property="company_id", type="integer", example=1),
     *                 @OA\Property(property="salary_min", type="number", example=500),
     *                 @OA\Property(property="salary_max", type="number", example=1200),
     *                 @OA\Property(property="currency", type="string", example="TZS"),
     *                 @OA\Property(property="deadline", type="string", format="date", example="2025-12-31"),
     *                 @OA\Property(
     *                     property="project_skills",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="project_id", type="integer", example=1),
     *                         @OA\Property(property="skill_id", type="integer", example=2)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employer not found",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(
     *             property="status",
     *             type="boolean",
     *             example=false,
     *           ),
     *           @OA\Property(
     *             property="message",
     *             type="string",
     *             example="Employer not found."
     *           ),
     *           @OA\Property(
     *             property="code",
     *             type="integer",
     *             example=404
     *           )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="Internal server error",
     *       ref="#/components/responses/500"
     *     )
     * )
     */


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_min' => 'required|integer|min:1',
            'duration_max' => 'nullable|integer|min:1',
            'duration_unit' => 'required|string|in:days,weeks,months,years',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'deadline' => 'nullable|date|after:today',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:skills,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 422);
        }
        $auth = auth()->user();
        $authId = $auth->id;
        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer) {
            return ResponseHelper::error([], "Employer Not Recognized", 404);
        }


        $validated = $validator->validated();
        $validated['employer_id'] = $employer->id;
        $validated['company_id'] = $employer->company_id;

        try {

            $project = Project::create($validated);


            if (!empty($validated['skills'])) {
                foreach ($validated['skills'] as $skillId) {
                    $project->projectSkills()->create([
                        'skill_id' => $skillId,
                    ]);
                }
            }

            return ResponseHelper::success($project->load('projectSkills'), "Project created successfully", 201);
        } catch (\Exception $e) {
            return ResponseHelper::error([], "Failed to create project: " . $e->getMessage(), 500);
        }
    }





    /**
     * @OA\Get(
     *     path="/projects/{id}",
     *     summary="Get project details",
     *     description="Retrieve a specific project with its details and related skills",
     *     tags={"Projects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Project ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Project details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                 @OA\Property(property="description", type="string", example="Get a Job for free my dear."),
     *                 @OA\Property(property="duration_min", type="integer", example=1),
     *                 @OA\Property(property="duration_max", type="integer", example=6),
     *                 @OA\Property(property="duration_unit", type="string", example="months"),
     *                 @OA\Property(property="employer_id", type="integer", example=1),
     *                 @OA\Property(property="company_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="salary_min", type="number", example=500),
     *                 @OA\Property(property="salary_max", type="number", example=1500),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="deadline", type="string", format="date-time", example="2025-09-30T23:59:59Z"),
     *                 @OA\Property(property="views", type="integer", example=0),
     *                 @OA\Property(property="proposal_count", type="integer", example=5),
     *                 @OA\Property(
     *                     property="project_skills",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="project_id", type="integer", example=1),
     *                         @OA\Property(property="skill_id", type="integer", example=2),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        try {
            $project = Project::with('projectSkills')->find($id);

            if (!$project) {
                return ResponseHelper::error([], "Project not found", 404);
            }

            return ResponseHelper::success($project, "Project details");
        } catch (\Exception $e) {
            return ResponseHelper::error([], "Failed to fetch project: " . $e->getMessage(), 500);
        }
    }




    /**
     * @OA\Get(
     *     path="/projects/companies/{id}",
     *     summary="Get all projects for a company",
     *     description="Retrieve all projects under a specific company, including project skills.",
     *     tags={"Projects"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Company ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of projects for the company",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all projects in company"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                     @OA\Property(property="description", type="string", example="Project description here"),
     *                     @OA\Property(property="duration_min", type="integer", example=1),
     *                     @OA\Property(property="duration_max", type="integer", example=6),
     *                     @OA\Property(property="duration_unit", type="string", example="months"),
     *                     @OA\Property(property="employer_id", type="integer", example=2),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="salary_min", type="number", example=500),
     *                     @OA\Property(property="salary_max", type="number", example=1500),
     *                     @OA\Property(property="currency", type="string", example="USD"),
     *                     @OA\Property(property="deadline", type="string", format="date-time", example="2025-10-01T23:59:59Z"),
     *                     @OA\Property(property="views", type="integer", example=20),
     *                     @OA\Property(property="proposal_count", type="integer", example=10),
     *                     @OA\Property(
     *                         property="project_skills",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="project_id", type="integer", example=1),
     *                             @OA\Property(property="skill_id", type="integer", example=3),
     *                             @OA\Property(property="created_at", type="string", format="date-time"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Company not found"),
     *             @OA\Property(property="code", type="integer", example=404),
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

        $jobs = Project::with('projectSkills')->where('company_id', $id)->get();
        return ResponseHelper::success(
            $jobs,
            "List of all projects in company"
        );
    }
}
