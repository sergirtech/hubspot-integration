<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Bmg\BmgClient;
use App\Services\Hubspot\HubspotClient;
use App\Models\Editor;
class SyncController extends Controller
{
    //Laravel inyecta automáticamente los servicios en el constructor
    //No es necesario hacer new BmgClient() de forma manual
    public function __construct(
        private BmgClient $bmg,
        private HubspotClient $hubspot
    ){}

    //Este metodo recibe el webhook de BMG
    //BMG mandará un POST con el cod_editor_bmg del editor nuevo
    public function handleWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validamos que el payload tiene el campo obligatorio
        $validated=$request->validate([
            'cod_editor_bmg'=>'required|string',
        ]);

        //1. Obtenemos los datos completos del editor desde BMG
        $data=$this->bmg->getEditor($validated['cod_editor_bmg']);

        //2. Transformamos los datos al formato HubSpot
        $editor=new Editor($data);

        //3. Creamos o actualizamos el contacto de HubSpot
        $result=$this->hubspot->upsertContact($editor);

        //4. Devolvemos respuesta OK a BMG
        return response()->json([
            'success'=>true,
            'hubspot_id' => $result['id']?? null,
        ]);
    }
}
