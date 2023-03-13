<?php

namespace App\Http\Controllers;

use App\Models\SigOrdenTrabajoEmpleado;
use Illuminate\Http\Request;

class SigOrdenTrabajoEmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        return $user;
        $result = SigOrdenTrabajoEmpleado::where('usuario_id', '=', $user->id)
            ->select(
                'sig_ordenes_trabajo_empleados.id',
                'sig_ordenes_trabajo_empleados.orden_trabajo_id',
                'sig_ordenes_trabajo_empleados.usuario_id',
                'sig_ordenes_trabajo_empleados.descripcion',
            )
            ->get();
    if (count($result) == 0 && $user->rol_id == 1 || $user->rol_id == 2) {
            $result = SigOrdenTrabajoEmpleado::all();
            return $result;
        } else {
            return $result;
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $orden_trabajo = new SigOrdenTrabajoEmpleado;
        $orden_trabajo->orden_trabajo_id->$request->orden_trabajo_id;
        $orden_trabajo->usuario_id->$request->usuario_id;
        if ($orden_trabajo->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
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
        $orden_trabajo = SigOrdenTrabajoEmpleado::find($id);
        $orden_trabajo->orden_trabajo_id->$request->orden_trabajo_id;
        $orden_trabajo->usuario_id->$request->usuario_id;
        if ($orden_trabajo->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
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
        $orden_trabajo = SigOrdenTrabajoEmpleado::find($id);
        if ($orden_trabajo->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
