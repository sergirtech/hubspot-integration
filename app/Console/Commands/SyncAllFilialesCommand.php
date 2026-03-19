<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Filial;
use App\Models\Editor;
use App\Services\Bmg\BmgClient;
use App\Services\Hubspot\HubspotClient;

//ESTE ES EL COMANDO QUE SE EJECUTARA PERIÓDICAMENTE EN BMG
class SyncAllFilialesCommand extends Command
{
    // Comando para sincronizar todas las filiales activas
    protected $signature = 'sync:filiales';
    protected $description = 'Sincroniza todos los editores de todas las filiales activas a HubSpot';

    public function handle(): int
    {
        // Obtenemos todas las filiales activas de la BD
        $filiales = Filial::activas()->get();

        if ($filiales->isEmpty()) {
            $this->warn('No hay filiales activas en la base de datos.');
            return Command::FAILURE;
        }

        $this->info("Encontradas {$filiales->count()} filiales activas.");

        $hubspot = new HubspotClient();

        // Iteramos cada filial
        foreach ($filiales as $filial) {
            $this->info("--- Sincronizando filial: {$filial->nombre} ---");

            try {
                // Autenticamos con las credenciales de esta filial
                $bmg = new BmgClient($filial);

                // Obtenemos todos los editores de esta filial
                $editores = $bmg->getAllEditors();

                $this->info("Encontrados {$editores->count()} editores.");

                // Sincronizamos cada editor con HubSpot
                foreach ($editores as $editorData) {
                    try {
                        $editor = new Editor($editorData);
                        $hubspot->upsertContact($editor);
                        $this->info("✅ Editor {$editor->codeditorbmg} sincronizado.");
                    } catch (\Exception $e) {
                        // Si un editor falla, continuamos con el siguiente
                        $this->error("❌ Error editor {$editorData['cod_editor_bmg']}: " . $e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                // Si una filial falla, continuamos con la siguiente
                $this->error("❌ Error filial {$filial->nombre}: " . $e->getMessage());
            }
        }

        $this->info('✅ Sincronización completada.');
        return Command::SUCCESS;
    }
}
