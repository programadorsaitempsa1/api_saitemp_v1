<?php

namespace App\Http\Controllers;

use App\Models\SigContratoEmpleado;
use Illuminate\Http\Request;

class SigContratoEmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = SigContratoEmpleado::join('sig_contratos', 'sig_contratos.id', '=', 'sig_contrato_empleados.contrato_id')
            ->join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
            ->join('sig_zonas', 'sig_zonas.id', '=', 'sig_contrato_empleados.zona_id')
            ->join('sig_empleados', 'sig_empleados.id', '=', 'sig_contrato_empleados.empleado_id')
            ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
            ->select(
                'sig_contrato_empleados.id',
                'sig_contratos.numero as contrato',
                'sig_estados_contrato.nombre as estado_contrato',
                'sig_contrato_empleados.descripcion',
                'sig_contratos.descripcion as descripcion_contrato',
                'sig_zonas.nombre as zona',
                'sig_empleados.nombres as nombres_empleado',
                'sig_empleados.apellidos as apellidos_empleado',              
                'sig_empleados.documento_identidad as documento_identidad_empleado',
                'sig_cargos.nombre as cargo_empleado',
            )
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function filtro(Request $request)
    {
        return response()->json(['status' => 'error', 'message' => 'Este módulo no está configurado para filtros avanzados']);
        // $result = SigContratoEmpleado::join('sig_contratos', 'sig_contratos.id', '=', 'Sig_contrato_empleados.contrato_id')
        //     ->join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
        //     ->join('sig_zonas', 'sig_zonas.id', '=', 'Sig_contrato_empleados.zona_id')
        //     ->join('sig_empleados', 'sig_empleados.id', '=', 'Sig_contrato_empleados.empleado_id')
        //     ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
        //     ->select(
        //         'Sig_contrato_empleados.id',
        //         'sig_contratos.numero as contrato',
        //         'sig_estados_contrato.nombre as estado_contrato',
        //         'sig_contrato_empleados.descripcion',
        //         'sig_contratos.descripcion as descripcion_contrato',
        //         'sig_zonas.nombre as zona',
        //         'sig_empleados.nombres as nombres_empleado',
        //         'sig_empleados.apellidos as apellidos_empleado',              
        //         'sig_empleados.documento_identidad as documento_identidad_empleado',
        //         'sig_cargos.nombre as cargo_empleado',
        //     )
        //     ->paginate($cantidad);
        // return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $contrato_empleado = new SigContratoEmpleado;
            $contrato_empleado->descripcion = $request->descripcion;
            $contrato_empleado->contrato_id = $request->contrato;
            $contrato_empleado->empleado_id = $request->empleado;
            $contrato_empleado->zona_id = $request->zona;
            if ($contrato_empleado->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'El empleado ya se encuentra registrado en un contrato']);
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

    public function actualizacionmasiva(Request $request)
    {
        try {
            foreach ($request->id as $valor) {
                $result = SigContratoEmpleado::find($valor);

                foreach ($request->campos as $clave => $valor) {
                    if ($valor != "") {
                        $result->$clave = $valor;
                    }
                }
                $result->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros actualizados exitosamente']);

        } catch (\Exception$e) {
            return response()->json($e);
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
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
            $contrato_empleado = SigContratoEmpleado::find($id);
            $contrato_empleado->descripcion = $request->descripcion;
            $contrato_empleado->contrato_id = $request->contrato;
            $contrato_empleado->empleado_id = $request->empleado;
            $contrato_empleado->zona_id = $request->zona;
            if ($contrato_empleado->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
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
        $contrato_empleado = SigContratoEmpleado::find($id);
        if ($contrato_empleado->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = SigContratoEmpleado::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
