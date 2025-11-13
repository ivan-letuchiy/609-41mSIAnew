<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Flat extends Model
{
    protected $fillable = ['apartment_number', 'area', 'house_id'];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class, 'flat_owner')->withPivot('ownership_percentage');
    }
}
