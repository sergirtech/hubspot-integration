<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bmg\BmgClient;
use App\Services\Hubspot\HubspotClient;
use App\Models\Editor;

class SyncEditorCommand extends Command
{
    //Así se llama el comando desde la terminal
    //{codEditorBmg} es el argumento obligatorio
    protected $signature = 'sync:editor {codEditorBmg}';

    //Descripción que aparece en "php artisan list"
    protected $description = 'Sincroniza un editor de BMG a HubSpot por su código';

    public function __construct(
        private BmgClient $bmg,
        private HubspotClient $hubspot
    ){
        parent::__construct();
    }

    //Este metodo se ejecuta cuando se llama al comando
    public function handle():int{
        //Recogemos el argumento que se pasa en la terminal
        $codEditorBmg= $this->argument('codEditorBmg');

        //Informamos al usuario que empieza el proceso
        $this->info('Sincronizando editor {codEditorBmg}...');

        //1. Obtenemos datos de BMG
        $data=$this->bmg->getEditor($codEditorBmg);

        //2. Transformamos los datos
        $editor=new Editor($data);

        //3. Enviamos a HubSpot
        $result=$this->hubspot->upsertContact($editor);

        //Mensaje de éxito con el ID que devuelve HubSpot
        $this->info("✅ Editor sincronizado. HubSpot ID: " . ($result['id'] ?? 'desconocido'));

        // Command::SUCCESS=0 INDICA QUE EVVERYTHING OK
        return Command::SUCCESS;
    }
}
