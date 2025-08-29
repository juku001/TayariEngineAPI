<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Role;
use App\Models\User;
use Exception;
use App\Services\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $logService;

    public function __construct(AdminLogService $logService)
    {
        $this->logService = $logService;
    }


    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get filtered list of users",
     *     description="Retrieve a list of users with optional filters. Super admin is excluded from results.",
     *     operationId="getUsers",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role (e.g., learner, employer, admin, instructor)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter users by status (e.g., active, inactive, suspended)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="verified",
     *         in="query",
     *         description="Filter only verified users (true/false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of filtered users",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Filtered list of users"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/401"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = User::with('roles'); // eager load roles

        // Exclude super_admin
        $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'super_admin');
        });

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Verified filter
        if ($request->boolean('verified')) {
            $query->whereNotNull('email_verified_at');
        }

        $users = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status' => $user->status,
                'verified' => $user->email_verified_at ? true : false,
                'created_at' => $user->created_at,
                'roles' => $user->roles->pluck('name')->first(), // add roles here
            ];
        });

        return ResponseHelper::success($users, 'Filtered list of users');
    }






    /**
     * @OA\Patch(
     *     path="/users/{id}",
     *     summary="Update a user",
     *     description="Update user role or status. Super admin cannot be modified.",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="role", type="string", enum={"admin","learner","instructor","employer"}, example="learner"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive","suspended"}, example="active")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot modify super admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You cannot modify a super_admin user."),
     *             @OA\Property(property="code", type="integer", example=403),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/responses/422"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/500"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

       
        if ($user->roles()->where('name', 'super_admin')->exists()) {
            return ResponseHelper::error([], 'You cannot modify a super_admin user.', 403);
        }

       
        $validator = Validator::make($request->all(), [
            'role' => 'nullable|string|in:admin,learner,instructor,employer',
            'status' => 'nullable|string|in:active,inactive,suspended,unverified'
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error(
                $validator->errors(),
                'Failed to validate fields',
                422
            );
        }

        try {
            DB::beginTransaction();

           
            if ($request->filled('role')) {
                $role = Role::where('name', $request->role)->first();
                if ($role) {
                 
                    $user->roles()->sync([$role->id]);
                }
            }

          
            if ($request->filled('status')) {
                $user->status = $request->status;
                $action = $this->logService->getActionByCode(3);
                $this->logService->record($user->id, $action, 'Changed user status for ' . $user->first_name . ' ' . $user->last_name);

                $user->save();
            }

            // $action = $this->logService->getActionByCode(1);
            // $userType = ucfirst($user->roles->pluck('name')->first());
            // $this->logService->record($user->id, $action, $userType . ' dashboard access');
            DB::commit();
            return ResponseHelper::success($user->fresh('roles'), 'User updated successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], "Error: " . $e->getMessage(), 500);
        }
    }






    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     description="Soft deletes a user, anonymizes sensitive data, and archives original info. Super admin cannot be deleted.",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully"),
     *             @OA\Property(property="data", type="object", example={"id": 10})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot delete super admin",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cannot delete super admin"),
     *             @OA\Property(property="code", type="integer", example=403),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/500"
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseHelper::error([], "User not found", 404);
        }

        if ($user->roles()->where('name', 'super_admin')->exists()) {
            return ResponseHelper::error([], 'You cannot delete a super_admin user.', 403);
        }

        DB::beginTransaction();
        try {
            $user->archive = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'sex' => $user->sex,
                'provider' => $user->provider,
                'created_by' => $user->created_by,
                'date_of_birth' => $user->date_of_birth,
            ];

            $user->first_name = 'Deleted User';
            $user->last_name = null;
            $user->email = 'deleted_' . $user->id . '_' . uniqid() . '@tayari.local';
            $user->mobile = null;
            $user->status = 'deleted';
            $user->deleted_by = $request->user()->id ?? null;

            $user->save();
            $user->delete(); // SoftDeletes

            DB::commit();
            return ResponseHelper::success(['id' => $user->id], 'User deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error([], "Error: " . $e->getMessage(), 500);
        }
    }


}
