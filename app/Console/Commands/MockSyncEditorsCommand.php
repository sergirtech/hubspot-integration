<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bmg\MockBmgClient;
use App\Services\Hubspot\HubspotClient;
use App\Models\Editor;

class MockSyncEditorsCommand extends Command
{
    // --dry-run es opcional, si no se pasa hace la sync real
    protected $signature   = 'mock:sync-editors {--dry-run : Muestra los datos sin enviar nada a HubSpot}';
    protected $description = 'Sincroniza los editores fake a HubSpot (para pruebas)';

    public function handle(): int
    {
        $bmg      = new MockBmgClient();
        $editores = $bmg->getAllEditors();
        $dryRun   = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('MODO DRY-RUN — no se enviará nada a HubSpot');
        }

        $this->info("Sincronizando {$editores->count()} editores fake...");

        foreach ($editores as $data) {
            try {
                $editor = new Editor($data);

                if ($dryRun) {
                    // Muestra lo que se mandaría a HubSpot en formato tabla
                    $this->info("--- Editor: {$editor->codeditorbmg} ---");
                    $this->table(
                        ['Campo', 'Valor'],
                        collect($editor->toHubspot())
                            ->map(fn($valor, $campo) => [$campo, $valor])
                            ->values()
                            ->toArray()
                    );
                    continue;
                }

                // Sync real — solo se ejecuta si no es dry-run
                $hubspot  = new HubspotClient();
                $response = $hubspot->upsertContact($editor);
                $this->info("✓ {$editor->codeditorbmg} — {$editor->email} → HubSpot ID: {$response['id']}");

            } catch (\Exception $e) {
                $this->error("✗ {$data['cod_editor_bmg']}: {$e->getMessage()}");
            }
        }

        $this->info('Sync completado.');
        return Command::SUCCESS;
    }
}
