<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CursoService {
    public function criar(array $dados, User $criador): Curso {
        return DB::transaction(function () use ($dados, $criador) {
            $curso = Curso::create($dados);

            $curso->gestores()->attach($criador->id, ['criador' => true]);

            return $curso;
        });
    }
}
