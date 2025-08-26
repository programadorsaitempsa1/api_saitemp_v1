<?php

namespace App\Http\Controllers;

use App\Models\PermisoRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SigPermisoRolController extends Controller
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
            $roles = PermisoRol::join("usr_app_permisos as per", "per.id", "=", "usr_app_permisos_roles.permiso_id")
                ->join("usr_app_roles as rol", "rol.id", "=", "usr_app_permisos_roles.rol_id")
                ->select(
                    "usr_app_permisos_roles.id",
                    "rol.nombre as rol",
                    "per.nombre as permiso",
                )
                ->orderby('usr_app_permisos_roles.id', 'DESC')
                ->paginate($cantidad);
        } else {
            $roles = PermisoRol::join("usr_app_permisos as per", "per.id", "=", "usr_app_permisos_roles.permiso_id")
                ->join("usr_app_roles", "usr_app_roles.id", "=", "usr_app_permisos_roles.rol_id")
                ->where("usr_app_menus_roles.rol_id", "!=", 1)
                ->select(

                    "usr_app_permisos_roles.id",
                    "usr_app_roles.nombre as rol",
                    "usr_app_permisos.nombre as menu",
                )
                ->orderby('usr_app_permisos_roles.id', 'DESC')
                ->paginate($cantidad);
        }
        return response()->json($roles);
    }

    public function filtrorol($id = null, $cantidad)
    {
        $user = auth()->user();
        if ($user->rol_id == 1) {
            $roles = PermisoRol::join("usr_app_permisos as per", "per.id", "=", "usr_app_permisos_roles.permiso_id")
                ->join("usr_app_roles as rol", "rol.id", "=", "usr_app_permisos_roles.rol_id")
                ->when($id != null, function ($query) use ($id) {
                    return $query->where('rol.id', '=', $id);
                })
                ->select(
                    "usr_app_permisos_roles.id",
                    "rol.nombre as rol",
                    "per.nombre as permiso",
                )
                ->orderby('usr_app_permisos_roles.id', 'DESC')
                ->paginate($cantidad);
        } else {
            $roles = PermisoRol::join("usr_app_permisos as per", "per.id", "=", "usr_app_permisos_roles.permiso_id")
                ->join("usr_app_roles as rol", "usr_app_roles.id", "=", "usr_app_permisos_roles.rol_id")
                ->when($id != null, function ($query) use ($id) {
                    return $query->where('usr_app_roles.id', '=', $id);
                })
                ->where("usr_app_menus_roles.rol_id", "!=", 1)
                ->select(

                    "usr_app_permisos_roles.id",
                    "rol.nombre as rol",
                    "usr_app_permisos.nombre as menu",
                )
                ->orderby('usr_app_permisos_roles.id', 'DESC')
                ->paginate($cantidad);
        }
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
            $permisos = $request->all();
            foreach ($permisos[0] as  $rol) {
                foreach ($permisos[1] as  $permiso) {
                    $permisos_roles = new PermisoRol;
                    $permisos_roles->rol_id = $rol['id'];
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
        $roles_permisos = PermisoRol::find($id);
        $roles_permisos->rol_id = $request->rol_id;
        $roles_permisos->permiso_id = $request->permiso_id;
        if ($roles_permisos->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = PermisoRol::find($request->id[$i]);
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
        $roles_permisos = PermisoRol::find($id);
        if ($roles_permisos->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
