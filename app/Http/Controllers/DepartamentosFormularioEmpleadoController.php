<?php

namespace App\Http\Controllers;

use App\Models\DepartamentosFormularioEmpleado;
use Illuminate\Http\Request;

class DepartamentosFormularioEmpleadoController extends Controller
{
    public function index()
    {
        $result = DepartamentosFormularioEmpleado::select()->get();
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }}
    public function byCodPai($codPai){
        $result = DepartamentosFormularioEmpleado::where('cod_pai', $codPai)->
        orderBy('nom_dep', 'asc')->get();
        if ($result) {
            return response()->json($result);
        } else {
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }}

}