<?php

namespace App\Http\Controllers;

use App\Models\Compatibilidad;
use App\Models\Componente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompatibilidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $compatibilidades = Compatibilidad::with(['componente1', 'componente2'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $compatibilidades
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $componentes = Componente::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de compatibilidad',
            'componentes' => $componentes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_componente1' => 'required|exists:componentes,id_componente',
            'id_componente2' => 'required|exists:componentes,id_componente|different:id_componente1',
            'porcentaje_compatibilidad' => 'required|numeric|min:0|max:100'
        ]);

        // Verificar que no exista ya esta compatibilidad
        $existing = Compatibilidad::where(function($query) use ($request) {
            $query->where('id_componente1', $request->id_componente1)
                  ->where('id_componente2', $request->id_componente2);
        })->orWhere(function($query) use ($request) {
            $query->where('id_componente1', $request->id_componente2)
                  ->where('id_componente2', $request->id_componente1);
        })->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Esta compatibilidad ya existe'
            ], 400);
        }

        $compatibilidad = Compatibilidad::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Compatibilidad creada exitosamente',
            'data' => $compatibilidad->load(['componente1', 'componente2'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $compatibilidad = Compatibilidad::with(['componente1', 'componente2'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $compatibilidad
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $compatibilidad = Compatibilidad::with(['componente1', 'componente2'])->findOrFail($id);
        $componentes = Componente::all();
        
        return response()->json([
            'success' => true,
            'data' => $compatibilidad,
            'componentes' => $componentes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_componente1' => 'sometimes|required|exists:componentes,id_componente',
            'id_componente2' => 'sometimes|required|exists:componentes,id_componente',
            'porcentaje_compatibilidad' => 'sometimes|required|numeric|min:0|max:100'
        ]);

        $compatibilidad = Compatibilidad::findOrFail($id);
        $compatibilidad->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Compatibilidad actualizada exitosamente',
            'data' => $compatibilidad->load(['componente1', 'componente2'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $compatibilidad = Compatibilidad::findOrFail($id);
        $compatibilidad->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compatibilidad eliminada exitosamente'
        ]);
    }

    /**
     * Obtener compatibilidades de un componente específico
     */
    public function getCompatibilidadesComponente(string $idComponente): JsonResponse
    {
        $compatibilidades = Compatibilidad::where('id_componente1', $idComponente)
                                         ->orWhere('id_componente2', $idComponente)
                                         ->with(['componente1', 'componente2'])
                                         ->get();

        return response()->json([
            'success' => true,
            'data' => $compatibilidades
        ]);
    }

    /**
     * Calcular compatibilidad entre dos componentes específicos
     */
    public function calcularCompatibilidad(Request $request): JsonResponse
    {
        $request->validate([
            'id_componente1' => 'required|exists:componentes,id_componente',
            'id_componente2' => 'required|exists:componentes,id_componente'
        ]);

        $compatibilidad = Compatibilidad::where(function($query) use ($request) {
            $query->where('id_componente1', $request->id_componente1)
                  ->where('id_componente2', $request->id_componente2);
        })->orWhere(function($query) use ($request) {
            $query->where('id_componente1', $request->id_componente2)
                  ->where('id_componente2', $request->id_componente1);
        })->with(['componente1', 'componente2'])->first();

        if (!$compatibilidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de compatibilidad entre estos componentes',
                'porcentaje_compatibilidad' => 0
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $compatibilidad,
            'porcentaje_compatibilidad' => $compatibilidad->porcentaje_compatibilidad
        ]);
    }

    /**
     * Obtener componentes compatibles con uno específico
     */
    public function getComponentesCompatibles(string $idComponente): JsonResponse
    {
        $compatibilidades = Compatibilidad::where('id_componente1', $idComponente)
                                         ->orWhere('id_componente2', $idComponente)
                                         ->with(['componente1', 'componente2'])
                                         ->get();

        $componentesCompatibles = [];
        foreach ($compatibilidades as $comp) {
            $otroComponente = $comp->id_componente1 == $idComponente ? $comp->componente2 : $comp->componente1;
            $componentesCompatibles[] = [
                'componente' => $otroComponente,
                'porcentaje_compatibilidad' => $comp->porcentaje_compatibilidad
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $componentesCompatibles
        ]);
    }

    /**
     * Obtener componentes compatibles con múltiples componentes seleccionados
     * Implementa filtrado cruzado usando reglas dinámicas
     */
    public function getComponentesCompatiblesCruzado(Request $request): JsonResponse
    {
        $request->validate([
            'componentes_seleccionados' => 'required|array',
            'componentes_seleccionados.*' => 'exists:componentes,id_componente',
            'categoria_filtro' => 'nullable|string|in:CPU,GPU,RAM,Motherboard,Storage,PSU,Case'
        ]);

        $componentesSeleccionados = $request->componentes_seleccionados;
        $categoriaFiltro = $request->categoria_filtro;

        // Si no hay componentes seleccionados, devolver todos los componentes de la categoría especificada
        if (empty($componentesSeleccionados)) {
            $query = Componente::where('stock', '>', 0);
            if ($categoriaFiltro) {
                $query->where('categoria', $categoriaFiltro);
            }
            $componentes = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $componentes->map(function($comp) {
                    return [
                        'componente' => $comp,
                        'porcentaje_compatibilidad_promedio' => 100,
                        'porcentajes_individuales' => []
                    ];
                }),
                'message' => 'No hay componentes seleccionados para filtrar'
            ]);
        }

        // Obtener los componentes seleccionados
        $componentesSeleccionadosObjs = Componente::whereIn('id_componente', $componentesSeleccionados)->get();

        // Obtener todos los componentes de la categoría de destino
        $query = Componente::where('stock', '>', 0)
                          ->whereNotIn('id_componente', $componentesSeleccionados);
        
        if ($categoriaFiltro) {
            $query->where('categoria', $categoriaFiltro);
        }
        
        $componentesCandidatos = $query->get();

        // Calcular compatibilidad para cada componente candidato
        $componentesConCompatibilidad = [];
        
        foreach ($componentesCandidatos as $componenteDestino) {
            $porcentajesCompatibilidad = [];
            $esCompatibleConTodos = true;
            
            foreach ($componentesSeleccionadosObjs as $componenteOrigen) {
                // Usar el sistema de reglas dinámicas
                $compatibilidad = \App\Models\ReglaCompatibilidad::verificarCompatibilidad(
                    $componenteOrigen, 
                    $componenteDestino
                );
                
                // Solo incluir si la compatibilidad es mayor a 0
                if ($compatibilidad > 0) {
                    $porcentajesCompatibilidad[] = $compatibilidad;
                } else {
                    $esCompatibleConTodos = false;
                    break; // No es compatible con todos los componentes seleccionados
                }
            }
            
            // Solo incluir si es compatible con todos los componentes seleccionados Y tiene compatibilidad > 0
            if ($esCompatibleConTodos && !empty($porcentajesCompatibilidad)) {
                $promedioCompatibilidad = array_sum($porcentajesCompatibilidad) / count($porcentajesCompatibilidad);
                
                // Solo incluir si el promedio es mayor a 0
                if ($promedioCompatibilidad > 0) {
                    $componentesConCompatibilidad[] = [
                        'componente' => $componenteDestino,
                        'porcentaje_compatibilidad_promedio' => round($promedioCompatibilidad, 2),
                        'porcentajes_individuales' => $porcentajesCompatibilidad
                    ];
                }
            }
        }

        // Si no hay componentes completamente compatibles, mostrar solo los que tienen alguna compatibilidad
        if (empty($componentesConCompatibilidad)) {
            // Obtener todos los componentes de la categoría especificada
            $query = Componente::where('stock', '>', 0)
                              ->whereNotIn('id_componente', $componentesSeleccionados);
            
            if ($categoriaFiltro) {
                $query->where('categoria', $categoriaFiltro);
            }
            
            $componentesCandidatos = $query->get();
            
            // Calcular compatibilidad para cada componente candidato
            foreach ($componentesCandidatos as $componenteDestino) {
                $porcentajesCompatibilidad = [];
                
                foreach ($componentesSeleccionadosObjs as $componenteOrigen) {
                    $compatibilidad = \App\Models\ReglaCompatibilidad::verificarCompatibilidad(
                        $componenteOrigen, 
                        $componenteDestino
                    );
                    
                    // Solo incluir si la compatibilidad es mayor a 0
                    if ($compatibilidad > 0) {
                        $porcentajesCompatibilidad[] = $compatibilidad;
                    }
                }
                
                // Solo incluir si tiene alguna compatibilidad Y el promedio es mayor a 0
                if (!empty($porcentajesCompatibilidad)) {
                    $promedioCompatibilidad = array_sum($porcentajesCompatibilidad) / count($porcentajesCompatibilidad);
                    
                    // Solo incluir si el promedio es mayor a 0
                    if ($promedioCompatibilidad > 0) {
                        $componentesConCompatibilidad[] = [
                            'componente' => $componenteDestino,
                            'porcentaje_compatibilidad_promedio' => round($promedioCompatibilidad, 2),
                            'porcentajes_individuales' => $porcentajesCompatibilidad
                        ];
                    }
                }
            }
            
            // Si aún no hay componentes compatibles, devolver lista vacía
            if (empty($componentesConCompatibilidad)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'componentes_seleccionados' => $componentesSeleccionados,
                    'total_componentes_compatibles' => 0,
                    'message' => 'No se encontraron componentes compatibles'
                ]);
            }
        }

        // Ordenar por porcentaje de compatibilidad descendente
        usort($componentesConCompatibilidad, function($a, $b) {
            return $b['porcentaje_compatibilidad_promedio'] <=> $a['porcentaje_compatibilidad_promedio'];
        });

        return response()->json([
            'success' => true,
            'data' => $componentesConCompatibilidad,
            'componentes_seleccionados' => $componentesSeleccionados,
            'total_componentes_compatibles' => count($componentesConCompatibilidad)
        ]);
    }

    /**
     * Verificar compatibilidad de un componente específico con una lista de componentes
     */
    public function verificarCompatibilidadConLista(Request $request): JsonResponse
    {
        $request->validate([
            'id_componente' => 'required|exists:componentes,id_componente',
            'componentes_lista' => 'required|array',
            'componentes_lista.*' => 'exists:componentes,id_componente'
        ]);

        $idComponente = $request->id_componente;
        $componentesLista = $request->componentes_lista;
        
        $resultados = [];
        $esCompletamenteCompatible = true;
        $porcentajeMinimo = 100;

        foreach ($componentesLista as $idOtroComponente) {
            if ($idComponente == $idOtroComponente) {
                continue; // Saltar comparación consigo mismo
            }

            $compatibilidad = Compatibilidad::where(function($query) use ($idComponente, $idOtroComponente) {
                $query->where('id_componente1', $idComponente)
                      ->where('id_componente2', $idOtroComponente);
            })->orWhere(function($query) use ($idComponente, $idOtroComponente) {
                $query->where('id_componente1', $idOtroComponente)
                      ->where('id_componente2', $idComponente);
            })->first();

            $porcentaje = $compatibilidad ? $compatibilidad->porcentaje_compatibilidad : 0;
            
            $resultados[] = [
                'id_componente_comparado' => $idOtroComponente,
                'porcentaje_compatibilidad' => $porcentaje,
                'es_compatible' => $porcentaje > 0
            ];

            if ($porcentaje == 0) {
                $esCompletamenteCompatible = false;
            }
            
            if ($porcentaje < $porcentajeMinimo) {
                $porcentajeMinimo = $porcentaje;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_componente_verificado' => $idComponente,
                'es_completamente_compatible' => $esCompletamenteCompatible,
                'porcentaje_minimo_compatibilidad' => $porcentajeMinimo,
                'resultados_individuales' => $resultados
            ]
        ]);
    }
}
