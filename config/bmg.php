<?php
return [
    //Cada filial tiene su propia URL y credenciales
    'filiales' =>[
        'mexico'=>[
            'base_url'=>env('BMG_MEXICO_URL',null),
            'usuario' => env('BMG_MEXICO_USUARIO',null),
            'password' => env('BMG_MEXICO_PASSWORD',null),
        ],
        'colombia'=>[
            'base_url'=>env('BMG_COLOMBIA_URL',null),
            'usuario' => env('BMG_COLOMBIA_USUARIO',null),
            'password' => env('BMG_COLOMBIA_PASSWORD',null),
        ],
        'espana'=>[
            'base_url'=>env('BMG_ESPANA_URL',null),
            'usuario' => env('BMG_ESPANA_USUARIO',null),
            'password' => env('BMG_ESPANA_PASSWORD',null),
        ],
        'peru'=>[
            'base_url'=>env('BMG_PERU_URL',null),
            'usuario' => env('BMG_PERU_USUARIO',null),
            'password' => env('BMG_PERU_PASSWORD',null),
        ],
        'argentina'=>[
            'base_url'=>env('BMG_ARGENTINA_URL',null),
            'usuario' => env('BMG_ARGENTINA_USUARIO',null),
            'password' => env('BMG_ARGENTINA_PASSWORD',null),
        ],
        'brasil'=>[
            'base_url'=>env('BMG_BRASIL_URL',null),
            'usuario' => env('BMG_BRASIL_USUARIO',null),
            'password' => env('BMG_BRASIL_PASSWORD',null),
        ],
        'chile'=>[
            'base_url'=>env('BMG_CHILE_URL',null),
            'usuario' => env('BMG_CHILE_USUARIO',null),
            'password' => env('BMG_CHILE_PASSWORD',null),
        ],
        'guatemala'=>[
            'base_url'=>env('BMG_GUATEMALA_URL',null),
            'usuario' => env('BMG_GUATEMALA_USUARIO',null),
            'password' => env('BMG_GUATEMALA_PASSWORD',null),
        ],
        'uruguay'=>[
            'base_url'=>env('BMG_URUGUAY_URL',null),
            'usuario' => env('BMG_URUGUAY_USUARIO',null),
            'password' => env('BMG_URUGUAY_PASSWORD',null),
        ],
        'ecuador'=>[
            'base_url'=>env('BMG_ECUADOR_URL',null),
            'usuario' => env('BMG_ECUADOR_USUARIO',null),
            'password' => env('BMG_ECUADOR_PASSWORD',null),
        ],

    ],
    'token_ttl'=>90, //Tiempo que dura el token a caducar (90 minutos)

];
