<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglaCompatibilidad extends Model
{
    use HasFactory;

    protected $table = 'reglas_compatibilidad';
    protected $primaryKey = 'id_regla';

    protected $fillable = [
        'categoria_origen',
        'categoria_destino',
        'tipo_regla',
        'condicion_origen',
        'condicion_destino',
        'porcentaje_compatibilidad',
        'activo'
    ];

    protected $casts = [
        'condicion_origen' => 'array',
        'condicion_destino' => 'array',
        'activo' => 'boolean'
    ];

    /**
     * Verificar si dos componentes son compatibles según las reglas
     */
    public static function verificarCompatibilidad($componenteOrigen, $componenteDestino)
    {
        $reglas = self::where('categoria_origen', $componenteOrigen->categoria)
                      ->where('categoria_destino', $componenteDestino->categoria)
                      ->where('activo', true)
                      ->get();

        foreach ($reglas as $regla) {
            $resultado = self::aplicarRegla($componenteOrigen, $componenteDestino, $regla);
            if ($resultado !== false) {
                // Si la regla es de tipo potencia, usar el valor calculado
                if ($regla->tipo_regla === 'potencia') {
                    return $resultado; // Ya es un porcentaje
                } else {
                    return $regla->porcentaje_compatibilidad;
                }
            }
        }

        return 0; // No compatible
    }

    /**
     * Aplicar una regla específica a dos componentes
     */
    private static function aplicarRegla($origen, $destino, $regla)
    {
        $nombreOrigen = strtolower($origen->nombre);
        $nombreDestino = strtolower($destino->nombre);
        $especificacionesOrigen = strtolower($origen->especificaciones ?? '');
        $especificacionesDestino = strtolower($destino->especificaciones ?? '');

        switch ($regla->tipo_regla) {
            case 'fabricante':
                return self::verificarFabricante($nombreOrigen, $nombreDestino, $regla);
            
            case 'socket':
                return self::verificarSocket($nombreOrigen, $nombreDestino, $regla);
            
            case 'ddr':
                return self::verificarDDR($nombreOrigen, $nombreDestino, $regla);
            
            case 'potencia':
                return self::verificarPotencia($nombreOrigen, $nombreDestino, $regla);
            
            case 'tamaño':
                return self::verificarTamaño($nombreOrigen, $nombreDestino, $regla);
            
            case 'conector':
                return self::verificarConector($nombreOrigen, $nombreDestino, $regla);
            
            default:
                return false;
        }
    }

    private static function verificarFabricante($nombreOrigen, $nombreDestino, $regla)
    {
        $fabricantesOrigen = $regla->condicion_origen['fabricantes'] ?? [];
        $fabricantesDestino = $regla->condicion_destino['fabricantes'] ?? [];

        $origenValido = false;
        $destinoValido = false;

        foreach ($fabricantesOrigen as $fabricante) {
            if (str_contains($nombreOrigen, strtolower($fabricante))) {
                $origenValido = true;
                break;
            }
        }

        foreach ($fabricantesDestino as $fabricante) {
            if (str_contains($nombreDestino, strtolower($fabricante))) {
                $destinoValido = true;
                break;
            }
        }

        return $origenValido && $destinoValido;
    }

    private static function verificarSocket($nombreOrigen, $nombreDestino, $regla)
    {
        $socketsOrigen = $regla->condicion_origen['sockets'] ?? [];
        $socketsDestino = $regla->condicion_destino['sockets'] ?? [];

        $origenValido = false;
        $destinoValido = false;

        foreach ($socketsOrigen as $socket) {
            if (str_contains($nombreOrigen, strtolower($socket))) {
                $origenValido = true;
                break;
            }
        }

        foreach ($socketsDestino as $socket) {
            if (str_contains($nombreDestino, strtolower($socket))) {
                $destinoValido = true;
                break;
            }
        }

        return $origenValido && $destinoValido;
    }

    private static function verificarDDR($nombreOrigen, $nombreDestino, $regla)
    {
        $tiposDDROrigen = $regla->condicion_origen['tipos_ddr'] ?? [];
        $tiposDDRDestino = $regla->condicion_destino['tipos_ddr'] ?? [];
        
        // Si no hay condiciones específicas, asumir compatibilidad general
        if (empty($tiposDDROrigen) && empty($tiposDDRDestino)) {
            return true;
        }
        
        // Verificar si el origen tiene algún tipo DDR especificado
        $origenValido = false;
        if (empty($tiposDDROrigen)) {
            $origenValido = true; // Sin restricciones en origen
        } else {
            foreach ($tiposDDROrigen as $tipo) {
                if (str_contains($nombreOrigen, strtolower($tipo))) {
                    $origenValido = true;
                    break;
                }
            }
        }
        
        // Verificar si el destino tiene algún tipo DDR especificado
        $destinoValido = false;
        if (empty($tiposDDRDestino)) {
            $destinoValido = true; // Sin restricciones en destino
        } else {
            foreach ($tiposDDRDestino as $tipo) {
                if (str_contains($nombreDestino, strtolower($tipo))) {
                    $destinoValido = true;
                    break;
                }
            }
        }
        
        return $origenValido && $destinoValido;
    }

    private static function verificarPotencia($nombreOrigen, $nombreDestino, $regla)
    {
        // Extraer potencia de la PSU
        preg_match('/(\d+)w/', $nombreDestino, $matches);
        $potenciaPSU = isset($matches[1]) ? (int)$matches[1] : 0;

        // Consumo estimado de GPU
        $consumoGPU = 0;
        if (str_contains($nombreOrigen, 'rtx 4090') || str_contains($nombreOrigen, 'rtx 4080')) {
            $consumoGPU = 350;
        } elseif (str_contains($nombreOrigen, 'rtx 4070') || str_contains($nombreOrigen, 'rtx 3080')) {
            $consumoGPU = 300;
        } elseif (str_contains($nombreOrigen, 'rtx 3070') || str_contains($nombreOrigen, 'nvidia rtx 3070')) {
            $consumoGPU = 250; // RTX 3070 necesita más potencia
        } elseif (str_contains($nombreOrigen, 'rtx 3060') || str_contains($nombreOrigen, 'nvidia rtx 3060')) {
            $consumoGPU = 200; // RTX 3060 necesita más potencia
        } elseif (str_contains($nombreOrigen, 'rtx 3050')) {
            $consumoGPU = 130;
        } elseif (str_contains($nombreOrigen, 'gtx 1650')) {
            $consumoGPU = 100;
        } elseif (str_contains($nombreOrigen, 'gt 730')) {
            $consumoGPU = 50;
        } elseif (str_contains($nombreOrigen, 'gt 610')) {
            $consumoGPU = 30;
        } elseif (str_contains($nombreOrigen, 'rx 6700')) {
            $consumoGPU = 230;
        } elseif (str_contains($nombreOrigen, 'rx 6600')) {
            $consumoGPU = 160;
        } elseif (str_contains($nombreOrigen, 'rx 580')) {
            $consumoGPU = 185;
        }

        if ($potenciaPSU == 0 || $consumoGPU == 0) {
            return 75; // Compatibilidad básica si no se puede determinar
        }

        // Calcular potencia requerida total del sistema
        // GPU + CPU (100-150W) + Motherboard (50W) + RAM (30W) + Storage (20W) + Ventiladores (30W) + Margen de seguridad (200W)
        $potenciaRequerida = $consumoGPU + 430; // GPU + resto del sistema + margen de seguridad
        
        if ($potenciaPSU >= $potenciaRequerida + 150) {
            return 95; // Excelente - PSU con mucha potencia de sobra
        } elseif ($potenciaPSU >= $potenciaRequerida) {
            return 85; // Buena - PSU adecuada
        } elseif ($potenciaPSU >= $potenciaRequerida - 100) {
            return 70; // Regular - PSU al límite
        } else {
            return 0; // No compatible - PSU insuficiente
        }
    }

    private static function verificarTamaño($nombreOrigen, $nombreDestino, $regla)
    {
        // Lógica para verificar compatibilidad de tamaño (GPU-Case)
        return true; // Por ahora, compatibilidad general
    }

    private static function verificarConector($nombreOrigen, $nombreDestino, $regla)
    {
        // Lógica para verificar conectores (Storage-Motherboard)
        return true; // Por ahora, compatibilidad general
    }
} 