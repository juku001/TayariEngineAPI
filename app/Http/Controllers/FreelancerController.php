<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Freelancer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;

class FreelancerController extends Controller
{
    /**
     * List all freelancers
     */
    /**
     * @OA\Get(
     *     path="/freelancers",
     *     summary="Get list of available freelancers",
     *     description="Returns all freelancers who are currently available.",
     *     operationId="getAvailableFreelancers",
     *     tags={"Freelancer"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Freelancers retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=12),
     *                     @OA\Property(property="title", type="string", example="Data Scientist"),
     *                     @OA\Property(property="description", type="string", example="Expert in machine learning and data analysis."),
     *                     @OA\Property(property="phone_number", type="string", example="+255712345678"),
     *                     @OA\Property(property="whatsapp", type="string", example=null),
     *                     @OA\Property(property="phone_is_whatsapp", type="boolean", example=true),
     *                     @OA\Property(property="country", type="string", example="Nigeria"),
     *                     @OA\Property(property="region", type="string", example="Lagos"),
     *                     @OA\Property(property="address", type="string", example="Victoria Island"),
     *                     @OA\Property(property="start_price", type="number", format="float", example=55),
     *                     @OA\Property(property="end_price", type="number", format="float", example=70),
     *                     @OA\Property(property="rate", type="string", example="hr"),
     *                     @OA\Property(property="currency", type="string", example="USD"),
     *                     @OA\Property(property="responds_in", type="string", example="4 hours"),
     *                     @OA\Property(property="rating", type="number", format="float", example=5),
     *                     @OA\Property(property="reviews_count", type="integer", example=42),
     *                     @OA\Property(property="projects_completed", type="integer", example=73),
     *                     @OA\Property(property="success_rate", type="number", format="float", example=100),
     *                     @OA\Property(property="status", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-10T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-10T12:00:00Z"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="first_name", type="string", example="Amina"),
     *                         @OA\Property(property="last_name", type="string", example="Hassan"),
     *                         @OA\Property(property="email", type="string", example="amina@example.com")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Freelancers retrieved successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $freelancers = Freelancer::with('user')->where('is_available', true)->get();

        return ResponseHelper::success($freelancers, 'Freelancers retrieved successfully');
    }

    /**
     * Example status endpoint
     * You can filter by rate, availability, projects, etc.
     */

    /**
     * @OA\Patch(
     *     path="/freelancers/{id}/status",
     *     summary="Toggle freelancer availability status",
     *     description="Updates the 'is_available' status of the authenticated user's freelancer account.",
     *     operationId="toggleFreelancerStatus",
     *     tags={"Freelancer"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Freelancer ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Freelancer status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=12),
     *                 @OA\Property(property="is_available", type="boolean", example=false)
     *             ),
     *             @OA\Property(property="message", type="string", example="Freelancer status updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Freelancer account not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Freelancer account not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function status(Request $request, string $id)
    {
        $authId = auth()->user()->id;

        $freelancer = Freelancer::where('id', $id)
            ->where('user_id', $authId)
            ->first();

        if (!$freelancer) {
            return ResponseHelper::error([], 'Freelancer account not found', 404);
        }

        // Toggle status
        $freelancer->is_available = !$freelancer->is_available;
        $freelancer->save();

        return ResponseHelper::success($freelancer, 'Freelancer status updated successfully');
    }


    /**
     * Show a single freelancer
     */
    /**
     * @OA\Get(
     *     path="/freelancers/{id}",
     *     summary="Get a single freelancer",
     *     description="Returns detailed information about a freelancer by ID, including user details.",
     *     operationId="getFreelancerById",
     *     tags={"Freelancer"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Freelancer ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Freelancer retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=12),
     *                 @OA\Property(property="title", type="string", example="Data Scientist"),
     *                 @OA\Property(property="description", type="string", example="Expert in machine learning and data analysis."),
     *                 @OA\Property(property="phone_number", type="string", example="+255712345678"),
     *                 @OA\Property(property="whatsapp", type="string", example=null),
     *                 @OA\Property(property="phone_is_whatsapp", type="boolean", example=true),
     *                 @OA\Property(property="country", type="string", example="Nigeria"),
     *                 @OA\Property(property="region", type="string", example="Lagos"),
     *                 @OA\Property(property="address", type="string", example="Victoria Island"),
     *                 @OA\Property(property="start_price", type="number", format="float", example=55),
     *                 @OA\Property(property="end_price", type="number", format="float", example=70),
     *                 @OA\Property(property="rate", type="string", example="hr"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="responds_in", type="string", example="4 hours"),
     *                 @OA\Property(property="rating", type="number", format="float", example=5),
     *                 @OA\Property(property="reviews_count", type="integer", example=42),
     *                 @OA\Property(property="projects_completed", type="integer", example=73),
     *                 @OA\Property(property="success_rate", type="number", format="float", example=100),
     *                 @OA\Property(property="is_available", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-10T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-10T12:00:00Z"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="first_name", type="string", example="Amina"),
     *                     @OA\Property(property="last_name", type="string", example="Hassan"),
     *                     @OA\Property(property="email", type="string", example="amina@example.com")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Freelancer retrieved successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Freelancer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Freelancer not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        $freelancer = Freelancer::with('user')->find($id);

        if (!$freelancer) {
            return ResponseHelper::error([], 'Freelancer not found', 404);
        }

        return ResponseHelper::success($freelancer, 'Freelancer retrieved successfully');
    }

    /**
     * Store a new freelancer
     */

