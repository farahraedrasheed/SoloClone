<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TmdbService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $imageBaseUrl,
    ) {}

    /**
     * Fetch a page of popular movies from TMDB, normalized for our `contents` table.
     *
     * @return Collection<int, array<string, string>>
     */
    public function popularMovies(int $page = 1): Collection
    {
        $response = Http::baseUrl($this->baseUrl)
            ->get('/movie/popular', [
                'api_key' => $this->apiKey,
                'page' => $page,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("TMDB request failed: {$response->status()} {$response->body()}");
        }

        return collect($response->json('results', []))
            ->map(fn (array $movie) => $this->normalize($movie));
    }

    /**
     * @param  array<string, mixed>  $movie
     * @return array<string, string>
     */
    private function normalize(array $movie): array
    {
        $title = $movie['title'] ?? 'Untitled';

        return [
            'title' => $title,
            'thumbnail' => $movie['poster_path']
                ? $this->imageBaseUrl.$movie['poster_path']
                : '',
            'description' => $movie['overview'] ?? '',
            'category' => 'Movie',
            'slug' => str($title)->slug().'-'.$movie['id'],
        ];
    }
}
