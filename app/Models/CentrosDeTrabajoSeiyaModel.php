<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentrosDeTrabajoSeiyaModel extends Model
{
    use HasFactory;
    protected $table = 'usr_app_centros_trabajo';
    protected $dateFormat = 'd-m-Y H:i:s';
}
