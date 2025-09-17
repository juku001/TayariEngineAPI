<?php


namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Course;
use App\Models\CourseRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class CourseRatingController extends Controller
{
    /**
     * Store or update a rating for a course.
     */

    /**
     * @OA\Post(
     *     path="/courses/{id}/ratings",
     *     tags={"Courses"},
     *     summary="Add or update a course rating",
     *     description="Allows a logged-in user to rate a course and optionally leave a review. If the user has already rated the course, it updates the existing rating.",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the course to rate",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer", description="Rating from 1 to 5", example=5),
     *             @OA\Property(property="review", type="string", description="Optional review text", example="Great course!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rating saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating saved successfully"),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(property="review", type="string", example="Great course!"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     **/


    public function store(Request $request, $courseId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors(), 'Validation failed', 422);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return ResponseHelper::error([], 'Course not found', 404);
        }

        $data = $validator->validated();

        // The logged-in user
        $user = Auth::user();

        // Create or update rating
        $rating = CourseRating::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null,
            ]
        );

        return ResponseHelper::success($rating, 'Rating saved successfully', 201);
    }

    /**
     * Get all ratings for a course.
     */


    /**
     * @OA\Get(
     *     path="/courses/{id}/ratings",
     *     tags={"Courses"},
     *     summary="Get course ratings",
     *     description="Fetch all ratings for a specific course including reviewer details, average rating, and total reviews.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the course",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ratings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course ratings retrieved successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="course_name", type="string", example="Intro to Programming"),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.5),
     *                 @OA\Property(property="total_reviews", type="integer", example=12),
     *                 @OA\Property(property="ratings", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="name", type="string", example="John Doe")
     *                         ),
     *                         @OA\Property(property="rating", type="integer", example=5),
     *                         @OA\Property(property="review", type="string", example="Great course!"),
     *                         @OA\Property(property="date", type="string", format="date", example="2025-09-17")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function index($courseId)
    {
        $course = Course::with('ratings.user')->find($courseId);

        if (!$course) {
            return ResponseHelper::error([], 'Course not found', 404);
        }

        $ratings = $course->ratings->map(function ($rating) {
            return [
                'id' => $rating->id,
                'user' => [
                    'id' => $rating->user->id,
                    'name' => $rating->user->first_name . ' ' . $rating->user->last_name,
                ],
                'rating' => $rating->rating,
                'review' => $rating->review,
                'date' => $rating->created_at->toDateString(),
            ];
        });

        return ResponseHelper::success([
            'course_id' => $course->id,
            'course_name' => $course->name,
            'average_rating' => round($course->ratings()->avg('rating'), 2),
            'total_reviews' => $course->ratings()->count(),
            'ratings' => $ratings,
        ], 'Course ratings retrieved successfully');
    }
}
