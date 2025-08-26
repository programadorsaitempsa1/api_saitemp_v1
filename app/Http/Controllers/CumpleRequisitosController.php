<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidatosRequisitosModel;

class CumpleRequisitosController extends Controller
{
    public function destroy($id)
    {
        $requisito = CandidatosRequisitosModel::find($id);
        if ($requisito->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}