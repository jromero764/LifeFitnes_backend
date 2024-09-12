<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('productos_id');
            $table->time('HoraTransaccion');
            $table->date('FechaTransaccion');
            $table->set('TipoDeTransaccion', ['Compra', 'Venta']);
            $table->timestamps();
            $table->integer('id_clientes')->nullable();
            $table->integer('id_administrador')->nullable();
            
            $table->foreign('id_administrador', 'fk_id_administrador_transaccion')->references('id')->on('administradores')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_clientes', 'fk_id_cliente_transaccion')->references('id')->on('clientes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('productos_id', 'transacciones_productos_id_foreign')->references('id')->on('productos')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transacciones');
    }
}
