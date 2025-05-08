<?php

namespace App\Http\Controllers;
use App\Models\BancosFormularioEmpleado;
use Illuminate\Http\Request;

class BancosFormularioEmpleadoController extends Controller
{
    public function index()
    {
        $result = BancosFormularioEmpleado::whereIn('cod_ban', ['0','01', '07', '13', '19', '52'])->get();
    
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }}
}