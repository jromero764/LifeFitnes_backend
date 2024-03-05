<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuotas extends Model
{
    use HasFactory;
    public function Cuota(){
        return $this->belongsTo(Productos::class, "productos_id", "id");
    }
}
