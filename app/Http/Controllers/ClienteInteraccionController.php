<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClienteInteraccion;

class ClienteInteraccionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ClienteInteraccion::select(

        )
        ->get();
        return response()->json($result);
    }


    public function byid($id)
    {
        $result = ClienteInteraccion::join('usr_app_clientes as cli','cli.id','usr_app_cliente_interaccion.cliente_id')
        ->join('usr_app_procesos as pro','pro.id','usr_app_cliente_interaccion.proceso_id')
        ->join('usr_app_atencion_interacion as int','int.id','usr_app_cliente_interaccion.interaccion_id')
        ->where('cliente_id',$id)
        ->select(
            'usr_app_cliente_interaccion.id',
            'usr_app_cliente_interaccion.usuario',
            'usr_app_cliente_interaccion.observacion',
            'usr_app_cliente_interaccion.created_at',
            'usr_app_cliente_interaccion.updated_at',
            'cli.razon_social',
            'cli.numero_radicado',
            'pro.nombre as proceso',
            'int.nombre as interaccion',
        )
        ->orderby( 'usr_app_cliente_interaccion.id', 'DESC')
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
        $user = auth()->user();
        $result = new ClienteInteraccion;
        $result->cliente_id = $request->cliente;
        $result->proceso_id = $request->proceso_id;
        $result->interaccion_id = $request->interaccion_id;
        $result->usuario = $user->nombres.' '.$user->apellidos;
        $result->observacion = $request->observacion;
        if($result->save()){
            return response()->json(['status'=>'success','message'=>'Registro guardado exitosamente']);
        }else{
            return response()->json(['status'=>'error','message'=>'Error al guardar registro']);
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
