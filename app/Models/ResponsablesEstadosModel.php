<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ResponsablesEstadosModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_clientes_responsable_estado';
    protected $fillable = ['usuario_id', 'estado_firma_id'];

    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}