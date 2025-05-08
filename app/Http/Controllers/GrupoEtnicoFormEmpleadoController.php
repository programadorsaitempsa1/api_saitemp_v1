<?php

namespace App\Http\Controllers;
use App\Models\GrupoEtnicoFormularioEmpleado;
use Illuminate\Http\Request;

class GrupoEtnicoFormEmpleadoController extends Controller
{
    public function index()
    {
        $result = GrupoEtnicoFormularioEmpleado::whereIn('cod_grupo', ['07','01', '02', '06', '09'])->get();
    
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }}
}