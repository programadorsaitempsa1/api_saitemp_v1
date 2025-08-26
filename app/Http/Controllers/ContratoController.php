<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use App\Models\RepresentanteLegal;
use App\Models\VersionContratoDD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, $asJson = true)
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
            ->join('gen_tipide as doc', 'doc.cod_tip', '=', 'rl.tipo_identificacion_id')
            ->select(
                'usr_app_clientes.id',
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
                'correo_facturacion_electronica',
                'aiu_negociado',
                'plazo_pago',
                DB::raw('COALESCE(CONVERT(VARCHAR, usr_app_clientes.numero_radicado), CONVERT(VARCHAR, usr_app_clientes.id)) AS radicado'),
            )
            ->where('usr_app_clientes.id', '=', $id)
            ->first();

        $currentDate = now(); // Obtener la fecha actual

        // Establecer la versión del formulario basada en la fecha actual
        $version = $currentDate->isAfter('2024-08-04') ? 2 : 1;

        // Realizar la consulta con la versión seleccionada
        $versionamiento = VersionContratoDD::where('version_contrato', $version)
            ->select()
            ->get();
        $result->codigo_documento = $versionamiento[0]['descripcion'];
        $result->fecha_documento = $versionamiento[1]['descripcion'];
        $result->version_documento = $versionamiento[2]['descripcion'];
        if (!$asJson) {
            return $result;
        }
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
