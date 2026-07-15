<?php

namespace App\Http\Controllers\Api;

class WatchlistController extends UserActionController
{
    protected function actionType(): string
    {
        return 'watchlist';
    }
}
