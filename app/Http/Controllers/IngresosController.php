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
    public function Login(request $request){
        //<----------------------------------------PERFIL SOCIO------------------------------------------------------->//
                $Cliente=Clientes::where('usuarios_ci',$request->ci)->first();
                if($Cliente!=null){
                    //Se calcula cuantos dias de cuota le queda y retorna
                    $Cuota=DB::table('transacciones')
                    ->where('socios_ci','=',$request->ci)
                    ->latest()
                    ->first();
                    if(empty($Cuota)){return response()->json(false);}
                    $FechaActual = Carbon::now();
                    $FechaHabilitado = Carbon::parse($Cuota->FechaTransaccion);
                    $DiasDeCuota = $FechaActual->diffInDays($FechaHabilitado);
                    $DiasDeCuota = 30-$DiasDeCuota;
    
                    if($DiasDeCuota<1){
                        return response()->json(false);
                    }
                    //Realiza el registro del ingreso
                    $this->RegistroDeIngreso($request->ci);
                    return response()->json([
                        "Nombre"=>$Cliente->Usuarios->Nombre,
                        "Apellido"=>$Cliente->Usuarios->Apellido,
                        "DiasDeCuota"=>$DiasDeCuota]);
                }
                //<----------------------------------------PERFIL ADMINISTRADOR------------------------------------------------------->//
                $Administrador=Administradores::where('usuarios_ci',$request->ci)->first();
                if($Administrador!=null){
                    //Aca se chequeo que es admin y devuelve true
                    if($request->Administrador==true){
                                if(password_verify($request->password,$Administrador->password)){
                                    return response()->json(["respuesta"    => 'Se valida el ingreso']);
                                }
                                return response()->json(["respuesta"    => 'Contraseña incorrecta']);
                        }

                    return response()->json(["respuesta"    => true]);
                }
        //Se valido que la cedula ingresada no pertenece a usuario ni administrador
        return response()->json(["respuesta"    => 'Usuario no existe']);
       }

    public function RegistroDeIngreso($ci)
    {
        
        $Ingreso=new Ingresos();
        $Ingreso->usuarios_ci=$ci;
        $Ingreso->HoraIngreso=Carbon::now()->format('H:i:s');
        $Ingreso->FechaIngreso=Carbon::now()->format('Y:m:d');

        $Ingreso->save();
    }

    
    public function show($ci)
    {
        //
        if($ci!=0){
        $Ingresos=DB::table('ingresos')
        ->join('usuarios','ingresos.usuarios_ci','=','usuarios.ci')
        ->select('usuarios_ci','HoraIngreso','FechaIngreso','Nombre','Apellido')
        ->where('ingresos.usuarios_ci','=',$ci)
        ->get();
        return response()->json($Ingresos);
        }
        $Ingresos=DB::table('ingresos')
        ->join('usuarios','ingresos.usuarios_ci','=','usuarios.ci')
        ->select('usuarios_ci','HoraIngreso','FechaIngreso','Nombre','Apellido')
        ->paginate(10);
        return response()->json($Ingresos);

    }

   
}
