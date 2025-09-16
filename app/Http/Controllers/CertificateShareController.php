<?php


namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\CertificateShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateShareController extends Controller
{


    /**
     * @OA\Post(
     *     path="/certificates/share",
     *     tags={"Certificates"},
     *     summary="Share a certificate on LinkedIn",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"certificate_id"},
     *             @OA\Property(property="certificate_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate shared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Certificate shared successfully!"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="certificate_id", type="integer", example=2),
     *                 @OA\Property(property="platform", type="string", example="linkedin"),
     *                 @OA\Property(property="share_url", type="string", example="https://www.linkedin.com/feed/update/...")
     *             )
     *         )
     *     )
     * )
     */

    public function share(Request $request)
    {
        $validated = $request->validate([
            'certificate_id' => 'required|exists:certificates,id',
        ]);

        $user = Auth::user();


        $linkedInPostUrl = 'https://www.linkedin.com/feed/update/...'; // response from LinkedIn


        $share = CertificateShare::create([
            'user_id' => $user->id,
            'certificate_id' => $validated['certificate_id'],
            'platform' => 'linkedin',
            'share_url' => $linkedInPostUrl,
        ]);

        event(new \App\Events\CertificateShared($user));

        return ResponseHelper::success($share, 'Certificate shared successful');
    }
}
