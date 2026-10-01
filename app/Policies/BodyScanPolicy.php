<?php

namespace App\Policies;

use App\Models\BodyScan;
use App\Models\User;

class BodyScanPolicy
{
    public function delete(User $user, BodyScan $scan): bool
    {
        return $scan->user_id === $user->id;
    }
}
