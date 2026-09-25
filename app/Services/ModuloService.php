<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\Modulo;

class ModuloService {
    public function criar(Curso $curso, array $dados): Modulo {
        $proximaOrdem = ($curso->modulos()->reorder()->max('ordem') ?? 0) + 1; 
        return $curso->modulos()->create([
            ...$dados,
            'ordem' => $proximaOrdem,
        ]);
    }
}
