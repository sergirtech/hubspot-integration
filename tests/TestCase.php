<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp():void{
        parent::setUp();

        //En pruebas, a veces no existe APP_KEY y Laravel falla al inicializar
        if(empty(config('app.key'))){
            config([
                'app.key'=>'base64: '. base64_encode(random_bytes(32)),
            ]);
        }
    }
}
