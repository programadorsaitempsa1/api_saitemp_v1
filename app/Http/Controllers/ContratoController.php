<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use App\Models\RepresentanteLegal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $municipio_expedicion = RepresentanteLegal::join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_representantes_legales.municipio_expedicion_id')
        ->where('usr_app_representantes_legales.cliente_id', '=', $id)
        ->select(
            'mun.nombre as municipio_expedicion'
        )
        ->first();
        $result = Cliente::join('usr_app_representantes_legales as rl', 'rl.cliente_id', '=', 'usr_app_clientes.id')
            ->join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_clientes.municipio_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
            ->join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
            ->join('gen_tipide as doc', 'doc.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
            ->select(
                'razon_social',
                'nit',
                'rl.nombre as representante_legal',
                DB::raw("CONCAT(doc.tip_tip, ' ', rl.identificacion,' de ','$municipio_expedicion->municipio_expedicion') AS identificacion"), //'rl.identificacion as id_representante',
                'dep.nombre as departamento',
                'mun.nombre as ciudad',
                'direccion_empresa as direccion',
                'ac.codigo_actividad as actividad_ciu',
                'contacto_empresa as contacto',
                'celular_empresa as celular',
                'correo_empresa as correo',
            )
            ->where('usr_app_clientes.id', '=', $id)
            ->first();
        return response()->json($result);
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
