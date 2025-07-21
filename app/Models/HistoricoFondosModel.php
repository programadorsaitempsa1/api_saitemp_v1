<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class HistoricoFondosModel extends Model
{
    use HasFactory;

    protected $table = 'rhh_hisfon';

    // Este formato es válido para SQL Server
    protected $dateFormat = 'd-m-Y';

    protected $primaryKey = 'cod_emp'; // define tu primary key real

    public $incrementing = false; // si la PK no es autoincremental
    public $keyType = 'string';

    public $timestamps = false;
}
