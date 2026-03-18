<?php

namespace App\Services\Bmg;

// Implementa exactamente los mismos métodos públicos que BmgClient
// pero sin hacer ninguna llamada HTTP — devuelve datos fake
// Cuando llegue el BMG real, solo hay que cambiar qué cliente se inyecta
class MockBmgClient
{
    // Devuelve un único editor por código
    public function getEditor(string $codEditorBmg): array
    {
        return MockBmgData::editor($codEditorBmg);
    }

    // Devuelve todos los editores fake como Collection
    public function getAllEditors(): \Illuminate\Support\Collection
    {
        return collect(MockBmgData::editores());
    }
}
