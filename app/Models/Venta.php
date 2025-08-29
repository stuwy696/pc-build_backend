<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    protected $fillable = ['id_armado', 'id_usuario_empleado', 'fecha_venta', 'total', 'estado'];

    public function armado()
    {
        return $this->belongsTo(Armado::class, 'id_armado', 'id_armado');
    }

    public function usuarioEmpleado()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_empleado', 'id_usuario');
    }

    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class, 'id_venta', 'id_venta');
    }
}