<?php

namespace Tests\Unit;

use App\Models\Editor;
use PHPUnit\Framework\TestCase;

class EditorTest extends TestCase{
    private function datos():array{
        return[
            'cod_editor_bmg'=>'ED001',
            'nombre_fiscal'=>'Juan'
            'apellidos'=>'García López',
            'tipo_editor'=>'autónomo',
            'filial'=>'espana',
            'domicilios'=>[
                ['calle'=>'Calle Mayor', 'numero'=>'10','codigo_postal'=>'28001'],
            ],
            'contactos'=>[
                ['contacto'=>'juan@test.com'],
                ['contacto'=>'+34600000001'],
            ],
            'num_titulos'=>100,
            'num_titulos_activos'=80,
            'total_vendas_eur'=>10000.00,
            'unidades_vendidas'=>1000,
            'ultima_fecha_venta'=>'2026-01-15',
                
        ],
    }
    /**@test */
    public function mapea_campos_de_contacto_correctamente():void{
        $editor=new Editor($this->datos());

        $this->asertSame('Juan',$editor->firstname);
        $this->assertSame('García López',$editor->lastname);
        $this->assertSame('juan@test.com',$editor->email);
        $this->assertSame('+34600000001',$editor->phone);
        $this->assertSame('28001',$editor->zip);
        $this->assertSame('ED001', $editor->codeditorbmg);
        $this->assertSame('espana',$editor->filial);
        $this->assertSame('autonomo',$editor->tipo_editor);
    }

    /**@test */
    public function construye_direccion_concatenando_calle_y_numero():void{
        $editor= new Editor($this->datos());
        $this->assertSame('Calle Mayor 10', $editor->address);
    }

    /**@test */
    public function mapea_campos_numericos_correctamente():void{
        $editor=new Editor($this->datos());

        $this->assertSame(100,$editor->num_titulos);
        $this->assertSame(80,$editor->num_titulos_activos);
        $this->assertSame(10000.00,$editor->total_vendas_eur);
        $this->assertSame(1000,$editor->unidades_vendidas);
        $this.assertSame('2026-01-15',$editor->ultima_fecha_venta);
    }

    /**@test */
    public function usa_valores_por_defecto_cuando_faltan_campos():void{
        $editor=new Editor([
            'domicilios'=>[[]],
            'contactos'=>[[]],[[]],
        ]);

        $this.assertSame('',$editor->firstname);
        $this.assertSame('',$editor->lastnname);
        $this.assertSame('',$editor->email);
        $this.assertSame('',$editor->phone);
        $this.assertSame('',$editor->zip);
        $this.assertSame('',$editor->codeditorbmg);
        $this.assertSame(0,$editor->num_titulos);
        $this.assertSame(0,$editor->num_titulos_activos);
        $this.assertSame(0.0,$editor->total_ventas_eur);
        $this.assertSame(0,$editor->undades_vendidas);
        $this.assertSame('',$editor->ultima_fecha_venta);
    }

    /**@test */
    public function to_hubspot_devuelve_todos_los_campos_esperados():void{
        $editor=new Editor($this->datos());
        $payload=$editor->tohubspot();

        $camposEsperados=[
            'firstname','lastname','email', 'address','phone','zip',
            'codeditorbmg','filial','tipo_editor',
            'num_titulos','num_titulos_activos','total_ventas_eur',
            'unidades_vendidas','ultima_fecha_venta',
        ];

        foreach($camposEsperados as $campo){
            $this->assertArrayHasKey($campo,$payload,"Falta el campo: {$campo}");
        }
    }

    /**@test*/
    public function to_hubspot_contiene_los_valores_correctos():void{
        $editor=new Editor($this->datos());
        $payload=$editor->toHubspot();

        $this->assertSame('juan@test.com',$payload['email']);
        $this->assertSame('ED001',$payload['codeditorbmg']);
        $this->assertSame(100,$payload['num_titulos']);
        $this->assertSame(10000.00,$payload['total_ventas_eur']);

    }



}