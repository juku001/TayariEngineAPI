<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\JobPostApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ManageApplicationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/applications/{id}/view",
     *     tags={"Employer"},
     *     summary="Mark application as reviewed and get applicant details",
     *     description="Marks a specific job application as reviewed and returns details about the applicant, including certificates, skills, points, cover letter, and resume.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the job application",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Application marked as reviewed and details retrieved."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="application", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", example="reviewed"),
     *                     @OA\Property(property="cover_letter", type="string", example="https://storage.example.com/cover_letters/123.pdf"),
     *                     @OA\Property(property="resume", type="string", example="https://storage.example.com/resumes/123.pdf"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-17T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-17T12:10:00Z")
     *                 ),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="John"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="points", type="integer", example=120),
     *                     @OA\Property(property="cover_letter", type="string", example="https://storage.example.com/cover_letters/123.pdf"),
     *                     @OA\Property(property="resume", type="string", example="https://storage.example.com/resumes/123.pdf"),
     *                     @OA\Property(
     *                         property="certificates",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Laravel Mastery"),
     *                             @OA\Property(property="issued_at", type="string", format="date", example="2025-05-20"),
     *                             @OA\Property(property="file_path", type="string", example="https://storage.example.com/certificates/123.pdf")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="skills",
     *                         type="array",
     *                         @OA\Items(type="string", example="PHP")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Application not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */


    public function show($id)
    {
        $application = JobPostApplication::with(['user', 'jobPost'])->findOrFail($id);

        // Mark as reviewed
        $application->status = 'reviewed';
        $application->save();

        // Load additional user info
        $user = $application->user;
        $user->load(['certificates', 'skills']); // Make sure the User model has these relationships

        $data = [
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
                'cover_letter' => $application->cover_letter,
                'resume' => $application->resume,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,
            ],
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'points' => $user->points()->sum('points'), // Assuming points() is a relationship
                'certificates' => $user->certificates->map(function ($cert) {
                    return [
                        'id' => $cert->id,
                        'name' => $cert->name,
                        'issued_at' => $cert->issued_at,
                        'file_path' => $cert->file_path,
                    ];
                }),
                'skills' => $user->skills->pluck('name'),
            ]
        ];

        return ResponseHelper::success($data, 'Application marked as reviewed and details retrieved.');
    }
    /**
     * Reject an applicant.
     */

    /**
     * @OA\Post(
     *     path="/applications/{id}/reject",
     *     tags={"Employer"},
     *     summary="Reject a job application",
     *     description="Change the status of a job application to rejected.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the job application",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Application rejected successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Application not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $application = JobPostApplication::findOrFail($id);
        $application->status = 'rejected';
        $application->save();

        return ResponseHelper::success([], 'Application rejected successfully.');
    }

    /**
     * Shortlist an applicant and send an email.
     */

    /**
     * @OA\Post(
     *     path="/applications/{id}/invite",
     *     tags={"Employer"},
     *     summary="Shortlist a job applicant",
     *     description="Change the status of a job application to shortlisted and send an email notification to the applicant.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the job application",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Applicant shortlisted and notified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Applicant shortlisted and notified via email"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Application not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function store($id)
    {
        $application = JobPostApplication::with('user')->findOrFail($id);
        $application->status = 'shortlisted';
        $application->save();

        // send email notification



        Mail::raw("Congratulations! You have been shortlisted for the job: {$application->jobPost->title}.", function ($message) use ($application) {
            $message->to($application->user->email)
                ->subject('Job Application Shortlisted');
        });


        return ResponseHelper::success([], 'Applicant shortlisted and notified via email.');
    }

    /**
     * Accept an applicant and send an email.
     */



    /**
     * @OA\Post(
     *     path="/applications/{id}/accept",
     *     tags={"Employer"},
     *     summary="Accept a job applicant",
     *     description="Change the status of a job application to accepted and send an email notification to the applicant.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the job application",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Applicant accepted and notified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Applicant accepted and notified via email"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Application not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function accept($id)
    {
        $application = JobPostApplication::with('user')->findOrFail($id);
        $application->status = 'accepted';
        $application->save();

        // send email notification

        Mail::raw(
            "Congratulations! Your application for the job '{$application->jobPost->title}' has been accepted. Please check your dashboard for next steps.",
            function ($message) use ($application) {
                $message->to($application->user->email)
                    ->subject('Job Application Accepted');
            }
        );


        return ResponseHelper::success([], 'Applicant accepted and notified via email.');
    }
}
