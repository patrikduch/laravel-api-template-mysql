<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\DTOs\Auth\Requests\LoginDTO;
use App\DTOs\Auth\Responses\AuthResponseDTO;
use App\DTOs\Users\Responses\UserResponseDTO;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *   name="Auth",
 *   description="Authentication flow (login, logout, me]"
 * )
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {
    }

    /**
     * Authenticate user and return access token.
     *
     * @OA\Post(
     *   path="/auth/login",
     *   tags={"Auth"},
     *   summary="Login",
     *   operationId="loginUser",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="secret123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Login successful",
     *     @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string", example="1|xxxxx..."),
     *       @OA\Property(property="token_type", type="string", example="Bearer")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Invalid credentials",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *           property="email",
     *           type="array",
     *           @OA\Items(type="string", example="The provided credentials are incorrect.")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation failed",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The email field is required."),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\AdditionalProperties(
     *           type="array",
     *           @OA\Items(type="string")
     *         )
     *       )
     *     )
     *   )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromRequest($request);
        $token = $this->authService->login($dto);

        if (!$token) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $responseDto = AuthResponseDTO::fromToken($token);

        return response()->json($responseDto->toArray(), Response::HTTP_OK);
    }

    /**
     * Logout the authenticated user.
     *
     * @OA\Post(
     *   path="/auth/logout",
     *   tags={"Auth"},
     *   summary="Logout",
     *   operationId="logoutUser",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Logout successful",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Successfully logged out")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ], Response::HTTP_OK);
    }

    /**
     * Get authenticated user details.
     *
     * @OA\Get(
     *   path="/auth/me",
     *   tags={"Auth"},
     *   summary="Get current user",
     *   operationId="getCurrentUser",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="User details",
     *     @OA\JsonContent(
     *       @OA\Property(property="id", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="John Doe"),
     *       @OA\Property(property="email", type="string", example="john@example.com"),
     *       @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-30T10:00:00Z"),
     *       @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-30T10:00:00Z")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $dto = UserResponseDTO::fromModel($request->user());

        return response()->json($dto, Response::HTTP_OK);
    }
}
