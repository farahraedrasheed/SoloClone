<?php

namespace Tests\Feature\Api;

use App\Models\Content;
use App\Models\User;
use App\Models\UserAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserActionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array<string>>
     */
    public static function endpoints(): array
    {
        return [
            'cart' => ['cart', 'cart'],
            'watchlist' => ['watchlist', 'watchlist'],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_guest_cannot_access_the_endpoint(string $uri, string $actionType): void
    {
        $content = Content::factory()->create();

        $this->getJson("/api/{$uri}")->assertStatus(401);
        $this->postJson("/api/{$uri}/{$content->id}")->assertStatus(401);
        $this->deleteJson("/api/{$uri}/{$content->id}")->assertStatus(401);
    }

    #[DataProvider('endpoints')]
    public function test_authenticated_user_can_add_content_and_it_persists(string $uri, string $actionType): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/{$uri}/{$content->id}");

        $response->assertStatus(201)
            ->assertJsonPath('content.id', $content->id);

        $this->assertDatabaseHas('user_actions', [
            'user_id' => $user->id,
            'content_id' => $content->id,
            'action_type' => $actionType,
        ]);
    }

    #[DataProvider('endpoints')]
    public function test_adding_the_same_content_twice_does_not_create_a_duplicate_row(string $uri, string $actionType): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson("/api/{$uri}/{$content->id}")->assertStatus(201);
        $this->actingAs($user, 'sanctum')->postJson("/api/{$uri}/{$content->id}")->assertStatus(201);

        $this->assertDatabaseCount('user_actions', 1);
    }

    #[DataProvider('endpoints')]
    public function test_adding_a_nonexistent_content_returns_404(string $uri, string $actionType): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/{$uri}/999999")
            ->assertStatus(404);
    }

    #[DataProvider('endpoints')]
    public function test_index_only_returns_the_authenticated_users_items(string $uri, string $actionType): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownContent = Content::factory()->create();
        $otherContent = Content::factory()->create();

        UserAction::create(['user_id' => $user->id, 'content_id' => $ownContent->id, 'action_type' => $actionType]);
        UserAction::create(['user_id' => $otherUser->id, 'content_id' => $otherContent->id, 'action_type' => $actionType]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/{$uri}");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $ownContent->id);
    }

    #[DataProvider('endpoints')]
    public function test_user_can_remove_their_own_item(string $uri, string $actionType): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        UserAction::create(['user_id' => $user->id, 'content_id' => $content->id, 'action_type' => $actionType]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/{$uri}/{$content->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_actions', [
            'user_id' => $user->id,
            'content_id' => $content->id,
            'action_type' => $actionType,
        ]);
    }

    #[DataProvider('endpoints')]
    public function test_removing_an_item_that_was_never_added_returns_404(string $uri, string $actionType): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/{$uri}/{$content->id}")
            ->assertStatus(404);
    }

    #[DataProvider('endpoints')]
    public function test_user_cannot_remove_another_users_item(string $uri, string $actionType): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $content = Content::factory()->create();

        UserAction::create(['user_id' => $owner->id, 'content_id' => $content->id, 'action_type' => $actionType]);

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/{$uri}/{$content->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('user_actions', [
            'user_id' => $owner->id,
            'content_id' => $content->id,
            'action_type' => $actionType,
        ]);
    }

    public function test_cart_and_watchlist_are_independent_for_the_same_content(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson("/api/cart/{$content->id}")->assertStatus(201);

        $watchlistResponse = $this->actingAs($user, 'sanctum')->getJson('/api/watchlist');
        $watchlistResponse->assertStatus(200)->assertJsonCount(0);

        $cartResponse = $this->actingAs($user, 'sanctum')->getJson('/api/cart');
        $cartResponse->assertStatus(200)->assertJsonCount(1);
    }
}
