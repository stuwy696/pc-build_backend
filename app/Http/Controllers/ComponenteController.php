<?php

namespace App\Http\Controllers;

use App\Models\Componente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComponenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Componente::query();

        // Filtro por stock bajo
        if ($request->has('stock_bajo') && $request->stock_bajo === 'true') {
            $query->where('stock', '<=', 5);
        }

        // Filtro por categoría
        if ($request->has('categoria') && $request->categoria !== 'Todas') {
            $query->where('categoria', $request->categoria);
        }

        // Límite de resultados
        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        // Ordenamiento
        if ($request->has('order')) {
            $query->orderBy('stock', $request->order === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('stock', 'asc'); // Por defecto ordenar por stock ascendente
        }

        $componentes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $componentes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de componente'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'categoria' => 'required|in:CPU,GPU,RAM,Motherboard,Storage,PSU,Case',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'gama' => 'required|in:Media,Baja,Alta',
            'especificaciones' => 'nullable|string'
        ]);

        $componente = Componente::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Componente creado exitosamente',
            'data' => $componente
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $componente = Componente::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $componente
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $componente = Componente::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $componente
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'categoria' => 'sometimes|required|in:CPU,GPU,RAM,Motherboard,Storage,PSU,Case',
            'precio' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'gama' => 'sometimes|required|in:Media,Baja,Alta',
            'especificaciones' => 'nullable|string'
        ]);

        $componente = Componente::findOrFail($id);
        $componente->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Componente actualizado exitosamente',
            'data' => $componente
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $componente = Componente::findOrFail($id);
        $componente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Componente eliminado exitosamente'
        ]);
    }

    /**
     * Obtener componentes con stock disponible
     */
    public function getConStock(): JsonResponse
    {
        $componentes = Componente::where('stock', '>', 0)->get();
        
        return response()->json([
            'success' => true,
            'data' => $componentes
        ]);
    }

    /**
     * Obtener componentes por categoría
     */
    public function getByCategoria(string $categoria): JsonResponse
    {
        $componentes = Componente::where('categoria', $categoria)
                                ->where('stock', '>', 0)
                                ->get();
        
        return response()->json([
            'success' => true,
            'data' => $componentes
        ]);
    }

    /**
     * Actualizar stock de componente
     */
    public function updateStock(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        $componente = Componente::findOrFail($id);
        $componente->update(['stock' => $request->stock]);

        return response()->json([
            'success' => true,
            'message' => 'Stock actualizado exitosamente',
            'data' => $componente
        ]);
    }
}
