<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{

    /**
     * @OA\Get(
     *     path="/badges",
     *     operationId="getBadgesWithUserStatus",
     *     tags={"Miscellaneous"},
     *     summary="Get all badges with user status",
     *     description="Returns a list of all available badges and whether the authenticated user owns each badge.",
     *
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of badges with user status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of badges with user status"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Quick Learner"),
     *                     @OA\Property(property="slug", type="string", example="quick-learner"),
     *                     @OA\Property(property="has_badge", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated (missing or invalid token)"
     *     )
     * )
     */

    public function index()
    {
        $authId = auth()->user()->id;

  
        $badges = Badge::all();

        $userBadgeIds = UserBadge::where('user_id', $authId)
            ->pluck('badge_id')
            ->toArray();

        
        $badgesWithStatus = $badges->map(function ($badge) use ($userBadgeIds) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'slug' => $badge->slug,
                'has_badge' => in_array($badge->id, $userBadgeIds),
            ];
        });

        return ResponseHelper::success(
            $badgesWithStatus,
            'List of badges with user status'
        );
    }

}
