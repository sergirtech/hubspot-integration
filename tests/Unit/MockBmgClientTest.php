<?php

namespace Tests\Unit;

use App\Services\Bmg\MockBmgClient;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class MockBmgClientTest extends TestCase{
    private MockBmgClient $client;

    protected function setUp(): void{
        parent::setUp();
        $this->client= new MockBmgClient();
    }

    /**@test */
    public function get_all_editors_devuelve_una_collection():void{
        $resultado=$this->client->getAllEditors();

        $this->assertInstanceOf(Collection::class,$resultado);
    }

    /**@test */
    public function get_all_editors_devuelve_tres_editores():void{
        $resultado=$this->client->getAllEditors();

        $this->assertCount(3,$resultado);
    }

    /**@test */
    public function get_all_editors_tiene_los_campos_obligatorios():void{
        $editores=$this->client->getAllEditors();

        foreach($editores as $editor){
            $this->assertArrayHasKey('cod_editor_bmg',$editor);
            $this->assertArrayHasKey('nombre_fiscal',$editor);
            $this->assertArrayHasKey('contactos',$editor);
            $this->assertArrayHasKey('domicilios',$editor);
        }
    }

    /**@test */
    public function geet_editor_devuelve_el_editor_correcto():void{
        $editor=$rhis->client->getEditor('ED001');

        $this->assertSame('ED001',$editor['codeditor_bmg']);
        $this->assertSame('Juan',$editor['nombre_fiscal']);
        $this->assertSame('espana',$editor['filial']);
    }

    /**@test */
    public function get_editor_devuelve_distinto_editor_por_codigo():void{
        $ed001 = $this->client->getEditor('ED001');
        $ed002 = $this->client->getEditor('ED002');

        $this->assertNotSame($ed001['cod_editor_bmg'],$ed002['cod_editor_bmg']);
        $this->assertNotSame($ed001['filial'],$ed002['filial']);
    }

    /**@test */
    public function get_editor_lanza_excepcion_si_el_codigo_no_existe():void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/INEXISTENTE/');

        $this->client->getEditor('INEXISTENTE');
    }
}
