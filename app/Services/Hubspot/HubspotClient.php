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
        //La API de HubSpot espera los datos dentro de "properties"
        $payload=[
            'properties'=>$editor->toHubspot(),
        ];

        //Hacemos POST a HubSpot
        //withToken() añade: Authorization:Bearer MI_TOKEN
        $response=Http::withToken($this->token)
            ->post($this->baseUrl . '/crm/v3/objects/contacts', $payload);

        //Si HubSpot devuelve error, lanzar excepcion
        if($response->failed()){
            throw new \Exception('HubSpot error: '.$response->body());
        }
        //Devolvemos la respuesta de HubSpot como array
        return $response->json();
    }
}
