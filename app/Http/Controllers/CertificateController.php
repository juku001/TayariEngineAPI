<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{




    /**
     * @OA\Get(
     *     path="/certificates/{code}",
     *     operationId="viewCertificatePage",
     *     tags={"Certificates"},
     *     summary="View certificate page",
     *     description="Opens a certificate page in HTML view for a given certificate code. 
     *                  Unlike other API endpoints, this does not return JSON but renders a Blade template (`certificate.blade.php`).",
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="Unique certificate code",
     *         @OA\Schema(type="string", example="TAYARI-1755792727110-STAGE123")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="HTML page displaying the certificate",
     *         @OA\MediaType(
     *             mediaType="text/html"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Certificate not found (renders 404 page)"
     *     )
     * )
     */

    public function create($code)
    {

        $certificate = Certificate::with('user', 'course')
            ->where('certificate_code', $code)
            ->first();

        if (!$certificate) {
            abort(404, 'Certificate not found');
        }

        // return $certificate;

        return view('certificate', compact('certificate'));

    }



    /**
     * @OA\Get(
     *     path="/certificates/{code}/download",
     *     operationId="downloadCertificate",
     *     tags={"Certificates"},
     *     summary="Download certificate as PDF",
     *     description="Generates a PDF version of the certificate and triggers download. 
     *                  Unlike JSON API responses, this endpoint returns a binary PDF file.",
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="Unique certificate code",
     *         @OA\Schema(type="string", example="TAYARI-1755792727110-STAGE123")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PDF certificate file",
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Certificate not found"
     *     )
     * )
     */

    public function download($code)
    {
        $certificate = Certificate::with('user', 'course')
            ->where('certificate_code', $code)
            ->first();

        if (!$certificate) {
            abort(404, 'Certificate not found');
        }

        $pdf = Pdf::loadView('certificate', compact('certificate'))
            ->setPaper('a4', 'portrait');

        $filename = 'Certificate-' . $certificate->certificate_code . '.pdf';

        return $pdf->download($filename);
    }





    /**
     * @OA\Get(
     *     path="/certificates",
     *     operationId="getLearnerCertificates",
     *     tags={"Certificates"},
     *     summary="Get learner's certificates",
     *     description="Returns a list of certificates belonging to the authenticated learner. 
     *                  Requires authentication (Bearer token).",
     *
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of learner certificates",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of learner certificates"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="course_id", type="integer", example=3),
     *                     @OA\Property(property="user_id", type="integer", example=2),
     *                     @OA\Property(property="certificate_type", type="string", example="completion"),
     *                     @OA\Property(property="certificate_code", type="string", example="TAYARI-1755792727110-STAGE123"),
     *                     @OA\Property(property="issued_at", type="string", format="date", example="2025-08-28"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="first_name", type="string", example="Juma"),
     *                         @OA\Property(property="last_name", type="string", example="Kujellah"),
     *                         @OA\Property(property="email", type="string", example="jumakassim89@gmail.com")
     *                     ),
     *                     @OA\Property(property="course", type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="Organic Chemistry"),
     *                         @OA\Property(property="cover_image", type="string", example="thumbnails/GiGazPMb4NtRMzNcGAvAVeoAPHmrAH7ME5vYBrkN.png"),
     *                         @OA\Property(property="certificate_type", type="string", example="completion")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated (token missing or invalid)"
     *     )
     * )
     */

    public function index()
    {
        $authId = auth()->user()->id;

        $certificates = Certificate::
            with('user', 'course')->
            where('user_id', $authId)->get();


        return ResponseHelper::success(
            $certificates,
            'List of learner certificates'
        );
    }










    /**
     * @OA\Get(
     *     path="/certificates/{id}",
     *     operationId="getCertificate",
     *     tags={"Certificates"},
     *     summary="Get certificate details",
     *     security={{ "bearerAuth":{} }},
     *     description="Retrieve a certificate by its ID, including user and course details.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Certificate details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Certificate details"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="course_id", type="integer", example=3),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="enrollment_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="certificate_type", type="string", example="completion"),
     *                 @OA\Property(property="certificate_code", type="string", example="TAYARI-1755792727110-STAGE123"),
     *                 @OA\Property(property="issued_at", type="string", format="date", example="2025-08-28"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="first_name", type="string", example="Juma"),
     *                     @OA\Property(property="last_name", type="string", example="Kujellah"),
     *                     @OA\Property(property="email", type="string", format="email", example="jumakassim89@gmail.com"),
     *                     @OA\Property(property="profile_pic", type="string", nullable=true, example=null),
     *                     @OA\Property(property="status", type="string", example="active")
     *                 ),
     *                 @OA\Property(
     *                     property="course",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="name", type="string", example="Organic Chemistry"),
     *                     @OA\Property(property="slug", type="string", example="organic-chemistry"),
     *                     @OA\Property(property="description", type="string", example="This is the organic chemistry topic course"),
     *                     @OA\Property(property="cover_image", type="string", example="thumbnails/GiGazPMb4NtRMzNcGAvAVeoAPHmrAH7ME5vYBrkN.png"),
     *                     @OA\Property(property="certificate_type", type="string", example="completion"),
     *                     @OA\Property(property="status", type="string", example="draft")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Certificate not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Certificate not found"),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *
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

    public function show($id)
    {
        $certificate = Certificate::with('user', 'course')->find($id);
        if ($certificate) {
            return ResponseHelper::success($certificate, 'Certificate details');
        }

        return ResponseHelper::error(
            [],
            "Certificated not found",
            404
        );
    }
}
