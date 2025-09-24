<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = ['date', 'house_id'];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
