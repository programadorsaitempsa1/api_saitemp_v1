<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModalPrincipal;
use App\Events\VentanaModalPrincipal;
use App\Models\ActualizacionProgramada;

class ModalPrincipalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ModalPrincipal::first();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $result = new ModalPrincipal;
        $result->visible = $request->modal_visible;
        $result->titulo = $request->titulo;
        $result->contenido = $request->descripcion;
        if ($result->save()) {
            return response()->json(["status" => "success", "message" => "Registro insertado de manera exitosa."]);
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
            'visible' => $request->modal_visible,
            'titulo' => $request->titulo,
            'contenido' => $request->descripcion,
            'actualiza_pagina' => $request->recarga_navegador,
            'alerta' => $request->alerta,
        ];
        event(new VentanaModalPrincipal($data));
        $result = ModalPrincipal::find($id);
        $result->visible = $request->modal_visible;
        $result->titulo = $request->titulo;
        $result->contenido = $request->descripcion;
        $result2 = ActualizacionProgramada::find($id);
        $result2->visible = 0;
        $result2->save();
        if ($result->save()) {
            return response()->json(["status" => "success", "message" => "Registro actualizado de manera exitosa."]);
        }
    }


    public function updatevisibility($id)
    {
        $result = ModalPrincipal::find($id);
        $result->visible = 1;
        if ($result->save()) {
            return response()->json($result);
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
