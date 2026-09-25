<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Curso;

class CursoPolicy {
    
    public function before(User $user): ?bool {
        return (int) $user->tipo_user === 0 ? true : null;
    }
    
    public function gerenciar(User $user, Curso $curso): bool{
        return $curso->gestores()->whereKey($user->id)->exists();
    }
}
