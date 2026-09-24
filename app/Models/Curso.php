<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Curso extends Model {
    protected $fillable = ['titulo', 'descricao'];
    protected $attributes = ['publicado' => false,];

    protected function casts(): array {
        return['publicado' => 'boolean'];
    }

    public function gestores() {
        return $this->belongsToMany(User::class, 'curso_gestor', 'curso_id', 'gestor_id')->withPivot('criador');
    }

}
