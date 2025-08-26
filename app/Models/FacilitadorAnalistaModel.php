<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilitadorAnalistaModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_facilitador_analistas';
    protected $dateFormat = 'd-m-Y H:i:s';
}