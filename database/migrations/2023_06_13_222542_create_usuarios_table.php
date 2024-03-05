<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->integer('ci');
            $table->primary('ci');
            $table->string('Nombre');
            $table->string('Apellido')->nullable();
            $table->date('FechaDeNacimiento')->nullable();
            $table->string('Telefono')->nullable();
            $table->string('Mail')->nullable();
            $table->set('Sexo', ['Hombre', 'Mujer','Sin definir'])->nullable();
            $table->boolean('estado')->nullable();
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
        Schema::dropIfExists('usuarios');
    }
}
