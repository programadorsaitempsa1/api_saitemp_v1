<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstadoCandidatoServicioModel;

class EstadoCandidatoServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = EstadoCandidatoServicioModel::select('id', 'nombre')->get();
        return response()->json($result);
    }

    public function tabla()
    {
        $result = EstadoCandidatoServicioModel::select('id', 'nombre', 'descripcion', 'clave')->orderby('id')->paginate(10);
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
            $result = new EstadoCandidatoServicioModel;
            $result->nombre = $request->nombre;
            $result->descripcion = $request->descripcion;
            $result->clave = $request->clave;
            $result->save();
            return response()->json(['status' => 'success', 'message' => 'Registro insertado exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al insertar el registro.']);
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
            $result = EstadoCandidatoServicioModel::find($id);
            $result->nombre = $request->nombre;
            $result->descripcion = $request->descripcion;
            $result->clave = $request->clave;
            $result->save();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente.']);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro.']);
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
        try {
            $result = EstadoCandidatoServicioModel::find($id);
            if ($result->clave != '' && $result->clave != null) {
                return response()->json(['status' => 'error', 'message' => 'Este registro no permite ser borrado, por favor comuniquese con el administrador del sistema.']);
            } else {
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente.']);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro.']);
        }
    }
}
