<?php

namespace App\Http\Controllers;

use App\Models\HabilidadesCandidatosModel;
use Illuminate\Http\Request;

class HabilidadesCandidatosController extends Controller
{
    public function deleteHabilidad($id)
    {

        $habilidad = HabilidadesCandidatosModel::find($id);
        if ($habilidad->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}