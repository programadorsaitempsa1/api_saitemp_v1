<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\categoriaMenu;
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

    public function menubyRole()
    {
        $user = auth()->user();
        $users = menu::join("usr_app_menus_roles", "usr_app_menus_roles.menu_id", "=", "usr_app_menus.id")
            ->join('usr_app_roles', 'usr_app_roles.id', '=', 'usr_app_menus_roles.rol_id')
            ->join('usr_app_categorias_menu', 'usr_app_categorias_menu.id', '=', 'usr_app_menus.categoria_menu_id')
            ->where('usr_app_roles.id', '=', $user->rol_id)
            ->where('usr_app_menus.oculto', '=', 0)
            ->orderBy('usr_app_menus.posicion')
            ->select(

                "usr_app_roles.id as rol",
                "usr_app_menus.nombre",
                "usr_app_menus.id",
                "usr_app_menus.url",
                "usr_app_menus.icon",
                "usr_app_menus.urlExterna",
                "usr_app_menus.oculto",
                "usr_app_categorias_menu.nombre as categoria",
                "usr_app_menus.powerbi"
            )
            ->get();
        return response()->json($users);

        // $users = menu::leftJoin('usr_app_menus_roles', 'usr_app_menus_roles.menu_id', '=', 'usr_app_menus.id')
        //     ->leftJoin('usr_app_roles', 'usr_app_roles.id', '=', 'usr_app_menus_roles.rol_id')
        //     ->join('usr_app_categorias_menu', 'usr_app_categorias_menu.id', '=', 'usr_app_menus.categoria_menu_id')
        //     ->where('usr_app_menus.oculto', '=', 0)
        //     ->where('usr_app_roles.id', '=', $user->rol_id)
        //     ->orderBy('usr_app_menus.posicion')
        //     ->select(
        //         "usr_app_roles.id as rol",
        //         "usr_app_menus.nombre",
        //         "usr_app_menus.id",
        //         "usr_app_menus.url",
        //         "usr_app_menus.icon",
        //         "usr_app_menus.urlExterna",
        //         "usr_app_menus.oculto",
        //         "usr_app_categorias_menu.nombre as categoria"
        //     )
        //     ->get();

        // return response()->json($users);
        // $users = menu::leftJoin('usr_app_menus_roles', 'usr_app_menus_roles.menu_id', '=', 'usr_app_menus.id')
        //     ->leftJoin('usr_app_roles', 'usr_app_roles.id', '=', 'usr_app_menus_roles.rol_id')
        //     ->join('usr_app_categorias_menu', 'usr_app_categorias_menu.id', '=', 'usr_app_menus.categoria_menu_id')
        //     ->where('usr_app_menus.oculto', '=', 0)
        //     ->when($user->rol_id != 1, function ($query) use ($user) {
        //         return $query->where('usr_app_roles.id', '=', $user->rol_id);
        //     })
        //     ->orderBy('usr_app_menus.posicion')
        //     ->select(
        //         "usr_app_roles.id as rol",
        //         "usr_app_menus.nombre",
        //         "usr_app_menus.id",
        //         "usr_app_menus.url",
        //         "usr_app_menus.icon",
        //         "usr_app_menus.urlExterna",
        //         "usr_app_menus.oculto",
        //         "usr_app_categorias_menu.nombre as categoria"
        //     )
        //     ->get();

        // return response()->json($users);

    }


    public function categoriaMenu()
    {
        $menus = $this->menubyRole();
        $user = auth()->user();
        $result = categoriaMenu::select(
            'usr_app_categorias_menu.nombre as categoria',
            'usr_app_categorias_menu.icon',
        )
            ->orderBy('usr_app_categorias_menu.posicion')
            ->get();
        $menu = [];
        foreach ($result as $key => $item) {
            $menuCategoria = ['categoria' => $item->categoria, 'icon' => $item->icon, 'opciones' => []];
            foreach ($menus->original as $item2) {
                if ($item2->categoria == $item->categoria) {
                    $opcion = [
                        'nombre' => $item2->nombre,
                        'rol' => $item2->rol,
                        'url' => $item2->url,
                        'urlExterna' => $item2->urlExterna,
                        'oculto' => $item2->oculto,
                        'icon' => $item2->icon,
                        'powerbi' => $item2->powerbi,
                    ];
                    array_push($menuCategoria['opciones'], $opcion);
                }
            }
            if ($key > 0 && empty($menuCategoria['opciones'])) {
                unset($menu[$key]);
            } else {
                $menu[] = $menuCategoria;
            }
        }

        return response()->json($menu);
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
