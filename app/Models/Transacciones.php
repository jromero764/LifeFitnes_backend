<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacciones extends Model
{
    use HasFactory;
    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id_clientes');
    }

    public function administrador()
    {
        return $this->belongsTo(Administradores::class, 'id_administrador');
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'productos_id');
    }
}
