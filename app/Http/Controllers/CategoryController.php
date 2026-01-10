<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Get all categories",
     *     description="Retrieve a list of all available categories including their details.",
     *     @OA\Response(
     *         response=200,
     *         description="List of all categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all categories"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Digital Marketing"),
     *                     @OA\Property(property="slug", type="string", example="market"),
     *                     @OA\Property(property="description", type="string", nullable=true, example="Covers SEO, Ads, and more."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T19:46:09.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-28T19:46:09.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Database connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function index()
    {
        // $cats = Category::all();
        $cats = Category::where('status', 'active')->get();
        return ResponseHelper::success($cats, 'List of all categories');
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Create a new category",
     *     description="Create a new category by providing a unique name and an optional description.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Artificial Intelligence"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Covers AI, ML, and Deep Learning")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category added successfully"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="name", type="string", example="Artificial Intelligence"),
     *                 @OA\Property(property="slug", type="string", example="artificial-intelligence"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Covers AI, ML, and Deep Learning"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T10:15:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T10:15:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to validate fields"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|unique:categories,name',
                'description' => 'nullable|string'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {
            $slug = Str::slug($request->name);


            $originalSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $cat = Category::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description
            ]);

            return ResponseHelper::success(
                $cat,
                'Category added successfully',
                201
            );

        } catch (Exception $e) {
            return ResponseHelper::error([], "Error : {$e->getMessage()}", 500);
        }
    }


    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Get category details",
     *     description="Retrieve details of a single category by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Digital Marketing"),
     *                 @OA\Property(property="slug", type="string", example="market"),
     *                 @OA\Property(property="description", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */

    public function show(int $id)
    {
        $cat = Category::find($id);
        if (!$cat) {
            return ResponseHelper::error(
                [],
                'Category not found',
                404
            );
        }
        return ResponseHelper::success(
            $cat,
            'Category details'
        );
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Update a category",
     *     description="Update an existing category by ID. Name must be unique (except for the same ID).",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to update",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Data Science"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Covers Python, R, and Data Engineering")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="name", type="string", example="Data Science"),
     *                 @OA\Property(property="slug", type="string", example="data-science"),
     *                 @OA\Property(property="description", type="string", example="Covers Python, R, and Data Engineering"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|unique:categories,name,' . $id,
                'description' => 'nullable|string'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {
            $cat = Category::find($id);

            if (!$cat) {
                return ResponseHelper::error([], 'Category not found', 404);
            }

            $slug = Str::slug($request->name);


            $originalSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $cat->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description
            ]);

            return ResponseHelper::success(
                $cat,
                'Category updated successfully',
                200
            );

        } catch (Exception $e) {
            return ResponseHelper::error([], "Error : {$e->getMessage()}", 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     tags={"Categories"},
     *     summary="Delete a category",
     *     description="Delete an existing category by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to delete",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully"),
     *             @OA\Property(property="code", type="integer", example=204),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */

    public function destroy(string $id)
    {
        $cat = Category::find($id);
        if (!$cat) {
            return ResponseHelper::error(
                [],
                'Category not found',
                404
            );
        }

        $cat->delete();

        return ResponseHelper::success(
            [],
            'Category deleted',
            204
        );
    }


    /**
     * @OA\Patch(
     *     path="/categories/{id}/status",
     *     summary="Update category status",
     *     description="Toggle or update the status of a category",
     *     operationId="updateCategoryStatus",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(
     *             type="string",
     *             example="1"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         description="Optional: Set status explicitly",
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"active","inactive"},
     *                 example="inactive"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category status updated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Technology"),
     *                 @OA\Property(property="status", type="string", example="inactive")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function status(Request $request, string $id)
    {
        $cat = Category::find($id);

        if (!$cat) {
            return ResponseHelper::error([], 'Category not found', 404);
        }

        $cat->status = $cat->status === 'active' ? 'inactive' : 'active';
        $cat->save();

        return ResponseHelper::success(
            $cat,
            'Category status updated successfully.'
        );
    }

}




