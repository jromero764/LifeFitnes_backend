<?php

namespace App\Http\Controllers;

use App\Models\Ingresos;
use Illuminate\Http\Request;
use App\Models\Usuarios;
use App\Models\Clientes;
use App\Models\Administradores;
use App\Models\Transacciones;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IngresosController extends Controller
{
    public function Login(request $request)
    {
        //<----------------------------------------PERFIL SOCIO------------------------------------------------------->//
        $findUser = Usuarios::where('ci', $request->ci)->first();
        if ($findUser) {
            $findClient = Clientes::where('id_usuarios', $findUser->id)->first();

            if ($findClient != null) {
                //Se calcula cuantos dias de cuota le queda y retorna

                $Cuotas = Transacciones::where('id_clientes', '=', $findClient->id) // ULTIMAS 2
                    ->orderBy('FechaTransaccion', 'desc')
                    ->limit(2)  // Obtener las dos últimas transacciones
                    ->get();

                if ($Cuotas->isEmpty()) {
                    return response()->json(false);
                }

                if ($Cuotas[0]->productos_id == 1) {
                    $plan = 30;
                } elseif ($Cuotas[0]->productos_id == 15) {
                    $plan = 60;
                } else {
                    $plan = 90;
                }


                $ultimaCuota = $Cuotas->first();  // Última transacción

                // Verificar si existe una anteúltima cuota
                $anteultimaCuota = $Cuotas->skip(1)->first();

                $FechaActual = Carbon::now();
                $FechaHabilitado = Carbon::parse($ultimaCuota->FechaTransaccion); // Última transacción

                if ($anteultimaCuota) {
                    // Si hay una anteúltima cuota, calcular la fecha esperada y los días restantes
                    $FechaEsperada = Carbon::parse($anteultimaCuota->FechaTransaccion)->addDays($plan); // Fecha esperada de pago (30 días después de la anteúltima)

                    // Calcular la diferencia de días
                    $DiasDeCuota = $FechaActual->diffInDays($FechaHabilitado); // Días entre la última transacción y la fecha actual
                    $DiasDeCuota = $plan - $DiasDeCuota; // Días restantes antes de completar los 30 días

                    // Verificar si el cliente pagó anticipado
                    if ($ultimaCuota->FechaTransaccion < $FechaEsperada) {
                        $diasAnticipados = $FechaEsperada->diffInDays($ultimaCuota->FechaTransaccion);
                        $DiasDeCuota += $diasAnticipados; // Sumar los días a favor
                    }
                    if ($DiasDeCuota < $plan) {
                        $DiasDeCuota = $plan;
                    }
                } else {
                    // Si no hay anteúltima cuota, asignar un valor predeterminado
                    $FechaActual = Carbon::now();
                    $FechaHabilitado = Carbon::parse($ultimaCuota->FechaTransaccion);
                    $DiasDeCuota = $FechaActual->diffInDays($FechaHabilitado);
                    $DiasDeCuota = $plan - $DiasDeCuota;
                }

                if ($DiasDeCuota < 1) {
                    return response()->json(false);
                }

                // Realiza el registro del ingreso
                $this->RegistroDeIngreso($findClient->id);

                return response()->json([
                    "Nombre" => $findUser->Nombre,
                    "Apellido" => $findUser->Apellido,
                    "DiasDeCuota" => $DiasDeCuota

                ]);
            } else {
                $Administrador = Administradores::where('id_usuarios', $findUser->id)->first();
                if ($request->Administrador == true) {
                    if (password_verify($request->password, $Administrador->password)) {
                        return response()->json(["respuesta"    => 'Se valida el ingreso', "Usuario" => $Administrador, "Perfil" => $findUser]);
                    }
                    return response()->json(["respuesta"    => 'Contraseña incorrecta']);
                }

                return response()->json(["respuesta"    => true]);
            }
        }

        //<----------------------------------------PERFIL ADMINISTRADOR------------------------------------------------------->//

        //Aca se chequeo que es admin y devuelve true


        //Se valido que la cedula ingresada no pertenece a usuario ni administrador
        return response()->json(["respuesta"    => 'Usuario no existe']);
    }

    public function RegistroDeIngreso($id)
    {

        $Ingreso = new Ingresos();
        $Ingreso->id_clientes = $id;
        $Ingreso->HoraIngreso = Carbon::now()->format('H:i:s');
        $Ingreso->FechaIngreso = Carbon::now()->format('Y:m:d');

        $Ingreso->save();
    }

    public function show($ci)
    {
        //
        if ($ci != 0) {
            $idCliente = Clientes::whereHas('usuario', function ($query) use ($ci) {
                $query->where('ci', $ci);
            })->value('id');
            if ($idCliente != null) {
                $Ingresos = DB::table('ingresos')
                    ->select('id', 'HoraIngreso', 'FechaIngreso')
                    ->where('id_clientes', '=', $idCliente)
                    ->get();
                return response()->json($Ingresos);
            }
        }
    }
}
