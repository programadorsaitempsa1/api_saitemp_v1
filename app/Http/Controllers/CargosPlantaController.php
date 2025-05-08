<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CargosPlanta;

class CargosPlantaController extends Controller
{
     public function index()
    {
        $result = CargosPlanta::select(
            'cargo', 
            'id'
        )
        ->get();
        return response()->json($result);

    }
}