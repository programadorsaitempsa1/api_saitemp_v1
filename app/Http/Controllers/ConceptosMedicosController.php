<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConceptosMedicosModel;

class ConceptosMedicosController extends Controller
{
    public function index()
    {
        $result = ConceptosMedicosModel::select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($result);
    }
}