<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Componente;
use App\Models\Armado;
use App\Models\DetalleArmado;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

class AwsPersonalizeController extends Controller
{
    /**
     * Generar armado automático usando IA basada en datos históricos
     */
    public function generarArmadoAutomatico(Request $request): JsonResponse
    {
        try {
                    Log::info('🚀 Iniciando generación de armado automático con IA', [
            'presupuesto' => $request->presupuesto,
            'id_usuario' => $request->id_usuario
        ]);

            $request->validate([
                'presupuesto' => 'required|numeric|min:100',
                'id_usuario' => 'nullable|integer|exists:usuarios,id_usuario',
                'preferencias' => 'array'
            ]);

            $presupuesto = $request->presupuesto;
            $idUsuario = $request->id_usuario ?? null;
            $preferencias = $request->preferencias ?? [];

            Log::info('✅ Parámetros validados correctamente');

            // Obtener recomendaciones usando IA
            $recomendaciones = $this->obtenerRecomendacionesIA($idUsuario, $presupuesto, $preferencias);
            
            if (empty($recomendaciones)) {
                throw new \Exception('No se pudieron generar recomendaciones. Verifica que haya componentes disponibles con categorías válidas.');
            }

            // Filtrar componentes null
            $recomendaciones = array_filter($recomendaciones, function($componente) {
                return $componente !== null;
            });

            if (empty($recomendaciones)) {
                throw new \Exception('No se encontraron componentes válidos después del filtrado.');
            }

            Log::info('🎯 Recomendaciones de IA obtenidas', ['count' => count($recomendaciones)]);

            // Crear el armado
            $armado = $this->crearArmadoConRecomendaciones($recomendaciones, $idUsuario, $presupuesto);

            Log::info('✅ Armado creado exitosamente con IA', ['id_armado' => $armado['armado']->id_armado]);

            return response()->json([
                'success' => true,
                'message' => '🎯 Armado automático generado exitosamente usando IA',
                'data' => $armado,
                'metodo_utilizado' => 'IA - Datos históricos y rendimiento',
                'presupuesto_utilizado' => $armado['precio_total'],
                'presupuesto_restante' => $presupuesto - $armado['precio_total']
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al generar armado automático: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '❌ Error al generar armado automático: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener recomendaciones usando IA basada en datos históricos y rendimiento
     */
    private function obtenerRecomendacionesIA(?int $idUsuario, float $presupuesto, array $preferencias): array
    {
        Log::info('🧠 Iniciando análisis de IA para recomendaciones', [
            'id_usuario' => $idUsuario,
            'presupuesto' => $presupuesto
        ]);

        // Si no hay usuario específico, usar recomendaciones por rendimiento
        if ($idUsuario === null) {
            Log::info('👤 Sin usuario específico - usando recomendaciones por rendimiento');
            $recomendaciones = $this->recomendacionesPorRendimiento($presupuesto);
        } else {
            // 1. Obtener datos del usuario
            $usuario = \App\Models\Usuario::find($idUsuario);
            
            // 2. Obtener armados históricos del usuario
            $armadosHistoricos = Armado::where('id_usuario', $idUsuario)
                ->with('detallesArmado.componente')
                ->get();

            Log::info('📊 Datos históricos obtenidos', [
                'armados_historicos' => $armadosHistoricos->count()
            ]);

            // 3. Si hay armados históricos, analizar patrones
            if ($armadosHistoricos->isNotEmpty()) {
                $recomendaciones = $this->analizarPatronesHistoricos($armadosHistoricos, $presupuesto);
            } else {
                // 4. Si no hay historial, usar componentes por rendimiento
                $recomendaciones = $this->recomendacionesPorRendimiento($presupuesto);
            }
        }

        // 5. Aplicar filtros de compatibilidad
        $recomendaciones = $this->aplicarFiltrosCompatibilidad($recomendaciones, $presupuesto);

        Log::info('🎯 Recomendaciones finales generadas', [
            'total_componentes' => count($recomendaciones)
        ]);

        return $recomendaciones;
    }

    /**
     * Analizar patrones históricos del usuario
     */
    private function analizarPatronesHistoricos($armadosHistoricos, float $presupuesto): array
    {
        Log::info('🔍 Analizando patrones históricos del usuario');

        // Extraer todos los componentes de armados históricos
        $componentesHistoricos = collect();
        foreach ($armadosHistoricos as $armado) {
            foreach ($armado->detallesArmado as $detalle) {
                $componentesHistoricos->push($detalle->componente);
            }
        }

        // Calcular frecuencia y preferencias
        $frecuenciaComponentes = $componentesHistoricos->groupBy('id_componente')
            ->map(function ($group) {
                return [
                    'componente' => $group->first(),
                    'frecuencia' => $group->count(),
                    'precio_promedio' => $group->avg('precio')
                ];
            })
            ->sortByDesc('frecuencia');

        // Obtener componentes únicos ordenados por popularidad
        $componentesPopulares = $frecuenciaComponentes->pluck('componente')->unique('id_componente');

        Log::info('📈 Patrones históricos analizados', [
            'componentes_unicos' => $componentesPopulares->count()
        ]);

        return $this->organizarComponentesPorCategoria($componentesPopulares, $presupuesto);
    }

    /**
     * Recomendaciones basadas en rendimiento cuando no hay historial
     */
    private function recomendacionesPorRendimiento(float $presupuesto): array
    {
        Log::info('⚡ Generando recomendaciones por rendimiento');

        // Obtener componentes ordenados por precio (ya que no tienen rendimiento)
        $componentes = Componente::orderBy('precio', 'desc')
            ->where('precio', '<=', $presupuesto * 0.4) // Máximo 40% del presupuesto por componente
            ->whereNotNull('categoria') // Asegurar que tengan categoría
            ->get();

        Log::info('⚡ Componentes por precio obtenidos', [
            'total_componentes' => $componentes->count()
        ]);

        return $this->organizarComponentesPorCategoria($componentes, $presupuesto);
    }

    /**
     * Organizar componentes por categoría con IA
     */
    private function organizarComponentesPorCategoria($componentes, float $presupuesto): array
    {
        $categorias = [
            'Procesador' => ['CPU', 'Procesador'],
            'Tarjeta Gráfica' => ['GPU', 'Tarjeta Gráfica'],
            'Memoria RAM' => ['RAM', 'Memoria RAM'],
            'Placa Base' => ['Motherboard', 'Placa Base'],
            'Disco Duro' => ['Storage', 'Disco Duro', 'SSD'],
            'Fuente de Poder' => ['PSU', 'Fuente de Poder'],
            'Gabinete' => ['Case', 'Gabinete']
        ];

        $armado = [];
        $presupuestoRestante = $presupuesto;
        $categoriasSeleccionadas = []; // Para evitar duplicados
        $distribucionPresupuesto = [
            'Procesador' => 0.25,        // 25% del presupuesto
            'Tarjeta Gráfica' => 0.35,   // 35% del presupuesto
            'Memoria RAM' => 0.10,       // 10% del presupuesto
            'Placa Base' => 0.15,        // 15% del presupuesto
            'Disco Duro' => 0.08,        // 8% del presupuesto
            'Fuente de Poder' => 0.05,   // 5% del presupuesto
            'Gabinete' => 0.02           // 2% del presupuesto
        ];

        Log::info('🎯 Organizando componentes por categoría con IA');

        foreach ($categorias as $categoriaPrincipal => $categoriasAlias) {
            // Verificar que no se haya seleccionado ya un componente de esta categoría
            if (in_array($categoriaPrincipal, $categoriasSeleccionadas)) {
                Log::info("⏭️ Categoría {$categoriaPrincipal} ya seleccionada, saltando...");
                continue;
            }

            $componentesCategoria = $componentes->filter(function ($componente) use ($categoriasAlias) {
                return $componente && in_array($componente->categoria, $categoriasAlias);
            });
            
            if ($componentesCategoria->isNotEmpty()) {
                // Calcular presupuesto para esta categoría
                $presupuestoCategoria = $presupuesto * ($distribucionPresupuesto[$categoriaPrincipal] ?? 0.1);
                
                // Seleccionar el mejor componente dentro del presupuesto de la categoría
                $mejorComponente = $componentesCategoria
                    ->where('precio', '<=', $presupuestoCategoria)
                    ->sortByDesc('precio') // Ordenar por precio en lugar de rendimiento
                    ->first();

                // Si no encuentra componente en el presupuesto de categoría, buscar en el presupuesto restante
                if (!$mejorComponente) {
                    $mejorComponente = $componentesCategoria
                        ->where('precio', '<=', $presupuestoRestante)
                        ->sortByDesc('precio') // Ordenar por precio en lugar de rendimiento
                        ->first();
                }

                if ($mejorComponente) {
                    $armado[] = $mejorComponente;
                    $presupuestoRestante -= $mejorComponente->precio;
                    $categoriasSeleccionadas[] = $categoriaPrincipal; // Marcar como seleccionada
                    
                    Log::info("✅ Componente seleccionado para {$categoriaPrincipal}", [
                        'componente' => $mejorComponente->nombre ?? $mejorComponente->id_componente,
                        'precio' => $mejorComponente->precio,
                        'categoria' => $mejorComponente->categoria ?? 'Sin categoría'
                    ]);
                }
            }
        }

        Log::info('🎯 Armado organizado por categorías', [
            'total_componentes' => count($armado),
            'presupuesto_restante' => $presupuestoRestante,
            'categorias_seleccionadas' => $categoriasSeleccionadas
        ]);

        return $armado;
    }

    /**
     * Aplicar filtros de compatibilidad
     */
    private function aplicarFiltrosCompatibilidad(array $componentes, float $presupuesto): array
    {
        Log::info('🔧 Aplicando filtros de compatibilidad');

        // Por ahora, simplemente verificar que no exceda el presupuesto
        $componentesFiltrados = [];
        $precioTotal = 0;

        foreach ($componentes as $componente) {
            if ($componente && $precioTotal + $componente->precio <= $presupuesto) {
                $componentesFiltrados[] = $componente;
                $precioTotal += $componente->precio;
            }
        }

        Log::info('🔧 Filtros de compatibilidad aplicados', [
            'componentes_originales' => count($componentes),
            'componentes_filtrados' => count($componentesFiltrados),
            'precio_total' => $precioTotal
        ]);

        return $componentesFiltrados;
    }

    /**
     * Crear armado con las recomendaciones
     */
    private function crearArmadoConRecomendaciones(array $recomendaciones, ?int $idUsuario, float $presupuesto): array
    {
        // Calcular precio total
        $precioTotal = 0;
        foreach ($recomendaciones as $componente) {
            if ($componente) {
                $precioTotal += $componente->precio;
            }
        }

        // Crear el armado (sin usuario si es null)
        $armado = Armado::create([
            'id_usuario' => $idUsuario ?? 1, // Usar usuario por defecto si no se especifica
            'presupuesto' => $presupuesto,
            'estado' => 'Cotizacion',
            'metodo_creacion' => 'IA'
        ]);

        // Crear detalles del armado
        $detalles = [];
        foreach ($recomendaciones as $componente) {
            if ($componente) {
                $detalle = DetalleArmado::create([
                    'id_armado' => $armado->id_armado,
                    'id_componente' => $componente->id_componente,
                    'cantidad' => 1,
                    'precio_unitario' => $componente->precio,
                    'subtotal' => $componente->precio
                ]);
                $detalles[] = $detalle;
            }
        }

        Log::info('💾 Armado guardado en base de datos', [
            'id_armado' => $armado->id_armado,
            'total_componentes' => count($detalles),
            'precio_total' => $precioTotal
        ]);

        return [
            'armado' => $armado,
            'detalles' => $detalles,
            'componentes' => $recomendaciones,
            'precio_total' => $precioTotal,
            'presupuesto_original' => $presupuesto
        ];
    }
} 