<?php

namespace App\Models;

use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'thumbnail', 'description', 'category', 'slug'])]
class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    /**
     * @return HasMany<UserAction, $this>
     */
    public function userActions(): HasMany
    {
        return $this->hasMany(UserAction::class);
    }
}
