<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoricoConsultasApiModel;

class HistoricoConsultasPilaController extends Controller
{
    public function index($cantidad)
    {
        $result = HistoricoConsultasApiModel::join('usr_app_usuarios as usuario', 'usuario.id', 'usr_app_historico_masivo_pila.usuario_id')
            ->select(
                'usr_app_historico_masivo_pila.id',
                'usr_app_historico_masivo_pila.nombre',
                'usr_app_historico_masivo_pila.usuario_id',
                'usr_app_historico_masivo_pila.created_at',
                'usuario.nombre as usuario_nombre'
            )
            ->orderby('usr_app_historico_masivo_pila.created_at')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function create($user_id, $consulta_id)
    {
        try {
            $historico_pila = new HistoricoConsultasApiModel;
            $historico_pila->user_id = $user_id;
            $historico_pila->consulta_id = $consulta_id;
            if ($historico_pila->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Este rol ya se encuentra registrado']);
        }
    }
}