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
            DB::beginTransaction();
            $usuarios = $request->all();
            foreach ($usuarios[0] as  $usuarios_facilitadores) {
                foreach ($usuarios[1] as  $usuarios_analistas) {
                    $result = new FacilitadorAnalistaModel();
                    $result->facilitador_id = $usuarios_facilitadores['id'];
                    $result->analista_id = $usuarios_analistas['id'];
                    $result->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'ya existe uno de los estados ingresados.']);
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

    public function byUserId($cadena, $cantidad = null)
    {
        if ($cantidad == null) {
            $cantidad = 10;
        }
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];

        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];
        $query = FacilitadorAnalistaModel::join('usr_app_usuarios as us_facilitador', 'us_facilitador.id', 'usr_app_facilitador_analistas.facilitador_id')
            ->join('usr_app_usuarios as us_analista', 'us_analista.id', 'usr_app_facilitador_analistas.analista_id')
            ->join('usr_app_usuarios_internos as ui_facilitador', 'ui_facilitador.usuario_id', 'us_facilitador.id')
            ->join('usr_app_usuarios_internos as ui_analista', 'ui_analista.usuario_id', 'us_analista.id')
            ->select(
                'usr_app_facilitador_analistas.id',
                DB::raw("CONCAT(ui_facilitador.nombres,' ',ui_facilitador.apellidos) AS nombre_facilitador"),
                DB::raw("CONCAT(ui_analista.nombres,' ',ui_analista.apellidos) AS nombre_analista")
            )
            ->orderby('usr_app_facilitador_analistas.id');
        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];
            $esUsuario = $campoActual === 'usuario';

            $prefijoCampo = '';
            if ($campoActual === 'nombre_facilitador') {
                $prefijoCampo = 'ui_facilitador.';
                $campoActual = 'nombres';
            } elseif ($campoActual === 'nombre_analista') {
                $prefijoCampo = 'ui_analista.';
                $campoActual = 'nombres';
            }


            switch ($operadorActual) {
                case 'Igual a':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Contiene':
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
            }
        }
        $resultados = $query->paginate($cantidad);

        return $resultados;
    }
}