<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $user = auth()->user();
        if ($user->rol_id == 1) {
            $result = Rol::select(
                'usr_app_roles.id',
                'usr_app_roles.nombre',
                'usr_app_roles.descripcion',
            )
                ->orderby('usr_app_roles.id', 'DESC')
                ->paginate($cantidad);
            return response()->json($result);
        } else {
            $result = Rol::where('usr_app_roles.id', '!=', 1)
                ->select(
                    'usr_app_roles.id',
                    'usr_app_roles.nombre',
                    'usr_app_roles.descripcion',
                )
                ->orderby('usr_app_roles.id', 'DESC')
                ->paginate($cantidad);
            return response()->json($result);
        }
    }

    public function lista()
    {
        $user = auth()->user();
        if ($user->rol_id == 1) {
            $result = Rol::select(
                'usr_app_roles.id',
                'usr_app_roles.nombre',
                'usr_app_roles.descripcion',
            )
                ->orderby('usr_app_roles.id', 'DESC')
                ->get();
            return response()->json($result);
        } else {
            $result = Rol::where('usr_app_roles.id', '!=', 1)
                ->select(
                    'usr_app_roles.id',
                    'usr_app_roles.nombre',
                    'usr_app_roles.descripcion',
                )
                ->orderby('usr_app_roles.id', 'DESC')
                ->get();
            return response()->json($result);
        }
    }

    public function rolesPermisos()
    {
        $roles = Rol::join("usr_app_permisos_roles", "usr_app_permisos_roles.rol_id", "=", "usr_app_roles.id")
            ->join("usr_app_permisos", "usr_app_permisos.id", "=", "usr_app_permisos_roles.permiso_id")
            ->select(

                "usr_app_roles.nombre as rol",
            )
            ->distinct()
            ->get();
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
            $rol = new Rol;
            $rol->nombre = $request->nombre;
            $rol->descripcion = $request->descripcion;
            if ($rol->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Este rol ya se encuentra registrado']);
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
                $result = Rol::find($valor);

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
        try {
            $rol = Rol::find($id);
            if ($request->input('nombre')) {
                $rol->nombre = $request->nombre;
                $rol->descripcion = $request->descripcion;
            }
            if ($rol->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Este rol ya se encuentra registrado']);
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
        $rol = Rol::find($id);
        if ($rol->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = Rol::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
