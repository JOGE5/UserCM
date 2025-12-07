<?php

namespace App\Policies;

use App\Models\Foro;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForoPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Foro $foro)
    {
        return $user->getKey() === $foro->ID_Creador;
    }

    public function delete(User $user, Foro $foro)
    {
        return $user->getKey() === $foro->ID_Creador;
    }
}
