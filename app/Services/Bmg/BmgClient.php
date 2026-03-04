<?php
namespace App\Services\Bmg;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Filial;

class BmgClient
{
    private ?string $baseUrl;
    private ?string $usuario;
    private ?string $password;
    private string $filialNombre;

    // Ahora recibe un objeto Filial de la BD
    public function __construct(Filial $filial)
    {
        $this->baseUrl      = $filial->url;
        $this->usuario      = $filial->usuario;
        $this->password     = $filial->password;
        $this->filialNombre = $filial->nombre;
    }

    // Obtiene el token de caché o se autentica si ha caducado
    private function getToken(): string
    {
        // Clave única por filial para que cada una tenga su propio token
        $cacheKey = "bmg_token_{$this->filialNombre}";

        // Si el token existe en caché y no ha caducado lo reutiliza
        // Si no, se autentica y guarda el nuevo token durante 90 minutos
        return Cache::remember($cacheKey, now()->addMinutes(90), function () {
            return $this->authenticate();
        });
    }

    // Llama a /Acceso y devuelve el token
    private function authenticate(): string
    {
        $response = Http::post($this->baseUrl . '/Acceso', [
            'usuario'     => $this->usuario,
            'contrasenia' => $this->password,
        ]);

        if ($response->failed()) {
            throw new \Exception("BMG auth error [{$this->filialNombre}]: " . $response->body());
        }

        return trim($response->body());
    }

    // Obtiene los datos de UN editor por su código
    public function getEditor(string $codEditorBmg): array
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->get($this->baseUrl . '/Editor/dameDatosEditor', [
                'cod_editor_bmg' => $codEditorBmg,
            ]);

        $data = $response->json();

        if ($data['iResultado'] !== 1) {
            throw new \Exception("BMG error [{$this->filialNombre}]: " . $data['sError']);
        }

        return $data['oResultado'];
    }

    // Obtiene TODOS los editores de la filial paginando
    public function getAllEditors(): \Illuminate\Support\Collection
    {
        $token    = $this->getToken();
        $editores = collect();
        $pagina   = 1;

        // BMG devuelve los editores paginados — seguimos pidiendo
        // páginas hasta que no haya más
        do {
            $response = Http::withToken($token)
                ->get($this->baseUrl . '/Editor/dameDatosEditor', [
                    'pagina'        => $pagina,
                    'num_registros' => 100,
                ]);

            $data = $response->json();

            if ($data['iResultado'] !== 1) {
                throw new \Exception("BMG error [{$this->filialNombre}]: " . $data['sError']);
            }

            // Añadimos los editores de esta página a la colección
            $editores = $editores->merge($data['oResultado']['editores']);

            $totalPaginas = $data['oResultado']['total_paginas'];
            $pagina++;

        } while ($pagina <= $totalPaginas);

        return $editores;
    }
}
