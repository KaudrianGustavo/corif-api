<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model {
    protected $fillable =['titulo', 'descricao', 'ordem'];

    public function curso() {
        return $this->belongsTo(Curso::class);
    }
}
