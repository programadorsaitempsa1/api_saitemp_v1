<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Menu::all();
        return response()->json($result);
    }

    public function menubyRole($id)
    {
        $users = menu::join("menus_roles", "menus_roles.menu_id", "=", "menus.id")
            ->join('roles', 'roles.id', '=', 'menus_roles.rol_id')
            ->where('roles.id', '=', $id)
            ->where('menus.oculto', '=', 0)
            ->orderBy('menus.posicion')
            ->select(

                "roles.id as rol",
                "menus.nombre",
                "menus.url",
                "menus.icon",
                "menus.urlExterna",
                "menus.oculto",
            )
            ->get();
        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $datosmenu = new Menu;
        $datosmenu->nombre = $request->nombre;
        $datosmenu->url = $request->url;
        $datosmenu->icon = $request->icon;
        $datosmenu->urlExterna = $request->urlExterna;
        $datosmenu->posicion = $request->posicion;
        $datosmenu->oculto = $request->oculto;
        if ($datosmenu->save()) {
            return response()->json(['status' => 'success', 'message' => 'Menú insertado exitosamente']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al insertar registro, por favor intente nuevamente']);
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
        $datosmenu = Menu::find($id);

        $datosmenu->nombre = $request->nombre;
        $datosmenu->url = $request->url;
        $datosmenu->icon = $request->icon;
        $datosmenu->urlExterna = $request->urlExterna;
        $datosmenu->posicion = $request->posicion;
        $datosmenu->oculto = $request->oculto;
        if ($datosmenu->save()) {
            return response()->json(['status' => 'success', 'message' => 'Menú actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al actualizar registro, por favor intente nuevamente']);
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
        $datosmenu = Menu::find($id);
            if ($datosmenu->delete()) {
            return response()->json("registro borrado Con Exito");
        } else {
            return response()->json("Error al borrar registro");
        }
    }
}
