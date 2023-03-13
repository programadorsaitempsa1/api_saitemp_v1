<?php

namespace App\Http\Controllers;

use App\Models\SigContratoEmpleado;
use App\Models\SigFormulario;
use App\Models\SigFormularioOrdenTrabajo;
use App\Models\SigItem;
use App\Models\SigOrdenTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SigFormularioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = SigFormulario::all();
        return response()->json($result);
    }

    public function getbyid($id)
    {
        $result = SigFormulario::where('sig_formularios.n_orden_trabajo', '=', $id)
            ->select(
                'sig_formularios.id'
            )
            ->get();
        if (count($result) > 0) {
            return response()->json(['status' => 'success', 'message' => 'Ya se encuentra registrada una orden de trabajo con este número']);
        } else {
            return response()->json(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $user = auth()->user();
            $formulario = new SigFormulario;
            $firmas = '';
            if ($request->firma0) {
                for ($i = 0; $i <= 100; $i++) {
                    $nombre = 'nombre' . '' . $i;
                    $cedula = 'cedula' . '' . $i;
                    $firma = 'firma' . '' . $i;

                    if ($request->$nombre != '') {
                        $nombreArchivoOriginal = $request->file($firma)->getClientOriginalName();
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                        $carpetaDestino = './upload/';
                        $request->file($firma)->move($carpetaDestino, $nuevoNombre);
                        $firma = ltrim($carpetaDestino, '.') . $nuevoNombre;

                        $firmas .= $request->$nombre . ',' . $request->$cedula . ',' . $firma . ',';
                    }
                }
            }
            $formulario->grupo_trabajo = $firmas;

            if ($request->hasFile('planos')) {
                $nombreArchivoOriginal = $request->file('planos')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('planos')->move($carpetaDestino, $nuevoNombre);
                $formulario->planos = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            if ($request->hasFile('encargado_trabajo_firma')) {
                $nombreArchivoOriginal = $request->file('encargado_trabajo_firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('encargado_trabajo_firma')->move($carpetaDestino, $nuevoNombre);
                $formulario->encargado_trabajo_firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }
            if ($request->hasFile('gestor_sst_firma')) {
                $nombreArchivoOriginal = $request->file('gestor_sst_firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('gestor_sst_firma')->move($carpetaDestino, $nuevoNombre);
                $formulario->gestor_sst_firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }
            if ($request->hasFile('aprobador_firma')) {
                $nombreArchivoOriginal = $request->file('aprobador_firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('aprobador_firma')->move($carpetaDestino, $nuevoNombre);
                $formulario->aprobador_firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            $formulario->contrato = $request->contrato;
            $formulario->zona = $request->zona;
            $formulario->fecha_emision = $request->fecha_emision;
            $formulario->n_orden_trabajo = $request->n_descargo;
            $formulario->fecha_ejecucion = $request->fecha_ejecucion;
            $formulario->estado = $request->estado;
            $formulario->autoriza_cancelacion = $request->autoriza_cancelacion;
            $formulario->observacion_cancelacion = $request->observacion_cancelacion;
            $formulario->prioridad_trabajo = $request->prioridad_trabajo;
            $formulario->hora_inicio = $request->hora_inicio;
            $formulario->hora_fin = $request->hora_fin;
            $formulario->tiempo_programado = $request->tiempo_programado;
            $formulario->n_colaboradores = $request->n_colaboradores;
            $formulario->baja_tension = $request->baja_tension;
            $formulario->media_tension = $request->media_tension;
            $formulario->tarea_asignada = $request->tarea_asignada;
            $formulario->interpreta_planos = $request->interpreta_planos;
            $formulario->trabajo_en_redes = $request->trabajo_en_redes;
            $formulario->descripcion_ot = $request->descripcion_ot;
            $formulario->descripcion_procedimiento = $request->descripcion_procedimiento;
            $formulario->medidas_seguridad = $request->medidas_seguridad;
            $formulario->otra_tarea = $request->otra_tarea;
            $formulario->otra_tarea2 = $request->otra_tarea2;
            $formulario->encargado_trabajo_nombre = $request->encargado_trabajo_nombre;
            $formulario->encargado_trabajo_cargo = $request->encargado_trabajo_cargo;
            $formulario->gestor_sst_nombre = $request->gestor_sst_nombre;
            $formulario->aprobador_nombre = $request->aprobador_nombre;
            $formulario->aprobador_cargo = $request->aprobador_cargo;

            $formulario->save();

            $ordentrabajo_empleado = SigContratoEmpleado::join('sig_contratos', 'sig_contratos.id', '=', 'contrato_id')
                ->join('sig_empleados', 'sig_empleados.id', '=', 'empleado_id')
                ->join('sig_zonas', 'sig_zonas.id', '=', 'zona_id')
                ->where('sig_empleados.documento_identidad', '=', $user->documento_identidad)
                ->select(
                    'sig_contratos.numero as contrato',
                    'sig_empleados.nombres',
                    'sig_empleados.apellidos',
                    'sig_empleados.documento_identidad',
                    'sig_zonas.nombre as subregion',
                )
                ->get();

            $ordenes_trabajo_id = explode("*", $request->ots);
            for ($i = 0; $i <= count($ordenes_trabajo_id) - 2; $i++) {
                $formulario_orden_trabajo = new SigFormularioOrdenTrabajo;
                $formulario_orden_trabajo->orden_trabajo_id = $ordenes_trabajo_id[$i];
                $formulario_orden_trabajo->formulario_id = $formulario->id;
                $formulario_orden_trabajo->save();

                if ($request->hora_fin != '') {
                    $orden_trabajo = SigOrdenTrabajo::find($ordenes_trabajo_id[$i]);
                    $orden_trabajo->estado_orden_trabajo_id = 3;
                    $orden_trabajo->save();
                    if ($request->item0) {
                        for ($j = 0; $j <= 100; $j++) {
                            $item = 'item' . $j;
                            $categoria = 'categoria' . $j;
                            $unidad_medida = 'unidad_medida' . $j;
                            $valor_unitario = 'valor_unitario' . $j;
                            $descripcion = 'descripcion' . $j;
                            $cantidad = 'cantidad' . $j;
                            $items = new SigItem;
                            $items->item = $request->$item;
                            $items->orden_trabajo = $orden_trabajo->numero;
                            $items->categoria = $request->$categoria;
                            $items->unidad_medida = $request->$unidad_medida;
                            $items->encargado = $ordentrabajo_empleado[0]->nombres . ' ' . $ordentrabajo_empleado[0]->apellidos . ' - ' . $ordentrabajo_empleado[0]->documento_identidad; //'andres felipe duque caro';//$request->$item;
                            $items->valor_unitario = $request->$valor_unitario;
                            $items->descripcion = $request->$descripcion;
                            $items->cantidad = $request->$cantidad;
                            $items->subregion = $ordentrabajo_empleado[0]->subregion;
                            $items->contrato = $ordentrabajo_empleado[0]->contrato;
                            $items->valor_total_item = floatval($request->$valor_unitario) * floatval($request->$cantidad);
                            if ($items->item == null) {
                                break;
                            }
                            $items->save();
                        }
                    }
                }
            }
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception$e) {
            if ($e->errorInfo[1] == '1366') {
                return response()->json(['status' => 'success', 'message' => $e]);
            } else {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
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
        try {
            $user = auth()->user();
            $formulario = SigFormulario::where('n_orden_trabajo', '=', $id)
                ->select(
                    'sig_formularios.id'
                )
                ->get();
            $formulario = SigFormulario::find($formulario)->first();

            $firmas = '';
            if ($request->firma0) {
                for ($i = 0; $i <= 100; $i++) {
                    $nombre = 'nombre' . '' . $i;
                    $cedula = 'cedula' . '' . $i;
                    $firma = 'firma' . '' . $i;

                    if ($request->$nombre != '') {
                        $nombreArchivoOriginal = $request->file($firma)->getClientOriginalName();
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                        $carpetaDestino = './upload/';
                        $request->file($firma)->move($carpetaDestino, $nuevoNombre);
                        $firma = ltrim($carpetaDestino, '.') . $nuevoNombre;

                        $firmas .= $request->$nombre . ',' . $request->$cedula . ',' . $firma . ',';
                    }
                }
            }
            $formulario->grupo_trabajo = $firmas;

            if ($request->hasFile('planos')) {

                if ($formulario->planos != null) {
                    $rutaArchivo = base_path('public') . $formulario->planos;
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                    }
                }

                $nombreArchivoOriginal = $request->file('planos')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('planos')->move($carpetaDestino, $nuevoNombre);
                $formulario->planos = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }
            if ($request->hasFile('encargado_trabajo_firma')) {

                if ($formulario->encargado_trabajo_firma != null) {
                    $rutaArchivo = base_path('public') . $formulario->encargado_trabajo_firma;
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                    }
                }

                $nombreArchivoOriginal = $request->file('encargado_trabajo_firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('encargado_trabajo_firma')->move($carpetaDestino, $nuevoNombre);
                $formulario->encargado_trabajo_firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }
            if ($request->hasFile('gestor_sst_firma')) {

                if ($formulario->gestor_sst_firma != null) {
                    $rutaArchivo = base_path('public') . $formulario->gestor_sst_firma;
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                    }
                }

                $nombreArchivoOriginal = $request->file('gestor_sst_firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('gestor_sst_firma')->move($carpetaDestino, $nuevoNombre);
                $formulario->gestor_sst_firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }
            if ($request->hasFile('aprobador_firma')) {

                if ($formulario->aprobador_firma != null) {
                    $rutaArchivo = base_path('public') . $formulario->aprobador_firma;
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                    }
                }

                $nombreArchivoOriginal = $request->file('aprobador_firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('aprobador_firma')->move($carpetaDestino, $nuevoNombre);
                $formulario->aprobador_firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            $formulario->contrato = $request->contrato != 'null' ? $request->contrato : '';
            $formulario->zona = $request->zona != 'null' ? $request->zona : '';
            $formulario->fecha_emision = $request->fecha_emision != 'null' ? $request->fecha_emision : '';
            $formulario->n_orden_trabajo = $request->n_descargo != 'null' ? $request->n_descargo : '';
            if ($request->fecha_ejecucion != 'null') {
                $formulario->fecha_ejecucion = $request->fecha_ejecucion;
            }
            $formulario->estado = $request->estado != 'null' ? $request->estado : '';
            $formulario->autoriza_cancelacion = $request->autoriza_cancelacion != 'null' ? $request->autoriza_cancelacion : '';
            $formulario->observacion_cancelacion = $request->observacion_cancelacion != 'null' ? $request->observacion_cancelacion : '';
            $formulario->prioridad_trabajo = $request->prioridad_trabajo != 'null' ? $request->prioridad_trabajo : '';
            $formulario->hora_inicio = $request->hora_inicio != 'null' ? $request->hora_inicio : '';
            $formulario->hora_fin = $request->hora_fin != 'null' ? $request->hora_fin : '';
            $formulario->tiempo_programado = $request->tiempo_programado != 'null' ? $request->tiempo_programado : '';
            $formulario->n_colaboradores = $request->n_colaboradores != 'null' ? $request->n_colaboradores : '';
            $formulario->baja_tension = $request->baja_tension != 'null' ? $request->baja_tension : '';
            $formulario->media_tension = $request->media_tension != 'null' ? $request->media_tension : '';
            $formulario->tarea_asignada = $request->tarea_asignada != 'null' ? $request->tarea_asignada : '';
            $formulario->interpreta_planos = $request->interpreta_planos != 'null' ? $request->interpreta_planos : '';
            $formulario->trabajo_en_redes = $request->trabajo_en_redes != 'null' ? $request->trabajo_en_redes : '';
            $formulario->descripcion_ot = $request->descripcion_ot != 'null' ? $request->descripcion_ot : '';
            $formulario->descripcion_procedimiento = $request->descripcion_procedimiento != 'null' ? $request->descripcion_procedimiento : '';
            $formulario->medidas_seguridad = $request->medidas_seguridad != 'null' ? $request->medidas_seguridad : '';
            $formulario->otra_tarea = $request->otra_tarea != 'null' ? $request->otra_tarea : '';
            $formulario->otra_tarea2 = $request->otra_tarea2 != 'null' ? $request->otra_tarea2 : '';
            $formulario->encargado_trabajo_nombre = $request->encargado_trabajo_nombre != 'null' ? $request->encargado_trabajo_nombre : '';
            $formulario->encargado_trabajo_cargo = $request->encargado_trabajo_cargo != 'null' ? $request->encargado_trabajo_cargo : '';
            $formulario->gestor_sst_nombre = $request->gestor_sst_nombre != 'null' ? $request->gestor_sst_nombre : '';
            $formulario->aprobador_nombre = $request->aprobador_nombre != 'null' ? $request->aprobador_nombre : '';
            $formulario->aprobador_cargo = $request->aprobador_cargo != 'null' ? $request->aprobador_cargo : '';

            $formulario->save();

            $ordentrabajo_empleado = SigContratoEmpleado::join('sig_contratos', 'sig_contratos.id', '=', 'contrato_id')
            ->join('sig_empleados', 'sig_empleados.id', '=', 'empleado_id')
            ->join('sig_zonas', 'sig_zonas.id', '=', 'zona_id')
            ->where('sig_empleados.documento_identidad', '=', $user->documento_identidad)
            ->select(
                'sig_contratos.numero as contrato',
                'sig_empleados.nombres',
                'sig_empleados.apellidos',
                'sig_empleados.documento_identidad',
                'sig_zonas.nombre as subregion',
            )
            ->get();


            $ordenes_trabajo_id = SigFormularioOrdenTrabajo::where('formulario_id', '=', $formulario->id)
                ->select(
                    'sig_formulario_ordenes_trabajo.orden_trabajo_id'
                )
                ->get();
            for ($i = 0; $i <= count($ordenes_trabajo_id) - 1; $i++) {
                if ($formulario->tiempo_programado != 'null' && $formulario->tiempo_programado != '') {
                    $orden_trabajo = SigOrdenTrabajo::find($ordenes_trabajo_id[$i])->first();
                    $orden_trabajo->estado_orden_trabajo_id = 3;
                    $orden_trabajo->save();

                    if ($request->item0) {
                        for ($j = 0; $j <= 100; $j++) {
                            $item = 'item' . $j;
                            $categoria = 'categoria' . $j;
                            $unidad_medida = 'unidad_medida' . $j;
                            $valor_unitario = 'valor_unitario' . $j;
                            $descripcion = 'descripcion' . $j;
                            $cantidad = 'cantidad' . $j;
                            $items = new SigItem;
                            $items->item = $request->$item;
                            $items->orden_trabajo = $orden_trabajo->numero;
                            $items->categoria = $request->$categoria;
                            $items->unidad_medida = $request->$unidad_medida;
                            $items->encargado = $ordentrabajo_empleado[0]->nombres . ' ' . $ordentrabajo_empleado[0]->apellidos . ' - ' . $ordentrabajo_empleado[0]->documento_identidad; //'andres felipe duque caro';//$request->$item;
                            $items->valor_unitario = $request->$valor_unitario;
                            $items->descripcion = $request->$descripcion;
                            $items->cantidad = $request->$cantidad;
                            $items->subregion = $ordentrabajo_empleado[0]->subregion;
                            $items->contrato = $ordentrabajo_empleado[0]->contrato;
                            $items->valor_total_item = floatval($request->$valor_unitario) * floatval($request->$cantidad);
                            if ($items->item == null) {
                                break;
                            }
                            $items->save();
                        }
                    }
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
        } catch (\Exception$e) {
            if ($e->errorInfo[1] == '1366') {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
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
