<?php

namespace App\Http\Controllers;

use App\Models\DetalleArmado;
use App\Models\Armado;
use App\Models\Componente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DetalleArmadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DetalleArmado::with(['armado', 'componente']);
        
        if ($request->has('armado_id')) {
            $query->where('id_armado', $request->armado_id);
        }
        
        $detalles = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $detalles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de detalle de armado'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_armado' => 'required|exists:armados,id_armado',
            'id_componente' => 'required|exists:componentes,id_componente',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0'
        ]);

        // Verificar que el armado existe
        $armado = Armado::findOrFail($request->id_armado);
        
        // Verificar que el componente existe y tiene stock
        $componente = Componente::findOrFail($request->id_componente);
        
        if ($componente->stock < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente para este componente'
            ], 400);
        }

        // Verificar si ya existe este componente en el armado
        $detalleExistente = DetalleArmado::where('id_armado', $request->id_armado)
                                        ->where('id_componente', $request->id_componente)
                                        ->first();

        if ($detalleExistente) {
            // Actualizar cantidad existente
            $nuevaCantidad = $detalleExistente->cantidad + $request->cantidad;
            $detalleExistente->update([
                'cantidad' => $nuevaCantidad,
                'subtotal' => $detalleExistente->precio_unitario * $nuevaCantidad
            ]);
            
            $detalle = $detalleExistente;
        } else {
            // Crear nuevo detalle
            $detalle = DetalleArmado::create($request->all());
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalle de armado creado exitosamente',
            'data' => $detalle->load(['componente'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $detalle = DetalleArmado::with(['armado', 'componente'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $detalle
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $detalle = DetalleArmado::with(['armado', 'componente'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $detalle
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'cantidad' => 'sometimes|required|integer|min:1',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
            'subtotal' => 'sometimes|required|numeric|min:0'
        ]);

        $detalle = DetalleArmado::findOrFail($id);
        
        // Si se actualiza la cantidad, verificar stock
        if ($request->has('cantidad')) {
            $componente = Componente::findOrFail($detalle->id_componente);
            if ($componente->stock < $request->cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente para este componente'
                ], 400);
            }
        }

        $detalle->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Detalle de armado actualizado exitosamente',
            'data' => $detalle->load(['componente'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $detalle = DetalleArmado::findOrFail($id);
        $detalle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detalle de armado eliminado exitosamente'
        ]);
    }
}
