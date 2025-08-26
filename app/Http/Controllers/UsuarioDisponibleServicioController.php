<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioDisponibleServicioModel;
use App\Models\AsignacionServicioModel;
use Illuminate\Support\Facades\DB;

class UsuarioDisponibleServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = UsuarioDisponibleServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_usuarios_disponibles_servicio.usuario_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->select(
                'usr_app_usuarios_disponibles_servicio.usuario_id',
                'usr_app_usuarios_disponibles_servicio.rol_usuario_interno_id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
            )->get();

        $radios = AsignacionServicioModel::select(
            'id',
            'nombre',
            'checked',
            'tipo'
        )->get();

        $result['usuarios'] = $usuarios;
        $result['radios'] = $radios;
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        UsuarioDisponibleServicioModel::select()->delete();
        $usuarios = '';
        foreach ($request['usuarios'] as $item) {
            try {
                $result = new  UsuarioDisponibleServicioModel;
                $result->usuario_id = $item['usuario_id'];
                $result->rol_usuario_interno_id = $item['rol_usuario_interno_id'];
                $result->save();
            } catch (\Exception $e) {
                $usuarios .= ' ' . $item['nombres'];
            }
        }
        foreach ($request['radios'] as $item) {
            $result = AsignacionServicioModel::find($item['id']);
            $result->checked = $item['checked'];
            $result->save();
        }
        if ($usuarios != '') {
            $mensaje = 'los usuarios' . $usuarios . ', no se puedieron guardar.';
            return response()->json(['status' => 'error', 'message' => $mensaje]);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa.']);
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
    public function update(Request $request) {}

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
