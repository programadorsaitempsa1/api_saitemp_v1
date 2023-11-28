<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuariosMenus;
use Illuminate\Support\Facades\DB;

class UsuariosMenusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = UsuariosMenus::join('usr_app_usuarios as user', 'user.id', 'usr_app_usuarios_menus.usuario_id')
            ->join('usr_app_menus', 'usr_app_menus.id', 'usr_app_usuarios_menus.menu_id')
            ->select(
                'usr_app_usuarios_menus.id',
                'user.nombres',
                'user.apellidos',
                'usr_app_menus.nombre as menu',
            )
            ->paginate($cantidad);
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
            DB::beginTransaction();
            $menus = $request->all();
            foreach ($menus[0] as  $usuario) {
                foreach ($menus[1] as  $menu) {
                    $menus_ususarios = new UsuariosMenus;
                    $menus_ususarios->usuario_id = $usuario['id'];
                    $menus_ususarios->menu_id = $menu['id'];
                    $menus_ususarios->save();
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

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = UsuariosMenus::find($request->id[$i]);
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
        $result = UsuariosMenus::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
