<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    /** @test */
    public function login_requires_email_and_password(): void
    {
        // Act
        $response = $this->postJson('/api/auth/login', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function user_can_get_their_profile(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    /** @test */
    public function user_cannot_access_profile_without_token(): void
    {
        // Act
        $response = $this->getJson('/api/auth/me');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);

        // Verify token is deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
