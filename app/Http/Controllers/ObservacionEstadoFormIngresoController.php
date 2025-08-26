<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObservacionEstadoFormIngreso;
use App\Models\ObservacionEstadoModel;
use App\Models\UsuarioPermiso;
use App\Traits\Permisos;

class ObservacionEstadoFormIngresoController extends Controller
{
    use Permisos;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $permisos = $this->permisos();
        // $result = ObservacionEstadoFormIngreso::when(!in_array('45', $permisos), function ($query) {
        //     return $query->where('id','17');
        // })
        // ->select(
        //     'id',
        //     'nombre'
        // )
        // ->get();
        // return response()->json($result);
        // $permisos = $this->permisos();
        $result = ObservacionEstadoFormIngreso::select(
            'id',
            'nombre'
        )
            ->get();
        return response()->json($result);
    }

    public function index2()
    {
        // $permisos = $this->permisos();
        // $result = ObservacionEstadoFormIngreso::when(!in_array('45', $permisos), function ($query) {
        //     return $query->where('id','17');
        // })
        // ->select(
        //     'id',
        //     'nombre'
        // )
        // ->get();
        // return response()->json($result);
        // $permisos = $this->permisos();
        $result = ObservacionEstadoModel::select(
            'id',
            'nombre'
        )
            ->get();
        return response()->json($result);
    }

    public function validaPermiso()
    {
        $user = auth()->user();
        $responsable = UsuarioPermiso::where('usr_app_permisos_usuarios.usuario_id', '=', $user->id)
            ->select(
                'permiso_id'
            )
            ->get();
        $array = $responsable->toArray();
        $permisos = array_column($array, 'permiso_id');
        return $permisos;
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}