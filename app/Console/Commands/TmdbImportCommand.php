<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Services\TmdbService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('tmdb:import {--pages=1 : Number of TMDB result pages to import (20 movies per page)}')]
#[Description('Import popular movies from TMDB into the contents table')]
class TmdbImportCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (blank(config('services.tmdb.key'))) {
            $this->error('TMDB_API_KEY is not set in your .env file. Get one at https://www.themoviedb.org/settings/api');

            return self::FAILURE;
        }

        $tmdb = app(TmdbService::class);
        $pages = (int) $this->option('pages');
        $imported = 0;
        $skipped = 0;

        foreach (range(1, $pages) as $page) {
            $this->info("Fetching page {$page} from TMDB...");

            try {
                $movies = $tmdb->popularMovies($page);
            } catch (RuntimeException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            foreach ($movies as $movie) {
                $content = Content::firstOrCreate(
                    ['slug' => $movie['slug']],
                    $movie,
                );

                $content->wasRecentlyCreated ? $imported++ : $skipped++;
            }
        }

        $this->info("Imported {$imported} new movies. Skipped {$skipped} already existing.");

        return self::SUCCESS;
    }
}
