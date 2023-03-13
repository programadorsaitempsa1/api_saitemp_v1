<?php

namespace App\Http\Controllers;

use App\Models\SigEstadoEmpleado;
use Illuminate\Http\Request;

class SigEstadoEmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = SigEstadoEmpleado::select(
            'sig_estado_empleados.id',
            'sig_estado_empleados.nombre',
            'sig_estado_empleados.descripcion',
            )
            ->paginate($cantidad);
            return response()->json($result);
    }
    
    public function lista()
    {
        $result = SigEstadoEmpleado::select(
            'sig_estado_empleados.id',
            'sig_estado_empleados.nombre',
            'sig_estado_empleados.descripcion',
            )
            ->get();
            return response()->json($result);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $estado_empleado = new SigEstadoEmpleado;
            $estado_empleado->nombre = $request->nombre;
            $estado_empleado->descripcion = $request->descripcion;
            if ($estado_empleado->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Este estado ya se encuentra registrado']);
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
                $result = SigEstadoEmpleado::find($valor);

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
            $estado_empleado = SigEstadoEmpleado::find($id);
            $estado_empleado->nombre = $request->nombre;
            $estado_empleado->descripcion = $request->descripcion;
            if ($estado_empleado->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Este estado ya se encuentra registrado']);
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
        $estado_empleado = SigEstadoEmpleado::find($id);
        if ($estado_empleado->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = SigEstadoEmpleado::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
