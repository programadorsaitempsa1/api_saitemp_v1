<?php

namespace App\Http\Controllers;

use App\Models\SigContratoEmpleado;
use App\Models\SigEmpleados;
use App\Models\SigUsuarioContrato;
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

    public function usuariosporcontrato()
    {
        $id = auth()->id();
        $contrato = SigUsuarioContrato::where('usuario_id', '=', $id)
            ->select(
                "sig_usuarios_contratos.contrato_id",
            )
            ->get();
        if (count($contrato) == 0) {
            $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
                ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
                ->select(
                    "roles.nombre as rol",
                    "usuarios.nombres",
                    "usuarios.apellidos",
                    "usuarios.documento_identidad",
                    "usuarios.email",
                    "usuarios.id as id_user",
                    "estado_usuarios.nombre as estado",
                )
                ->get();
            return response()->json($users);

        } else {
            $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
                ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
                ->join("sig_usuarios_contratos", "sig_usuarios_contratos.usuario_id", "=", "usuarios.id")
                ->where('contrato_id', '=', $contrato[0]->contrato_id)
                ->select(
                    "roles.nombre as rol",
                    "usuarios.nombres",
                    "usuarios.apellidos",
                    "usuarios.documento_identidad",
                    "usuarios.email",
                    "usuarios.id as id_user",
                    "estado_usuarios.nombre as estado",
                )
                ->get();
            return response()->json($users);
        }

    }

    public function usuariosporcontrato2($id_contrato)
    {
        $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
            ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
            ->join("sig_usuarios_contratos", "sig_usuarios_contratos.usuario_id", "=", "usuarios.id")
            ->where('contrato_id', '=', $id_contrato)
            ->select(
                "roles.nombre as rol",
                "usuarios.nombres",
                "usuarios.apellidos",
                "usuarios.documento_identidad",
                "usuarios.email",
                "usuarios.id as id_user",
                "estado_usuarios.nombre as estado",
            )
            ->get();
        return response()->json($users);

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
        // $id = auth()->id();
        // $usuario_contrato = SigUsuarioContrato::join('usuarios', 'usuarios.id', '=', 'sig_usuarios_contratos.usuario_id')
        //     ->where('sig_usuarios_contratos.usuario_id', '=', $id)
        //     ->select(
        //         'usuarios.id'
        //     )
        //     ->get();
        // if (empty($usuario_contrato[0])) {
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
        // } else {
        //     $users = user::join("roles", "roles.id", "=", "usuarios.rol_id")
        //         ->join("estado_usuarios", "estado_usuarios.id", "=", "usuarios.estado_id")
        //         ->join("sig_usuarios_contratos", "sig_usuarios_contratos.usuario_id", "=", "usuarios.id")
        //         ->join("sig_contratos", "sig_contratos.id", "=", "sig_usuarios_contratos.contrato_id")
        //         ->where('usuarios.id', '=', $id)
        //         ->select(

        //             "usuarios.nombres",
        //             "usuarios.apellidos",
        //             "usuarios.documento_identidad",
        //             "usuarios.email",
        //             "usuarios.id as id_user",
        //             "roles.nombre as rol",
        //             "roles.id as id_rol",
        //             "estado_usuarios.nombre as estado",
        //             "estado_usuarios.id as id_estado",
        //             "sig_contratos.id as contrato_id",
        //             "sig_contratos.numero as contrato_numero",
        //         )
        //         ->get();
        //     return response()->json($users);
        // }

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
        $documento_identidad = $user->documento_identidad;

        try {
            if ($request->input('email')) {
                $user->nombres = $request->nombres;
                $user->apellidos = $request->apellidos;
                $user->documento_identidad = $request->documento_identidad;
                $user->email = $request->email;
                $user->estado_id = $request->estado_id;
                $user->rol_id = $request->rol_id;
                if ($request->password != null || $request->password != "") {
                    $user->password = app('hash')->make($request->password);
                }

                if ($request->contrato_id != null || $request->contrato_id != '') {
                    $usuario_contrato_id = SigUsuarioContrato::join('usuarios', 'usuarios.id', '=', 'sig_usuarios_contratos.usuario_id')
                        ->where('sig_usuarios_contratos.usuario_id', '=', $request->id_user)
                        ->select(
                            'sig_usuarios_contratos.id'
                        )
                        ->get();
                    $usuario_contrato = SigUsuarioContrato::find($usuario_contrato_id);
                    if (count($usuario_contrato) > 0) {
                        $usuario_contrato[0]->contrato_id = $request->contrato_id;
                        $usuario_contrato[0]->save();
                    } else {
                        $usuario_contrato = new SigUsuarioContrato;
                        $usuario_contrato->contrato_id = $request->contrato_id;
                        $usuario_contrato->usuario_id = $request->id_user;
                        $usuario_contrato->save();
                    }

                    if ($request->empleado == true) {
                     
                        $empleado = $this->getEmpleado($documento_identidad);
                        if (count($empleado) > 0) {
                            $empleado = SigEmpleados::find($empleado[0]->id);
                            $empleado->nombres = $request->nombres;
                            $empleado->apellidos = $request->apellidos;
                            $empleado->documento_identidad = $request->documento_identidad;
                            $empleado->estado_empleado_id = $request->estado_id;
                            $empleado->tipo_documento_identidad_id = 1;
                            $empleado->sig_cargo_id = 1;
                            $empleado->save();
                        } else {
                            $empleado = new SigEmpleados;
                            $empleado->nombres = $request->nombres;
                            $empleado->apellidos = $request->apellidos;
                            $empleado->documento_identidad = $request->documento_identidad;
                            $empleado->tipo_documento_identidad_id = 1;
                            $empleado->sig_cargo_id = 1;
                            $empleado->save();
                        }

                    
                        $contrato_empleado = $this->getContratoEmpleado($documento_identidad);
                        if (count($contrato_empleado) > 0) {
                            $contrato_empleado = SigContratoEmpleado::find($contrato_empleado[0]->id);
                            $contrato_empleado->contrato_id = $request->contrato_id;
                            $contrato_empleado->save();
                        } else {
                            $contrato_empleado = new SigContratoEmpleado;
                            $contrato_empleado->empleado_id = $empleado->id;
                            $contrato_empleado->contrato_id = $request->contrato_id;
                            $contrato_empleado->zona_id = 1;
                            $contrato_empleado->save();
                        }

                    }
                }

            }
            if ($user->save()) {
                return response()->json(['status' => 'success', 'message' => 'Usuario actualizado exitosamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }


    public function getEmpleado($documento_identidad){
        $empleado = SigEmpleados::where('documento_identidad', '=', $documento_identidad)
        ->select(
            'id'
        )
        ->get();
        return $empleado;
    }
    public function getContratoEmpleado($documento_identidad){
        $contrato_empleado = SigContratoEmpleado::join('sig_empleados', 'sig_empleados.id', '=', 'empleado_id')
        ->where('sig_empleados.documento_identidad', '=', $documento_identidad)
        ->select(
            'sig_contrato_empleados.id',
        )
        ->get();
        return $contrato_empleado;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\user  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
        $user = user::find($id);
        $documento_identidad = $user->documento_identidad;
        if ($user) {
            $user->delete();
        }
        $empleado = $this->getEmpleado($documento_identidad);
        if (count($empleado) > 0) {
            $empleado[0]->delete();
        }
        $contrato_empleado = $this->getContratoEmpleado($documento_identidad);
        if (count($contrato_empleado) > 0) {
            $contrato_empleado[0]->delete();
        }     
        return response()->json(['status' => 'success', 'message' => 'Usuario eliminado exitosamente']);
    }catch(\Exception $e){
        return response()->json(['status' => 'error', 'message' => 'Error al eliminar el usuario']);
    }

    }
}
