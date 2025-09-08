<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Project;
use App\Models\ProjectActivity;
use DB;
use Illuminate\Http\Request;
use Validator;

class ProjectActivityController extends Controller
{

    /**
     * @OA\Patch(
     *     path="/projects/{id}/review/start",
     *     operationId="startProjectReview",
     *     tags={"Projects"},
     *     summary="Set a project to 'in_review' status",
     *     description="Allows a project to enter the review phase if it is currently active.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project to start review for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project now in review",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Project now in review"),
     *             @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid project status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only active projects can be reviewed."),
     *              @OA\Property(property="code", type="integer", example=400),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project not found."),
     *              @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     * )
     */

    public function reviewStart(string $id)
    {

        $project = Project::find($id);

        if (!$project) {
            return ResponseHelper::error([], "Project not found.", 404);
        }

        if ($project->status == 'in_review') {
            return ResponseHelper::error([], 'Project already in review', 400);
        }

        if ($project->status !== 'active') {
            return ResponseHelper::error([], 'Only active projects can be reviewed.', 400);
        }

        $project->status = 'in_review';
        $project->save();
        return ResponseHelper::success([], 'Project now in review', 200);


    }




    /**
     * @OA\Post(
     *     path="/projects/{id}/review/submit",
     *     operationId="submitProjectForReview",
     *     tags={"Projects"},
     *     summary="Submit a project for review and assign it to a learner",
     *     description="Updates the project status to 'working' and creates a new project activity for the selected learner.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project to submit for review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"learner_id"},
     *             @OA\Property(property="learner_id", type="integer", description="ID of the learner to assign the project to")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project submitted for review successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Project submitted for review."),
     *              @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="project_id", type="integer", example=1),
     *                 @OA\Property(property="company_id", type="integer", example=1),
     *                 @OA\Property(property="learner_id", type="integer", example=2),
     *                 @OA\Property(property="status", type="string", example="assigned"),
     *                 @OA\Property(property="started_at", type="string", format="date-time", example="2025-09-08T12:00:00Z"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project not found."),
     *              @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         ref="#/components/responses/500"
     *     )
     * )
     */

    public function reviewSubmit(Request $request, string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return ResponseHelper::error([], "Project not found.", 404);
        }

        $validator = Validator::make($request->all(), [
            'learner_id' => 'required|exists:users,id'
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {
            DB::beginTransaction();

            // 1. Update project status to "working"
            $project->status = 'working';
            $project->save();

            // 2. Create new project activity
            $activity = new ProjectActivity();
            $activity->project_id = $project->id;
            $activity->company_id = $project->company_id; // assuming project has company_id
            $activity->learner_id = $request->learner_id;
            $activity->status = 'assigned';
            $activity->started_at = now();
            $activity->save();

            DB::commit();

            return ResponseHelper::success($activity, 'Project submitted for review.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error(['error' => $e->getMessage()], 'Something went wrong', 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/projects/{id}/start",
     *     operationId="learnerStartProject",
     *     tags={"Projects"},
     *     summary="Start a project for the authenticated learner",
     *     description="Marks the project activity as in progress and updates the project status to 'working'.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project to start",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project started successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="project_id", type="integer", example=1),
     *                 @OA\Property(property="learner_id", type="integer", example=2),
     *                 @OA\Property(property="status", type="string", example="in_progress"),
     *                 @OA\Property(property="started_at", type="string", format="date-time", example="2025-09-08T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Project in progress."),
     *              @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found or no active assignment",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project not found."),
     *              @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         ref="#/components/responses/500"
     *     )
     * )
     */

    public function learnerStart(Request $request, string $projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return ResponseHelper::error([], "Project not found.", 404);
        }

        $learnerId = auth()->user()->id;

        $activity = ProjectActivity::where('project_id', $project->id)
            ->where('learner_id', $learnerId)
            ->whereIn('status', ['assigned']) // only active
            ->first();

        if (!$activity) {
            return ResponseHelper::error([], "No active project assignment found.", 404);
        }

        try {
            DB::beginTransaction();

            $activity->status = 'in_progress';
            $activity->started_at = now();
            $activity->save();

            $project->status = 'working';
            $project->save();

            DB::commit();

            return ResponseHelper::success($activity, 'Project in progress.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error(['error' => $e->getMessage()], 'Failed to start project', 500);
        }


    }


    /**
     * @OA\Post(
     *     path="/projects/{id}/complete",
     *     operationId="learnerCompleteProject",
     *     tags={"Projects"},
     *     summary="Submit a project as completed for employer review",
     *     description="Marks the project activity as submitted, records learner notes, and updates the project status to 'in_review'.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project to complete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", description="Optional notes from the learner")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project submitted for employer review successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="project_id", type="integer", example=1),
     *                 @OA\Property(property="learner_id", type="integer", example=2),
     *                 @OA\Property(property="status", type="string", example="submitted"),
     *                 @OA\Property(property="submitted_at", type="string", format="date-time", example="2025-09-08T14:00:00Z"),
     *                 @OA\Property(property="learner_notes", type="string", example="Completed all tasks")
     *             ),
     *             @OA\Property(property="message", type="string", example="Project submitted for employer review."),
     *              @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found or no active assignment",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No active project assignment found."),
     *              @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         ref="#/components/responses/500"
     *     )
     * )
     */

    public function learnerComplete(Request $request, string $projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return ResponseHelper::error([], "Project not found.", 404);
        }

        // Get the current learner (if using auth)
        $learnerId = auth()->id();

        // Find the project activity for this learner
        $activity = ProjectActivity::where('project_id', $project->id)
            ->where('learner_id', $learnerId)
            ->whereIn('status', ['assigned', 'in_progress']) // only active
            ->first();

        if (!$activity) {
            return ResponseHelper::error([], "No active project assignment found.", 404);
        }

        try {
            DB::beginTransaction();

            // Update activity
            $activity->status = 'submitted';
            $activity->submitted_at = now();
            $activity->learner_notes = $request->input('notes', null);
            $activity->save();

            // Optionally also update project status to "in_review"
            $project->status = 'in_review';
            $project->save();

            DB::commit();

            return ResponseHelper::success($activity, 'Project submitted for employer review.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error(['error' => $e->getMessage()], 'Failed to submit project', 500);
        }
    }

}
