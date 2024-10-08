<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Models\Administradores;
use App\Models\Clientes;
use App\Models\Cuotas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class UsuariosController extends Controller
{
    //return response()->json(["respuesta"    => "200"]);
    
    public function create(Request $request)
    {
        //
            $Usuario = new Usuarios();
            $Usuario->ci=$request->ci;
            $Usuario->Nombre = $request->Nombre;
            $Usuario->Apellido = $request->Apellido;
            $fechaNacimiento = date('Y-m-d', strtotime($request->FechaDeNacimiento));
            $Usuario->FechaDeNacimiento = $fechaNacimiento;
            $Usuario->Telefono = $request->Telefono;
            $Usuario->Mail = $request->Mail;
            $Usuario->Sexo = $request->Sexo;
            $Usuario->estado=false;
            $Usuario->save();

        if ($request->Opcion==='Administrador'){return $this->AltaDeAdministrador($Usuario->id,$request->password);}
        if ($request->Opcion==='Cliente'){return $this->AltaDeCliente($Usuario->id);}
    }
    

  
    // public function show($ci)
    // {
    //     //
    //     if($ci!=0){
    //         $Usuarios=DB::table('usuarios')
    //         ->where('ci','=',$ci)
    //         ->first();
    //         return response()->json($Usuarios);
    //     }
    //     $Usuarios=DB::table('usuarios')
    //     ->get();
    //     return response()->json($Usuarios);
    // }

    public function show($idUsuario)
    {
        //Obtiene el usuario buscado
        if($idUsuario!=0){
            $usuario = Usuarios::whereHas('cliente', function ($query) use ($idUsuario) {
                $query->where('ci', $idUsuario);
            })->with('cliente')->first();
            return response()->json($usuario);
            
            $Usuarios=DB::table('usuarios')
            ->where('ci','=',$idUsuario)
            ->first();
        }
        //Obtiene todos los usuarios
        
        $Usuarios=DB::table('usuarios')
        ->get();
        return response()->json($Usuarios);
    }



  
    public function update(Request $request, $idUsuario)
    {
        //
        DB::table('usuarios')
            ->where('id', '=',$idUsuario)
            ->update([
            'ci'=>$request->ci,    
            'Nombre' => $request->Nombre,
            'Apellido' => $request->Apellido,
            'FechaDeNacimiento' => $request->FechaDeNacimiento,
            'Telefono' => $request->Telefono,
            'Mail' => $request->Mail,
            'Sexo' => $request->Sexo,
            "estado"=>$request->estado]);
            return response()->json([
                "codigo"    => "200",
                "respuesta" => "Se modifico el usuario con exito",
            ]);
    }

    public function destroy($idUsuario)
    {
        DB::table('usuarios')
            ->where('id', '=',$idUsuario)
            ->delete();

         return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se elimino el usuario con exito",
            
        ]);
    }
//hago cambio
    function AltaDeAdministrador($id,$password){
    $Administrador = new Administradores();
    $Administrador->id_usuarios=$id;
    $Administrador->password=bcrypt($password);
    $Administrador->save();
    return response()->json([
        "codigo"    => "200",
        "respuesta" => "Se ingreso usuario Administrador con exito",
    ]);
    
    }

    function AltaDeCliente($id){
        $Cliente = new Clientes();
        $Cliente->id_usuarios=$id;
        $Cliente->save();
        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se ingreso usuario Cliente con exito",
        ]);
        
        }

    function ChangePassword(request $request){
        try {
            Administradores::where('id', $request->id_administrador)
                ->update(['password' => bcrypt($request->password)]);
                return response()->json(["respuesta"=>"Se modifica la clave"]);
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }    
    public function checkServerStatus()
    {
        return response()->json([
            "status" => "online",
            "message" => "El servidor está en línea."
        ]);
    }
}

