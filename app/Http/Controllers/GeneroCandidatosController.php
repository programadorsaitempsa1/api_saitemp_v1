<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneroCandidatosModel;

class GeneroCandidatosController extends Controller
{
    public function index()
    {
        $genero = GeneroCandidatosModel::orderby('usr_app_genero.nombre', 'ASC')->get();
        return response()->json($genero);
    }
    public function create(Request $request)
    {
        try {
            $genero =  new GeneroCandidatosModel;
            $genero->nombre = $request->nombre;
            $genero->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $genero =  GeneroCandidatosModel::find($id);
            $genero->nombre = $request->nombre;
            $genero->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
}