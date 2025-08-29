<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $empleados = Empleado::with(['usuario'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $empleados
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de empleado'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'cargo' => 'required|string|max:100',
            'fecha_contratacion' => 'required|date',
            'salario' => 'required|numeric|min:0',
            'estado' => 'sometimes|string'
        ]);

        $empleado = Empleado::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Empleado creado exitosamente',
            'data' => $empleado->load('usuario')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $empleado = Empleado::with(['usuario'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $empleado
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $empleado = Empleado::with(['usuario'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $empleado
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'sometimes|required|exists:usuarios,id_usuario',
            'cargo' => 'sometimes|required|string|max:100',
            'fecha_contratacion' => 'sometimes|required|date',
            'salario' => 'sometimes|required|numeric|min:0',
            'estado' => 'sometimes|string'
        ]);

        $empleado = Empleado::findOrFail($id);
        $empleado->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado exitosamente',
            'data' => $empleado->load('usuario')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empleado eliminado exitosamente'
        ]);
    }
}
