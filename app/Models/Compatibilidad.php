<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compatibilidad extends Model
{
    use HasFactory;

    protected $table = 'compatibilidades';
    protected $primaryKey = 'id_compatibilidad';
    protected $fillable = ['id_componente1', 'id_componente2', 'porcentaje_compatibilidad'];

    public function componente1()
    {
        return $this->belongsTo(Componente::class, 'id_componente1', 'id_componente');
    }

    public function componente2()
    {
        return $this->belongsTo(Componente::class, 'id_componente2', 'id_componente');
    }
}