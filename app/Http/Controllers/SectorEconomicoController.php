<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SectorEconomicoModel;

class SectorEconomicoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = SectorEconomicoModel::select(
            'id',
            'nombre',
            'descripcion',
        )->paginate($cantidad);
        return response()->json($result);
    }


    public function lista()
    {
        $result = SectorEconomicoModel::select(
            'id',
            'nombre',
            'descripcion',
        )->get();
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
            $result = new SectorEconomicoModel;
            $result->nombre = $request->nombre;
            $result->descripcion = $request->descripcion;
            if ($result->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registrado insertado exitosamente.']);
            }
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
            $result = SectorEconomicoModel::find($id);
            $result->nombre = $request->nombre;
            $result->descripcion = $request->descripcion;
            if ($result->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registrado actualizado exitosamente.']);
            }
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
            $result = SectorEconomicoModel::find($id);
            $result->delete();
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erroor al eliminar registro.']);
        }
    }
}
