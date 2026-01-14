<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Employer;
use App\Models\Freelancer;
use App\Models\FreelancerSave;
use Illuminate\Http\Request;

class FreelancerSaveController extends Controller
{


    /**
     * @OA\Get(
     *     path="/freelancer/saves/employer",
     *     operationId="getEmployerSavedFreelancers",
     *     tags={"Saved Freelancers"},
     *     summary="Get saved freelancers by employer",
     *     description="Returns a list of freelancers saved by the authenticated employer.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Saved freelancers fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Saved freelancers by Employer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     description="FreelancerSave object"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer account not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer account not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function employer()
    {
        $authId = auth()->user()->id;
        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer) {
            return ResponseHelper::error([], 'Employer account not found', 404);
        }
        $freelancers = FreelancerSave::where('employer_id', $employer->id)->get();
        return ResponseHelper::success($freelancers, 'Saved freelancers by Employer');
    }

    /**
     * @OA\Get(
     *     path="/freelancer/saves/company",
     *     operationId="getCompanySavedFreelancers",
     *     tags={"Saved Freelancers"},
     *     summary="Get saved freelancers by company",
     *     description="Returns a list of freelancers saved by the authenticated employer's company.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Saved freelancers fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Saved freelancers by Company"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     description="FreelancerSave object"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer account not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer account not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function company()
    {
        $authId = auth()->user()->id;
        $employer = Employer::where('user_id', $authId)->first();
        if (!$employer) {
            return ResponseHelper::error([], 'Employer account not found', 404);
        }
        $companyId = $employer->company_id;
        $freelancers = FreelancerSave::where('company_id', $companyId)->get();
        return ResponseHelper::success($freelancers, 'Saved freelancers by Company');
    }

    /**
     * @OA\Get(
     *     path="/freelancers/saves/{id}",
     *     operationId="getSavedFreelancer",
     *     tags={"Saved Freelancers"},
     *     summary="Get freelancer details and saved status",
     *     description="Returns freelancer details and whether the authenticated employer or their company has saved the freelancer.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Freelancer ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Freelancer details fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="freelancer", type="object"),
     *                 @OA\Property(property="is_saved", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Employer or Freelancer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function show(string $freelancerId)
    {
        $freelancer = Freelancer::find($freelancerId);

        if (!$freelancer) {
            return ResponseHelper::error([], 'Freelancer not found.', 404);
        }

        $authUser = auth()->user();

        $employer = Employer::where('user_id', $authUser->id)->first();
        if (!$employer) {
            return ResponseHelper::error([], 'Employer not found.', 404);
        }

        $companyId = $employer->company_id;

        $freelancerSave = FreelancerSave::where('freelancer_id', $freelancer->id)
            ->where(function ($query) use ($authUser, $companyId) {
                $query->where('user_id', $authUser->id)   // saved by employer
                    ->orWhere('company_id', $companyId); // saved by company
            })
            ->first();

        return ResponseHelper::success([
            'freelancer' => $freelancer,
            'is_saved' => (bool) $freelancerSave,
        ], 'Freelancer details fetched successfully');
    }




    /**
     * @OA\Put(
     *     path="/freelancers/saves/{id}",
     *     operationId="toggleSavedFreelancer",
     *     tags={"Saved Freelancers"},
     *     summary="Save or unsave a freelancer",
     *     description="Toggles save status for a freelancer. If already saved, it will be unsaved; otherwise it will be saved.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Freelancer ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Save status toggled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Freelancer saved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_saved", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Freelancer or Employer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Employer not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function update(string $freelancerId)
    {
        $freelancer = Freelancer::find($freelancerId);

        if (!$freelancer) {
            return ResponseHelper::error([], 'Freelancer not found.', 404);
        }

        $authUser = auth()->user();

        $employer = Employer::where('user_id', $authUser->id)->first();
        if (!$employer) {
            return ResponseHelper::error([], 'Employer not found.', 404);
        }

        $companyId = $employer->company_id;

        // Check if already saved (by employer OR company)
        $freelancerSave = FreelancerSave::where('freelancer_id', $freelancer->id)
            ->where(function ($query) use ($authUser, $companyId) {
                $query->where('user_id', $authUser->id)
                    ->orWhere('company_id', $companyId);
            })
            ->first();

        // 🔁 Toggle save / unsave
        if ($freelancerSave) {
            $freelancerSave->delete();

            return ResponseHelper::success([
                'is_saved' => false
            ], 'Freelancer removed from saved list');
        }

        // ➕ Save freelancer
        FreelancerSave::create([
            'freelancer_id' => $freelancer->id,
            'user_id' => $authUser->id,
            'company_id' => $companyId,
        ]);

        return ResponseHelper::success([
            'is_saved' => true
        ], 'Freelancer saved successfully');
    }

}
