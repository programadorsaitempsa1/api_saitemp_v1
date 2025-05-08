<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReferenciasModel;
class FamiliaresFormEmpleadoController extends Controller
{
    public function index()
    {
        $result = ReferenciasModel::select()->get();
    
        if ($result) {
            return response()->json($result);
        } else {
            
            return response()->json(['message' => 'Registro no encontrado'], 404);
    }}
}