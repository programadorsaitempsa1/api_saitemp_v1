<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdiomasModel;

class IdiomasController extends Controller
{
    public function index()
    {
        $idioma = IdiomasModel::orderby('usr_app_idiomas_c.nombre', 'ASC')->get();
        return response()->json($idioma);
    }
    public function create(Request $request)
    {
        try {
            $idioma =  new IdiomasModel;
            $idioma->nombre = $request->nombre;
            $idioma->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
}