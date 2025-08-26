<?php

namespace App\Imports;

use App\Models\CargosCandidatoModel;
use Maatwebsite\Excel\Concerns\ToModel;


class CargosCandidatoImport implements ToModel
{
    public function model(array $row)
    {
        return new CargosCandidatoModel([
            'nombre' => $row[0], // Asume que el Excel tiene columna "nombre"
            // Agrega más campos según tu estructura
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|unique:usr_app_cargos_candidatos,nombre'
        ];
    }
}