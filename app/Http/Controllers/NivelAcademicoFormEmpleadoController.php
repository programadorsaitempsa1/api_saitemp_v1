<?php

namespace App\Http\Controllers;
use App\Models\NivelAcademicoFormularioEmpleado;
use Illuminate\Http\Request;
use App\Models\ReferenciasFormularioEmpleado;

class NivelAcademicoFormEmpleadoController extends Controller
{
    public function index()
    {
        $result = NivelAcademicoFormularioEmpleado::select()->get();
    
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }}
}