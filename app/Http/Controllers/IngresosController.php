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

                $Cuotas = Transacciones::where('id_clientes', '=', $findClient->id) //* ULTIMAS 2 CUOTAS
                    ->orderBy('FechaTransaccion', 'desc')
                    ->limit(2)
                    ->get();

                if ($Cuotas->isEmpty()) {
                    return response()->json(false);
                }

                $ultimaCuota = $Cuotas->first();

                $anteultimaCuota = $Cuotas->skip(1)->first();


                $fechaVencimiento = null;
                $diferenciaDias = null;
                $DiasDeCuota = null;

                if ($anteultimaCuota) {
                    $fechaVencimiento = Carbon::parse($anteultimaCuota->FechaTransaccion)->addDays(30);
                    $diferenciaDias = Carbon::parse($ultimaCuota->FechaTransaccion)->diffInDays($fechaVencimiento, false);
                }

                //* 2) SOLO EXISTE UNA CUOTA O HAY MAS DE 30 DIAS DE DIFERENCIA ENTRE AMBAS
                if (!$anteultimaCuota || Carbon::parse($ultimaCuota->FechaTransaccion)->diffInDays(Carbon::parse($anteultimaCuota->FechaTransaccion)) > 30) {

                    $FechaActual = Carbon::now();
                    $FechaHabilitado = Carbon::parse($ultimaCuota->FechaTransaccion);
                    $FechaVencimiento = $FechaHabilitado->copy()->addDays(30);
                    $DiasDeCuota = $FechaActual->diffInDays($FechaVencimiento, false);

                    //! 1) CUOTA VENCIDA
                    if ($DiasDeCuota < 1) {
                        return response()->json(false);
                    }

                    return response()->json([
                        'CASO 1 CUOTA o MAS DE 30 DIAS DE DIFERENCIA' => !$anteultimaCuota ? 'SOLO HAY UNA CUOTA' : 'HAY MÁS DE 30 DÍAS DE DIFERENCIA ENTRE AMBAS CUOTAS',
                        'anteultimaCuota' => $anteultimaCuota ? $anteultimaCuota->FechaTransaccion : null,
                        'vencido' => $fechaVencimiento ? $fechaVencimiento->toDateString() : null,
                        'ultimaCuota' => $ultimaCuota->FechaTransaccion,
                        'proximoVencimiento' => $FechaHabilitado->addDays(29)->toDateString(),
                        'diferenciaDias' => $diferenciaDias,
                        'DiasDeCuota' => $DiasDeCuota,
                        'Nombre' => $findUser->Nombre,
                        'Apellido' => $findUser->Apellido,
                    ]);
                }

                //? 3) HAY 2 CUOTAS CON 30 DIAS O MENOS DE DIFERENCIA
                return response()->json([
                    'CASO 2 CUOTAS CON PAGO ANTICIPADO' => true,
                    'anteultimaCuota' => $anteultimaCuota->FechaTransaccion,
                    'vencido' => $fechaVencimiento ? $fechaVencimiento->toDateString() : null,
                    'ultimaCuota' => $ultimaCuota->FechaTransaccion,
                    'diferenciaDias' => $diferenciaDias,
                    'DiasDeCuota' => 30 + $diferenciaDias,
                    'proximoVencimiento' => Carbon::parse($ultimaCuota->FechaTransaccion)
                        ->addDays(30 + $diferenciaDias)
                        ->toDateString(),
                    'Nombre' => $findUser->Nombre,
                    'Apellido' => $findUser->Apellido,
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
