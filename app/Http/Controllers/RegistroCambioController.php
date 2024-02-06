<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroCambio;

class RegistroCambioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = RegistroCambio::select(
            'solicitante',
            'autoriza',
            'actualiza',
            'observaciones',
            'cliente_id as cliente',
            'created_at',
            'updated_at'
        )
            ->get();
        return response()->json($result);
    }

    public function byid($id)
    {
        $result = RegistroCambio::join('usr_app_clientes as cli','cli.id','usr_app_registro_cambios.cliente_id')
        ->select(
            'cli.razon_social',
            'cli.numero_radicado',
            'usr_app_registro_cambios.solicitante',
            'usr_app_registro_cambios.autoriza',
            'usr_app_registro_cambios.actualiza',
            'usr_app_registro_cambios.observaciones',
            'usr_app_registro_cambios.cliente_id as cliente',
            'usr_app_registro_cambios.updated_at'
        )
            ->where('usr_app_registro_cambios.cliente_id', $id)
            ->orderby('usr_app_registro_cambios.id','DESC')
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        //
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
