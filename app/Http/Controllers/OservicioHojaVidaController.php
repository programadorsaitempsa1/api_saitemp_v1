<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use Illuminate\Http\Request;
use App\Models\OservicioHojaVida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Mockery\Undefined;

class OservicioHojaVidaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioHojaVida::select()
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $cliente = new OservicioClienteController;
        try {
            $cliente_id = $cliente->getIdCliente($id);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Cliente no registrado']);
        }
        DB::beginTransaction();
        $hojas_vida = $request->all();
        try {
            $cargo = null;
            $registros = 0;
            foreach ($hojas_vida['cargo'] as $items) {
                foreach ($items as $item) {

                    $result = new OservicioHojaVida;
                    if (is_string($item)) {
                        $cargo = $item;
                    } elseif ($item != null) {

                        $registros++;
                        $result->nombre_cargo = $cargo;
                        $result->cliente_id = $cliente_id;
                        $result->fecha_hora_envio = Carbon::now();

                        $nombreArchivoOriginal = $item->getClientOriginalName();
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                        $carpetaDestino = './upload/hojas_vida/' . $id.'/';
                        $item->move($carpetaDestino, $nuevoNombre);
                        $item->ruta_documento = ltrim($carpetaDestino, '.') . $nuevoNombre;
                        $result->ruta_documento = $item->ruta_documento;
                        $result->save();
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'success', 'message' => 'Error al enviar hojas de vida']);
        }
        if ($registros == 0) {
            return response()->json(['status' => 'error', 'message' => 'No se adjuntaron hojas de vida']);
        }
        DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Hojas de vida enviadas de manera exitosa']);
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
        $result = OservicioHojaVida::find($id);
        $result->cliente_id = $request->cliente_id;
        $result->nombre_cargo = $request->nombre_cargo;
        $result->fecha_hora_envio = $request->fecha_hora_envio;
        $result->ruta_documento = $request->ruta_documento;
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
        $result = OservicioHojaVida::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
