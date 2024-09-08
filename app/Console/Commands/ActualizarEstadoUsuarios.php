<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Usuarios;
use Carbon\Carbon;

class ActualizarEstadoUsuarios extends Command
{
    protected $signature = 'estado:usuarios';
    protected $description = 'Actualizar el estado de los usuarios activos';

    public function handle()
    {
        $HOY = Carbon::now()->subDays(1)->format('Y-m-d');

        $array =
            Usuarios::join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->where(function ($query) use ($HOY) {
                $query->where('clientes.vencimiento_pase', '<', $HOY) // Filtra por vencimiento anterior a hoy
                    ->orWhereNull('clientes.vencimiento_pase');     // Incluye filas donde vencimiento_pase es NULL
            })
            ->pluck('clientes.id_usuarios')
            ->toArray();

        $arrayCount = Usuarios::join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->where(function ($query) use ($HOY) {
                $query->where('clientes.vencimiento_pase', '<', $HOY) // Filtra por vencimiento anterior a hoy
                    ->orWhereNull('clientes.vencimiento_pase');     // Incluye filas donde vencimiento_pase es NULL
            })
            ->pluck('clientes.id_usuarios')
            ->count();

        //! descomentar para actualiza la base de datos
        Usuarios::whereIn('id', $array)->update(['estado' => 0]);

        Log::info('Se actualizó el estado de ' . $arrayCount . ' usuarios correctamente');
        // Log::info( $arrayCount . ''. $array);

        $this->info('Mensaje registrado en el log.');
        return 0;
    }
}
