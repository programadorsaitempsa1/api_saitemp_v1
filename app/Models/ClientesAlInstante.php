<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesAlInstante extends Model
{
    use HasFactory;

    protected $connection = 'second_db';

    protected $table = 'cxc_cliente';
}
