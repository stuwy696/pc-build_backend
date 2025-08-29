<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;

    protected $table = 'devoluciones';
    protected $primaryKey = 'id_devolucion';
    protected $fillable = [
        'id_venta', 'id_componente', 'id_usuario_empleado',
        'fecha_devolucion', 'motivo', 'cantidad', 'monto_reembolsado'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class, 'id_componente', 'id_componente');
    }

    public function usuarioEmpleado()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_empleado', 'id_usuario');
    }
}