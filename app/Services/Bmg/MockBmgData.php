<?php

namespace App\Services\Bmg;

class MockBmgData
{
    // Devuelve un array de editores falsos con la misma estructura
    // que devolvería la API real de BMG
    public static function editores(): array
    {
        return [
            [
                'cod_editor_bmg' => 'ED001',
                'nombre_fiscal'  => 'Juan',
                'apellidos'      => 'García López',
                'tipo_editor'    => 'autonomo',
                'filial'         => 'espana',
                'domicilios'     => [
                    [
                        'calle'          => 'Calle Mayor',
                        'numero'         => '10',
                        'codigo_postal'  => '28001',
                    ]
                ],
                'contactos'      => [
                    ['contacto' => 'juan.garcia@ejemplo.com'],  // índice 0 → email
                    ['contacto' => '+34600000001'],              // índice 1 → teléfono
                ],
                 //Datos numéricos fake
                'num_titulos' => 100,
                'num_titulos_activos' => 80,
                'total_ventas_eur' => 10000.00,
                'unidades_vendidas' => 1000,
                'ultima_fecha_venta' => '2026-03-18',
            ],
            [
                'cod_editor_bmg' => 'ED002',
                'nombre_fiscal'  => 'María',
                'apellidos'      => 'Rodríguez Pérez',
                'tipo_editor'    => 'editorial',
                'filial'         => 'mexico',
                'domicilios'     => [
                    [
                        'calle'          => 'Av. Insurgentes',
                        'numero'         => '200',
                        'codigo_postal'  => '06600',
                    ]
                ],
                'contactos'      => [
                    ['contacto' => 'maria.rodriguez@ejemplo.com'],
                    ['contacto' => '+5255000000002'],
                ],
                //Datos numéricos fake
                'num_titulos' => 150,
                'num_titulos_activos' => 120,
                'total_ventas_eur' => 15000.00,
                'unidades_vendidas' => 1500,
                'ultima_fecha_venta' => '2026-03-18'
            ],
            [
                'cod_editor_bmg' => 'ED003',
                'nombre_fiscal'  => 'Carlos',
                'apellidos'      => 'Martínez',
                'tipo_editor'    => 'autonomo',
                'filial'         => 'colombia',
                'domicilios'     => [
                    [
                        'calle'          => 'Carrera 7',
                        'numero'         => '55',
                        'codigo_postal'  => '110111',
                    ]
                ],
                'contactos'      => [
                    ['contacto' => 'carlos.martinez@ejemplo.com'],
                    ['contacto' => '+5713000000003'],
                ],
                //Datos numéricos fake
                'num_titulos' => 150,
                'num_titulos_activos' => 120,
                'total_ventas_eur' => 15000.00,
                'unidades_vendidas' => 1500,
                'ultima_fecha_venta' => '2026-03-18',
            ],
        ];
    }

    // Devuelve un único editor por código — simula getEditor()
    public static function editor(string $codEditorBmg): array
    {
        $encontrado = collect(self::editores())
            ->firstWhere('cod_editor_bmg', $codEditorBmg);

        if (!$encontrado) {
            throw new \Exception("MockBmg: editor {$codEditorBmg} no encontrado");
        }

        return $encontrado;
    }
}
