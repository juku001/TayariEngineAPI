<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Employer;
use App\Models\Project;
use App\Models\ProjectProposal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{



    /**
     * @OA\Get(
     *     path="/projects/proposals",
     *     tags={"Projects"},
     *     summary="Get list of proposals submitted by the logged-in freelancer",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of proposals retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of proposals"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="project_id", type="integer", example=1),
     *                     @OA\Property(property="freelancer_id", type="integer", example=7),
     *                     @OA\Property(property="amount", type="number", format="float", example=500),
     *                     @OA\Property(property="experience", type="integer", example=2),
     *                     @OA\Property(property="experience_unit", type="string", example="years"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="message", type="string", example="Something something"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T22:19:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T22:19:27.000000Z"),
     *                     @OA\Property(
     *                         property="project",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                         @OA\Property(property="description", type="string", example="Get a Job for free my dear."),
     *                         @OA\Property(property="duration_min", type="integer", example=1),
     *                         @OA\Property(property="duration_max", type="integer", example=null),
     *                         @OA\Property(property="duration_unit", type="string", example="months"),
     *                         @OA\Property(property="employer_id", type="integer", example=1),
     *                         @OA\Property(property="company_id", type="integer", example=1),
     *                         @OA\Property(property="status", type="string", example="in_review"),
     *                         @OA\Property(property="salary_min", type="number", format="float", example=null),
     *                         @OA\Property(property="salary_max", type="number", format="float", example=null),
     *                         @OA\Property(property="currency", type="string", example="TZS"),
     *                         @OA\Property(property="deadline", type="string", format="date-time", example=null),
     *                         @OA\Property(property="views", type="integer", example=0),
     *                         @OA\Property(property="proposal_count", type="integer", example=0),
     *                         @OA\Property(property="slug", type="string", example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T21:28:31.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T21:28:31.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Unauthorized"),
     *           @OA\Property(property="code", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Failed to fetch proposals"),
     *           @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */


    public function index()
    {
        $authId = auth()->user()->id;
        try {
            $proposals = ProjectProposal::with('project')->where(
                'freelancer_id',
                $authId
            )->get();
            return ResponseHelper::success($proposals, 'List of proposals');
        } catch (Exception $e) {
            return ResponseHelper::error([], "Failed to fetch proposals: " . $e->getMessage(), 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/projects/proposals",
     *     tags={"Projects"},
     *     summary="Submit a proposal for a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="experience", type="integer", example=3),
     *             @OA\Property(property="experience_unit", type="string", example="months"),
     *             @OA\Property(property="message", type="string", example="I am interested in this project."),
     *             @OA\Property(property="amount", type="number", format="float", example=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proposal sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proposal sent successfully"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *               property="data",
     *               type="object",
     *               @OA\Property(property="freelancer_id", type="integer", example=7),
     *               @OA\Property(property="project_id", type="integer", example=1),
     *               @OA\Property(property="experience", type="integer", example=2),
     *               @OA\Property(property="experience_unit", type="string", example="years"),
     *               @OA\Property(property="message", type="string", example="Something something"),
     *               @OA\Property(property="amount", type="number", format="float", example=null),
     *               @OA\Property(property="created_at", type="string", example="2025-09-01T22:19:27.000000Z"),
     *               @OA\Property(property="updated_at", type="string", example="2025-09-01T22:19:27.000000Z"),
     *               @OA\Property(property="id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", ref="#/components/responses/401"),
     *     @OA\Response(response=422, description="Validation error", ref="#/components/responses/422"),
     *     @OA\Response(response=500, description="Server error", ref="#/components/responses/500")
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'experience' => 'nullable|integer|required_with:experience_unit',
            'experience_unit' => 'nullable|in:hours,weeks,months,years|required_with:experience',
            'message' => 'nullable|string',
            'amount' => 'nullable|numeric'
        ]);


        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 422);
        }

        $authId = auth()->id();

        $data = [
            'freelancer_id' => $authId,
            'project_id' => $request->project_id,
            'experience' => $request->experience,
            'experience_unit' => $request->experience_unit,
            'message' => $request->message,
            'amount' => $request->amount
        ];

        try {
            $proposal = ProjectProposal::create($data);
            return ResponseHelper::success($proposal, "Proposal sent successfully", 201);
        } catch (Exception $e) {
            return ResponseHelper::error([], "Error: " . $e->getMessage(), 500);
        }
    }




    /**
     * @OA\Patch(
     *     path="/projects/proposals/feedback/{proposalId}",
     *     tags={"Projects"},
     *     summary="Employer feedback on a proposal (accept or deny)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="proposalId",
     *         in="path",
     *         description="ID of the proposal to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="Proposal status update",
     *                 enum={"accepted","denied"},
     *                 example="accepted"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposal status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proposal status updated successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="project_id", type="integer", example=1),
     *                 @OA\Property(property="freelancer_id", type="integer", example=7),
     *                 @OA\Property(property="amount", type="number", format="float", example=500),
     *                 @OA\Property(property="experience", type="integer", example=2),
     *                 @OA\Property(property="experience_unit", type="string", example="years"),
     *                 @OA\Property(property="status", type="string", example="accepted"),
     *                 @OA\Property(property="message", type="string", example="Something something"),
     *                 @OA\Property(property="employer_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T22:19:27.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T22:19:27.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Unauthorized"),
     *           @OA\Property(property="code", type="integer", example=401),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden: Not the employer",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="You are not allowed to perform this action"),
     *           @OA\Property(property="code", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proposal not found",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Proposal not found"),
     *           @OA\Property(property="code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Server error"),
     *           @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */


    public function feedback(Request $request, $proposalId)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,denied'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 422);
        }

        // Find the proposal
        $proposal = ProjectProposal::find($proposalId);
        if (!$proposal) {
            return ResponseHelper::error([], "Proposal not found", 404);
        }

        // Ensure logged-in user is the employer of the project
        $employerId = auth()->user()->id;
        if ($proposal->project->employer_id !== $employerId) {
            return ResponseHelper::error([], "Unauthorized: You are not the employer of this project", 403);
        }

        try {
            // Update the proposal
            $proposal->status = $request->status;
            $proposal->employer_id = $employerId;
            $proposal->save();

            return ResponseHelper::success($proposal, "Proposal status updated successfully");
        } catch (Exception $e) {
            return ResponseHelper::error([], "Failed to update proposal: " . $e->getMessage(), 500);
        }
    }







    /**
     * @OA\Get(
     *     path="/projects/proposals/employer",
     *     operationId="getEmployerProjectProposals",
     *     tags={"Projects"},
     *     summary="Get employer projects with filtered proposals",
     *     description="Returns a list of projects belonging to the authenticated employer's company, with project proposals optionally filtered by status.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter project proposals by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "shortlist", "accepted", "denied"},
     *             example="shortlist"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of projects and filtered proposals",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of projects and filtered proposals"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Website Redesign"),
     *                     @OA\Property(
     *                         property="project_proposals",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="status", type="string", example="shortlist"),
     *                             @OA\Property(property="created_at", type="string", format="date-time")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Unauthorized"),
     *           @OA\Property(property="code", type="integer", example=401),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden: Not the employer",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="You are not allowed to perform this action"),
     *           @OA\Property(property="code", type="integer", example=403)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer Company not found",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Employer Company not found"),
     *           @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */


    public function employer(Request $request)
    {
        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer company not found', 404);
        }

        $status = $request->query('status');
        $projects = Project::with([
            'projectProposals' => function ($query) use ($status) {
                if ($status) {
                    $query->where('status', $status);
                }
            }
        ])
            ->where('company_id', $employer->company_id)
            ->get();

        return ResponseHelper::success($projects, 'List of projects and filtered proposals');
    }



    /**
     * @OA\Get(
     *     path="/projects/proposals/project/{id}",
     *     operationId="getProjectProposalsByProject",
     *     tags={"Projects"},
     *     summary="Get proposals for a specific project",
     *     description="Returns all proposals for a specific project owned by the authenticated employer's company. Proposals can be filtered by status.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=12
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter project proposals by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "shortlist", "accepted", "denied"},
     *             example="shortlist"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Project proposals retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Project proposals retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="title", type="string", example="Mobile App Development"),
     *                 @OA\Property(
     *                     property="project_proposals",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=45),
     *                         @OA\Property(property="status", type="string", example="shortlist"),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
    *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Unauthorized"),
     *           @OA\Property(property="code", type="integer", example=401),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden: Not the employer",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="You are not allowed to perform this action"),
     *           @OA\Property(property="code", type="integer", example=403)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer Company not found",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=false),
     *           @OA\Property(property="message", type="string", example="Employer Company not found"),
     *           @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function byProject(Request $request, $id)
    {
        $authId = auth()->user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer || !$employer->company_id) {
            return ResponseHelper::error([], 'Employer has no company', 404);
        }

        $status = $request->query('status');

        $project = Project::with([
            'projectProposals' => function ($query) use ($status) {
                if ($status) {
                    $query->where('status', $status);
                }
            }
        ])
            ->where('company_id', $employer->company_id)
            ->where('id', $id)
            ->first();

        if (!$project) {
            return ResponseHelper::error([], 'Project not found', 404);
        }

        return ResponseHelper::success($project, 'Project proposals retrieved successfully');
    }






}
