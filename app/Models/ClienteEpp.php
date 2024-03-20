<?php

namespace App\Models;
use Illuminate\Support\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteEpp extends Model
{
    use HasFactory;

    protected $table = 'usr_app_cliente_epp';

    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}