    /**
     * @OA\Post(
     *     path="/freelancers",
     *     summary="Create a new freelancer",
     *     description="Creates a new freelancer profile for a logged in user.",
     *     operationId="createFreelancer",
     *     tags={"Freelancer"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"user_id","title","description","rate","currency"},
     *             @OA\Property(property="title", type="string", example="Data Scientist"),
     *             @OA\Property(property="description", type="string", example="Expert in machine learning and data analysis."),
     *             @OA\Property(property="phone_number", type="string", example="+255712345678"),
     *             @OA\Property(property="whatsapp", type="string", example=null),
     *             @OA\Property(property="phone_is_whatsapp", type="boolean", example=true),
     *             @OA\Property(property="country", type="string", example="Nigeria"),
     *             @OA\Property(property="region", type="string", example="Lagos"),
     *             @OA\Property(property="address", type="string", example="Victoria Island"),
     *             @OA\Property(property="start_price", type="number", format="float", example=55),
     *             @OA\Property(property="end_price", type="number", format="float", example=70),
     *             @OA\Property(property="rate", type="string", enum={"hr","day","project","month"}, example="hr"),
     *             @OA\Property(property="currency", type="string", enum={"TZS","USD","EUR"}, example="USD"),
     *             @OA\Property(property="responds_in", type="string", example="4 hours"),
     *             @OA\Property(property="rating", type="number", format="float", example=5),
     *             @OA\Property(property="reviews_count", type="integer", example=42),
     *             @OA\Property(property="projects_completed", type="integer", example=73),
     *             @OA\Property(property="success_rate", type="number", format="float", example=100)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Freelancer created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Data Scientist"),
     *                 @OA\Property(property="description", type="string", example="Expert in machine learning and data analysis."),
     *                 @OA\Property(property="phone_number", type="string", example="+255712345678"),
     *                 @OA\Property(property="whatsapp", type="string", example=null),
     *                 @OA\Property(property="phone_is_whatsapp", type="boolean", example=true),
     *                 @OA\Property(property="country", type="string", example="Nigeria"),
     *                 @OA\Property(property="region", type="string", example="Lagos"),
     *                 @OA\Property(property="address", type="string", example="Victoria Island"),
     *                 @OA\Property(property="start_price", type="number", format="float", example=55),
     *                 @OA\Property(property="end_price", type="number", format="float", example=70),
     *                 @OA\Property(property="rate", type="string", example="hr"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="responds_in", type="string", example="4 hours"),
     *                 @OA\Property(property="rating", type="number", format="float", example=5),
     *                 @OA\Property(property="reviews_count", type="integer", example=42),
     *                 @OA\Property(property="projects_completed", type="integer", example=73),
     *                 @OA\Property(property="success_rate", type="number", format="float", example=100)
     *             ),
     *             @OA\Property(property="message", type="string", example="Freelancer created successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'phone_number' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'phone_is_whatsapp' => 'nullable|boolean',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'start_price' => 'nullable|numeric|min:0',
            'end_price' => 'nullable|numeric|min:0',
            'rate' => 'required|in:hr,day,project,month',
            'currency' => 'required|in:TZS,USD,EUR',
            'responds_in' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'projects_completed' => 'nullable|integer|min:0',
            'success_rate' => 'nullable|numeric|min:0|max:100',
        ]);


        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Validation failed', 422);
        }
        $data = $validator->validated();
        $data['user_id'] = auth()->user()->id;

        $freelancer = Freelancer::create($data);

        return ResponseHelper::success($freelancer, 'Freelancer created successfully');
    }

    /**
     * Update an existing freelancer
     */



