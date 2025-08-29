<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Componente;
use App\Models\Cliente;
use App\Models\Armado;
use App\Models\DetalleArmado;
use Illuminate\Support\Facades\Storage;

class ExportPersonalizeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'personalize:export {type : Tipo de datos a exportar (items, users, interactions)} {--output= : Ruta de salida del archivo CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exportar datos para AWS Personalize';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $output = $this->option('output') ?? "personalize_{$type}.csv";

        switch ($type) {
            case 'items':
                $this->exportItems($output);
                break;
            case 'users':
                $this->exportUsers($output);
                break;
            case 'interactions':
                $this->exportInteractions($output);
                break;
            default:
                $this->error("Tipo no válido. Use: items, users, o interactions");
                return 1;
        }

        $this->info("Datos exportados exitosamente a: {$output}");
        return 0;
    }

    /**
     * Exportar componentes como items
     */
    private function exportItems(string $output): void
    {
        $this->info('Exportando componentes...');
        
        $componentes = Componente::all();
        $csv = "ITEM_ID,CATEGORIA,PRECIO,MARCA,RENDIMIENTO\n";

        $bar = $this->output->createProgressBar($componentes->count());
        $bar->start();

        foreach ($componentes as $componente) {
            $csv .= sprintf(
                "%s,%s,%.2f,%s,%.2f\n",
                $componente->id_componente,
                $componente->categoria,
                $componente->precio,
                $componente->marca ?? 'Unknown',
                $componente->rendimiento ?? 5.0
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        file_put_contents($output, $csv);
        $this->info("Exportados {$componentes->count()} componentes");
    }

    /**
     * Exportar clientes como users
     */
    private function exportUsers(string $output): void
    {
        $this->info('Exportando usuarios...');
        
        $usuarios = Cliente::all();
        $csv = "USER_ID,PREFERENCIAS\n";

        $bar = $this->output->createProgressBar($usuarios->count());
        $bar->start();

        foreach ($usuarios as $usuario) {
            // Calcular presupuesto promedio basado en armados previos
            $presupuestoPromedio = Armado::where('id_usuario', $usuario->id_cliente)
                ->avg('presupuesto') ?? 1000;

            $preferencias = json_encode([
                'presupuesto_promedio' => round($presupuestoPromedio, 2),
                'total_armados' => Armado::where('id_usuario', $usuario->id_cliente)->count()
            ]);

            $csv .= sprintf(
                "%s,%s\n",
                $usuario->id_cliente,
                $preferencias
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        file_put_contents($output, $csv);
        $this->info("Exportados {$usuarios->count()} usuarios");
    }

    /**
     * Exportar armados como interactions
     */
    private function exportInteractions(string $output): void
    {
        $this->info('Exportando interacciones...');
        
        $armados = Armado::with('detallesArmado')->get();
        $csv = "USER_ID,ITEM_ID,EVENT_TYPE,TIMESTAMP,PRESUPUESTO\n";

        $totalInteractions = 0;
        $bar = $this->output->createProgressBar($armados->count());
        $bar->start();

        foreach ($armados as $armado) {
            foreach ($armado->detallesArmado as $detalle) {
                $csv .= sprintf(
                    "%s,%s,%s,%d,%.2f\n",
                    $armado->id_usuario,
                    $detalle->id_componente,
                    'PURCHASE',
                    strtotime($armado->fecha_creacion),
                    $armado->presupuesto
                );
                $totalInteractions++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        file_put_contents($output, $csv);
        $this->info("Exportadas {$totalInteractions} interacciones de {$armados->count()} armados");
    }
} 