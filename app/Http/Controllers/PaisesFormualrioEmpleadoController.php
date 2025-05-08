<?php

namespace App\Http\Controllers;
use App\Models\PaisesFormularioEmpleados;
use Illuminate\Http\Request;

class PaisesFormualrioEmpleadoController extends Controller
{
    public function index()
    {
        $result = PaisesFormularioEmpleados::select()->get();
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }
    }
}