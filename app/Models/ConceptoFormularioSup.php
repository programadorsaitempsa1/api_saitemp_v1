<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ConceptoFormularioSup extends Model
{
    use HasFactory;

    protected $connection = 'second_db';

    protected $table = 'usr_app_conceptos_formulario_sup';

    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}
