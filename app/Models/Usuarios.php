<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuarios extends Model
{
    use HasFactory;
    public function cliente()
    {
        return $this->hasOne(Clientes::class, 'id_usuarios');
    }
    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'id_usuarios');
    }
}
