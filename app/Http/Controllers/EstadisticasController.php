<?php

namespace App\Http\Controllers;

use App\Models\estadisticas;
use App\Models\Usuarios;
use App\Models\Administradores;
use App\Models\Clientes;
use App\Models\Cuotas;
use App\Models\Ingresos;
use App\Models\Transacciones;
use App\Models\Productos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class EstadisticasController extends Controller
{
    public function show($Opcion,$sub){
        if($Opcion==='Usuarios'){
            if($sub==='Activos'){
                $UsuariosActivos=DB::table('usuarios')
                ->where('usuarios.estado','=',1)
                ->get();
                $UsuariosPasivos=DB::table('usuarios')
                ->where('usuarios.estado','=',0)
                ->get();
                return response()->json(["Activos"=>count($UsuariosActivos),"Pasivos"=>count($UsuariosPasivos)]);
            }
            if($sub==='Sexo'){
                $Hombres=DB::table('usuarios')
                ->where('usuarios.sexo','=','Hombre')
                ->get();
                $Mujeres=DB::table('usuarios')
                ->where('usuarios.sexo','=','Mujer')
                ->get();
                return response()->json(["Masculinos"=>count($Hombres),"Femeninos"=>count($Mujeres)]);
            }
            if($sub==='Edad'){
                $edadPromedioHombres = DB::table('usuarios')
                ->where('sexo', 'Hombre')
                ->select(DB::raw('AVG(DATEDIFF(CURDATE(), FechaDeNacimiento) / 365) as edad_promedio_hombres'))
                ->get();

                $edadPromedioMujeres = DB::table('usuarios')
                    ->where('sexo', 'Mujer')
                    ->select(DB::raw('AVG(DATEDIFF(CURDATE(), FechaDeNacimiento) / 365) as edad_promedio_mujeres'))
                    ->get();

                // Obtener el valor de la edad promedio para hombres
                $edadPromedioHombres = $edadPromedioHombres[0]->edad_promedio_hombres;

                // Obtener el valor de la edad promedio para mujeres
                $edadPromedioMujeres = $edadPromedioMujeres[0]->edad_promedio_mujeres;

                // Redondear las edades promedio a números enteros
                $edadPromedioHombres = round($edadPromedioHombres);
                $edadPromedioMujeres = round($edadPromedioMujeres);
                
                return response()->json(["EdadMujeres"=>$edadPromedioMujeres,"EdadHombres"=>$edadPromedioHombres]);
            }
            if ($sub === 'Ingresos') {
                $result = DB::select("
                    SELECT HOUR(HoraIngreso) AS Hora, COUNT(*) AS Cantidad
                    FROM ingresos
                    WHERE MONTH(FechaIngreso) = MONTH(CURDATE()) AND YEAR(FechaIngreso) = YEAR(CURDATE())
                    GROUP BY HOUR(HoraIngreso)
                    ORDER BY COUNT(*) DESC
                    LIMIT 1
                ");
            
                if (!empty($result)) {
                    // Obtiene el resultado de la consulta
                    $hora = $result[0]->Hora;
            
                    // Formatea la hora en el formato deseado
                    $horarioFinal = str_pad($hora, 2, "0", STR_PAD_LEFT) . ":00";
            
                    return response()->json($horarioFinal);
                } else {
                    // Si no se encontraron registros
                    return response()->json("No hay registros para el mes actual");
                }
            }

        }
        
    }
}
