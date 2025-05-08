<?php

namespace App\Http\Controllers;
use App\Models\CiudadesFormularioEmpleado;
use Illuminate\Http\Request;

class CiudadesFormularioEmpleadoController extends Controller
{
    public function index()
    {
        $result = CiudadesFormularioEmpleado::select()->get();
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }
    }

    public function byCodDep($codPai, $codDep){
        $result = CiudadesFormularioEmpleado::where('cod_pai', $codPai)
        ->where('cod_dep', $codDep)->orderBy('nom_ciu', 'asc')->get();
        if ($result) {
            return response()->json($result);
        } else {
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }
    
    }
}