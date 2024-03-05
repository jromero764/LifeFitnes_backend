<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->integer('usuarios_ci'); // Columna para la clave foránea

            $table->foreign('usuarios_ci')
                ->references('ci') // Columna de referencia en la tabla 'categorias'
                ->on('usuarios')
                ->onDelete('cascade');
            $table->time('HoraIngreso');
            $table->time('HoraSalida')->nullable();
            $table->date('FechaIngreso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingresos');
    }
}
