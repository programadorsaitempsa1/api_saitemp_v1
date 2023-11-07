<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OservicioCliente;

class OservicioClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioCliente::select(
            'id',
            'nit_ndocumento',
            'nombre_razon_social',
            'nombre_solicitante',
            'celular_solicitante',
            'correo_solicitante'
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
        // return auth()->user()->id;
        $result = new OservicioCliente;
        $result->nit_ndocumento = $request->nit_ndocumento;
        $result->nombre_razon_social = $request->nombre_razon_social;
        $result->nombre_solicitante = $request->nombre_solicitante;
        $result->celular_solicitante = $request->celular_solicitante;
        $result->correo_solicitante = $request->correo_solicitante;
        $result->usuario_id = $request->usuario_id == '' ? auth()->user()->id:$request->usuario_id;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al guardar registro']);
        }
    }

    public function getIdCliente($id)
    {
        $cliente_id = OservicioCliente::select(
            'id'
        )
            ->where('nit_ndocumento', '=', $id)
            ->first();
        return $cliente_id->id;
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
        $result = OservicioCliente::find($id);
        $result->nit_ndocumento = $request->nit_ndocumento;
        $result->nombre_razon_social = $request->nombre_razon_social;
        $result->nombre_solicitante = $request->nombre_solicitante;
        $result->celular_solicitante = $request->celular_solicitante;
        $result->correo_solicitant = $request->correo_solicitant;
        $result->usuario_id = $request->usuario_id;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al actualizar registro']);
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
        $result = OservicioCliente::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
