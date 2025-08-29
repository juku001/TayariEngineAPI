<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Skill;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Level;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/skills",
     *     tags={"Skills"},
     *     summary="Get all skills",
     *     description="Retrieves a list of all available skills in the system. Authentication is not required.",
     *     @OA\Response(
     *         response=200,
     *         description="List of skills retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all skills."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="JavaScript"),
     *                     @OA\Property(property="slug", type="string", example="javascript"),
     *                     @OA\Property(property="category_id", type="integer", nullable=true, example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $skills = Skill::all();
        return ResponseHelper::success($skills, 'List of all skills.');
    }

    /**
     * Store a newly created resource in storage.
     */



    /**
     * @OA\Post(
     *     path="/skills",
     *     tags={"Skills"},
     *     summary="Create a new skill",
     *     description="Adds a new skill to the system. Authentication is not required.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="JavaScript"),
     *             @OA\Property(property="category_id", type="integer", nullable=true, example=2, description="ID of the category if applicable")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Skill created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Skill added successful"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="JavaScript"),
     *                 @OA\Property(property="slug", type="string", example="javascript"),
     *                 @OA\Property(property="category_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:00:00.000000Z")
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
     *             @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: database connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:skills,name',
            'category_id' => 'nullable|integer|exists:categories,id'
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {

            $catId = $request->category_id;


            $skill = Skill::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category_id' => $catId
            ]);


            return ResponseHelper::success(
                $skill,
                'Skill added successful',
                201
            );


        } catch (Exception $e) {
            return ResponseHelper::error(
                [],
                'Error : ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */


    /**
     * @OA\Get(
     *     path="/skills/{id}",
     *     tags={"Skills"},
     *     summary="Get skill details",
     *     description="Retrieve details of a specific skill by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the skill",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Skill details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="JavaScript"),
     *                 @OA\Property(property="slug", type="string", example="javascript"),
     *                 @OA\Property(property="category_id", type="integer", nullable=true, example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-29T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T12:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Skill not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Skill not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        $skill = Skill::find($id);

        if (!$skill) {
            return ResponseHelper::error([], "Skill not found", 404);
        }
        return ResponseHelper::success(
            $skill,
            "Skill details"
        );
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Put(
     *     path="/skills/{id}",
     *     tags={"Skills"},
     *     summary="Update a skill",
     *     description="Update the details of an existing skill by its ID. The skill name must be unique, but the current skill can keep its existing name.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the skill to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Advanced JavaScript"),
     *             @OA\Property(property="category_id", type="integer", nullable=true, example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Skill updated successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Advanced JavaScript"),
     *                 @OA\Property(property="slug", type="string", example="advanced-javascript"),
     *                 @OA\Property(property="category_id", type="integer", nullable=true, example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T19:46:10.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-29T15:22:45.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Skill not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Skill not found"),
     *             @OA\Property(property="code", type="integer", example=404),
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
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Database connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|unique:skills,name,' . $id, // allow same name for this ID
                'category_id' => 'nullable|integer|exists:categories,id'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Failed to validate fields', 422);
        }

        try {
            $skill = Skill::find($id);
            $catId = $request->category_id;

            if (!$skill) {
                return ResponseHelper::error([], 'Skill not found', 404);
            }

            $slug = Str::slug($request->name);

            $originalSlug = $slug;
            $counter = 1;
            while (Skill::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $skill->update([
                'name' => $request->name,
                'slug' => $slug,
                'category_id' => $catId
            ]);

            return ResponseHelper::success(
                $skill,
                'Skill updated successfully',
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
     *     path="/skills/{id}",
     *     tags={"Skills"},
     *     summary="Delete a skill",
     *     description="Remove a skill by its ID. Once deleted, the skill cannot be recovered.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the skill to delete",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Skill deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Skill deleted successful"),
     *             @OA\Property(property="code", type="integer", example=204),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Skill not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Skill not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error : Database connection failed"),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function destroy(string $id)
    {
        $skill = Skill::find($id);
        if (!$skill) {
            return ResponseHelper::error(
                [],
                'Skill not found',
                404
            );
        }
        $skill->delete();
        return ResponseHelper::success(
            [],
            'Skill deleted successful',
            204
        );
    }




    /**
     * @OA\Get(
     *     path="/levels",
     *     tags={"Miscellaneous"},
     *     summary="Get all levels",
     *     description="Retrieve the list of available levels such as Beginner, Intermediate, and Difficult.",
     *     @OA\Response(
     *         response=200,
     *         description="List of all levels",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of all levels"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Beginner"),
     *                     @OA\Property(property="slug", type="string", example="beginner"),
     *                     @OA\Property(property="description", type="string", example="Just getting started."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-28T19:46:10.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-28T19:46:10.000000Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function levels()
    {
        $levels = Level::all();
        return ResponseHelper::success(
            $levels,
            "List of all levels"
        );
    }
}
