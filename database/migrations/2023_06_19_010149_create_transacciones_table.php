<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaccionesTable extends Migration
{
    
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();

            $table->integer('usuarios_ci'); // Columna para la clave foránea
            $table->foreign('usuarios_ci')
                ->references('ci') // Columna de referencia en la tabla 'categorias'
                ->on('usuarios')
                ->onDelete('cascade');

                
            $table->integer('socios_ci')->nullable(); // Columna para la clave foránea
            $table->foreign('socios_ci')
                ->references('ci') // Columna de referencia en la tabla 'categorias'
                ->on('usuarios')
                ->onDelete('cascade');

            $table->foreignId('productos_id')
                ->constrained('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->time('HoraTransaccion');
            $table->date('FechaTransaccion');
            $table->set('TipoDeTransaccion',['Compra','Venta']);        
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacciones');
    }
}
