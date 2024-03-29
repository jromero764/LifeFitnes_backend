<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;
    public function Usuarios(){
        return $this->belongsTo(Usuarios::class, "usuarios_ci", "ci");
    }
}
