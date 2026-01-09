<?php

namespace App\Http\Controllers;

use App\Helpers\JobMatchingHelper;
use App\Helpers\ResponseHelper;
use App\Models\JobPost;
use Illuminate\Http\Request;

class JobMatchController extends Controller
{


    /**
     * @OA\Get(
     *     path="/jobs/matches",
     *     summary="Get matching jobs for the logged-in learner",
     *     description="Returns a list of jobs with their match status and score based on the learner's aptitude results.",
     *     tags={"Employer"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of matching jobs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Matching jobs"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="job",
     *                         type="object",
     *                         description="The job post object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="title", type="string", example="Frontend Developer"),
     *                         @OA\Property(property="category_id", type="integer", example=3),
     *                         @OA\Property(property="job_type", type="string", example="remote"),
     *                         @OA\Property(property="company_id", type="integer", example=7)
     *                     ),
     *                     @OA\Property(
     *                         property="match",
     *                         type="object",
     *                         description="Match results from JobMatchingHelper",
     *                         @OA\Property(property="status", type="string", example="Great Match"),
     *                         @OA\Property(property="value", type="number", format="float", example=87.5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         ref="#/components/responses/401"
     *     )
     * )
     */

    public function index(Request $request)
    {
        $user = auth()->user();

        $jobs = JobPost::all();

        $data = $jobs->map(function ($jobPost) use ($user) {
   
            $jobMatchingHelper = new JobMatchingHelper($jobPost, $user);
            $matchScore = $jobMatchingHelper->getMatchingStatus(); 

            return [
                'job' => $jobPost,
                'match_score' => $matchScore,
            ];
        });

        return ResponseHelper::success($data, "Matching jobs");
    }

}
