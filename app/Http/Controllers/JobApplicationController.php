<?php

namespace App\Http\Controllers;

use App\Helpers\JobMatchingHelper;
use App\Helpers\ResponseHelper;
use App\Models\Employer;
use App\Models\JobPost;
use App\Models\JobPostApplication;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use Validator;

class JobApplicationController extends Controller
{


    /**
     * @OA\Get(
     *     path="/jobs/recent",
     *     summary="Get recent jobs for the authenticated employer",
     *     description="Returns the 5 most recent jobs posted by the employer, including status, number of applications, and time ago since posted.",
     *     operationId="getRecentJobs",
     *     tags={"Employer"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of recent jobs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Recent Job postings"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="title", type="string", example="Frontend Developer"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="applications_count", type="integer", example=8),
     *                     @OA\Property(property="posted_time_ago", type="string", example="2 days ago")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - missing or invalid token",
     *         ref="#/components/responses/401"
     *     )
     * )
     */
    public function recent()
    {
        $authId = auth()->id();

        $employer = Employer::where('user_id', $authId)->first();

        if (!$employer) {
            return ResponseHelper::error([], 'Employer not found', 404);
        }

        $jobs = JobPost::where('employer_id', $employer->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->withCount('applications')
            ->get();

        $data = $jobs->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'applications_count' => $job->applications_count,
                'posted_time_ago' => $job->created_at->diffForHumans(),
            ];
        });


        return ResponseHelper::success($data, 'Recent Job postings');
    }




    /**
     * @OA\Get(
     *     path="/jobs/candidates",
     *     tags={"Employer"},
     *     summary="Get list of matching candidates for employer's jobs",
     *     description="Returns candidates (learners) that match the employer's posted jobs, including skills, points, certificates, and match score.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of matching candidates",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="List of matching candidates"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="job_post",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Software Engineer"),
     *                         @OA\Property(property="category_id", type="integer", example=3),
     *                         @OA\Property(property="job_type", type="string", example="full_time")
     *                     ),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                         @OA\Property(
     *                             property="skills",
     *                             type="array",
     *                             @OA\Items(type="string", example="PHP")
     *                         ),
     *                         @OA\Property(property="points", type="integer", example=120),
     *                         @OA\Property(property="certificate_count", type="integer", example=3)
     *                     ),
     *                     @OA\Property(
     *                         property="match",
     *                         type="object",
     *                         @OA\Property(property="status", type="string", example="Great Match"),
     *                         @OA\Property(property="value", type="number", format="float", example=87.5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     * )
     */


    public function candidates()
    {
        $authId = auth()->id();

        $employer = Employer::where('user_id', $authId)->first();

        if (!$employer) {
            return ResponseHelper::error([], 'Employer not found', 404);
        }

        // Get all jobs posted by employer
        $jobs = JobPost::where('employer_id', $employer->id)
            ->with('jobSkills') // make sure this relation exists
            ->get();

        if ($jobs->isEmpty()) {
            return ResponseHelper::success([], 'No jobs posted yet.');
        }

        // Get all learners (with skills, points, certificates)
        $learners = User::whereHas('roles', function ($q) {
            $q->where('name', 'learner');
        })
            ->with(['skills', 'points', 'certificates'])
            ->get();

        $results = [];

        foreach ($jobs as $jobPost) {
            foreach ($learners as $user) {
                // Calculate matching score
                $jobMatchingHelper = new JobMatchingHelper($jobPost, $user);
                $matchScore = $jobMatchingHelper->getMatchingStatus();

                // Compare skills
                $userSkillNames = $user->skills->pluck('name')->toArray();
                $jobSkillNames = $jobPost->jobskills->pluck('name')->toArray();
                $hasSkillMatch = count(array_intersect($userSkillNames, $jobSkillNames)) > 0;

                // Points logic
                $hasEnoughPoints = $user->points->sum('value') >= 50; // adjust threshold

                if ($hasSkillMatch && $hasEnoughPoints) {
                    $results[] = [
                        'job_post' => [
                            'id' => $jobPost->id,
                            'title' => $jobPost->title,
                            'category_id' => $jobPost->category_id,
                            'job_type' => $jobPost->job_type,
                        ],
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'skills' => $userSkillNames,
                            'points' => $user->points->sum('value'),
                            'certificate_count' => $user->certificates->count(),
                        ],
                        'match' => $matchScore,
                    ];
                }
            }
        }

        return ResponseHelper::success($results, 'List of matching candidates');
    }





    /**
     * @OA\Get(
     *     path="/jobs/applications",
     *     tags={"Employer"},
     *     summary="Get list of job applications",
     *     description="Returns all applications for jobs, including applicant details, job details, certificate count, and match score.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of job applications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="List of applicants"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="application_id", type="integer", example=15),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="first_name", type="string", example="Jane"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="email", type="string", example="jane@example.com"),
     *                         @OA\Property(property="certificate_count", type="integer", example=2)
     *                     ),
     *                     @OA\Property(
     *                         property="job_post",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="title", type="string", example="Frontend Developer"),
     *                         @OA\Property(property="description", type="string", example="Job description here..."),
     *                         @OA\Property(property="city", type="string", example="Nairobi"),
     *                         @OA\Property(property="country", type="string", example="Kenya"),
     *                         @OA\Property(property="type_id", type="integer", example=2),
     *                         @OA\Property(property="employer_id", type="integer", example=8),
     *                         @OA\Property(property="company_id", type="integer", example=3),
     *                         @OA\Property(property="category_id", type="integer", example=6),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="salary_min", type="number", example=500),
     *                         @OA\Property(property="salary_max", type="number", example=1200),
     *                         @OA\Property(property="currency", type="string", example="USD"),
     *                         @OA\Property(property="experience_level", type="string", example="mid"),
     *                         @OA\Property(property="education_level", type="string", example="bachelor"),
     *                         @OA\Property(property="is_remote", type="boolean", example=true),
     *                         @OA\Property(property="deadline", type="string", format="date-time", example="2025-09-30T23:59:59Z"),
     *                         @OA\Property(property="views", type="integer", example=120),
     *                         @OA\Property(property="applications_count", type="integer", example=45)
     *                     ),
     *                     @OA\Property(
     *                         property="match",
     *                         type="object",
     *                         @OA\Property(property="status", type="string", example="Good Match"),
     *                         @OA\Property(property="value", type="number", format="float", example=72.5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="Employer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     * )
     */

    public function applications()
    {
        $applications = JobPostApplication::with(['user.certificates', 'jobPost'])->get();

        $data = $applications->map(function ($application) {
            $user = $application->user;
            $jobPost = $application->jobPost;

            // Calculate matching score
            $jobMatchingHelper = new JobMatchingHelper($jobPost, $user);
            $matchScore = $jobMatchingHelper->getMatchingStatus();

            // Count user certificates
            $certificateCount = $user->certificates->count();

            return [
                'application_id' => $application->id,
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'certificate_count' => $certificateCount,
                ],
                'job_post' => [
                    'id' => $jobPost->id,
                    'title' => $jobPost->title,
                    'description' => $jobPost->description,
                    'city' => $jobPost->city,
                    'country' => $jobPost->country,
                    'type_id' => $jobPost->type_id,
                    'employer_id' => $jobPost->employer_id,
                    'company_id' => $jobPost->company_id,
                    'category_id' => $jobPost->category_id,
                    'status' => $jobPost->status,
                    'salary_min' => $jobPost->salary_min,
                    'salary_max' => $jobPost->salary_max,
                    'currency' => $jobPost->currency,
                    'experience_level' => $jobPost->experience_level,
                    'education_level' => $jobPost->education_level,
                    'is_remote' => $jobPost->is_remote,
                    'deadline' => $jobPost->deadline,
                    'views' => $jobPost->views,
                    'applications_count' => $jobPost->applications_count,
                ],
                'match' => $matchScore
            ];
        });

        return ResponseHelper::success($data, 'List of applicants');
    }




    /**
     * @OA\Post(
     *     path="/jobs/apply",
     *     tags={"Employer"},
     *     summary="Apply for a job",
     *     description="Learner applies for a job by submitting resume and cover letter files.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"job_post_id", "resume", "cover_letter"},
     *                 @OA\Property(
     *                     property="job_post_id",
     *                     type="integer",
     *                     example=12,
     *                     description="ID of the job post to apply for"
     *                 ),
     *                 @OA\Property(
     *                     property="resume",
     *                     type="string",
     *                     format="binary",
     *                     description="Resume file (PDF/DOC/DOCX)"
     *                 ),
     *                 @OA\Property(
     *                     property="cover_letter",
     *                     type="string",
     *                     format="binary",
     *                     description="Cover letter file (PDF/DOC/DOCX)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Application submitted successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="application_id", type="integer", example=45),
     *                 @OA\Property(property="job_post_id", type="integer", example=12),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="resume_path", type="string", example="resumes/123456_resume.pdf"),
     *                 @OA\Property(property="cover_letter", type="string", example="cover_letters/123456_cover.docx"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Already applied or validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You already applied for this job"),
     *             @OA\Property(property="code", type="integer", example=400),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         ref="#/components/responses/401"
     *     )
     * )
     */

    public function apply(Request $request)
    {
        $authId = auth()->id();


        $validator = Validator::make($request->all(), [
            'job_post_id' => 'required|exists:job_posts,id',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'cover_letter' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate', 422);
        }
        $validated = $validator->validated();

        $alreadyApplied = JobPostApplication::where('job_post_id', $validated['job_post_id'])
            ->where('user_id', $authId)
            ->exists();

        if ($alreadyApplied) {
            return ResponseHelper::error([], "You already applied for this job", 400);
        }

        DB::beginTransaction();
        try {

            $resumePath = $request->file('resume')->store('resumes', 'public');
            $coverLetterPath = $request->file('cover_letter')->store('cover_letters', 'public');

            $application = JobPostApplication::create([
                'job_post_id' => $validated['job_post_id'],
                'user_id' => $authId,
                'status' => 'pending',
                'resume_path' => $resumePath,
                'cover_letter' => $coverLetterPath,
                'updated_by' => $authId,
            ]);

            // $jobPost = JobPost::find($validated['job_post_id']);
            // $applicationCount = $jobPost->applications_count;
            // $jobPost->applications_count = $applicationCount + 1;
            // $jobPost->save();


            DB::commit();

            return ResponseHelper::success($application, "Application submitted successfully");

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], 'Error : ' . $e->getMessage());
        }
    }



}
