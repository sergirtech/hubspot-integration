<?php
namespace App\Services\Bmg;
use Illuminate\Support\Facades\Http;

class BmgClient{
    private string $baseUrl;
    private string $token;

    public function __construct(){
        //config('bmg.base_url') lee el valor de config/bmg.php que a su vez lo lee del .env
        $this->baseUrl=config('bmg.base_url');
        $this->token=config('bmg.token');
    }
    //Este metodo recibe el código del editor y devuelve un objeto Editor co sus datos
    public function getEditor(String $codEditorBmg): array{
        //Hacemos GET a BMG con el token en el header
        $response= Http::withToken($this->token)
            ->get($this->baseUrl.'/Editor/dameDatosEditor',[
                'cod_editor_bmg'=>$codEditorBmg,
            ]);

        //Convertimos la respuesta a array PHP
        $data=$response->json();

        //iResultado==1 significa éxito según la doc de BMG
        if($data['isResultado']!==1){
            throw new Exception('BMG error: '.$data['sError']);
        }
        //oResultado contiene los datos del editor. Es un array con todos los campos del editor
        return $data['oResultado'];
    }
}
