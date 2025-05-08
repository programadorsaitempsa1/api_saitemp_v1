<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RecepcionEmpleado extends Model
{
    use HasFactory;
    protected $table = "GTH_RptEmplea";
    protected $primaryKey = 'cod_emp';
    public $timestamps = false;
    public $incrementing = false;
    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}