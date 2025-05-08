<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\AutenticacionGuard;
use App\Models\UsuariosInternosModel;

class UsuarioController extends Controller
{
    use AutenticacionGuard;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad, $tipo)
    {
        $lista = $this->listaUsuarios($cantidad, $tipo);
        return $lista;
    }

    public function index2()
    {
        $users = user::select(
            DB::raw("CONCAT(REPLACE(nombres, 'null', ''), ' ', REPLACE(apellidos, 'null', '')) AS nombre")

        )
            ->orderby('nombre')->get();
        return response()->json($users);
    }

    public function filtro($filtro, $cantidad)
    {

        $users = user::join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_usuarios.rol_id")
            ->join("usr_app_estados_usuario ", "usr_app_estados_usuario .id", "=", "usr_app_usuarios.estado_id")
            ->where('usr_app_usuarios.nombres', 'like', '%' . $filtro . '%')
            ->orWhere('usr_app_usuarios.apellidos', 'like', '%' . $filtro . '%')
            ->orWhere('usr_app_usuarios.email', 'like', '%' . $filtro . '%')
            ->select(
                "usr_app_roles.nombre as rol",
                "usr_app_usuarios.nombres",
                "usr_app_usuarios.apellidos",
                "usr_app_usuarios.usuario",
                "usr_app_usuarios.email",
                "usr_app_usuarios.id as usuario_id",
                "usr_app_estados_usuario .nombre as estado",
            )
            ->paginate($cantidad);
        return response()->json($users);
    }

    public function userslist()
    {
        $result = user::select(
            'id',
            DB::raw("CONCAT(nombres,' ',apellidos)  AS nombre"),
            'usuario AS email',
            'lider',
        )
            ->get();
        return response()->json($result);
    }
    public function userlogued()
    {
        $user = $this->getUserRelaciones();
        return $user;
    }

    public function userById($id)
    {
        $user = $this->getUserRelaciones($id);
        return $user;
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
        $user = UsuariosInternosModel::find($request->id_user);
        $login = user::find($request->id_user);
        $archivos = $request->files->all();

        if ($user->imagen_firma_1 != null && count($archivos) > 0) {
            $rutaArchivo1 = base_path('public') . $user->imagen_firma_1;
            if (file_exists($rutaArchivo1)) {
                unlink($rutaArchivo1);
            }
        }

        if ($user->imagen_firma_2 != null && count($archivos) > 1) {
            $rutaArchivo2 = base_path('public') . $user->imagen_firma_2;
            if (file_exists($rutaArchivo2)) {
                unlink($rutaArchivo2);
            }
        }

        $contador = 1;
        foreach ($archivos as $archivo) {
            if ($contador <= 2) {

                $nombreArchivoOriginal = ($archivo)->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                ($archivo)->move($carpetaDestino, $nuevoNombre);
                $user->{'imagen_firma_' . $contador} = ltrim($carpetaDestino, '.') . $nuevoNombre;
                $contador++;
            }
        }

        try {

            $login->estado_id = $request->estado_id !== "null" ? $request->estado_id : null;
            $login->rol_id = $request->rol_id !== "null" ? $request->rol_id : null;
            $login->save();

            $user->nombres = $request->nombres !== "null" ? $request->nombres : null;
            $user->apellidos = $request->apellidos !== "null" ? $request->apellidos : null;
            $user->documento_identidad = $request->documento_identidad !== "null" ? $request->documento_identidad : null;
            $user->correo = $request->usuario !== "null" ? $request->usuario : null;
            if ($request->contrasena_correo != '') {
                $user->contrasena_correo = Crypt::encryptString($request->contrasena_correo);
            }
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


    public function asignacionUsuarios()
    {
        $result = User::all();
        foreach ($result as $usuario) {
            if ($usuario->id > 3) {
                $nombres = explode(" ", $usuario->nombres);
                $nombre1 = isset($nombres[0]) ? $nombres[0] : '';
                $nombre2 = isset($nombres[1]) ? $nombres[1] : '';
                $apellido1 = isset($nombres[2]) ? $nombres[2] : '';
                $apellido2 = isset($nombres[3]) ? $nombres[3] : '';

                $nuevoUsuario = new UsuariosInternosModel();
                // $nuevoUsuario->rol_usuario_id = 1;
                $nuevoUsuario->usuario_id = $usuario->id;
                $nuevoUsuario->nombres = trim("$nombre1 $nombre2");
                $nuevoUsuario->apellidos = trim("$apellido1 $apellido2");
                $nuevoUsuario->documento_identidad = $usuario->documento_identidad ?? '';
                $nuevoUsuario->correo = $usuario->usuario;
                $nuevoUsuario->contrasena_correo = $usuario->contrasena_correo;
                $nuevoUsuario->imagen_firma_1 = $usuario->imagen_firma_1;
                $nuevoUsuario->imagen_firma_2 = $usuario->imagen_firma_2;
                $nuevoUsuario->rol_usuario_interno_id = 1;
                $nuevoUsuario->save();
            }
        }

        return response()->json("Usuarios insertados con éxito");
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
    public function updateVendedorId(Request $request, $id)
    {
        $result = user::find($id);
        $result->vendedor_id = $request->vendedor_id;
        $result->save();
    }
}
