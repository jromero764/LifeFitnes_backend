<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administradores extends Model
{
    use HasFactory;

    public function Usuarios(){
        return $this->belongsTo(Usuarios::class, "id_usuarios", "id");
    }
    public function transacciones()
    {
        return $this->hasMany(Transacciones::class, 'id_administrador');
    }   
}
