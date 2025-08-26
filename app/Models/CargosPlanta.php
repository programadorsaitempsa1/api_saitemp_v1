<?php
namespace App\Models;
use Illuminate\Support\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargosPlanta extends Model
{
    use HasFactory;
    protected $table = 'usr_app_cargos_crm';

    public function fromDateTime($value)
    {
        // return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m H:i:s');
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}