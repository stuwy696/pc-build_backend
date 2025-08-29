<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdministradorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $administradores = Administrador::with(['usuario'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $administradores
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de administrador'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'nivel_acceso' => 'required|string|max:50',
            'fecha_asignacion' => 'required|date',
            'estado' => 'sometimes|string'
        ]);

        $administrador = Administrador::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Administrador creado exitosamente',
            'data' => $administrador->load('usuario')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $administrador = Administrador::with(['usuario'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $administrador
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $administrador = Administrador::with(['usuario'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $administrador
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'sometimes|required|exists:usuarios,id_usuario',
            'nivel_acceso' => 'sometimes|required|string|max:50',
            'fecha_asignacion' => 'sometimes|required|date',
            'estado' => 'sometimes|string'
        ]);

        $administrador = Administrador::findOrFail($id);
        $administrador->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Administrador actualizado exitosamente',
            'data' => $administrador->load('usuario')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $administrador = Administrador::findOrFail($id);
        $administrador->delete();

        return response()->json([
            'success' => true,
            'message' => 'Administrador eliminado exitosamente'
        ]);
    }
}
