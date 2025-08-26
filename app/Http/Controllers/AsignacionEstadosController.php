<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionEstadosModel;
use Illuminate\Support\Facades\DB;

class AsignacionEstadosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = AsignacionEstadosModel::leftJoin('usr_app_estados_ingreso as est','est.id','usr_app_asignacion_estados.estado_id')
        ->select(
            'usr_app_asignacion_estados.id',
            'usr_app_asignacion_estados.nombre_modulo_solicitd',
            'usr_app_asignacion_estados.nombre_modulo_estados',
            'usr_app_asignacion_estados.descripcion_solicitud',
            'usr_app_asignacion_estados.nombre_tabla',
            'usr_app_asignacion_estados.estado_id',
            'est.nombre as estado_orden_servicio',
            'usr_app_asignacion_estados.nombre_endpoint',
        )->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return $request;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->all() as  $item) {
                $estado = AsignacionEstadosModel::find($item['id']);
                $estado->estado_id = $item['estado_id'];
                $estado->save();
            }
            DB::commit();
            return response()->json(["status" => "success", "message" => "Registro guardado exitosamente."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json(["status" => "error", "message" => "Error al guardar el registro."]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
