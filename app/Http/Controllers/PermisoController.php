<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = Permiso::select(
            'id',
            'nombre',
            'descripcion',
        )
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function byId()
    {
        $user = auth()->user();
        $result = Permiso::leftJoin('usr_app_permisos_roles as pr', 'pr.permiso_id', '=', 'usr_app_permisos.id')
            ->leftJoin('usr_app_permisos_usuarios as pu', 'pu.permiso_id', '=', 'usr_app_permisos.id')
            ->where(function ($query) use ($user) {
                $query->where('pr.rol_id', '=', $user['rol_id'])
                    ->orWhere('pu.usuario_id', '=', $user['id']);
            })
            ->select(
                'usr_app_permisos.alias'
            )
            ->get();

        return response()->json($result);
    }
    public function permisoslista()
    {
        $result = Permiso::select(
            'id',
            'nombre',
        )
            ->get();
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
            $permiso = new Permiso;
            $permiso->nombre = $request->nombre;
            $permiso->descripcion = $request->descripcion;
            $permiso->oculto = 0;
            if ($permiso->save()) {
                Permiso::where('id', $permiso->id)
                    ->update([
                        'alias' => 'P' . $permiso->id,
                    ]);
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Este permiso ya se encuentra registrado']);
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
        try {
            $permiso = Permiso::find($id);
            $permiso->nombre = $request->nombre;
            $permiso->descripcion = $request->descripcion;
            $permiso->oculto = 0;
            if ($permiso->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Este permiso ya se encuentra registrado']);
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
        $permiso = Permiso::find($id);
        if ($permiso->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
