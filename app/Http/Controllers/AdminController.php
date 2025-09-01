<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{


    /**
     * @OA\Get(
     *     path="/admin/logs",
     *     tags={"Admin"},
     *      security={{"bearerAuth":{}}},
     *     summary="Get filtered admin logs",
     *     description="Fetch admin logs with filters for search, action, and role. Returns paginated logs along with stats (total logs, active users, last 24h logs, and filtered count).",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search by user's first name, last name, or email",
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         required=false,
     *         description="Filter logs by action type",
     *         @OA\Schema(type="string", example="LOGIN")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         required=false,
     *         description="Filter logs by user role",
     *         @OA\Schema(type="string", example="Admin")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Pagination page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
          *     @OA\Response(
     *         response=200,
     *         description="Filtered admin logs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Filtered admin logs"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="stats",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=1),
     *                     @OA\Property(property="active", type="integer", example=1),
     *                     @OA\Property(property="last_24", type="integer", example=1),
     *                     @OA\Property(property="filtered", type="integer", example=1)
     *                 ),
     *                 @OA\Property(
     *                     property="logs",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="user_id", type="integer", example=1),
     *                             @OA\Property(property="action", type="string", example="LOGIN"),
     *                             @OA\Property(property="details", type="string", example="Super_admin dashboard access"),
     *                             @OA\Property(property="ip_address", type="string", example="127.0.0.1"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-30T06:10:53.000000Z"),
     *                             @OA\Property(property="first_name", type="string", example="Tayari"),
     *                             @OA\Property(property="last_name", type="string", example="Admin"),
     *                             @OA\Property(property="roles", type="string", example="super_admin")
     *                         )
     *                     ),
     *                     @OA\Property(property="first_page_url", type="string", example="{base_url}/admin/logs?page=1"),
     *                     @OA\Property(property="from", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=1),
     *                     @OA\Property(property="last_page_url", type="string", example="{base_url}/api/admin/logs?page=1"),
     *                     @OA\Property(
     *                         property="links",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="url", type="string", nullable=true, example=null),
     *                             @OA\Property(property="label", type="string", example="« Previous"),
     *                             @OA\Property(property="page", type="integer", nullable=true, example=null),
     *                             @OA\Property(property="active", type="boolean", example=false)
     *                         )
     *                     ),
     *                     @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                     @OA\Property(property="path", type="string", example="{base_url}/api/admin/logs"),
     *                     @OA\Property(property="per_page", type="integer", example=20),
     *                     @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                     @OA\Property(property="to", type="integer", example=1),
     *                     @OA\Property(property="total", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     )
     * )
     */

    public function logs(Request $request)
    {
        $query = AdminLog::with(['user.roles']); 

       
        $total = AdminLog::count();

        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

       
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        
        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('user.roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

      
        $logs = $query->latest()->paginate(100); ///list of 100 logs

     
        $transformedLogs = $logs->getCollection()->map(function ($log) {
            return [
                'id' => $log->id,
                "user_id" => $log->user_id,
                "action" => $log->action,
                "details" => $log->details,
                "ip_address" => $log->ip_address,
                'created_at' => $log->created_at,
                'first_name' => $log->user?->first_name,
                'last_name' => $log->user?->last_name,
                'roles' => $log->user->roles->pluck('name')->first(),
            ];
        });

       
        $logs->setCollection($transformedLogs);

      
        $active = AdminLog::where('action', 'LOGIN')
            ->where('created_at', '>=', now()->subDays(7))
            ->distinct('user_id')
            ->count(); 

        $last = AdminLog::where('created_at', '>=', now()->subDay())->count(); // logs in last 24h

        $stats = [
            'total' => $total,
            'active' => $active,
            'last_24' => $last,
            'filtered' => $logs->total(),
        ];

        $data = [
            "stats" => $stats,
            "logs" => $logs, 
        ];

        return ResponseHelper::success($data, 'Filtered admin logs');
    }





    /**
     * @OA\Post(
     *     path="/admin/communications",
     *     tags={"Admin"},
     *     summary="Send bulk communications",
     *     security={{"bearerAuth":{}}},
     *     description="Send a message to one or more recipients via email. Accepts comma-separated list of emails, subject, and message body. Returns a list of successfully sent emails.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"emails", "subject", "message"},
     *             @OA\Property(
     *                 property="emails",
     *                 type="string",
     *                 example="user1@example.com, user2@example.com",
     *                 description="Comma-separated list of email addresses"
     *             ),
     *             @OA\Property(
     *                 property="subject",
     *                 type="string",
     *                 maxLength=255,
     *                 example="System Maintenance Notice"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Dear users, the system will be undergoing maintenance tonight at 11:00 PM UTC."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Emails sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Emails sent successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="sent_to",
     *                     type="array",
     *                     @OA\Items(type="string", example="user1@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No valid emails provided",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No valid emails provided"),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object",
     *                 example={"emails": {"The emails field is required."}, "subject": {"The subject field is required."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error sending emails",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error sending emails: SMTP connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     )
     * )
     */

    public function comms(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'emails' => 'required|string', 
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

       
        $emails = array_map('trim', explode(',', $request->emails));
        $validEmails = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $email;
            }
        }

        if (empty($validEmails)) {
            return ResponseHelper::error([], 'No valid emails provided', 400);
        }

        try {
            foreach ($validEmails as $email) {
                Mail::raw($request->message, function ($mail) use ($email, $request) {
                    $mail->to($email)
                        ->subject($request->subject);
                });
            }

            return ResponseHelper::success([
                'sent_to' => $validEmails
            ], 'Emails sent successfully.');

        } catch (\Exception $e) {
            return ResponseHelper::error(
                [],
                "Error sending emails: " . $e->getMessage(),
                500
            );
        }
    }

}
