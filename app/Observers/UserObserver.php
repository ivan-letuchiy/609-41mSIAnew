<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function updated(User $user): void
    {
        if ($user->isDirty('name') && $user->owner) {
            $user->owner->full_name = $user->name;
            $user->owner->save();
        }
    }

    public function created(User $user): void
    {
        if ($user->owner) {
            $user->owner->full_name = $user->name;
            $user->owner->save();
        }
    }

    public function deleted(User $user): void
    {
        if ($user->owner) {
            $user->owner()->delete();
        }
    }
}
