<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComponenteController;
use App\Http\Controllers\CompatibilidadController;
use App\Http\Controllers\ArmadoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\AdministradorController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de Autenticación (públicas)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/has-role/{role}', [AuthController::class, 'hasRole']);
        Route::get('/permissions', [AuthController::class, 'getPermissions']);
    });
});

// Rutas de Componentes (accesibles para todos los roles autenticados)
Route::middleware('auth:sanctum')->prefix('componentes')->group(function () {
    Route::get('/', [ComponenteController::class, 'index']);
    Route::get('/create', [ComponenteController::class, 'create']);
    Route::post('/', [ComponenteController::class, 'store']);
    Route::get('/{id}', [ComponenteController::class, 'show']);
    Route::get('/{id}/edit', [ComponenteController::class, 'edit']);
    Route::put('/{id}', [ComponenteController::class, 'update']);
    Route::delete('/{id}', [ComponenteController::class, 'destroy']);
    
    // Rutas específicas de componentes
    Route::get('/gama/{gama}', [ComponenteController::class, 'getByGama']);
    Route::get('/stock/disponible', [ComponenteController::class, 'getWithStock']);
    Route::get('/categoria/{categoria}', [ComponenteController::class, 'getByCategoria']);
    Route::put('/{id}/stock', [ComponenteController::class, 'updateStock']);
});

// Rutas de Compatibilidades (accesibles para todos los roles autenticados)
Route::middleware('auth:sanctum')->prefix('compatibilidades')->group(function () {
    Route::get('/', [CompatibilidadController::class, 'index']);
    Route::get('/create', [CompatibilidadController::class, 'create']);
    Route::post('/', [CompatibilidadController::class, 'store']);
    Route::get('/{id}', [CompatibilidadController::class, 'show']);
    Route::get('/{id}/edit', [CompatibilidadController::class, 'edit']);
    Route::put('/{id}', [CompatibilidadController::class, 'update']);
    Route::delete('/{id}', [CompatibilidadController::class, 'destroy']);
    
    // Rutas específicas de compatibilidad
    Route::get('/componente/{idComponente}', [CompatibilidadController::class, 'getCompatibilidadesComponente']);
    Route::post('/calcular', [CompatibilidadController::class, 'calcularCompatibilidad']);
    Route::get('/componente/{idComponente}/compatibles', [CompatibilidadController::class, 'getComponentesCompatibles']);
});

// Rutas de Armados (accesibles para todos los roles autenticados)
Route::middleware('auth:sanctum')->prefix('armados')->group(function () {
    Route::get('/', [ArmadoController::class, 'index']);
    Route::get('/create', [ArmadoController::class, 'create']);
    Route::post('/', [ArmadoController::class, 'store']);
    Route::get('/{id}', [ArmadoController::class, 'show']);
    Route::get('/{id}/edit', [ArmadoController::class, 'edit']);
    Route::put('/{id}', [ArmadoController::class, 'update']);
    Route::delete('/{id}', [ArmadoController::class, 'destroy']);
    
    // Rutas específicas de armados
    Route::post('/generar-automatico', [ArmadoController::class, 'generarArmadoAutomatico']);
    Route::post('/{id}/agregar-componente', [ArmadoController::class, 'agregarComponente']);
    Route::delete('/{id}/remover-componente', [ArmadoController::class, 'removerComponente']);
    Route::get('/{id}/calcular-total', [ArmadoController::class, 'calcularTotal']);
});

// Rutas de Ventas (solo Empleados y Administradores)
Route::middleware(['auth:sanctum', 'role:Empleado'])->prefix('ventas')->group(function () {
    Route::get('/', [VentaController::class, 'index']);
    Route::get('/create', [VentaController::class, 'create']);
    Route::post('/', [VentaController::class, 'store']);
    Route::get('/{id}', [VentaController::class, 'show']);
    Route::get('/{id}/edit', [VentaController::class, 'edit']);
    Route::put('/{id}', [VentaController::class, 'update']);
    Route::delete('/{id}', [VentaController::class, 'destroy']);
    
    // Rutas específicas de ventas
    Route::get('/armado/{idArmado}/cotizacion', [VentaController::class, 'generarCotizacion']);
    Route::post('/por-fecha', [VentaController::class, 'getVentasPorFecha']);
    Route::get('/empleado/{idEmpleado}', [VentaController::class, 'getVentasPorEmpleado']);
    Route::put('/{id}/cancelar', [VentaController::class, 'cancelarVenta']);
});

// Rutas de Devoluciones (solo Empleados y Administradores)
Route::middleware(['auth:sanctum', 'role:Empleado'])->prefix('devoluciones')->group(function () {
    Route::get('/', [DevolucionController::class, 'index']);
    Route::get('/create', [DevolucionController::class, 'create']);
    Route::post('/', [DevolucionController::class, 'store']);
    Route::get('/{id}', [DevolucionController::class, 'show']);
    Route::get('/{id}/edit', [DevolucionController::class, 'edit']);
    Route::put('/{id}', [DevolucionController::class, 'update']);
    Route::delete('/{id}', [DevolucionController::class, 'destroy']);
    
    // Rutas específicas de devoluciones
    Route::put('/{id}/aprobar', [DevolucionController::class, 'aprobarDevolucion']);
    Route::put('/{id}/rechazar', [DevolucionController::class, 'rechazarDevolucion']);
    Route::post('/por-fecha', [DevolucionController::class, 'getDevolucionesPorFecha']);
    Route::get('/componente/{idComponente}', [DevolucionController::class, 'getDevolucionesPorComponente']);
});

// Rutas de Reportes (solo Administradores)
Route::middleware(['auth:sanctum', 'role:Administrador'])->prefix('reportes')->group(function () {
    Route::get('/', [ReporteController::class, 'index']);
    Route::get('/create', [ReporteController::class, 'create']);
    Route::post('/', [ReporteController::class, 'store']);
    Route::get('/{id}', [ReporteController::class, 'show']);
    Route::get('/{id}/edit', [ReporteController::class, 'edit']);
    Route::put('/{id}', [ReporteController::class, 'update']);
    Route::delete('/{id}', [ReporteController::class, 'destroy']);
    
    // Rutas específicas de reportes
    Route::post('/ventas', [ReporteController::class, 'reporteVentas']);
    Route::post('/devoluciones', [ReporteController::class, 'reporteDevoluciones']);
    Route::post('/inventario', [ReporteController::class, 'reporteInventario']);
    Route::post('/estadisticas', [ReporteController::class, 'reporteEstadisticas']);
    Route::post('/generar-automatico', [ReporteController::class, 'generarAutomatico']);
});

// Rutas de Usuarios (solo Administradores)
Route::middleware(['auth:sanctum', 'role:Administrador'])->apiResource('usuarios', UsuarioController::class);

// Rutas de Clientes (solo Administradores)
Route::middleware(['auth:sanctum', 'role:Administrador'])->apiResource('clientes', ClienteController::class);

// Rutas de Empleados (solo Administradores)
Route::middleware(['auth:sanctum', 'role:Administrador'])->apiResource('empleados', EmpleadoController::class);

// Rutas de Administradores (solo Administradores)
Route::middleware(['auth:sanctum', 'role:Administrador'])->apiResource('administradores', AdministradorController::class);
