<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filial;

class FilialController extends Controller
{
    //GET /api/filiales - Lista todas las filiales
    public function index(){
        return response()->json(Filial::all());
    }

    //POST /api/filiales - Crea una nueva filial
    public function store(Request $request){
        $validated=$request->validate([
            'nombre'=>'required|string|unique:filiales,nombre',
            'url'=>'required|string',
            'usuario'=>'required|string',
            'password'=>'required|string',
            'activa'=>'boolean',
        ]);
        $filial=Filial::create($validated);
        return response()->json($filial,201);
    }

    //GET /appi/filiales/{id} - Muestra una filial
    public function show(Filial $filial){
        return response()->json($filial);
    }

    //PUT /api/filiales/{id} - Actualiza una filial
    public function update(Request $request, Filial $filial){
        $validated = $request->validate([
            'nombre'   => 'string|unique:filiales,nombre,' . $filial->id,
            'url'      => 'string',
            'usuario'  => 'string',
            'password' => 'string',
            'activa'   => 'boolean',
        ]);
        $filial->update($validated);
        return response()->json($filial);
    }

    //DELETE /api/filiales/{id} - Elimina una filial
    public function destroy(Filial $filial){
        $filial->delete();
        return response()->json(['success'=>true]);
    }
}
