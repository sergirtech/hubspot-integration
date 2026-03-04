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
    ){}

    //Este metodo recibe el webhook de BMG
    //BMG mandará un POST con el cod_editor_bmg del editor nuevo
    public function handleWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'cod_editor_bmg'=> 'required|string',
            //La filial viene en el webhook para saber de dónde es el editor
            'filial=>required|string',
        ]);
        //Pasar la filial al BmgClient
        $data=(new BmgClient($validated['filial']))->getEditor($validated['cod_editor_bmg']);
        $editor=new Editor($data);
        $result= $this->hubspot->upsertContact($editor);
        return response()->json([
            'success'=>true,
            'hubspot'=>$result['id']??null,
        ]);
    }
}
