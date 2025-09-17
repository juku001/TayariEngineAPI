<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\JobPostType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Validator;

class JobTypeController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware(['auth:sanctum', 'user.type:super_admin'], only: ['store', 'update', 'destroy'])
        ];
    }




    /**
     * @OA\Get(
     *     path="/job-types",
     *     tags={"Job Types"},
     *     summary="Get list of all job types",
     *     @OA\Response(
     *         response=200,
     *         description="Job type list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job Type list"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Full-time"),
     *                     @OA\Property(property="slug", type="string", example="full-time"),
     *                     @OA\Property(property="description", type="string", example="Full-time employment"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $jobTypes = JobPostType::all();
        return ResponseHelper::success($jobTypes, 'Job Type list');
    }

    /**
     * @OA\Post(
     *     path="/job-types",
     *     tags={"Job Types"},
     *     summary="Create a new job type",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Internship"),
     *             @OA\Property(property="description", type="string", example="Temporary work for interns")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Job type created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job Type added successful"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Internship"),
     *                 @OA\Property(property="slug", type="string", example="internship"),
     *                 @OA\Property(property="description", type="string", example="Temporary work for interns"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate', 422);
        }
        $data = $validator->validated();

        $job = JobPostType::create($data);

        return ResponseHelper::success($job, 'Job Type added successful', 201);
    }

    /**
     * @OA\Get(
     *     path="/job-types/{id}",
     *     tags={"Job Types"},
     *     summary="Get details of a specific job type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the job type",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job type details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job Type details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Full-time"),
     *                 @OA\Property(property="slug", type="string", example="full-time"),
     *                 @OA\Property(property="description", type="string", example="Full-time employment")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job type not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Job Type not found."),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $job = JobPostType::find($id);
        if (!$job) {
            return ResponseHelper::error([], 'Job Type not found.', 404);
        }

        return ResponseHelper::success($job, 'Job Type details', 200);
    }

    /**
     * @OA\Put(
     *     path="/job-types/{id}",
     *     tags={"Job Types"},
     *     summary="Update an existing job type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the job type",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Part-time"),
     *             @OA\Property(property="description", type="string", example="Flexible part-time employment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job Type updated successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Part-time"),
     *                 @OA\Property(property="slug", type="string", example="part-time"),
     *                 @OA\Property(property="description", type="string", example="Flexible part-time employment")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job type not found"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $job = JobPostType::find($id);
        if (!$job) {
            return ResponseHelper::error([], 'Job Type not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate', 422);
        }

        $job->update($validator->validated());

        return ResponseHelper::success($job, 'Job Type updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/job-types/{id}",
     *     tags={"Job Types"},
     *     summary="Delete a job type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the job type",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job type deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Job Type deleted successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job type not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $job = JobPostType::find($id);
        if (!$job) {
            return ResponseHelper::error([], 'Job Type not found.', 404);
        }

        $job->delete();
        return ResponseHelper::success([], 'Job Type deleted successfully');
    }
}
