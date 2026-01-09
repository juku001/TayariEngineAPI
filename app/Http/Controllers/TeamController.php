<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Mail\TeamInviteMail;
use App\Models\Employer;
use App\Models\EmployerTeamMember;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;
use Response;
use Str;

class TeamController extends Controller
{


    /**
     * @OA\Get(
     *     path="/teams",
     *     tags={"Employer"},
     *     summary="Get all teams of the authenticated employer's company",
     *     description="Returns a list of all teams under the employer's company.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of teams",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all teams"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="name", type="string", example="Marketing Team"),
     *                     @OA\Property(property="company_id", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : SQLSTATE[HY000] ..."),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function index()
    {
        $authId = auth()->user()->id;
        $companyId = Employer::where('user_id', $authId)->first()->company_id;

        $teams = Team::where('company_id', $companyId)->get();
        return ResponseHelper::success($teams, 'List of all teams');
    }



    /**
     * @OA\Post(
     *     path="/teams",
     *     tags={"Employer"},
     *     summary="Create a new team",
     *     description="Employers can create a new team under their company.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Engineering Team")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Team created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successful added team"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="name", type="string", example="Engineering Team"),
     *                 @OA\Property(property="company_id", type="integer", example=5),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:34:56Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : SQLSTATE[HY000] ..."),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {

            $authId = auth()->user()->id;
            $companyId = Employer::where('user_id', $authId)->first()->company_id;

            $team = Team::create([
                'name' => $request->name,
                'company_id' => $companyId
            ]);

            return ResponseHelper::success(
                $team,
                "Successful added team",
                201
            );


        } catch (Exception $e) {
            return ResponseHelper::error(
                [],
                "Error : $e",
                500
            );
        }
    }


    /**
     * @OA\Get(
     *     path="/teams/{id}",
     *     tags={"Employer"},
     *     summary="Get a specific team with invitations",
     *     description="Returns details of a team along with its invitations.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Team ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Team details with invitations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Team with invitations"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Engineering Team"),
     *                     @OA\Property(property="company_id", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T10:00:00Z"),
     *                     @OA\Property(
     *                         property="invitations",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=12),
     *                             @OA\Property(property="team_id", type="integer", example=5),
     *                             @OA\Property(property="company_id", type="integer", example=2),
     *                             @OA\Property(property="email", type="string", example="newmember@company.com"),
     *                             @OA\Property(property="token", type="string", example="abc123xyz"),
     *                             @OA\Property(property="invited_by", type="integer", example=7),
     *                             @OA\Property(property="status", type="string", example="pending"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T10:15:00Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T10:15:00Z")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Team not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Team not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        $team = Team::with('invitations')->where('id', $id)->get();
        return ResponseHelper::success(
            $team,
            "Team with invitations"
        );
    }



    /**
     * @OA\Get(
     *     path="/team/invites",
     *     tags={"Employer"},
     *     summary="Get all team invitations for the employer",
     *     description="Returns a list of all invitations sent by the authenticated employer’s company, including team details.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of invitations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invitation list"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="company_id", type="integer", example=3),
     *                     @OA\Property(property="team_id", type="integer", example=5),
     *                     @OA\Property(property="email", type="string", example="invitee@example.com"),
     *                     @OA\Property(property="token", type="string", example="ab9c8d7e6f5g4h3i2j1k"),
     *                     @OA\Property(property="invited_by", type="integer", example=7),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:00:00Z"),
     *                     @OA\Property(
     *                         property="team",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Marketing Team"),
     *                         @OA\Property(property="company_id", type="integer", example=3),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-20T10:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-20T10:00:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Unexpected error"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function getInvites()
    {
        $auth = auth()->user()->id;
        $employer = Employer::where('user_id', $auth)->first();
        $companyId = $employer->company_id;
        $invitations = TeamInvitation::with('team')->where('company_id', $companyId)->get();

        return ResponseHelper::success(
            $invitations,
            'Invitation list'
        );
    }



    /**
     * @OA\Post(
     *     path="/team/invites",
     *     tags={"Employer"},
     *     summary="Send invitations to join a team",
     *     description="Allows an employer to invite multiple members to a specific team (or no group if team_id is not provided). Accepts a comma-separated list of email addresses.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email_addresses"},
     *             @OA\Property(
     *                 property="email_addresses",
     *                 type="string",
     *                 example="john@example.com, jane@example.com",
     *                 description="Comma-separated email addresses of invitees"
     *             ),
     *             @OA\Property(
     *                 property="team_id",
     *                 type="integer",
     *                 nullable=true,
     *                 example=3,
     *                 description="Optional Team ID. If not provided, users are invited without a team"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Invitations sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invitations sent successfully."),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=15),
     *                     @OA\Property(property="company_id", type="integer", example=2),
     *                     @OA\Property(property="team_id", type="integer", example=3),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="token", type="string", example="a9s8d7f6g5h4j3k2l1"),
     *                     @OA\Property(property="invited_by", type="integer", example=7),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email_addresses", type="array", @OA\Items(type="string", example="The email_addresses field is required."))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Unexpected error"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_addresses' => 'required|string', // Comma-separated emails
            'team_id' => 'nullable|exists:teams,id' // If not provided, they’ll be "no group"
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {
            $authId = auth()->user()->id;
            $employer = Employer::where('user_id', $authId)->first();
            // Split and clean up emails
            $emails = array_map('trim', explode(',', $request->email_addresses));

            $authId = auth()->user()->id;
            $team = Team::find($request->team_id);


            if (isset($request->team_id) && !$team) {
                return ResponseHelper::error([], 'Team not found.', 404);
            }
            $companyId = $employer->company_id;

            $invitations = [];
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue; // skip invalid emails
                }

                $invitation = TeamInvitation::create([
                    'email' => $email,
                    'team_id' => $request->team_id ?? null,
                    'token' => Str::random(40), // unique token for acceptance
                    'status' => 'pending',
                    'invited_by' => $authId,
                    'company_id' => $companyId
                ]);

                $invitations[] = $invitation;

                // Build the invite link (adjust route name/domain)
                $inviteLink = url("/invite/accept/{$invitation->token}");

                // Send email
                Mail::to($email)->send(new TeamInviteMail(
                    $inviteLink,
                    optional($team)->name ?? 'No Group',
                    auth()->user()->name
                ));

            }


            return ResponseHelper::success(
                $invitations,
                'Invitations sent successfully.',
                201
            );

        } catch (Exception $e) {
            return ResponseHelper::error([], "Error : {$e->getMessage()}", 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/team/invite/accept/{token}",
     *     tags={"Employer"},
     *     summary="Accept a team invitation",
     *     description="Allows an invited user to accept a team invitation using the unique token sent via email. 
     *     If the user exists, they are added to the team. If the user does not exist, they are redirected to the registration page with prefilled invitation data.",
     *
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="Invitation token provided in the email",
     *         @OA\Schema(type="string", example="abc123xyz456")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Invitation accepted and user added to the team",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="You have been added to the team successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Details of the user who accepted the invitation",
     *                 @OA\Property(property="id", type="integer", example=45),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Invalid or expired invitation token",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or expired invitation token."),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function accept(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return ResponseHelper::error([], 'Invalid or expired invitation token.', 404);
        }

        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            EmployerTeamMember::firstOrCreate([
                'team_id' => $invitation->team_id,
                'user_id' => $user->id,
            ]);

            $invitation->update(['status' => 'accepted']);

            return ResponseHelper::success(
                $user,
                'You have been added to the team successfully.',
                200
            );
        }
        session(['team_invite_token' => $invitation->token]);

        return redirect()->route('register')->with([
            'invite_email' => $invitation->email,
            'invite_team_id' => $invitation->team_id
        ]);
    }




    /**
     * @OA\Delete(
     *     path="/team/invite/remove/{id}",
     *     tags={"Employer"},
     *     summary="Remove a team invitation",
     *     description="Deletes a specific team invitation by ID. Only available to authenticated employers.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the invitation to delete",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Deleted successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Deleted successful"),
     *             @OA\Property(property="code", type="integer", example=204),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Invitation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invitation not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Unexpected error"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function destroy(string $id)
    {

        $invite = TeamInvitation::find($id);

        if ($invite) {
            $invite->delete();
            return ResponseHelper::success([], "Deleted successful", 204);
        }
        return ResponseHelper::error([], 'Invitation not found', 404);
    }





}
