<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    protected $fillable = ['question_id', 'answer_text'];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'vote_answer', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

