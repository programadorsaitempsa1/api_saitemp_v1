<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoIngresoArlModel extends Model
{
    use HasFactory;
    protected $table = 'rhh_EmpConv';
    protected $dateFormat = 'd-m-Y H:i:s';
}
