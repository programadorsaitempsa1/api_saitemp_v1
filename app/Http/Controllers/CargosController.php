<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CargosCrm;

class CargosController extends Controller
{
    public function index()
    {
        $result = CargosCrm::select(
            'id', 
            'cargo'
        )
        ->orderby('cargo')
        ->paginate(12);
        return response()->json($result);
    }
}