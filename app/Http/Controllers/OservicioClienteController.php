<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OservicioCliente;
use App\Models\OservicioCargo;
use App\Models\OservicioHojaVida;
use Illuminate\Support\Facades\DB;

class OservicioClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioCliente::select(
            'id',
            'nombre_razon_social',
            'nit_ndocumento',
            'nombre_solicitante',
            'celular_solicitante',
            'correo_solicitante'
        )
            ->get();
        return response()->json($result);
    }

    public function tabla($cantidad)
    {
        $result = OservicioCliente::join('usr_app_usuarios as user', 'user.id', 'usr_app_oservicio_clientes.usuario_id')
            ->select(
                'usr_app_oservicio_clientes.id',
                'usr_app_oservicio_clientes.nombre_razon_social',
                'usr_app_oservicio_clientes.nit_ndocumento',
                'usr_app_oservicio_clientes.nombre_solicitante',
                'usr_app_oservicio_clientes.celular_solicitante',
                'usr_app_oservicio_clientes.correo_solicitante',
                'user.nombres as responsable'
            )
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function getClienteCompleto($id)
    {

        $result = OservicioCliente::join('usr_app_usuarios as user', 'user.id', 'usr_app_oservicio_clientes.usuario_id')
            ->select(
                'usr_app_oservicio_clientes.id',
                'usr_app_oservicio_clientes.nit_ndocumento',
                'usr_app_oservicio_clientes.nombre_razon_social',
                'usr_app_oservicio_clientes.nombre_solicitante',
                'usr_app_oservicio_clientes.celular_solicitante',
                'usr_app_oservicio_clientes.correo_solicitante',
                'usr_app_oservicio_clientes.usuario_id',
                // DB::raw("CONCAT(user.nombres,'-',user.apellidos)  AS nombre_usuario"),
                'user.nombres',
                'user.apellidos',
            )
            ->where('usr_app_oservicio_clientes.id', $id)
            ->first();
        $cargos = OservicioCargo::join('usr_app_municipios as mun', 'mun.id', 'usr_app_oservicio_cargos.ciudad_id')
            ->join('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->select(
                'usr_app_oservicio_cargos.id',
                'usr_app_oservicio_cargos.nombre',
                'usr_app_oservicio_cargos.cantidad_vacantes',
                'usr_app_oservicio_cargos.salario',
                'usr_app_oservicio_cargos.fecha_inicio',
                'usr_app_oservicio_cargos.fecha_solicitud',
                'usr_app_oservicio_cargos.observaciones',
                'usr_app_oservicio_cargos.ciudad_id',
                'mun.nombre as municipio',
                'dep.nombre as departamento',
                'dep.id as departamento_id',
            )
            ->where('usr_app_oservicio_cargos.cliente_id', $id)
            ->get();
        $result['cargos'] = $cargos;
        // $hojas_vida = OservicioHojaVida::select(
        //     'id',
        //     'nombre_cargo',
        //     'cliente_id',
        //     'fecha_hora_envio',
        //     'ruta_documento',
        // )
        //     ->where('cliente_id', $id)
        //     ->groupBy('nombre_cargo')
        //     ->get();
        //     $result['hojas_vida'] = $hojas_vida;
        // use Illuminate\Support\Facades\DB;

        $hojas_vida = OservicioHojaVida::select(
            'nombre_cargo',
            'id',
            'cliente_id',
            'fecha_hora_envio',
            'ruta_documento'
        )
            ->where('cliente_id', $id)
            ->get();

        $result['hojas_vida'] = $hojas_vida->groupBy('nombre_cargo')->values()->map(function ($items, $index) {
            $cargo = $items->first()->nombre_cargo;

            return [
                'cargo' => $cargo,
                'detalles' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'cliente_id' => $item->cliente_id,
                        'fecha_hora_envio' => $item->fecha_hora_envio,
                        'ruta_documento' => $item->ruta_documento,
                    ];
                }),
                'posicion' => $index,
            ];
        });

        // $result['hojas_vida'] ahora contiene un array numérico donde cada elemento tiene 'cargo' (nombre del cargo),
        // 'detalles' (un array de objetos con los atributos de cada cargo) y 'posicion' (posición numérica en el array).


        // $result['hojas_vida'] ahora contiene un array numérico donde cada elemento es un array de objetos con los atributos de cada cargo.


        // $result['hojas_vida'] ahora contiene un array asociativo donde cada clave es el nombre del cargo,
        // y el valor es un array con 'cargo' (el nombre del cargo



        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // return auth()->user()->id;
        $result = new OservicioCliente;
        $result->nit_ndocumento = $request->nit_ndocumento;
        $result->nombre_razon_social = $request->nombre_razon_social;
        $result->nombre_solicitante = $request->nombre_solicitante;
        $result->celular_solicitante = $request->celular_solicitante;
        $result->correo_solicitante = $request->correo_solicitante;
        $result->usuario_id = $request->usuario_id == '' ? auth()->user()->id : $request->usuario_id;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al guardar registro']);
        }
    }

    public function getIdCliente($id)
    {
        $cliente_id = OservicioCliente::select(
            'id'
        )
            ->where('nit_ndocumento', '=', $id)
            ->first();
        return $cliente_id->id;
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
        $result = OservicioCliente::find($id);
        $result->nit_ndocumento = $request->nit_ndocumento;
        $result->nombre_razon_social = $request->nombre_razon_social;
        $result->nombre_solicitante = $request->nombre_solicitante;
        $result->celular_solicitante = $request->celular_solicitante;
        $result->correo_solicitant = $request->correo_solicitant;
        $result->usuario_id = $request->usuario_id;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al actualizar registro']);
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
        $result = OservicioCliente::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
