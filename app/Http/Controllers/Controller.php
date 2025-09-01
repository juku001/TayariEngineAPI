<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Tayari Engine API",
 *     version="1.0.0",
 *     description="This is the official documentation for the Tayari Engine API.It provides all endpoints available for Learners, Instructors, Employers , Program Coordinators and Others.Use this documentation as a guide to integrate or test the Tayari system.",
 *     @OA\Contact(
 *         name="Tayari Support Team",
 *         email="support@Tayari.co.tz"
 *     )
 * )
 *
 * 
 * @OA\Server(
 *     url="http://localhost:1234/api",
 *     description="Test Server"
 * )
 * 
 * 
* @OA\Server(
 *     url="http://143.198.57.242/api",
 *     description="Live Server"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Use a valid bearer token",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * ),
 * @OA\Response(
 *     response=401,
 *     description="Unauthorized",
 *     @OA\JsonContent(
 *         @OA\Property(property="status", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Unauthorized"),
 *         @OA\Property(property="code", type="integer", example=401)
 *     )
 * ),
 * @OA\Response(
 *     response=422,
 *     description="Unprocessable Content",
 *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 * ),
 * @OA\Response(
 *     response=500,
 *     description="Unprocessable Content",
 *     @OA\JsonContent(
 *         @OA\Property(property="status", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Error : Something went wrong"),
 *         @OA\Property(property="code", type="integer", example=500),
 *     )
 * ),
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   title="User",
 *   description="User model schema",
 *   @OA\Property(property="id", type="integer", example=2),
 *   @OA\Property(property="first_name", type="string", example="Juma"),
 *   @OA\Property(property="middle_name", type="string", nullable=true, example=null),
 *   @OA\Property(property="last_name", type="string", example="Kujellah"),
 *   @OA\Property(property="email", type="string", format="email", example="jumakassim89@gmail.com"),
 *   @OA\Property(property="mobile", type="string", nullable=true, example=null),
 *   @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-08-21T17:26:14.000000Z"),
 *   @OA\Property(property="profile_pic", type="string", nullable=true, example=null),
 *   @OA\Property(property="date_of_birth", type="string", format="date", nullable=true, example=null),
 *   @OA\Property(property="provider", type="string", example="email"),
 *   @OA\Property(property="google_id", type="string", nullable=true, example=null),
 *   @OA\Property(property="created_by", type="integer", nullable=true, example=null),
 *   @OA\Property(property="deleted_by", type="integer", nullable=true, example=null),
 *   @OA\Property(property="status", type="string", example="active", enum={"active","inactive","suspended","deleted"}),
 *   @OA\Property(
 *       property="archive",
 *       type="object",
 *       nullable=true,
 *       example=null,
 *       description="Archived user details before deletion"
 *   ),
 *   @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-21T17:21:55.000000Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-21T17:26:14.000000Z")
 * ),
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     @OA\Property(
 *         property="status",
 *         type="boolean",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Failed to validate fields"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         example=422
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties=@OA\Schema(
 *             type="array",
 *             @OA\Items(type="string", example="The field is required.")
 *         ),
 *         example={
 *             "key1": {"some field on the request might be required."},
 *             "key2": {"The key 2 is not well formated."}
 *         }
 *     )
 * ),
 *
* @OA\Tag(
 *     name="Authentication",
 *     description="APIs related to user authentication like login, logout, registration, password updates, etc."
 * ),
 * @OA\Tag(
 *     name="Aptitude",
 *     description="APIs related to the learner aptitude tests. They are used right after the learner finishes registration during oboarding."
 * ),
 * @OA\Tag(
 *     name="Users",
 *     description="User related API's, specifically designed for the admin to manage its users."
 * ),
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard API's for all the users. Showing stats and some important data."
 * ),
 * @OA\Tag(
 *     name="Courses",
 *     description="Courses API for everybody to use it. Courses List, Adding New Courses and Updating the existing ones."
 * ),
* @OA\Tag(
 *     name="Certificates",
 *     description="These API endpoints are responsible for getting the learner awarded certificates, downloading,sharing and displaying them for the learner."
 * ),
 * @OA\Tag(
 *     name="Quizzes",
 *     description="The API endpoint related to Quizzes that a learner takes."
 * ),
  * @OA\Tag(
 *     name="Instructor",
 *     description="The API endpoint related to Instructor, from applications to getting approved/rejectd."
 * ),
   * @OA\Tag(
 *     name="Employer",
 *     description="Employers related APIs. From the registration, Team Management and Course Assignments."
 * ),
 * 
 * @OA\Tag(
 *     name="Projects",
 *     description="Project APIs for the Employers. Viewing projects, sending proposals, accept and deny for proposals. "
 * ),
 * @OA\Tag(
 *     name="Admin",
 *     description="Endpoints for managing administrative actions such as approving/rejecting instructor applications, overseeing users, and handling system-level operations."
 * ),
 * @OA\Tag(
 *     name="Skills",
 *     description="Skills resource APIs."
 * ),
  * @OA\Tag(
 *     name="Categories",
 *     description="Categories resource APIs."
 * ),
 * @OA\Tag(
 *     name="Miscellaneous",
 *     description="Endpoints that don't fit into a specific module or category. This tag groups together utility, helper, and general-purpose APIs."
 * ),


 **/

abstract class Controller
{
  //
}
