<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ActualizarEstadoUsuarios extends Command
{
    protected $signature = 'usuarios:actualizar-estado';
    protected $description = 'Actualizar el estado de los usuarios según ciertas condiciones';

    public function handle()
    {
        // Obtener todos los clientes
        $clientes = Clientes::with('usuario')->get();

        // Actualizar el estado de cada usuario
        foreach ($clientes as $cliente) {
            // Verificar si la última transacción es mayor a 31 días
            $ultimaTransaccion = $cliente->transacciones()->orderBy('FechaTransaccion', 'desc')->first();
            if ($ultimaTransaccion && Carbon::parse($ultimaTransaccion->FechaTransaccion)->diffInDays(Carbon::now()) > 31) {
                // Actualizar el estado del usuario
                $cliente->usuario->estado = false;
                $cliente->usuario->save();
            }
        }

        $this->info('Estado de los usuarios actualizado exitosamente.');
    }
}
