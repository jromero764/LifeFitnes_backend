<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Productos;
use App\Models\Usuarios;
use App\Models\Clientes;
use App\Models\Administradores;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CuotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Productos::create([
            'Nombre'            => 'Cuota',
            'Descripcion' => 'Cuota de Socio',
            'PrecioCompra'=> 0,
            'PrecioVenta'=>1700,
            'Stock'=>0
        ]);
        $data = [
            "ci"=>"12345678",
            "Nombre"=>"No Cliente",
            "Opcion"=>"Administrador"
        ];

        $response = Http::post('127.0.0.1:8000/api/Usuarios', $data);
        
    }
}
