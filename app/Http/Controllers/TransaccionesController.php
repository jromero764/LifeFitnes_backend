<?php

namespace App\Http\Controllers;

use App\Models\Transacciones;
use App\Models\Usuarios;
use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class TransaccionesController extends Controller
{
     
    public function create(request $request)
    {
        //Registro de cuota
        
        if($request->productos_id===1){
            $Transaccion = new Transacciones();
            $Transaccion->id_administrador=$request->id_administrador;
            $Transaccion->id_clientes=$request->id_clientes;
            $Transaccion->productos_id=$request->productos_id;
            $Transaccion->HoraTransaccion=Carbon::now()->format('H:i:s');
            $Transaccion->FechaTransaccion=Carbon::now()->format('Y:m:d');
            $Transaccion->TipoDeTransaccion=$request->TipoDeTransaccion;
            $Transaccion->save();
            $Cuota=Usuarios::where('ci','=',$request->ci_cliente)
            ->update([
                'estado' => 1
            ]);
        }else{
            //Registro del resto de las transacciones
            $id_cliente=null;
            if($request->id_clientes!=null){
                $id_cliente = Usuarios::where('ci', $request->id_clientes)->first()->cliente->id;
            }

            $Transaccion = new Transacciones();
            $Transaccion->id_administrador=$request->id_administrador;
            $Transaccion->id_clientes=$id_cliente? $id_cliente:null;
            $Transaccion->productos_id=$request->productos_id;
            $Transaccion->HoraTransaccion=Carbon::now()->format('H:i:s');
            $Transaccion->FechaTransaccion=Carbon::now()->format('Y:m:d');
            $Transaccion->TipoDeTransaccion=$request->TipoDeTransaccion;
            $Transaccion->save();
        }
        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se registro la transaccion con exito",
        ]);
        
    }
 
    


    public function show($Opcion,$Fecha)
    {
//--------------------------------------------------------------------------Esta consulta me devuelve las transacciones venta por Fecha-------------------------------------------------------------------------->
        
        if($Opcion=='Venta'){
            
            $Ventas = Transacciones::with('cliente.Usuario', 'administrador.Usuarios','producto')
                ->where('TipoDeTransaccion', '=', $Opcion)
                ->where('FechaTransaccion', '=', $Fecha)
                ->orderBy('HoraTransaccion', 'ASC')
                ->get();
            return response()->json($Ventas);

        }
        //Esta consulta me devuelve el efectivo del dia de las ventas
        if($Opcion=='VentasDelDia'){
            $VentasDelDia=DB::table('transacciones')
            ->join('productos','transacciones.productos_id','=','productos.id')
            ->whereDate('FechaTransaccion',$Fecha)
            ->where('TipoDeTransaccion','=','Venta')
            ->sum('productos.precioventa');
            return response()->json($VentasDelDia);

            
            
        }
//------------------------------------------------------------------------Esta consulta me devuelve las transacciones compra por Fecha-------------------------------------------------------------------------->
      
        if($Opcion=='Compra'){
            $Compras = Transacciones::with('cliente.Usuario', 'administrador.Usuarios')
                ->where('TipoDeTransaccion', '=', $Opcion)
                ->where('FechaTransaccion', '=', $Fecha)
                ->get();
            return response()->json($Compras);
        

        }
        //Esta consulta me devuelve el efectivo del dia de las ventas
        if($Opcion=='ComprasDelDia'){
            $ComprasDelDia=DB::table('transacciones')
            ->join('productos','transacciones.productos_id','=','productos.id')
            ->whereDate('FechaTransaccion',$Fecha)
            ->where('TipoDeTransaccion','=','Compra')
            ->sum('productos.preciocompra');
            return response()->json($ComprasDelDia);    
        }
        $Transacciones=DB::table('transacciones')
        ->paginate(10);
        return response()->json($Transacciones);
    }
    //Obtener historial de cuotas del usuario
    public function ConsultarCuotas($ci){
        if($ci!=0){
            $idCliente = Clientes::whereHas('usuario', function ($query) use ($ci) {
                $query->where('ci', $ci);
                })->value('id');

        $Cuotas=DB::table('transacciones')
        ->select('transacciones.id', 'HoraTransaccion', 'FechaTransaccion', 'id_administrador', 'ci')
        ->join('administradores', 'administradores.id', '=', 'transacciones.id_administrador')
        ->join('usuarios', 'usuarios.id', '=', 'administradores.id_usuarios')
        ->where('id_clientes', '=', $idCliente)
        ->where('productos_id', '=', 1)
        ->get();
        return response()->json($Cuotas);
        }
    }
   
    public function update(Request $request, Transacciones $transacciones)
    {
        //
    }
    public function destroy($id)
    {
        //
        $Transaccion = Transacciones::findOrFail($id);
        $Transaccion->delete();
        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se elimino la transaccion con exito",
        ]);
    }


public function deshabilitarUsuariosCuotaVencida(){
    //return response()->json(["response"=>"true"]);
    $clientes = Clientes::with('usuario')->get();

    // Actualizar el estado de cada usuario
    foreach ($clientes as $cliente) {
        // Verificar si la última transacción es mayor a 31 días
        $ultimaTransaccion = $cliente->transacciones()->orderBy('FechaTransaccion', 'desc')->first();
        if ($ultimaTransaccion && Carbon::parse($ultimaTransaccion->FechaTransaccion)->diffInDays(Carbon::now()) > 31) {
            // Actualizar el estado del usuario
            $cliente->usuario->estado = 0;
            $cliente->usuario->save();
            return response()->json(["response"=>"true"]);
        }
    }
}
}
