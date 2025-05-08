<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistoricoProfesionalesModel;

class HistoricoProfesionalesController extends Controller
{
    public function index()
    {

        $historico = HistoricoProfesionalesModel::join("usr_app_clientes", "usr_app_clientes.id", "=", "usr_app_historico_profesionales_dd.cliente_id")
            ->select(
                "usr_app_historico_profesionales_dd.profesional_seleccion",
                "usr_app_historico_profesionales_dd.usuario_selecion_id",
                "usr_app_historico_profesionales_dd.anotacion_seleccion",
                "usr_app_historico_profesionales_dd.profesional_cartera",
                "usr_app_historico_profesionales_dd.usuario_cartera_id",
                "usr_app_historico_profesionales_dd.anotacion_cartera",
                "usr_app_historico_profesionales_dd.profesional_sst",
                "usr_app_historico_profesionales_dd.usuario_sst_id",
                "usr_app_historico_profesionales_dd.anotacion_sst",
                "usr_app_clientes.nit as nit",
                "usr_app_clientes.numero_identificacion as numero_identificacion"
            )->get();
        return response()->json($historico);
    }

    public function byClienteId($cliente_id, $asJson = true)
    {

        $historico = HistoricoProfesionalesModel::join("usr_app_clientes", "usr_app_clientes.id", "=", "usr_app_historico_profesionales_dd.cliente_id")
            ->where("usr_app_historico_profesionales_dd.cliente_id", $cliente_id)
            ->select(
                "usr_app_historico_profesionales_dd.profesional_seleccion",
                "usr_app_historico_profesionales_dd.usuario_selecion_id",
                "usr_app_historico_profesionales_dd.anotacion_seleccion",
                "usr_app_historico_profesionales_dd.profesional_cartera",
                "usr_app_historico_profesionales_dd.usuario_cartera_id",
                "usr_app_historico_profesionales_dd.anotacion_cartera",
                "usr_app_historico_profesionales_dd.profesional_sst",
                "usr_app_historico_profesionales_dd.usuario_sst_id",
                "usr_app_historico_profesionales_dd.anotacion_sst",
                "usr_app_historico_profesionales_dd.profesional_nomina",
                "usr_app_historico_profesionales_dd.usuario_nomina_id",
                "usr_app_historico_profesionales_dd.anotacion_nomina",
                "usr_app_clientes.nit as nit",
                "usr_app_clientes.numero_identificacion as numero_identificacion"
            )->latest("usr_app_historico_profesionales_dd.id") // Asegúrate de que `id` sea una columna válida
            ->first();
        if ($asJson) {
            return response()->json($historico);
        } else {
            return $historico;
        }
    }
}