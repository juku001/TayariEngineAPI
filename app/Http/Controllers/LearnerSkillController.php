<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\LearnerPoint;
use App\Models\LearnerSkill;
use Illuminate\Http\Request;

class LearnerSkillController extends Controller
{


    /**
     * @OA\Get(
     *     path="/learner/skills",
     *     tags={"Courses"},
     *     summary="Get skills for authenticated learner",
     *     description="Returns all skills associated with the authenticated learner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of learner skills",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="List of learner skills"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="PHP"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $authId = auth()->id();

        $skills = LearnerSkill::with('skill')
            ->where('user_id', $authId)
            ->get()
            ->map(function ($learnerSkill) {
                return [
                    'id' => $learnerSkill->skill->id,
                    'name' => $learnerSkill->skill->name
                ];
            });

        return ResponseHelper::success($skills, 'List of learner skills');
    }





    /**
     * @OA\Get(
     *     path="/learner/points",
     *     tags={"Courses"},
     *     summary="Get total points and point history for the authenticated learner",
     *     description="Returns the total points and a list of point transactions (history) for the authenticated learner.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Learner points and history retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Learner points and history"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_points", type="integer", example=45),
     *                 @OA\Property(
     *                     property="history",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="points", type="integer", example=10),
     *                         @OA\Property(property="reason", type="string", example="Completed lesson"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-11T15:45:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function points(Request $request)
    {
        $authId = auth()->id();

        $pointHistory = LearnerPoint::where('user_id', $authId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'points', 'created_at']);

        $totalPoints = $pointHistory->sum('points');

        return ResponseHelper::success([
            'total_points' => $totalPoints,
            'history' => $pointHistory
        ], 'Learner points and history');
    }


}
