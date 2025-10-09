<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    /** @test */
    public function registration_requires_first_name(): void
    {
        $response = $this->postJson('/api/register', [
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    /** @test */
    public function registration_requires_last_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    /** @test */
    public function registration_requires_email(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_requires_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function registration_requires_valid_email(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_fails_with_duplicate_email(): void
    {
        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_requires_minimum_password_length(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function registered_user_password_is_hashed(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function registration_returns_user_data(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'user' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'full_name' => 'John Doe',
                    'email' => 'john@example.com',
                ]
            ]);
    }

    // ============================================
    // NEW EDGE CASES BELOW
    // ============================================

    /** @test */
    public function registration_fails_with_empty_first_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => '',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    /** @test */
    public function registration_fails_with_empty_last_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => '',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    /** @test */
    public function registration_fails_with_whitespace_only_first_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => '   ',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    /** @test */
    public function registration_fails_with_whitespace_only_last_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => '   ',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    /** @test */
    public function registration_fails_with_first_name_exceeding_max_length(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => str_repeat('a', 256), // 256 characters
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    /** @test */
    public function registration_fails_with_last_name_exceeding_max_length(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => str_repeat('a', 256), // 256 characters
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    /** @test */
    public function registration_accepts_first_name_at_max_length(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => str_repeat('a', 255), // Exactly 255 characters
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_last_name_at_max_length(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => str_repeat('a', 255), // Exactly 255 characters
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_fails_with_email_without_at_symbol(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johnexample.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_fails_with_email_without_domain(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_fails_with_email_exceeding_max_length(): void
    {
        $longEmail = str_repeat('a', 246) . '@example.com'; // Total 258 characters

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $longEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_accepts_email_with_plus_addressing(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john+test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_email_with_subdomain(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@mail.example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_email_with_numbers(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john123@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_fails_with_password_less_than_8_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'pass123', // 7 characters
            'password_confirmation' => 'pass123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function registration_accepts_password_exactly_8_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'pass1234', // Exactly 8 characters
            'password_confirmation' => 'pass1234',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_long_password(): void
    {
        $longPassword = str_repeat('a', 100);

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => $longPassword,
            'password_confirmation' => $longPassword,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_password_with_special_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'P@ssw0rd!#$%',
            'password_confirmation' => 'P@ssw0rd!#$%',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_password_with_unicode_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'pässwörd123',
            'password_confirmation' => 'pässwörd123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_fails_with_empty_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function registration_fails_with_only_spaces_in_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => '        ', // 8 spaces
            'password_confirmation' => '        ',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function registration_fails_with_null_values(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'password' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
    }

    /** @test */
    public function registration_fails_with_numeric_first_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 12345,
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name']);
    }

    /** @test */
    public function registration_fails_with_numeric_last_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 12345,
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    /** @test */
    public function registration_accepts_names_with_hyphens(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Mary-Jane',
            'last_name' => 'Watson-Parker',
            'email' => 'mary@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_names_with_apostrophes(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => "O'Brien",
            'last_name' => "D'Angelo",
            'email' => 'obrien@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_accepts_names_with_unicode_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'José',
            'last_name' => 'Müller',
            'email' => 'jose@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function registration_trims_whitespace_from_email(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => '  john@example.com  ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com', // Should be trimmed
        ]);
    }

    /** @test */
    public function two_users_cannot_register_simultaneously_with_same_email(): void
    {
        // First registration
        $response1 = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Second registration with same email
        $response2 = $this->postJson('/api/register', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'password' => 'password456',
            'password_confirmation' => 'password456',
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_response_does_not_include_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        // Ensure password is NOT in the response
        $responseData = $response->json();
        $this->assertArrayNotHasKey('password', $responseData['user'] ?? []);
    }

    /** @test */
    public function registration_sets_created_at_and_updated_at_timestamps(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertEquals($user->created_at, $user->updated_at);
    }
}
