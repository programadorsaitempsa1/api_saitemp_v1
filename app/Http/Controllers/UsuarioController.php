<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {

        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
            ->join("usr_app_estados_usuario ", "usr_app_estados_usuario .id", "=", "usr_app_usuarios.estado_id")
            ->select(
                "usr_app_roles.nombre as rol",
                "usr_app_usuarios.nombres",
                "usr_app_usuarios.apellidos",
                "usr_app_usuarios.email",
                "usr_app_usuarios.id as id_user",
                "usr_app_estados_usuario .nombre as estado",
            )
            ->paginate($cantidad);
        return response()->json($users);
    }

    public function filtro($filtro, $cantidad)
    {
        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
            ->join("usr_app_estados_usuario ", "usr_app_estados_usuario .id", "=", "usr_app_usuarios.estado_id")
            ->where('usr_app_usuarios.nombres','like', '%'.$filtro.'%')
            ->orWhere('usr_app_usuarios.apellidos','like', '%'.$filtro.'%')
            ->orWhere('usr_app_usuarios.email','like', '%'.$filtro.'%')
            ->select(
                "usr_app_roles.nombre as rol",
                "usr_app_usuarios.nombres",
                "usr_app_usuarios.apellidos",
                "usr_app_usuarios.email",
                "usr_app_usuarios.id as id_user",
                "usr_app_estados_usuario .nombre as estado",
            )
            ->paginate($cantidad);
        return response()->json($users);
    }

    public function userslist()
    {
        $result = user::select(
            'email'
        )
            ->get();
        return response()->json($result);
    }


    public function userlogued()
    {
        $id = auth()->id();
        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
            ->join("usr_app_estados_usuario", "usr_app_estados_usuario.id", "=", "usr_app_usuarios.estado_id")
            ->where('usr_app_usuarios.id', '=', $id)
            ->select(
                "usr_app_roles.nombre as rol",
                "usr_app_usuarios.nombres",
                "usr_app_usuarios.apellidos",
                "usr_app_usuarios.documento_identidad",
                "usr_app_usuarios.email",
                "usr_app_roles.id",
                'usr_app_usuarios.id as usuario_id',
                "usr_app_estados_usuario.nombre as estado",
            )
            ->get();
        if (count($users) == 0) {
            $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
                ->join("usr_app_estados_usuario", "usr_app_estados_usuario.id", "=", "usr_app_usuarios.estado_id")
                ->where('usr_app_usuarios.id', '=', $id)
                ->select(
                    "usr_app_usuarios.nombres",
                    "usr_app_usuarios.apellidos",
                    "usr_app_usuarios.email",
                    "usr_app_usuarios.id as id_user",
                    "usr_app_roles.nombre as rol",
                    "usr_app_roles.id",
                    'usr_app_usuarios.id as usuario_id',
                    "estado_usuarios.nombre as estado",
                    "usr_app_estados_usuario.id as id_estado",
                )
                ->get();
            return response()->json($users);
        } else {
            return response()->json($users);
        }
    }

    public function userById($id)
    {

        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
            ->join("usr_app_estados_usuario", "usr_app_estados_usuario.id", "=", "usr_app_usuarios.estado_id")
            ->where('usr_app_usuarios.id', '=', $id)
            ->select(
                "usr_app_usuarios.nombres",
                "usr_app_usuarios.apellidos",
                "usr_app_usuarios.documento_identidad",
                "usr_app_usuarios.email",
                "usr_app_usuarios.id as id_user",
                "usr_app_roles.nombre as rol",
                "usr_app_roles.id as id_rol",
                "usr_app_estados_usuario.nombre as estado",
                "usr_app_estados_usuario.id as id_estado",
            )
            ->get();
        return response()->json($users);
    }

    public function infoLogin($id)
    {
        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
            ->where('usr_app_usuarios.id', '=', $id)
            ->select(

                "usr_app_roles.nombre as rol",
                "usr_app_usuarios.nombres as nombres",
                "usr_app_usuarios.apellidos as apellidos",
                "usr_app_roles.id",
            )
            ->get();
        return response()->json($users);
    }

    public function permissions($id)
    {
        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.id_rol")
            ->join("usr_app_permisos_roles", "usr_app_permisos_roles.id_rol", "=", "usr_app_roles.id")
            ->join("usr_app_permisos", "usr_app_permisos.id", "=", "usr_app_permisos_roles.id_permiso")
            ->where('usuarios.id', '=', $id)
            ->select(

                "usr_app_permisos.nombre as permiso",
                "usr_app_permisos.id as id",
            )
            ->get();
        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function show(user $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(user $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $user = user::find($request->id_user);

        try {
            $user->nombres = $request->nombres;
            $user->apellidos = $request->apellidos;
            $user->documento_identidad = $request->documento_identidad;
            $user->email = $request->email;
            $user->estado_id = $request->estado_id;
            $user->rol_id = $request->rol_id;
            if ($request->password != null || $request->password != "") {
                $user->password = app('hash')->make($request->password);
            }
            if ($user->save()) {
                return response()->json(['status' => 'success', 'message' => 'Usuario actualizado exitosamente']);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = user::find($id);
            if ($user) {
                $user->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Usuario eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el usuario']);
        }
    }
}
