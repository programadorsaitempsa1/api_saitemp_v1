<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ReferenciasFormularioEmpleado extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $table = "GTH_RptReferencias";
    protected $primaryKey = 'num_ref';
    public $incrementing = false;
    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}