<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Componente extends Model
{
    use HasFactory;

    protected $table = 'componentes';
    protected $primaryKey = 'id_componente';
    protected $fillable = [
        'nombre', 'marca', 'modelo', 'categoria', 'precio',
        'stock', 'gama', 'especificaciones'
    ];

    public function compatibilidades()
    {
        return $this->hasMany(Compatibilidad::class, 'id_componente1', 'id_componente')
                    ->orWhere('id_componente2', $this->id_componente);
    }

    public function detallesArmado()
    {
        return $this->hasMany(DetalleArmado::class, 'id_componente', 'id_componente');
    }

    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class, 'id_componente', 'id_componente');
    }
}