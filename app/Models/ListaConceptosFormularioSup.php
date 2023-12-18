<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaConceptosFormularioSup extends Model
{
    use HasFactory;

    protected $connection = 'second_db';

    protected $table = 'usr_app_lista_conceptos_formulario_sup';
}
