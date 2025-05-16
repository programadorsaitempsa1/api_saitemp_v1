<?php

namespace App\Http\Controllers;

use App\Models\BancosFormularioEmpleado;
use Illuminate\Http\Request;
use Illuminate\Support\Str;



class BancosFormularioEmpleadoController extends Controller
{
    public function index()
    {
        $result = BancosFormularioEmpleado::whereIn('cod_ban', ['0', '01', '07', '13', '19', '52', '74'])->orderByRaw('CASE WHEN cod_ban = 0 THEN 0 ELSE 1 END ASC')->orderBy('nom_ban', 'asc')->get()
            ->map(function ($banco) {
                $banco->nom_ban = Str::ucfirst(Str::lower($banco->nom_ban));
                return $banco;
            });;

        if ($result) {
            return response()->json($result);
        } else {

            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }
}