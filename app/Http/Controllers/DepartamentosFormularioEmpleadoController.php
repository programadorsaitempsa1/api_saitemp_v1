<?php

namespace App\Http\Controllers;

use App\Models\DepartamentosFormularioEmpleado;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartamentosFormularioEmpleadoController extends Controller
{
    public function index()
    {
        $result = DepartamentosFormularioEmpleado::select()->get();
        if ($result) {
            return response()->json($result);
        } else {

            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }
    public function byCodPai($codPai)
    {
        $departamentos = DepartamentosFormularioEmpleado::where('cod_pai', $codPai)
            ->orderBy('nom_dep', 'asc')
            ->get()
            ->map(function ($departamento) {
                $departamento->nom_dep = Str::ucfirst(Str::lower($departamento->nom_dep));
                return $departamento;
            });

        if ($departamentos->isEmpty()) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($departamentos);
    }
}