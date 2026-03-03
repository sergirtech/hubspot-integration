<?php
return[
    //Url base de la API de BMG
    'base_url' => rtrim(env('BMG_BASE_URL',''), '/'),
    //Token para el uso de la API
    'token' => env('BMG_TOKEN', null),
    ];

