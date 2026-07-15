<?php

namespace App\Http\Controllers\Api;

class CartController extends UserActionController
{
    protected function actionType(): string
    {
        return 'cart';
    }
}
