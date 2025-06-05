<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesAnalistaModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_clientes_analista';
    protected $dateFormat = 'd-m-Y H:i:s';
}
