<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Course;
use Illuminate\Http\Request;

class PopularCourseController extends Controller
{


    /**
     * @OA\Get(
     *     path="/courses/featured",
     *     summary="Get the list of popular (featured) courses",
     *     description="Returns a list of published courses that are marked as featured, including their average ratings and number of enrolled students.",
     *     tags={"Courses"},
     *     @OA\Response(
     *         response=200,
     *         description="List of popular courses retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of popular courses."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Organic Chemistry"),
     *                     @OA\Property(property="avg_rating", type="number", format="float", example=4.5),
     *                     @OA\Property(property="students", type="integer", example=120)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $courses = Course::with(['ratings', 'enrollments'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->get();

        $popularCourses = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'avg_rating' => round($course->ratings->avg('rating'), 1) ?? 0,
                'students' => $course->enrollments()->count() ?? 0,
            ];
        });

        return ResponseHelper::success($popularCourses, 'List of popular courses.');
    }




    /**
     * @OA\Put(
     *     path="/courses/{id}/featured",
     *     tags={"Courses"},
     *     summary="Toggle featured status of a course",
     *     description="Toggles the `is_featured` field for the given course. Only accessible to authorized users (e.g., admins or instructors with permissions).",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course featured status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course feature updated."),
     *             @OA\Property(property="code", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found."),
     *             @OA\Property(property="code", type="integer", example=404),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         ref="#/components/responses/401"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return ResponseHelper::error([], 'Course not found.', 404);
        }

        $course->is_featured = !$course->is_featured;
        $course->save();

        return ResponseHelper::success([], 'Course feature updated.');
    }
}
