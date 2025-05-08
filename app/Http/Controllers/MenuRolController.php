<?php

namespace App\Http\Controllers;

use App\Models\MenuRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $roles = MenuRol::join("usr_app_menus", "usr_app_menus.id", "=", "usr_app_menus_roles.menu_id")
                ->join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_menus_roles.rol_id")
                ->select(
                    "usr_app_roles.nombre as rol",
                )
                ->distinct()
                // ->orderby('roles.id', 'ASC')
                ->get();
        } else {
            $roles = MenuRol::join("usr_app_menus", "usr_app_menus.id", "=", "usr_app_menus_roles.menu_id")
                ->join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_menus_roles.rol_id")
                ->where("usr_app_menus_roles.rol_id", "!=", 1)
                ->select(
                    "usr_app_roles.nombre as rol",
                )
                ->distinct()
                // ->orderby('roles.id', 'desc')
                ->get();
        }
        return response()->json($roles);
    }

    public function rolesMenus($cantidad)
    {
        $user = auth()->user();
        if ($user->rol_id == 1) {
            $roles = MenuRol::join("usr_app_menus", "usr_app_menus.id", "=", "usr_app_menus_roles.menu_id")
                ->join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_menus_roles.rol_id")
                ->select(

                    "usr_app_menus_roles.id",
                    "usr_app_roles.nombre as rol",
                    "usr_app_menus.nombre as menu",
                )
                ->orderby('usr_app_menus_roles.id', 'DESC')
                ->paginate($cantidad);
        } else {
            $roles = MenuRol::join("usr_app_menus", "usr_app_menus.id", "=", "usr_app_menus_roles.menu_id")
                ->join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_menus_roles.rol_id")
                ->where("usr_app_menus_roles.rol_id", "!=", 1)
                ->select(

                    "usr_app_menus_roles.id",
                    "usr_app_roles.nombre as rol",
                    "usr_app_menus.nombre as menu",
                )
                ->orderby('usr_app_menus_roles.id', 'DESC')
                ->paginate($cantidad);
        }
        return response()->json($roles);
    }

    public function rolesMenusbyid($id)
    {
        $roles = MenuRol::join("usr_app_menus", "usr_app_menus.id", "=", "usr_app_menus_roles.menu_id")
            ->join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_menus_roles.rol_id")
            ->where('usr_app_menus_roles.rol_id', '=', $id)
            ->select(
                "usr_app_menus_roles.id",
                "usr_app_roles.nombre as rol",
                "usr_app_menus.nombre as menu",

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
        try {
            DB::beginTransaction();
            $menus = $request->all();
            foreach ($menus[0] as  $rol) {
                foreach ($menus[1] as  $menu) {
                    $menu_roles = new MenuRol;
                    $menu_roles->rol_id = $rol['id'];
                    $menu_roles->menu_id = $menu['id'];
                    $menu_roles->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
