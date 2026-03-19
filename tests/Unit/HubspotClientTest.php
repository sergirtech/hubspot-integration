<?php

namespace Tests\Feature;

use App\Models\Editor;
use App\Services\Hubspot\HubspotClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HubspotClientTest extends Testase{
    private function editorFake():Editor{
        return new Editor([
            'cod_editor_bmg'=>'ED001',
            'nombre_fiscal'='Juan',
            'apellidos'=>'García',
            'tipo_editor' ='autonomo',
            'filial'=>'espana',
            'domicilios'=>[
                ['calle'=>'Calle Mayor','numero'=>'10','codigo_postal'=>'28001'],
            ],
            'contactos'=>[
                ['contacto'=>'juan@test.com'],
                ['contacto'=>'+34600000001'],
            ],
            'num_titulos'=>10,
            'num_titulos_activos'=>8,
            'total_ventas_eur'=500.00,
            'unidades_vendidas'=>50,
            'ultima_fecha_venta'=>'2026-01-01',
        ]);
    }
    private function clienteConToken(): HubspotClient{
        config(['hubspot.base_url'=>'https://api.hubapi.com']);
        config(['hubspot.token'=>'fake-token-para-tests']);
        return new HubspotClient();
    }

    /**@test */
    public function lanza_excepcion_si_el_token_esta_vacio():void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/HUBSPOT_TOKEN/');

        config(['hubspot.token'=>'']);
        config(['hubspot.base_url'=>'https://api.hubapi.com']);

        new HubspotClient();
    }

    /**@test */
    public function upsert_actualiza_contacto_existente_con_patch():void{
        Http::fake([
            '*/contacts/*' => Http::response(['id' => '123', 'properties' => []], 200),

        ]);
        $respuesta = $this->clienteConToken()->upsertContact($this->editorFake());

        $this->assertSame('123', $respuesta['id']);

        Http::assertSentCount(1); // Solo el PATCH, no POST
    }

    /**@test */
    public function upsert_crea_contacto_nuevo_si_patch_devuelve_404():void{
        Http::fake([
            '*contacts/jan%40test.com'=>Http::response([],404),
            '*contacts' =>Http::response(['id'=>'456'],201)
        ]);

        $respuesta=$this->clienteConToken()->upsertContact($this->editorFake());
        
        $this->assertSame('456',$respuesta['id']);
        Http:: assertSentCount(2);
    }

    /**@test */
    public function upsert_lanza_exception_si_post_falla():void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Error al crear contacto/');

        Http::fake([
            '*/contacts/juan%40test.com*'=>Http::response([],404),
            '*/contacts'=> Http::response(['message'=>'Bad request'],400);
        ]);
        $this->clienteConToken()->upsertContact($this->editorFake());
    }

    /**@test */
    public function upsert_lanza_excepcion_en_error_de_autenticacion():void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Error en upsert/');

        Htto::fake([
            '*/contacts/*'=> Http:response(['message'=>'Unauthorized'],401)
        ]);

        $this-clienteConToken()->upsertContact($this->editorFake());
    }

    /**@test */
    public function upsert_envia_el_email_codificado_en_la_url():void{
        Http::fake([
            '*/contacts/*'=>Http::response(['id'=> '789'],200),
        ]);
        $this->clienteConToken()->upsertContact($this->editorFake());

        Http::assertSent(function($request){
            return str_contains($request->url(),urlencode('juan@test.com'));
        });
    }
}