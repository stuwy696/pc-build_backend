<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReglaCompatibilidad;

class ReglasCompatibilidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar reglas existentes
        ReglaCompatibilidad::truncate();

        // Regla 1: CPU AMD - Motherboard AMD
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'Motherboard',
            'tipo_regla' => 'fabricante',
            'condicion_origen' => [
                'fabricantes' => ['amd', 'ryzen']
            ],
            'condicion_destino' => [
                'fabricantes' => ['amd', 'a520', 'b450', 'b550', 'x570', 'a620']
            ],
            'porcentaje_compatibilidad' => 95,
            'activo' => true
        ]);

        // Regla 2: CPU Intel - Motherboard Intel
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'Motherboard',
            'tipo_regla' => 'fabricante',
            'condicion_origen' => [
                'fabricantes' => ['intel', 'core i']
            ],
            'condicion_destino' => [
                'fabricantes' => ['intel', 'h610', 'b660', 'z690', 'h370', 'h470', 'h510']
            ],
            'porcentaje_compatibilidad' => 95,
            'activo' => true
        ]);

        // Regla 3: Motherboard DDR4 - RAM DDR4
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Motherboard',
            'categoria_destino' => 'RAM',
            'tipo_regla' => 'ddr',
            'condicion_origen' => [
                'tipos_ddr' => ['ddr4']
            ],
            'condicion_destino' => [
                'tipos_ddr' => ['ddr4']
            ],
            'porcentaje_compatibilidad' => 95,
            'activo' => true
        ]);

        // Regla 4: Motherboard DDR5 - RAM DDR5
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Motherboard',
            'categoria_destino' => 'RAM',
            'tipo_regla' => 'ddr',
            'condicion_origen' => [
                'tipos_ddr' => ['ddr5']
            ],
            'condicion_destino' => [
                'tipos_ddr' => ['ddr5']
            ],
            'porcentaje_compatibilidad' => 95,
            'activo' => true
        ]);

        // Regla 5: Motherboard DDR3 - RAM DDR3
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Motherboard',
            'categoria_destino' => 'RAM',
            'tipo_regla' => 'ddr',
            'condicion_origen' => [
                'tipos_ddr' => ['ddr3']
            ],
            'condicion_destino' => [
                'tipos_ddr' => ['ddr3']
            ],
            'porcentaje_compatibilidad' => 95,
            'activo' => true
        ]);

        // Regla 6: GPU - PSU (por potencia)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'GPU',
            'categoria_destino' => 'PSU',
            'tipo_regla' => 'potencia',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 7: GPU - Case (por tamaño)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'GPU',
            'categoria_destino' => 'Case',
            'tipo_regla' => 'tamaño',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 8: Motherboard - Case (por factor de forma)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Motherboard',
            'categoria_destino' => 'Case',
            'tipo_regla' => 'tamaño',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 9: Storage - Motherboard (por conectores)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Storage',
            'categoria_destino' => 'Motherboard',
            'tipo_regla' => 'conector',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 10: CPU - RAM (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'RAM',
            'tipo_regla' => 'ddr',
            'condicion_origen' => [
                'tipos_ddr' => ['ddr4', 'ddr5']
            ],
            'condicion_destino' => [
                'tipos_ddr' => ['ddr4', 'ddr5']
            ],
            'porcentaje_compatibilidad' => 90,
            'activo' => true
        ]);

        // Regla 11: CPU - Storage (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'Storage',
            'tipo_regla' => 'conector',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 80,
            'activo' => true
        ]);

        // Regla 12: RAM - Storage (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'RAM',
            'categoria_destino' => 'Storage',
            'tipo_regla' => 'conector',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 13: CPU - PSU (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'PSU',
            'tipo_regla' => 'potencia',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 80,
            'activo' => true
        ]);

        // Regla 14: RAM - PSU (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'RAM',
            'categoria_destino' => 'PSU',
            'tipo_regla' => 'potencia',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 15: Storage - PSU (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Storage',
            'categoria_destino' => 'PSU',
            'tipo_regla' => 'potencia',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 80,
            'activo' => true
        ]);

        // Regla 16: CPU - Case (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'Case',
            'tipo_regla' => 'tamaño',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 80,
            'activo' => true
        ]);

        // Regla 17: RAM - Case (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'RAM',
            'categoria_destino' => 'Case',
            'tipo_regla' => 'tamaño',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        // Regla 18: Storage - Case (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'Storage',
            'categoria_destino' => 'Case',
            'tipo_regla' => 'tamaño',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 80,
            'activo' => true
        ]);

        // Regla 19: CPU - GPU (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'CPU',
            'categoria_destino' => 'GPU',
            'tipo_regla' => 'fabricante',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 80,
            'activo' => true
        ]);

        // Regla 20: GPU - RAM (general)
        ReglaCompatibilidad::create([
            'categoria_origen' => 'GPU',
            'categoria_destino' => 'RAM',
            'tipo_regla' => 'ddr',
            'condicion_origen' => [],
            'condicion_destino' => [],
            'porcentaje_compatibilidad' => 85,
            'activo' => true
        ]);

        $this->command->info('Reglas de compatibilidad creadas exitosamente.');
    }
} 