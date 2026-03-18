<?php

namespace App\Models;

class Editor
{
    //Campos que vamos a enviar a HubSpot
    public string $firstname;
    public string $lastname; 
    public string $email;
    public string $address;
    public string $phone;
    public string $zip;
    public string $codeditorbmg;
    public string $filial;
    public string $tipo_editor;

    //Campos numéricos del catálogo
    public int $num_titulos;
    public int $num_titulos_activos;
    public float $total_ventas_eur;
    public int $unidades_vendidas;
    public string $ultima_fecha_venta;

    //Constructor recibe array $data que devuelve BMG y extrae solo los campos que nos interesan
    public function __construct(array $data){
        $this->firstname = $data['nombre_fiscal'];
        $this->lastname= $data['apellidos']?? ''; //campo a comfirmar cuando tengamos BMG
        $this->address = trim(($data['domicilios'][0]['calle']?? '').' '.($data['domicilios'][0]['numero']?? ''));
        $this->email = $data['contactos'][0]['contacto']?? null;
        $this->phone = $data['contactos'][1]['contacto']?? null;
        $this->zip = $data['domicilios'][0]['codigo_postal']?? null;
        $this->codeditorbmg = $data['cod_editor_bmg'];
        $this->filial = $data['filial'] ?? ''; //campo a comfirmar cuando tengamos BMG
        $this->tipo_editor = $data['tipo_editor'] ?? ''; //campo a comfirmar cuando tengamos BMG
        
        //Campos numéricos del catálogo
        $this->num_titulos = $data['num_titulos'] ?? 0;
        $this->num_titulos_activos = $data['num_titulos_activos'] ?? 0;
        $this->total_ventas_eur = $data['total_ventas_eur'] ?? 0.0;
        $this->unidades_vendidas = $data ['unidades_vendidas'] ?? 0;
        $this->ultima_fecha_venta = $data['ultima_fecha_venta'] ?? '';
    }

    public function toHubspot(): array{
        return[
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'zip' => $this->zip,
            'codeditorbmg' => $this->codeditorbmg,
            'filial' => $this->filial,
            'tipo_editor' => $this->tipo_editor,
            'num_titulos'=>$this->num_titulos,
            'num_titulos_activos'=> $this->num_titulos_activos,
            'total_ventas_eur'=> $this -> total_ventas_eur,
            'unidades_vendidas'=> $this->unidades_vendidas,
            'ultima_fecha_venta'=> $this->ultima_fecha_venta,
        ];
    }
}
