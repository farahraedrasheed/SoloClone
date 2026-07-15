<?php

namespace Tests\Feature\Console;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TmdbImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fails_gracefully_when_the_api_key_is_missing(): void
    {
        config(['services.tmdb.key' => null]);

        $this->artisan('tmdb:import')
            ->assertFailed();

        $this->assertDatabaseCount('contents', 0);
    }

    public function test_it_imports_movies_from_tmdb_into_contents(): void
    {
        config(['services.tmdb.key' => 'fake-key']);

        Http::fake([
            '*/movie/popular*' => Http::response([
                'results' => [
                    ['id' => 1, 'title' => 'Fake Movie One', 'overview' => 'Desc one', 'poster_path' => '/one.jpg'],
                    ['id' => 2, 'title' => 'Fake Movie Two', 'overview' => 'Desc two', 'poster_path' => null],
                ],
            ], 200),
        ]);

        $this->artisan('tmdb:import', ['--pages' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('contents', 2);
        $this->assertDatabaseHas('contents', [
            'title' => 'Fake Movie One',
            'slug' => 'fake-movie-one-1',
            'category' => 'Movie',
        ]);
    }

    public function test_it_does_not_duplicate_movies_already_imported(): void
    {
        config(['services.tmdb.key' => 'fake-key']);

        Content::factory()->create(['slug' => 'fake-movie-one-1']);

        Http::fake([
            '*/movie/popular*' => Http::response([
                'results' => [
                    ['id' => 1, 'title' => 'Fake Movie One', 'overview' => 'Desc one', 'poster_path' => '/one.jpg'],
                ],
            ], 200),
        ]);

        $this->artisan('tmdb:import')->assertSuccessful();

        $this->assertDatabaseCount('contents', 1);
    }

    public function test_it_fails_when_tmdb_returns_an_error(): void
    {
        config(['services.tmdb.key' => 'fake-key']);

        Http::fake([
            '*/movie/popular*' => Http::response(['status_message' => 'Invalid API key'], 401),
        ]);

        $this->artisan('tmdb:import')->assertFailed();
    }
}
