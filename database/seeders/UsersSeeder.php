<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Usuarios;
use App\Models\Clientes;
use Illuminate\Support\Facades\Http;
use Faker\Factory as Faker;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 200; $i++) {
            $data=[
                'ci' => $faker->unique()->randomNumber(8),
                'Nombre' => $faker->firstName,
                'Apellido' => $faker->lastName,
                'FechaDeNacimiento' => $faker->date,
                'Telefono' => $faker->phoneNumber,
                'Mail' => $faker->unique()->email,
                'Sexo' => $faker->randomElement(['Hombre', 'Mujer']),
                "Opcion"=>"Cliente"
            ];
            $response = Http::post('127.0.0.1:8000/api/Usuarios', $data);
        }
        // for ($i = 1; $i <= 200; $i++) {
        //     DB::table('usuarios')->insert([
        //         'ci' => $faker->unique()->randomNumber(8),
        //         'Nombre' => $faker->firstName,
        //         'Apellido' => $faker->lastName,
        //         'FechaDeNacimiento' => $faker->date,
        //         'Telefono' => $faker->phoneNumber,
        //         'Mail' => $faker->unique()->email,
        //         'Sexo' => $faker->randomElement(['Hombre', 'Mujer']),
        //     ]);
        // }
    }
}
