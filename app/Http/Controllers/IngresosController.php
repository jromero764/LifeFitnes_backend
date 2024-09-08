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

            // Encuentra el cliente relacionado
            $findClient = Clientes::where('id_usuarios', $findUser->id)->first();

            if ($findClient) {
                // Obtiene el campo vencimiento_pase
                $vencimiento_pase = $findClient->vencimiento_pase;

                // Si vencimiento_pase está vacío, responde con falso
                if (!$vencimiento_pase) {
                    return response()->json(false);
                }

                // Calcula la fecha de vencimiento y la fecha actual
                $fechaVencimiento = Carbon::parse($vencimiento_pase)->startOfDay();
                $fechaActual = Carbon::now()->startOfDay();

                // Calcula la diferencia en días
                $DiasDeCuota = $fechaActual->diffInDays($fechaVencimiento, false);

                // Verifica si la fecha de vencimiento ha pasado
                if ($DiasDeCuota < 1) {
                    return response()->json(false);
                }

                //Realiza el registro del ingreso
                $this->RegistroDeIngreso($findClient->id);

                // Responde con la información de vencimiento
                return response()->json([
                    "vencimiento_pase" => $fechaVencimiento->format('Y-m-d'),
                    "hoy" => $fechaActual->format('Y-m-d'),
                    'DiasDeCuota' => $DiasDeCuota,
                    'Nombre' => $findUser->Nombre,
                    'Apellido' => $findUser->Apellido,
                    'mensaje' => "El pase está vigente. Quedan $DiasDeCuota días para el vencimiento."
                ]);
            } else {
                //<----------------------------------------PERFIL ADMINISTRADOR------------------------------------------------------->//
                //Aca se chequeo que es admin y devuelve true

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
