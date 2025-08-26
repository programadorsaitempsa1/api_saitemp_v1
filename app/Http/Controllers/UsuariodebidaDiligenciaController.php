<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\user;
use App\Models\UsuarioDebidaDiligenciaModel;
use Illuminate\Support\Facades\DB;

class UsuariodebidaDiligenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            $usuario = new user;
            $usuario->email = $request->email == '' ?  $request->nit : $request->email;
            $usuario->password = $request->password == '' ?  bcrypt($request->nit) : bcrypt($request->password);
            $usuario->estado_id = 1;
            $usuario->rol_id = 53;
            $usuario->tipo_usuario_id = 2;
            $usuario->save();

            $usuario_cliente = new UsuarioDebidaDiligenciaModel;
            $usuario_cliente->usuario_id = $usuario->id;
            $usuario_cliente->cliente_id = $request->cliente_id;
            $usuario_cliente->email = $request->email == '' ?  $request->nit : $request->email;
            $usuario_cliente->razon_social = $request->nombres;
            $usuario_cliente->nit = $request->nit;
            $usuario_cliente->nombre_contacto = $request->nombre_contacto;
            $usuario_cliente->telefono_contacto = $request->telefono_contacto;
            $usuario_cliente->cargo_contacto = $request->cargo_contacto;
            $usuario_cliente->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa.']);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro.']);
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
            DB::beginTransaction();
            $usuario = user::find($id);
            $usuario->email = $request->email == '' ?  $request->nit : $request->email;
            if ($request->password != '') {
                $usuario->password = bcrypt($request->password);
            }
            $usuario->estado_id = $request->estado_id;
            $usuario->rol_id = $request->rol_id;
            $usuario->save();

            $usuario_cliente = UsuarioDebidaDiligenciaModel::where('usuario_id', $id)->first();
            $usuario_cliente->usuario_id = $usuario->id;
            $usuario_cliente->email =  $request->email == '' ?  $request->nit : $request->email;
            $usuario_cliente->razon_social = $request->nombres;
            $usuario_cliente->nit = $request->nit;
            $usuario_cliente->nombre_contacto = $request->nombre_contacto;
            $usuario_cliente->telefono_contacto = $request->telefono_contacto;
            $usuario_cliente->cargo_contacto = $request->cargo_contacto;
            $usuario_cliente->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        } catch (\Exception $e) {
            DB::rollback();
            // return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro.']);
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
        //
    }
}
