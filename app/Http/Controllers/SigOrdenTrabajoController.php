<?php

namespace App\Http\Controllers;

use App\Models\SigFormulario;
use App\Models\SigFormularioOrdenTrabajo;
use App\Models\SigItem;
use App\Models\SigOrdenTrabajo;
use App\Models\user;
use Illuminate\Http\Request;

class SigOrdenTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function cargaMasiva(Request $request)
    {
        try {
            $user = auth()->user();
            $contrato = $this->contrato();
            $documento = $request->file('cargamasiva')->getContent();
            $lineas = explode("\n", $documento);
            $numero_ots = count($lineas) - 1;
            $posicion = 0;
            $valores = explode(";", $lineas[0]);
            $duplicados = "Los numeros de orden de trabajo";
            $valida_duplicados = false;
            for ($i = 1; $i < $numero_ots; $i++) {
                try {
                    $valores = explode(";", $lineas[$i]);
                    $orden_trabajo = new SigOrdenTrabajo;
                    $orden_trabajo->numero = $valores[$posicion];
                    $orden_trabajo->actividad = $valores[$posicion + 1];
                    $orden_trabajo->direccion = $valores[$posicion + 2];
                    $orden_trabajo->responsable = $valores[$posicion + 3];
                    $orden_trabajo->gestor_sst = $valores[$posicion + 4];
                    $orden_trabajo->descripcion = $valores[$posicion + 5];
                    $orden_trabajo->contrato_id = $contrato[0]->contrato_id;
                    $orden_trabajo->creador_user_id = $user->documento_identidad;
                    $orden_trabajo->save();
                } catch (\Exception$e) {
                    if ($e->errorInfo[1] == '1062') {
                        $valida_duplicados = true;
                        $duplicados .= ', ' . $valores[$posicion];
                    }
                }
            }
            if ($valida_duplicados) {
                return response()->json(['status' => 'duplicate', 'message' => $duplicados . ' ya se encuentran registrados en la base de datos.']);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Registros guardados exitosamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar los registros, por favor verifique el archivo cargado e intente nuevamente']);
        }

    }

    public function index($cantidad)
    {
        $id = auth()->user();
        $contrato = $this->contrato();
        if (count($contrato) <= 0) {
            $result = SigOrdenTrabajo::join('sig_estado_ordenes_trabajo', 'sig_estado_ordenes_trabajo.id', '=', 'sig_ordenes_trabajo.estado_orden_trabajo_id')
                ->select(
                    'sig_ordenes_trabajo.id',
                    'sig_ordenes_trabajo.numero',
                    'sig_ordenes_trabajo.actividad',
                    'sig_estado_ordenes_trabajo.nombre as estado',
                    'sig_ordenes_trabajo.estado_orden_trabajo_id',
                    'sig_ordenes_trabajo.direccion',
                    'sig_ordenes_trabajo.descripcion',
                    'sig_ordenes_trabajo.contrato_id',
                    'sig_ordenes_trabajo.responsable',
                    'sig_ordenes_trabajo.gestor_sst',
                    'sig_ordenes_trabajo.created_at as fecha_creacion',
                    'sig_ordenes_trabajo.updated_at as fecha_actualizacion',

                )
                ->orderby('sig_ordenes_trabajo.id', 'DESC')
                ->paginate($cantidad);
            // ->get();
            return response()->json($result);
        } else {
            $result = SigOrdenTrabajo::join('sig_estado_ordenes_trabajo', 'sig_estado_ordenes_trabajo.id', '=', 'sig_ordenes_trabajo.estado_orden_trabajo_id')
                ->where('contrato_id', '=', $contrato[0]->contrato_id)
                ->where('creador_user_id', '=', $id->documento_identidad)
                ->select(
                    'sig_ordenes_trabajo.id',
                    'sig_ordenes_trabajo.numero',
                    'sig_ordenes_trabajo.actividad',
                    'sig_estado_ordenes_trabajo.nombre as estado',
                    'sig_ordenes_trabajo.estado_orden_trabajo_id',
                    'sig_ordenes_trabajo.direccion',
                    'sig_ordenes_trabajo.descripcion',
                    'sig_ordenes_trabajo.responsable',
                    'sig_ordenes_trabajo.gestor_sst',
                    'sig_ordenes_trabajo.created_at as fecha_creacion',
                    'sig_ordenes_trabajo.updated_at as fecha_actualizacion',
                )
                ->orderby('sig_ordenes_trabajo.id', 'desc')
                ->paginate($cantidad);
            // ->get();
            return response()->json($result);
        }
    }

    public function asignadas($cantidad)
    {

        $id = auth()->user();
        $result = SigOrdenTrabajo::join('sig_estado_ordenes_trabajo', 'sig_estado_ordenes_trabajo.id', '=', 'sig_ordenes_trabajo.estado_orden_trabajo_id')
            ->where('responsable', 'like', '%' . $id->documento_identidad . '%')
        // ->where('estado_orden_trabajo_id','=','1')
        // ->orwhere('estado_orden_trabajo_id','=','3')
            ->select(
                'sig_ordenes_trabajo.id',
                'sig_ordenes_trabajo.numero',
                'sig_ordenes_trabajo.actividad',
                'sig_estado_ordenes_trabajo.nombre as estado',
                'sig_ordenes_trabajo.estado_orden_trabajo_id',
                'sig_ordenes_trabajo.direccion',
                'sig_ordenes_trabajo.descripcion',
                'sig_ordenes_trabajo.contrato_id',
                'sig_ordenes_trabajo.responsable',
                'sig_ordenes_trabajo.gestor_sst',
                'sig_ordenes_trabajo.created_at as fecha_asignado',
                'sig_ordenes_trabajo.creador_user_id as autor',
            )
            ->orderby('sig_ordenes_trabajo.id', 'DESC')
        // ->get();
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function contrato()
    {
        $id = auth()->id();
        $contrato = user::join("sig_usuarios_contratos", "sig_usuarios_contratos.usuario_id", "=", "usuarios.id")
            ->join("sig_contratos", "sig_contratos.id", "=", "sig_usuarios_contratos.contrato_id")
            ->where('usuarios.id', '=', $id)
            ->select(
                "sig_contratos.id as contrato_id",
            )
        // ->get();
            ->paginate();
        return $contrato;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        // $documento = $request->file('cargamasiva')->getContent();
        // return $documento;
        $user = auth()->user();
        try {
            $orden_trabajo = new SigOrdenTrabajo;
            $orden_trabajo->numero = $request->numero;
            $orden_trabajo->actividad = $request->actividad;
            $orden_trabajo->direccion = $request->direccion;
            $orden_trabajo->descripcion = $request->descripcion;
            $orden_trabajo->contrato_id = $request->contrato_id;
            $orden_trabajo->responsable = $request->responsable;
            $orden_trabajo->gestor_sst = $request->gestor_sst;
            $orden_trabajo->creador_user_id = $user->documento_identidad;
            // $orden_trabajo->estado_orden_trabajo_id = $request->estado_orden_trabajo_id;

            $orden_trabajo->save();

            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception$e) {
            if ($e->errorInfo[1] == '1062') {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, ya existe una orden de trabajo con este número, agreguele un * al final.']);
            } else {
                return response()->json($e);
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

    public function otsactualizacionmasiva(Request $request)
    {
        try {
            // return $request->ots;
            foreach ($request->ots as $valor) {
                $result = SigOrdenTrabajo::find($valor);
                // return $result;
                if ($request->actividad != "") {
                    $result->actividad = $request->actividad;
                }
                if ($request->contrato_id != "") {
                    $result->contrato_id = $request->contrato_id;
                }
                if ($request->estado_orden_trabajo_id != "") {
                    $estadoAnterior = $request->estado_orden_trabajo_id;
                    $result->estado_orden_trabajo_id = $request->estado_orden_trabajo_id;
                }
                if ($request->direccion != "") {
                    $result->direccion = $request->direccion;
                }
                if ($request->responsable != "") {
                    $result->responsable = $request->responsable;
                }
                if ($request->gestor_sst != "") {
                    $result->gestor_sst = $request->gestor_sst;
                }
                if ($request->descripcion != "") {
                    $result->descripcion = $request->descripcion;
                }
                $result->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros actualizados exitosamente']);

        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
            return response()->json($e);
        }

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
            $orden_trabajo = SigOrdenTrabajo::find($id);
            $estado_ot = $orden_trabajo->estado_orden_trabajo_id;
            $orden_trabajo->numero = $request->numero;
            $orden_trabajo->actividad = $request->actividad;
            $orden_trabajo->direccion = $request->direccion;
            $orden_trabajo->descripcion = $request->descripcion;
            $orden_trabajo->estado_orden_trabajo_id = $request->estado_orden_trabajo_id;
            $orden_trabajo->contrato_id = $request->contrato_id;
            $orden_trabajo->responsable = $request->responsable;
            $orden_trabajo->gestor_sst = $request->gestor_sst;

            if ($orden_trabajo->save()) {
                // if ($estado_ot == 3 && $request->estado_orden_trabajo_id == 1) {
                //     $orden_trabajo = SigFormularioOrdenTrabajo::where('orden_trabajo_id', '=', $id)
                //         ->select(
                //             'sig_formulario_ordenes_trabajo.id',
                //             'sig_formulario_ordenes_trabajo.formulario_id'
                //         )
                //         ->get();

                //     $orden_trabajo = SigFormularioOrdenTrabajo::find($orden_trabajo[0]->id);
                //     $orden_trabajo->delete();

                //     $formularios = SigFormularioOrdenTrabajo::where('formulario_id', '=', $orden_trabajo->formulario_id)
                //         ->select(
                //             'sig_formulario_ordenes_trabajo.id',
                //         )
                //         ->get();
                //     if (count($formularios) <= 0) {
                //         $formularios = SigFormulario::find($orden_trabajo->formulario_id);

                //         $items = SigItem::where('sig_items.orden_trabajo', '=', $formularios->n_orden_trabajo)
                //             ->get();
                //         foreach ($items as $item) {
                //             $item->delete();
                //         }
                //         $formularios->delete();
                //     }
                    return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
                // } else {
                //     return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
                // }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => $e]);
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
        $orden_trabajo = SigOrdenTrabajo::find($id);
        if ($orden_trabajo->delete()) {

                $orden_trabajo = SigFormularioOrdenTrabajo::where('orden_trabajo_id', '=', $id)
                    ->select(
                        'sig_formulario_ordenes_trabajo.id',
                        'sig_formulario_ordenes_trabajo.formulario_id'
                    )
                    ->get();

                $orden_trabajo = SigFormularioOrdenTrabajo::find($orden_trabajo[0]->id);
                $orden_trabajo->delete();

                $formularios = SigFormularioOrdenTrabajo::where('formulario_id', '=', $orden_trabajo->formulario_id)
                    ->select(
                        'sig_formulario_ordenes_trabajo.id',
                    )
                    ->get();
                if (count($formularios) <= 0) {
                    $formularios = SigFormulario::find($orden_trabajo->formulario_id);

                    $items = SigItem::where('sig_items.orden_trabajo', '=', $formularios->n_orden_trabajo)
                        ->get();
                    foreach ($items as $item) {
                        $item->delete();
                    }
                    $formularios->delete();
                }

            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function otseliminacionmasiva(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->ot); $i++) {
                $result = SigOrdenTrabajo::find($request->ot[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

}
