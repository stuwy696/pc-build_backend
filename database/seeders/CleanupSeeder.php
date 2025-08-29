<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Componente;
use App\Models\Compatibilidad;

class CleanupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear componentes de ejemplo si no existen
        $this->createComponentes();
        
        // Crear compatibilidades de ejemplo
        $this->createCompatibilidades();
    }

    private function createComponentes()
    {
        $componentes = [
            // CPUs
            ['nombre' => 'Intel Core i5-12400F', 'marca' => 'Intel', 'modelo' => 'i5-12400F', 'categoria' => 'CPU', 'precio' => 180.00, 'stock' => 15, 'gama' => 'Media', 'especificaciones' => '6 cores, 12 threads, 2.5GHz base, 4.4GHz turbo'],
            ['nombre' => 'AMD Ryzen 5 5600X', 'marca' => 'AMD', 'modelo' => 'Ryzen 5 5600X', 'categoria' => 'CPU', 'precio' => 200.00, 'stock' => 12, 'gama' => 'Media', 'especificaciones' => '6 cores, 12 threads, 3.7GHz base, 4.6GHz turbo'],
            ['nombre' => 'Intel Core i7-12700K', 'marca' => 'Intel', 'modelo' => 'i7-12700K', 'categoria' => 'CPU', 'precio' => 350.00, 'stock' => 8, 'gama' => 'Media', 'especificaciones' => '12 cores, 20 threads, 3.6GHz base, 5.0GHz turbo'],
            
            // GPUs
            ['nombre' => 'NVIDIA RTX 3060', 'marca' => 'NVIDIA', 'modelo' => 'RTX 3060', 'categoria' => 'GPU', 'precio' => 350.00, 'stock' => 10, 'gama' => 'Media', 'especificaciones' => '12GB GDDR6, 3584 CUDA cores'],
            ['nombre' => 'AMD RX 6600 XT', 'marca' => 'AMD', 'modelo' => 'RX 6600 XT', 'categoria' => 'GPU', 'precio' => 320.00, 'stock' => 8, 'gama' => 'Media', 'especificaciones' => '8GB GDDR6, 2048 stream processors'],
            ['nombre' => 'NVIDIA RTX 3070', 'marca' => 'NVIDIA', 'modelo' => 'RTX 3070', 'categoria' => 'GPU', 'precio' => 500.00, 'stock' => 6, 'gama' => 'Media', 'especificaciones' => '8GB GDDR6, 5888 CUDA cores'],
            
            // RAM
            ['nombre' => 'Corsair Vengeance 16GB DDR4', 'marca' => 'Corsair', 'modelo' => 'Vengeance', 'categoria' => 'RAM', 'precio' => 80.00, 'stock' => 25, 'gama' => 'Media', 'especificaciones' => '16GB (2x8GB) DDR4-3200'],
            ['nombre' => 'G.Skill Ripjaws 32GB DDR4', 'marca' => 'G.Skill', 'modelo' => 'Ripjaws', 'categoria' => 'RAM', 'precio' => 120.00, 'stock' => 15, 'gama' => 'Media', 'especificaciones' => '32GB (2x16GB) DDR4-3600'],
            ['nombre' => 'Kingston Fury 8GB DDR4', 'marca' => 'Kingston', 'modelo' => 'Fury', 'categoria' => 'RAM', 'precio' => 45.00, 'stock' => 30, 'gama' => 'Baja', 'especificaciones' => '8GB (1x8GB) DDR4-2666'],
            
            // Motherboards
            ['nombre' => 'MSI B660M-A WiFi', 'marca' => 'MSI', 'modelo' => 'B660M-A WiFi', 'categoria' => 'Motherboard', 'precio' => 140.00, 'stock' => 12, 'gama' => 'Media', 'especificaciones' => 'Intel B660, LGA 1700, DDR4, WiFi 6'],
            ['nombre' => 'ASUS ROG B550-F', 'marca' => 'ASUS', 'modelo' => 'ROG B550-F', 'categoria' => 'Motherboard', 'precio' => 160.00, 'stock' => 10, 'gama' => 'Media', 'especificaciones' => 'AMD B550, AM4, DDR4, PCIe 4.0'],
            ['nombre' => 'Gigabyte Z690 Aorus', 'marca' => 'Gigabyte', 'modelo' => 'Z690 Aorus', 'categoria' => 'Motherboard', 'precio' => 250.00, 'stock' => 8, 'gama' => 'Media', 'especificaciones' => 'Intel Z690, LGA 1700, DDR4, PCIe 5.0'],
            
            // Storage
            ['nombre' => 'Samsung 970 EVO 1TB', 'marca' => 'Samsung', 'modelo' => '970 EVO', 'categoria' => 'Storage', 'precio' => 100.00, 'stock' => 20, 'gama' => 'Media', 'especificaciones' => '1TB NVMe M.2 SSD, 3500MB/s read'],
            ['nombre' => 'WD Blue 2TB HDD', 'marca' => 'Western Digital', 'modelo' => 'Blue', 'categoria' => 'Storage', 'precio' => 50.00, 'stock' => 25, 'gama' => 'Baja', 'especificaciones' => '2TB 7200RPM HDD, SATA 6Gb/s'],
            ['nombre' => 'Crucial P5 500GB', 'marca' => 'Crucial', 'modelo' => 'P5', 'categoria' => 'Storage', 'precio' => 60.00, 'stock' => 18, 'gama' => 'Media', 'especificaciones' => '500GB NVMe M.2 SSD, 3400MB/s read'],
            
            // PSU
            ['nombre' => 'EVGA 650W Bronze', 'marca' => 'EVGA', 'modelo' => '650W Bronze', 'categoria' => 'PSU', 'precio' => 70.00, 'stock' => 15, 'gama' => 'Media', 'especificaciones' => '650W, 80+ Bronze, Semi-modular'],
            ['nombre' => 'Corsair RM750x', 'marca' => 'Corsair', 'modelo' => 'RM750x', 'categoria' => 'PSU', 'precio' => 120.00, 'stock' => 10, 'gama' => 'Media', 'especificaciones' => '750W, 80+ Gold, Fully modular'],
            ['nombre' => 'Seasonic 550W', 'marca' => 'Seasonic', 'modelo' => '550W', 'categoria' => 'PSU', 'precio' => 60.00, 'stock' => 20, 'gama' => 'Baja', 'especificaciones' => '550W, 80+ Bronze, Non-modular'],
            
            // Cases
            ['nombre' => 'NZXT H510', 'marca' => 'NZXT', 'modelo' => 'H510', 'categoria' => 'Case', 'precio' => 80.00, 'stock' => 12, 'gama' => 'Media', 'especificaciones' => 'ATX Mid Tower, Tempered Glass'],
            ['nombre' => 'Phanteks P300A', 'marca' => 'Phanteks', 'modelo' => 'P300A', 'categoria' => 'Case', 'precio' => 60.00, 'stock' => 15, 'gama' => 'Baja', 'especificaciones' => 'ATX Mid Tower, Mesh Front'],
            ['nombre' => 'Lian Li O11 Dynamic', 'marca' => 'Lian Li', 'modelo' => 'O11 Dynamic', 'categoria' => 'Case', 'precio' => 150.00, 'stock' => 8, 'gama' => 'Media', 'especificaciones' => 'ATX Mid Tower, Dual Chamber'],
        ];

        foreach ($componentes as $componente) {
            Componente::firstOrCreate(
                ['nombre' => $componente['nombre']],
                $componente
            );
        }
    }

    private function createCompatibilidades()
    {
        // Obtener todos los componentes
        $cpus = Componente::where('categoria', 'CPU')->get();
        $gpus = Componente::where('categoria', 'GPU')->get();
        $rams = Componente::where('categoria', 'RAM')->get();
        $motherboards = Componente::where('categoria', 'Motherboard')->get();
        $storages = Componente::where('categoria', 'Storage')->get();
        $psus = Componente::where('categoria', 'PSU')->get();
        $cases = Componente::where('categoria', 'Case')->get();

        // Compatibilidades CPU - Motherboard
        foreach ($cpus as $cpu) {
            foreach ($motherboards as $motherboard) {
                $compatibilidad = $this->calculateCpuMotherboardCompatibilidad($cpu, $motherboard);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($cpu->id_componente, $motherboard->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades CPU - RAM
        foreach ($cpus as $cpu) {
            foreach ($rams as $ram) {
                $compatibilidad = $this->calculateCpuRamCompatibilidad($cpu, $ram);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($cpu->id_componente, $ram->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades Motherboard - RAM
        foreach ($motherboards as $motherboard) {
            foreach ($rams as $ram) {
                $compatibilidad = $this->calculateMotherboardRamCompatibilidad($motherboard, $ram);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($motherboard->id_componente, $ram->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades GPU - PSU (basado en consumo de energía)
        foreach ($gpus as $gpu) {
            foreach ($psus as $psu) {
                $compatibilidad = $this->calculateGpuPsuCompatibilidad($gpu, $psu);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($gpu->id_componente, $psu->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades GPU - Case (basado en tamaño)
        foreach ($gpus as $gpu) {
            foreach ($cases as $case) {
                $compatibilidad = $this->calculateGpuCaseCompatibilidad($gpu, $case);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($gpu->id_componente, $case->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades Motherboard - Case
        foreach ($motherboards as $motherboard) {
            foreach ($cases as $case) {
                $compatibilidad = $this->calculateMotherboardCaseCompatibilidad($motherboard, $case);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($motherboard->id_componente, $case->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades Storage - Motherboard
        foreach ($storages as $storage) {
            foreach ($motherboards as $motherboard) {
                $compatibilidad = $this->calculateStorageMotherboardCompatibilidad($storage, $motherboard);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($storage->id_componente, $motherboard->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades CPU - GPU (general)
        foreach ($cpus as $cpu) {
            foreach ($gpus as $gpu) {
                $compatibilidad = $this->calculateCpuGpuCompatibilidad($cpu, $gpu);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($cpu->id_componente, $gpu->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades GPU - RAM (general)
        foreach ($gpus as $gpu) {
            foreach ($rams as $ram) {
                $compatibilidad = $this->calculateGpuRamCompatibilidad($gpu, $ram);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($gpu->id_componente, $ram->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades CPU - Storage (general)
        foreach ($cpus as $cpu) {
            foreach ($storages as $storage) {
                $compatibilidad = $this->calculateCpuStorageCompatibilidad($cpu, $storage);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($cpu->id_componente, $storage->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades RAM - Storage (general)
        foreach ($rams as $ram) {
            foreach ($storages as $storage) {
                $compatibilidad = $this->calculateRamStorageCompatibilidad($ram, $storage);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($ram->id_componente, $storage->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades CPU - PSU (general)
        foreach ($cpus as $cpu) {
            foreach ($psus as $psu) {
                $compatibilidad = $this->calculateCpuPsuCompatibilidad($cpu, $psu);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($cpu->id_componente, $psu->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades RAM - PSU (general)
        foreach ($rams as $ram) {
            foreach ($psus as $psu) {
                $compatibilidad = $this->calculateRamPsuCompatibilidad($ram, $psu);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($ram->id_componente, $psu->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades Storage - PSU (general)
        foreach ($storages as $storage) {
            foreach ($psus as $psu) {
                $compatibilidad = $this->calculateStoragePsuCompatibilidad($storage, $psu);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($storage->id_componente, $psu->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades CPU - Case (general)
        foreach ($cpus as $cpu) {
            foreach ($cases as $case) {
                $compatibilidad = $this->calculateCpuCaseCompatibilidad($cpu, $case);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($cpu->id_componente, $case->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades RAM - Case (general)
        foreach ($rams as $ram) {
            foreach ($cases as $case) {
                $compatibilidad = $this->calculateRamCaseCompatibilidad($ram, $case);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($ram->id_componente, $case->id_componente, $compatibilidad);
                }
            }
        }

        // Compatibilidades Storage - Case (general)
        foreach ($storages as $storage) {
            foreach ($cases as $case) {
                $compatibilidad = $this->calculateStorageCaseCompatibilidad($storage, $case);
                if ($compatibilidad > 0) {
                    $this->createCompatibilidad($storage->id_componente, $case->id_componente, $compatibilidad);
                }
            }
        }
    }

    private function createCompatibilidad($id1, $id2, $porcentaje)
    {
        // Verificar que no exista ya esta compatibilidad
        $existing = Compatibilidad::where(function($query) use ($id1, $id2) {
            $query->where('id_componente1', $id1)
                  ->where('id_componente2', $id2);
        })->orWhere(function($query) use ($id1, $id2) {
            $query->where('id_componente1', $id2)
                  ->where('id_componente2', $id1);
        })->first();

        if (!$existing) {
            Compatibilidad::create([
                'id_componente1' => $id1,
                'id_componente2' => $id2,
                'porcentaje_compatibilidad' => $porcentaje
            ]);
        }
    }

    private function calculateCpuMotherboardCompatibilidad($cpu, $motherboard)
    {
        // Lógica mejorada basada en socket y fabricante
        $cpuName = strtolower($cpu->nombre);
        $motherboardName = strtolower($motherboard->nombre);
        
        // Intel CPUs
        if (str_contains($cpuName, 'intel') || str_contains($cpuName, 'core i')) {
            if (str_contains($motherboardName, 'intel') || 
                str_contains($motherboardName, 'h610') || 
                str_contains($motherboardName, 'b660') || 
                str_contains($motherboardName, 'z690') ||
                str_contains($motherboardName, 'h370') ||
                str_contains($motherboardName, 'h470') ||
                str_contains($motherboardName, 'h510')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        // AMD CPUs
        if (str_contains($cpuName, 'amd') || str_contains($cpuName, 'ryzen')) {
            if (str_contains($motherboardName, 'amd') || 
                str_contains($motherboardName, 'a520') || 
                str_contains($motherboardName, 'b450') || 
                str_contains($motherboardName, 'b550') ||
                str_contains($motherboardName, 'x570') ||
                str_contains($motherboardName, 'a620')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        return 0; // No compatible por defecto
    }

    private function calculateCpuRamCompatibilidad($cpu, $ram)
    {
        // Lógica mejorada basada en tipo de RAM
        $cpuName = strtolower($cpu->nombre);
        $ramName = strtolower($ram->nombre);
        
        // CPUs modernos soportan DDR4 y DDR5
        if (str_contains($cpuName, 'intel') || str_contains($cpuName, 'amd')) {
            if (str_contains($ramName, 'ddr4') || str_contains($ramName, 'ddr5')) {
                return 90;
            }
        }
        
        // CPUs más antiguos solo DDR3
        if (str_contains($ramName, 'ddr3')) {
            return 85;
        }
        
        return 0; // No compatible
    }

    private function calculateMotherboardRamCompatibilidad($motherboard, $ram)
    {
        // Lógica mejorada basada en especificaciones de la motherboard
        $motherboardName = strtolower($motherboard->nombre);
        $motherboardSpecs = strtolower($motherboard->especificaciones ?? '');
        $ramName = strtolower($ram->nombre);
        
        // Motherboards DDR4
        if (str_contains($motherboardName, 'ddr4') || str_contains($motherboardSpecs, 'ddr4')) {
            if (str_contains($ramName, 'ddr4')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        // Motherboards DDR5
        if (str_contains($motherboardName, 'ddr5') || str_contains($motherboardSpecs, 'ddr5')) {
            if (str_contains($ramName, 'ddr5')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        // Motherboards DDR3
        if (str_contains($motherboardName, 'ddr3') || str_contains($motherboardSpecs, 'ddr3')) {
            if (str_contains($ramName, 'ddr3')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        // Por defecto, asumir DDR4
        if (str_contains($ramName, 'ddr4')) {
            return 85;
        }
        
        return 0; // No compatible
    }

    private function calculateGpuPsuCompatibilidad($gpu, $psu)
    {
        // Lógica mejorada basada en consumo de energía real
        $gpuName = strtolower($gpu->nombre);
        $psuName = strtolower($psu->nombre);
        
        // Extraer potencia de la PSU
        preg_match('/(\d+)w/', $psuName, $matches);
        $psuPower = isset($matches[1]) ? (int)$matches[1] : 0;
        
        // Consumo estimado de GPUs
        $gpuPower = 0;
        if (str_contains($gpuName, 'rtx 4090') || str_contains($gpuName, 'rtx 4080')) {
            $gpuPower = 350;
        } elseif (str_contains($gpuName, 'rtx 4070') || str_contains($gpuName, 'rtx 3080')) {
            $gpuPower = 300;
        } elseif (str_contains($gpuName, 'rtx 3060') || str_contains($gpuName, 'rtx 3070')) {
            $gpuPower = 220;
        } elseif (str_contains($gpuName, 'rtx 3050') || str_contains($gpuName, 'gtx 1660')) {
            $gpuPower = 170;
        } elseif (str_contains($gpuName, 'gtx 1650') || str_contains($gpuName, 'gtx 1060')) {
            $gpuPower = 120;
        } elseif (str_contains($gpuName, 'gt 730') || str_contains($gpuName, 'gt 610')) {
            $gpuPower = 50;
        } elseif (str_contains($gpuName, 'rx 6700') || str_contains($gpuName, 'rx 6800')) {
            $gpuPower = 250;
        } elseif (str_contains($gpuName, 'rx 6600')) {
            $gpuPower = 160;
        } elseif (str_contains($gpuName, 'rx 580')) {
            $gpuPower = 185;
        }
        
        if ($psuPower == 0 || $gpuPower == 0) {
            return 75; // Compatibilidad básica si no se puede determinar
        }
        
        // Calcular compatibilidad basada en potencia
        $requiredPower = $gpuPower + 200; // GPU + resto del sistema
        
        if ($psuPower >= $requiredPower + 100) {
            return 95; // Excelente
        } elseif ($psuPower >= $requiredPower) {
            return 85; // Buena
        } elseif ($psuPower >= $requiredPower - 50) {
            return 70; // Regular
        } else {
            return 0; // No compatible
        }
    }

    private function calculateGpuCaseCompatibilidad($gpu, $case)
    {
        // Lógica mejorada basada en tamaño de GPU
        $gpuName = strtolower($gpu->nombre);
        $caseName = strtolower($case->nombre);
        $caseSpecs = strtolower($case->especificaciones ?? '');
        
        // GPUs grandes
        if (str_contains($gpuName, 'rtx 4090') || str_contains($gpuName, 'rtx 4080') || 
            str_contains($gpuName, 'rtx 3080') || str_contains($gpuName, 'rtx 3090')) {
            // Necesitan gabinetes grandes
            if (str_contains($caseName, 'full tower') || str_contains($caseName, 'atx') ||
                str_contains($caseSpecs, 'full tower') || str_contains($caseSpecs, 'atx')) {
                return 90;
            }
            return 60; // Compatibilidad limitada
        }
        
        // GPUs medianas
        if (str_contains($gpuName, 'rtx 3070') || str_contains($gpuName, 'rtx 3060') ||
            str_contains($gpuName, 'rx 6700') || str_contains($gpuName, 'rx 6600')) {
            return 85; // Compatibilidad general
        }
        
        // GPUs pequeñas
        if (str_contains($gpuName, 'gtx 1650') || str_contains($gpuName, 'gt 730') ||
            str_contains($gpuName, 'gt 610')) {
            return 95; // Compatibles con casi cualquier gabinete
        }
        
        return 85; // Compatibilidad general
    }

    private function calculateMotherboardCaseCompatibilidad($motherboard, $case)
    {
        // Lógica mejorada basada en factor de forma
        $motherboardName = strtolower($motherboard->nombre);
        $caseName = strtolower($case->nombre);
        $caseSpecs = strtolower($case->especificaciones ?? '');
        
        // Motherboards ATX
        if (str_contains($motherboardName, 'atx') || 
            (str_contains($motherboardName, 'b660') && !str_contains($motherboardName, 'm-')) ||
            (str_contains($motherboardName, 'z690') && !str_contains($motherboardName, 'm-'))) {
            if (str_contains($caseName, 'atx') || str_contains($caseSpecs, 'atx') ||
                str_contains($caseName, 'full tower') || str_contains($caseName, 'mid tower')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        // Motherboards Micro-ATX
        if (str_contains($motherboardName, 'm-') || str_contains($motherboardName, 'micro')) {
            if (str_contains($caseName, 'atx') || str_contains($caseName, 'micro') ||
                str_contains($caseSpecs, 'atx') || str_contains($caseSpecs, 'micro')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        // Motherboards Mini-ITX
        if (str_contains($motherboardName, 'mini') || str_contains($motherboardName, 'itx')) {
            if (str_contains($caseName, 'mini') || str_contains($caseName, 'itx') ||
                str_contains($caseSpecs, 'mini') || str_contains($caseSpecs, 'itx')) {
                return 95;
            }
            return 0; // No compatible
        }
        
        return 85; // Compatibilidad general
    }

    private function calculateStorageMotherboardCompatibilidad($storage, $motherboard)
    {
        // Lógica mejorada basada en conectores
        $storageName = strtolower($storage->nombre);
        $motherboardName = strtolower($motherboard->nombre);
        $motherboardSpecs = strtolower($motherboard->especificaciones ?? '');
        
        // M.2 NVMe
        if (str_contains($storageName, 'm.2') && 
            (str_contains($storageName, 'nvme') || str_contains($storageName, 'p3') || 
             str_contains($storageName, '970 evo') || str_contains($storageName, 'nv2'))) {
            if (str_contains($motherboardName, 'm.2') || str_contains($motherboardSpecs, 'm.2') ||
                str_contains($motherboardSpecs, 'nvme') || str_contains($motherboardSpecs, 'pcie')) {
                return 95;
            }
            return 70; // Compatibilidad limitada
        }
        
        // M.2 SATA
        if (str_contains($storageName, 'm.2') && str_contains($storageName, 'sata')) {
            if (str_contains($motherboardName, 'm.2') || str_contains($motherboardSpecs, 'm.2')) {
                return 95;
            }
            return 70; // Compatibilidad limitada
        }
        
        // SATA SSD/HDD
        if (str_contains($storageName, 'ssd') || str_contains($storageName, 'hdd') ||
            str_contains($storageName, 'sata')) {
            return 95; // Compatible con cualquier motherboard moderna
        }
        
        return 85; // Compatibilidad general
    }

    private function calculateCpuGpuCompatibilidad($cpu, $gpu)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 80;
    }

    private function calculateGpuRamCompatibilidad($gpu, $ram)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 85;
    }

    private function calculateCpuStorageCompatibilidad($cpu, $storage)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 80;
    }

    private function calculateRamStorageCompatibilidad($ram, $storage)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 85;
    }

    private function calculateCpuPsuCompatibilidad($cpu, $psu)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 80;
    }

    private function calculateRamPsuCompatibilidad($ram, $psu)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 85;
    }

    private function calculateStoragePsuCompatibilidad($storage, $psu)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 80;
    }

    private function calculateCpuCaseCompatibilidad($cpu, $case)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 80;
    }

    private function calculateRamCaseCompatibilidad($ram, $case)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 85;
    }

    private function calculateStorageCaseCompatibilidad($storage, $case)
    {
        // Lógica simplificada - asumir compatibilidad general
        return 80;
    }
}
