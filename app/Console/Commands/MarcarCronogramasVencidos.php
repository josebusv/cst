<?php

namespace App\Console\Commands;

use App\Models\Cronograma;
use Illuminate\Console\Command;

class MarcarCronogramasVencidos extends Command
{
    protected $signature = 'cronogramas:marcar-vencidos';

    protected $description = 'Marca como vencidos los cronogramas pendientes cuya fecha programada ya paso';

    public function handle(): int
    {
        $count = Cronograma::where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->whereDate('fecha_programada', '<', now()->toDateString())
            ->update(['estado' => 'vencido']);

        $this->info("Cronogramas marcados como vencidos: {$count}");

        return self::SUCCESS;
    }
}
