<?php

namespace App\Exports;

use App\Models\cliente;
use App\Models\OrigenFondo;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FormularioDDExport implements WithMultipleSheets
{
    use Exportable;

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Hoja 1
        $sheets[] = new class($this->id) implements FromQuery, WithTitle, ShouldAutoSize
        {
            protected $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function query()
            {
                $cliente = cliente::query()->join('usr_app_operaciones as op', 'op.id', '=', 'usr_app_clientes.operacion_id')
                    ->join('usr_app_tipo_cliente as tcli', 'tcli.id', '=', 'usr_app_clientes.tipo_cliente_id')
                    ->join('usr_app_tipos_persona as tp', 'tp.id', '=', 'usr_app_clientes.tipo_persona_id')
                    ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
                    ->join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
                    ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
                    ->join('usr_app_estratos as est', 'est.id', '=', 'usr_app_clientes.estrato_id')
                    ->join('usr_app_municipios as mun1', 'mun1.id', '=', 'usr_app_clientes.municipio_id')
                    ->join('usr_app_municipios as mun2', 'mun2.id', '=', 'usr_app_clientes.municipio_prestacion_servicio_id')
                    ->join('usr_app_departamentos as dep1', 'dep1.id', '=', 'mun1.departamento_id')
                    ->join('usr_app_departamentos as dep2', 'dep2.id', '=', 'mun2.departamento_id')
                    ->join('usr_app_paises as pais', 'pais.id', '=', 'dep1.pais_id')
                    ->join('usr_app_paises as pais2', 'pais2.id', '=', 'dep2.pais_id')
                    ->join('usr_app_sociedades_comerciales as sc', 'sc.id', '=', 'usr_app_clientes.sociedad_comercial_id')
                    ->join('usr_app_periodicidad_liquidacion_nominas as pl', 'pl.id', '=', 'usr_app_clientes.periodicidad_liquidacion_id')
                    ->join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                    ->join('usr_app_jornadas_laborales as jl', 'jl.id', '=', 'usr_app_clientes.jornada_laboral_id')
                    ->join('usr_app_rotaciones_personal as rp', 'rp.id', '=', 'usr_app_clientes.rotacion_personal_id')
                    // ->join('gen_sucursal as scf', 'scf.cod_suc', '=', 'usr_app_clientes.sucursal_facturacion_id')
                    ->join('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
                    ->join('gen_tipide as ti2', 'ti2.cod_tip', '=', 'con.tipo_identificacion_id')
                    ->join('usr_app_datos_tesoreria as tes', 'tes.cliente_id', '=', 'usr_app_clientes.id')
                    ->join('usr_app_datos_financieros as fin', 'fin.cliente_id', '=', 'usr_app_clientes.id')
                    ->join('usr_app_operaciones_internacionales as opi', 'opi.cliente_id', '=', 'usr_app_clientes.id')
                    ->where('usr_app_clientes.id', $this->id)
                    ->select(
                        // 'usr_app_clientes.operacion_id',
                        'tcli.nombre as tipo_cliente',
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
                        'ac.descripcion as actividad_ciiu_descripcion',
                        'est.nombre as estrato',
                        'mun1.nombre as municipio',
                        'dep1.nombre as departamento',
                        'pais.nombre as pais',
                        'usr_app_clientes.direccion_empresa',
                        'usr_app_clientes.contacto_empresa',
                        'usr_app_clientes.correo_empresa',
                        'usr_app_clientes.telefono_empresa',
                        'usr_app_clientes.celular_empresa',
                        'sc.nombre as sociedad_comercial',
                        'usr_app_clientes.otra',
                        'pl.nombre as periodicidad_liquidacion',
                        'usr_app_clientes.plazo_pago',
                        'mun2.nombre as municipio_prestacion_servicio',
                        'dep2.nombre as departamento_prestacion_servicio',
                        'pais2.nombre as pais_prestacion_servicio',
                        'usr_app_clientes.aiu_negociado',
                        'ven.nom_ven as vendedor',
                        'usr_app_clientes.acuerdo_comercial',
                        'jl.nombre as jornada_laboral',
                        'rp.nombre as rotacion_personal',
                        'usr_app_clientes.junta_directiva',
                        // 'usr_app_clientes.responsable_inpuesto_ventas',
                        // 'usr_app_clientes.correo_facturacion_electronica',
                        // 'scf.nom_suc as sucursal_facturacion',
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
                        'ti.des_tip as tipo_operacion_internacional',
                        'usr_app_clientes.declaraciones_autirizaciones',
                    );

                $origenFondo = OrigenFondo::join('usr_app_tipos_origen_fondos as of', 'of.id', '=', 'usr_app_origenes_fondos.tipo_origen_fondos_id')
                    ->join('usr_app_tipos_origen_medios as om', 'om.id', '=', 'usr_app_origenes_fondos.tipo_origen_medios_id')
                    ->join('usr_app_tipos_origen_medios as om2', 'om2.id', '=', 'usr_app_origenes_fondos.tipo_origen_medios2_id')
                    ->where('cliente_id',$this->id)
                    ->select(
                        // 'tipo_origen_fondos_id',
                        'otro_origen',
                        // 'tipo_origen_medios_id',
                        // 'tipo_origen_medios2_id',
                        'alto_manejo_efectivo',
                        'of.nombre as origen_fondos',
                        'om.nombre as origen_medios',
                        'om2.nombre as origen_medios2',
                        'usr_app_origenes_fondos.alto_manejo_efectivo',
                    );

                    // ->first();

                // //     foreach ($origenFondo as $item) {
                //         array_push($cliente,  $origenFondo);
                // //     }
            
                return $cliente;
            }

            public function title(): string
            {
                return 'Datos cliente';
            }
        };

        // Hoja 2
        $sheets[] = new class($this->id) implements FromQuery, WithTitle, ShouldAutoSize
        {
            protected $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function query()
            {
                return cliente::query()->join('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
                    ->where('usr_app_clientes.id', $this->id)
                    ->select(
                        'con.nombre as nombre_contador',
                        'con.identificacion as identificacion_contador',
                        'con.telefono as telefono_contador',
                    );
            }

            public function title(): string
            {
                return 'Datos contador';
            }
        };

        // Agrega más hojas según tus necesidades

        return $sheets;
    }
}
