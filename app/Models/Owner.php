<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Используем этот класс
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    protected $fillable = ['full_name', 'ownership_interest', 'user_id'];

    public function flats(): BelongsToMany
    {
        return $this->belongsToMany(Flat::class, 'flat_owner')->withPivot('ownership_percentage');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    // ИСПРАВЛЕНО: hasOne -> belongsTo
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
