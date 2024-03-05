<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministradoresTable extends Migration
{
   
    public function up()
    {
        Schema::create('administradores', function (Blueprint $table) {
            $table->integer('usuarios_ci'); // Columna para la clave foránea

            $table->foreign('usuarios_ci')
                ->references('ci') // Columna de referencia en la tabla 'categorias'
                ->on('usuarios')
                ->onDelete('cascade');
            $table->String('password');
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('administradores');
    }
}
