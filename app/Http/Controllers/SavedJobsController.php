<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\SavedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SavedJobsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/saved-jobs",
     *     tags={"Saved Jobs"},
     *     summary="Get all saved jobs for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all saved jobs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all saved jobs."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=2),
     *                     @OA\Property(property="job_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-09T07:45:53.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-09T07:45:53.000000Z"),
     *                     @OA\Property(
     *                         property="job",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Full Stack Developer"),
     *                         @OA\Property(property="description", type="string", example="Get a Job for free my dear."),
     *                         @OA\Property(property="city", type="string", example="Dar es Salaam"),
     *                         @OA\Property(property="country", type="string", example="Tanzania"),
     *                         @OA\Property(property="type_id", type="integer", example=1),
     *                         @OA\Property(property="employer_id", type="integer", example=1),
     *                         @OA\Property(property="company_id", type="integer", example=1),
     *                         @OA\Property(property="category_id", type="integer", nullable=true, example=null),
     *                         @OA\Property(property="status", type="string", example="published"),
     *                         @OA\Property(property="salary_min", type="number", nullable=true, example=null),
     *                         @OA\Property(property="salary_max", type="number", nullable=true, example=null),
     *                         @OA\Property(property="currency", type="string", example="TZS"),
     *                         @OA\Property(property="experience_level", type="string", nullable=true, example=null),
     *                         @OA\Property(property="education_level", type="string", nullable=true, example=null),
     *                         @OA\Property(property="is_remote", type="boolean", example=false),
     *                         @OA\Property(property="deadline", type="string", nullable=true, example=null),
     *                         @OA\Property(property="views", type="integer", example=0),
     *                         @OA\Property(property="applications_count", type="integer", example=0),
     *                         @OA\Property(property="slug", type="string", example="full-stack-developer-68c9c6c198d8c"),
     *                         @OA\Property(property="deleted_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", example="2025-09-16T20:21:21.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2025-09-17T04:36:58.000000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         ref="#/components/responses/401"
     *     )
     * )
     */

    public function index()
    {
        $authId = auth()->id();
        $savedJobs = SavedJob::where('user_id', $authId)
            ->with('job')
            ->get();

        return ResponseHelper::success($savedJobs, 'List of all saved jobs.');
    }


    /**
     * @OA\Post(
     *     path="/saved-jobs",
     *     tags={"Saved Jobs"},
     *     summary="Save or remove a job (toggle)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"job_id"},
     *             @OA\Property(property="job_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Saved post updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Saved post updated."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="action", type="string", example="added")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         ref="#/components/responses/422"
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         ref="#/components/responses/401"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer|exists:job_posts,id'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        $userId = auth()->id();
        $jobPostId = $request->job_id;

        $savedPost = SavedJob::where('user_id', $userId)
            ->where('job_id', $jobPostId)
            ->first();

        if ($savedPost) {
            $savedPost->delete();
            $action = 'removed';
        } else {
            SavedJob::create([
                'user_id' => $userId,
                'job_id' => $jobPostId,
            ]);
            $action = 'added';
        }

        return ResponseHelper::success(
            ['action' => $action],
            'Saved post updated.'
        );
    }
}
