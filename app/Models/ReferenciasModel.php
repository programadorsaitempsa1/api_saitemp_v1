<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ReferenciasModel extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $primaryKey = 'nom_fam';
    protected $table = "GTH_RptFamilia";
    public $incrementing = false;
    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}