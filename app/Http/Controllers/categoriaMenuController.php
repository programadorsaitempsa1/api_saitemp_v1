<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\categoriaMenu;

class categoriaMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = categoriaMenu::select(
            'id',
            'nombre',
            'icon',
            'posicion',
            'oculto',

        )
            ->orderby('posicion','DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function lista()
    {
        $result = categoriaMenu::select(
            'id',
            'nombre'
        )
            // ->orderby('posicion')
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
        $result = new categoriaMenu;
        $result->nombre = $request->nombre;
        $result->icon = $request->icono;
        $result->posicion = $request->posicion;
        $result->oculto = $request->oculto;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Regitro guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar registro']);
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
        $result = categoriaMenu::find($id);
        $result->nombre = $request->nombre;
        $result->icon = $request->icono;
        $result->posicion = $request->posicion;
        $result->oculto = $request->oculto;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Regitro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = categoriaMenu::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
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
            $result = categoriaMenu::find($id);
            if ($result->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Regitro borrado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al borrar registro']);
            }
        } catch (\Exception $e) {
            // return $e;
            return response()->json(['status' => 'error', 'message' => 'Hay una relación entre un usuario y este menú, por favor primero elimine la relación']);
        }
    }
}
