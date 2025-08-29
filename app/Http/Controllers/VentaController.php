<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Armado;
use App\Models\Componente;
use App\Models\DetalleArmado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $ventas = Venta::with(['armado.detallesArmado.componente', 'usuarioEmpleado'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $ventas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $armados = Armado::with(['detallesArmado.componente'])->where('estado', '!=', 'Vendido')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de venta',
            'armados' => $armados
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_armado' => 'required|exists:armados,id_armado',
            'id_usuario_empleado' => 'required|exists:usuarios,id_usuario',
            'total' => 'required|numeric|min:0',
            'estado' => 'sometimes|string'
        ]);

        try {
            DB::beginTransaction();

            $armado = Armado::with(['detallesArmado.componente'])->findOrFail($request->id_armado);

            // Verificar stock de todos los componentes
            foreach ($armado->detallesArmado as $detalle) {
                if ($detalle->componente->stock < $detalle->cantidad) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuficiente para el componente: ' . $detalle->componente->nombre
                    ], 400);
                }
            }

            // Crear la venta
            $venta = Venta::create([
                'id_armado' => $request->id_armado,
                'id_usuario_empleado' => $request->id_usuario_empleado,
                'fecha_venta' => now(),
                'total' => $request->total,
                'estado' => $request->estado ?? 'Completada'
            ]);

            // Actualizar stock de componentes
            foreach ($armado->detallesArmado as $detalle) {
                $componente = $detalle->componente;
                $componente->update([
                    'stock' => $componente->stock - $detalle->cantidad
                ]);
            }

            // Actualizar estado del armado
            $armado->update(['estado' => 'Vendido']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'data' => $venta->load(['armado.detallesArmado.componente', 'usuarioEmpleado'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $venta = Venta::with(['armado.detallesArmado.componente', 'usuarioEmpleado'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $venta
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $venta = Venta::with(['armado.detallesArmado.componente', 'usuarioEmpleado'])->findOrFail($id);
        $armados = Armado::with(['detallesArmado.componente'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $venta,
            'armados' => $armados
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_armado' => 'sometimes|required|exists:armados,id_armado',
            'id_usuario_empleado' => 'sometimes|required|exists:usuarios,id_usuario',
            'total' => 'sometimes|required|numeric|min:0',
            'estado' => 'sometimes|string'
        ]);

        $venta = Venta::findOrFail($id);
        $venta->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Venta actualizada exitosamente',
            'data' => $venta->load(['armado.detallesArmado.componente', 'usuarioEmpleado'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Venta eliminada exitosamente'
        ]);
    }

    /**
     * Generar cotización para un armado
     */
    public function generarCotizacion(string $idArmado): JsonResponse
    {
        $armado = Armado::with(['detallesArmado.componente', 'usuario'])->findOrFail($idArmado);
        
        $total = 0;
        $detalles = [];
        
        foreach ($armado->detallesArmado as $detalle) {
            $subtotal = $detalle->cantidad * $detalle->precio_unitario;
            $total += $subtotal;
            
            $detalles[] = [
                'componente' => $detalle->componente,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $subtotal
            ];
        }

        $cotizacion = [
            'numero_cotizacion' => 'COT-' . str_pad($armado->id_armado, 6, '0', STR_PAD_LEFT),
            'fecha' => now()->format('Y-m-d H:i:s'),
            'cliente' => $armado->usuario,
            'presupuesto' => $armado->presupuesto,
            'detalles' => $detalles,
            'subtotal' => $total,
            'iva' => $total * 0.16, // 16% IVA
            'total' => $total * 1.16,
            'validez' => now()->addDays(30)->format('Y-m-d'),
            'estado_armado' => $armado->estado
        ];

        return response()->json([
            'success' => true,
            'message' => 'Cotización generada exitosamente',
            'data' => $cotizacion
        ]);
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public function getVentasPorFecha(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        $ventas = Venta::with(['armado.detallesArmado.componente', 'usuarioEmpleado'])
                      ->whereBetween('fecha_venta', [$request->fecha_inicio, $request->fecha_fin])
                      ->get();

        $totalVentas = $ventas->sum('total');
        $cantidadVentas = $ventas->count();

        return response()->json([
            'success' => true,
            'data' => [
                'ventas' => $ventas,
                'resumen' => [
                    'total_ventas' => $totalVentas,
                    'cantidad_ventas' => $cantidadVentas,
                    'promedio_venta' => $cantidadVentas > 0 ? round($totalVentas / $cantidadVentas, 2) : 0
                ]
            ]
        ]);
    }

    /**
     * Obtener ventas por empleado
     */
    public function getVentasPorEmpleado(string $idEmpleado): JsonResponse
    {
        $ventas = Venta::with(['armado.detallesArmado.componente', 'usuarioEmpleado'])
                      ->where('id_usuario_empleado', $idEmpleado)
                      ->get();

        $totalVentas = $ventas->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'ventas' => $ventas,
                'total_ventas_empleado' => $totalVentas,
                'cantidad_ventas' => $ventas->count()
            ]
        ]);
    }

    /**
     * Cancelar venta (devolver stock)
     */
    public function cancelarVenta(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $venta = Venta::with(['armado.detallesArmado.componente'])->findOrFail($id);
            
            if ($venta->estado === 'Cancelada') {
                return response()->json([
                    'success' => false,
                    'message' => 'La venta ya está cancelada'
                ], 400);
            }

            // Devolver stock de componentes
            foreach ($venta->armado->detallesArmado as $detalle) {
                $componente = $detalle->componente;
                $componente->update([
                    'stock' => $componente->stock + $detalle->cantidad
                ]);
            }

            // Actualizar estado de la venta y armado
            $venta->update(['estado' => 'Cancelada']);
            $venta->armado->update(['estado' => 'Disponible']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada exitosamente, stock devuelto'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la venta: ' . $e->getMessage()
            ], 500);
        }
    }
}
