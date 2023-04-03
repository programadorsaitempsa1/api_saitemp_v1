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

        $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
            ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
            ->select(
                "roles.nombre as rol",
                "usuarios.nombres",
                "usuarios.apellidos",
                "usuarios.email",
                "usuarios.id as id_user",
                "estado_usuarios.nombre as estado",
            )
            ->paginate($cantidad);
        return response()->json($users);
    }

    public function userslist(){
        $result = user::select(
            'email'
        )
        ->get();
        return response()->json($result);
    }


    public function userlogued()
    {
        $id = auth()->id();
        $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
            ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
            ->where('usuarios.id', '=', $id)
            ->select(
                "roles.nombre as rol",
                "usuarios.nombres",
                "usuarios.apellidos",
                "usuarios.documento_identidad",
                "usuarios.email",
                "roles.id",
                'usuarios.id as usuario_id',
                "estado_usuarios.nombre as estado",
            )
            ->get();
        if (count($users) == 0) {
            $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
                ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
                ->where('usuarios.id', '=', $id)
                ->select(
                    "usuarios.nombres",
                    "usuarios.apellidos",
                    "usuarios.email",
                    "usuarios.id as id_user",
                    "roles.nombre as rol",
                    "roles.id",
                    'usuarios.id as usuario_id',
                    "estado_usuarios.nombre as estado",
                    "estado_usuarios.id as id_estado",
                )
                ->get();
            return response()->json($users);
        } else {
            return response()->json($users);
        }
    }

    public function userById($id)
    {

        $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
            ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
            ->where('usuarios.id', '=', $id)
            ->select(
                "usuarios.nombres",
                "usuarios.apellidos",
                "usuarios.documento_identidad",
                "usuarios.email",
                "usuarios.id as id_user",
                "roles.nombre as rol",
                "roles.id as id_rol",
                "estado_usuarios.nombre as estado",
                "estado_usuarios.id as id_estado",
            )
            ->get();
        return response()->json($users);
    }

    public function infoLogin($id)
    {
        $users = user::join("roles", "roles.id", "=", "users.rol_id")
            ->where('usuarios.id', '=', $id)
            ->select(

                "roles.nombre as rol",
                "usuarios.nombres as nombres",
                "usuarios.apellidos as apellidos",
                "roles.id",
            )
            ->get();
        return response()->json($users);
    }

    public function permissions($id)
    {
        $users = user::join("roles", "roles.id", "=", "usuarios.id_rol")
            ->join("roles_permisos", "roles_permisos.id_rol", "=", "roles.id")
            ->join("permisos", "permisos.id", "=", "roles_permisos.id_permiso")
            ->where('usuarios.id', '=', $id)
            ->select(

                "permisos.nombre as permiso",
                "permisos.id as id",
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
