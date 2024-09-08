<?php

namespace App\Http\Controllers;

use App\Models\Transacciones;
use App\Models\Usuarios;
use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransaccionesController extends Controller
{

    public function create(Request $request)
    {
        $response = []; // Variable para almacenar la respuesta final

        try {
            // Inicia la transacción de base de datos
            DB::beginTransaction();

            // Registro de cuota
            if (in_array($request->productos_id, [15, 30, 90, 180])) {
                $transaccion = new Transacciones();
                $transaccion->id_administrador = $request->id_administrador;
                $transaccion->id_clientes = $request->id_clientes;
                $transaccion->productos_id = $request->productos_id;
                $transaccion->HoraTransaccion = Carbon::now()->format('H:i:s');
                $transaccion->FechaTransaccion = Carbon::now()->format('Y-m-d');
                $transaccion->TipoDeTransaccion = $request->TipoDeTransaccion;
                $transaccion->save();

                // Actualiza el estado del usuario
                Usuarios::where('ci', $request->ci_cliente)->update(['estado' => 1]);

                // Obtener la fecha de vencimiento actual del cliente
                $cliente = Clientes::select('vencimiento_pase')
                    ->where('id', $request->id_clientes)
                    ->first();

                $diasASumar = 0;
                switch ($request->productos_id) {
                    case 30:
                        $diasASumar = 30;
                        break;
                    case 15:
                        $diasASumar = 15;
                        break;
                    case 90:
                        $diasASumar = 90;
                        break;
                    case 180:
                        $diasASumar = 180;
                        break;
                    default:
                        $diasASumar = 30; // Valor predeterminado en caso de que productos_id no coincida con ningún caso
                        break;
                }

                // NO
                // necesito ir a la tabla transacciones y traer el registro del id_clientes con producto_id 1 , 15, 16 o 17 pero el mas actual necesario para restar y saber si la cuota anterior ya paso el vencimiento





                //! SI NO HAY CLIENTE || SI VENCIMIENTO_PASE ESTA NULL || SI VENCIMIENTO_PASE ES MENOR QUE LA FECHA ACTUAL(pase vencido)
                if (!$cliente || !$cliente->vencimiento_pase || Carbon::parse($cliente->vencimiento_pase)->lt(Carbon::now())) {
                    // si no hay pagos o esta vencido hay 14,29,89,179 dias mas
                    $diasASumar = $diasASumar - 1;
                    // Si no tiene fecha de vencimiento o esta vencido, establece una nueva
                    $nuevaFechaVencimiento = Carbon::now()->addDays($diasASumar)->format('Y-m-d');

                    Clientes::where('id', $request->id_clientes)->update(['vencimiento_pase' => $nuevaFechaVencimiento]);

                    $response = [
                        'mensaje' => 'El campo vencimiento_pase ha sido actualizado.',
                        'id cliente' => $request->id_clientes,
                        'pago este id_producto' => $request->productos_id,
                        'nueva_fecha_vencimiento' => $nuevaFechaVencimiento,
                        'dias_sumados' => $diasASumar
                    ];
                } else {
                    // Si la fecha de vencimiento es válida, suma los días al vencimiento actual - al ser pago anticipado los dias son 15,30,90,180
                    $nuevaFechaVencimiento = Carbon::parse($cliente->vencimiento_pase)->addDays($diasASumar)->format('Y-m-d');

                    Clientes::where('id', $request->id_clientes)->update(['vencimiento_pase' => $nuevaFechaVencimiento]);

                    $response = [
                        'id cliente' => $request->id_clientes,
                        'id producto' => $request->productos_id,
                        'mensaje' => 'El campo vencimiento_pase ha sido actualizado.',
                        'nueva_fecha_vencimiento' => $nuevaFechaVencimiento,
                        'dias_sumados' => $diasASumar
                    ];
                }
            } else {
                // Registro del resto de las transacciones
                $id_cliente = null;
                if ($request->id_clientes != null) {
                    $usuario = Usuarios::where('ci', $request->id_clientes)->first();
                    if ($usuario && $usuario->cliente) {
                        $id_cliente = $usuario->cliente->id;
                    }
                }

                $transaccion = new Transacciones();
                $transaccion->id_administrador = $request->id_administrador;
                $transaccion->id_clientes = $id_cliente ? $id_cliente : null;
                $transaccion->productos_id = $request->productos_id;
                $transaccion->HoraTransaccion = Carbon::now()->format('H:i:s');
                $transaccion->FechaTransaccion = Carbon::now()->format('Y-m-d');
                $transaccion->TipoDeTransaccion = $request->TipoDeTransaccion;
                $transaccion->save();

                $response = [
                    "codigo"    => "200",
                    "respuesta" => "Se registró la transacción con éxito",
                    "cliente" => $transaccion->id_clientes,
                ];
            }

            // Confirma la transacción
            DB::commit();
        } catch (\Exception $e) {
            // Si ocurre un error, deshace la transacción
            DB::rollBack();

            // Loguea el error para facilitar el debugging
            Log::error('Error al registrar la transacción: ' . $e->getMessage());

            // Respuesta en caso de error
            return response()->json([
                'codigo' => '500',
                'error' => 'Ocurrió un error al procesar la transacción.',
                'detalle' => $e->getMessage() // Opcional, puede ocultarse en producción
            ], 500);
        }

        // response final
        return response()->json($response);
    }






    public function show($Opcion, $Fecha)
    {
        //--------------------------------------------------------------------------Esta consulta me devuelve las transacciones venta por Fecha-------------------------------------------------------------------------->

        if ($Opcion == 'Venta') {

            $Ventas = Transacciones::with('cliente.Usuario', 'administrador.Usuarios', 'producto')
                ->where('TipoDeTransaccion', '=', $Opcion)
                ->where('FechaTransaccion', '=', $Fecha)
                ->orderBy('HoraTransaccion', 'ASC')
                ->get();
            return response()->json($Ventas);
        }
        //Esta consulta me devuelve el efectivo del dia de las ventas
        if ($Opcion == 'VentasDelDia') {
            $VentasDelDia = DB::table('transacciones')
                ->join('productos', 'transacciones.productos_id', '=', 'productos.id')
                ->whereDate('FechaTransaccion', $Fecha)
                ->where('TipoDeTransaccion', '=', 'Venta')
                ->sum('productos.precioventa');
            return response()->json($VentasDelDia);
        }
        //------------------------------------------------------------------------Esta consulta me devuelve las transacciones compra por Fecha-------------------------------------------------------------------------->

        if ($Opcion == 'Compra') {
            $Compras = Transacciones::with('cliente.Usuario', 'administrador.Usuarios')
                ->where('TipoDeTransaccion', '=', $Opcion)
                ->where('FechaTransaccion', '=', $Fecha)
                ->get();
            return response()->json($Compras);
        }
        //Esta consulta me devuelve el efectivo del dia de las ventas
        if ($Opcion == 'ComprasDelDia') {
            $ComprasDelDia = DB::table('transacciones')
                ->join('productos', 'transacciones.productos_id', '=', 'productos.id')
                ->whereDate('FechaTransaccion', $Fecha)
                ->where('TipoDeTransaccion', '=', 'Compra')
                ->sum('productos.preciocompra');
            return response()->json($ComprasDelDia);
        }
        $Transacciones = DB::table('transacciones')
            ->paginate(10);
        return response()->json($Transacciones);
    }

    //Obtener historial de cuotas del usuario
    public function ConsultarCuotas($ci)
    {
        if ($ci != 0) {
            $idCliente = Clientes::whereHas('usuario', function ($query) use ($ci) {
                $query->where('ci', $ci);
            })->value('id');

            $Cuotas = DB::table('transacciones')
                ->select('transacciones.id', 'HoraTransaccion', 'FechaTransaccion', 'id_administrador', 'ci', 'productos_id')
                ->join('administradores', 'administradores.id', '=', 'transacciones.id_administrador')
                ->join('usuarios', 'usuarios.id', '=', 'administradores.id_usuarios')
                ->where('id_clientes', '=', $idCliente)
                ->whereIn('productos_id', [30, 15, 90, 180])
                ->orderByDesc('transacciones.FechaTransaccion') // Ordenar por FechaTransaccion de forma descendente
                ->orderByDesc('transacciones.HoraTransaccion') // Luego, ordenar por HoraTransaccion de forma descendente
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



    /* ------ FUNCIONES PARA PROBAR CODIGO ----- */

    public function VerData()
    {

        $fechaFiltro = Carbon::now()->subDays(29)->format('Y-m-d');

        $arrayUsuarios = Usuarios::select('usuarios.id', 'usuarios.ci', 'usuarios.estado', 'clientes.id_usuarios', 'trans.FechaTransaccion')
            ->join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->join(DB::raw("(SELECT id_clientes, MAX(FechaTransaccion) as FechaTransaccion 
                     FROM transacciones
                     WHERE productos_id = 30
                     GROUP BY id_clientes
                     HAVING MAX(FechaTransaccion) <= '$fechaFiltro') as trans"), 'clientes.id', '=', 'trans.id_clientes')
            ->where('usuarios.estado', 1)
            ->get();

        $arrayCount = Usuarios::select('usuarios.id', 'usuarios.ci', 'usuarios.estado', 'clientes.id_usuarios', 'trans.FechaTransaccion')
            ->join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->join(DB::raw("(SELECT id_clientes, MAX(FechaTransaccion) as FechaTransaccion 
                     FROM transacciones
                     WHERE productos_id = 30
                     GROUP BY id_clientes
                     HAVING MAX(FechaTransaccion) <= '$fechaFiltro') as trans"), 'clientes.id', '=', 'trans.id_clientes')
            ->where('usuarios.estado', 1)
            ->count();

        $arrayCountORIGINAL = Usuarios::select('usuarios.id', 'usuarios.ci', 'usuarios.estado', 'clientes.id_usuarios', 'trans.FechaTransaccion')
            ->join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->join(DB::raw("(SELECT id_clientes, MAX(FechaTransaccion) as FechaTransaccion 
                     FROM transacciones
                     WHERE productos_id = 30
                     GROUP BY id_clientes
                     HAVING MAX(FechaTransaccion) <= '$fechaFiltro') as trans"), 'clientes.id', '=', 'trans.id_clientes')
            ->where('usuarios.estado', 1)
            ->count();

        $array = Usuarios::select('usuarios.id', 'usuarios.ci', 'usuarios.estado', 'clientes.id_usuarios', 'trans.FechaTransaccion')
            ->join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->join(DB::raw("(SELECT id_clientes, MAX(FechaTransaccion) as FechaTransaccion 
                     FROM transacciones
                     WHERE productos_id = 30
                     GROUP BY id_clientes
                     HAVING MAX(FechaTransaccion) <= '$fechaFiltro') as trans"), 'clientes.id', '=', 'trans.id_clientes')
            ->where('usuarios.estado', 1)
            ->pluck('usuarios.id')
            ->toArray();

        // Usuarios::whereIn('id', $array)->update(['estado' => 0]);

        // Log::info('Se actualizó el estado de ' . $arrayCount . ' usuarios correctamente');
        // // Log::info( $arrayCount . ''. $array);

        // $this->info('Mensaje registrado en el log.');


        return response()->json([
            "FECHA FILTRO" => $fechaFiltro,
            "total New" => $arrayCount,
            "total Ori" => $arrayCountORIGINAL,
            "arrayUsuarios" => $arrayUsuarios,
            "dataArray" => $array
        ]);
    }



    public function VerData2()
    {

        //! toma la ultima transaccion cuota=1 de cada cliente
        $clientesCuota =
            Transacciones::select('transacciones.id_clientes', 'transacciones.productos_id', 'transacciones.FechaTransaccion')
            ->join(
                DB::raw('(SELECT id_clientes, MAX(FechaTransaccion) AS max_fecha
                     FROM transacciones
                     WHERE productos_id = 30
                     GROUP BY id_clientes) as sub'),
                function ($join) {
                    $join->on('transacciones.id_clientes', '=', 'sub.id_clientes')
                        ->on('transacciones.FechaTransaccion', '=', 'sub.max_fecha');
                }
            )
            ->where('transacciones.productos_id', 30)
            ->orderBy('transacciones.id_clientes')
            ->get();


        $clientesCuotaCount =
            Transacciones::select('transacciones.id_clientes', 'transacciones.productos_id', 'transacciones.FechaTransaccion')
            ->join(
                DB::raw('(SELECT id_clientes, MAX(FechaTransaccion) AS max_fecha
                     FROM transacciones
                     WHERE productos_id = 30
                     GROUP BY id_clientes) as sub'),
                function ($join) {
                    $join->on('transacciones.id_clientes', '=', 'sub.id_clientes')
                        ->on('transacciones.FechaTransaccion', '=', 'sub.max_fecha');
                }
            )
            ->where('transacciones.productos_id', 30)
            ->count();

        //!carga la columna vencimiento_pase en la tabla clientes con la fecha de vencimiento de la ultima cuota paga(descomentar para ejecutar) 
        // Itera sobre los resultados y actualiza la tabla Clientes
        // foreach ($clientesCuota as $cliente) {
        //     // Calcula la nueva fecha de vencimiento sumando 30 días
        //     $nuevaFechaVencimiento = Carbon::parse($cliente->FechaTransaccion)->addDays(29);

        //     // Actualiza la tabla Clientes con la nueva fecha de vencimiento
        //     DB::table('clientes')
        //         ->where('id', $cliente->id_clientes)
        //         ->update(['vencimiento_pase' => $nuevaFechaVencimiento]);
        // }


        return response()->json([
            "clientes" => $clientesCuota,
            "cantidad" => $clientesCuotaCount
        ]);
    }

    public function VerData3()
    {
        $HOY = Carbon::now()->subDays(1)->format('Y-m-d');

        $Vencidos =
            Usuarios::join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->where(function ($query) use ($HOY) {
                $query->where('clientes.vencimiento_pase', '<', $HOY) // Filtra por vencimiento anterior a hoy
                    ->orWhereNull('clientes.vencimiento_pase');     // Incluye filas donde vencimiento_pase es NULL
            })
            ->get();

        $VencidosCantidad =
            Usuarios::join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->where(function ($query) use ($HOY) {
                $query->where('clientes.vencimiento_pase', '<', $HOY) // Filtra por vencimiento anterior a hoy
                    ->orWhereNull('clientes.vencimiento_pase');     // Incluye filas donde vencimiento_pase es NULL
            })
            ->count();

        $VencidosArray = Usuarios::join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->where(function ($query) use ($HOY) {
                $query->where('clientes.vencimiento_pase', '<', $HOY) // Filtra por vencimiento anterior a hoy
                    ->orWhereNull('clientes.vencimiento_pase');     // Incluye filas donde vencimiento_pase es NULL
            })
            ->pluck('clientes.id_usuarios')
            ->toArray();

        $VencidosArrayCount = Usuarios::join('clientes', 'usuarios.id', '=', 'clientes.id_usuarios')
            ->where(function ($query) use ($HOY) {
                $query->where('clientes.vencimiento_pase', '<', $HOY) // Filtra por vencimiento anterior a hoy
                    ->orWhereNull('clientes.vencimiento_pase');     // Incluye filas donde vencimiento_pase es NULL
            })
            ->pluck('clientes.id_usuarios')
            ->count();

        $estadonull = Usuarios::where('estado', null)->count();
        $estado0 = Usuarios::where('estado', 0)->count();
        $estado1 = Usuarios::where('estado', 1)->count();
        $totoales = Usuarios::count();

        // Usuarios::whereIn('id', $VencidosArray)->update(['estado' => 0]);


        return response()->json([
            "FECHA HOY" => $HOY,
            "cantidad a modificar" => $VencidosArrayCount,
            "usuarios estado 0" => $estado0,
            "usuarios estado null" => $estadonull,
            "usuarios estado 1" => $estado1,
            "usuarios totales sumados" => $estado1 + $estadonull + $estado0,
            "usuarios totales" => $totoales,
            "VENCIDOS" => $Vencidos,
            "VENCIDOS CANTIDAD" => $VencidosArrayCount,
            "VENCIDOS ARRAY" => $VencidosArray,
            "VENCIDOS ARRAY COUNT" => $VencidosArrayCount,

        ]);
    }
}
