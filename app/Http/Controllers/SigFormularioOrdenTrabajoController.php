<?php

namespace App\Http\Controllers;

use App\Models\SigFormulario;
use App\Models\SigFormularioOrdenTrabajo;
use Illuminate\Http\Request;

class SigFormularioOrdenTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = SigFormularioOrdenTrabajo::all();
        return response()->json($result);
    }

    public function findById($id)
    {
        $result = SigFormularioOrdenTrabajo::join('sig_ordenes_trabajo', 'sig_ordenes_trabajo.id', '=', 'sig_formulario_ordenes_trabajo.orden_trabajo_id')
            ->where('sig_ordenes_trabajo.numero', '=', $id)
            ->select(
                'sig_formulario_ordenes_trabajo.formulario_id',
            )
            ->get();
            if(count($result) > 0){
                $ots = SigFormularioOrdenTrabajo::join('sig_ordenes_trabajo', 'sig_ordenes_trabajo.id', '=', 'sig_formulario_ordenes_trabajo.orden_trabajo_id')
                ->where('sig_formulario_ordenes_trabajo.formulario_id', '=', $result[0]->formulario_id)
                ->select(
                    'sig_ordenes_trabajo.numero',
                )
                ->get();
            }
        if (count($result) > 0) {
            $result = SigFormulario::find($result[0]->formulario_id)
            ->where('sig_formularios.id','=',$result[0]->formulario_id)
                ->select(
                    'sig_formularios.n_orden_trabajo',
                    'sig_formularios.contrato',
                    'sig_formularios.zona',
                    'sig_formularios.fecha_emision',
                    'sig_formularios.fecha_emision',
                    'sig_formularios.n_orden_trabajo',
                    'sig_formularios.fecha_ejecucion',
                    'sig_formularios.estado',
                    'sig_formularios.autoriza_cancelacion',
                    'sig_formularios.observacion_cancelacion',
                    'sig_formularios.prioridad_trabajo',
                    'sig_formularios.hora_inicio',
                    'sig_formularios.hora_fin',
                    'sig_formularios.tiempo_programado',
                    'sig_formularios.n_colaboradores',
                    'sig_formularios.baja_tension',
                    'sig_formularios.media_tension',
                    'sig_formularios.tarea_asignada',
                    'sig_formularios.interpreta_planos',
                    'sig_formularios.planos',
                    'sig_formularios.trabajo_en_redes',
                    'sig_formularios.descripcion_ot',
                    'sig_formularios.descripcion_procedimiento',
                    'sig_formularios.medidas_seguridad',
                    'sig_formularios.otra_tarea',
                    'sig_formularios.otra_tarea2',
                    'sig_formularios.grupo_trabajo',
                    'sig_formularios.encargado_trabajo_nombre',
                    'sig_formularios.encargado_trabajo_firma',
                    'sig_formularios.encargado_trabajo_cargo',
                    'sig_formularios.gestor_sst_nombre',
                    'sig_formularios.gestor_sst_firma',
                    'sig_formularios.aprobador_nombre',
                    'sig_formularios.aprobador_firma',
                    'sig_formularios.aprobador_cargo',
                )
                ->get();

                $info = "";
                foreach ($ots as $valor){
                    $info .= $valor->numero.'-';
                }
                $result[0]['ots'] = $info;
                
            return response()->json($result);
        } else {
            return [];
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $formulario_orden_trabajo = new SigFormularioOrdenTrabajo;
        $formulario_orden_trabajo->orden_trabajo_id = $request->orden_trabajo_id;
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
    public function update(Request $request, $id)
    {
        //
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
