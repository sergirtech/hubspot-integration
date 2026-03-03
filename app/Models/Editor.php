<?php

namespace App\Models;

class Editor
{
    //Campos que vamos a enviar a HubSpot
    public string $firstname;
    public string $email;
    public string $address;
    public string $phone;
    public string $zipcode;
    public string $codeditorbmg;


    //Constructor recibe array $data que devuelve BMG y extrae solo los campos que nos interesan
    public function __construct(array $data){
        $this->firstname = $data['nombre_fiscal'];
        $this->address = trim(($data['domicilios'][0]['calle']?? '').' '.($data['domicilios'][0]['numero']?? ''));
        $this->email = $data['contactos'][0]['contacto']?? null;
        $this->phone = $data['contactos'][1]['contacto']?? null;
        $this->zipcode = $data['domicilios'][0]['codigo_postal']?? null;
        $this->codeditorbmg = $data['cod_editor_bmg'];
    }

    public function toHubspot(): array{
        return[
            'firstname' => $this->firstname,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'zipcode' => $this->zipcode,
            'codeditorbmg' => $this->codeditorbmg,
        ];
    }





}
