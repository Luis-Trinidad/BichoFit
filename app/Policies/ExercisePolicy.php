<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    /**
     * El catálogo global es de solo lectura; los ejercicios
     * personalizados solo los modifica su dueño.
     */
    public function update(User $user, Exercise $exercise): bool
    {
        return $exercise->user_id !== null && $exercise->user_id === $user->id;
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $this->update($user, $exercise);
    }
}
