<?php

namespace App\Http\Controllers;

use App\Models\Armado;
use App\Models\Componente;
use App\Models\Compatibilidad;
use App\Models\DetalleArmado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ArmadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Armado::with(['usuario', 'detallesArmado.componente']);
        
        // Filtrar por usuario si se proporciona el parámetro
        if ($request->has('usuario_id')) {
            $query->where('id_usuario', $request->usuario_id);
        }
        
        $armados = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $armados
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $componentes = Componente::where('stock', '>', 0)->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de armado',
            'componentes' => $componentes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'presupuesto' => 'required|numeric|min:0',
            'estado' => 'sometimes|string|in:Cotizacion,Completado,Cancelado'
        ]);

        $armado = Armado::create([
            'id_usuario' => $request->id_usuario,
            'presupuesto' => $request->presupuesto,
            'estado' => $request->estado ?? 'Cotizacion',
            'metodo_creacion' => 'Manual'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Armado creado exitosamente',
            'data' => $armado
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $armado = Armado::with(['usuario', 'detallesArmado.componente'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $armado
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $armado = Armado::with(['usuario', 'detallesArmado.componente'])->findOrFail($id);
        $componentes = Componente::where('stock', '>', 0)->get();
        
        return response()->json([
            'success' => true,
            'data' => $armado,
            'componentes' => $componentes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'sometimes|required|exists:usuarios,id_usuario',
            'presupuesto' => 'sometimes|required|numeric|min:0',
            'estado' => 'sometimes|string'
        ]);

        $armado = Armado::findOrFail($id);
        $armado->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Armado actualizado exitosamente',
            'data' => $armado
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $armado = Armado::findOrFail($id);
        $armado->delete();

        return response()->json([
            'success' => true,
            'message' => 'Armado eliminado exitosamente'
        ]);
    }

    /**
     * Generar armado automático según presupuesto y gama
     */
    public function generarArmadoAutomatico(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'presupuesto' => 'required|numeric|min:0',
            'gama' => 'required|in:Media,Baja'
        ]);

        try {
            DB::beginTransaction();

            // Crear el armado
            $armado = Armado::create([
                'id_usuario' => $request->id_usuario,
                'presupuesto' => $request->presupuesto,
                'estado' => 'Cotizacion',
                'metodo_creacion' => 'IA'
            ]);

            $presupuestoRestante = $request->presupuesto;
            $componentesSeleccionados = [];
            $categorias = ['CPU', 'Motherboard', 'RAM', 'Storage', 'GPU', 'PSU', 'Case'];

            // Seleccionar componentes por categoría
            foreach ($categorias as $categoria) {
                $componente = $this->seleccionarMejorComponente($categoria, $request->gama, $presupuestoRestante);
                
                if ($componente) {
                    $componentesSeleccionados[] = $componente;
                    $presupuestoRestante -= $componente->precio;
                    
                    // Crear detalle del armado
                    DetalleArmado::create([
                        'id_armado' => $armado->id_armado,
                        'id_componente' => $componente->id_componente,
                        'cantidad' => 1,
                        'precio_unitario' => $componente->precio
                    ]);
                }
            }

            // Calcular compatibilidad general
            $compatibilidadGeneral = $this->calcularCompatibilidadGeneral($componentesSeleccionados);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Armado generado exitosamente',
                'data' => [
                    'armado' => $armado->load(['detallesArmado.componente']),
                    'componentes_seleccionados' => $componentesSeleccionados,
                    'presupuesto_utilizado' => $request->presupuesto - $presupuestoRestante,
                    'presupuesto_restante' => $presupuestoRestante,
                    'compatibilidad_general' => $compatibilidadGeneral
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el armado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar componente al armado
     */
    public function agregarComponente(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_componente' => 'required|exists:componentes,id_componente',
            'cantidad' => 'required|integer|min:1'
        ]);

        $armado = Armado::findOrFail($id);
        $componente = Componente::findOrFail($request->id_componente);

        // Verificar stock
        if ($componente->stock < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente para este componente'
            ], 400);
        }

        // Verificar si ya existe en el armado
        $detalleExistente = DetalleArmado::where('id_armado', $id)
                                        ->where('id_componente', $request->id_componente)
                                        ->first();

        if ($detalleExistente) {
            $detalleExistente->update([
                'cantidad' => $detalleExistente->cantidad + $request->cantidad
            ]);
        } else {
            DetalleArmado::create([
                'id_armado' => $id,
                'id_componente' => $request->id_componente,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $componente->precio
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Componente agregado al armado exitosamente'
        ]);
    }

    /**
     * Remover componente del armado
     */
    public function removerComponente(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_componente' => 'required|exists:componentes,id_componente'
        ]);

        $detalle = DetalleArmado::where('id_armado', $id)
                                ->where('id_componente', $request->id_componente)
                                ->firstOrFail();

        $detalle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Componente removido del armado exitosamente'
        ]);
    }

    /**
     * Calcular total del armado
     */
    public function calcularTotal(string $id): JsonResponse
    {
        $armado = Armado::with(['detallesArmado.componente'])->findOrFail($id);
        
        $total = 0;
        foreach ($armado->detallesArmado as $detalle) {
            $total += $detalle->cantidad * $detalle->precio_unitario;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'armado' => $armado,
                'total' => $total,
                'presupuesto' => $armado->presupuesto,
                'diferencia' => $armado->presupuesto - $total
            ]
        ]);
    }

    /**
     * Seleccionar el mejor componente según criterios
     */
    private function seleccionarMejorComponente(string $categoria, string $gama, float $presupuestoRestante)
    {
        return Componente::where('categoria', $categoria)
                        ->where('gama', $gama)
                        ->where('stock', '>', 0)
                        ->where('precio', '<=', $presupuestoRestante)
                        ->orderBy('precio', 'desc')
                        ->first();
    }

    /**
     * Calcular compatibilidad general del armado
     */
    private function calcularCompatibilidadGeneral(array $componentes): float
    {
        if (count($componentes) < 2) {
            return 100;
        }

        $totalCompatibilidad = 0;
        $comparaciones = 0;

        for ($i = 0; $i < count($componentes); $i++) {
            for ($j = $i + 1; $j < count($componentes); $j++) {
                $compatibilidad = Compatibilidad::where(function($query) use ($componentes, $i, $j) {
                    $query->where('id_componente1', $componentes[$i]->id_componente)
                          ->where('id_componente2', $componentes[$j]->id_componente);
                })->orWhere(function($query) use ($componentes, $i, $j) {
                    $query->where('id_componente1', $componentes[$j]->id_componente)
                          ->where('id_componente2', $componentes[$i]->id_componente);
                })->first();

                if ($compatibilidad) {
                    $totalCompatibilidad += $compatibilidad->porcentaje_compatibilidad;
                } else {
                    $totalCompatibilidad += 50; // Compatibilidad neutral si no hay datos
                }
                $comparaciones++;
            }
        }

        return $comparaciones > 0 ? round($totalCompatibilidad / $comparaciones, 2) : 100;
    }
}
