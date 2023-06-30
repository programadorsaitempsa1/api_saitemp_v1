<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TipoProveedor extends Model
{
    use HasFactory;
    protected $table= 'usr_app_tipo_proveedor';

    public function fromDateTime($value)
    {
        // return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m H:i:s');
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}
