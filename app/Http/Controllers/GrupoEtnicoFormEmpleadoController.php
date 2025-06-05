<?php

namespace App\Http\Controllers;

use App\Models\GrupoEtnicoFormularioEmpleado;
use Illuminate\Http\Request;

class GrupoEtnicoFormEmpleadoController extends Controller
{
    public function index()
    {
        $result = GrupoEtnicoFormularioEmpleado::whereIn('cod_grupo', ['07', '01', '02', '06', '09'])->orderByRaw('CASE WHEN cod_grupo = 09 THEN 0 ELSE 1 END ASC')->orderBy('descripcion', 'asc')->get();

        if ($result) {
            return response()->json($result);
        } else {

            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }
}