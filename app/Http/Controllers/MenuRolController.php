<?php

namespace App\Http\Controllers;

use App\Models\MenuRol;
use Illuminate\Http\Request;

class MenuRolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = MenuRol::all();
        return response()->json($result);
    }

    public function rolesConMenu()
    {
        $user = auth()->user();
        if ($user->rol_id == 1) {
            $roles = MenuRol::join("menus", "menus.id", "=", "menus_roles.menu_id")
                ->join("roles", "roles.id", "=", "menus_roles.rol_id")
                ->select(
                    "roles.nombre as rol",
                )
                ->distinct()
                ->orderby('roles.id', 'asc')
                ->get();
        } else {
            $roles = MenuRol::join("menus", "menus.id", "=", "menus_roles.menu_id")
                ->join("roles", "roles.id", "=", "menus_roles.rol_id")
                ->where("menus_roles.rol_id", "!=", 1)
                ->select(
                    "roles.nombre as rol",
                )
                ->distinct()
                ->orderby('roles.id', 'asc')
                ->get();

        }
        return response()->json($roles);
    }

    public function rolesMenus($cantidad)
    {
        $user = auth()->user();
        if ($user->rol_id == 1) {
            $roles = MenuRol::join("menus", "menus.id", "=", "menus_roles.menu_id")
                ->join("roles", "roles.id", "=", "menus_roles.rol_id")
                ->select(

                    "menus_roles.id",
                    "roles.nombre as rol",
                    "menus.nombre as menu",
                )
                ->orderby('menus_roles.id', 'DESC')
                ->paginate($cantidad);
        } else {
            $roles = MenuRol::join("menus", "menus.id", "=", "menus_roles.menu_id")
                ->join("roles", "roles.id", "=", "menus_roles.rol_id")
                ->where("menus_roles.rol_id", "!=", 1)
                ->select(

                    "menus_roles.id",
                    "roles.nombre as rol",
                    "menus.nombre as menu",
                )
                ->orderby('menus_roles.id', 'DESC')
                ->paginate($cantidad);
        }
        return response()->json($roles);
    }

    public function rolesMenusbyid($id)
    {
        $roles = MenuRol::join("menus", "menus.id", "=", "menus_roles.menu_id")
            ->join("roles", "roles.id", "=", "menus_roles.rol_id")
            ->where('menus_roles.rol_id', '=', $id)
            ->select(
                "menus_roles.id",
                "roles.nombre as rol",
                "menus.nombre as menu",

            )
            // ->get();
            ->paginate(100);
        return response()->json($roles);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $menu_roles = new MenuRol;
        $menu_roles->rol_id = $request->rol_id;
        $menu_roles->menu_id = $request->menu_id;
        if ($menu_roles->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
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

    public function actualizacionmasiva(Request $request)
    {
        try {
            foreach ($request->id as $valor) {
                $result = MenuRol::find($valor);

                foreach ($request->campos as $clave => $valor) {
                    if ($valor != "") {
                        $result->$clave = $valor;
                    }
                }
                $result->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros actualizados exitosamente']);

        } catch (\Exception$e) {
            return response()->json($e);
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
        }
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
        $menu_roles = MenuRol::find($id);
        
       
            $menu_roles->rol_id = $request->rol_id;
            $menu_roles->menu_id = $request->menu_id;
            // return $menu_roles;
        
        if ($menu_roles->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
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
        $menu_roles = MenuRol::find($id);
        if ($menu_roles->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = MenuRol::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
