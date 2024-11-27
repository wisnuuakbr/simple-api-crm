<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginSuccess()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message', 'token']);
    }

    public function testLoginFailureInvalidCredentials()
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)->assertJson(['message' => 'email or password incorrect!']);
    }

    public function testLogoutSuccess()
    {
        $user = User::factory()->create();

        // Simulate logging in the user
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/logout',
            [],
            [
                'Authorization' => "Bearer $token",
            ],
        );

        $response->assertStatus(200)->assertJson(['message' => 'success']);
    }

    public function testUpdateProfileSuccess()
    {
        // Create a user and manually assign the 'manager' role
        $user = User::factory()->create();
        $user->role = 'manager';
        $user->save();

        // Generate a JWT token for the user
        $token = JWTAuth::fromUser($user);

        // Send the request with the Bearer token in the headers
        $response = $this->putJson(
            '/api/profile',
            [
                'name' => 'Updated Name',
                'phone' => '081234567890',
                'address' => 'Updated Address',
                'email' => $user->email,
            ],
            [
                'Authorization' => "Bearer $token",
            ],
        );

        // Assert the response status is 200 OK
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'data' => ['name', 'phone', 'address']])
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function testUpdateProfileValidationError()
    {
        // Create a user and set their role manually (replace this with your role system)
        $user = User::factory()->create();
        $user->role = 'manager';
        $user->save();

        // Generate a JWT token for the user
        $token = JWTAuth::fromUser($user);

        // Send the request with invalid data (missing required fields)
        $response = $this->putJson(
            '/api/profile',
            [
                'name' => '',
                'address' => '',
            ],
            [
                'Authorization' => "Bearer $token",
            ],
        );

        // Assert the response status is 422 Unprocessable Entity for validation errors
        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'address']);
    }

    public function testUpdateProfileWithoutToken()
    {
        // Send the request without a token (unauthorized)
        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Name',
            'phone' => '081234567890',
            'address' => 'Updated Address',
            'email' => 'updated@example.com',
        ]);

        // Assert the response status is 401 Unauthorized
        $response->assertStatus(401);
    }

    public function testUpdateProfileWithoutRole()
    {
        // Create a user without the 'manager' role
        $user = User::factory()->create();
        $user->role = 'employee';

        // Generate a JWT token for the user
        $token = JWTAuth::fromUser($user);

        // Send the request with the Bearer token but the user doesn't have the 'manager' role
        $response = $this->putJson(
            '/api/profile',
            [
                'name' => 'Updated Name',
                'phone' => '081234567890',
                'address' => 'Updated Address',
                'email' => 'updated@example.com',
            ],
            [
                'Authorization' => "Bearer $token",
            ],
        );

        // Assert the response status is 403 Forbidden
        $response->assertStatus(403);
    }
}
