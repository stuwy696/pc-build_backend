<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Venta;
use App\Models\Devolucion;
use App\Models\Componente;
use App\Models\Armado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $tipo = $request->query('tipo');
        $idComponente = $request->query('id_componente');
        $query = Reporte::with(['usuario']);
        
        if ($tipo) {
            $query->where('tipo_reporte', $tipo);
        }
        
        // Filtrar por componente si se especifica
        if ($idComponente) {
            $query->where(function($q) use ($idComponente) {
                $q->whereRaw("JSON_EXTRACT(contenido, '$.filtros.idComponente') = ?", [$idComponente])
                  ->orWhereRaw("JSON_EXTRACT(contenido, '$.filtros.id_componente') = ?", [$idComponente])
                  ->orWhereRaw("contenido LIKE ?", ["%$idComponente%"]);
            });
        }
        
        $reportes = $query->get();
        return response()->json([
            'success' => true,
            'data' => $reportes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Formulario de creación de reporte'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'tipo_reporte' => 'required|string',
            'contenido' => 'required|string',
            'modo' => 'sometimes|string'
        ]);

        $reporte = Reporte::create([
            'id_usuario' => $request->id_usuario,
            'tipo_reporte' => $request->tipo_reporte,
            'contenido' => $request->contenido,
            'fecha_generacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte creado exitosamente',
            'data' => $reporte->load('usuario')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $reporte = Reporte::with(['usuario'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $reporte
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        $reporte = Reporte::with(['usuario'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $reporte
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'sometimes|required|exists:usuarios,id_usuario',
            'tipo_reporte' => 'sometimes|required|string',
            'contenido' => 'sometimes|required|string',
            'modo' => 'sometimes|string'
        ]);

        $reporte = Reporte::findOrFail($id);
        $reporte->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reporte actualizado exitosamente',
            'data' => $reporte->load('usuario')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $reporte = Reporte::findOrFail($id);
        $reporte->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reporte eliminado exitosamente'
        ]);
    }

    /**
     * Generar reporte de ventas
     */
    public function reporteVentas(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_componente' => 'sometimes|exists:componentes,id_componente'
        ]);

        $ventas = Venta::with(['armado.detallesArmado.componente', 'usuarioEmpleado'])
                      ->whereBetween('fecha_venta', [$request->fecha_inicio, $request->fecha_fin]);

        // Filtrar por componente si se especifica
        if ($request->has('id_componente') && $request->id_componente) {
            $ventas = $ventas->whereHas('armado.detallesArmado', function ($query) use ($request) {
                $query->where('id_componente', $request->id_componente);
            });
        }

        $ventas = $ventas->get();

        $totalVentas = $ventas->sum('total');
        $cantidadVentas = $ventas->count();
        $promedioVenta = $cantidadVentas > 0 ? round($totalVentas / $cantidadVentas, 2) : 0;

        // Ventas por empleado
        $ventasPorEmpleado = $ventas->groupBy('id_usuario_empleado')
                                   ->map(function ($ventasEmpleado) {
                                       return [
                                           'empleado' => $ventasEmpleado->first()->usuarioEmpleado,
                                           'total_ventas' => $ventasEmpleado->sum('total'),
                                           'cantidad_ventas' => $ventasEmpleado->count()
                                       ];
                                   });

        // Componentes más vendidos
        $componentesVendidos = [];
        foreach ($ventas as $venta) {
            foreach ($venta->armado->detallesArmado as $detalle) {
                $idComponente = $detalle->id_componente;
                if (!isset($componentesVendidos[$idComponente])) {
                    $componentesVendidos[$idComponente] = [
                        'componente' => $detalle->componente,
                        'cantidad_vendida' => 0,
                        'total_ventas' => 0
                    ];
                }
                $componentesVendidos[$idComponente]['cantidad_vendida'] += $detalle->cantidad;
                $componentesVendidos[$idComponente]['total_ventas'] += $detalle->cantidad * $detalle->precio_unitario;
            }
        }

        $datosReporte = [
            'periodo' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin
            ],
            'resumen' => [
                'total_ventas' => $totalVentas,
                'cantidad_ventas' => $cantidadVentas,
                'promedio_venta' => $promedioVenta
            ],
            'ventas_por_empleado' => $ventasPorEmpleado,
            'componentes_mas_vendidos' => collect($componentesVendidos)
                ->sortByDesc('cantidad_vendida')
                ->take(10)
                ->values()
        ];

        // Guardar reporte
        Reporte::create([
            'id_usuario' => $request->id_usuario,
            'tipo_reporte' => 'Ventas',
            'contenido' => json_encode($datosReporte),
            'fecha_generacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte de ventas generado exitosamente',
            'data' => $datosReporte
        ]);
    }

    /**
     * Generar reporte de devoluciones
     */
    public function reporteDevoluciones(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_componente' => 'sometimes|exists:componentes,id_componente'
        ]);

        $devoluciones = Devolucion::with(['venta.armado.detallesArmado.componente', 'componente'])
                                 ->whereBetween('fecha_devolucion', [$request->fecha_inicio, $request->fecha_fin]);

        // Filtrar por componente si se especifica
        if ($request->has('id_componente') && $request->id_componente) {
            $devoluciones = $devoluciones->where('id_componente', $request->id_componente);
        }

        $devoluciones = $devoluciones->get();

        $resumen = [
            'total_devoluciones' => $devoluciones->count(),
            'devoluciones_aprobadas' => $devoluciones->where('estado', 'Aprobada')->count(),
            'devoluciones_pendientes' => $devoluciones->where('estado', 'Pendiente')->count(),
            'devoluciones_rechazadas' => $devoluciones->where('estado', 'Rechazada')->count(),
            'total_cantidad_devuelta' => $devoluciones->sum('cantidad_devuelta')
        ];

        // Componentes más devueltos
        $componentesDevueltos = $devoluciones->groupBy('id_componente')
                                            ->map(function ($devolucionesComponente) {
                                                return [
                                                    'componente' => $devolucionesComponente->first()->componente,
                                                    'cantidad_devuelta' => $devolucionesComponente->sum('cantidad_devuelta'),
                                                    'cantidad_devoluciones' => $devolucionesComponente->count()
                                                ];
                                            })
                                            ->sortByDesc('cantidad_devuelta')
                                            ->take(10);

        // Motivos más comunes
        $motivosComunes = $devoluciones->groupBy('motivo')
                                     ->map(function ($devolucionesMotivo) {
                                         return [
                                             'motivo' => $devolucionesMotivo->first()->motivo,
                                             'cantidad' => $devolucionesMotivo->count()
                                         ];
                                     })
                                     ->sortByDesc('cantidad')
                                     ->take(5);

        $datosReporte = [
            'periodo' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin
            ],
            'resumen' => $resumen,
            'componentes_mas_devueltos' => $componentesDevueltos,
            'motivos_comunes' => $motivosComunes
        ];

        // Guardar reporte
        Reporte::create([
            'id_usuario' => $request->id_usuario,
            'tipo_reporte' => 'Devoluciones',
            'contenido' => json_encode($datosReporte),
            'fecha_generacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte de devoluciones generado exitosamente',
            'data' => $datosReporte
        ]);
    }

    /**
     * Generar reporte de inventario
     */
    public function reporteInventario(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario'
        ]);

        $componentes = Componente::all();

        $resumen = [
            'total_componentes' => $componentes->count(),
            'componentes_con_stock' => $componentes->where('stock', '>', 0)->count(),
            'componentes_sin_stock' => $componentes->where('stock', 0)->count(),
            'valor_total_inventario' => $componentes->sum(function ($componente) {
                return $componente->precio * $componente->stock;
            })
        ];

        // Stock por categoría
        $stockPorCategoria = $componentes->groupBy('categoria')
                                        ->map(function ($componentesCategoria) {
                                            return [
                                                'categoria' => $componentesCategoria->first()->categoria,
                                                'total_componentes' => $componentesCategoria->count(),
                                                'total_stock' => $componentesCategoria->sum('stock'),
                                                'valor_total' => $componentesCategoria->sum(function ($comp) {
                                                    return $comp->precio * $comp->stock;
                                                })
                                            ];
                                        });

        // Stock por gama
        $stockPorGama = $componentes->groupBy('gama')
                                   ->map(function ($componentesGama) {
                                       return [
                                           'gama' => $componentesGama->first()->gama,
                                           'total_componentes' => $componentesGama->count(),
                                           'total_stock' => $componentesGama->sum('stock'),
                                           'valor_total' => $componentesGama->sum(function ($comp) {
                                               return $comp->precio * $comp->stock;
                                           })
                                       ];
                                   });

        // Componentes con bajo stock (menos de 5 unidades)
        $componentesBajoStock = $componentes->where('stock', '<', 5)
                                           ->sortBy('stock')
                                           ->values();

        $datosReporte = [
            'fecha_generacion' => now()->format('Y-m-d H:i:s'),
            'resumen' => $resumen,
            'stock_por_categoria' => $stockPorCategoria,
            'stock_por_gama' => $stockPorGama,
            'componentes_bajo_stock' => $componentesBajoStock
        ];

        // Guardar reporte
        Reporte::create([
            'id_usuario' => $request->id_usuario,
            'tipo_reporte' => 'Inventario',
            'contenido' => json_encode($datosReporte),
            'fecha_generacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte de inventario generado exitosamente',
            'data' => $datosReporte
        ]);
    }

    /**
     * Generar reporte de estadísticas generales
     */
    public function reporteEstadisticas(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario'
        ]);

        // Estadísticas de ventas del mes actual
        $mesActual = now()->startOfMonth();
        $ventasMes = Venta::where('fecha_venta', '>=', $mesActual)->get();
        
        // Estadísticas de devoluciones del mes actual
        $devolucionesMes = Devolucion::where('fecha_devolucion', '>=', $mesActual)->get();

        // Componentes más populares (por ventas)
        $componentesPopulares = DB::table('detalles_armado')
                                 ->join('componentes', 'detalles_armado.id_componente', '=', 'componentes.id_componente')
                                 ->select('componentes.*', DB::raw('SUM(detalles_armado.cantidad) as total_vendido'))
                                 ->groupBy('componentes.id_componente')
                                 ->orderBy('total_vendido', 'desc')
                                 ->limit(10)
                                 ->get();

        $estadisticas = [
            'ventas_mes_actual' => [
                'total_ventas' => $ventasMes->sum('total'),
                'cantidad_ventas' => $ventasMes->count(),
                'promedio_venta' => $ventasMes->count() > 0 ? round($ventasMes->sum('total') / $ventasMes->count(), 2) : 0
            ],
            'devoluciones_mes_actual' => [
                'total_devoluciones' => $devolucionesMes->count(),
                'devoluciones_aprobadas' => $devolucionesMes->where('estado', 'Aprobada')->count(),
                'total_cantidad_devuelta' => $devolucionesMes->sum('cantidad_devuelta')
            ],
            'inventario' => [
                'total_componentes' => Componente::count(),
                'componentes_con_stock' => Componente::where('stock', '>', 0)->count(),
                'valor_total_inventario' => Componente::sum(DB::raw('precio * stock'))
            ],
            'componentes_populares' => $componentesPopulares
        ];

        // Guardar reporte
        Reporte::create([
            'id_usuario' => $request->id_usuario,
            'tipo_reporte' => 'Estadísticas',
            'contenido' => json_encode($estadisticas),
            'fecha_generacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reporte de estadísticas generado exitosamente',
            'data' => $estadisticas
        ]);
    }

    /**
     * Generar reporte automático según el tipo
     */
    public function generarAutomatico(Request $request): JsonResponse
    {
        $tipo = ucfirst(strtolower($request->input('tipo_reporte')));
        $idUsuario = $request->input('id_usuario');
        $idComponente = $request->input('id_componente');
        
        if (!$tipo || !$idUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros requeridos (tipo_reporte, id_usuario)'
            ], 400);
        }

        // Puedes ajustar los parámetros de fechas según tu lógica de negocio
        $fecha_inicio = now()->startOfMonth()->toDateString();
        $fecha_fin = now()->toDateString();

        if ($tipo === 'Ventas') {
            $requestData = [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'id_usuario' => $idUsuario
            ];
            if ($idComponente) {
                $requestData['id_componente'] = $idComponente;
            }
            $request2 = new Request($requestData);
            return $this->reporteVentas($request2);
        } else if ($tipo === 'Inventario') {
            $request2 = new Request([
                'id_usuario' => $idUsuario
            ]);
            return $this->reporteInventario($request2);
        } else if ($tipo === 'Devoluciones') {
            $requestData = [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'id_usuario' => $idUsuario
            ];
            if ($idComponente) {
                $requestData['id_componente'] = $idComponente;
            }
            $request2 = new Request($requestData);
            return $this->reporteDevoluciones($request2);
        } else if ($tipo === 'Estadisticas') {
            $request2 = new Request([
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'id_usuario' => $idUsuario
            ]);
            return $this->reporteEstadisticas($request2);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de reporte no soportado'
            ], 400);
        }
    }
}
