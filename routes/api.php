<?php

use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\ComponenteController;
use App\Http\Controllers\CompatibilidadController;
use App\Http\Controllers\ArmadoController;
use App\Http\Controllers\DetalleArmadoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AwsPersonalizeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API
|--------------------------------------------------------------------------
|
| Aquí puedes registrar las rutas de la API para tu aplicación.
|
*/

// Rutas de autenticación (públicas)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/has-role/{role}', [AuthController::class, 'hasRole']);
    Route::get('/permissions', [AuthController::class, 'getPermissions']);
    
    // Rutas de recursos
    Route::apiResource('roles', RolController::class);
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('empleados', EmpleadoController::class);
    Route::apiResource('administradores', AdministradorController::class);
    Route::apiResource('componentes', ComponenteController::class);
    Route::apiResource('compatibilidades', CompatibilidadController::class);
    
    // Rutas adicionales para compatibilidad
    Route::get('/compatibilidades/componente/{id}', [CompatibilidadController::class, 'getCompatibilidadesComponente']);
    Route::get('/compatibilidades/componentes-compatibles/{id}', [CompatibilidadController::class, 'getComponentesCompatibles']);
    Route::post('/compatibilidades/filtrado-cruzado', [CompatibilidadController::class, 'getComponentesCompatiblesCruzado']);
    Route::post('/compatibilidades/verificar-lista', [CompatibilidadController::class, 'verificarCompatibilidadConLista']);
    
    Route::apiResource('armados', ArmadoController::class);
    Route::apiResource('detalles-armado', DetalleArmadoController::class);
    Route::apiResource('ventas', VentaController::class);
    Route::apiResource('devoluciones', DevolucionController::class);
    Route::apiResource('reportes', ReporteController::class);
    Route::middleware(['auth:sanctum', 'role:Administrador'])->prefix('reportes')->group(function () {
        Route::post('/generar-automatico', [\App\Http\Controllers\ReporteController::class, 'generarAutomatico']);
    });
    
    // Rutas para AWS Personalize
    Route::post('/armados/generar-automatico', [AwsPersonalizeController::class, 'generarArmadoAutomatico']);
});