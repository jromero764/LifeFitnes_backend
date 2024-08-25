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
        $fechaFiltro = Carbon::now()->subDays(30)->format('Y-m-d');

        $array = Usuarios::select('usuarios.id', 'usuarios.ci', 'usuarios.estado', 'clientes.id_usuarios', 'trans.FechaTransaccion')
            ->join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->join(DB::raw("(SELECT id_clientes, MAX(FechaTransaccion) as FechaTransaccion 
                     FROM transacciones
                     WHERE productos_id = 1
                     GROUP BY id_clientes
                     HAVING MAX(FechaTransaccion) <= '$fechaFiltro') as trans"), 'clientes.id', '=', 'trans.id_clientes')
            ->where('usuarios.estado', 1)
            ->pluck('usuarios.id')
            ->toArray();

        $arrayCount = Usuarios::select('usuarios.id', 'usuarios.ci', 'usuarios.estado', 'clientes.id_usuarios', 'trans.FechaTransaccion')
            ->join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->join(DB::raw("(SELECT id_clientes, MAX(FechaTransaccion) as FechaTransaccion 
                     FROM transacciones
                     WHERE productos_id = 1
                     GROUP BY id_clientes
                     HAVING MAX(FechaTransaccion) <= '$fechaFiltro') as trans"), 'clientes.id', '=', 'trans.id_clientes')
            ->where('usuarios.estado', 1)
            ->pluck('usuarios.id')
            ->count();

        Usuarios::whereIn('id', $array)->update(['estado' => 0]);

        Log::info('Se actualizó el estado de ' . $arrayCount . ' usuarios correctamente');
        // Log::info( $arrayCount . ''. $array);

        $this->info('Mensaje registrado en el log.');
        return 0;
    }
}
