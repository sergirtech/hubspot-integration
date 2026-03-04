<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    // Campos que se pueden rellenar masivamente
    protected $table = 'filiales';

    protected $fillable = [
        'nombre',
        'url',
        'usuario',
        'password',
        'activa',
    ];

    // Castings — activa se trata como booleano
    protected $casts = [
        'activa' => 'boolean',
    ];

    // Scope para obtener solo filiales activas
    // Uso: Filial::activas()->get()
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
