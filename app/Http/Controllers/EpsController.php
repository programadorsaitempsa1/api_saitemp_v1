<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EpsModel;
use Illuminate\Support\Str;

class EpsController extends Controller
{
    public function index()
    {
        $eps = EpsModel::orderby('usr_app_eps_c.nombre', 'ASC')->get()
            ->map(function ($eps) {
                $eps->nombre = Str::ucfirst(Str::lower($eps->nombre));
                return $eps;
            });
        return response()->json($eps);
    }
    public function create(Request $request)
    {
        try {
            $eps =  new EpsModel;
            $eps->nombre = $request->nombre;
            $eps->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
}