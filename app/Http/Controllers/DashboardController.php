<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Employer;
use App\Models\EmployerTeamMember;
use App\Models\Enrollment;
use App\Models\JobPost;
use App\Models\JobPostApplication;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Models\UserBadge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class DashboardController extends Controller
{





    /**
     * @OA\Get(
     *     path="/dashboard/admin",
     *     tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *     summary="Get admin dashboard stats and system status",
     *     description="Provides an overview of users, courses, jobs, and system dependencies (database, mail, queue, uptime, error rate). Useful for monitoring application health and high-level admin insights.",
     *     @OA\Response(
     *         response=200,
     *         description="Admin dashboard details",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="boolean", example=true),
     *           @OA\Property(property="message", type="string", example="Admin dashboard details"),
     *           @OA\Property(property="code", type="integer", example=200),
     *           @OA\Property(
     *             property="data",
     *             type="object",
     *             @OA\Property(property="users", type="object",
     *                 @OA\Property(property="total", type="integer", example=152),
     *                 @OA\Property(property="learners", type="integer", example=120),
     *                 @OA\Property(property="instructors", type="integer", example=20),
     *                 @OA\Property(property="employers", type="integer", example=12)
     *             ),
     *             @OA\Property(property="courses", type="object",
     *                 @OA\Property(property="total", type="integer", example=85),
     *                 @OA\Property(property="published", type="integer", example=60),
     *                 @OA\Property(property="draft", type="integer", example=25)
     *             ),
     *             @OA\Property(property="jobs", type="integer", example=34),
     *             @OA\Property(property="sys_update", type="object",
     *                 @OA\Property(property="status", type="string", example="operational"),
     *                 @OA\Property(property="uptime", type="string", example="up 5 days, 2 hours"),
     *                 @OA\Property(property="last_check", type="string", format="date-time", example="2025-08-29T14:35:12Z"),
     *                 @OA\Property(property="error_rate", type="string", example="2 errors today"),
     *                 @OA\Property(property="response_time", type="string", example="120ms"),
     *                 @OA\Property(property="dependencies", type="object",
     *                     @OA\Property(property="database", type="string", example="up"),
     *                     @OA\Property(property="mail", type="string", example="up"),
     *                     @OA\Property(property="queue", type="string", example="up")
     *                 )
     *             )
     *           )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *       response=401,
     *       description="Unauthorized",
     *       ref="#/components/responses/401"
     *     )
     * )
     */

    public function admin()
    {

        $start = microtime(true);


        try {
            DB::connection()->getPdo();
            $dbStatus = "up";
        } catch (\Exception $e) {
            $dbStatus = "down";
        }


        try {
            $queueStatus = Queue::size() >= 0 ? "up" : "down";
        } catch (\Exception $e) {
            $queueStatus = "down";
        }


        try {
            $transport = Mail::mailer()->getSymfonyTransport();
            if (method_exists($transport, 'start')) {
                $transport->start();
                $transport->stop();
            }
            $mailStatus = "up";
        } catch (\Exception $e) {
            $mailStatus = "down";
        }




        $errorLines = @preg_grep('/ERROR/', file(storage_path('logs/laravel.log'))) ?? [];
        $errorRate = count($errorLines) . " errors today";


        $uptime = @shell_exec("uptime -p") ?? "unknown";


        $responseTime = round((microtime(true) - $start) * 1000) . "ms";


        $status = ($dbStatus === "up" && $queueStatus === "up" && $mailStatus === "up")
            ? "operational"
            : "degraded";


        if ($status === "operational") {
            $description = "All systems running normally";
        } else {
            $downSystems = [];
            if ($dbStatus === "down")
                $downSystems[] = "Database";
            if ($queueStatus === "down")
                $downSystems[] = "Queue";
            if ($mailStatus === "down")
                $downSystems[] = "Mail";

            $description = "Issues detected with: " . implode(", ", $downSystems);
        }



        $systemStatus = [
            "status" => $status,
            "uptime" => trim($uptime),
            "description" => $description,
            "last_check" => now()->toDateTimeString(),
            "error_rate" => $errorRate,
            "response_time" => $responseTime,
            "dependencies" => [
                "database" => $dbStatus,
                "mail" => $mailStatus,
                "queue" => $queueStatus
            ]
        ];

        $query = User::query();
        $data = [
            "users" => [
                "total" => $query->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['super_admin', 'admin']);
                })->count(),
                "learners" => $query->whereHas('roles', function ($q) {
                    $q->where('name', 'learner');
                })->count(),

                "instructors" => $query->whereHas('roles', function ($q) {
                    $q->where('name', 'instructor');
                })->count(),

                "employers" => $query->whereHas('roles', function ($q) {
                    $q->where('name', 'employer');
                })->count(),

            ],

            "courses" => [
                "total" => Course::count(),
                "published" => Course::where('status', 'published')->count(),
                "draft" => Course::where('status', 'draft')->count()
            ],
            "jobs" => JobPost::count(),
            "sys_update" => $systemStatus
        ];

        return ResponseHelper::success(
            $data,
            "Admin dashboard details"
        );
    }







    /**
     * @OA\Get(
     *     path="/dashboard/employer",
     *     tags={"Dashboard"},
     *     summary="Get employer dashboard statistics",
     *     description="Returns a summary of active jobs, applications, team members, users in training, certificates, and their changes over time.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard stats retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Employer stats dashboard"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="active", type="integer", example=12, description="Number of active job posts"),
     *                 @OA\Property(property="active_change", type="integer", example=2, description="Change in active jobs compared to last week"),
     *                 @OA\Property(property="applications", type="integer", example=35, description="Total job applications received"),
     *                 @OA\Property(property="applications_change", type="integer", example=-1, description="Change in applications compared to last week"),
     *                 @OA\Property(
     *                     property="team_members",
     *                     type="object",
     *                     @OA\Property(property="count", type="integer", example=25, description="Total team members"),
     *                     @OA\Property(property="in_training", type="integer", example=10, description="Number of users currently in training")
     *                 ),
     *                 @OA\Property(property="certificates", type="integer", example=15, description="Total certificates awarded"),
     *                 @OA\Property(property="certificates_change", type="integer", example=3, description="Change in certificates compared to last month")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Something went wrong"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */


    public function employer()
    {
        $authId = auth()->id();
        $employer = Employer::where('user_id', $authId)->firstOrFail();
        $companyId = $employer->company_id;

        $now = now();
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonth();

        // Active jobs
        $active = JobPost::where('company_id', $companyId)
            ->where('status', 'published')
            ->count();

        $activeLastWeek = JobPost::where('company_id', $companyId)
            ->where('status', 'published')
            ->whereBetween('created_at', [$lastWeek, $now])
            ->count();

        $activeChange = $active - $activeLastWeek;

        // Applications
        $applications = JobPostApplication::whereHas('jobPost', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count();

        $applicationsLastWeek = JobPostApplication::whereHas('jobPost', function ($q) use ($companyId, $lastWeek, $now) {
            $q->where('company_id', $companyId)
                ->whereBetween('created_at', [$lastWeek, $now]);
        })->count();

        $applicationsChange = $applications - $applicationsLastWeek;

        // Team members
        $teamMembers = DB::table('employer_team_members as etm')
            ->join('teams as t', 'etm.team_id', '=', 't.id')
            ->where('t.company_id', $companyId)
            ->distinct('etm.user_id')
            ->count('etm.user_id');

        // Users in training
        $training = User::query()
            ->select('users.*')
            ->join('enrollments as e', 'users.id', '=', 'e.user_id')
            ->join('teams as t', 'e.team_id', '=', 't.id')
            ->where('t.company_id', $companyId)
            ->where('e.status', 'active')
            ->distinct()
            ->count();

        // Certificates
        $certificates = User::query()
            ->whereHas('employerTeamMembers.team', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereHas('certificates')
            ->with('certificates')
            ->count();

        $certificatesLastMonth = User::query()
            ->whereHas('employerTeamMembers.team', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereHas('certificates', function ($q) use ($lastMonth, $now) {
                $q->whereBetween('created_at', [$lastMonth, $now]);
            })
            ->count();

        $certificatesChange = $certificates - $certificatesLastMonth;

        $data = [
            "active" => $active,
            'active_change' => $activeChange,
            "applications" => $applications,
            'applications_change' => $applicationsChange,
            "team_members" => [
                "count" => $teamMembers,
                "in_training" => $training
            ],
            "certificates" => $certificates,
            "certificates_change" => $certificatesChange,
        ];

        return ResponseHelper::success($data, 'Employer stats dashboard');
    }




    /**
     * @OA\Get(
     *     path="/dashboard/employer/teams",
     *     tags={"Dashboard"},
     *     summary="Get employer team dashboard stats",
     *     description="Returns statistics about team invitations for the employer’s company (total, pending, active).",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Team dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Team Dashboard Stats"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=12, description="Total invitations"),
     *                 @OA\Property(property="pending", type="integer", example=5, description="Pending invitations"),
     *                 @OA\Property(property="active", type="integer", example=7, description="Accepted/active invitations")
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

    public function teams()
    {

        $authId = Auth::user()->id;

        $employer = Employer::where('user_id', $authId)->first();
        $companyId = $employer->company_id;

   

        $teamMembers = EmployerTeamMember::query()
            ->whereHas('team', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->with(['team', 'user']);

        $total = $teamMembers->count();
        $query = TeamInvitation::where('company_id', $employer->company_id);

        $pending = $query->where('status', 'pending')->count();
        $active = $query->where('status', 'accepted')->count();


        $data = [
            'total' => $total ?? 0,
            'pending' => $pending ?? 0,
            'active' => $active ?? 0
        ];

        return ResponseHelper::success($data, 'Team Dashboard Stats');
    }


    /**
     * @OA\Get(
     *     path="/dashboard/learner",
     *     operationId="getLearnerDashboard",
     *     tags={"Dashboard"},
     *     summary="Fetch learner dashboard statistics",
     *     description="Returns an overview of learner statistics such as active courses, certificates, badges, and job matches. Requires authentication.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Learner Dashboard Stats"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="courses_progress", type="integer", example=3),
     *                 @OA\Property(property="certificates", type="integer", example=1),
     *                 @OA\Property(property="badges", type="integer", example=2),
     *                 @OA\Property(property="job_matches", type="integer", example=0)
     *             ),
     *             example={
     *                 "status": true,
     *                 "message": "Learner Dashboard Stats",
     *                 "code": 200,
     *                 "data": {
     *                     "courses_progress": 3,
     *                     "certificates": 1,
     *                     "badges": 2,
     *                     "job_matches": 0
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Something went wrong"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function learner()
    {


        $authId = auth()->user()->id;


        $courseProgress = Enrollment::where('user_id', $authId)->where('status', 'active')->count();
        $badges = UserBadge::where('user_id', $authId)->count();
        $certificates = Certificate::where('user_id', $authId)->count();



        $data = [
            'courses_progress' => $courseProgress ?? 0,
            'certificates' => $certificates ?? 0,
            'badges' => $badges ?? 0,
            'job_matches' => $jobMatches ?? 0
        ];
        return ResponseHelper::success($data, 'Learner Dashboard Stats');
    }




    public function instructor()
    {
        //role noto st

    }



}
