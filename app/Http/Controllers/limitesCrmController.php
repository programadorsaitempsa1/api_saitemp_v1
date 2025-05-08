<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class limitesCrmController extends Controller
{
    public function getLimitesCrm(){
        $limites = new \stdClass();
    $limites->limite_evidencias = 10;
    $limites->limite_compromisos = 10;

    return response()->json(['status' => 'success', 'message' => 'limites obtenidos correctamente', 'limites' => $limites]);

    }
}