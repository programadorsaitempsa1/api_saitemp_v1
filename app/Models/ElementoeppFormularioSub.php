<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ElementoeppFormularioSub extends Model
{
    protected $connection = 'second_db';

    protected $table = 'usr_app_elementos_de_pp_formulario_sub';

    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}
