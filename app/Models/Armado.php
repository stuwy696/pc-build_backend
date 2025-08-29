<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Armado extends Model
{
    use HasFactory;

    protected $table = 'armados';
    protected $primaryKey = 'id_armado';
    protected $fillable = ['id_usuario', 'fecha_creacion', 'presupuesto', 'estado', 'metodo_creacion'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function detallesArmado()
    {
        return $this->hasMany(DetalleArmado::class, 'id_armado', 'id_armado');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_armado', 'id_armado');
    }
}