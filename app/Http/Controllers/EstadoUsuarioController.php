<?php

namespace App\Http\Controllers;

use App\Models\EstadoUsuario;
use Illuminate\Http\Request;

class EstadoUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = EstadoUsuario::all();
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
            $estado = new EstadoUsuario;
            $estado->nombre = $request->nombre;
            $estado->descripcion = $request->descripcion;
            if ($estado->save()) {
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
            $estado = EstadoUsuario::find($id);
            $estado->nombre = $request->nombre;
            $estado->descripcion = $request->descripcion;
            if ($estado->save()) {
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
        $estado = EstadoUsuario::find($id);
        if ($estado->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
