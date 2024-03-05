<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->foreignId('productos_id')
            ->constrained('productos')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->integer('usuarios_ci'); // Columna para la clave foránea

            $table->foreign('usuarios_ci')
                ->references('ci') // Columna de referencia en la tabla 'categorias'
                ->on('usuarios')
                ->onDelete('cascade');
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
        Schema::dropIfExists('cuotas');
    }
}
