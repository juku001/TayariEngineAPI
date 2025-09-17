<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Category;
use App\Models\Course;
use App\Models\JobPost;
use Illuminate\Http\Request;

class PopularController extends Controller
{

    /**
     * @OA\Get(
     *     path="/popular/categories",
     *     tags={"Miscellaneous"},
     *     summary="Get top 5 popular categories",
     *     description="Returns the 5 categories with the highest number of courses.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Top 5 popular categories with course count"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Programming"),
     *                     @OA\Property(property="slug", type="string", example="programming"),
     *                     @OA\Property(property="count", type="integer", example=25, description="Number of courses in this category")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function category()
    {
        $categories = Category::withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->take(5)
            ->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->courses_count,
            ];
        });

        return ResponseHelper::success($data, 'Top 5 popular categories with course count');
    }





    /**
     * @OA\Get(
     *     path="/popular/courses",
     *     tags={"Miscellaneous"},
     *     summary="Get top 3 popular courses",
     *     description="Fetches the top 3 courses ranked by number of enrollments, with their names, enrollment counts, and average ratings.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Top 3 popular courses"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Intro to Programming"),
     *                     @OA\Property(property="enrollments", type="integer", example=150),
     *                     @OA\Property(property="avg_rating", type="number", format="float", example=4.5)
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function courses()
    {
        $courses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(3)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'enrollments' => $course->enrollments_count,
                    'avg_rating' => $course->avg_rating,
                ];
            });

        return ResponseHelper::success($courses, 'Top 3 popular courses');
    }



    /**
     * @OA\Get(
     *     path="/jobs/trending",
     *     tags={"Employer"},
     *     summary="Get trending jobs",
     *     description="Returns the latest 4 trending jobs with status, location, salary range, and positions available.",
     *     @OA\Response(
     *         response=200,
     *         description="List of trending jobs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Trending jobs"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="title", type="string", example="Software Developer"),
     *                     @OA\Property(property="status", type="string", example="Hot"),
     *                     @OA\Property(property="location", type="string", example="Remote"),
     *                     @OA\Property(property="salary", type="string", example="TZS 40,000,000 - TZS 80,000,000"),
     *                     @OA\Property(property="positions_available", type="integer", example=25)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error: Something went wrong"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function trending()
    {
        $jobs = JobPost::with('jobPostType', 'company')
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($job) {

                $statusLabel = 'Open';
                $daysToDeadline = $job->deadline ? now()->diffInDays($job->deadline, false) : null;

                if ($daysToDeadline !== null && $daysToDeadline <= 3) {
                    $statusLabel = 'Closing Soon';
                } elseif ($job->is_hot ?? false) { // optional hot flag
                    $statusLabel = 'Hot';
                }

                return [
                    'title' => $job->title,
                    'status' => $statusLabel,
                    'location' => $job->is_remote ? 'Remote' : "{$job->city}, {$job->country}",
                    'salary' => $job->salary_min && $job->salary_max
                        ? "{$job->currency} " . number_format($job->salary_min) . " - {$job->currency} " . number_format($job->salary_max)
                        : 'Negotiable',
                    'positions_available' => $job->applications_count,
                ];
            });

        return ResponseHelper::success($jobs, 'Trending jobs');
    }


}
