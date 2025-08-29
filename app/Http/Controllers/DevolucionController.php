<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\Venta;
use App\Models\Componente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $devoluciones = Devolucion::with(['venta.armado.detallesArmado.componente', 'componente'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $devoluciones
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ventas = Venta::with(['armado.detallesArmado.componente'])
                      ->where('estado', 'Completada')
                      ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de devolución',
            'ventas' => $ventas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_venta' => 'required|exists:ventas,id_venta',
            'id_componente' => 'required|exists:componentes,id_componente',
            'cantidad_devuelta' => 'required|integer|min:1',
            'motivo' => 'required|string|max:500',
            'estado' => 'sometimes|string'
        ]);

        try {
            DB::beginTransaction();

            $venta = Venta::with(['armado.detallesArmado'])->findOrFail($request->id_venta);
            
            // Verificar que la venta esté completada
            if ($venta->estado !== 'Completada') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden devolver productos de ventas completadas'
                ], 400);
            }

            // Verificar que el componente esté en la venta
            $detalleVenta = $venta->armado->detallesArmado
                ->where('id_componente', $request->id_componente)
                ->first();

            if (!$detalleVenta) {
                return response()->json([
                    'success' => false,
                    'message' => 'El componente no pertenece a esta venta'
                ], 400);
            }

            // Verificar cantidad a devolver
            if ($request->cantidad_devuelta > $detalleVenta->cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cantidad a devolver no puede ser mayor a la cantidad vendida'
                ], 400);
            }

            // Crear la devolución
            $devolucion = Devolucion::create([
                'id_venta' => $request->id_venta,
                'id_componente' => $request->id_componente,
                'cantidad_devuelta' => $request->cantidad_devuelta,
                'motivo' => $request->motivo,
                'fecha_devolucion' => now(),
                'estado' => $request->estado ?? 'Pendiente'
            ]);

            // Actualizar stock del componente
            $componente = Componente::findOrFail($request->id_componente);
            $componente->update([
                'stock' => $componente->stock + $request->cantidad_devuelta
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Devolución procesada exitosamente',
                'data' => $devolucion->load(['venta.armado.detallesArmado.componente', 'componente'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $devolucion = Devolucion::with(['venta.armado.detallesArmado.componente', 'componente'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $devolucion
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $devolucion = Devolucion::with(['venta.armado.detallesArmado.componente', 'componente'])->findOrFail($id);
        $ventas = Venta::with(['armado.detallesArmado.componente'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $devolucion,
            'ventas' => $ventas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_venta' => 'sometimes|required|exists:ventas,id_venta',
            'id_componente' => 'sometimes|required|exists:componentes,id_componente',
            'cantidad_devuelta' => 'sometimes|required|integer|min:1',
            'motivo' => 'sometimes|required|string|max:500',
            'estado' => 'sometimes|string'
        ]);

        $devolucion = Devolucion::findOrFail($id);
        $devolucion->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Devolución actualizada exitosamente',
            'data' => $devolucion->load(['venta.armado.detallesArmado.componente', 'componente'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $devolucion = Devolucion::findOrFail($id);
        $devolucion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Devolución eliminada exitosamente'
        ]);
    }

    /**
     * Aprobar devolución
     */
    public function aprobarDevolucion(string $id): JsonResponse
    {
        $devolucion = Devolucion::findOrFail($id);
        
        if ($devolucion->estado === 'Aprobada') {
            return response()->json([
                'success' => false,
                'message' => 'La devolución ya está aprobada'
            ], 400);
        }

        $devolucion->update(['estado' => 'Aprobada']);

        return response()->json([
            'success' => true,
            'message' => 'Devolución aprobada exitosamente'
        ]);
    }

    /**
     * Rechazar devolución
     */
    public function rechazarDevolucion(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $devolucion = Devolucion::findOrFail($id);
            
            if ($devolucion->estado === 'Rechazada') {
                return response()->json([
                    'success' => false,
                    'message' => 'La devolución ya está rechazada'
                ], 400);
            }

            // Devolver el stock que se había agregado
            $componente = Componente::findOrFail($devolucion->id_componente);
            $componente->update([
                'stock' => $componente->stock - $devolucion->cantidad_devuelta
            ]);

            $devolucion->update(['estado' => 'Rechazada']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Devolución rechazada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener devoluciones por rango de fechas
     */
    public function getDevolucionesPorFecha(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        $devoluciones = Devolucion::with(['venta.armado.detallesArmado.componente', 'componente'])
                                 ->whereBetween('fecha_devolucion', [$request->fecha_inicio, $request->fecha_fin])
                                 ->get();

        $resumen = [
            'total_devoluciones' => $devoluciones->count(),
            'devoluciones_aprobadas' => $devoluciones->where('estado', 'Aprobada')->count(),
            'devoluciones_pendientes' => $devoluciones->where('estado', 'Pendiente')->count(),
            'devoluciones_rechazadas' => $devoluciones->where('estado', 'Rechazada')->count(),
            'total_cantidad_devuelta' => $devoluciones->sum('cantidad_devuelta')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'devoluciones' => $devoluciones,
                'resumen' => $resumen
            ]
        ]);
    }

    /**
     * Obtener devoluciones por componente
     */
    public function getDevolucionesPorComponente(string $idComponente): JsonResponse
    {
        $devoluciones = Devolucion::with(['venta.armado.detallesArmado.componente', 'componente'])
                                 ->where('id_componente', $idComponente)
                                 ->get();

        $totalCantidadDevuelta = $devoluciones->sum('cantidad_devuelta');

        return response()->json([
            'success' => true,
            'data' => [
                'devoluciones' => $devoluciones,
                'total_cantidad_devuelta' => $totalCantidadDevuelta,
                'cantidad_devoluciones' => $devoluciones->count()
            ]
        ]);
    }
}
