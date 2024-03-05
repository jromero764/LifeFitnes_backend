<?php

namespace App\Http\Controllers;

use App\Models\Productos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ProductosController extends Controller
{
    public function create(request $request)
    {
        //
        
        $Producto=new Productos();
        $Producto->Nombre=$request->Nombre;
        $Producto->Descripcion=$request->Descripcion;
        $Producto->PrecioCompra=$request->PrecioCompra;
        $Producto->PrecioVenta=$request->PrecioVenta;
        $Producto->Stock=$request->Stock;
        $Producto->FechaIngreso=$request->FechaIngreso;
        $Producto->Lote=$request->Lote;
        $Producto->FechaVencimiento=$request->FechaVencimiento;
        $Producto->save();

        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se registro el producto con exito",
        ]);
    }

    public function show($id)
    {
        //
        if($id!=0){
            $Productos=DB::table('productos')
            ->where('productos.id','=',$id)
            ->first();
            return response()->json($Productos);
        }
        $Productos=DB::table('productos')
        ->orderBy('Nombre','asc')
        ->paginate(10);
        return response()->json($Productos);
    }
    public function update(Request $request, $idProducto)
    {
        //
        $Producto = Productos::findOrFail($idProducto);
        $Producto->Nombre=$request->Nombre;
        $Producto->Descripcion=$request->Descripcion;
        $Producto->PrecioCompra=$request->PrecioCompra;
        $Producto->PrecioVenta=$request->PrecioVenta;
        $Producto->Stock=$request->Stock;
        $Producto->FechaIngreso=$request->FechaIngreso;
        $Producto->Lote=$request->Lote;
        $Producto->FechaVencimiento=$request->FechaVencimiento;
        $Producto->save();

        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se modifico el producto con exito",
        ]);
    }
    public function destroy($idProducto)
    {
        //
        $Producto = Productos::findOrFail($idProducto);
        $Producto->delete();
        return response()->json([
            "codigo"    => "200",
            "respuesta" => "Se elimino el producto con exito",
        ]);
    }
    public function ConsultarCuota($ci){
        $Cuota = DB::table('productos')
        ->Join('cuotas','productos_id','=','id')
        ->where('usuarios_ci','=',$ci)
        ->first();
        if(empty($Cuota)){return response()->json(["Respuesta"    => "Usuario sin cuota"]);}
        return response()->json($Cuota);
    }
    public function ConsultarProducto(){
        $Cuota = DB::table('productos')
        ->Join('cuotas','productos_id','=','id')
        ->where('usuarios_ci','=',$ci)
        ->first();
        if(empty($Cuota)){return response()->json(["Respuesta"    => "Usuario sin cuota"]);}
        return response()->json($Cuota);
    }

    public function GestionStock($id,$cantidad){
            $Producto=Productos::findOrFail($id);
            if($cantidad < $Producto->Stock){
                $Producto=Productos::where('id','=',$id)
                ->update([
                    'Stock' => $Producto->Stock-$cantidad
                ]);
                return true;
            }
            return response()->json("Producto sin Stock");
        
    }
  public function CompraStock($id,$cantidad){
    $Producto=Productos::findOrFail($id);
    $Producto=Productos::where('id','=',$id)
    ->update([
        'Stock' => $Producto->Stock+$cantidad
    ]);
    return true;
  }
}
