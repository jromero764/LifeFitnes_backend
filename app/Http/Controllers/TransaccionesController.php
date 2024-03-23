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
        //
        $Transaccion = new Transacciones();
        $Transaccion->id_administrador=$request->ci;
        $Transaccion->id_clientes=$request->socios_ci;
        $Transaccion->productos_id=$request->productos_id;
        $Transaccion->HoraTransaccion=Carbon::now()->format('H:i:s');
        $Transaccion->FechaTransaccion=Carbon::now()->format('Y:m:d');
        $Transaccion->TipoDeTransaccion=$request->TipoDeTransaccion;
        $Transaccion->save();
        if($request->productos_id===1){
            $Cuota=Usuarios::where('ci','=',$request->socios_ci)
            ->update([
                'estado' => 1
            ]);
        }
        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se registro la transaccion con exito",
        ]);
        
    }
 
    


    public function show($Opcion,$Fecha)
    {
//--------------------------------------------------------------------------Esta consulta me devuelve las transacciones venta por Fecha-------------------------------------------------------------------------->
        //id-Vendedor-Cliente-Producto-Hora-Fecha-Precio
        if($Opcion=='Venta'){
            $Ventas=DB::table('transacciones')
            ->join('productos','transacciones.productos_id','=','productos.id')
            ->join('usuarios as u1','transacciones.usuarios_ci','=','u1.ci')
            ->join('usuarios as u2','transacciones.socios_ci','=','u2.ci')
            ->where('TipoDeTransaccion','=',$Opcion)
            ->where('FechaTransaccion','=',$Fecha)
            ->select('transacciones.id','u1.Nombre as Vendedor','u2.ci as CI','u2.Nombre as Nombre','u2.Apellido as Apellido','productos.nombre as Producto','transacciones.HoraTransaccion','transacciones.FechaTransaccion','productos.PrecioVenta as Precio')
            ->get();
            return response()->json($Ventas);

        }
        //Esta consulta me devuelve el efectivo del dia de las ventas
        if($Opcion=='VentasDelDia'){
            $VentasDelDia=DB::table('transacciones')
            ->join('productos','transacciones.productos_id','=','productos.id')
            ->whereDate('FechaTransaccion',$Fecha)
            ->sum('productos.precioventa');
            return response()->json($VentasDelDia);    
        }
//------------------------------------------------------------------------Esta consulta me devuelve las transacciones compra por Fecha-------------------------------------------------------------------------->
        //id-Vendedor-Cliente-Producto-Hora-Fecha-Precio
        if($Opcion=='Compra'){
            $Compras=DB::table('transacciones')
            ->join('productos','transacciones.productos_id','=','productos.id')
            ->join('usuarios as u1','transacciones.usuarios_ci','=','u1.ci')
            ->join('usuarios as u2','transacciones.socios_ci','=','u2.ci')
            ->where('TipoDeTransaccion','=',$Opcion)
            ->where('FechaTransaccion','=',$Fecha)
            ->select('transacciones.id','u1.Nombre as Vendedor','u2.ci as CI','u2.Nombre as Nombre','u2.Apellido as Apellido','productos.nombre as Producto','transacciones.HoraTransaccion','transacciones.FechaTransaccion','productos.PrecioCompra as Precio')
            ->get();
            return response()->json($Compras);

        }
        //Esta consulta me devuelve el efectivo del dia de las ventas
        if($Opcion=='ComprasDelDia'){
            $ComprasDelDia=DB::table('transacciones')
            ->join('productos','transacciones.productos_id','=','productos.id')
            ->whereDate('FechaTransaccion',$Fecha)
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
        ->select('id','HoraTransaccion','FechaTransaccion')
        ->where('id_clientes','=',$idCliente)
        ->where('productos_id','=',1)
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
}
