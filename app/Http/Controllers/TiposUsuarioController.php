<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TiposUsuarioModel;

class TiposUsuarioController extends Controller
{
    public function index()
    {
        $users = TiposUsuarioModel::get();
        return response()->json($users);
    }
    public function create(Request $request)
    {
        $user = new TiposUsuarioModel;
        $user->nombre = $request->nombre;
        $user->descripcion = $request->descripcion;
        if ($user->save()) {
            return response()->json(['status' => 'success', 'message' => 'Usuario actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al actualizar']);
        }
    }
    public function update(Request $request)
    {

        $user = TiposUsuarioModel::find($request->id);
        $user->nombre = $request->nombre;
        $user->descripcion = $request->descripcion;

        if ($user->save()) {
            return response()->json(['status' => 'success', 'message' => 'Usuario actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al actualizar']);
        }
    }
}