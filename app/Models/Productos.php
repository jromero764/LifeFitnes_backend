<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    use HasFactory;

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'productos_id');
    }
    
}
