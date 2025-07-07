<?php

namespace App\Http\Controllers;

use App\Models\FacilitadorAnalistaModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FacilitadorAnalistaController extends Controller
{
    public function index($cantidad)
    {

        $result = FacilitadorAnalistaModel::join('usr_app_usuarios as us_facilitador', 'us_facilitador.id', 'usr_app_facilitador_analistas.facilitador_id')
            ->join('usr_app_usuarios as us_analista', 'us_analista.id', 'usr_app_facilitador_analistas.analista_id')
            ->join('usr_app_usuarios_internos as ui_facilitador', 'ui_facilitador.usuario_id', 'us_facilitador.id')
            ->join('usr_app_usuarios_internos as ui_analista', 'ui_analista.usuario_id', 'us_analista.id')
            ->select(
                'usr_app_facilitador_analistas.id',
                /*         'usr_app_facilitador_analistas.facilitador_id',
                'usr_app_facilitador_analistas.analista_id', */
                DB::raw("CONCAT(ui_facilitador.nombres,' ',ui_facilitador.apellidos) AS nombre_facilitador"),
                DB::raw("CONCAT(ui_analista.nombres,' ',ui_analista.apellidos) AS nombre_analista")
            )
            ->orderby('ui_facilitador.nombres')
            ->paginate($cantidad);

        return response()->json($result);
    }

    public function create(Request $request)
    {
        try {
            $facilitadorAnalista = new FacilitadorAnalistaModel;
            $facilitadorAnalista->facilitador_id = $request['facilitador_id'];
            $facilitadorAnalista->analista_id = $request['analista_id'];
            $facilitadorAnalista->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    public function delete($id)
    {
        try {
            $facilitadorAnalista = FacilitadorAnalistaModel::find($id);
            if ($facilitadorAnalista->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }

    public function byUserId($usuario_id)
    {
        try {
            $result = FacilitadorAnalistaModel::join('usr_app_usuarios as us_facilitador', 'us_facilitador.id', 'usr_app_facilitador_analistas.facilitador_id')
                ->join('usr_app_usuarios as us_analista', 'us_analista.id', 'usr_app_facilitador_analistas.analista_id')
                ->join('usr_app_usuarios_internos as ui_facilitador', 'ui_facilitador.usuario_id', 'us_facilitador.id')
                ->join('usr_app_usuarios_internos as ui_analista', 'ui_analista.usuario_id', 'us_analista.id')->where('facilitador_id', $usuario_id)->orWhere('analista_id', $usuario_id)
                ->select(
                    'usr_app_facilitador_analistas.id',
                    'usr_app_facilitador_analistas.facilitador_id',
                    'usr_app_facilitador_analistas.analista_id',
                    DB::raw("CONCAT(ui_facilitador.nombres,' ',ui_facilitador.apellidos) AS nombre_facilitador"),
                    DB::raw("CONCAT(ui_analista.nombres,' ',ui_analista.apellidos) AS nombre_analista")
                )
                ->get();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al buscar, por favor intente nuevamente']);
        }
    }
}