<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Filial;
use App\Models\Editor;
use App\Services\Bmg\BmgClient;
use App\Services\Hubspot\HubspotClient;

class SyncEditorCommand extends Command
{
    protected $signature = 'sync:editor {codEditorBmg} {filial}';
    protected $description = 'Sincroniza manualmente un editor de BMG a HubSpot';

    public function handle(): int
    {
        $codEditorBmg = $this->argument('codEditorBmg');
        $filialNombre = $this->argument('filial');

        // Buscamos la filial en la BD por su nombre
        $filial = Filial::where('nombre', $filialNombre)->first();

        if (!$filial) {
            $this->error("Filial '{$filialNombre}' no encontrada en la base de datos.");
            return Command::FAILURE;
        }

        $this->info("Sincronizando editor {$codEditorBmg} de filial {$filialNombre}...");

        $bmg     = new BmgClient($filial);
        $hubspot = new HubspotClient();

        $data   = $bmg->getEditor($codEditorBmg);
        $editor = new Editor($data);
        $result = $hubspot->upsertContact($editor);

        $this->info("✅ Sincronizado. HubSpot ID: " . ($result['id'] ?? 'desconocido'));

        return Command::SUCCESS;
    }
}
