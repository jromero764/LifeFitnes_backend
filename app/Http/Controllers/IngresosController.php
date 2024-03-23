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
                $findUser=Usuarios::where('ci',$request->ci)->first();
                if($findUser){
                $findClient=Clientes::where('id_usuarios',$findUser->id)->first();
                if($findClient!=null){
                    //Se calcula cuantos dias de cuota le queda y retorna
                     
                        $Cuota=DB::table('transacciones')
                        ->where('id_clientes','=',$findClient->id)
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
                        $this->RegistroDeIngreso($findClient->id);
                        return response()->json([
                            "Nombre"=>$findUser->Nombre,
                            "Apellido"=>$findUser->Apellido,
                            "DiasDeCuota"=>$DiasDeCuota]);
                }else{
                    $Administrador=Administradores::where('id_usuarios',$findUser->id)->first();
                    if($request->Administrador==true){
                        if(password_verify($request->password,$Administrador->password)){
                            return response()->json(["respuesta"    => 'Se valida el ingreso',"Usuario"=>$Administrador]);
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
        
        $Ingreso=new Ingresos();
        $Ingreso->id_clientes=$id;
        $Ingreso->HoraIngreso=Carbon::now()->format('H:i:s');
        $Ingreso->FechaIngreso=Carbon::now()->format('Y:m:d');

        $Ingreso->save();
    }

    // $Ingresos=DB::table('ingresos')
    //     ->join('usuarios','ingresos.usuarios_ci','=','usuarios.ci')
    //     ->select('usuarios_ci','HoraIngreso','FechaIngreso','Nombre','Apellido')
    //     ->where('ingresos.usuarios_ci','=',$ci)
    //     ->get();
    public function show($ci)
    {
        //
        if($ci!=0){
        $idCliente = Clientes::whereHas('usuario', function ($query) use ($ci) {
                $query->where('ci', $ci);
                })->value('id');
                if($idCliente!=null){
                    $Ingresos=DB::table('ingresos')
                    ->select('id','HoraIngreso','FechaIngreso')
                    ->where('id_clientes','=',$idCliente)
                    ->get();
                    
           // dd('esto es la respuesta de ingresos',$Ingresos);
            return response()->json($Ingresos);
        }
        // 
        }
        // $Ingresos=DB::table('ingresos')
        // ->join('usuarios','ingresos.usuarios_ci','=','usuarios.ci')
        // ->select('usuarios_ci','HoraIngreso','FechaIngreso','Nombre','Apellido')
        // ->paginate(10);
        // return response()->json($Ingresos);

    }

   
}
