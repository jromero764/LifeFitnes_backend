<?php

namespace Database\Factories;

use App\Models\Usuarios;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsuariosFactory extends Factory
{
    protected $model = Usuarios::class;

    public function definition()
    {
        return [
            'ci' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'Nombre' => $this->faker->firstName,
            'Apellido' => $this->faker->lastName,
            'Estado'=> false
            // Define otros atributos según tus necesidades
        ];
    }
}

