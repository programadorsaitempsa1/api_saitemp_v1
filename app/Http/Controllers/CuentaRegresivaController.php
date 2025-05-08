<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActualizacionProgramada;
use App\Events\TiempoActualizacion;
use Illuminate\Support\Facades\DB;


class CuentaRegresivaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ActualizacionProgramada::select(
            'id',
            'visible',
            'mensaje_navbar',
            'fecha_hora',
            'estilo_span',
            'estilo_contador',
            'tamano_contador',
            'tamano_texto_contador'
        )
            ->first();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $result = new ActualizacionProgramada;
        $result->visible = $request->contador_visible;
        $result->fecha_hora = $request->fecha_hora;
        $result->mensaje_navbar = $request->mensaje_navbar;
        $result->estilo_span = $request->estilo_span;
        $result->estilo_contador = $request->estilo_contador;
        $result->tamano_contador = $request->tamano_contador;
        $result->tamano_texto_contador = $request->tamano_texto_contador;
        if ($result->save()) {
            return response()->json(["status" => "success", "message" => "Registro insertado de manera exitosa."]);
        }
    }

    public function ocultacontador()
    {
        $result = ActualizacionProgramada::find(1);
        $result->visible = 0;
        if ($result->save()) {
            return response()->json(0);
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

        $data = [
            'visible' => $request->contador_visible,
            'fecha_hora' => $request->fecha_hora,
            'mensaje_navbar' => $request->mensaje_navbar,
            'estilo_span' => $request->estilo_span,
            'estilo_contador' => $request->estilo_contador,
            'tamano_contador' => $request->tamano_contador,
            'tamano_texto_contador' => $request->tamano_texto_contador
        ];

        event(new TiempoActualizacion($data));
        $result = ActualizacionProgramada::find($id);
        $result->visible = $request->contador_visible;
        $result->fecha_hora = $request->fecha_hora;
        $result->mensaje_navbar = $request->mensaje_navbar;
        $result->estilo_span = $request->estilo_span;
        $result->estilo_contador = $request->estilo_contador;
        $result->tamano_contador = $request->tamano_contador;
        $result->tamano_texto_contador = $request->tamano_texto_contador;
        if ($result->save()) {
            return response()->json(["status" => "success", "message" => "Registro actualizado de manera exitosa."]);
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
