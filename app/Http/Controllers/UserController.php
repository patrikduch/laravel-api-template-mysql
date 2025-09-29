<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *   name="Users",
 *   description="User registration & profiles"
 * )
 */
class UserController extends Controller
{
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
     *       required={"name","email","password"},
     *       @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *       @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password", minLength=8, example="secret123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User created",
     *     @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(
     *         property="user",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="email", type="string", example="john@example.com"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-29T10:15:30.000000Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-29T10:15:30.000000Z")
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
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'user'         => $user,
            ], Response::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            \Log::error('User registration failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Registration failed. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
