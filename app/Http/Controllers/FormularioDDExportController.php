<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormularioDDExport;
use App\Exports\FormularioDDExport2;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Return_;

class FormularioDDExportController extends Controller
{
    public function export($id)
    {
        // $export = new MultipleSheetsExport($user);
        // return Excel::download(new FormularioDDExport(), 'archivo_excel.xlsx');
        $export = new FormularioDDExport($id);

        return Excel::download($export, 'archivo_excel.xlsx');
    }
    public function export2($cadena)
    {

        $consulta = base64_decode($cadena);
        $valores = explode("/", $consulta);
        $campo = $valores[0];
        $operador = $valores[1];
        $valor = $valores[2];
        $valor2 = $valores[3];


        if ($operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $valor . '%';
        } else if ($operador == 'Igual a') {
            $operador = '=';
        } else if ($operador == 'Igual a fecha') {
            $operador = '=';
            $resultados1 = DB::table('usr_app_clientes')
                ->join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
                ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
                ->join('usr_app_tipos_persona as tp', 'tp.id', '=', 'usr_app_clientes.tipo_persona_id')
                ->join('usr_app_operaciones as op', 'op.id', '=', 'usr_app_clientes.operacion_id')
                ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
                ->join('usr_app_estratos as est', 'est.id', '=', 'usr_app_clientes.estrato_id')
                ->join('usr_app_municipios as mun1', 'mun1.id', '=', 'usr_app_clientes.municipio_id')
                ->join('usr_app_municipios as mun2', 'mun2.id', '=', 'usr_app_clientes.municipio_prestacion_servicio_id')
                ->join('usr_app_departamentos as dep1', 'dep1.id', '=', 'mun1.departamento_id')
                ->join('usr_app_departamentos as dep2', 'dep2.id', '=', 'mun2.departamento_id')
                ->join('usr_app_paises as pais', 'pais.id', '=', 'dep1.pais_id')
                ->join('usr_app_paises as pais2', 'pais2.id', '=', 'dep2.pais_id')
                ->join('usr_app_sociedades_comerciales as sc', 'sc.id', '=', 'usr_app_clientes.sociedad_comercial_id')
                ->join('usr_app_jornadas_laborales as jl', 'jl.id', '=', 'usr_app_clientes.jornada_laboral_id')
                ->join('usr_app_rotaciones_personal as rp', 'rp.id', '=', 'usr_app_clientes.rotacion_personal_id')
                ->join('usr_app_riesgos_laborales as rl', 'rl.id', '=', 'usr_app_clientes.riesgo_cliente_id')
                ->join('gen_sucursal as scf', 'scf.cod_suc', '=', 'usr_app_clientes.sucursal_facturacion_id')
                ->join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->join('usr_app_periodicidad_liquidacion_nominas as pl', 'pl.id', '=', 'usr_app_clientes.periodicidad_liquidacion_id')
                ->join('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
                ->join('gen_tipide as ti2', 'ti2.cod_tip', '=', 'con.tipo_identificacion_id')
                ->join('usr_app_datos_tesoreria as tes', 'tes.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_datos_financieros as fin', 'fin.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_operaciones_internacionales as opi', 'opi.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_tipo_operaciones_internacionales as topi', 'topi.id', '=', 'opi.tipo_operaciones_id')
                ->join('usr_app_tipo_proveedor as tpro', 'tpro.id', '=', 'usr_app_clientes.tipo_proveedor_id')
                ->join('usr_app_tipo_cliente as tcli', 'tcli.id', '=', 'usr_app_clientes.tipo_cliente_id')
                ->select(
                    'usr_app_clientes.id',
                    'tcli.nombre as tipo_cliente',
                    'tpro.nombre as tipo_proveedor',
                    'op.nombre as tipo_operacion',
                    'usr_app_clientes.contratacion_directa',
                    'usr_app_clientes.atraccion_seleccion',
                    'tp.nombre as tipo_persona',
                    'ti.des_tip as tipo_identificacion',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.fecha_exp_documento',
                    'usr_app_clientes.nit',
                    'usr_app_clientes.digito_verificacion',
                    'usr_app_clientes.razon_social',
                    'usr_app_clientes.fecha_constitucion',
                    'usr_app_clientes.numero_empleados',
                    'cc.codigo as codigo_ciiu',
                    'ac.codigo_actividad as codigo_actividad_ciiu',
                    'est.nombre as estrato',
                    'pais.nombre as pais',
                    'dep1.nombre as departamento',
                    'mun1.nombre as municipio',
                    'usr_app_clientes.direccion_empresa',
                    'usr_app_clientes.contacto_empresa',
                    'usr_app_clientes.correo_empresa',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.celular_empresa',
                    'sc.nombre as sociedad_comercial',
                    'usr_app_clientes.otra',
                    'pl.nombre as periodicidad_liquidacion',
                    'usr_app_clientes.plazo_pago',
                    'pais2.nombre as pais_prestacion_servicio',
                    'dep2.nombre as departamento_prestacion_servicio',
                    'mun2.nombre as municipio_prestacion_servicio',
                    'usr_app_clientes.aiu_negociado',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.acuerdo_comercial',
                    'jl.nombre as jornada_laboral',
                    'rp.nombre as rotacion_personal',
                    'rl.nombre as riesgo_cliente',
                    'usr_app_clientes.junta_directiva',
                    'usr_app_clientes.responsable_inpuesto_ventas',
                    'usr_app_clientes.correo_facturacion_electronica',
                    'scf.nom_suc as sucursal_facturacion',
                    'con.nombre as nombre_contador',
                    'con.identificacion as identificacion_contador',
                    'con.telefono as telefono_contador',
                    'ti2.des_tip as tipo_identificacion_contador',
                    'tes.nombre as nombre_tesorero',
                    'tes.telefono as telefono_tesorero',
                    'tes.correo as correo_tesorero',
                    'fin.ingreso_mensual as ingreso_mensual',
                    'fin.otros_ingresos as otros_ingresos',
                    'fin.total_ingresos as total_ingresos',
                    'fin.costos_gastos_mensual as costos_gastos_mensual',
                    'fin.detalle_otros_ingresos as detalle_otros_ingresos',
                    'fin.reintegro_costos_gastos as reintegro_costos_gastos',
                    'fin.activos as activos',
                    'fin.pasivos as pasivos',
                    'fin.patrimonio as patrimonio',
                    'usr_app_clientes.operaciones_internacionales',
                    'topi.nombre as tipo_operacion_internacional',
                    'usr_app_clientes.declaraciones_autirizaciones',
                    'usr_app_clientes.tratamiento_datos_personales'
                )
                ->whereDate('usr_app_clientes.' . $campo, $operador, $valor)
                ->get();

            $i = 0;
            foreach ($resultados1 as $item) {
                try {
                    $resultados2 = DB::table('usr_app_accionistas')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_accionistas.tipo_identificacion_id')
                        ->select(
                            'usr_app_accionistas.id',
                            'usr_app_accionistas.identificacion',
                            'usr_app_accionistas.accionista as socio',
                            'usr_app_accionistas.participacion',
                            'ti.des_tip',
                            'usr_app_accionistas.cliente_id'
                        )
                        ->where('usr_app_accionistas.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados2 as $item2) {
                        if ($item->id == $item2->cliente_id) {
                            $item->{'socio' . $i} = $item2->socio;
                            $item->{'tipo_identificacion' . $i} = $item2->des_tip;
                            $item->{'identificacion' . $i} = $item2->identificacion;
                            $item->{'participacion' . $i} = $item2->participacion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'socio' . $i} = 'N/A';
                        $item->{'tipo_identificacion' . $i} = 'N/A';
                        $item->{'identificacion' . $i} = 'N/A';
                        $item->{'participacion' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados3 = DB::table('usr_app_representantes_legales')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_representantes_legales.tipo_identificacion_id')
                        ->join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_representantes_legales.municipio_expedicion_id')
                        ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                        ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
                        ->select(
                            'usr_app_representantes_legales.id',
                            'usr_app_representantes_legales.nombre',
                            'usr_app_representantes_legales.identificacion',
                            'usr_app_representantes_legales.correo_electronico as correo',
                            'usr_app_representantes_legales.telefono',
                            'ti.des_tip',
                            'mun.nombre as ciudad_expedicion',
                            'dep.nombre as departamento',
                            'pais.nombre as pais',
                            'usr_app_representantes_legales.cliente_id'
                        )
                        ->where('usr_app_representantes_legales.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados3 as $item3) {
                        if ($item->id == $item3->cliente_id) {
                            $item->{'nombre_rl' . $i} = $item3->nombre;
                            $item->{'des_tip_rl' . $i} = $item3->des_tip;
                            $item->{'identificacion_rl' . $i} = $item3->identificacion;
                            $item->{'telefono_rl' . $i} = $item3->telefono;
                            $item->{'correo_rl' . $i} = $item3->correo;
                            $item->{'pais_rl' . $i} = $item3->pais;
                            $item->{'departamento_rl' . $i} = $item3->departamento;
                            $item->{'ciudad_expedicion_rl' . $i} = $item3->ciudad_expedicion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'nombre_rl' . $i} = 'N/A';
                        $item->{'des_tip_rl' . $i} = 'N/A';
                        $item->{'identificacion_rl' . $i} = 'N/A';
                        $item->{'telefono_rl' . $i} = 'N/A';
                        $item->{'correo_rl' . $i} = 'N/A';
                        $item->{'pais_rl' . $i} = 'N/A';
                        $item->{'departamento_rl' . $i} = 'N/A';
                        $item->{'ciudad_expedicion_rl' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados4 = DB::table('usr_app_juntas_directivas')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_juntas_directivas.tipo_identificacion_id')
                        ->select(
                            'usr_app_juntas_directivas.id',
                            'usr_app_juntas_directivas.nombre',
                            'usr_app_juntas_directivas.identificacion',
                            'ti.des_tip',
                        )
                        ->where('usr_app_juntas_directivas.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados4 as $item4) {
                        if ($item->id == $item4->cliente_id) {
                            $item->{'nombre_jd' . $i} = $item4->nombre;
                            $item->{'tipo_identificacion_jd' . $i} = $item4->des_tip;
                            $item->{'identificacion_jd' . $i} = $item4->identificacion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'nombre_jd' . $i} = 'N/A';
                        $item->{'tipo_identificacion_jd' . $i} = 'N/A';
                        $item->{'identificacion_jd' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados5 = DB::table('usr_app_referencias_bancarias')
                        ->join('gen_bancos as ban', 'ban.cod_ban', '=', 'usr_app_referencias_bancarias.banco_id')
                        ->join('usr_app_tipos_cuenta_banco as tc', 'tc.id', '=', 'usr_app_referencias_bancarias.tipo_cuenta_id')
                        ->select(
                            'ban.cod_ban as banco_id',
                            'ban.nom_ban as banco',
                            'usr_app_referencias_bancarias.id',
                            'usr_app_referencias_bancarias.numero_cuenta',
                            'tc.id as tipo_cuenta_banco',
                            'tc.nombre as tipo_cuenta',
                            'usr_app_referencias_bancarias.sucursal',
                            'usr_app_referencias_bancarias.telefono',
                            'usr_app_referencias_bancarias.contacto',
                            'usr_app_referencias_bancarias.cliente_id'
                        )
                        ->where('usr_app_referencias_bancarias.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados5 as $item5) {
                        if ($item->id == $item5->cliente_id) {
                            $item->{'banco_rb' . $i} = $item5->banco;
                            $item->{'numero_cuenta_rb' . $i} = $item5->numero_cuenta;
                            $item->{'tipo_cuenta_rb' . $i} = $item5->tipo_cuenta;
                            $item->{'sucursal_rb' . $i} = $item5->sucursal;
                            $item->{'telefono_rb' . $i} = $item5->telefono;
                            $item->{'contacto_rb' . $i} = $item5->contacto;
                            $i++;
                        }
                    }

                    while ($i < 2) {
                        $item->{'banco_rb' . $i} = 'N/A';
                        $item->{'banco_rb' . $i} = 'N/A';
                        $item->{'numero_cuenta_rb' . $i} = 'N/A';
                        $item->{'tipo_cuenta_rb' . $i} = 'N/A';
                        $item->{'sucursal_rb' . $i} = 'N/A';
                        $item->{'telefono_rb' . $i} = 'N/A';
                        $item->{'contacto_rb' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados6 = DB::table('usr_app_referencias_comerciales')
                        ->select(
                            'razon_social as nombre',
                            'contacto',
                            'telefono',
                            'cliente_id',
                        )
                        ->where('usr_app_referencias_comerciales.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados6 as $item6) {
                        if ($item->id == $item6->cliente_id) {
                            $item->{'nombre_rc' . $i} = $item6->nombre;
                            $item->{'contacto_rc' . $i} = $item6->contacto;
                            $item->{'telefono_rc' . $i} = $item6->telefono;
                            $i++;
                        }
                    }

                    while ($i < 2) {
                        $item->{'nombre_rc' . $i} = 'N/A';
                        $item->{'contacto_rc' . $i} = 'N/A';
                        $item->{'telefono_rc' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados7 = DB::table('usr_app_personas_expuestas_politica')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_personas_expuestas_politica.tipo_identificacion_id')
                        ->select(
                            'usr_app_personas_expuestas_politica.nombre',
                            'usr_app_personas_expuestas_politica.numero_identificacion as identificacion',
                            'usr_app_personas_expuestas_politica.parentesco',
                            'ti.des_tip',
                            'usr_app_personas_expuestas_politica.cliente_id'
                        )
                        ->where('usr_app_personas_expuestas_politica.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados7 as $item7) {
                        if ($item->id == $item7->cliente_id) {
                            $item->{'nombre_pe' . $i} = $item7->nombre;
                            $item->{'tipo_identificacion_pe' . $i} = $item7->des_tip;
                            $item->{'identificacion_pe' . $i} = $item7->identificacion;
                            $item->{'parentesco_pe' . $i} = $item7->parentesco;
                            $i++;
                        }
                    }

                    while ($i < 10) {
                        $item->{'nombre_pe' . $i} = 'N/A';
                        $item->{'tipo_identificacion_pe' . $i} = 'N/A';
                        $item->{'identificacion_pe' . $i} = 'N/A';
                        $item->{'parentesco_pe' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados8 = DB::table('usr_app_cargos')
                        ->select(
                            'id',
                            'nombre',
                            'riesgo_laboral_id',
                            'cliente_id'
                        )
                        ->where('usr_app_cargos.cliente_id', '=', $item->id)
                        ->get();


                    foreach ($resultados8 as $item8) {
                        if ($item->id == $item8->cliente_id) {
                            $item->{'nombre_cargo' . $i} = $item8->nombre;
                            $item->{'riesgo_laboral_cargo' . $i} = $item8->riesgo_laboral_id;
                            $i++;
                        //     $examenes = '';
                        //     $resultados9 = DB::table('usr_app_cargos_examenes')
                        //     ->select(
                        //         'id',
                        //         'nombre',
                        //         'riesgo_laboral_id',
                        //         // 'cliente_id'
                        //     )
                        //     ->where('usr_app_cargos_examenes.cargo_id', '=', $item8->id)
                        //     ->get();
                        //     foreach ($resultados9 as $item9) {
                        //         $examenes .= $item9->examen_id;
                        //     }
                        //     $item->{'examenes' . $i} = $examenes;

                        //     $requisitos= '';
                        //     $resultados10 = DB::table('usr_app_cargos_requisitos')
                        //     ->select(
                        //         'id',
                        //         'nombre',
                        //         'riesgo_laboral_id',
                        //         // 'cliente_id'
                        //     )
                        //     ->where('usr_app_cargos_requisitos.cargo_id', '=', $item8->id)
                        //     ->get();
                         
                        //     foreach ($resultados10 as $item10) {
                        //         $requisitos .= $item10->requisito_id;
                        //     }
                        //     $item->{'requsitos' . $i} = $requisitos;

                        }
                    }

                    while ($i < 5) {
                        $item->{'nombre_cargo' . $i} = 'N/A';
                        $item->{'riesgo_laboral_cargo' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            return (new FormularioDDExport2($resultados1))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        } else if ($operador == 'Entre') {

            $resultados1 = DB::table('usr_app_clientes')
                ->join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
                ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
                ->join('usr_app_tipos_persona as tp', 'tp.id', '=', 'usr_app_clientes.tipo_persona_id')
                ->join('usr_app_operaciones as op', 'op.id', '=', 'usr_app_clientes.operacion_id')
                ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
                ->join('usr_app_estratos as est', 'est.id', '=', 'usr_app_clientes.estrato_id')
                ->join('usr_app_municipios as mun1', 'mun1.id', '=', 'usr_app_clientes.municipio_id')
                ->join('usr_app_municipios as mun2', 'mun2.id', '=', 'usr_app_clientes.municipio_prestacion_servicio_id')
                ->join('usr_app_departamentos as dep1', 'dep1.id', '=', 'mun1.departamento_id')
                ->join('usr_app_departamentos as dep2', 'dep2.id', '=', 'mun2.departamento_id')
                ->join('usr_app_paises as pais', 'pais.id', '=', 'dep1.pais_id')
                ->join('usr_app_paises as pais2', 'pais2.id', '=', 'dep2.pais_id')
                ->join('usr_app_sociedades_comerciales as sc', 'sc.id', '=', 'usr_app_clientes.sociedad_comercial_id')
                ->join('usr_app_jornadas_laborales as jl', 'jl.id', '=', 'usr_app_clientes.jornada_laboral_id')
                ->join('usr_app_rotaciones_personal as rp', 'rp.id', '=', 'usr_app_clientes.rotacion_personal_id')
                ->join('usr_app_riesgos_laborales as rl', 'rl.id', '=', 'usr_app_clientes.riesgo_cliente_id')
                ->join('gen_sucursal as scf', 'scf.cod_suc', '=', 'usr_app_clientes.sucursal_facturacion_id')
                ->join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->join('usr_app_periodicidad_liquidacion_nominas as pl', 'pl.id', '=', 'usr_app_clientes.periodicidad_liquidacion_id')
                ->join('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
                ->join('gen_tipide as ti2', 'ti2.cod_tip', '=', 'con.tipo_identificacion_id')
                ->join('usr_app_datos_tesoreria as tes', 'tes.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_datos_financieros as fin', 'fin.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_operaciones_internacionales as opi', 'opi.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_tipo_operaciones_internacionales as topi', 'topi.id', '=', 'opi.tipo_operaciones_id')
                ->join('usr_app_tipo_proveedor as tpro', 'tpro.id', '=', 'usr_app_clientes.tipo_proveedor_id')
                ->join('usr_app_tipo_cliente as tcli', 'tcli.id', '=', 'usr_app_clientes.tipo_cliente_id')
                ->select(
                    'usr_app_clientes.id',
                    'tcli.nombre as tipo_cliente',
                    'tpro.nombre as tipo_proveedor',
                    'op.nombre as tipo_operacion',
                    'usr_app_clientes.contratacion_directa',
                    'usr_app_clientes.atraccion_seleccion',
                    'tp.nombre as tipo_persona',
                    'ti.des_tip as tipo_identificacion',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.fecha_exp_documento',
                    'usr_app_clientes.nit',
                    'usr_app_clientes.digito_verificacion',
                    'usr_app_clientes.razon_social',
                    'usr_app_clientes.fecha_constitucion',
                    'usr_app_clientes.numero_empleados',
                    'cc.codigo as codigo_ciiu',
                    'ac.codigo_actividad as codigo_actividad_ciiu',
                    'est.nombre as estrato',
                    'pais.nombre as pais',
                    'dep1.nombre as departamento',
                    'mun1.nombre as municipio',
                    'usr_app_clientes.direccion_empresa',
                    'usr_app_clientes.contacto_empresa',
                    'usr_app_clientes.correo_empresa',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.celular_empresa',
                    'sc.nombre as sociedad_comercial',
                    'usr_app_clientes.otra',
                    'pl.nombre as periodicidad_liquidacion',
                    'usr_app_clientes.plazo_pago',
                    'pais2.nombre as pais_prestacion_servicio',
                    'dep2.nombre as departamento_prestacion_servicio',
                    'mun2.nombre as municipio_prestacion_servicio',
                    'usr_app_clientes.aiu_negociado',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.acuerdo_comercial',
                    'jl.nombre as jornada_laboral',
                    'rp.nombre as rotacion_personal',
                    'rl.nombre as riesgo_cliente',
                    'usr_app_clientes.junta_directiva',
                    'usr_app_clientes.responsable_inpuesto_ventas',
                    'usr_app_clientes.correo_facturacion_electronica',
                    'scf.nom_suc as sucursal_facturacion',
                    'con.nombre as nombre_contador',
                    'con.identificacion as identificacion_contador',
                    'con.telefono as telefono_contador',
                    'ti2.des_tip as tipo_identificacion_contador',
                    'tes.nombre as nombre_tesorero',
                    'tes.telefono as telefono_tesorero',
                    'tes.correo as correo_tesorero',
                    'fin.ingreso_mensual as ingreso_mensual',
                    'fin.otros_ingresos as otros_ingresos',
                    'fin.total_ingresos as total_ingresos',
                    'fin.costos_gastos_mensual as costos_gastos_mensual',
                    'fin.detalle_otros_ingresos as detalle_otros_ingresos',
                    'fin.reintegro_costos_gastos as reintegro_costos_gastos',
                    'fin.activos as activos',
                    'fin.pasivos as pasivos',
                    'fin.patrimonio as patrimonio',
                    'usr_app_clientes.operaciones_internacionales',
                    'topi.nombre as tipo_operacion_internacional',
                    'usr_app_clientes.declaraciones_autirizaciones',
                    'usr_app_clientes.tratamiento_datos_personales'
                )
                ->whereDate('usr_app_clientes.' . $campo, '>=', $valor)
                ->whereDate('usr_app_clientes.' . $campo, '<=', $valor2)
                ->get();

            $i = 0;
            foreach ($resultados1 as $item) {
                try {
                    $resultados2 = DB::table('usr_app_accionistas')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_accionistas.tipo_identificacion_id')
                        ->select(
                            'usr_app_accionistas.id',
                            'usr_app_accionistas.identificacion',
                            'usr_app_accionistas.accionista as socio',
                            'usr_app_accionistas.participacion',
                            'ti.des_tip',
                            'usr_app_accionistas.cliente_id'
                        )
                        ->where('usr_app_accionistas.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados2 as $item2) {
                        if ($item->id == $item2->cliente_id) {
                            $item->{'socio' . $i} = $item2->socio;
                            $item->{'tipo_identificacion' . $i} = $item2->des_tip;
                            $item->{'identificacion' . $i} = $item2->identificacion;
                            $item->{'participacion' . $i} = $item2->participacion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'socio' . $i} = 'N/A';
                        $item->{'tipo_identificacion' . $i} = 'N/A';
                        $item->{'identificacion' . $i} = 'N/A';
                        $item->{'participacion' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados3 = DB::table('usr_app_representantes_legales')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_representantes_legales.tipo_identificacion_id')
                        ->join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_representantes_legales.municipio_expedicion_id')
                        ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                        ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
                        ->select(
                            'usr_app_representantes_legales.id',
                            'usr_app_representantes_legales.nombre',
                            'usr_app_representantes_legales.identificacion',
                            'usr_app_representantes_legales.correo_electronico as correo',
                            'usr_app_representantes_legales.telefono',
                            'ti.des_tip',
                            'mun.nombre as ciudad_expedicion',
                            'dep.nombre as departamento',
                            'pais.nombre as pais',
                            'usr_app_representantes_legales.cliente_id'
                        )
                        ->where('usr_app_representantes_legales.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados3 as $item3) {
                        if ($item->id == $item3->cliente_id) {
                            $item->{'nombre_rl' . $i} = $item3->nombre;
                            $item->{'des_tip_rl' . $i} = $item3->des_tip;
                            $item->{'identificacion_rl' . $i} = $item3->identificacion;
                            $item->{'telefono_rl' . $i} = $item3->telefono;
                            $item->{'correo_rl' . $i} = $item3->correo;
                            $item->{'pais_rl' . $i} = $item3->pais;
                            $item->{'departamento_rl' . $i} = $item3->departamento;
                            $item->{'ciudad_expedicion_rl' . $i} = $item3->ciudad_expedicion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'nombre_rl' . $i} = 'N/A';
                        $item->{'des_tip_rl' . $i} = 'N/A';
                        $item->{'identificacion_rl' . $i} = 'N/A';
                        $item->{'telefono_rl' . $i} = 'N/A';
                        $item->{'correo_rl' . $i} = 'N/A';
                        $item->{'pais_rl' . $i} = 'N/A';
                        $item->{'departamento_rl' . $i} = 'N/A';
                        $item->{'ciudad_expedicion_rl' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados4 = DB::table('usr_app_juntas_directivas')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_juntas_directivas.tipo_identificacion_id')
                        ->select(
                            'usr_app_juntas_directivas.id',
                            'usr_app_juntas_directivas.nombre',
                            'usr_app_juntas_directivas.identificacion',
                            'ti.des_tip',
                        )
                        ->where('usr_app_juntas_directivas.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados4 as $item4) {
                        if ($item->id == $item4->cliente_id) {
                            $item->{'nombre_jd' . $i} = $item4->nombre;
                            $item->{'tipo_identificacion_jd' . $i} = $item4->des_tip;
                            $item->{'identificacion_jd' . $i} = $item4->identificacion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'nombre_jd' . $i} = 'N/A';
                        $item->{'tipo_identificacion_jd' . $i} = 'N/A';
                        $item->{'identificacion_jd' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados5 = DB::table('usr_app_referencias_bancarias')
                        ->join('gen_bancos as ban', 'ban.cod_ban', '=', 'usr_app_referencias_bancarias.banco_id')
                        ->join('usr_app_tipos_cuenta_banco as tc', 'tc.id', '=', 'usr_app_referencias_bancarias.tipo_cuenta_id')
                        ->select(
                            'ban.cod_ban as banco_id',
                            'ban.nom_ban as banco',
                            'usr_app_referencias_bancarias.id',
                            'usr_app_referencias_bancarias.numero_cuenta',
                            'tc.id as tipo_cuenta_banco',
                            'tc.nombre as tipo_cuenta',
                            'usr_app_referencias_bancarias.sucursal',
                            'usr_app_referencias_bancarias.telefono',
                            'usr_app_referencias_bancarias.contacto',
                            'usr_app_referencias_bancarias.cliente_id'
                        )
                        ->where('usr_app_referencias_bancarias.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados5 as $item5) {
                        if ($item->id == $item5->cliente_id) {
                            $item->{'banco_rb' . $i} = $item5->banco;
                            $item->{'numero_cuenta_rb' . $i} = $item5->numero_cuenta;
                            $item->{'tipo_cuenta_rb' . $i} = $item5->tipo_cuenta;
                            $item->{'sucursal_rb' . $i} = $item5->sucursal;
                            $item->{'telefono_rb' . $i} = $item5->telefono;
                            $item->{'contacto_rb' . $i} = $item5->contacto;
                            $i++;
                        }
                    }

                    while ($i < 2) {
                        $item->{'banco_rb' . $i} = 'N/A';
                        $item->{'banco_rb' . $i} = 'N/A';
                        $item->{'numero_cuenta_rb' . $i} = 'N/A';
                        $item->{'tipo_cuenta_rb' . $i} = 'N/A';
                        $item->{'sucursal_rb' . $i} = 'N/A';
                        $item->{'telefono_rb' . $i} = 'N/A';
                        $item->{'contacto_rb' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados6 = DB::table('usr_app_referencias_comerciales')
                        ->select(
                            'razon_social as nombre',
                            'contacto',
                            'telefono',
                            'cliente_id',
                        )
                        ->where('usr_app_referencias_comerciales.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados6 as $item6) {
                        if ($item->id == $item6->cliente_id) {
                            $item->{'nombre_rc' . $i} = $item6->nombre;
                            $item->{'contacto_rc' . $i} = $item6->contacto;
                            $item->{'telefono_rc' . $i} = $item6->telefono;
                            $i++;
                        }
                    }

                    while ($i < 2) {
                        $item->{'nombre_rc' . $i} = 'N/A';
                        $item->{'contacto_rc' . $i} = 'N/A';
                        $item->{'telefono_rc' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados7 = DB::table('usr_app_personas_expuestas_politica')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_personas_expuestas_politica.tipo_identificacion_id')
                        ->select(
                            'usr_app_personas_expuestas_politica.nombre',
                            'usr_app_personas_expuestas_politica.numero_identificacion as identificacion',
                            'usr_app_personas_expuestas_politica.parentesco',
                            'ti.des_tip',
                            'usr_app_personas_expuestas_politica.cliente_id'
                        )
                        ->where('usr_app_personas_expuestas_politica.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados7 as $item7) {
                        if ($item->id == $item7->cliente_id) {
                            $item->{'nombre_pe' . $i} = $item7->nombre;
                            $item->{'tipo_identificacion_pe' . $i} = $item7->des_tip;
                            $item->{'identificacion_pe' . $i} = $item7->identificacion;
                            $item->{'parentesco_pe' . $i} = $item7->parentesco;
                            $i++;
                        }
                    }

                    while ($i < 10) {
                        $item->{'nombre_pe' . $i} = 'N/A';
                        $item->{'tipo_identificacion_pe' . $i} = 'N/A';
                        $item->{'identificacion_pe' . $i} = 'N/A';
                        $item->{'parentesco_pe' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados8 = DB::table('usr_app_cargos')
                        ->select(
                            'id',
                            'nombre',
                            'riesgo_laboral_id',
                            'cliente_id'
                        )
                        ->where('usr_app_cargos.cliente_id', '=', $item->id)
                        ->get();


                    foreach ($resultados8 as $item8) {
                        if ($item->id == $item8->cliente_id) {
                            $item->{'nombre_cargo' . $i} = $item8->nombre;
                            $item->{'riesgo_laboral_cargo' . $i} = $item8->riesgo_laboral_id;
                            $i++;
                            $examenes = 'N/A';
                            $resultados9 = DB::table('usr_app_cargos_examenes')
                            ->select(
                                'id',
                                'nombre',
                                'riesgo_laboral_id',
                                // 'cliente_id'
                            )
                            ->where('usr_app_cargos_examenes.cargo_id', '=', $item8->id)
                            ->get();
                            
                            foreach ($resultados9 as $item9) {
                                $examenes .= $item9->examen_id;
                            }
                            $item->{'examenes' . $i} = $examenes;

                            $requisitos= 'N/A';
                            $resultados10 = DB::table('usr_app_cargos_requisitos')
                            ->select(
                                'id',
                                'nombre',
                                'riesgo_laboral_id',
                                // 'cliente_id'
                            )
                            ->where('usr_app_cargos_requisitos.cargo_id', '=', $item8->id)
                            ->get();
                         
                            foreach ($resultados10 as $item10) {
                                $requisitos .= $item10->requisito_id;
                            }
                            $item->{'requsitos' . $i} = $requisitos;

                        }
                    }

                    while ($i < 5) {
                        $item->{'nombre_cargo' . $i} = 'N/A';
                        $item->{'riesgo_laboral_cargo' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            return (new FormularioDDExport2($resultados1))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }
        if ($campo == 'vendedor') {

            $resultados1 = DB::table('usr_app_clientes')
                ->join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
                ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
                ->join('usr_app_tipos_persona as tp', 'tp.id', '=', 'usr_app_clientes.tipo_persona_id')
                ->join('usr_app_operaciones as op', 'op.id', '=', 'usr_app_clientes.operacion_id')
                ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
                ->join('usr_app_estratos as est', 'est.id', '=', 'usr_app_clientes.estrato_id')
                ->join('usr_app_municipios as mun1', 'mun1.id', '=', 'usr_app_clientes.municipio_id')
                ->join('usr_app_municipios as mun2', 'mun2.id', '=', 'usr_app_clientes.municipio_prestacion_servicio_id')
                ->join('usr_app_departamentos as dep1', 'dep1.id', '=', 'mun1.departamento_id')
                ->join('usr_app_departamentos as dep2', 'dep2.id', '=', 'mun2.departamento_id')
                ->join('usr_app_paises as pais', 'pais.id', '=', 'dep1.pais_id')
                ->join('usr_app_paises as pais2', 'pais2.id', '=', 'dep2.pais_id')
                ->join('usr_app_sociedades_comerciales as sc', 'sc.id', '=', 'usr_app_clientes.sociedad_comercial_id')
                ->join('usr_app_jornadas_laborales as jl', 'jl.id', '=', 'usr_app_clientes.jornada_laboral_id')
                ->join('usr_app_rotaciones_personal as rp', 'rp.id', '=', 'usr_app_clientes.rotacion_personal_id')
                ->join('usr_app_riesgos_laborales as rl', 'rl.id', '=', 'usr_app_clientes.riesgo_cliente_id')
                ->join('gen_sucursal as scf', 'scf.cod_suc', '=', 'usr_app_clientes.sucursal_facturacion_id')
                ->join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->join('usr_app_periodicidad_liquidacion_nominas as pl', 'pl.id', '=', 'usr_app_clientes.periodicidad_liquidacion_id')
                ->join('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
                ->join('gen_tipide as ti2', 'ti2.cod_tip', '=', 'con.tipo_identificacion_id')
                ->join('usr_app_datos_tesoreria as tes', 'tes.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_datos_financieros as fin', 'fin.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_operaciones_internacionales as opi', 'opi.cliente_id', '=', 'usr_app_clientes.id')
                ->join('usr_app_tipo_operaciones_internacionales as topi', 'topi.id', '=', 'opi.tipo_operaciones_id')
                ->join('usr_app_tipo_proveedor as tpro', 'tpro.id', '=', 'usr_app_clientes.tipo_proveedor_id')
                ->join('usr_app_tipo_cliente as tcli', 'tcli.id', '=', 'usr_app_clientes.tipo_cliente_id')
                ->select(
                    'usr_app_clientes.id',
                    'tcli.nombre as tipo_cliente',
                    'tpro.nombre as tipo_proveedor',
                    'op.nombre as tipo_operacion',
                    'usr_app_clientes.contratacion_directa',
                    'usr_app_clientes.atraccion_seleccion',
                    'tp.nombre as tipo_persona',
                    'ti.des_tip as tipo_identificacion',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.fecha_exp_documento',
                    'usr_app_clientes.nit',
                    'usr_app_clientes.digito_verificacion',
                    'usr_app_clientes.razon_social',
                    'usr_app_clientes.fecha_constitucion',
                    'usr_app_clientes.numero_empleados',
                    'cc.codigo as codigo_ciiu',
                    'ac.codigo_actividad as codigo_actividad_ciiu',
                    'est.nombre as estrato',
                    'pais.nombre as pais',
                    'dep1.nombre as departamento',
                    'mun1.nombre as municipio',
                    'usr_app_clientes.direccion_empresa',
                    'usr_app_clientes.contacto_empresa',
                    'usr_app_clientes.correo_empresa',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.celular_empresa',
                    'sc.nombre as sociedad_comercial',
                    'usr_app_clientes.otra',
                    'pl.nombre as periodicidad_liquidacion',
                    'usr_app_clientes.plazo_pago',
                    'pais2.nombre as pais_prestacion_servicio',
                    'dep2.nombre as departamento_prestacion_servicio',
                    'mun2.nombre as municipio_prestacion_servicio',
                    'usr_app_clientes.aiu_negociado',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.acuerdo_comercial',
                    'jl.nombre as jornada_laboral',
                    'rp.nombre as rotacion_personal',
                    'rl.nombre as riesgo_cliente',
                    'usr_app_clientes.junta_directiva',
                    'usr_app_clientes.responsable_inpuesto_ventas',
                    'usr_app_clientes.correo_facturacion_electronica',
                    'scf.nom_suc as sucursal_facturacion',
                    'con.nombre as nombre_contador',
                    'con.identificacion as identificacion_contador',
                    'con.telefono as telefono_contador',
                    'ti2.des_tip as tipo_identificacion_contador',
                    'tes.nombre as nombre_tesorero',
                    'tes.telefono as telefono_tesorero',
                    'tes.correo as correo_tesorero',
                    'fin.ingreso_mensual as ingreso_mensual',
                    'fin.otros_ingresos as otros_ingresos',
                    'fin.total_ingresos as total_ingresos',
                    'fin.costos_gastos_mensual as costos_gastos_mensual',
                    'fin.detalle_otros_ingresos as detalle_otros_ingresos',
                    'fin.reintegro_costos_gastos as reintegro_costos_gastos',
                    'fin.activos as activos',
                    'fin.pasivos as pasivos',
                    'fin.patrimonio as patrimonio',
                    'usr_app_clientes.operaciones_internacionales',
                    'topi.nombre as tipo_operacion_internacional',
                    'usr_app_clientes.declaraciones_autirizaciones',
                    'usr_app_clientes.tratamiento_datos_personales'
                )
                ->where('ven.nom_ven', $operador, $valor2)
                ->get();

            $i = 0;
            foreach ($resultados1 as $item) {

                try {
                    $resultados2 = DB::table('usr_app_accionistas')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_accionistas.tipo_identificacion_id')
                        ->select(
                            'usr_app_accionistas.id',
                            'usr_app_accionistas.identificacion',
                            'usr_app_accionistas.accionista as socio',
                            'usr_app_accionistas.participacion',
                            'ti.des_tip',
                            'usr_app_accionistas.cliente_id'
                        )
                        ->where('usr_app_accionistas.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados2 as $item2) {
                        if ($item->id == $item2->cliente_id) {
                            $item->{'socio' . $i} = $item2->socio;
                            $item->{'tipo_identificacion' . $i} = $item2->des_tip;
                            $item->{'identificacion' . $i} = $item2->identificacion;
                            $item->{'participacion' . $i} = $item2->participacion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'socio' . $i} = 'N/A';
                        $item->{'tipo_identificacion' . $i} = 'N/A';
                        $item->{'identificacion' . $i} = 'N/A';
                        $item->{'participacion' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados3 = DB::table('usr_app_representantes_legales')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_representantes_legales.tipo_identificacion_id')
                        ->join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_representantes_legales.municipio_expedicion_id')
                        ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                        ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
                        ->select(
                            'usr_app_representantes_legales.id',
                            'usr_app_representantes_legales.nombre',
                            'usr_app_representantes_legales.identificacion',
                            'usr_app_representantes_legales.correo_electronico as correo',
                            'usr_app_representantes_legales.telefono',
                            'ti.des_tip',
                            'mun.nombre as ciudad_expedicion',
                            'dep.nombre as departamento',
                            'pais.nombre as pais',
                            'usr_app_representantes_legales.cliente_id'
                        )
                        ->where('usr_app_representantes_legales.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados3 as $item3) {
                        if ($item->id == $item3->cliente_id) {
                            $item->{'nombre_rl' . $i} = $item3->nombre;
                            $item->{'des_tip_rl' . $i} = $item3->des_tip;
                            $item->{'identificacion_rl' . $i} = $item3->identificacion;
                            $item->{'telefono_rl' . $i} = $item3->telefono;
                            $item->{'correo_rl' . $i} = $item3->correo;
                            $item->{'pais_rl' . $i} = $item3->pais;
                            $item->{'departamento_rl' . $i} = $item3->departamento;
                            $item->{'ciudad_expedicion_rl' . $i} = $item3->ciudad_expedicion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'nombre_rl' . $i} = 'N/A';
                        $item->{'des_tip_rl' . $i} = 'N/A';
                        $item->{'identificacion_rl' . $i} = 'N/A';
                        $item->{'telefono_rl' . $i} = 'N/A';
                        $item->{'correo_rl' . $i} = 'N/A';
                        $item->{'pais_rl' . $i} = 'N/A';
                        $item->{'departamento_rl' . $i} = 'N/A';
                        $item->{'ciudad_expedicion_rl' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados4 = DB::table('usr_app_juntas_directivas')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_juntas_directivas.tipo_identificacion_id')
                        ->select(
                            'usr_app_juntas_directivas.id',
                            'usr_app_juntas_directivas.nombre',
                            'usr_app_juntas_directivas.identificacion',
                            'ti.des_tip',
                        )
                        ->where('usr_app_juntas_directivas.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados4 as $item4) {
                        if ($item->id == $item4->cliente_id) {
                            $item->{'nombre_jd' . $i} = $item4->nombre;
                            $item->{'tipo_identificacion_jd' . $i} = $item4->des_tip;
                            $item->{'identificacion_jd' . $i} = $item4->identificacion;
                            $i++;
                        }
                    }
                    while ($i < 5) {
                        $item->{'nombre_jd' . $i} = 'N/A';
                        $item->{'tipo_identificacion_jd' . $i} = 'N/A';
                        $item->{'identificacion_jd' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados5 = DB::table('usr_app_referencias_bancarias')
                        ->join('gen_bancos as ban', 'ban.cod_ban', '=', 'usr_app_referencias_bancarias.banco_id')
                        ->join('usr_app_tipos_cuenta_banco as tc', 'tc.id', '=', 'usr_app_referencias_bancarias.tipo_cuenta_id')
                        ->select(
                            'ban.cod_ban as banco_id',
                            'ban.nom_ban as banco',
                            'usr_app_referencias_bancarias.id',
                            'usr_app_referencias_bancarias.numero_cuenta',
                            'tc.id as tipo_cuenta_banco',
                            'tc.nombre as tipo_cuenta',
                            'usr_app_referencias_bancarias.sucursal',
                            'usr_app_referencias_bancarias.telefono',
                            'usr_app_referencias_bancarias.contacto',
                            'usr_app_referencias_bancarias.cliente_id'
                        )
                        ->where('usr_app_referencias_bancarias.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados5 as $item5) {
                        if ($item->id == $item5->cliente_id) {
                            $item->{'banco_rb' . $i} = $item5->banco;
                            $item->{'numero_cuenta_rb' . $i} = $item5->numero_cuenta;
                            $item->{'tipo_cuenta_rb' . $i} = $item5->tipo_cuenta;
                            $item->{'sucursal_rb' . $i} = $item5->sucursal;
                            $item->{'telefono_rb' . $i} = $item5->telefono;
                            $item->{'contacto_rb' . $i} = $item5->contacto;
                            $i++;
                        }
                    }

                    while ($i < 2) {
                        $item->{'banco_rb' . $i} = 'N/A';
                        $item->{'banco_rb' . $i} = 'N/A';
                        $item->{'numero_cuenta_rb' . $i} = 'N/A';
                        $item->{'tipo_cuenta_rb' . $i} = 'N/A';
                        $item->{'sucursal_rb' . $i} = 'N/A';
                        $item->{'telefono_rb' . $i} = 'N/A';
                        $item->{'contacto_rb' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados6 = DB::table('usr_app_referencias_comerciales')
                        ->select(
                            'razon_social as nombre',
                            'contacto',
                            'telefono',
                            'cliente_id',
                        )
                        ->where('usr_app_referencias_comerciales.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados6 as $item6) {
                        if ($item->id == $item6->cliente_id) {
                            $item->{'nombre_rc' . $i} = $item6->nombre;
                            $item->{'contacto_rc' . $i} = $item6->contacto;
                            $item->{'telefono_rc' . $i} = $item6->telefono;
                            $i++;
                        }
                    }

                    while ($i < 2) {
                        $item->{'nombre_rc' . $i} = 'N/A';
                        $item->{'contacto_rc' . $i} = 'N/A';
                        $item->{'telefono_rc' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados7 = DB::table('usr_app_personas_expuestas_politica')
                        ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_personas_expuestas_politica.tipo_identificacion_id')
                        ->select(
                            'usr_app_personas_expuestas_politica.nombre',
                            'usr_app_personas_expuestas_politica.numero_identificacion as identificacion',
                            'usr_app_personas_expuestas_politica.parentesco',
                            'ti.des_tip',
                            'usr_app_personas_expuestas_politica.cliente_id'
                        )
                        ->where('usr_app_personas_expuestas_politica.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados7 as $item7) {
                        if ($item->id == $item7->cliente_id) {
                            $item->{'nombre_pe' . $i} = $item7->nombre;
                            $item->{'tipo_identificacion_pe' . $i} = $item7->des_tip;
                            $item->{'identificacion_pe' . $i} = $item7->identificacion;
                            $item->{'parentesco_pe' . $i} = $item7->parentesco;
                            $i++;
                        }
                    }

                    while ($i < 10) {
                        $item->{'nombre_pe' . $i} = 'N/A';
                        $item->{'tipo_identificacion_pe' . $i} = 'N/A';
                        $item->{'identificacion_pe' . $i} = 'N/A';
                        $item->{'parentesco_pe' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;

                    $resultados8 = DB::table('usr_app_cargos')
                        ->select(
                            'id',
                            'nombre',
                            'riesgo_laboral_id',
                            'cliente_id'
                        )
                        ->where('usr_app_cargos.cliente_id', '=', $item->id)
                        ->get();

                    foreach ($resultados8 as $item8) {
                        if ($item->id == $item8->cliente_id) {
                            $item->{'nombre_cargo' . $i} = $item8->nombre;
                            $item->{'riesgo_laboral_cargo' . $i} = $item8->riesgo_laboral_id;
                            $i++;
                            // $resultados9 = DB::table('usr_app_cargos_examenes')
                            // ->select(
                            //     'id',
                            //     'nombre',
                            //     'riesgo_laboral_id',
                            //     // 'cliente_id'
                            // )
                            // ->where('usr_app_cargos_examenes.cargo_id', '=', $item8->id)
                            // ->get();

                            // return $resultados9;

                            // $resultados9 = DB::table('usr_app_cargos_requisitos')
                            // ->select(
                            //     'id',
                            //     'nombre',
                            //     'riesgo_laboral_id',
                            //     // 'cliente_id'
                            // )
                            // ->where('usr_app_cargos_requisitos.cargo_id', '=', $item8->id)
                            // ->get();

                        }
                    }

                    while ($i < 5) {
                        $item->{'nombre_cargo' . $i} = 'N/A';
                        $item->{'riesgo_laboral_cargo' . $i} = 'N/A';
                        $i++;
                    }
                    $i = 0;
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }


            return (new FormularioDDExport2($resultados1))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

        $resultados1 = DB::table('usr_app_clientes')
            ->join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
            ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
            ->join('usr_app_tipos_persona as tp', 'tp.id', '=', 'usr_app_clientes.tipo_persona_id')
            ->join('usr_app_operaciones as op', 'op.id', '=', 'usr_app_clientes.operacion_id')
            ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
            ->join('usr_app_estratos as est', 'est.id', '=', 'usr_app_clientes.estrato_id')
            ->join('usr_app_municipios as mun1', 'mun1.id', '=', 'usr_app_clientes.municipio_id')
            ->join('usr_app_municipios as mun2', 'mun2.id', '=', 'usr_app_clientes.municipio_prestacion_servicio_id')
            ->join('usr_app_departamentos as dep1', 'dep1.id', '=', 'mun1.departamento_id')
            ->join('usr_app_departamentos as dep2', 'dep2.id', '=', 'mun2.departamento_id')
            ->join('usr_app_paises as pais', 'pais.id', '=', 'dep1.pais_id')
            ->join('usr_app_paises as pais2', 'pais2.id', '=', 'dep2.pais_id')
            ->join('usr_app_sociedades_comerciales as sc', 'sc.id', '=', 'usr_app_clientes.sociedad_comercial_id')
            ->join('usr_app_jornadas_laborales as jl', 'jl.id', '=', 'usr_app_clientes.jornada_laboral_id')
            ->join('usr_app_rotaciones_personal as rp', 'rp.id', '=', 'usr_app_clientes.rotacion_personal_id')
            ->join('usr_app_riesgos_laborales as rl', 'rl.id', '=', 'usr_app_clientes.riesgo_cliente_id')
            ->join('gen_sucursal as scf', 'scf.cod_suc', '=', 'usr_app_clientes.sucursal_facturacion_id')
            ->join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
            ->join('usr_app_periodicidad_liquidacion_nominas as pl', 'pl.id', '=', 'usr_app_clientes.periodicidad_liquidacion_id')
            ->join('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
            ->join('gen_tipide as ti2', 'ti2.cod_tip', '=', 'con.tipo_identificacion_id')
            ->join('usr_app_datos_tesoreria as tes', 'tes.cliente_id', '=', 'usr_app_clientes.id')
            ->join('usr_app_datos_financieros as fin', 'fin.cliente_id', '=', 'usr_app_clientes.id')
            ->join('usr_app_operaciones_internacionales as opi', 'opi.cliente_id', '=', 'usr_app_clientes.id')
            ->join('usr_app_tipo_operaciones_internacionales as topi', 'topi.id', '=', 'opi.tipo_operaciones_id')
            ->join('usr_app_tipo_proveedor as tpro', 'tpro.id', '=', 'usr_app_clientes.tipo_proveedor_id')
            ->join('usr_app_tipo_cliente as tcli', 'tcli.id', '=', 'usr_app_clientes.tipo_cliente_id')
            ->select(
                'usr_app_clientes.id',
                'tcli.nombre as tipo_cliente',
                'tpro.nombre as tipo_proveedor',
                'op.nombre as tipo_operacion',
                'usr_app_clientes.contratacion_directa',
                'usr_app_clientes.atraccion_seleccion',
                'tp.nombre as tipo_persona',
                'ti.des_tip as tipo_identificacion',
                'usr_app_clientes.numero_identificacion',
                'usr_app_clientes.fecha_exp_documento',
                'usr_app_clientes.nit',
                'usr_app_clientes.digito_verificacion',
                'usr_app_clientes.razon_social',
                'usr_app_clientes.fecha_constitucion',
                'usr_app_clientes.numero_empleados',
                'cc.codigo as codigo_ciiu',
                'ac.codigo_actividad as codigo_actividad_ciiu',
                'est.nombre as estrato',
                'pais.nombre as pais',
                'dep1.nombre as departamento',
                'mun1.nombre as municipio',
                'usr_app_clientes.direccion_empresa',
                'usr_app_clientes.contacto_empresa',
                'usr_app_clientes.correo_empresa',
                'usr_app_clientes.telefono_empresa',
                'usr_app_clientes.celular_empresa',
                'sc.nombre as sociedad_comercial',
                'usr_app_clientes.otra',
                'pl.nombre as periodicidad_liquidacion',
                'usr_app_clientes.plazo_pago',
                'pais2.nombre as pais_prestacion_servicio',
                'dep2.nombre as departamento_prestacion_servicio',
                'mun2.nombre as municipio_prestacion_servicio',
                'usr_app_clientes.aiu_negociado',
                'ven.nom_ven as vendedor',
                'usr_app_clientes.acuerdo_comercial',
                'jl.nombre as jornada_laboral',
                'rp.nombre as rotacion_personal',
                'rl.nombre as riesgo_cliente',
                'usr_app_clientes.junta_directiva',
                'usr_app_clientes.responsable_inpuesto_ventas',
                'usr_app_clientes.correo_facturacion_electronica',
                'scf.nom_suc as sucursal_facturacion',
                'con.nombre as nombre_contador',
                'con.identificacion as identificacion_contador',
                'con.telefono as telefono_contador',
                'ti2.des_tip as tipo_identificacion_contador',
                'tes.nombre as nombre_tesorero',
                'tes.telefono as telefono_tesorero',
                'tes.correo as correo_tesorero',
                'fin.ingreso_mensual as ingreso_mensual',
                'fin.otros_ingresos as otros_ingresos',
                'fin.total_ingresos as total_ingresos',
                'fin.costos_gastos_mensual as costos_gastos_mensual',
                'fin.detalle_otros_ingresos as detalle_otros_ingresos',
                'fin.reintegro_costos_gastos as reintegro_costos_gastos',
                'fin.activos as activos',
                'fin.pasivos as pasivos',
                'fin.patrimonio as patrimonio',
                'usr_app_clientes.operaciones_internacionales',
                'topi.nombre as tipo_operacion_internacional',
                'usr_app_clientes.declaraciones_autirizaciones',
                'usr_app_clientes.tratamiento_datos_personales'
            )
            ->where('usr_app_clientes.' . $campo, $operador, $valor)
            ->get();

        $i = 0;
        foreach ($resultados1 as $item) {

            try {
                $resultados2 = DB::table('usr_app_accionistas')
                    ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_accionistas.tipo_identificacion_id')
                    ->select(
                        'usr_app_accionistas.id',
                        'usr_app_accionistas.identificacion',
                        'usr_app_accionistas.accionista as socio',
                        'usr_app_accionistas.participacion',
                        'ti.des_tip',
                        'usr_app_accionistas.cliente_id'
                    )
                    ->where('usr_app_accionistas.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados2 as $item2) {
                    if ($item->id == $item2->cliente_id) {
                        $item->{'socio' . $i} = $item2->socio;
                        $item->{'tipo_identificacion' . $i} = $item2->des_tip;
                        $item->{'identificacion' . $i} = $item2->identificacion;
                        $item->{'participacion' . $i} = $item2->participacion;
                        $i++;
                    }
                }
                while ($i < 5) {
                    $item->{'socio' . $i} = 'N/A';
                    $item->{'tipo_identificacion' . $i} = 'N/A';
                    $item->{'identificacion' . $i} = 'N/A';
                    $item->{'participacion' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;

                $resultados3 = DB::table('usr_app_representantes_legales')
                    ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_representantes_legales.tipo_identificacion_id')
                    ->join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_representantes_legales.municipio_expedicion_id')
                    ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                    ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
                    ->select(
                        'usr_app_representantes_legales.id',
                        'usr_app_representantes_legales.nombre',
                        'usr_app_representantes_legales.identificacion',
                        'usr_app_representantes_legales.correo_electronico as correo',
                        'usr_app_representantes_legales.telefono',
                        'ti.des_tip',
                        'mun.nombre as ciudad_expedicion',
                        'dep.nombre as departamento',
                        'pais.nombre as pais',
                        'usr_app_representantes_legales.cliente_id'
                    )
                    ->where('usr_app_representantes_legales.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados3 as $item3) {
                    if ($item->id == $item3->cliente_id) {
                        $item->{'nombre_rl' . $i} = $item3->nombre;
                        $item->{'des_tip_rl' . $i} = $item3->des_tip;
                        $item->{'identificacion_rl' . $i} = $item3->identificacion;
                        $item->{'telefono_rl' . $i} = $item3->telefono;
                        $item->{'correo_rl' . $i} = $item3->correo;
                        $item->{'pais_rl' . $i} = $item3->pais;
                        $item->{'departamento_rl' . $i} = $item3->departamento;
                        $item->{'ciudad_expedicion_rl' . $i} = $item3->ciudad_expedicion;
                        $i++;
                    }
                }
                while ($i < 5) {
                    $item->{'nombre_rl' . $i} = 'N/A';
                    $item->{'des_tip_rl' . $i} = 'N/A';
                    $item->{'identificacion_rl' . $i} = 'N/A';
                    $item->{'telefono_rl' . $i} = 'N/A';
                    $item->{'correo_rl' . $i} = 'N/A';
                    $item->{'pais_rl' . $i} = 'N/A';
                    $item->{'departamento_rl' . $i} = 'N/A';
                    $item->{'ciudad_expedicion_rl' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;

                $resultados4 = DB::table('usr_app_juntas_directivas')
                    ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_juntas_directivas.tipo_identificacion_id')
                    ->select(
                        'usr_app_juntas_directivas.id',
                        'usr_app_juntas_directivas.nombre',
                        'usr_app_juntas_directivas.identificacion',
                        'ti.des_tip',
                    )
                    ->where('usr_app_juntas_directivas.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados4 as $item4) {
                    if ($item->id == $item4->cliente_id) {
                        $item->{'nombre_jd' . $i} = $item4->nombre;
                        $item->{'tipo_identificacion_jd' . $i} = $item4->des_tip;
                        $item->{'identificacion_jd' . $i} = $item4->identificacion;
                        $i++;
                    }
                }
                while ($i < 5) {
                    $item->{'nombre_jd' . $i} = 'N/A';
                    $item->{'tipo_identificacion_jd' . $i} = 'N/A';
                    $item->{'identificacion_jd' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;

                $resultados5 = DB::table('usr_app_referencias_bancarias')
                    ->join('gen_bancos as ban', 'ban.cod_ban', '=', 'usr_app_referencias_bancarias.banco_id')
                    ->join('usr_app_tipos_cuenta_banco as tc', 'tc.id', '=', 'usr_app_referencias_bancarias.tipo_cuenta_id')
                    ->select(
                        'ban.cod_ban as banco_id',
                        'ban.nom_ban as banco',
                        'usr_app_referencias_bancarias.id',
                        'usr_app_referencias_bancarias.numero_cuenta',
                        'tc.id as tipo_cuenta_banco',
                        'tc.nombre as tipo_cuenta',
                        'usr_app_referencias_bancarias.sucursal',
                        'usr_app_referencias_bancarias.telefono',
                        'usr_app_referencias_bancarias.contacto',
                        'usr_app_referencias_bancarias.cliente_id'
                    )
                    ->where('usr_app_referencias_bancarias.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados5 as $item5) {
                    if ($item->id == $item5->cliente_id) {
                        $item->{'banco_rb' . $i} = $item5->banco;
                        $item->{'numero_cuenta_rb' . $i} = $item5->numero_cuenta;
                        $item->{'tipo_cuenta_rb' . $i} = $item5->tipo_cuenta;
                        $item->{'sucursal_rb' . $i} = $item5->sucursal;
                        $item->{'telefono_rb' . $i} = $item5->telefono;
                        $item->{'contacto_rb' . $i} = $item5->contacto;
                        $i++;
                    }
                }

                while ($i < 2) {
                    $item->{'banco_rb' . $i} = 'N/A';
                    $item->{'banco_rb' . $i} = 'N/A';
                    $item->{'numero_cuenta_rb' . $i} = 'N/A';
                    $item->{'tipo_cuenta_rb' . $i} = 'N/A';
                    $item->{'sucursal_rb' . $i} = 'N/A';
                    $item->{'telefono_rb' . $i} = 'N/A';
                    $item->{'contacto_rb' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;

                $resultados6 = DB::table('usr_app_referencias_comerciales')
                    ->select(
                        'razon_social as nombre',
                        'contacto',
                        'telefono',
                        'cliente_id',
                    )
                    ->where('usr_app_referencias_comerciales.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados6 as $item6) {
                    if ($item->id == $item6->cliente_id) {
                        $item->{'nombre_rc' . $i} = $item6->nombre;
                        $item->{'contacto_rc' . $i} = $item6->contacto;
                        $item->{'telefono_rc' . $i} = $item6->telefono;
                        $i++;
                    }
                }

                while ($i < 2) {
                    $item->{'nombre_rc' . $i} = 'N/A';
                    $item->{'contacto_rc' . $i} = 'N/A';
                    $item->{'telefono_rc' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;

                $resultados7 = DB::table('usr_app_personas_expuestas_politica')
                    ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_personas_expuestas_politica.tipo_identificacion_id')
                    ->select(
                        'usr_app_personas_expuestas_politica.nombre',
                        'usr_app_personas_expuestas_politica.numero_identificacion as identificacion',
                        'usr_app_personas_expuestas_politica.parentesco',
                        'ti.des_tip',
                        'usr_app_personas_expuestas_politica.cliente_id'
                    )
                    ->where('usr_app_personas_expuestas_politica.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados7 as $item7) {
                    if ($item->id == $item7->cliente_id) {
                        $item->{'nombre_pe' . $i} = $item7->nombre;
                        $item->{'tipo_identificacion_pe' . $i} = $item7->des_tip;
                        $item->{'identificacion_pe' . $i} = $item7->identificacion;
                        $item->{'parentesco_pe' . $i} = $item7->parentesco;
                        $i++;
                    }
                }

                while ($i < 10) {
                    $item->{'nombre_pe' . $i} = 'N/A';
                    $item->{'tipo_identificacion_pe' . $i} = 'N/A';
                    $item->{'identificacion_pe' . $i} = 'N/A';
                    $item->{'parentesco_pe' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;

                $resultados8 = DB::table('usr_app_cargos')
                    ->select(
                        'id',
                        'nombre',
                        'riesgo_laboral_id',
                        'cliente_id'
                    )
                    ->where('usr_app_cargos.cliente_id', '=', $item->id)
                    ->get();

                foreach ($resultados8 as $item8) {
                    if ($item->id == $item8->cliente_id) {
                        $item->{'nombre_cargo' . $i} = $item8->nombre;
                        $item->{'riesgo_laboral_cargo' . $i} = $item8->riesgo_laboral_id;
                        $i++;
                        // $resultados9 = DB::table('usr_app_cargos_examenes')
                        // ->select(
                        //     'id',
                        //     'nombre',
                        //     'riesgo_laboral_id',
                        //     // 'cliente_id'
                        // )
                        // ->where('usr_app_cargos_examenes.cargo_id', '=', $item8->id)
                        // ->get();

                        // return $resultados9;

                        // $resultados9 = DB::table('usr_app_cargos_requisitos')
                        // ->select(
                        //     'id',
                        //     'nombre',
                        //     'riesgo_laboral_id',
                        //     // 'cliente_id'
                        // )
                        // ->where('usr_app_cargos_requisitos.cargo_id', '=', $item8->id)
                        // ->get();

                    }
                }

                while ($i < 5) {
                    $item->{'nombre_cargo' . $i} = 'N/A';
                    $item->{'riesgo_laboral_cargo' . $i} = 'N/A';
                    $i++;
                }
                $i = 0;
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        return (new FormularioDDExport2($resultados1))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
