<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OservicioestadoCargo;

class OservicioEstadoCargoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioestadoCargo::select(
            'id',
            'nombre'
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
        $result = new OservicioestadoCargo;
        $result->nombre = $request->nombre;
        $result->descripcion = $request->descripcion;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registrado guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Eroor al guardar registro']);
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
        $result = OservicioestadoCargo::find($id);
        $result->nombre = $request->nombre;
        $result->descripcion = $request->descripcion;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registrado guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Eroor al guardar registro']);
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
        $result = OservicioestadoCargo::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registrado guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Eroor al guardar registro']);
        }
    }
}
