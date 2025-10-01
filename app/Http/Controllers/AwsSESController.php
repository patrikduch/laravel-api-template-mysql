<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Emails",
 *     description="Email sending endpoints (AWS SES)."
 * )
 */
class AwsSESController extends Controller
{
    /**
     * Send a minimal plaintext email "Hello world" via configured mailer (AWS SES).
     *
     * @OA\Post(
     *     path="/aws-ses/hello",
     *     operationId="sendHelloEmail",
     *     tags={"Emails"},
     *     summary="Send 'Hello world' email",
     *     description="Sends a simple plaintext email with subject 'Test: Hello world' to the provided recipient.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Recipient payload",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"to"},
     *             @OA\Property(
     *                 property="to",
     *                 type="string",
     *                 format="email",
     *                 example="recipient@example.com",
     *                 description="Recipient email address"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email successfully queued/sent",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ok", type="boolean", example=true),
     *             @OA\Property(property="to", type="string", example="recipient@example.com"),
     *             @OA\Property(property="message", type="string", example="Email sent via AWS SES mailer.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The to field must be a valid email address."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="to",
     *                     type="array",
     *                     @OA\Items(type="string", example="The to field must be a valid email address.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error while sending email",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ok", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Sending failed")
     *         )
     *     )
     * )
     */
    public function sendHello(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => ['required', 'email'],
        ]);

        try {
            Mail::raw('Hello world', function ($message) use ($data) {
                $message->to($data['to'])
                    ->subject('Test: Hello world');
            });

            return response()->json([
                'ok'      => true,
                'to'      => $data['to'],
                'message' => 'Email sent via AWS SES mailer.',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('AWS SES send failed', ['error' => $e->getMessage()]);

            return response()->json([
                'ok'    => false,
                'error' => 'Sending failed',
            ], 500);
        }
    }
}
