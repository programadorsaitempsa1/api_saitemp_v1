<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContratacionModal;
use App\Models\RegistroCambio;
use Illuminate\SUpport\Facades\DB;

class ContratacionModalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ContratacionModal::select(
            'id',
            'contacto',
            'cargo',
            'telefono',
            'celular',
            'correo',
            'hora_ingreso',
            'correo_confirmacion',
            'otro_si',
            'cliente_id'
        )
            ->get();
        return response()->json($result);
    }

    public function byid($id)
    {
        $result = ContratacionModal::select(
            'id',
            'contacto',
            'cargo',
            'telefono',
            'celular',
            'correo',
            'hora_ingreso',
            'correo_confirmacion',
            'otro_si',
            'cliente_id'
        )
            ->where('cliente_id', $id)
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
        $result = new ContratacionModal;
        $result->contacto = $request->contacto;
        $result->cargo = $request->cargo;
        $result->telefono = $request->telefono;
        $result->celular = $request->celular;
        $result->correo = $request->correo_electronico;
        $result->hora_ingreso = $request->hora_ingreso;
        $result->correo_confirmacion = $request->correo_ingresos;
        $result->otro_si = $request->otro_si;
        $result->cliente_id = $request->cliente_id;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro aguardado con exito.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar registro por favor ingrese nuevamente.']);
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
            DB::beginTransaction();
            $user = auth()->user();
            $result = ContratacionModal::where('cliente_id', '=', $id)->first();
            $result->contacto = $request->contacto;
            $result->cargo = $request->cargo;
            $result->telefono = $request->telefono;
            $result->celular = $request->celular;
            $result->correo = $request->correo_electronico;
            $result->hora_ingreso = $request->hora_ingreso;
            $result->correo_confirmacion = $request->correo_ingresos;
            $result->otro_si = $request->otro_si;
            $result->cliente_id = $request->cliente_id;
            $result->save();

            $nombres = str_replace("null", "", $user->nombres);
            $apellidos = str_replace("null", "", $user->apellidos);

            $registroCambio = new RegistroCambio;
            $registroCambio->observaciones = $request['registro_cambios']['observaciones'];
            $registroCambio->solicitante = $request['registro_cambios']['solicitante'];
            $registroCambio->autoriza = $request['registro_cambios']['autoriza'];
            $registroCambio->actualiza = $nombres . ' ' . $apellidos;
            $registroCambio->cliente_id = $request->cliente_id;
            $registroCambio->save();


            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado con exito.']);
        } catch (\Exception $e) {
            return $e;
            // return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro por favor ingrese nuevamente.']);
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
        $result = ContratacionModal::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado con exito.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar registro por favor ingrese nuevamente.']);
        }
    }
}
