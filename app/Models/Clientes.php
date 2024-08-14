<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;
    public function Usuario()
    {
        return $this->belongsTo(Usuarios::class, "id_usuarios", "id");
    }
    public function transacciones()
    {
        return $this->hasMany(Transacciones::class, 'id_clientes', 'id');
    }
}
