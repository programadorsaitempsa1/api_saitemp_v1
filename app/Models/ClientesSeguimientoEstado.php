<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class ClientesSeguimientoEstado extends Model
{
    use HasFactory;

    protected $table = 'usr_app_clientes_seguimiento_estado';
    /*  protected $dates = ['created_at', 'updated_at']; */

    /*     public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-m-d H:i:s.v');
    } */
    protected $dateFormat = 'd-m-Y H:i:s';
}