    /**
     * @OA\Put(
     *     path="/freelancers/{id}",
     *     summary="Update an existing freelancer",
     *     description="Updates the details of an existing freelancer by ID.",
     *     operationId="updateFreelancer",
     *     tags={"Freelancer"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Freelancer ID to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Senior Data Scientist"),
     *             @OA\Property(property="description", type="string", example="Expert in machine learning and statistical modeling."),
     *             @OA\Property(property="phone_number", type="string", example="+255712345678"),
     *             @OA\Property(property="whatsapp", type="string", example=null),
     *             @OA\Property(property="phone_is_whatsapp", type="boolean", example=true),
     *             @OA\Property(property="country", type="string", example="Nigeria"),
     *             @OA\Property(property="region", type="string", example="Lagos"),
     *             @OA\Property(property="address", type="string", example="Victoria Island"),
     *             @OA\Property(property="start_price", type="number", format="float", example=55),
     *             @OA\Property(property="end_price", type="number", format="float", example=70),
     *             @OA\Property(property="rate", type="string", enum={"hr","day","project","month"}, example="hr"),
     *             @OA\Property(property="currency", type="string", enum={"TZS","USD","EUR"}, example="USD"),
     *             @OA\Property(property="responds_in", type="string", example="4 hours"),
     *             @OA\Property(property="rating", type="number", format="float", example=5),
     *             @OA\Property(property="reviews_count", type="integer", example=42),
     *             @OA\Property(property="projects_completed", type="integer", example=73),
     *             @OA\Property(property="success_rate", type="number", format="float", example=100)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Freelancer updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=12),
     *                 @OA\Property(property="title", type="string", example="Data Scientist"),
     *                 @OA\Property(property="description", type="string", example="Expert in machine learning and data analysis."),
     *                 @OA\Property(property="phone_number", type="string", example="+255712345678"),
     *                 @OA\Property(property="whatsapp", type="string", example=null),
     *                 @OA\Property(property="phone_is_whatsapp", type="boolean", example=true),
     *                 @OA\Property(property="country", type="string", example="Nigeria"),
     *                 @OA\Property(property="region", type="string", example="Lagos"),
     *                 @OA\Property(property="address", type="string", example="Victoria Island"),
     *                 @OA\Property(property="start_price", type="number", format="float", example=55),
     *                 @OA\Property(property="end_price", type="number", format="float", example=70),
     *                 @OA\Property(property="rate", type="string", example="hr"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="responds_in", type="string", example="4 hours"),
     *                 @OA\Property(property="rating", type="number", format="float", example=5),
     *                 @OA\Property(property="reviews_count", type="integer", example=42),
     *                 @OA\Property(property="projects_completed", type="integer", example=73),
     *                 @OA\Property(property="success_rate", type="number", format="float", example=100)
     *             ),
     *             @OA\Property(property="message", type="string", example="Freelancer updated successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Freelancer not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Freelancer not found")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {

        $freelancer = Freelancer::find($id);

        if (!$freelancer) {
            return ResponseHelper::error([], 'Freelancer not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'phone_number' => 'sometimes,nullable|string',
            'whatsapp' => 'sometimes,nullable|string',
            'phone_is_whatsapp' => 'sometimes,nullable|boolean',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'start_price' => 'nullable|numeric|min:0',
            'end_price' => 'nullable|numeric|min:0',
            'rate' => 'nullable|in:hr,day,project,month',
            'currency' => 'nullable|in:TZS,USD,EUR',
            'responds_in' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'projects_completed' => 'nullable|integer|min:0',
            'success_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Validation failed', 422);
        }

        $freelancer->update($validator->validated());

        return ResponseHelper::success($freelancer, 'Freelancer updated successfully');
    }
}
