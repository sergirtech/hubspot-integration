<?php

namespace App\Services\Hubspot;
use Illuminate\Support\Facades\Http;
use App\Models\Editor;

class HubspotClient
{
    private string $baseUrl;
    private ?string $token;

    public function __construct(){
        //Igual que BmgClient, pero letndo config/hubspot.php
        $this->baseUrl = config('hubspot.base_url');
        $this->token=config('hubspot.token');
    }

    //Recibe un objeto Editor y lo crea/actualiza en HubSpot
    //upset=update+insert(si existe actualiza, si no crea)

    public function upsertContact(Editor $editor): array{
       $payload=[
        'properties'=>$editor->toHubspot(),
       ];

       //Intentar atualizar el contacto existente buscando por email
       //Hacemos patch con ?idProperty=email le dice a HubSpot que busque por ese campo
       $updateResponse= Http::withToken($this->token)
       ->patch($this->baseUrl . '/crm/v3/objects/contacts' . urldecode($editor->email) . '?idProperty=email', $payload);

       //Si el contacto existe, HubSpot devuelve 200 y el contacto actualizado
       if($updateResponse->successful()){
        return $updateResponse ->json();
       }
       //Si HubSot devuelve 404, el contacto no existe, creamos uno nuevo
       if($updateResponse->status() === 404){
        $createResponse= Http::withToken($this->token)
        ->post($this->baseUrl . '/crm/v3/objects/contacts', $payload);
        if($createResponse->failed()){
            throw new \Exception('Error al crear contacto:  '.$createResponse->body());
        }
        return $createResponse->json();
       }

       //Gestionamos cualquier otro código de error (401,501...)
       throw new \Exception('Error en upsert: '.$updateResponse->body());
    }
}