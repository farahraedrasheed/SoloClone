<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)->assertJson(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_guest_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_protected_route_without_json_accept_header(): void
    {
        $response = $this->get('/api/cart');

        $response->assertStatus(401);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('test-token');
        $tokenId = $accessToken->accessToken->id;

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => "Bearer {$accessToken->plainTextToken}",
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }
}
