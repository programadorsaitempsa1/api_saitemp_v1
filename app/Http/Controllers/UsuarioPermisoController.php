<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioPermiso;
use Illuminate\Support\Facades\DB;

class UsuarioPermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = UsuarioPermiso::join('usr_app_usuarios as user', 'user.id', 'usr_app_permisos_usuarios.usuario_id')
            ->join('usr_app_permisos as per', 'per.id', 'usr_app_permisos_usuarios.permiso_id')
            ->select(
                'usr_app_permisos_usuarios.id',
                'user.nombres',
                'user.apellidos',
                'per.nombre as permiso',
            )
            ->paginate($cantidad);
        return response()->json($result);
    }


    public function filtroporusuario($id, $cantidad)
    {
        $result = UsuarioPermiso::join('usr_app_usuarios as user', 'user.id', 'usr_app_permisos_usuarios.usuario_id')
            ->join('usr_app_permisos as per', 'per.id', 'usr_app_permisos_usuarios.permiso_id')
            // ->when($id != null, function ($query) use ($id) {
            //     return $query->where('rol.id', '=', $id);
            // })
            ->where('user.id', '=', $id)
            ->select(
                'usr_app_permisos_usuarios.id',
                'user.nombres',
                'user.apellidos',
                'per.nombre as permiso',
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
            $permisos = $request->all();
            foreach ($permisos[0] as  $usuario) {
                foreach ($permisos[1] as  $permiso) {
                    $permisos_roles = new UsuarioPermiso;
                    $permisos_roles->usuario_id = $usuario['id'];
                    $permisos_roles->permiso_id = $permiso['id'];
                    $permisos_roles->save();
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
        // $result = UsuarioPermiso::find($id);
        // $result->usuario_id = $request->usuario_id;
        // $result->permiso_id = $request->permiso_id;
        // if ($result->save()) {
        //     return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        // } else {
        //     return response()->json(['status' => 'success', 'message' => 'Error al guardar registro']);
        // }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = UsuarioPermiso::find($request->id[$i]);
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
        $result = UsuarioPermiso::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
