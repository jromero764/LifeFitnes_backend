<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Administradores;

class Usuarios extends Model
{
    use HasFactory;
    public function cliente()
    {
        return $this->hasOne(Clientes::class, 'id_usuarios', 'id');
    }

    public function administrador()
    {
        return $this->hasOne(Administradores::class, 'id_usuarios');
    }
}
