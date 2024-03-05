<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\IngresosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\TransaccionesController;
use App\Http\Controllers\EstadisticasController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//USUARIOS-------------------------------------------------------------------------------------------------------------------------->
Route::post('/Usuarios', [UsuariosController::class, 'create']);
Route::post('/ChangePassword', [UsuariosController::class, 'ChangePassword']);
Route::get('/Usuarios/{ci}', [UsuariosController::class, 'show']);
Route::patch('/Usuarios/{ci}', [UsuariosController::class, 'update']);
Route::delete('/Usuarios/{ci}', [UsuariosController::class, 'destroy']);
//INGRESOS-------------------------------------------------------------------------------------------------------------------------->
Route::post('/Ingresos', [IngresosController::class, 'Login']);
Route::get('/Ingresos/{ci}', [IngresosController::class, 'show']);
//PRODUCTOS-------------------------------------------------------------------------------------------------------------------------->
Route::post('/Productos', [ProductosController::class, 'create']);
Route::get('/Stock/{id}/{cantidad}', [ProductosController::class, 'GestionStock']);
Route::get('/CompraStock/{id}/{cantidad}', [ProductosController::class, 'CompraStock']);
Route::get('/Productos/{id}', [ProductosController::class, 'show']);
Route::patch('/Productos/{id}', [ProductosController::class, 'update']);
Route::delete('/Productos/{id}', [ProductosController::class, 'destroy']);
//TRANSACCIONES-------------------------------------------------------------------------------------------------------------------------->
Route::post('/Transacciones', [TransaccionesController::class, 'create']);
Route::get('/Transacciones/{string}/{date}', [TransaccionesController::class, 'show']);
Route::get('/Cuotas/{ci}', [TransaccionesController::class, 'ConsultarCuotas']);
Route::delete('/Transacciones/{id}', [TransaccionesController::class, 'destroy']);
//ESTADISTICAS-------------------------------------------------------------------------------------------------------------------------->
Route::get('/Estadisticas/{Opcion}/{sub}', [EstadisticasController::class, 'show']);