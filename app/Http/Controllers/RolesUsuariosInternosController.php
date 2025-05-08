<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RolesUsuariosInternosModel;

class RolesUsuariosInternosController extends Controller
{
    public function index()
    {
        $result = RolesUsuariosInternosModel::select()
            ->get();
        return response()->json($result);
    }

    public function create(Request $request)
    {
        $rol = new RolesUsuariosInternosModel;
        $rol->nombre = $request->nombre;
        $rol->descripcion = $request->descripcion;
        if ($rol->save()) {
            return response()->json(['status' => 'success', 'message' => 'Regitro guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar registro']);
        }
    }

    public function update(Request $request, $id)
    {
        $result = RolesUsuariosInternosModel::find($id);
        $result->nombre = $request->nombre;
        $result->descripcion = $request->descripcion;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Regitro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro']);
        }
    }
    public function destroy($id)
    {
        try {
            $result = RolesUsuariosInternosModel::find($id);
            if ($result->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Regitro borrado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al borrar registro']);
            }
        } catch (\Exception $e) {
            // return $e;
            return response()->json(['status' => 'error', 'message' => 'Hay una relación entre un usuario y este menú, por favor primero elimine la relación']);
        }
    }
}