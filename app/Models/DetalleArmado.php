<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleArmado extends Model
{
    use HasFactory;

    protected $table = 'detalles_armado';
    protected $primaryKey = 'id_detalle';
    protected $fillable = ['id_armado', 'id_componente', 'cantidad', 'precio_unitario', 'subtotal'];

    public function armado()
    {
        return $this->belongsTo(Armado::class, 'id_armado', 'id_armado');
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class, 'id_componente', 'id_componente');
    }
}