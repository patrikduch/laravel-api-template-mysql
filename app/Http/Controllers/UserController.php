<?php

namespace App\Http\Controllers;

use App\Contracts\UserServiceInterface;
use App\DTOs\Users\Requests\RegisterUserDTO;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *   name="Users",
 *   description="User registration & profiles"
 * )
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Register a new user.
     *
     * Creates a user account and immediately issues a Bearer token (Sanctum).
     *
     * @OA\Post(
     *   path="/register",
     *   tags={"Users"},
     *   summary="Register",
     *   operationId="registerUser",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"first_name","last_name","email","password"},
     *       @OA\Property(property="first_name", type="string", maxLength=255, example="John"),
     *       @OA\Property(property="last_name", type="string", maxLength=255, example="Doe"),
     *       @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password", minLength=8, example="secret123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User created",
     *     @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string", example="1|xxxxx..."),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(
     *         property="user",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="first_name", type="string", example="John"),
     *         @OA\Property(property="last_name", type="string", example="Doe"),
     *         @OA\Property(property="email", type="string", example="john@example.com"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-29T10:15:30+00:00"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-29T10:15:30+00:00")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation failed",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Validation failed"),
     *       @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\AdditionalProperties(
     *           type="array",
     *           @OA\Items(type="string")
     *         ),
     *         example={
     *           "email": {"The email has already been taken."},
     *           "password": {"The password field must be at least 8 characters."}
     *         }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Server error",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Registration failed. Please try again.")
     *     )
     *   )
     * )
     */
    public function store(RegisterUserRequest $request): JsonResponse
    {
        try {
            // Convert validated request to DTO
            $dto = RegisterUserDTO::fromRequest($request);

            // Service returns the response DTO
            $responseDto = $this->userService->register($dto);

            // DTO enforces the response structure
            return response()->json(
                $responseDto->toArray(),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Registration failed. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
