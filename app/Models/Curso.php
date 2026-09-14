<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Curso extends Model {
    protected $fillable = ['titulo', 'descricao', 'publicado'];

    public function gestores() {
        return $this->belongsToMany(User::class, 'curso_gestor', 'curso_id', 'gestor_id');
    }

}
