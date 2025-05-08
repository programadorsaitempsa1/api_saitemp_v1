<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Controllers\ContratoController;
use App\Events\NotificacionSeiya;
use App\Http\Controllers\HorarioLaboralController;
use App\Http\Controllers\EstadosFirmaController;
use App\Models\ResponsablesEstadosModel;
use App\Models\ClientesSeguimientoGuardado;
use App\Models\HistoricoContratosDDModel;
use App\Models\ClientesSeguimientoEstado;
use App\Models\UsuarioPermiso;
use App\Models\cliente;
use App\Models\ActividadCiiu;
use App\Models\Cargos;
use App\Models\Accionista;
use App\Models\RepresentanteLegal;
use App\Models\MiembroJunta;
use App\Models\CalidadTributaria;
use App\Models\Contador;
use App\Models\Tesorero;
use App\Models\DatoFinanciero;
use App\Models\OperacionIternacional;
use App\Models\ReferenciaBancaria;
use App\Models\ReferenciaComercial;
use App\Models\CargoRequisito;
use App\Models\CargoExamen;
use App\Models\Documento;
use App\Models\DocumentoCliente;
use App\Models\PersonasExpuestas;
use App\Models\OrigenFondo;
use App\Models\CargoCliente;
use App\Models\Cargo2;
use App\Models\Cargo2Examen;
use App\Models\Cargo2Recomendacion;
use App\Models\ClienteEpp;
use App\Models\RegistroCambio;
use App\Models\ClienteOtroSi;
use App\Models\ClienteConvenioBanco;
use App\Models\ClienteTipoContrato;
use App\Models\ClienteLaboratorio;
use App\Models\HistoricoOperacionesDDModel;
use App\Models\VersionFormularioDD;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\enviarCorreoDDController;
use App\Models\NovedadesDD;
use App\Models\HistoricoProfesionalesModel;
use App\Models\VersionTablasAndroid;
use App\Http\Controllers\HistoricoProfesionalesController;

// use App\Events\EventoPrueba2;





class formularioDebidaDiligenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = cliente::select()
            ->get();
        return response()->json($result);
    }

    public function empresascliente()
    {
        $result = cliente::select(
            'id',
            'razon_social as nombre',
            DB::raw('COALESCE(nit, numero_identificacion) as nit')
        )->get();

        return response()->json($result);
    }
    public function consultacliente($cantidad)
    {
        $permisos = $this->validaPermiso();

        $user = auth()->user();
        $year_actual = date('Y');

        $result = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
            ->leftJoin('usr_app_estados_firma as estf', 'estf.id', '=', 'usr_app_clientes.estado_firma_id')
            ->leftJoin(
                DB::raw('(SELECT cliente_id, estado_contrato 
                             FROM usr_app_historico_contratos_dd 
                             WHERE activo = 1) AS contratos'),
                'contratos.cliente_id',
                '=',
                'usr_app_clientes.id'
            )
            /*   ->whereYear('usr_app_clientes.created_at', $year_actual) */
            ->when(!in_array('39', $permisos), function ($query) use ($user) {
                return $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('usr_app_clientes.vendedor_id', $user->vendedor_id)
                        ->orWhere('usr_app_clientes.responsable_id', $user->id);
                });
            })
            ->select(
                'usr_app_clientes.id',
                DB::raw('COALESCE(CONVERT(VARCHAR, usr_app_clientes.numero_radicado), CONVERT(VARCHAR, usr_app_clientes.id)) AS numero_radicado'),
                'usr_app_clientes.razon_social',
                'usr_app_clientes.numero_identificacion',
                'usr_app_clientes.nit',
                'ven.nom_ven as vendedor',
                'usr_app_clientes.telefono_empresa',
                'usr_app_clientes.created_at',
                'estf.nombre as nombre_estado_firma',
                'contratos.estado_contrato', // Estado del contrato como propiedad directa
                'estf.color as color_estado_firma',
                'usr_app_clientes.responsable_corregir',
                'usr_app_clientes.responsable',
                'estf.id as estado_firma_id',
            )
            ->orderby('usr_app_clientes.created_at', 'DESC')
            ->orderby('usr_app_clientes.numero_radicado', 'DESC')
            ->paginate($cantidad);

        return response()->json($result);
    }

    public function clientesactivos()
    {
        $result = cliente::select()
            ->get();
        return response()->json(count($result));
    }

    public function existbyid($id, $tipo_id)
    {
        if ($tipo_id == 1) {
            $result = Cliente::where('usr_app_clientes.numero_identificacion', '=', $id)
                ->select(
                    'numero_identificacion'
                )
                ->first();
            return $result;
        } else if ($tipo_id == 2) {
            $result = Cliente::where('usr_app_clientes.nit', '=', $id)
                ->select(
                    'nit',
                )
                ->first();
            return $result;
        }
    }


    public function getbyid($id, $asJson = true)
    {
        try {
            $result = Cliente::leftJoin('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
                ->leftJoin('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
                ->leftJoin('usr_app_tipos_persona as tp', 'tp.id', '=', 'usr_app_clientes.tipo_persona_id')
                ->leftJoin('usr_app_operaciones as op', 'op.id', '=', 'usr_app_clientes.operacion_id')
                ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_clientes.tipo_identificacion_id')
                ->leftJoin('usr_app_estratos as est', 'est.id', '=', 'usr_app_clientes.estrato_id')
                ->leftJoin('usr_app_municipios as mun1', 'mun1.id', '=', 'usr_app_clientes.municipio_id')
                ->leftJoin('usr_app_municipios as mun2', 'mun2.id', '=', 'usr_app_clientes.municipio_prestacion_servicio_id')
                ->leftJoin('usr_app_municipios as mun3', 'mun3.id', '=', 'usr_app_clientes.municipio_rut_id')
                ->leftJoin('usr_app_departamentos as dep1', 'dep1.id', '=', 'mun1.departamento_id')
                ->leftJoin('usr_app_departamentos as dep2', 'dep2.id', '=', 'mun2.departamento_id')
                ->leftJoin('usr_app_departamentos as dep3', 'dep3.id', '=', 'mun3.departamento_id')
                ->leftJoin('usr_app_paises as pais', 'pais.id', '=', 'dep1.pais_id')
                ->leftJoin('usr_app_paises as pais2', 'pais2.id', '=', 'dep2.pais_id')
                ->leftJoin('usr_app_sociedades_comerciales as sc', 'sc.id', '=', 'usr_app_clientes.sociedad_comercial_id')
                ->leftJoin('usr_app_jornadas_laborales as jl', 'jl.id', '=', 'usr_app_clientes.jornada_laboral_id')
                ->leftJoin('usr_app_rotaciones_personal as rp', 'rp.id', '=', 'usr_app_clientes.rotacion_personal_id')
                ->leftJoin('usr_app_riesgos_laborales as rl', 'rl.id', '=', 'usr_app_clientes.riesgo_cliente_id')
                ->leftJoin('gen_sucursal as scf', 'scf.cod_suc', '=', 'usr_app_clientes.sucursal_facturacion_id')
                ->leftJoin('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->leftJoin('usr_app_periodicidad_liquidacion_nominas as pl', 'pl.id', '=', 'usr_app_clientes.periodicidad_liquidacion_id')
                ->leftJoin('usr_app_datos_contador as con', 'con.cliente_id', '=', 'usr_app_clientes.id')
                ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'con.tipo_identificacion_id')
                ->leftJoin('usr_app_datos_tesoreria as tes', 'tes.cliente_id', '=', 'usr_app_clientes.id')
                ->leftJoin('usr_app_datos_financieros as fin', 'fin.cliente_id', '=', 'usr_app_clientes.id')
                ->leftJoin('usr_app_operaciones_internacionales as opi', 'opi.cliente_id', '=', 'usr_app_clientes.id')
                ->leftJoin('usr_app_tipo_operaciones_internacionales as topi', 'topi.id', '=', 'opi.tipo_operaciones_id')
                ->leftJoin('usr_app_tipo_proveedor as tpro', 'tpro.id', '=', 'usr_app_clientes.tipo_proveedor_id')
                ->leftJoin('usr_app_tipo_cliente as tcli', 'tcli.id', '=', 'usr_app_clientes.tipo_cliente_id')
                ->leftJoin('usr_app_estados_firma as estf', 'estf.id', '=', 'usr_app_clientes.estado_firma_id')
                ->leftJoin('usr_app_usuarios as usuario', 'usuario.id', '=', 'usr_app_clientes.usuario_corregir_id')
                ->leftJoin('usr_app_observacion_estado as novedad', 'novedad.id', '=', 'usr_app_clientes.novedad_servicio')
                ->select(
                    DB::raw('COALESCE(CONVERT(VARCHAR, usr_app_clientes.numero_radicado), CONVERT(VARCHAR, usr_app_clientes.id)) AS numero_radicado'),
                    'ac.codigo_actividad as codigo_actividad_ciiu',
                    'ac.id as codigo_actividad_ciiu_id',
                    'cc.codigo as codigo_ciiu',
                    'cc.id as codigo_ciiu_id',
                    'ac.descripcion as actividad_ciiu_descripcion',
                    'tp.nombre as tipo_persona',
                    'usr_app_clientes.tipo_persona_id',
                    'op.nombre as tipo_operacion',
                    'usr_app_clientes.operacion_id',
                    'ti.des_tip as tipo_identificacion',
                    'usr_app_clientes.tipo_identificacion_id',
                    'usr_app_clientes.contratacion_directa',
                    'usr_app_clientes.atraccion_seleccion',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.fecha_exp_documento',
                    'usr_app_clientes.nit',
                    'usr_app_clientes.digito_verificacion',
                    'usr_app_clientes.razon_social',
                    'usr_app_clientes.fecha_constitucion',
                    'usr_app_clientes.junta_directiva',
                    'est.nombre as estrato',
                    'est.id as estrato_id',
                    'mun1.nombre as municipio',
                    'mun1.id as municipio_id',
                    'mun2.nombre as municipio_prestacion_servicio',
                    'mun2.id as municipio_prestacion_servicio_id',
                    'mun3.nombre as municipio_rut',
                    'mun3.id as municipio_rut_id',
                    'dep1.nombre as departamento',
                    'dep1.id as departamento_id',
                    'dep2.nombre as departamento_prestacion_servicio',
                    'dep2.id as departamento_prestacion_servicio_id',
                    'dep3.nombre as departamento_rut',
                    'dep3.id as departamento_rut_id',
                    'pais.nombre as pais',
                    'pais.id as pais_id',
                    'pais2.nombre as pais_prestacion_servicio',
                    'pais2.id as pais_prestacion_servicio_id',
                    'usr_app_clientes.direccion_empresa',
                    'usr_app_clientes.contacto_empresa',
                    'usr_app_clientes.correo_empresa',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.celular_empresa',
                    'sc.nombre as sociedad_comercial',
                    'sc.id as sociedad_comercial_id',
                    'usr_app_clientes.otra',
                    'usr_app_clientes.aiu_negociado',
                    'usr_app_clientes.plazo_pago',
                    'usr_app_clientes.acuerdo_comercial',
                    'usr_app_clientes.numero_empleados',
                    'jl.nombre as jornada_laboral',
                    'jl.id as jornada_laboral_id',
                    'rp.nombre as rotacion_personal',
                    'rp.id as rotacion_personal_id',
                    'rl.nombre as riesgo_cliente',
                    'rl.id as riesgo_cliente_id',
                    'usr_app_clientes.responsable_inpuesto_ventas',
                    'usr_app_clientes.correo_facturacion_electronica',
                    'usr_app_clientes.declaraciones_autorizaciones',
                    'usr_app_clientes.tratamiento_datos_personales',
                    'usr_app_clientes.operaciones_internacionales',
                    'scf.nom_suc as sucursal_facturacion',
                    'scf.cod_suc as sucursal_facturacion_id',
                    'ven.nom_ven as vendedor',
                    'ven.cod_ven as vendedor_id',
                    'pl.id as periodicidad_liquidacion_id',
                    'pl.nombre as periodicidad_liquidacion',
                    'con.id as contador_id',
                    'con.nombre as nombre_contador',
                    'con.identificacion as identificacion_contador',
                    'con.telefono as telefono_contador',
                    'con.tipo_identificacion_id as tipo_identificacion_id_contador',
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
                    'opi.tipo_operaciones_id as tipo_operacion_internacional_id',
                    'topi.nombre as tipo_operacion_internacional',
                    'tpro.nombre as tipo_proveedor',
                    'usr_app_clientes.tipo_proveedor_id as tipo_proveedor_id',
                    'tcli.nombre as tipo_cliente',
                    'usr_app_clientes.tipo_cliente_id as tipo_cliente_id',
                    'usr_app_clientes.empresa_extranjera',
                    'usr_app_clientes.empresa_en_exterior',
                    'usr_app_clientes.vinculos_empresa',
                    'usr_app_clientes.numero_empleados_directos',
                    'usr_app_clientes.vinculado_empresa_temporal',
                    'usr_app_clientes.visita_presencial',
                    'usr_app_clientes.facturacion_contacto',
                    'usr_app_clientes.facturacion_cargo',
                    'usr_app_clientes.facturacion_telefono',
                    'usr_app_clientes.facturacion_celular',
                    'usr_app_clientes.facturacion_correo',
                    'usr_app_clientes.facturacion_factura_unica',
                    'usr_app_clientes.facturacion_fecha_corte',
                    'usr_app_clientes.facturacion_encargado_factura',
                    'usr_app_clientes.requiere_anexo_factura',
                    'usr_app_clientes.trabajo_alto_riesgo',
                    'usr_app_clientes.accidentalidad',
                    'usr_app_clientes.encargado_sst',
                    'usr_app_clientes.nombre_encargado_sst',
                    'usr_app_clientes.cargo_encargado_sst',
                    'usr_app_clientes.induccion_entrenamiento',
                    'usr_app_clientes.entrega_dotacion',
                    'usr_app_clientes.evaluado_arl',
                    'usr_app_clientes.entrega_epp',
                    'usr_app_clientes.contratacion_contacto',
                    'usr_app_clientes.contratacion_cargo',
                    'usr_app_clientes.contratacion_telefono',
                    'usr_app_clientes.contratacion_celular',
                    'usr_app_clientes.contratacion_correo',
                    'usr_app_clientes.contratacion_hora_ingreso',
                    'usr_app_clientes.contratacion_manipulacion_alimentos',
                    'usr_app_clientes.contratacion_hora_confirmacion',
                    'usr_app_clientes.contratacion_tallas_uniforme',
                    'usr_app_clientes.contratacion_suministra_transporte',
                    'usr_app_clientes.contratacion_suministra_alimentacion',
                    'usr_app_clientes.contratacion_pago_efectivo',
                    'usr_app_clientes.contratacion_carnet_corporativo',
                    'usr_app_clientes.contratacion_pagos_31',
                    'usr_app_clientes.contratacion_observacion',
                    'usr_app_clientes.responsable_id',
                    'usr_app_clientes.responsable',
                    'usr_app_clientes.estado_firma_id',
                    'estf.nombre as nombre_estado_firma',
                    'estf.posicion as posicion_estado_firma',
                    'usr_app_clientes.novedad_servicio',
                    'usr_app_clientes.afectacion_servicio',
                    'usr_app_clientes.usuario_corregir_id',
                    'usr_app_clientes.dirección_rut',
                    'usr_app_clientes.responsable_corregir',
                    'novedad.nombre as nombre_novedad_servicio',
                    DB::raw("CONCAT(usuario.nombres,' ',usuario.apellidos)  AS nombre_usuario_corregir"),

                )
                ->where('usr_app_clientes.id', '=', $id)
                ->first();

            $historico_profesionales_controller = new HistoricoProfesionalesController;
            $historico = $historico_profesionales_controller->byClienteId($id, false);
            $result['historico_profesionales'] = $historico;

            $seguimiento = RegistroCambio::join('usr_app_clientes as cli', 'cli.id', 'usr_app_registro_cambios.cliente_id')
                ->select(
                    'cli.razon_social',
                    'cli.numero_radicado',
                    'usr_app_registro_cambios.solicitante',
                    'usr_app_registro_cambios.autoriza',
                    'usr_app_registro_cambios.actualiza',
                    'usr_app_registro_cambios.observaciones',
                    'usr_app_registro_cambios.cliente_id as cliente',
                    'usr_app_registro_cambios.estado',
                    'usr_app_registro_cambios.updated_at'
                )
                ->where('usr_app_registro_cambios.cliente_id', $id)
                ->orderby('usr_app_registro_cambios.id', 'DESC')
                ->get();
            $result['seguimiento'] = $seguimiento;

            /*  $seguimiento = ClientesSeguimientoGuardado::join('usr_app_estados_firma as ei', 'ei.id', '=', 'usr_app_clientes_seguimiento_guardado.estado_firma_id')
                ->where('usr_app_clientes_seguimiento_guardado.cliente_id', $id)
                ->select(
                    'usr_app_clientes_seguimiento_guardado.usuario',
                    'ei.nombre as estado',
                    'usr_app_clientes_seguimiento_guardado.created_at',

                )
                ->orderby('usr_app_clientes_seguimiento_guardado.id', 'desc')
                ->get();
            $result['seguimiento'] = $seguimiento; */

            $novedades = NovedadesDD::join('usr_app_usuarios as usuario', 'usuario.id', 'usr_app_novedades_dd.usuario_corrige')
                ->where('usr_app_novedades_dd.registro_cliente_id', $id)
                ->select(
                    'usr_app_novedades_dd.registro_cliente_id',
                    'usr_app_novedades_dd.observaciones',
                    'usr_app_novedades_dd.usuario_guarda',
                    'usr_app_novedades_dd.usuario_corrige',
                    'usr_app_novedades_dd.created_at',
                    DB::raw("CONCAT(usuario.nombres,' ',usuario.apellidos)  AS nombre_usuario_corrige")
                )
                ->get();
            $result['novedades'] = $novedades;

            $contrato = HistoricoContratosDDModel::join('usr_app_usuarios as usuario', 'usuario.id', '=', 'usr_app_historico_contratos_dd.usuario_envia')
                ->where('usr_app_historico_contratos_dd.cliente_id', $id)->where('usr_app_historico_contratos_dd.activo', '=', 1)
                ->select(
                    'usr_app_historico_contratos_dd.cliente_id',
                    'usr_app_historico_contratos_dd.firmado_cliente',
                    'usr_app_historico_contratos_dd.firmado_empresa',
                    'usr_app_historico_contratos_dd.ruta_contrato',
                    'usr_app_historico_contratos_dd.contrato_firma_id',
                    'usr_app_historico_contratos_dd.transaccion_id',
                    'usr_app_historico_contratos_dd.correo_enviado_cliente',
                    'usr_app_historico_contratos_dd.correo_enviado_empresa',
                    'usr_app_historico_contratos_dd.activo',
                    'usr_app_historico_contratos_dd.estado_contrato',
                    'usr_app_historico_contratos_dd.usuario_envia',
                    'usr_app_historico_contratos_dd.created_at',
                    DB::raw("CONCAT(usuario.nombres,' ',usuario.apellidos)  AS nombre_usuario_envia"),
                )
                ->get();
            $result['contrato'] = $contrato;
            $seguimiento_estados = ClientesSeguimientoEstado::join('usr_app_estados_firma as ei', 'ei.id', '=', 'usr_app_clientes_seguimiento_estado.estados_firma_inicial')
                ->join('usr_app_estados_firma as ef', 'ef.id', '=', 'usr_app_clientes_seguimiento_estado.estados_firma_final')
                ->where('usr_app_clientes_seguimiento_estado.cliente_id', $id)
                ->select(
                    'usr_app_clientes_seguimiento_estado.responsable_inicial',
                    'usr_app_clientes_seguimiento_estado.responsable_final',
                    'usr_app_clientes_seguimiento_estado.oportuno',
                    'ei.nombre as estados_firma_inicial',
                    'ef.nombre as estados_firma_final',
                    'usr_app_clientes_seguimiento_estado.actualiza_registro',
                    'usr_app_clientes_seguimiento_estado.created_at',
                    'usr_app_clientes_seguimiento_estado.updated_at',
                )
                ->orderby('usr_app_clientes_seguimiento_estado.id', 'desc')
                ->get();
            $result['seguimiento_estados'] = $seguimiento_estados;

            $cargos = Cargos::join('usr_app_riesgos_laborales as rl2', 'rl2.id', '=', 'usr_app_cargos.riesgo_laboral_id')
                ->join('usr_app_cargos_requisitos as cr', 'cr.cargo_id', '=', 'usr_app_cargos.id')
                ->join('usr_app_requisitos as requ', 'requ.id', '=', 'cr.requisito_id')
                ->join('usr_app_cargos_examenes as cx', 'cx.cargo_id', '=', 'usr_app_cargos.id')
                ->join('usr_app_examenes as exam', 'exam.id', '=', 'cx.examen_id')
                ->select(
                    'usr_app_cargos.id as id_cargo',
                    'usr_app_cargos.nombre as cargo',
                    'usr_app_cargos.riesgo_laboral_id',
                    'rl2.nombre as riesgo_laboral',
                    'cr.requisito_id',
                    'requ.nombre as requisito',
                    'cx.examen_id',
                    'exam.nombre as examen',
                )
                ->where('cliente_id', '=', $id)
                ->distinct('requ.nombre as requisito')
                ->get();

            // Array para almacenar los resultados
            $resultados = [];

            // Recorrer el array original
            foreach ($cargos as $objeto) {
                // Extraer los datos del objeto
                $idCargo = $objeto['id_cargo'];
                $cargo = $objeto['cargo'];
                $idRiesgoLaboral = $objeto['riesgo_laboral_id'];
                $riesgoLaboral = $objeto['riesgo_laboral'];
                $idRequisito = $objeto['requisito_id'];
                $requisito = $objeto['requisito'];
                $idExamen = $objeto['examen_id'];
                $examen = $objeto['examen'];

                // Verificar si el cargo ya existe en los resultados
                if (!isset($resultados[$idCargo])) {
                    // Si no existe, crear un nuevo objeto para el cargo
                    $resultados[$idCargo] = [
                        'id_cargo' => $idCargo,
                        'cargo' => $cargo,
                        'riesgo_laboral_id' => $idRiesgoLaboral,
                        'riesgo_laboral' => $riesgoLaboral,
                        'examenes' => [],
                        'requisitos' => [],
                    ];
                }

                // Verificar si el examen ya existe en los resultados del cargo
                if (!in_array($idExamen, array_column($resultados[$idCargo]['examenes'], 'id'))) {
                    $resultados[$idCargo]['examenes'][] = [
                        'id' => $idExamen,
                        'nombre' => $examen,
                    ];
                }

                // Verificar si el requisito ya existe en los resultados del cargo
                if (!in_array($idRequisito, array_column($resultados[$idCargo]['requisitos'], 'id'))) {
                    $resultados[$idCargo]['requisitos'][] = [
                        'id' => $idRequisito,
                        'nombre' => $requisito,
                    ];
                }
            }

            // Resultado: Array final con cargos, exámenes y requisitos sin duplicados
            $resultados = array_values($resultados);
            $result['cargos'] = $resultados;


            // **************************************************************************************
            $cargos = Cargo2::join('usr_app_riesgos_laborales as rl2', 'rl2.id', '=', 'usr_app_cargos2.riesgo_laboral_id')
                ->join('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_cargos2.cargo_id')
                ->join('usr_app_subcategoria_cargos as sc', 'sc.id', '=', 'lc.subcategoria_cargo_id')
                ->join('usr_app_categoria_cargos as cc', 'cc.id', '=', 'sc.categoria_cargo_id')
                ->join('usr_app_cargos2_recomendaciones as cr', 'cr.cargo_id', '=', 'usr_app_cargos2.id')
                ->join('usr_app_lista_recomendaciones as recom', 'recom.id', '=', 'cr.recomendacion_id')
                ->join('usr_app_cargos2_examenes as cx', 'cx.cargo_id', '=', 'usr_app_cargos2.id')
                ->join('usr_app_lista_examenes as exam', 'exam.id', '=', 'cx.examen_id')
                ->select(
                    'usr_app_cargos2.cargo_id as id_cargo',
                    'lc.nombre as cargo',
                    'lc.subcategoria_cargo_id as categoria_cargo_id',
                    'sc.categoria_cargo_id as tipo_cargo_id',
                    'sc.nombre as categoria',
                    'cc.nombre as tipo_cargo',
                    'usr_app_cargos2.funcion_cargo as funcion_cargo',
                    'usr_app_cargos2.riesgo_laboral_id',
                    'rl2.nombre as riesgo_laboral',
                    'cr.recomendacion_id',
                    'recom.recomendacion1 as recomendacion1',
                    'recom.recomendacion2 as recomendacion2',
                    'cx.examen_id',
                    'exam.nombre as examen',

                )
                ->where('cliente_id', '=', $id)
                ->distinct('exam.nombre as examen')
                ->get();

            // Array para almacenar los resultados
            $resultados = [];

            // Recorrer el array original
            foreach ($cargos as $objeto) {
                // Extraer los datos del objeto

                $funcion_cargo = $objeto['funcion_cargo'];
                $idCargo = $objeto['id_cargo'];
                $cargo = $objeto['cargo'];
                $idSubcategoria = $objeto['categoria_cargo_id'];
                $subcategoria = $objeto['categoria'];
                $idCategoria = $objeto['tipo_cargo_id'];
                $categoria = $objeto['tipo_cargo'];
                $idRiesgoLaboral = $objeto['riesgo_laboral_id'];
                $riesgoLaboral = $objeto['riesgo_laboral'];
                $idRequisito = $objeto['recomendacion_id'];
                $recomendacion1 = $objeto['recomendacion1'];
                $recomendacion2 = $objeto['recomendacion2'];
                $idExamen = $objeto['examen_id'];
                $examen = $objeto['examen'];

                // Verificar si el cargo ya existe en los resultados
                if (!isset($resultados[$idCargo])) {
                    // Si no existe, crear un nuevo objeto para el cargo
                    $resultados[$idCargo] = [
                        'id_cargo' => $idCargo,
                        'cargo' => $cargo,
                        'riesgo_laboral_id' => $idRiesgoLaboral,
                        'riesgo_laboral' => $riesgoLaboral,
                        'examenes' => [],
                        'recomendaciones' => [],
                        'categoria_cargo_id' => $idSubcategoria,
                        'categoria' => $subcategoria,
                        'tipo_cargo_id' => $idCategoria,
                        'tipo_cargo' => $categoria,
                        'funcion_cargo' => $funcion_cargo
                    ];
                }

                // Verificar si el examen ya existe en los resultados del cargo
                if (!in_array($idExamen, array_column($resultados[$idCargo]['examenes'], 'id'))) {
                    $resultados[$idCargo]['examenes'][] = [
                        'id' => $idExamen,
                        'nombre' => $examen,
                    ];
                }

                // Verificar si el requisito ya existe en los resultados del cargo
                if (!in_array($idRequisito, array_column($resultados[$idCargo]['recomendaciones'], 'id'))) {
                    $resultados[$idCargo]['recomendaciones'][] = [
                        'id' => $idRequisito,
                        'recomendacion1' => $recomendacion1,
                        'recomendacion2' => $recomendacion2,
                    ];
                }
            }

            // Resultado: Array final con cargos, exámenes y requisitos sin duplicados
            $resultados = array_values($resultados);
            $result['cargos2'] = $resultados;
            // **************************************************************************************

            $clientes_epps = ClienteEpp::where('cliente_id', $id)->leftjoin('usr_app_elementos_pp as epp', DB::raw('epp.id'), '=', DB::raw('usr_app_cliente_epp.epp_id + 1'))
                ->select(
                    'epp_id',
                    'epp.nombre as nombre'
                )
                ->get();
            $result['clientes_epps'] = $clientes_epps;

            $accionistas = Accionista::join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_accionistas.tipo_identificacion_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_accionistas.id',
                    'usr_app_accionistas.tipo_identificacion_id',
                    'usr_app_accionistas.identificacion',
                    'usr_app_accionistas.accionista as socio',
                    'usr_app_accionistas.participacion',
                    'ti.des_tip',
                )
                ->get();
            $result['accionistas'] = $accionistas;


            $RepresentanteLegal = RepresentanteLegal::join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_representantes_legales.tipo_identificacion_id')
                ->join('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_representantes_legales.municipio_expedicion_id')
                ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_representantes_legales.id',
                    'usr_app_representantes_legales.nombre',
                    'usr_app_representantes_legales.identificacion',
                    'usr_app_representantes_legales.correo_electronico as correo',
                    'usr_app_representantes_legales.telefono',
                    'usr_app_representantes_legales.tipo_identificacion_id as tipo_identificacion',
                    'ti.des_tip',
                    'mun.nombre as ciudad_expedicion',
                    'mun.id as municipio_id',
                    'dep.nombre as departamento',
                    'dep.id as departamento_id',
                    'pais.nombre as pais',
                    'pais.id as pais_id',
                )
                ->get();
            $result['representantes_legales'] = $RepresentanteLegal;

            $miembrosjunta = MiembroJunta::join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_juntas_directivas.tipo_identificacion_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_juntas_directivas.id',
                    'usr_app_juntas_directivas.nombre',
                    'usr_app_juntas_directivas.tipo_identificacion_id',
                    'usr_app_juntas_directivas.identificacion',
                    'ti.des_tip',
                )
                ->get();

            $result['junta_directiva'] = $miembrosjunta;

            $calidadTributaria = CalidadTributaria::where('cliente_id', '=', $id)
                ->select(
                    'usr_app_calidad_tributaria.id',
                    'usr_app_calidad_tributaria.gran_contribuyente',
                    'usr_app_calidad_tributaria.resolucion_gran_contribuyente',
                    'usr_app_calidad_tributaria.fecha_gran_contribuyente',
                    'usr_app_calidad_tributaria.auto_retenedor',
                    'usr_app_calidad_tributaria.resolucion_auto_retenedor',
                    'usr_app_calidad_tributaria.fecha_auto_retenedor',
                    'usr_app_calidad_tributaria.exento_impuesto_rent',
                    'usr_app_calidad_tributaria.resolucion_exento_impuesto_rent',
                    'usr_app_calidad_tributaria.fecha_exento_impuesto_rent',
                )
                ->get();
            $result['calidad_tributaria'] = $calidadTributaria;


            $result['junta_directiva'] = $miembrosjunta;

            $referenciaBancaria = ReferenciaBancaria::join('gen_bancos as ban', 'ban.cod_ban', '=', 'usr_app_referencias_bancarias.banco_id')
                ->join('usr_app_tipos_cuenta_banco as tc', 'tc.id', '=', 'usr_app_referencias_bancarias.tipo_cuenta_id')
                ->where('cliente_id', '=', $id)
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
                )
                ->get();

            $result['referencia_bancaria'] = $referenciaBancaria;

            $referenciaComercial = ReferenciaComercial::where('cliente_id', '=', $id)
                ->select(
                    'razon_social as nombre',
                    'contacto',
                    'telefono',
                )
                ->get();
            $result['referencia_comercial'] = $referenciaComercial;

            $personasExpuestas = PersonasExpuestas::join('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_personas_expuestas_politica.tipo_identificacion_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_personas_expuestas_politica.nombre',
                    'usr_app_personas_expuestas_politica.numero_identificacion as identificacion',
                    'usr_app_personas_expuestas_politica.parentesco',
                    'usr_app_personas_expuestas_politica.tipo_identificacion_id',
                    'ti.des_tip',
                )
                ->get();
            $result['personas_expuestas'] = $personasExpuestas;

            $origenFondo = OrigenFondo::join('usr_app_tipos_origen_fondos as of', 'of.id', '=', 'usr_app_origenes_fondos.tipo_origen_fondos_id')
                ->join('usr_app_tipos_origen_medios as om', 'om.id', '=', 'usr_app_origenes_fondos.tipo_origen_medios_id')
                ->join('usr_app_tipos_origen_medios as om2', 'om2.id', '=', 'usr_app_origenes_fondos.tipo_origen_medios2_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'tipo_origen_fondos_id',
                    'otro_origen',
                    'tipo_origen_medios_id',
                    'tipo_origen_medios2_id',
                    'alto_manejo_efectivo',
                    'of.nombre as origen_fondos',
                    'om.nombre as origen_medios',
                    'om2.nombre as origen_medios2',
                    'usr_app_origenes_fondos.alto_manejo_efectivo',
                )

                ->first();
            $result['origen_fondos'] = $origenFondo;

            $documentoCliente = DocumentoCliente::join('usr_app_tipos_documento as td', 'td.id', '=', 'usr_app_documentos_cliente.tipo_documento_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_documentos_cliente.id',
                    'usr_app_documentos_cliente.tipo_documento_id',
                    'usr_app_documentos_cliente.ruta',
                    'usr_app_documentos_cliente.descripcion',
                    'td.nombre',
                    'td.tipo_archivo'
                )

                ->get();
            $result['documentos_adjuntos'] = $documentoCliente;

            $cliente_otrosi = ClienteOtroSi::join('usr_app_otros_si as ots', 'ots.id', '=', 'usr_app_cliente_otrosi.otro_si_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'ots.id',
                    'ots.nombre as nombre',
                )
                ->get();

            $result['otrosi'] = $cliente_otrosi;

            $cliente_convenio_banco = ClienteConvenioBanco::join('gen_bancos as ban', 'ban.cod_ban', '=', 'usr_app_cliente_convenio_bancos.convenio_banco_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_cliente_convenio_bancos.convenio_banco_id as cod_ban',
                    'ban.nom_ban as nombre',
                )
                ->get();

            $result['convenios_banco'] = $cliente_convenio_banco;

            $cliente_tipo_contrato = ClienteTipoContrato::join('rhh_tipcon as tcon', 'tcon.tip_con', '=', 'usr_app_cliente_tipos_contrato.tipo_contrato_id')
                ->where('cliente_id', '=', $id)
                ->select(
                    'usr_app_cliente_tipos_contrato.tipo_contrato_id as tip_con',
                    'tcon.nom_con as nombre',
                )
                ->get();
            $result['tipos_contrato'] = $cliente_tipo_contrato;


            $cliente_laboratorio = ClienteLaboratorio::join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'usr_app_cliente_laboraorio.laboratorio_id')
                ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
                ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
                ->where('usr_app_cliente_laboraorio.cliente_id', '=', $id)
                ->select(
                    'ciulab.id',
                    'ciulab.ciudad_id',
                    'ciulab.laboratorio as nombre',
                    'mun.id as municipio_id',
                    'mun.nombre as municipio',
                    'dep.id as departamento_id',
                    'dep.nombre as departamento',
                    'pais.id as pais_id',
                    'pais.nombre as pais',
                )
                ->get();
            $ubicacion_laboratorio = [];

            $cliente_laboratorio = $cliente_laboratorio->map(function ($item) use (&$ubicacion_laboratorio) {
                $pais = $item['pais'];
                $departamento = $item['departamento'];
                $municipio = $item['municipio'];
                $pais_id = $item['pais_id'];
                $departamento_id = $item['departamento_id'];
                $municipio_id = $item['municipio_id'];

                $ubicacion_laboratorio[] = [
                    'pais' => $pais,
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'pais_id' => $pais_id,
                    'departamento_id' => $departamento_id,
                    'municipio_id' => $municipio_id,
                ];

                unset($item['pais'], $item['departamento'], $item['municipio'], $item['pais_id'], $item['departamento_id'], $item['municipio_id']);
                return $item;
            });

            $result['laboratorios_agregados'] = $cliente_laboratorio;
            $result['ubicacion_laboratorio'] = $ubicacion_laboratorio;

            if (!$asJson) {
                return $result;
            }
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function filtro($cadena)
    {
        $permisos = $this->validaPermiso();

        $user = auth()->user();
        $year_actual = date('Y');
        // $objeto = (object) [
        //     'mensaje' => 'Filtrando empresas',
        //     'componente' => 'navbar/debida-diligencia/clientes'
        // ];
        // event(new EventoPrueba2($objeto));
        try {
            $consulta = base64_decode($cadena);
            $valores = explode("/", $consulta);
            $campo = $valores[0];
            $operador = $valores[1];
            $valor = $valores[2];
            $valor2 = isset($valores[3]) ? $valores[3] : null;

            // return $campo."".$valor;

            $query = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->leftJoin('usr_app_estados_firma as estf', 'estf.id', '=', 'usr_app_clientes.estado_firma_id')
                ->leftJoin(
                    DB::raw('(SELECT cliente_id, estado_contrato 
                                 FROM usr_app_historico_contratos_dd 
                                 WHERE activo = 1) AS contratos'),
                    'contratos.cliente_id',
                    '=',
                    'usr_app_clientes.id'
                )
                /*    ->whereYear('usr_app_clientes.created_at', $year_actual) */
                ->when(!in_array('39', $permisos), function ($query) use ($user) {
                    return $query->where(function ($subQuery) use ($user) {
                        $subQuery->where('usr_app_clientes.vendedor_id', $user->vendedor_id)
                            ->orWhere('usr_app_clientes.responsable_id', $user->id);
                    });
                })
                ->select(
                    'usr_app_clientes.id',
                    DB::raw('COALESCE(CONVERT(VARCHAR, usr_app_clientes.numero_radicado), CONVERT(VARCHAR, usr_app_clientes.id)) AS numero_radicado'),
                    'usr_app_clientes.razon_social as nombre',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.nit',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.created_at',
                    'estf.nombre as nombre_estado_firma',
                    'contratos.estado_contrato',
                    'estf.color as color_estado_firma',
                    'estf.id as estado_firma_id',
                    'usr_app_clientes.responsable_corregir'
                )
                ->orderby('created_at', 'DESC');


            switch ($operador) {
                case 'Contiene':
                    if ($campo == "vendedor") {
                        $query->where('ven.nom_ven', 'like', '%' . $valor . '%');
                    } else if ($campo == "nombre_estado_firma") {
                        $query->where('estf.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "estado_contrato") {
                        $query->where('contratos.estado_contrato', 'like', '%' . $valor . '%');
                    } else {
                        $query->where($campo, 'like', '%' . $valor . '%');
                    }
                    break;
                case 'Igual a':
                    if ($campo == "vendedor") {
                        $query->where('ven.nom_ven', '=', $valor);
                    } else if ($campo == "nombre_estado_firma") {
                        $query->where('estf.id', '=', $valor);
                    } else {
                        $query->where($campo, '=', $valor);
                    }
                    break;
                case 'Igual a fecha':
                    $query->whereDate("usr_app_clientes." . $campo, '=', $valor);
                    break;
                case 'Entre':
                    $query->whereDate("usr_app_clientes." . $campo, '>=', $valor)
                        ->whereDate("usr_app_clientes." . $campo, '<=', $valor2);
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        DB::beginTransaction();

        try {
            // $request = $request[0];
            $actividad_ciiu = $this->actividades_ciiu($request['actividad_ciiu']);
            $cliente = new cliente;

            // encabezado paraa el formato del contrato
            $cliente->codigo_documento = 'FEGC-01-02';
            $cliente->fecha_documento = '21/05/2024';
            $cliente->version_documento = '21';
            // fin encabezado paraa el formato del contrato
            $cliente->operacion_id = $request['operacion'] == '' ? 1 : $request['operacion'];
            $cliente->tipo_persona_id = $request['tipo_persona'];
            $cliente->digito_verificacion = $request['digito_verificacion'];
            $cliente->razon_social = $request['razon_social'];
            $cliente->periodicidad_liquidacion_id = $request['periodicidad_liquidacion_id'];
            $cliente->tipo_identificacion_id = $request['tipo_identificacion'] == '' ? 0 : $request['tipo_identificacion'];
            $cliente->numero_identificacion = $request['numero_identificacion'];
            $cliente->fecha_exp_documento = $request['fecha_expedicion'];
            $cliente->contratacion_directa = $request['contratacion_directa'];
            $cliente->atraccion_seleccion = $request['atraccion_seleccion'];
            $cliente->nit = $request['nit'];
            $cliente->fecha_constitucion = $request['fecha_constitucion'];
            $cliente->actividad_ciiu_id = $actividad_ciiu->id;
            $cliente->estrato_id = $request['estrato'];
            $cliente->municipio_id = $request['municipio'];
            $cliente->municipio_rut_id = $request['municipio_rut'];
            $cliente->dirección_rut = $request['direccion_rut'];
            $cliente->direccion_empresa = $request['direccion_empresa'];
            $cliente->contacto_empresa = $request['contacto_empresa'];
            $cliente->correo_empresa = $request['correo_electronico'];
            $cliente->telefono_empresa = $request['telefono_empresa'];
            $cliente->celular_empresa = $request['numero_celular'];
            $cliente->sociedad_comercial_id = $request['sociedad_comercial'];
            $cliente->otra = $request['otra_cual'];
            $cliente->acuerdo_comercial = $request['acuerdo_comercial'];
            $cliente->aiu_negociado = $request['aiu_negociado'];
            $cliente->plazo_pago = $request['plazo_pago'];
            $cliente->vendedor_id = $request['vendedor'] == '' ? "0  " : $request['vendedor'];
            $cliente->numero_empleados = $request['empleados_empresa'];
            $cliente->jornada_laboral_id = $request['jornada_laboral'] == '' ? 1 : $request['jornada_laboral'];
            $cliente->rotacion_personal_id = $request['rotacion_personal'] == '' ? 1 : $request['rotacion_personal'];
            $cliente->riesgo_cliente_id = $request['riesgo_cliente'] == '' ? 1 : $request['riesgo_cliente'];
            $cliente->junta_directiva = $request['junta_directiva'];
            $cliente->responsable_inpuesto_ventas = $request['responsable_inpuesto_ventas'];
            $cliente->correo_facturacion_electronica = $request['correo_factura_electronica'];
            $cliente->sucursal_facturacion_id = $request['sucursal_facturacion'] == '' ? '0' : $request['sucursal_facturacion'];
            $cliente->declaraciones_autorizaciones = $request['declaraciones_autorizaciones']  == 0 ? 0 : 1;
            $cliente->tratamiento_datos_personales = $request['tratamiento_datos_personales'];
            $cliente->operaciones_internacionales = $request['operaciones_internacionales'];
            $cliente->tipo_cliente_id = $request['tipo_cliente_id'];
            $cliente->tipo_proveedor_id = $request['tipo_proveedor_id'] == '' ? 1 : $request['tipo_proveedor_id'];
            $cliente->municipio_prestacion_servicio_id = $request['municipio_prestacion_servicio'];
            $cliente->empresa_extranjera = $request['empresa_extranjera'];
            $cliente->empresa_en_exterior = $request['empresa_exterior'];
            $cliente->vinculos_empresa = $request['vinculos_empresa'];
            $cliente->numero_empleados_directos = $request['numero_empleados_directos'];
            $cliente->vinculado_empresa_temporal = $request['personal_vinculado_temporal'];
            $cliente->visita_presencial = $request['visita_presencial'];
            $cliente->facturacion_contacto = $request['facturacion_contacto'];
            $cliente->facturacion_cargo = $request['facturacion_cargo'];
            $cliente->facturacion_telefono = $request['facturacion_telefono'];
            $cliente->facturacion_celular = $request['facturacion_celular'];
            $cliente->facturacion_correo = $request['facturacion_correo'];
            $cliente->facturacion_factura_unica = $request['facturacion_factura'];
            $cliente->facturacion_fecha_corte = $request['facturacion_fecha_corte'];
            $cliente->facturacion_encargado_factura = $request['facturacion_encargado_factura'];
            $cliente->requiere_anexo_factura = $request['anexo_factura'];
            $cliente->trabajo_alto_riesgo = $request['trabajo_alto_riesgo'];
            $cliente->accidentalidad = $request['accidentalidad'];
            $cliente->encargado_sst = $request['encargado_sst'];
            $cliente->nombre_encargado_sst = $request['nombre_encargado_sst'];
            $cliente->cargo_encargado_sst = $request['cargo_encargado_sst'];
            $cliente->induccion_entrenamiento = $request['induccion_entrenamiento'];
            $cliente->entrega_dotacion = $request['entrega_dotacion'];
            $cliente->evaluado_arl = $request['evaluado_arl'];
            $cliente->entrega_epp = $request['entrega_epp'];
            $cliente->contratacion_contacto = $request['contratacion_contacto'];
            $cliente->contratacion_cargo = $request['contratacion_cargo'];
            $cliente->contratacion_telefono = $request['contratacion_telefono'];
            $cliente->contratacion_celular = $request['contratacion_celular'];
            $cliente->contratacion_correo = $request['contratacion_correo_electronico'];
            $cliente->contratacion_hora_ingreso = $request['contratacion_hora_ingreso'];
            $cliente->contratacion_manipulacion_alimentos = $request['contratacion_manipulacion_alimentos'];
            $cliente->contratacion_hora_confirmacion = $request['contratacion_confirma_ingreso'];
            $cliente->contratacion_tallas_uniforme = $request['contratacion_tallas_uniforme'];
            $cliente->contratacion_suministra_transporte = $request['contratacion_suministra_transporte'];
            $cliente->contratacion_suministra_alimentacion = $request['contratacion_suministra_alimentacion'];
            $cliente->contratacion_pago_efectivo = $request['contratacion_pago_efectivo'];
            $cliente->contratacion_carnet_corporativo = $request['contratacion_carnet_corporativo'];
            $cliente->contratacion_pagos_31 = $request['contratacion_pagos_31'];

            if (
                $request->estado_firma_id
                == ''
            ) {
                $cliente->estado_firma_id = 1;
            } else {
                $cliente->estado_firma_id = $request->estado_firma_id;
            }
            $cliente->responsable = $request->responsable;
            $cliente->responsable_id = $request->responsable_id;
            $cliente->contratacion_observacion = $request['contratacion_observacion'];
            $cliente->save();

            $seguimiento_estado = new ClientesSeguimientoEstado;
            $seguimiento_estado->responsable_inicial =  $user->nombres . ' ' . $user->apellidos;
            $seguimiento_estado->responsable_final = $cliente->responsable = $request->responsable;
            $seguimiento_estado->estados_firma_inicial =  $cliente->estado_firma_id;
            $seguimiento_estado->estados_firma_final =    $cliente->estado_firma_id;
            $seguimiento_estado->cliente_id =   $cliente->id;
            $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' .  $user->apellidos;
            $seguimiento_estado->oportuno = "2";
            $seguimiento_estado->save();
            $this->eventoSocket($request->responsable_id);

            $operacion = new HistoricoOperacionesDDModel();
            $operacion->cliente_id =  $cliente->id;
            $operacion->tipo_operacion_id = $cliente->operacion_id;
            $operacion->nombre_usuario_actualiza = $user->nombres . ' ' . $user->apellidos;
            $operacion->save();

            $seguimiento = new ClientesSeguimientoGuardado();
            $seguimiento->estado_firma_id = $cliente->estado_firma_id;
            $seguimiento->usuario = $user->nombres . ' ' . $user->apellidos;
            $seguimiento->cliente_id = $cliente->id;
            $seguimiento->save();

            $contador = 0;
            foreach ($request['cargos2'] as $item) {
                if ($item['cargo_id'] != '' || $item['riesgo_laboral_id'] != '') {
                    $cargo = new cargo2;
                    $cargo->cargo_id = $item['cargo_id'];
                    $cargo->riesgo_laboral_id = $item['riesgo_laboral_id'];
                    $cargo->funcion_cargo = $item['funcion_cargo'];
                    $cargo->cliente_id = $cliente->id;
                    $cargo->save();


                    foreach ($request['cargos2'][$contador]['examenes'] as $item) {
                        if ($item['id'] != '') {
                            $cargoExamen = new Cargo2Examen;
                            $cargoExamen->examen_id = $item['id'];
                            $cargoExamen->cargo_id = $cargo->id;
                            $cargoExamen->save();
                        }
                    }

                    // Se eliminan los requisitos del formulario
                    foreach ($request['cargos2'][$contador]['recomendaciones'] as $item) {
                        if ($item['id'] != '') {
                            $cargoRecomendacion = new Cargo2Recomendacion;
                            $cargoRecomendacion->recomendacion_id = $item['id'];
                            $cargoRecomendacion->cargo_id = $cargo->id;
                            $cargoRecomendacion->save();
                        }
                    }
                    $contador++;
                }
            }



            foreach ($request['accionistas'] as $item) {
                // if ($item['socio'] != '' || $item['tipo_identificacion'] != '' || $item['identificacion'] != '' || $item['participacion'] != '') {
                $accionista = new Accionista;
                $accionista->accionista = $item['socio'];
                $accionista->tipo_identificacion_id = $item['tipo_identificacion_id'];
                $accionista->identificacion = $item['identificacion'];
                $accionista->participacion = $item['participacion'];
                $accionista->cliente_id = $cliente->id;
                $accionista->save();
                // }
            }

            foreach ($request['representantes_legales'] as $item) {
                if ($item['nombre'] != '' || $item['tipo_identificacion'] != '' || $item['identificacion'] != '' || $item['correo'] != '' || $item['telefono'] != '' || $item['ciudad_expedicion'] != '') {
                    $RepresentanteLegal = new RepresentanteLegal;
                    $RepresentanteLegal->nombre = $item['nombre'];
                    $RepresentanteLegal->tipo_identificacion_id = $item['tipo_identificacion'];
                    $RepresentanteLegal->identificacion = $item['identificacion'];
                    $RepresentanteLegal->correo_electronico = $item['correo'];
                    $RepresentanteLegal->telefono = $item['telefono'];
                    $RepresentanteLegal->municipio_expedicion_id = $item['municipio_id'];
                    $RepresentanteLegal->cliente_id = $cliente->id;
                    $RepresentanteLegal->save();
                }
            }

            foreach ($request['miembros_Junta'] as $item) {
                if ($item['nombre'] != '' || $item['tipo_identificacion_id'] != '' || $item['identificacion']) {
                    $MiembroJunta = new MiembroJunta;
                    $MiembroJunta->nombre = $item['nombre'];
                    $MiembroJunta->tipo_identificacion_id = $item['tipo_identificacion_id'];
                    $MiembroJunta->identificacion = $item['identificacion'];
                    $MiembroJunta->cliente_id = $cliente->id;
                    $MiembroJunta->save();
                }
            }

            if ($request['calidad_tributaria'][0]['opcion'] != '' ||  $request['calidad_tributaria'][1]['opcion'] != '' || $request['calidad_tributaria'][2]['opcion'] != '') {
                $CalidadTributaria = new CalidadTributaria;
                $CalidadTributaria->gran_contribuyente = $request['calidad_tributaria'][0]['opcion'];
                $CalidadTributaria->resolucion_gran_contribuyente = $request['calidad_tributaria'][0]['numero_resolucion'];
                $CalidadTributaria->fecha_gran_contribuyente = $request['calidad_tributaria'][0]['fecha'];
                $CalidadTributaria->auto_retenedor = $request['calidad_tributaria'][1]['opcion'];
                $CalidadTributaria->resolucion_auto_retenedor = $request['calidad_tributaria'][1]['numero_resolucion'];
                $CalidadTributaria->fecha_auto_retenedor = $request['calidad_tributaria'][1]['fecha'];
                $CalidadTributaria->exento_impuesto_rent = $request['calidad_tributaria'][2]['opcion'];
                $CalidadTributaria->resolucion_exento_impuesto_rent = $request['calidad_tributaria'][2]['numero_resolucion'];
                $CalidadTributaria->fecha_exento_impuesto_rent = $request['calidad_tributaria'][2]['fecha'];
                $CalidadTributaria->cliente_id = $cliente->id;
                $CalidadTributaria->save();
            }

            // if ($request['nombre_completo_contador'] != '' || $request['tipo_identificacion_contador'] != '' || $request['identificacion_contador'] != '' || $request['telefono_contador'] != '') {
            $Contador = new Contador;
            $Contador->nombre = $request['nombre_completo_contador'];
            $Contador->tipo_identificacion_id = $request['tipo_identificacion_contador'];
            $Contador->identificacion = $request['identificacion_contador'];
            $Contador->telefono = $request['telefono_contador'];
            $Contador->cliente_id = $cliente->id;
            $Contador->save();
            // }

            // if ($request['nombre_completo_tesorero'] != '' || $request['telefono_tesorero'] != '' || $request['correo_tesorero'] != '') {
            $Tesorero = new Tesorero;
            $Tesorero->nombre = $request['nombre_completo_tesorero'];
            $Tesorero->telefono = $request['telefono_tesorero'];
            $Tesorero->correo = $request['correo_tesorero'];
            $Tesorero->cliente_id = $cliente->id;
            $Tesorero->save();
            // }

            if ($request['ingreso_mensual'] != '' || $request['otros_ingresos'] != '' || $request['total_ingresos'] != '' || $request['costos_gastos'] != '' || $request['detalle_otros_ingresos'] != '' || $request['reintegro_costos'] != '' || $request['activos'] != '' || $request['pasivos'] != '' || $request['patrimonio'] != '') {
                $DatoFinanciero = new DatoFinanciero;
                $DatoFinanciero->ingreso_mensual = $request['ingreso_mensual'];
                $DatoFinanciero->otros_ingresos = $request['otros_ingresos'];
                $DatoFinanciero->total_ingresos = $request['total_ingresos'];
                $DatoFinanciero->costos_gastos_mensual = $request['costos_gastos'];
                $DatoFinanciero->detalle_otros_ingresos = $request['detalle_otros_ingresos'];
                $DatoFinanciero->reintegro_costos_gastos = $request['reintegro_costos'];
                $DatoFinanciero->activos = $request['activos'];
                $DatoFinanciero->pasivos = $request['pasivos'];
                $DatoFinanciero->patrimonio = $request['patrimonio'];
                $DatoFinanciero->cliente_id = $cliente->id;
                $DatoFinanciero->save();
            }


            $origenFondo = new OrigenFondo();
            $origenFondo->tipo_origen_fondos_id = $request['tipo_origen_fondo'];
            $origenFondo->otro_origen = $request['otro_tipo_origen_fondos'];
            $origenFondo->tipo_origen_medios_id = $request['tipo_origen_medios'];
            $origenFondo->tipo_origen_medios2_id = $request['otro_tipo_origen_medios'];
            $origenFondo->alto_manejo_efectivo = $request['alto_manejo_efectivo'];
            $origenFondo->cliente_id = $cliente->id;
            $origenFondo->save();


            if ($request['tipo_operacion_internacional'] != '') {
                $OperacionIternacional = new OperacionIternacional;
                $OperacionIternacional->tipo_operaciones_id = $request['tipo_operacion_internacional'];
                $OperacionIternacional->cliente_id = $cliente->id;
                $OperacionIternacional->save();
            }

            foreach ($request['referencias_bancarias'] as $item) {
                // if ($item['numero_cuenta'] != '' || $item['tipo_cuenta'] != '' ||  $item['sucursal'] != '' || $item['telefono'] != '' || $item['contacto'] != '' || $item['banco'] != '') {
                $ReferenciaBancaria = new ReferenciaBancaria;
                $ReferenciaBancaria->numero_cuenta = $item['numero_cuenta'];
                $ReferenciaBancaria->tipo_cuenta_id = $item['tipo_cuenta'];
                $ReferenciaBancaria->sucursal = $item['sucursal'];
                $ReferenciaBancaria->telefono = $item['telefono'];
                $ReferenciaBancaria->contacto = $item['contacto'];
                $ReferenciaBancaria->banco_id = $item['banco_id'];
                $ReferenciaBancaria->cliente_id = $cliente->id;
                $ReferenciaBancaria->save();
                // }
            }

            foreach ($request['personas_expuestas'] as $item) {
                $personasExpuestas = new PersonasExpuestas;
                $personasExpuestas->nombre = $item['nombre'];
                $personasExpuestas->numero_identificacion = $item['identificacion'];
                $personasExpuestas->tipo_identificacion_id = $item['tipo_identificacion_id'];
                $personasExpuestas->parentesco = $item['parentesco'];
                $personasExpuestas->cliente_id = $cliente->id;
                $personasExpuestas->save();
            }

            foreach ($request['referencias_comerciales'] as $item) {
                $ReferenciaComercial = new ReferenciaComercial;
                $ReferenciaComercial->razon_social = $item['nombre'];
                $ReferenciaComercial->contacto = $item['contacto'];
                $ReferenciaComercial->telefono = $item['telefono'];
                $ReferenciaComercial->cliente_id = $cliente->id;
                $ReferenciaComercial->save();
            }

            foreach ($request['elementos_epp'] as $index => $item) {
                if ($item == true) {
                    $cliente_epp = new ClienteEpp;
                    $cliente_epp->epp_id = $index;
                    $cliente_epp->cliente_id = $cliente->id;
                    $cliente_epp->save();
                }
            }

            foreach ($request['otros_si_agregados'] as $item) {
                $Cliente_otrosi = new ClienteOtroSi;
                $Cliente_otrosi->otro_si_id = $item['id'];
                $Cliente_otrosi->cliente_id = $cliente->id;
                $Cliente_otrosi->save();
            }

            foreach ($request['tipos_contratos_agregados'] as $item) {
                $Cliente_tipo_contrato = new ClienteTipoContrato;
                $Cliente_tipo_contrato->tipo_contrato_id = $item['id'];
                $Cliente_tipo_contrato->cliente_id = $cliente->id;
                $Cliente_tipo_contrato->save();
            }

            foreach ($request['bancos_agregados'] as $item) {
                $Cliente_convenio_banco = new ClienteConvenioBanco;
                $Cliente_convenio_banco->convenio_banco_id = $item['id'];
                $Cliente_convenio_banco->cliente_id = $cliente->id;
                $Cliente_convenio_banco->save();
            }

            foreach ($request['laboratorios_medicos'] as $item) {
                $cliente_laboratorio = new ClienteLaboratorio;
                $cliente_laboratorio->laboratorio_id = $item['id'];
                $cliente_laboratorio->cliente_id = $cliente->id;
                $cliente_laboratorio->save();
            }

            $clientes_android = VersionTablasAndroid::find(12);
            $clientes_android->version = $clientes_android->version + 1;
            $clientes_android->save();

            DB::commit();
            return response()->json(['status' => '200', 'message' => 'ok', 'client' => $cliente->id]);
        } catch (\Exception $e) {
            // Revertir la transacción si se produce alguna excepción
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor verifique el llenado de todos los campos e intente nuevamente']);
        }
    }

    public function actividades_ciiu($codigo)
    {
        $result = ActividadCiiu::select(
            'id'
        )
            ->where('codigo_actividad', '=', $codigo)
            ->first();
        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id_cliente)
    {

        try {
            $result = DocumentoCliente::where('cliente_id', '=', $id_cliente)
                ->get();
            $documentos = $request->all();
            $value = '';
            $id = '';
            $ids = [];
            $rutas = [];

            $directorio = public_path('upload/');
            $archivos = glob($directorio . '*');
            foreach ($archivos as $archivo) {
                $nombreArchivo = basename($archivo);

                if (strpos($nombreArchivo, '_' . $id_cliente . '_') !== false) {
                    unlink($archivo);
                }
            }
            foreach ($result as $item) {
                $archivo = DocumentoCliente::find($item->id);
                $archivo->delete();
            }

            foreach ($documentos as $item) {
                $contador = 0;
                if (!is_numeric($item)) {
                    $nombreArchivoOriginal = $item->getClientOriginalName();
                    $nuevoNombre = '_' . $id_cliente . "_" . $nombreArchivoOriginal;

                    $carpetaDestino = './upload/';
                    $item->move($carpetaDestino, $nuevoNombre);
                    $item = ltrim($carpetaDestino, '.') . $nuevoNombre;
                    array_push($rutas, $item);
                    $value .= $item . ' ';
                } else {
                    array_push($ids, $item);
                    $id .= $item . ' ';
                }
                $contador++;
            }
            for ($i = 0; $i < count($ids); $i++) {
                $documento = new DocumentoCliente;
                $documento->tipo_documento_id = $ids[$i];
                $documento->ruta = $rutas[$i];
                $documento->cliente_id = $id_cliente;
                $documento->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Formulario guardado exitosamente']);
        } catch (\Throwable $th) {
            //throw $th;
            // $cliente = cliente::find($id_cliente);
            // $cliente->delete();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intente nuevamente, si el problema persiste por favor contacte al administrador del sitio']);
        }
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
        $estado__nuevo_id = $request->estado_firma_id;
        $user = auth()->user();
        $permisos = $this->validaPermiso();
        $cliente = Cliente::where('usr_app_clientes.id', '=', $id)
            ->select()
            ->first();

        try {
            if ($cliente->operacion_id != $request['operacion']) {
                $operacion = new HistoricoOperacionesDDModel();
                $operacion->cliente_id =  $cliente->id;
                $operacion->tipo_operacion_id = $request['operacion'];
                $operacion->nombre_usuario_actualiza = $user->nombres . ' ' . $user->apellidos;
                $operacion->save();
            }
            $cliente->acuerdo_comercial = $request['acuerdo_comercial'];
            $actividad_ciiu = $this->actividades_ciiu($request['actividad_ciiu']);
            $cliente->operacion_id = $request['operacion'];
            $cliente->novedad_servicio = $request['novedad_servicio'];
            $cliente->usuario_corregir_id = $request['usuario_corregir_id'];
            $cliente->afectacion_servicio = $request['afectacion_servicio'];
            $cliente->contratacion_directa = $request['contratacion_directa'];
            $cliente->atraccion_seleccion = $request['atraccion_seleccion'];
            $cliente->tipo_persona_id = $request['tipo_persona'];
            $cliente->tipo_identificacion_id = $request['tipo_identificacion'];
            $cliente->numero_identificacion = $request['numero_identificacion'];
            $cliente->fecha_exp_documento = $request['fecha_expedicion'];
            $cliente->nit = $request['nit'];
            $cliente->digito_verificacion = $request['digito_verificacion'];
            $cliente->razon_social = $request['razon_social'];
            $cliente->periodicidad_liquidacion_id = $request['periodicidad_liquidacion_id'];
            $cliente->fecha_constitucion = $request['fecha_constitucion'];
            $cliente->actividad_ciiu_id = $actividad_ciiu->id;
            $cliente->estrato_id = $request['estrato'];
            $cliente->municipio_id = $request['municipio'];
            $cliente->direccion_empresa = $request['direccion_empresa'];
            $cliente->contacto_empresa = $request['contacto_empresa'];
            $cliente->correo_empresa = $request['correo_electronico'];
            $cliente->telefono_empresa = $request['telefono_empresa'];
            $cliente->celular_empresa = $request['numero_celular'];
            $cliente->sociedad_comercial_id = $request['sociedad_comercial'];
            $cliente->otra = $request['otra_cual'];
            /* $cliente->acuerdo_comercial = $request['observaciones']; */
            $cliente->aiu_negociado = $request['aiu_negociado'];
            $cliente->plazo_pago = $request['plazo_pago'];
            $cliente->vendedor_id = $request['vendedor'];
            $cliente->numero_empleados = $request['empleados_empresa'];
            $cliente->jornada_laboral_id = $request['jornada_laboral'];
            $cliente->rotacion_personal_id = $request['rotacion_personal'];
            $cliente->riesgo_cliente_id = $request['riesgo_cliente'];
            $cliente->junta_directiva = $request['junta_directiva'];
            $cliente->responsable_inpuesto_ventas = $request['responsable_inpuesto_ventas'];
            $cliente->correo_facturacion_electronica = $request['correo_factura_electronica'];
            $cliente->sucursal_facturacion_id = $request['sucursal_facturacion'];
            $cliente->declaraciones_autorizaciones = $request['declaraciones_autorizaciones'];
            $cliente->tratamiento_datos_personales = $request['tratamiento_datos_personales'];
            $cliente->operaciones_internacionales = $request['operaciones_internacionales'];
            $cliente->tipo_cliente_id = $request['tipo_cliente_id'];
            $cliente->tipo_proveedor_id = $request['tipo_proveedor_id'];
            $cliente->municipio_prestacion_servicio_id = $request['municipio_prestacion_servicio'];
            $cliente->empresa_extranjera = $request['empresa_extranjera'];
            $cliente->empresa_en_exterior = $request['empresa_exterior'];
            $cliente->vinculos_empresa = $request['vinculos_empresa'];
            $cliente->numero_empleados_directos = $request['numero_empleados_directos'];
            $cliente->vinculado_empresa_temporal = $request['personal_vinculado_temporal'];
            $cliente->visita_presencial = $request['visita_presencial'];
            $cliente->facturacion_contacto = $request['facturacion_contacto'];
            $cliente->facturacion_cargo = $request['facturacion_cargo'];
            $cliente->facturacion_telefono = $request['facturacion_telefono'];
            $cliente->facturacion_celular = $request['facturacion_celular'];
            $cliente->facturacion_correo = $request['facturacion_correo'];
            $cliente->facturacion_factura_unica = $request['facturacion_factura'];
            $cliente->facturacion_fecha_corte = $request['facturacion_fecha_corte'];
            $cliente->facturacion_encargado_factura = $request['facturacion_encargado_factura'];
            $cliente->requiere_anexo_factura = $request['anexo_factura'];
            $cliente->trabajo_alto_riesgo = $request['trabajo_alto_riesgo'];
            $cliente->accidentalidad = $request['accidentalidad'];
            $cliente->encargado_sst = $request['encargado_sst'];
            $cliente->nombre_encargado_sst = $request['nombre_encargado_sst'];
            $cliente->cargo_encargado_sst = $request['cargo_encargado_sst'];
            $cliente->induccion_entrenamiento = $request['induccion_entrenamiento'];
            $cliente->entrega_dotacion = $request['entrega_dotacion'];
            $cliente->evaluado_arl = $request['evaluado_arl'];
            $cliente->entrega_epp = $request['entrega_epp'];
            $cliente->contratacion_contacto = $request['contratacion_contacto'];
            $cliente->contratacion_cargo = $request['contratacion_cargo'];
            $cliente->contratacion_telefono = $request['contratacion_telefono'];
            $cliente->contratacion_celular = $request['contratacion_celular'];
            $cliente->contratacion_correo = $request['contratacion_correo_electronico'];
            $cliente->contratacion_hora_ingreso = $request['contratacion_hora_ingreso'];
            $cliente->contratacion_manipulacion_alimentos = $request['contratacion_manipulacion_alimentos'];
            $cliente->contratacion_hora_confirmacion = $request['contratacion_confirma_ingreso'];
            $cliente->contratacion_tallas_uniforme = $request['contratacion_tallas_uniforme'];
            $cliente->contratacion_suministra_transporte = $request['contratacion_suministra_transporte'];
            $cliente->contratacion_suministra_alimentacion = $request['contratacion_suministra_alimentacion'];
            $cliente->contratacion_pago_efectivo = $request['contratacion_pago_efectivo'];
            $cliente->contratacion_carnet_corporativo = $request['contratacion_carnet_corporativo'];
            $cliente->contratacion_pagos_31 = $request['contratacion_pagos_31'];
            $cliente->contratacion_observacion = $request['contratacion_observacion'];
            $cliente->dirección_rut = $request['direccion_rut'];
            $cliente->municipio_id = $request['municipio'];
            $cliente->municipio_rut_id = $request['municipio_rut'];
            /*    $cliente->estado_firma_id = $request->estado_firma_id;
            $cliente->responsable = $request->responsable;
            $cliente->responsable_id = $request->responsable_id; */
            $cliente->responsable_corregir = $request['responsable_corregir'];
            $cliente->save();


            if ($request['novedad_servicio'] == 17) {

                $novedad = new NovedadesDD();
                $novedad->registro_cliente_id =  $cliente->id;
                $novedad->observaciones = $request['afectacion_servicio'];
                $novedad->usuario_guarda = $user->nombres . ' ' . $user->apellidos;
                $novedad->usuario_corrige = $request['usuario_corregir_id'];
                $novedad->save();
            }

            $historico_profesionales_controller = new HistoricoProfesionalesController;
            $historico = $historico_profesionales_controller->byClienteId($id, false);

            if (!$historico || ($historico->usuario_nomina_id !== $request['profesional_nomina_id'] ||  $historico->usuario_cartera_id !== $request['profesional_cartera_id'] || $historico->usuario_sst_id !== $request['profesional_sst'])) {
                $historico_profesionales = new HistoricoProfesionalesModel;
                $historico_profesionales->cliente_id = $id;
                if ($request['profesional_sst_id']) {
                    $historico_profesionales->profesional_sst = $request['profesional_sst'];
                    $historico_profesionales->usuario_sst_id = $request['profesional_sst_id'];
                    $historico_profesionales->anotacion_sst = $request['anotacion_sst'];
                }
                if ($request['profesional_cartera_id']) {
                    $historico_profesionales->profesional_cartera = $request['profesional_cartera'];
                    $historico_profesionales->usuario_cartera_id = $request['profesional_cartera_id'];
                    $historico_profesionales->anotacion_cartera = $request['anotacion_cartera'];
                }
                if ($request['profesional_nomina_id']) {
                    $historico_profesionales->profesional_nomina = $request['profesional_nomina'];
                    $historico_profesionales->usuario_nomina_id  = $request['profesional_nomina_id'];
                    $historico_profesionales->anotacion_nomina = $request['anotacion_nomina'];
                }
                $historico_profesionales->save();
            } else {

                $historico->anotacion_nomina = $request['anotacion_nomina'];
                $historico->anotacion_cartera = $request['anotacion_cartera'];
                $historico->anotacion_sst = $request['anotacion_sst'];
                $historico->save();
            }




            $ids = [];
            array_push($ids, $id);
            if ($cliente->responsable_id != null && $cliente->responsable_id != $user->id && !in_array('31', $permisos)) {

                $seguimiento = new ClientesSeguimientoGuardado;
                $seguimiento->estado_firma_id = $request->estado_id;
                $seguimiento->usuario =  $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
                $seguimiento->cliente_id = $id;
                $seguimiento->save();

                return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
            }

            $seguimiento = new ClientesSeguimientoGuardado();
            $seguimiento->estado_firma_id = $request->estado_firma_id;
            $seguimiento->usuario =  $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
            $seguimiento->cliente_id = $id;
            $seguimiento->save();

            $responsable_inicial = str_replace("null", "", $cliente->responsable);

            $estado_inicial = $cliente->estado_firma_id;



            if ($estado__nuevo_id != $estado_inicial ||  $cliente->responsable == null || $cliente->responsable_id != $request->responsable_id) {
                $this->actualizaestadofirma($id, $estado__nuevo_id, $request->responsable_id, $responsable_inicial, $estado_inicial);
            }/* else if( $cliente->responsable_id != $request->responsable_id){
                $this->actualizaestadofirma($id, $estado__nuevo_id, $request->responsable_id, $responsable_inicial, $estado_inicial);
            } */


            /* else {
                $seguimiento_estado = new ClientesSeguimientoEstado();
                $seguimiento_estado->responsable_inicial =  $responsable_inicial;
                $seguimiento_estado->responsable_final = str_replace("null", "", $cliente->responsable);
                $seguimiento_estado->estados_firma_inicial = $estado_inicial;
                $seguimiento_estado->estados_firma_final =   $request->estado_firma_id;
                $seguimiento_estado->cliente_id =  $id;
                $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
                $seguimiento_estado->save(); 
            }
 */
            $nombres = str_replace("null", "", $user->nombres);
            $apellidos = str_replace("null", "", $user->apellidos);
            $registroCambio = new RegistroCambio;
            $registroCambio->observaciones = $request['registro_cambios']['observaciones'];
            $registroCambio->estado = $request['consulta_estado_firma'];
            $registroCambio->solicitante = $request['registro_cambios']['solicitante'];
            $registroCambio->autoriza = $request['registro_cambios']['autoriza'];
            $registroCambio->actualiza = $nombres . ' ' . $apellidos;
            $registroCambio->cliente_id = $id;
            $registroCambio->save();

            $cargo = Cargo2::where('cliente_id', '=', $id)
                ->select()
                ->get();
            $cargoExamen = Cargo2Examen::where('cargo_id', '=', $id)
                ->select()
                ->get();
            $cargoRecomendacion = Cargo2Recomendacion::where('cargo_id', '=', $id)
                ->select()
                ->get();
            foreach ($cargoExamen as $item) {
                $item->delete();
            }
            foreach ($cargoRecomendacion as $item) {
                $item->delete();
            }
            foreach ($cargo as $item) {
                $item->delete();
            }
            $contador = 0;
            foreach ($request['cargos2'] as $item) {
                if ($item['cargo_id'] != '' || $item['riesgo_laboral_id'] != '') {
                    $cargo = new cargo2;
                    $cargo->cargo_id = intval($item['cargo_id']);
                    $cargo->riesgo_laboral_id = $item['riesgo_laboral_id'];
                    $cargo->funcion_cargo = $item['funcion_cargo'];
                    $cargo->cliente_id = $cliente->id;
                    $cargo->save();


                    foreach ($request['cargos2'][$contador]['examenes'] as $item) {
                        if ($item['id'] != '') {
                            $cargoExamen = new Cargo2Examen;
                            $cargoExamen->examen_id = $item['id'];
                            $cargoExamen->cargo_id = $cargo->id;
                            $cargoExamen->save();
                        }
                    }

                    foreach ($request['cargos2'][$contador]['recomendaciones'] as $item) {
                        if ($item['id'] != '') {
                            $cargoRecomendacion = new Cargo2Recomendacion;
                            $cargoRecomendacion->recomendacion_id = $item['id'];
                            $cargoRecomendacion->cargo_id = $cargo->id;
                            $cargoRecomendacion->save();
                        }
                    }
                    $contador++;
                }
            }
            // foreach ($request['cargos2'] as $item) {
            //     if ($item['cargo'] != '' || $item['riesgo_laboral_id'] != '') {
            //         $cargo = new cargo2;
            //         $cargo->cargo_id = $item['cargo'];
            //         $cargo->riesgo_laboral_id = $item['riesgo_laboral_id'];
            //         $cargo->funcion_cargo = $item['funcion_cargo'];
            //         $cargo->cliente_id = $cliente->id;
            //         $cargo->save();


            //         foreach ($request['cargos2'][$contador]['examenes'] as $item) {
            //             if ($item['id'] != '') {
            //                 $cargoExamen = new Cargo2Examen;
            //                 $cargoExamen->examen_id = $item['id'];
            //                 $cargoExamen->cargo_id = $cargo->id;
            //                 $cargoExamen->save();
            //             }
            //         }

            //         // Se eliminan los requisitos del formulario
            //         foreach ($request['cargos2'][$contador]['recomendaciones'] as $item) {
            //             if ($item['id'] != '') {
            //                 $cargoRecomendacion = new Cargo2Recomendacion;
            //                 $cargoRecomendacion->recomendacion_id = $item['id'];
            //                 $cargoRecomendacion->cargo_id = $cargo->id;
            //                 $cargoRecomendacion->save();
            //             }
            //         }
            //         $contador++;
            //     }
            // }

            $accionista = Accionista::where('cliente_id', '=', $id)
                ->get();
            foreach ($accionista as $item) {
                $item->delete();
            }
            $cont = 0;
            foreach ($request['accionistas'] as $item) {
                if ($item['socio'] != '' || $item['tipo_identificacion_id'] != '' || $item['identificacion'] != '' || $item['participacion'] != '') {
                    $accionista = new Accionista;
                    $accionista->accionista = $item['socio'];
                    $accionista->tipo_identificacion_id = $item['tipo_identificacion_id'];
                    $accionista->identificacion = $item['identificacion'];
                    $accionista->participacion = $item['participacion'];
                    $accionista->cliente_id = $id;
                    $accionista->save();
                }
            }

            $RepresentanteLegal = RepresentanteLegal::where('cliente_id', '=', $id)
                ->select()
                ->get();
            foreach ($RepresentanteLegal as $item) {
                $item->delete();
            }
            $cont = 0;
            foreach ($request['representantes_legales'] as $item) {
                if ($item['nombre'] != '' || $item['tipo_identificacion'] != '' || $item['identificacion'] != '' || $item['correo'] != '' || $item['telefono'] != '' || $item['municipio_id'] != '') {
                    $RepresentanteLegal = new RepresentanteLegal;
                    $RepresentanteLegal->nombre = $item['nombre'];
                    $RepresentanteLegal->tipo_identificacion_id = $item['tipo_identificacion'];
                    $RepresentanteLegal->identificacion = $item['identificacion'];
                    $RepresentanteLegal->correo_electronico = $item['correo'];
                    $RepresentanteLegal->telefono = $item['telefono'];
                    $RepresentanteLegal->municipio_expedicion_id = $item['municipio_id'];
                    $RepresentanteLegal->cliente_id = $id;
                    $RepresentanteLegal->save();
                }
            }


            $MiembroJunta = MiembroJunta::where('cliente_id', '=', $id)
                ->select()
                ->get();
            foreach ($MiembroJunta as $item) {
                $item->delete();
            }
            $cont = 0;
            foreach ($request['miembros_Junta'] as $item) {
                if ($item['nombre'] != '' || $item['tipo_identificacion_id'] != '' || $item['identificacion']) {
                    $MiembroJunta = new MiembroJunta;
                    $MiembroJunta->nombre = $item['nombre'];
                    $MiembroJunta->tipo_identificacion_id = $item['tipo_identificacion_id'];
                    $MiembroJunta->identificacion = $item['identificacion'];
                    $MiembroJunta->cliente_id = $id;
                    $MiembroJunta->save();
                }
            }

            $CalidadTributaria = CalidadTributaria::where('cliente_id', '=', $id)
                ->select()
                ->first();
            if ($CalidadTributaria == null) {
                if ($request['calidad_tributaria'][0]['opcion'] != '' ||  $request['calidad_tributaria'][1]['opcion'] != '' || $request['calidad_tributaria'][2]['opcion'] != '') {
                    $CalidadTributaria = new CalidadTributaria;
                    $CalidadTributaria->gran_contribuyente = $request['calidad_tributaria'][0]['opcion'];
                    $CalidadTributaria->resolucion_gran_contribuyente = $request['calidad_tributaria'][0]['numero_resolucion'];
                    $CalidadTributaria->fecha_gran_contribuyente = $request['calidad_tributaria'][0]['fecha'];
                    $CalidadTributaria->auto_retenedor = $request['calidad_tributaria'][1]['opcion'];
                    $CalidadTributaria->resolucion_auto_retenedor = $request['calidad_tributaria'][1]['numero_resolucion'];
                    $CalidadTributaria->fecha_auto_retenedor = $request['calidad_tributaria'][1]['fecha'];
                    $CalidadTributaria->exento_impuesto_rent = $request['calidad_tributaria'][2]['opcion'];
                    $CalidadTributaria->resolucion_exento_impuesto_rent = $request['calidad_tributaria'][2]['numero_resolucion'];
                    $CalidadTributaria->fecha_exento_impuesto_rent = $request['calidad_tributaria'][2]['fecha'];
                    $CalidadTributaria->cliente_id = $cliente->id;
                    $CalidadTributaria->save();
                }
            } else {
                if ($request['calidad_tributaria'][0]['opcion'] != '' && $request['calidad_tributaria'][0]['opcion'] != 0 ||  $request['calidad_tributaria'][1]['opcion'] != '' && $request['calidad_tributaria'][1]['opcion'] != 0 || $request['calidad_tributaria'][2]['opcion'] != '' && $request['calidad_tributaria'][2]['opcion'] != 0) {
                    $CalidadTributaria->gran_contribuyente = $request['calidad_tributaria'][0]['opcion'];
                    $CalidadTributaria->resolucion_gran_contribuyente = $request['calidad_tributaria'][0]['numero_resolucion'];
                    $CalidadTributaria->fecha_gran_contribuyente = $request['calidad_tributaria'][0]['fecha'];
                    $CalidadTributaria->auto_retenedor = $request['calidad_tributaria'][1]['opcion'];
                    $CalidadTributaria->resolucion_auto_retenedor = $request['calidad_tributaria'][1]['numero_resolucion'];
                    $CalidadTributaria->fecha_auto_retenedor = $request['calidad_tributaria'][1]['fecha'];
                    $CalidadTributaria->exento_impuesto_rent = $request['calidad_tributaria'][2]['opcion'];
                    $CalidadTributaria->resolucion_exento_impuesto_rent = $request['calidad_tributaria'][2]['numero_resolucion'];
                    $CalidadTributaria->fecha_exento_impuesto_rent = $request['calidad_tributaria'][2]['fecha'];
                    $CalidadTributaria->cliente_id = $id;
                    $CalidadTributaria->save();
                }
            }

            $Contador = Contador::where('cliente_id', '=', $id)
                ->select()
                ->get();
            foreach ($Contador as $item) {
                $item->delete();
            }
            $cont = 0;
            if ($request['nombre_completo_contador'] != '' || $request['tipo_identificacion_contador'] != '' || $request['identificacion_contador'] != '' || $request['telefono_contador'] != '') {
                $Contador = new Contador;
                $Contador->nombre = $request['nombre_completo_contador'];
                $Contador->tipo_identificacion_id = $request['tipo_identificacion_contador'];
                $Contador->identificacion = $request['identificacion_contador'];
                $Contador->telefono = $request['telefono_contador'];
                $Contador->cliente_id = $id;
                $Contador->save();
            }

            $Tesorero = Tesorero::where('cliente_id', '=', $id)
                ->select()
                ->get();
            foreach ($Tesorero as $item) {
                $item->delete();
            }
            $cont = 0;
            // if ($request['nombre_completo_tesorero'] != '' || $request['telefono_tesorero'] != '' || $request['correo_tesorero'] != '') {
            $Tesorero = new Tesorero;
            $Tesorero->nombre = $request['nombre_completo_tesorero'];
            $Tesorero->telefono = $request['telefono_tesorero'];
            $Tesorero->correo = $request['correo_tesorero'];
            $Tesorero->cliente_id = $id;
            $Tesorero->save();
            // }

            $DatoFinanciero = DatoFinanciero::where('cliente_id', '=', $id)
                ->select()
                ->first();
            if ($DatoFinanciero == null) {
                $DatoFinanciero = new DatoFinanciero;
                $DatoFinanciero->ingreso_mensual = $request['ingreso_mensual'];
                $DatoFinanciero->otros_ingresos = $request['otros_ingresos'];
                $DatoFinanciero->total_ingresos = $request['total_ingresos'];
                $DatoFinanciero->costos_gastos_mensual = $request['costos_gastos'];
                $DatoFinanciero->detalle_otros_ingresos = $request['detalle_otros_ingresos'];
                $DatoFinanciero->reintegro_costos_gastos = $request['reintegro_costos'];
                $DatoFinanciero->activos = $request['activos'];
                $DatoFinanciero->pasivos = $request['pasivos'];
                $DatoFinanciero->patrimonio = $request['patrimonio'];
                $DatoFinanciero->cliente_id = $cliente->id;
                $DatoFinanciero->save();
            } else {
                if ($request['ingreso_mensual'] != '' || $request['otros_ingresos'] != '' || $request['total_ingresos'] != '' || $request['costos_gastos'] != '' || $request['detalle_otros_ingresos'] != '' || $request['reintegro_costos'] != '' || $request['activos'] != '' || $request['pasivos'] != '' || $request['patrimonio'] != '') {
                    $DatoFinanciero->ingreso_mensual = $request['ingreso_mensual'];
                    $DatoFinanciero->otros_ingresos = $request['otros_ingresos'];
                    $DatoFinanciero->total_ingresos = $request['total_ingresos'];
                    $DatoFinanciero->costos_gastos_mensual = $request['costos_gastos'];
                    $DatoFinanciero->detalle_otros_ingresos = $request['detalle_otros_ingresos'];
                    $DatoFinanciero->reintegro_costos_gastos = $request['reintegro_costos'];
                    $DatoFinanciero->activos = $request['activos'];
                    $DatoFinanciero->pasivos = $request['pasivos'];
                    $DatoFinanciero->patrimonio = $request['patrimonio'];
                    $DatoFinanciero->cliente_id = $id;
                    $DatoFinanciero->save();
                }
            }


            $origenFondo = OrigenFondo::where('cliente_id', '=', $id)
                ->select()
                ->first();
            if ($origenFondo == null) {
                $origenFondo = new OrigenFondo();
                $origenFondo->tipo_origen_fondos_id = $request['tipo_origen_fondo'];
                $origenFondo->otro_origen = $request['otro_tipo_origen_fondos'];
                $origenFondo->tipo_origen_medios_id = $request['tipo_origen_medios'];
                $origenFondo->tipo_origen_medios2_id = $request['otro_tipo_origen_medios'];
                $origenFondo->alto_manejo_efectivo = $request['alto_manejo_efectivo'];
                $origenFondo->cliente_id = $cliente->id;
                $origenFondo->save();
            } else {
                $origenFondo->tipo_origen_fondos_id = $request['tipo_origen_fondo'];
                $origenFondo->otro_origen = $request['otro_tipo_origen_fondos'];
                $origenFondo->tipo_origen_medios_id = $request['tipo_origen_medios'];
                $origenFondo->tipo_origen_medios2_id = $request['otro_tipo_origen_medios'];
                $origenFondo->alto_manejo_efectivo = $request['alto_manejo_efectivo'];
                $origenFondo->cliente_id = $id;
                $origenFondo->save();
            }


            $OperacionIternacional = OperacionIternacional::where('cliente_id', '=', $id)
                ->select()
                ->first();
            if ($OperacionIternacional == null) {
                $OperacionIternacional = new OperacionIternacional;
                $OperacionIternacional->tipo_operaciones_id = $request['tipo_operacion_internacional'];
                $OperacionIternacional->cliente_id = $cliente->id;
                $OperacionIternacional->save();
            } else {
                if ($request['tipo_operacion_internacional'] != '') {
                    $OperacionIternacional->tipo_operaciones_id = $request['tipo_operacion_internacional'];
                    $OperacionIternacional->cliente_id = $id;
                    $OperacionIternacional->save();
                }
            }

            $ReferenciaBancaria = ReferenciaBancaria::where('cliente_id', '=', $id)
                ->select()
                ->get();
            if ($ReferenciaBancaria == null) {
                foreach ($request['referencias_bancarias'] as $item) {
                    $ReferenciaBancaria = new ReferenciaBancaria;
                    $ReferenciaBancaria->numero_cuenta = $item['numero_cuenta'];
                    $ReferenciaBancaria->tipo_cuenta_id = $item['tipo_cuenta'];
                    $ReferenciaBancaria->sucursal = $item['sucursal'];
                    $ReferenciaBancaria->telefono = $item['telefono'];
                    $ReferenciaBancaria->contacto = $item['contacto'];
                    $ReferenciaBancaria->banco_id = $item['banco_id'];
                    $ReferenciaBancaria->cliente_id = $cliente->id;
                    $ReferenciaBancaria->save();
                }
            } else {
                foreach ($ReferenciaBancaria as $item) {
                    $item->delete();
                }
                $cont = 0;
                foreach ($request['referencias_bancarias'] as $item) {
                    if ($item['numero_cuenta'] != '' || $item['tipo_cuenta'] != '' ||  $item['sucursal'] != '' || $item['telefono'] != '' || $item['contacto'] != '' || $item['banco_id'] != '') {
                        $ReferenciaBancaria = new ReferenciaBancaria;
                        $ReferenciaBancaria->numero_cuenta = $item['numero_cuenta'];
                        $ReferenciaBancaria->tipo_cuenta_id = $item['tipo_cuenta'];
                        $ReferenciaBancaria->sucursal = $item['sucursal'];
                        $ReferenciaBancaria->telefono = $item['telefono'];
                        $ReferenciaBancaria->contacto = $item['contacto'];
                        $ReferenciaBancaria->banco_id = $item['banco_id'];
                        $ReferenciaBancaria->cliente_id = $id;
                        $ReferenciaBancaria->save();
                    }
                }
            }

            $personasExpuestas = PersonasExpuestas::where('cliente_id', '=', $id)
                ->select()
                ->get();
            if ($personasExpuestas == null) {
                foreach ($request['personas_expuestas'] as $item) {
                    $personasExpuestas = new PersonasExpuestas;
                    $personasExpuestas->nombre = $item['nombre'];
                    $personasExpuestas->numero_identificacion = $item['identificacion'];
                    $personasExpuestas->tipo_identificacion_id = $item['tipo_identificacion_id'];
                    $personasExpuestas->parentesco = $item['parentesco'];
                    $personasExpuestas->cliente_id = $cliente->id;
                    $personasExpuestas->save();
                }
            } else {
                foreach ($personasExpuestas as $item) {
                    $item->delete();
                }
                $cont = 0;
                foreach ($request['personas_expuestas'] as $item) {
                    if ($item['nombre'] != '' || $item['identificacion'] != '' ||  $item['tipo_identificacion_id'] != '' || $item['parentesco'] != '') {
                        $personasExpuestas = new PersonasExpuestas;
                        $personasExpuestas->nombre = $item['nombre'];
                        $personasExpuestas->numero_identificacion = $item['identificacion'];
                        $personasExpuestas->tipo_identificacion_id = $item['tipo_identificacion_id'];
                        $personasExpuestas->parentesco = $item['parentesco'];
                        $personasExpuestas->cliente_id = $id;
                        $personasExpuestas->save();
                    }
                }
            }

            $ReferenciaComercial = ReferenciaComercial::where('cliente_id', '=', $id)
                ->select()
                ->get();
            if ($ReferenciaComercial == null) {
                foreach ($request['referencias_comerciales'] as $item) {
                    $ReferenciaComercial = new ReferenciaComercial;
                    $ReferenciaComercial->razon_social = $item['nombre'];
                    $ReferenciaComercial->contacto = $item['contacto'];
                    $ReferenciaComercial->telefono = $item['telefono'];
                    $ReferenciaComercial->cliente_id = $cliente->id;
                    $ReferenciaComercial->save();
                }
            } else {
                foreach ($ReferenciaComercial as $item) {
                    $item->delete();
                }
                $cont = 0;
                foreach ($request['referencias_comerciales'] as $item) {
                    if ($item['nombre'] != '' || $item['contacto'] != '' || $item['telefono'] != '') {
                        $ReferenciaComercial = new ReferenciaComercial;
                        $ReferenciaComercial->razon_social = $item['nombre'];
                        $ReferenciaComercial->contacto = $item['contacto'];
                        $ReferenciaComercial->telefono = $item['telefono'];
                        $ReferenciaComercial->cliente_id = $id;
                        $ReferenciaComercial->save();
                    }
                }
            }

            $clientes_epps = ClienteEpp::where('cliente_id', $id)
                ->select()
                ->get();
            if ($clientes_epps == null) {
                foreach ($request['elementos_epp'] as $index => $item) {
                    if ($item == true) {
                        $cliente_epp = new ClienteEpp;
                        $cliente_epp->epp_id = $index;
                        $cliente_epp->cliente_id = $cliente->id;
                        $cliente_epp->save();
                    }
                }
            } else {
                if (count($clientes_epps) > 0) {
                    foreach ($clientes_epps as $item) {
                        $item->delete();
                    }
                }
                foreach ($request['elementos_epp'] as $index => $item) {
                    if ($item == true) {
                        $cliente_epp = new ClienteEpp;
                        $cliente_epp->epp_id = $index;
                        $cliente_epp->cliente_id = $cliente->id;
                        $cliente_epp->save();
                    }
                }
            }

            $ClienteConvenioBanco = ClienteConvenioBanco::where('cliente_id', $id)
                ->select()
                ->get();
            if ($ClienteConvenioBanco == null) {
                foreach ($request['bancos_agregados'] as $item) {
                    $Cliente_convenio_banco = new ClienteConvenioBanco;
                    $Cliente_convenio_banco->convenio_banco_id = $item['id'];
                    $Cliente_convenio_banco->cliente_id = $cliente->id;
                    $Cliente_convenio_banco->save();
                }
            } else {
                if (count($ClienteConvenioBanco) > 0) {
                    foreach ($ClienteConvenioBanco as $item) {
                        $item->delete();
                    }
                }

                foreach ($request['bancos_agregados'] as $item) {
                    $Cliente_convenio_banco = new ClienteConvenioBanco;
                    $Cliente_convenio_banco->convenio_banco_id = $item['id'];
                    $Cliente_convenio_banco->cliente_id = $cliente->id;
                    $Cliente_convenio_banco->save();
                }
            }

            $ClienteOtroSi = ClienteOtroSi::where('cliente_id', $id)
                ->select()
                ->get();
            if ($ClienteOtroSi == null) {
                foreach ($request['otros_si_agregados'] as $item) {
                    $Cliente_otrosi = new ClienteOtroSi;
                    $Cliente_otrosi->otro_si_id = $item['id'];
                    $Cliente_otrosi->cliente_id = $cliente->id;
                    $Cliente_otrosi->save();
                }
            } else {
                if (count($ClienteOtroSi) > 0) {
                    foreach ($ClienteOtroSi as $item) {
                        $item->delete();
                    }
                }
                foreach ($request['otros_si_agregados'] as $item) {
                    $Cliente_otrosi = new ClienteOtroSi;
                    $Cliente_otrosi->otro_si_id = $item['id'];
                    $Cliente_otrosi->cliente_id = $cliente->id;
                    $Cliente_otrosi->save();
                }
            }


            $ClienteTipoContrato = ClienteTipoContrato::where('cliente_id', $id)
                ->select()
                ->get();
            if ($ClienteTipoContrato == null) {
                foreach ($request['tipos_contratos_agregados'] as $item) {
                    $Cliente_tipo_contrato = new ClienteTipoContrato;
                    $Cliente_tipo_contrato->tipo_contrato_id = $item['id'];
                    $Cliente_tipo_contrato->cliente_id = $cliente->id;
                    $Cliente_tipo_contrato->save();
                }
            } else {
                if (count($ClienteTipoContrato) > 0) {
                    foreach ($ClienteTipoContrato as $item) {
                        $item->delete();
                    }
                }
                foreach ($request['tipos_contratos_agregados'] as $item) {
                    $Cliente_tipo_contrato = new ClienteTipoContrato;
                    $Cliente_tipo_contrato->tipo_contrato_id = $item['id'];
                    $Cliente_tipo_contrato->cliente_id = $cliente->id;
                    $Cliente_tipo_contrato->save();
                }
            }


            $cliente_laboratorio = ClienteLaboratorio::where('cliente_id', $id)
                ->select()
                ->get();
            if (count($cliente_laboratorio) <= 0) {
                foreach ($request['laboratorios_medicos'] as $item) {
                    $cliente_laboratorio = new ClienteLaboratorio;
                    $cliente_laboratorio->laboratorio_id = $item['id'];
                    $cliente_laboratorio->cliente_id = $cliente->id;
                    $cliente_laboratorio->save();
                }
            } else {
                if (count($cliente_laboratorio) > 0) {
                    foreach ($cliente_laboratorio as $item) {
                        $item->delete();
                    }
                }
                foreach ($request['laboratorios_medicos'] as $item) {
                    $cliente_laboratorio = new ClienteLaboratorio;
                    $cliente_laboratorio->laboratorio_id = $item['id'];
                    $cliente_laboratorio->cliente_id = $cliente->id;
                    $cliente_laboratorio->save();
                }
            }
            DB::commit();
            return response()->json(['status' => '200', 'message' => 'ok', 'client' => $cliente->id]);
        } catch (\Exception $e) {
            // Revertir la transacción si se produce alguna excepción
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor intente nuevamente']);
        }
    }

    public function actualizaestadofirma($item_id, $estado_id, $responsable_id = null,  $responsable_actual = null, $estado_inicial = null)
    {

        $user = auth()->user();
        $usuarios = ResponsablesEstadosModel::where('usr_app_clientes_responsable_estado.estado_firma_id', '=', $estado_id)
            ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_clientes_responsable_estado.usuario_id')
            ->select(
                'usuario_id',
                'usr.nombres',
                'usr.apellidos'
            )
            ->get();

        $registro_ingreso = Cliente::where('usr_app_clientes.id', '=', $item_id)
            ->first();
        $estado_inicial = $registro_ingreso->estado_firma_id;
        if ($estado_id == 14) {
            $enviarCorreoDDController = new enviarCorreoDDController;

            $usuarioResponsable = User::where('usr_app_usuarios.id', '=', $registro_ingreso->responsable_id)
                ->select()
                ->first();

            $usuarioComercial = User::where('usr_app_usuarios.vendedor_id', '=', $registro_ingreso->vendedor_id)
                ->select()
                ->first();
            if ($usuarioComercial != null) {
                $correoCOmercial = $usuarioComercial->usuario;
                if ($correoCOmercial != null) {
                    $enviarCorreoDDController->enviarCorreo($correoCOmercial, $registro_ingreso, $registro_ingreso->id, 15, "", $user->usuario, false, true);
                }
            }

            $correoResponsable = $usuarioResponsable->usuario;
            if ($correoResponsable != null) {
                $enviarCorreoDDController->enviarCorreo($correoResponsable, $registro_ingreso, $registro_ingreso->id, 15, "", $user->usuario, false, true);
            }
        }



        $fin_semana_controller = new HorarioLaboralController;



        $estadoController = new EstadosFirmaController;
        if ($estado_inicial != null) {
            $estado_inicial_info = $estadoController->byId($estado_inicial);
            $tiempo_respuesta_segundos =  $estado_inicial_info->tiempo_respuesta * 60;
            $fecha_actual = Carbon::now()->format('Y-m-d H:i:s');
            $last_registro = ClientesSeguimientoEstado::where('usr_app_clientes_seguimiento_estado.cliente_id', $item_id)
                ->select()->orderBy('id', 'desc')->first();
            if (!$last_registro) {
                // Si no hay registro previo, crear uno predeterminado
                $last_registro = new ClientesSeguimientoEstado();
                $last_registro->cliente_id = $item_id;
                $last_registro->responsable_inicial = 'N/A';
                $last_registro->responsable_final = 'N/A';
                $last_registro->estados_firma_inicial = $estado_inicial ?? 0; // Estado inicial predeterminado
                $last_registro->estados_firma_final = $estado_inicial ?? 0; // Estado final predeterminado
                $last_registro->actualiza_registro = 'Sistema';
                $last_registro->oportuno = '2';
                $last_registro->created_at = now();
                $last_registro->updated_at = now();

                $last_registro->save();
            }

            $segundos_desde_unix = Carbon::parse($fecha_actual)->timestamp;
            $fecha_last_registro = $last_registro->created_at;
            $last_registro_segundos = $fecha_last_registro->timestamp;
            $conteo_dia_fin_semana = $fin_semana_controller->cuentaFindes($fecha_last_registro, $fecha_actual);
            $conteo_festivos = $fin_semana_controller->countHolidaysBetweenDates($fecha_last_registro, $fecha_actual);
            $tiempo_cumplimiento_segundos = $segundos_desde_unix - $last_registro_segundos - ($conteo_dia_fin_semana * 86400) - ($conteo_festivos * 86400);

            // 86400 corresponde a la cantidad de segundos que tiene un dia
            $tiempo_cumplimiento_dias =  $tiempo_cumplimiento_segundos / 86400;

            if ($tiempo_cumplimiento_dias >= 1) {
                // 28800 corresponde a 8 horas laborales en segundos
                $tiempo_cumplimiento_laboral =  $tiempo_cumplimiento_dias * 28800;
            } else {
                $tiempo_cumplimiento_laboral =   $tiempo_cumplimiento_segundos;
            }

            if ($tiempo_cumplimiento_laboral <= $tiempo_respuesta_segundos) {
                $last_registro->tiempo_estimado = $estado_inicial_info->tiempo_respuesta;
                $last_registro->oportuno = "1";
                $last_registro->save();
            } else {
                $last_registro->tiempo_estimado = $estado_inicial_info->tiempo_respuesta;
                $last_registro->oportuno = "0";
                $last_registro->save();
            }
        }
        /*  $estado_final_info = $estadoController->byId($estado_id); */

        if ($responsable_id != 0) {
            $responsable = ResponsablesEstadosModel::where('usuario_id', '=', $responsable_id)
                ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_clientes_responsable_estado.usuario_id')
                ->select('usuario_id', 'usr.nombres', 'usr.apellidos')
                ->first();
        } else {
            // Obtener la lista de responsables si no se proporciona $responsable_id
            $usuarios = ResponsablesEstadosModel::where('estado_firma_id', "=", $estado_id)
                ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_clientes_responsable_estado.usuario_id')
                ->select('usuario_id', 'usr.nombres', 'usr.apellidos')
                ->get();

            $numeroResponsables = $usuarios->count();
            $indiceResponsable = $registro_ingreso->id % $numeroResponsables;
            $responsable = $usuarios[$indiceResponsable];
        }
        // Obtener el número total de responsables
        /* $numeroResponsables = $usuarios->count(); */

        // Obtener el registro de ingreso
        $permisos = $this->validaPermiso();


        if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user->id && !in_array('34', $permisos)) {
            return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
        }

        // Asignar a cada registro d e ingreso un responsable
        /*   $indiceResponsable = $registro_ingreso->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable]; */


        $seguimiento_estado = new ClientesSeguimientoEstado;
        $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable != null ?  str_replace("null", "", $registro_ingreso->responsable) : str_replace("null", "", $responsable_actual);
        $seguimiento_estado->responsable_final = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);;
        $seguimiento_estado->estados_firma_inicial = $estado_inicial != null ? $estado_inicial : $registro_ingreso->estado_firma_id;
        $seguimiento_estado->estados_firma_final =   $estado_id;
        $seguimiento_estado->cliente_id =  $item_id;
        $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' .  $user->apellidos;
        $seguimiento_estado->oportuno = "2";
        $seguimiento_estado->save();

        // Actualizar el registro de ingreso con el estado y el responsable
        $registro_ingreso->estado_firma_id = $estado_id;
        $registro_ingreso->responsable_id = $responsable->usuario_id;
        $registro_ingreso->responsable = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);

        $this->eventoSocket($responsable->usuario_id);

        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }

    public function actualizaResponsableCliente($item_id, $responsable_id, $nombre_responsable)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $registro_ingreso = cliente::where('usr_app_clientes.id', '=', $item_id)
                ->first();

            $permisos = $this->validaPermiso();

            $last_registro = ClientesSeguimientoEstado::where('usr_app_clientes_seguimiento_estado.cliente_id', $item_id)
                ->select()->orderBy('id', 'desc')->first();
            if ($last_registro != null) {
                $registro_ingreso = Cliente::where('usr_app_clientes.id', '=', $item_id)
                    ->first();
                $estado_inicial = $registro_ingreso->estado_firma_id;
                $estadoController = new EstadosFirmaController;
                $estado_inicial_info = $estadoController->byId($estado_inicial);
                $tiempo_respuesta_segundos =  $estado_inicial_info->tiempo_respuesta * 60;
                $fecha_actual = Carbon::now()->format('Y-m-d H:i:s');
                $fin_semana_controller = new HorarioLaboralController;
                $segundos_desde_unix = Carbon::parse($fecha_actual)->timestamp;
                $fecha_last_registro = $last_registro->created_at;
                $last_registro_segundos = $fecha_last_registro->timestamp;
                $conteo_dia_fin_semana = $fin_semana_controller->cuentaFindes($fecha_last_registro, $fecha_actual);
                $conteo_festivos = $fin_semana_controller->countHolidaysBetweenDates($fecha_last_registro, $fecha_actual);
                $tiempo_cumplimiento_segundos = $segundos_desde_unix - $last_registro_segundos - ($conteo_dia_fin_semana * 86400) - ($conteo_festivos * 86400);

                // 86400 corresponde a la cantidad de segundos que tiene un dia
                $tiempo_cumplimiento_dias =  $tiempo_cumplimiento_segundos / 86400;

                if ($tiempo_cumplimiento_dias >= 1) {
                    // 28800 corresponde a 8 horas laborales en segundos
                    $tiempo_cumplimiento_laboral =  $tiempo_cumplimiento_dias * 28800;
                } else {
                    $tiempo_cumplimiento_laboral =   $tiempo_cumplimiento_segundos;
                }

                if ($tiempo_cumplimiento_laboral <= $tiempo_respuesta_segundos) {
                    $last_registro->tiempo_estimado = $estado_inicial_info->tiempo_respuesta;
                    $last_registro->oportuno = "1";
                    $last_registro->save();
                } else {
                    $last_registro->tiempo_estimado = $estado_inicial_info->tiempo_respuesta;
                    $last_registro->oportuno = "0";
                    $last_registro->save();
                }
            }


            if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user->id && !in_array('34', $permisos)) {
                return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
            }

            $seguimiento_estado = new ClientesSeguimientoEstado;
            $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable;
            $seguimiento_estado->responsable_final = $nombre_responsable;
            $seguimiento_estado->estados_firma_inicial =  $registro_ingreso->estado_firma_id;
            $seguimiento_estado->estados_firma_final =  $registro_ingreso->estado_firma_id;
            $seguimiento_estado->cliente_id =  $item_id;
            $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' .  $user->apellidos;
            $seguimiento_estado->oportuno =   "2";

            $seguimiento_estado->save();

            $registro_ingreso->responsable_anterior = $registro_ingreso->responsable;
            $registro_ingreso->responsable = $nombre_responsable;
            $registro_ingreso->responsable_id = $responsable_id;
            $registro_ingreso->save();
            $seguimiento = new ClientesSeguimientoGuardado;
            $seguimiento->estado_firma_id = $registro_ingreso->estado_firma_id;
            $seguimiento->usuario = $user->nombres . ' ' .  $user->apellidos; //Preguntar con Andres si debe ser el ususario
            $seguimiento->cliente_id = $item_id;
            $seguimiento->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        } catch (\Exception $e) {

            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
        }
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

    public function versionformulario($asJson = true)
    {
        $currentDate = now(); // Obtener la fecha actual

        // Establecer la versión del formulario basada en la fecha actual
        $version = $currentDate->isAfter('2024-08-04') ? 2 : 1;

        // Realizar la consulta con la versión seleccionada
        $result = VersionFormularioDD::where('version_formulario', $version)
            ->select()
            ->get();

        if (!$asJson) {
            return $result;
        }
        return response()->json($result);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $error_carga_archivos = null)
    {
        $result = Cliente::find($id);
        if ($result->delete()) {
            if ($error_carga_archivos == null) {
                return response()->json("registro borrado Con Exito");
            }
        } else {
            return response()->json("Error al borrar registro");
        }
    }
    public function eventoSocket($id)
    {
        try {
            $data = [
                'encargado_id' => $id,
                'mensaje' => 'Te han asignado una nueva actividad en el módulo Debida diligencia.'
            ];
            event(new NotificacionSeiya($data));
        } catch (\Throwable $th) {
        }
        return;
    }
    public function generarPdf($id, $outputToBrowser = true)
    {

        try {
            // Obtener los datos
            $versiones = $this->versionformulario(false);
            $result = $this->getbyid($id, false);

            $resultGenerales = [
                'Número de radicado:' => $result['numero_radicado'],
                'Tipo de operación:' => $result['tipo_operacion'],
                'Tipo de cliente:' => $result['tipo_cliente'],
                'Tipo de proveedor:' => $result['tipo_proveedor'],
                'Tipo de persona:' => $result['tipo_persona'],
                'Tipo de identificación:' => $result['tipo_identificacion'],
                'Número de identificación:' => $result['numero_identificacion'],
                'Fecha de expedición:' => $result['fecha_exp_documento'],
                'Nombre completo/Razón social:' => $result['razon_social'],
                'NIT:' => $result['nit'] . '-' . $result['digito_verificacion'],
                'Teléfono:' => $result['telefono_empresa'],
                'Número celular:' => $result['celular_empresa'],
                'Correo:' => $result['correo_empresa'],
                'Fecha de constitución:' => $result['fecha_constitucion'],
                'Número de empleados:' => $result['numero_empleados'],
                'Código ciiu:' => $result['codigo_ciiu'],
                'Actividad ciiu:' => $result['codigo_actividad_ciiu'],
                'Estrato socio económico (ubicación empresa):' => $result['estrato'],
                'Departamento del rut:' => $result['departamento_rut'],
                'Ciudad del rut:' => $result['municipio_rut'],
                'Pais de ubicación:' => $result['pais'],
                'Departamento de ubicación:' => $result['departamento'],
                'Ciudad de ubicación:' => $result['municipio'],
                'Dirección de la empresa:' => $result['direccion_empresa'],
                'Persona de contacto:' => $result['contacto_empresa'],
                'Sociedad comercial:' => $result['sociedad_comercial'],
                'Otra ¿Cuál?:' => $result['otra'],
                'Periocidad de pagos:' => $result['periodicidad_liquidacion'],
                'Plazo pagos(días):' => $result['plazo_pago'],
                'Pais prestación servicio:' => $result['pais_prestacion_servicio'],
                'Departamento prestación servicio:' => $result['departamento_prestacion_servicio'],
                'Municipio prestación servicio:' => $result['municipio_prestacion_servicio'],
                'AIU negociado:' => $result['aiu_negociado'],
                'Ejecutivo comercial:' => $result['vendedor'],
                'Observaciones acuerdos comerciales:' => $result['acuerdo_comercial'],
                'Jornada laboral:' => $result['jornada_laboral'],
                'Rotación de personal:' => $result['rotacion_personal'],
                'La empresa es extranjera:' => $result['empresa_extranjera'],
                '¿Es empresa del exterior radicada en colombia?:' => $result['empresa_en_exterior'],
                '¿Tiene vinculos con alguna empresa activa en saitemp?:' => $result['vinculos_empresa'],
                'Empleados directos empresa usuaria:' => $result['numero_empleados_directos'],
                '¿Actualmente tienen personal vinculado con empresa temporal?:' => $result['vinculado_empresa_temporal'],
                '¿Se realizó la visita presencial a las instalaciones del cliente?:' => $result['visita_presencial'],
            ];



            if (!$result) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }
            $url = public_path('public/upload/logo1.png');
            // Iniciar TCPDF
            $pdf = new \TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Saitemp SAS');
            $pdf->SetTitle('Sagrilaft de Cliente');
            $pdf->SetSubject('Detalle del Cliente');
            /*  $pdf->SetMargins(10, 10, 10); */


            $url = public_path('upload/logo1.png');
            $pdf->setPrintHeader(false);
            $pdf->AddPage();
            /*    $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0); */
            // Agregar imagen de fondo
            $url2 = public_path('/upload/MEMBRETE.png');
            /*  $pdf->Image($url2, 0, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0); */

            /* $pdf->SetMargins(15, 40, 15);
            $pdf->SetAutoPageBreak(true, 40); */
            $pdf->Ln(30);
            // Verificar que el archivo existe
            if (!file_exists($url)) {
                throw new Exception("La imagen no existe en la ruta especificada.");
            }

            // Convertir la imagen en base64 si el método estándar no funciona
            $imageData = base64_encode(file_get_contents($url));
            $imageSrc = 'data:image/png;base64,' . $imageData;
            // Iniciar HTML
            $html = '
            <style>
                .version{
                font-size: 11px;
           
                }
                h1, h2, h4 {
                    color: #333;
                    text-align: center;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th, table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                table th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }
                .section-title {
                    font-size: 40px;
                    font-weight: bold;
                    margin-top: 20px;
                    margin-bottom: 10px;
                    text-align: left;
                }
            </style>
            ';
            $html = '<table cellpadding="4" style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">
            <tr>
           
                <th colspan="2" style="text-align: center;border: 1px #000000 solid;">
                    <h5 >SAGRILAFT</h5>
                    <h6>Sistema de Autocontrol y Gestión del Riesgo Integral de Lavado de Activos y Financiación del Terrorismo FORMATO ÚNICO DE VINCULACIÓN DE CONTRAPARTES.</h5>
                </th>
            ';

            $html .= '
            <td style="text-align: left;border: 1px #000000 solid;">';

            foreach ($versiones as $version) {
                $html .= '<p style="font-size: 8px;">' . htmlspecialchars($version->descripcion) . '</p>';
            }

            $html .= '</td>
            </tr><tr><td colspan="4" style="text-align: center;"><h4>Datos generales:</h4></td></tr>';
            $indiceGenerales = 0;
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $clavePrev = "";
            $valorPrev = "";
            foreach ($resultGenerales as  $clave => $valor) {

                if ($indiceGenerales % 2 == 0) {
                    $clavePrev = $clave;
                    $valorPrev = $valor;
                } else {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $clavePrev . '</b> ' . $valorPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                }


                $indiceGenerales++;
            }

            if ($indiceGenerales % 2 == 1) {
                $html .= '<tr><td style="font-size:9px;"><b>' . $clavePrev . '</b> ' . $valorPrev . '</td></tr>';
            }
            $html .= '</table>';

            if ($result['tipo_cliente_id'] == 1) {
                $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;"><tr><td colspan="2" style="text-align: center;"><h4>Servicios solicitados:</h4></td></tr></table>';
                if ($result['contratacion_directa'] == 1) {
                    $contratacionDirecta = "Si";
                } else {
                    $contratacionDirecta = "No";
                }
                if ($result['atraccion_seleccion'] == 1) {
                    $atraccionSeleccion = "Si";
                } else {
                    $atraccionSeleccion = "No";
                }
                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
                $html .= ' <tr>
                <th style="font-size:9px;"><b>Contratacion directa:</b> ' . $contratacionDirecta . '</th>
                <td style="font-size:9px;"><b>Atracción y selección de talento:</b> ' . $atraccionSeleccion . '</td>
                </tr> ';
                $html .= '</table>';
                $resultContratacion = [
                    'Contacto notificación ingreso personal:' => $result['contratacion_contacto'],
                    'Cargo del contacto:' => $result['contratacion_cargo'],
                    'Teléfono del contacto:' => $result['contratacion_telefono'],
                    'Número celular del contacto:' => $result['contratacion_celular'],
                    'Hora de ingreso del personal primer día:' => $result['contratacion_hora_ingreso'],
                    'Hora límite para confirmar ingreso de personal:' => $result['contratacion_hora_confirmacion'],
                    'Correo electrónico notificación ingreso personal:' => $result['contratacion_correo'],
                    '¿Necesita carnet de manipulación de alimentos?:' => $result['contratacion_manipulacion_alimentos'],
                    '¿se require carnet corporativo con especificaciones distintas?:' => $result['contratacion_carnet_corporativo'],
                    '¿Se requieren tallas de uniformes?:' => $result['contratacion_tallas_uniforme'],
                    '¿Empresa suministra transporte?:' => $result['contratacion_suministra_transporte'],
                    '¿La empresa suministra alimentación?:' => $result['contratacion_suministra_alimentacion'],
                    '¿Realiza pago en efectivo?:' => $result['contratacion_pago_efectivo'],
                    '¿La empresa paga los días 31?:' => $result['contratacion_pagos_31'],

                ];
                $indiceContratacion = 0;
                $claveConPrev = "";
                $valorConPrev = "";
                $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                    <td colspan="2" style="text-align: center;"><h4>Contratación:</h4></td>
                </tr></table>';
                $html .= '
                <table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';

                foreach ($resultContratacion as $clave => $valor) {
                    if ($indiceContratacion % 2 == 0) {
                        $claveConPrev = $clave;
                        $valorConPrev = $valor;
                    } else {
                        $html .= '<tr><td style="font-size:9px;"><b>' . $claveConPrev . '</b> ' . $valorConPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                    }

                    $indiceContratacion++;
                }

                if ($indiceContratacion % 2 == 1) {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $claveConPrev . '</b> ' . $valorConPrev . '</td></tr>';
                }
                $html .= '<tr><th><b>Otro si solicitados:</b></th></tr>';

                $otroSiIndice = 0;
                $otroSiValPrev = "";

                if (count($result['otrosi']) > 0) {

                    foreach ($result['otrosi'] as $index => $otro) {
                        if ($index % 2 == 0) {
                            $otroSiValPrev = $otro['nombre'];
                        } else {
                            $html .= '<tr><td style="font-size:9px;">-' . $otroSiValPrev . '</td><td style="font-size:9px;">-' . $otro['nombre'] . '</td></tr>';
                        }

                        $otroSiIndice++;
                    }

                    if ($otroSiIndice % 2 == 1) {
                        $html .= '<tr><td style="font-size:9px;">-' . $otroSiValPrev . '</td></tr>';
                    }
                }


                $convBancIndice = 0;
                $convBancValPrev = "";

                $html .= '<tr><th><b>Convenio bancos:</b></th></tr>';
                if (count($result['convenios_banco']) > 0) {
                    foreach ($result['convenios_banco'] as $index => $banco) {
                        if ($index % 2 == 0) {
                            $convBancValPrev = $banco['nombre'];
                        } else {
                            $html .= '<tr><td style="font-size:9px;">-' . $convBancValPrev . '</td><td style="font-size:9px;">-' . $banco['nombre'] . '</td></tr>';
                        }

                        $convBancIndice++;
                    }


                    if ($convBancIndice % 2 == 1) {
                        $html .= '<tr><td style="font-size:9px;">-' . $convBancValPrev . '</td></tr>';
                    }
                }

                $tipoConIndice = 0;
                $tipoConValPrev = "";
                $html .= '<tr><th><b>Tipos de contrato:</b></th></tr>';
                if (count($result['tipos_contrato']) > 0) {
                    foreach ($result['tipos_contrato'] as $index => $contrato) {
                        if ($index % 2 == 0) {
                            $tipoConValPrev = $contrato['nombre'];
                        } else {
                            $html .= '<tr><td style="font-size:9px;">-' . $tipoConValPrev . '</td><td style="font-size:9px;">-' . $contrato['nombre'] . '</td></tr>';
                        }

                        $tipoConIndice++;
                    }


                    if ($tipoConIndice % 2 == 1) {
                        $html .= '<tr><td style="font-size:9px;">-' . $tipoConValPrev . '</td></tr>';
                    }
                }


                if (count($result['ubicacion_laboratorio']) > 0) {
                    $html .= '<tr><th style="font-size:9px;"><b>País ubicación laboratorio médico: </b> ' . $result['ubicacion_laboratorio'][0]['pais'] . '</th><td style="font-size:9px;"><b>Departamento ubicación laboratorio médico:</b> ' . $result['ubicacion_laboratorio'][0]['departamento'] . '</td></tr>
                 <tr><th style="font-size:9px;"><b>Ciudad ubicación laboratorio médico:</b>' . $result['ubicacion_laboratorio'][0]['municipio'] . '</th><td></td></tr>
                ';
                }

                $labMedIndice = 0;
                $labMedValPrev = "";

                $html .= '<tr><th><b>Laboratorios médicos:</b></th></tr>';
                if (count($result['laboratorios_agregados']) > 0) {
                    foreach ($result['laboratorios_agregados'] as $index => $lab) {
                        if ($index % 2 == 0) {
                            $labMedValPrev = $lab['nombre'];
                        } else {
                            $html .= '<tr><td style="font-size:9px;">-' . $labMedValPrev . '</td><td style="font-size:9px;">-' . $lab['nombre'] . '</td></tr>';
                        }

                        $labMedIndice++;
                    }


                    if ($labMedIndice % 2 == 1) {
                        $html .= '<tr><td style="font-size:9px;">-' . $labMedValPrev . '</td></tr>';
                    }
                }

                $resultFacturacion = [
                    'Contacto:' => $result['facturacion_contacto'],
                    'Cargo:' => $result['facturacion_cargo'],
                    'Teléfono:' => $result['facturacion_telefono'],
                    'Celular:' => $result['facturacion_celular'],
                    'Correo electrónico:' => $result['facturacion_correo'],
                    'Factura única o por CECO:' => $result['facturacion_factura_unica'],
                    'Fecha de corte para recibir las facturas:' => $result['facturacion_fecha_corte'],
                    'Persona encargada de recibir la factura:' => $result['facturacion_encargado_factura'],
                    '¿Requiere anexo de la factura?:' => $result['requiere_anexo_factura'],
                ];

                $indiceFactura = 0;
                $claveFacPrev = "";
                $valorFacPrev = "";
                $html .= '</table>';
                $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                <td colspan="2" style="text-align: center;"><h4>Facturación:</h4></td>
            </tr></table>';

                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';

                foreach ($resultFacturacion as $clave => $valor) {
                    if ($indiceFactura % 2 == 0) {
                        $claveFacPrev = $clave;
                        $valorFacPrev = $valor;
                    } else {
                        $html .= '<tr><td style="font-size:9px;"><b>' . $claveFacPrev . '</b> ' . $valorFacPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                    }

                    $indiceFactura++;
                }

                if ($indiceFactura % 2 == 1) {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $claveFacPrev . '</b> ' . $valorFacPrev . '</td></tr>';
                }

                $resultSST = [
                    'Riesgo de la empresa(ARL):' => $result['riesgo_cliente'],
                    '¿Realizan trabajo de alto riesgo?:' => $result['trabajo_alto_riesgo'],
                    'Accidentalidad:' => $result['accidentalidad'],
                    'Cuenta con persona encargada de SST:' => $result['encargado_sst'],
                    'Nombre encargado SST:' => $result['nombre_encargado_sst'],
                    'Cargo analista SST:' => $result['cargo_encargado_sst'],
                    '¿Realizan inducción y entrenamiento?:' => $result['induccion_entrenamiento'],
                    '¿Entregan dotación?:' => $result['entrega_dotacion'],
                    '¿Fue evaluado el SGST por la ARL?:' => $result['evaluado_arl'],
                    '¿Entrega EPP?:' => $result['entrega_epp'],
                ];

                $indiceSST = 0;
                $claveSSTPrev = "";
                $valorSSTPrev = "";

                $html .= '</table>';
                $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                <td colspan="2" style="text-align: center;"><h4>Seguridad y salud en el trabajo:</h4></td>
            </tr></table>';

                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';

                foreach ($resultSST as $clave => $valor) {
                    if ($indiceSST % 2 == 0) {
                        $claveSSTPrev = $clave;
                        $valorSSTPrev = $valor;
                    } else {
                        $html .= '<tr><td style="font-size:9px;"><b>' . $claveSSTPrev . '</b> ' . $valorSSTPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                    }

                    $indiceSST++;
                }

                if ($indiceSST % 2 == 1) {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $claveSSTPrev . '</b> ' . $valorSSTPrev . '</td></tr>';
                }


                $eppMedIndice = 0;
                $eppMedValPrev = "";
                if ($result['entrega_epp'] == "Si") {

                    $html .= '<tr><th><b>Epp solicitados:</b></th></tr>';

                    if (count($result['clientes_epps']) > 0) {
                        foreach ($result['clientes_epps'] as $index => $epp) {

                            $html .= '<tr><td style="font-size:9px;">-' . $epp['nombre'] . '</td></tr>';


                            $eppMedIndice++;
                        }
                    }
                    $html .= '</table>';
                }

                $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                <td colspan="2" style="text-align: center;"><h4>Cargos:</h4></td>
            </tr></table>';
                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
                if (count($result['cargos2']) > 0) {
                    $html .= '<tr><th><b>Número de cargos registrados:</b></th><td>' . count($result['cargos2']) . '</td></tr>';
                    foreach ($result['cargos2'] as $index => $cargo) {
                        $html .= '<tr><td colspan="2"><h4>Cargo: ' . $index + 1 . '</h4></td></tr>';
                        $html .= '
                    <tr><th style="font-size:9px;"><b>Tipo de cargo:</b> ' . $cargo['tipo_cargo'] . '</th><td style="font-size:9px;"> <b>Categoria del cargo:</b>' . $cargo['categoria'] . '</td> </tr>
                 
                    <tr><th style="font-size:9px;"><b>Cargo:</b> ' . $cargo['cargo'] . '</th><td style="font-size:9px;"><b>Riesgo del cargo(ARL): </b>' . $cargo['riesgo_laboral'] . '</td></tr>
              
                    <tr><th style="font-size:9px;"><b>Funciones del cargo:</b>' . $cargo['funcion_cargo'] . '</th><td></td></tr>
                   ';
                        $examIndice = 0;
                        $examValPrev = "";
                        $html .= '<tr><th><b>Exámenes:</b></th><td style="text-align:left;"></td></tr>';
                        if (count($cargo['examenes']) > 0) {
                            foreach ($cargo['examenes'] as $index => $examen) {
                                if ($index % 2 == 0) {
                                    $examValPrev = $examen['nombre'];
                                } else {
                                    $html .= '<tr><td style="font-size:9px;">-' . $examValPrev . '</td><td style="font-size:9px;">-' . $examen['nombre'] . '</td></tr>';
                                }

                                $examIndice++;
                            }


                            if ($examIndice % 2 == 1) {
                                $html .= '<tr><td style="font-size:9px;">-' . $examValPrev . '</td></tr>';
                            }
                        }


                        if (count($cargo['recomendaciones']) > 0) {

                            $html .= '<tr><th colspan="2" style="text-align:left; font-size:9px; margin:20px;"><b>Orientaciones específicas para los exámenes:</b> ' . $cargo['recomendaciones'][0]['recomendacion1'] . '</th></tr>';

                            $html .= '<tr><th colspan="2" style="text-align:left; font-size:9px; margin:20px;"><b>Patologías que restringen la labor:</b> ' . $cargo['recomendaciones'][0]['recomendacion2'] . '</th></tr>';
                        }
                    }
                }
                $html .= ' </table>';
            }


            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Información financiera:</h4></td>
        </tr></table>';
            if (count($result['accionistas']) > 0) {
                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
                foreach ($result['accionistas'] as $index => $accionista) {
                    $html .= '<tr><td colspan="2"><h4>Accionista ' . $index + 1 . '</h4></td></tr>';
                    $html .= '<tr><th style="font-size:9px;"><b>Tipo de identificación: </b> ' . $accionista['des_tip'] . '</th><td style="font-size:9px;"><b>Identificación:</b> ' . $accionista['identificacion'] . '</td></tr>';
                    $html .= '<tr><th style="font-size:9px;"><b>Socio/accionista: </b>' . $accionista['socio'] . '</th><td style="font-size:9px;"><b>Porcentaje participación:</b>' . $accionista['participacion'] . '</td></tr>';
                }
                $html .= '</table>';
            }

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Representantes legales:</h4></td>
        </tr></table>';


            if (count($result['representantes_legales']) > 0) {
                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
                foreach ($result['representantes_legales'] as $index => $representante) {
                    $html .= '<tr><td colspan="2"><h4>Representante legal ' . $index + 1 . '</h4></td></tr>';
                    $html .= '<tr><th style="font-size:9px;"><b>Tipo de identificación:</b> ' . $representante['des_tip'] . '</th><td style="font-size:9px;"><b>Identificación:</b> ' . $representante['identificacion'] . '</td></tr>';

                    $html .= '<tr><th style="font-size:9px;"><b>Nombre:</b> ' . $representante['nombre'] . '</th><td style="font-size:9px;"><b>Correo electrónico:</b> ' . $representante['pais'] . '</td></tr>';

                    $html .= '<tr><th style="font-size:9px;"><b>Correo electrónico:</b> ' . $representante['departamento'] . '</th><td style="font-size:9px;"><b>Correo electrónico:</b>' . $representante['ciudad_expedicion'] . '</td></tr>';

                    $html .= '<tr><th style="font-size:9px;"><b>Número celular:</b> ' . $representante['telefono'] . '</th><td style="font-size:9px;"><b>Correo electrónico:</b>' . $representante['correo'] . '</td></tr>';
                }
                $html .= '</table>';
            }
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Miembros de la junta directiva:</h4></td>
        </tr></table>';

            if (count($result['junta_directiva']) > 0) {
                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
                foreach ($result['junta_directiva'] as $index => $miembro) {
                    $html .= '<tr><td colspan="2"><h4>Miembro ' . $index + 1 . '</h4></td></tr>';
                    $html .= '<tr><th style="font-size:9px;"><b>Tipo de identificación: </b>' . $miembro['des_tip'] . '</th><td style="font-size:9px;"><b>Identificación:</b> ' . $miembro['identificacion'] . '</td></tr>';
                    $html .= '<tr><th style="font-size:9px;"><b>Nombre:</b> ' . $miembro['nombre'] . '</th><td></td></tr>';
                }
                $html .= '</table>';
            } else {
                $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;"><tr><th><p>La empresa no cuenta con Junta directiva</p></th><td></td></tr></table>';
            }

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Calidad tributaria:</h4></td>
        </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            if ($result['responsable_inpuesto_ventas'] == 1) {
                $html .= '<tr><th style="font-size:9px;"><b>Responsable de Impuestos a las Ventas:</b> Si</th><td style="font-size:9px;"><b>Correo para factura electrónica:</b> ' . $result['correo_facturacion_electronica'] . '</td></tr>';
            } else {
                $html .= '<tr><th style="font-size:9px;"><b>Responsable de Impuestos a las Ventas:</b> No</th><td style="font-size:9px;"><b>Correo para factura electrónica:</b> ' . $result['correo_facturacion_electronica'] . '</td></tr>';
            }


            if ($result['calidad_tributaria'][0]['gran_contribuyente'] == 1) {
                $html .= '<tr><th style="font-size:9px;"><b>Sucursal de facturación:</b>' . $result['sucursal_facturacion'] . '</th><td style="font-size:9px;"><b>¿Es Gran Contribuyente?:</b> Si</td></tr>';

                $html .= '<tr><th style="font-size:9px;"><b>Fecha de resolución gran contribuyente:</b>' . $result['calidad_tributaria'][0]['fecha_gran_contribuyente'] . '</th><td style="font-size:9px;"><b>Número de resolución gran contribuyente:</b>' . $result['calidad_tributaria'][0]['resolucion_gran_contribuyente'] . '</td></tr>';
            } else {
                $html .= '<tr><th style="font-size:9px;"><b>Sucursal de facturación:</b>' . $result['sucursal_facturacion'] . '</th><td style="font-size:9px;"><b>¿Es Gran Contribuyente?: </b> No</td></tr>';
            }
            if ($result['calidad_tributaria'][0]['auto_retenedor'] == 1) {
                $html .= '<tr><th style="font-size:9px;"><b>¿Es auto-retenedor?:</b> Si</th><td style="font-size:9px;"><b>Número de resolución auto-retenedor:</b> ' . $result['calidad_tributaria'][0]['resolucion_auto_retenedor'] . '</td></tr>';

                $html .= '<tr><th style="font-size:9px;"><b>Fecha de resolución auto-retenedor:</b>' . $result['calidad_tributaria'][0]['fecha_auto_retenedor'] . '</th><td></td></tr>';
            } else {
                $html .= '<tr><th style="font-size:9px;"><b>¿Es auto-retenedor?:</b> No</th><td></td></tr>';
            }
            if ($result['calidad_tributaria'][0]['resolucion_exento_impuesto_rent'] == 1) {
                $html .= '<tr><th style="font-size:9px;"><b>¿Exento de Impuesto a la Renta?:</b> Si</th><td style="font-size:9px;"><b>Número de resolución impuesto a la renta:</b>' . $result['calidad_tributaria'][0]['resolucion_exento_impuesto_rent'] . '</td></tr>';

                $html .= '<tr><th style="font-size:9px;"><b>Fecha de resolución impuesto a la renta:</b> ' . $result['calidad_tributaria'][0]['fecha_exento_impuesto_rent'] . '</th><td></td></tr>';
            } else {
                $html .= '<tr><th style="font-size:9px;"><b>¿Exento de Impuesto a la Renta?:</b> No</th><td></td></tr>';
            }
            $html .= '</table>';

            $resultContador = [
                'Tipo de identificación:' => $result['tipo_identificacion_contador'],
                'Identificación:' => $result['identificacion_contador'],
                'Nombre:' => $result['nombre_contador'],
                'Teléfono:' => $result['telefono_contador'],
            ];

            $indiceContador = 0;
            $claveContaPrev = "";
            $valorContaPrev = "";

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Datos del contador:</h4></td>
        </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            foreach ($resultContador as  $clave => $valor) {

                if ($indiceContador % 2 == 0) {
                    $claveContaPrev = $clave;
                    $valorContaPrev = $valor;
                } else {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $claveContaPrev . '</b> ' . $valorContaPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                }


                $indiceContador++;
            }

            if ($indiceContador % 2 == 1) {
                $html .= '<tr><td style="font-size:9px;"><b>' . $claveContaPrev . '</b> ' . $valorContaPrev . '</td></tr>';
            }
            $html .= '</table>';

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Datos del tesorero:</h4></td>
        </tr></table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr><th style="font-size:9px;"><b>Nombre:</b> ' . $result['nombre_tesorero'] . '</th><td style="font-size:9px;"><b>Teléfono:</b> ' . $result['telefono_tesorero'] . '</td></tr>';
            $html .= '<tr><th style="font-size:9px;"><b>Correo:</b> ' . $result['correo_tesorero'] . '</th><td></td></tr>';
            $html .= '</table>';

            $resulUltimoPeriodo = [
                'Ingreso mensual:' => $result['ingreso_mensual'],
                'Costos y Gastos Mensual:' => $result['costos_gastos_mensual'],
                'Activos:' => $result['activos'],
                'Otros ingresos:' => $result['otros_ingresos'],
                'Detalle de otros ingresos:' => $result['detalle_otros_ingresos'],
                'Pasivos:' => $result['pasivos'],
                'Total ingresos:' => $result['total_ingresos'],
                'Reintegro de costos y gastos:' => $result['reintegro_costos_gastos'],
                'Patrimonio:' => $result['patrimonio'],
            ];

            $indiceUltimoPeriodo = 0;
            $claveUltimoPrev = "";
            $valorUltimoPrev = "";

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Último periodo contable:</h4></td>
        </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            foreach ($resulUltimoPeriodo as  $clave => $valor) {

                if ($indiceUltimoPeriodo % 2 == 0) {
                    $claveUltimoPrev = $clave;
                    $valorUltimoPrev = $valor;
                } else {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $claveUltimoPrev . '</b> ' . $valorUltimoPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                }


                $indiceUltimoPeriodo++;
            }

            if ($indiceUltimoPeriodo % 2 == 1) {
                $html .= '<tr><td style="font-size:9px;"><b>' . $claveUltimoPrev . '</b> ' . $valorUltimoPrev . '</td></tr>';
            }
            $html .= '</table>';


            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Operaciones internacionales:</h4></td>
        </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            if ($result['operaciones_internacionales'] == 1) {
                $html .= '<tr><th style="font-size:9px;"><b>¿Realiza operaciones en moneda extranjera?:</b> Si</th><td style="font-size:9px;"><b>¿Cuál?:</b> ' . $result['tipo_operacion_internacional'] . '</td></tr>';
            } else {
                $html .= '<tr><th style="font-size:9px;"><b>¿Realiza operaciones en moneda extranjera?:</b> No</th><td style="font-size:9px;"><b>¿Cuál?:</b> ' . $result['tipo_operacion_internacional'] . '</td></tr>';
            }

            $html .= '</table>';


            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Referencias bancarias:</h4></td>
        </tr></table>';


            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            if (count($result['referencia_bancaria'])) {
                foreach ($result['referencia_bancaria'] as $index => $referencia) {
                    $html .= '<tr><td colspan="2"><h4>Referencia ' . $index + 1 . ' </h4></td></tr> ';
                    $html .= '<tr style="font-size:9px;"><th><b>Banco:</b> ' . $referencia['banco'] . '</th><td><b>Número de cuenta:</b> ' . $referencia['numero_cuenta'] . '</td></tr>';
                    $html .= '<tr style="font-size:9px;"><th><b>Tipo cuenta:</b> ' . $referencia['tipo_cuenta'] . '</th><td><b>Sucursal:</b> ' . $referencia['sucursal'] . '</td></tr>';
                    $html .= '<tr style="font-size:9px;"><th><b>Teléfono:</b> ' . $referencia['telefono'] . '</th><td><b>Contacto:</b> ' . $referencia['contacto'] . '</td></tr>';
                }
            }

            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Referencias comerciales:</h4></td>
        </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            if (count($result['referencia_comercial'])) {
                foreach ($result['referencia_comercial'] as $index => $referencia) {
                    $html .= '<tr><td colspan="2"><h4>Referencia ' . $index + 1 . ' </h4></td></tr> ';
                    $html .= '<tr style="font-size:9px;"><th><b>Nombre:</b> ' . $referencia['nombre'] . '</th><td><b>Contacto:</b> ' . $referencia['contacto'] . '</td></tr>';

                    $html .= '<tr style="font-size:9px;"><th><b>Teléfono:</b> ' . $referencia['telefono'] . '</th><td></td></tr>';
                }
            }
            $html .= '</table>';

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Declaraciones y autorizaciones:</h4></td>
            </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:2px; border-collapse: collapse;">';
            $html .= '<tr><td><p style="text-align:left; font-size:9px;">Cumplo con alguno de los siguientes atributos o tengo un vínculo familiar (cónyuge o compañero permanente, padres, abuelos, hijos, nietos, cuñados, adoptantes o adoptivos) con una persona que:</p></td></tr>

            <tr><td><p style="font-size:9px;">-Esté expuesta políticamente según la legislación nacional.</p></td></tr>

            <tr><td><p style="font-size:9px;">-Tenga la representación legal de un organismo internacional.</p></td></tr>

            <tr><td><p style="font-size:9px;">-Goce de reconocimiento público generalizado.</p></td></tr>    

            <tr><td><p style="font-size:9px;">-En afirmativo indique que le aplica y diligencie la siguiente información.</p></td></tr>';

            if ($result['declaraciones_autorizaciones'] == 1) {
                $html .= '<tr><td><label style="font-size:9px;"  for="rqb2"><span style="font-size:9px;">X </span>Si acepto</label><br/><label style="font-size:9px;"><span style="font-size:9px;">   </span>No acepto</label><br /></td></tr>';
            } else {
                $html .= '<tr><td><label  for="rqb2"><span style="font-size:20px;">°</span>Si acepto</label><br />  <label  for="rqb3"><span style="font-size:20px;">*</span> No acepto</label><br /></td></tr>';
            }
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Datos de personas expuestas politicamente:</h4></td>
            </tr></table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';

            if (count($result['personas_expuestas']) > 0) {
                foreach ($result['personas_expuestas'] as $index => $persona) {
                    $html .= '<tr><td colspan="2"><h4>Referencia ' . $index + 1 . ' </h4></td></tr> ';
                    $html .= '<tr style="font-size:9px;"><th><b>Tipo de identificación:</b> ' . $persona['des_tip'] . '</th><td><b>Identificación:</b> ' . $persona['identificacion'] . '</td></tr>';

                    $html .= '<tr style="font-size:9px;"><th><b>Nombre:</b> ' . $persona['nombre'] . '</th><td><b>Parentesco:</b>' . $persona['parentesco'] . '</td></tr>';
                }
            }
            $html .= '</table>';

            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Declaración de origen de fondos:</h4></td>
            </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:2px; border-collapse: collapse;">';
            $html .= '<tr><td colspan="2"><p style="text-align:left; font-size:9px;">Quien suscribe la presente solicitud obrando en nombre propio y/o en representación legal de la persona 
            jurídica que represento, de manera voluntaria y dando certeza de que todo lo aquí consignado es cierto, veraz y verificable, 
            realizo la siguiente declaración de fuente de bienes y/o fondos, con el propósito de dar cumplimiento a lo señalado
            al respecto a las normas legales vigentes y concordantes.</p></td></tr>

            <tr><td colspan="2"><p style="text-align:left; font-size:9px;">A. Declaro que yo y/o la persona jurídica que represento es beneficiaria efectiva de los recursos
            y son compatibles con mis actividades y situación patrimonial.</p></td></tr>

            <tr><td colspan="2"><p style="text-align:left; font-size:9px;">B. Que los recursos que se entreguen de mi parte en desarrollo de cualquiera de las relaciones contractuales que tenga
             con los destinatarios de la presente declaración, provienen de mi patrimonio y/o de la sociedad que represento y no de 
            terceros, y se derivan de las siguientes fuentes: (detalle de la actividad o negocio del que provienen los recursos)</p></td></tr>';

            $html .= '<tr style="font-size:9px;"><th><b>- ' . $result['origen_fondos']->origen_fondos . '</b></th><td>Otra¿Cuál?<b> ' . $result['origen_fondos']->otro_origen . '</b></td></tr>';
            $html .= '<tr style="font-size:9px;"><th></th><td></td></tr>';
            $html .= '<tr><td colspan="2"><p style="text-align:left; font-size:9px;">C. Declaro que los recursos no provienen de ninguna actividad ilícita de las contempladas en el Código Penal Colombiano o 
            en cualquier norma que lo modifique o adicione.</p></td></tr>

            <tr><td colspan="2"><p style="text-align:left; font-size:9px;">D. No se admitirá que terceros efectúen depósitos a mis cuentas y/o de la Entidad que represento con fondos provenientes 
            de las actividades ilícitas contempladas en el Código penal Colombiano o en cualquier norma que lo modifique, sustituya o adicione,
            ni se efectuarán transacciones destinadas a tales actividades o a favor de personas relacionadas con las mismas.</p></td></tr>

            <tr><td colspan="2"><p  style="font-size:9px;">E. Los recursos que recibo de mis contrapartes principalmente los capto por: </p></td></tr>';

            $html .= '<tr style="font-size:9px;"><th><b>- ' . $result['origen_fondos']->origen_medios . '</b></th><td><b>- ' . $result['origen_fondos']->origen_medios2 . '</b></td></tr>';
            $html .= '         
             <tr><td colspan="2"><p  style="font-size:9px;">F. Las operaciones que realizo por mi actividad implican un alto manejo de efectivo:</p></td></tr>';

            if ($result['origen_fondos']->alto_manejo_efectivo == 1) {
                $html .= '<tr><td><p style="font-size:9px;"><b>Si</b></p></td></tr>';
            } else {
                $html .= '<tr><td><p style="font-size:9px;"><b>No</b></p></td></tr>';
            }

            $html .= '<tr><td colspan="2"><p style="text-align:left; font-size:9px;">En nombre propio y/o de mi representado, declaro que no estoy impedido para realizar cualquier tipo de operación y 
            que conozco y acepto las normas que regulan el comercio colombiano y me obligo a cumplirlas. Conozco y acepto los riesgos que puedan 
            presentarse frente a las instrucciones y órdenes que imparta, derivados de la utilización de los medios y canales de distribución de 
            productos y servicios, tales como Internet, correos electrónicos u otros mecanismos similares, mensajería instantánea, teléfono, fax, 
            medios digitales entre otros. Autorizo a realizar los traslados de recursos y/o valores, previo cumplimiento de los procedimientos 
            establecidos por la entidad; así mismo, autorizo la realización de transferencias bancarias y conozco los riesgos de su utilización.
             Conozco y acepto las políticas establecidas para todos los productos ofrecidos, incluyendo los servicios de Internet. Bajo la gravedad 
            de juramento manifiesto que todos los datos acá consignados, incluidos los números de identificación tributaria, son ciertos, que la 
            información que adjunto es veraz, fidedigna, completa y verificable y autorizo su verificación ante cualquier persona natural o jurídica,
             pública o privada, sin limitación alguna, desde ahora y mientras subsista alguna relación comercial y que toda declaración falsa o 
             inexacta podrá ser sancionada, por las autoridades de conformidad con la legislación aplicable. Me comprometo a actualizar la información
              y documentación de acuerdo con la solicitud que se me haga, a proporcionar toda la información adicional y de apoyo que sea necesaria y 
              requerida, por lo menos cada año y cada vez que se presenten modificaciones respecto de cualquiera de mis datos, esto con el fin de dar 
              cumplimiento a la normatividad vigente para el efecto, y por tanto, autorizo, entre otras, a reportar la información fiscal, a verificar
               la autenticidad de mis firmas y de mis ordenantes y/o a validar los poderes y facultades de mis representantes. A su vez declaro que
                asumiré la responsabilidad civil, administrativa y/o penal derivada de cualquier información errónea, falsa o inexacta que llegaré
                 a suministrar o que dejare de suministrar oportunamente. De igual forma, declaro que resarciré a La empresa por cualquier multa,
                  perdida o daño que pudiera llegar a sufrir como consecuencia de la inexactitud o falsedad de dicha información Autorizo a La empresa
                   a suministrar la información contenida ente documento, al igual que sus anexos, a las autoridades administrativas y gubernamentales
                    correspondientes, incluidas las autoridades de mi país de residencia o de nacionalidad, de conformidad con la regulación vigente,
                     entre ellos, los Convenios Internacionales firmados por Colombia. Manifiesto que yo y/o la empresa que represento y sus empleados
                      conocen bien las normas referentes a la prevención del Lavado de Activos y Financiación del Terrorismo, todos aportamos con el
                       fin de no ser cómplices de la violación de las normas de esta ley. Igualmente, que no he pertenecido ni pertenezco a ningún
                        tipo de grupos ilegales al margen de la Ley, no les he auxiliado o colaborado en el desarrollo de sus actividades ilícitas,
                         como tampoco he realizado actividades de lavados de activos en Colombia o fuera de ella y que los bienes que conforman mi 
                         patrimonio han sido adquiridos por vías legales en desarrollo de mi profesión o actividad. De la misma manera, declaro que 
                         no tengo vínculos de parentesco con personas que estén o hayan estado incluidas en listas públicas como sospechosos de Lavado de 
                         Activos/Financiación de terrorismo o las empresas de las cuales sean accionistas, o que desarrollen o hayan desarrollado, apoyado 
                         o financiado cualquiera de las actividades descritas en el párrafo precedente. Todos los datos aquí consignados y los documentos 
                         anexos a él, son ciertos, la información que adjunto es veraz y verificable, y autorizo su verificación ante cualquier persona natural 
                         o jurídica, privada o pública, sin limitación alguna, desde ahora y mientras subsista alguna relación comercial con cualquiera de las 
                         entidades que pertenezcan a SAITEMP S.A. o con quien represente sus derechos, y me comprometo a actualizar la información y/o 
                         documentación al menos una vez cada 2 años o cada vez que se me indique. Así mismo, autorizo a SAITEMP S.A., o a quien represente 
                         sus derechos, en forma permanente e irrevocable, para que con fines estadísticos y de información financiera o comercial, consulte, 
                         informe, reporte, procese o divulgue, a las entidades de consulta de bases de datos o Centrales de Información y Riesgo, todo 
                         lo referente a mi comportamiento como cliente en general.</p></td></tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>Autorización de Tratamiento de Datos Personales:</h4></td>
        </tr></table>';

            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';

            $html .= '<tr><td><p style="text-align:left; font-size:9px; ">La Sociedad SAITEMP S.A., en cumplimiento de lo definido por la Ley 1581 de 2012, el decreto reglamentario 1377 de 2013 y 
            nuestra política de protección de datos personales, le informan que los datos personales que usted suministre en cualquiera de nuestros 
            establecimientos en desarrollo de cualquier operación comercial, serán tratados mediante el uso y mantenimiento de medidas de seguridad 
            técnicas, físicas y administrativas a fin de impedir que terceros no autorizados accedan a los mismos, lo anterior de conformidad con 
            lo establecido en la ley. El responsable del tratamiento de sus datos personales es SAITEMP S.A. sociedad legalmente existente de 
            acuerdo con la leyes Colombianas, domiciliado en la ciudad de MEDELLÍN, en la CALLE 51 # 49-11 PISO 10, quien recogerá dichos datos a 
            través de sus diferentes canales y serán usados para a) Ofrecer o informarle productos b) Para hacerle llegar información publicitaria 
            sobre promociones c) Atender o formalizar cualquier solicitud relacionada con nuestro objeto social e) Controles estadísticos sobre 
            proveedores, clientes f) Establecer rotación de los empleados. Usted podrá ejercer los derechos que la ley prevé, siguiendo los 
            procedimientos establecidos en nuestras políticas y procedimientos de Protección de datos Personales publicados en la página web de la 
            empresa, http://www.saitempsa.com, o solicitando la información que requiera a través de nuestro correo misdatos@saitempsa.com o llamando 
            al teléfono: (4) 4485744 Tenga en cuenta que el ejercicio de sus derechos no es requisito previo ni impide el ejercicio de otros 
            derechos y que cualquier modificación al presente aviso le será avisado a través de nuestra página Web. Leído lo anterior, autorizo 
            de manera previa, explicita e inequívoca a la sociedad SAITEMP S.A., para el tratamiento de los datos personales suministrados por mi 
            persona para las finalidades aquí establecidas. Declaro que soy el titular de la información reportada en este formato para autorizar 
            el tratamiento de datos personales y que la he suministrado de forma voluntaria y es completa, veraz, exacta y verídica.</p></td></tr> ';

            if ($result['tratamiento_datos_personales'] == 1) {
                $html .= '<tr><td><label style="font-size:9px;"  for="rqb2"><span style="font-size:9px;">X </span>Si acepto</label><br/><label style="font-size:9px;"><span style="font-size:9px;">   </span>No acepto</label><br /></td></tr>';
            } else {
                $html .= '<tr><td><label  for="rqb2"><span style="font-size:9px;">   </span>Si acepto</label><br />  <label  for="rqb3"><span style="font-size:9px;">X</span> No acepto</label><br /></td></tr>';
            }

            $html .= '</table>';
            $margen_izquierdo = 15;
            $margen_derecho = 15;
            $pdf->SetMargins($margen_izquierdo, 40, $margen_derecho);
            $pdf->SetAutoPageBreak(true, 40);
            // Generar PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            $totalPages = 0;
            if ($pdf->getNumPages() == 1) {
                $totalPages = 1;
            } else {
                $totalPages = $pdf->getNumPages();
            }

            // Agregar membrete en cada página después de la primera
            for ($i = 1; $i <= $totalPages; $i++) {
                // Cambiar a la página correspondiente
                $pdf->setPage($i);

                // Ajustar los márgenes para que el membrete no interfiera con el contenido
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false, 40);

                // Agregar la imagen del membrete
                $url = public_path('/upload/MEMBRETE.png');
                $pdf->Image($url, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);

                // Restaurar los márgenes para el contenido
                $pdf->SetMargins(15, 40, 15);
                $pdf->SetAutoPageBreak(true, 40);
            }
            if ($outputToBrowser) {
                $pdf->Output('Reporte_Cliente_' . $id . '.pdf', 'I');
            } else {
                $tempFilePath = tempnam(sys_get_temp_dir(), 'tcpdf_') . '.pdf';
                $pdf->Output($tempFilePath, 'F');
                return $tempFilePath;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el PDF: ' . $e->getMessage()], 500);
        }
    }

    public function generarContrato2($id, $outputToBrowser = true)
    {
        try {
            // Obtener los datos

            $contratoController = new ContratoController;
            $contrato_data = $contratoController->index($id, false);
            if (!$contrato_data) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }
            $url = public_path('public/upload/logo1.png');
            // Iniciar TCPDF
            $pdf = new \TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Saitemp SAS');
            $pdf->SetTitle('Sagrilaft de Cliente');
            $pdf->SetSubject('Detalle del Cliente');
            /*  $pdf->SetMargins(10, 10, 10); */


            $url = public_path('upload/logo1.png');
            $pdf->setPrintHeader(false);
            $pdf->SetMargins(15, 40, 15);
            $pdf->SetAutoPageBreak(true, 40);
            $pdf->AddPage();

            // Agregar imagen de fondo
            $url2 = public_path('/upload/MEMBRETE.png');
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->Image($url2, 0, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
            $pdf->SetMargins(15, 40, 15);
            $pdf->SetAutoPageBreak(true, 40);
            /* $pdf->SetMargins(15, 40, 15);
            $pdf->SetAutoPageBreak(true, 40); */

            // Verificar que el archivo existe
            if (!file_exists($url)) {
                throw new Exception("La imagen no existe en la ruta especificada.");
            }

            // Convertir la imagen en base64 si el método estándar no funciona
            $imageData = base64_encode(file_get_contents($url));
            $imageSrc = 'data:image/png;base64,' . $imageData;
            // Iniciar HTML
            $html = '<table cellpadding="4" style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">
            <tr>
           
                <th colspan="2" style="text-align: center;border: 1px #000000 solid;">
                    <h5 >CONTRATO DE PRESTACIÓN DE SERVICIOS TEMPORALES</h4>
                </th>
            ';

            $html .= '
            <td style="text-align: left;border: 1px #000000 solid;">';


            $html .= '<p style="font-size: 8px;">' . htmlspecialchars($contrato_data['codigo_documento']) . '</p>';
            $html .= '<p style="font-size: 8px;">' . htmlspecialchars($contrato_data['fecha_documento']) . '</p>';
            $html .= '<p style="font-size: 8px;">' . htmlspecialchars($contrato_data['version_documento']) . '</p>';

            $html .= '</td></tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
            <td colspan="2" style="text-align: center;"><h4>EMPRESA USUARIA (EU) Radicado: ' . $contrato_data['radicado'] . '</h4></td>
            </tr></table>';
            $resultGenerales = [
                'Razon social:' => $contrato_data['razon_social'],
                'NIT:' => $contrato_data['nit'],
                'Respresentante legal:' => $contrato_data['representante_legal'],
                'NN:' => $contrato_data['identificacion'],
                'Departamento:' => $contrato_data['departamento'],
                'Ciudad:' => $contrato_data['ciudad'],
                'Direccion:' => $contrato_data['direccion'],
                'Actividad económica:' => $contrato_data['actividad_ciu'],
                'Nombre de contacto:' => $contrato_data['contacto'],
                'Celular:' => $contrato_data['celular'],
                'E-mail:' => $contrato_data[''],
            ];
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $indiceGenerales = 0;
            $clavePrev = "";
            $valorPrev = "";
            foreach ($resultGenerales as  $clave => $valor) {

                if ($indiceGenerales % 2 == 0) {
                    $clavePrev = $clave;
                    $valorPrev = $valor;
                } else {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $clavePrev . '</b> ' . $valorPrev . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                }


                $indiceGenerales++;
            }

            if ($indiceGenerales % 2 == 1) {
                $html .= '<tr><td style="font-size:9px;"><b>' . $clavePrev . '</b> ' . $valorPrev . '</td></tr>';
            }
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                <td colspan="2" style="text-align: center;"><h4>EMPRESA DE SERVICIOS TEMPORALES (EST):</h4></td>
                </tr></table>';
            $resultEmpresasTemporales = [
                'Razón social:' => 'SAITEMP S.A',
                'NIT:' => '811.025.401-0',
                'Representante legal:' => 'HUBER ANTONIO BAENA MEJÍA',
                'C.C:' => '71.703.511 | Medellín-Antioquia',
                'Departamento:' => 'Antioquia',
                'Ciudad:' => 'Medellín',
                'Dirección:' => 'Calle 51 N° 49 – 11 Centro ',
                'Oficina:' => 'Edifcio Fabricato Ofcina 1005',
                'Nombre de contacto:' => 'ANDRES CORREA PINEDA',
                'Celular:' => '313 302 49 26',
                'E-mail:' => 'procesoscomerciales@saitempsa.com',
            ];
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $indiceTemporales = 0;
            $clavePrevTemp = "";
            $valorPrevTemp = "";
            foreach ($resultEmpresasTemporales as  $clave => $valor) {

                if ($indiceTemporales % 2 == 0) {
                    $clavePrevTemp = $clave;
                    $valorPrevTemp = $valor;
                } else {
                    $html .= '<tr><td style="font-size:9px;"><b>' . $clavePrevTemp . '</b> ' . $valorPrevTemp . '</td><td style="font-size:9px;"><b>' . $clave . '</b> ' . $valor . '</td></tr>';
                }

                $indiceTemporales++;
            }

            if ($indiceTemporales % 2 == 1) {
                $html .= '<tr><td style="font-size:9px;"><b>' . $clavePrevTemp . '</b> ' . $valorPrevTemp . '</td></tr>';
            }
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                <td colspan="2" style="text-left: center;"><h4>Consideraciones:</h4></td>
                </tr></table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;">1. La Empresa de servicios temporales (EST) SAITEMP S.A es una empresa constituida como persona jurídica, cuyo
                objeto social exclusivo es el establecido por los Artículos 71 y 72 de la Ley 50 de 1990 y el Artículo 2.2.6.5.2 del
                Decreto 1072 de 2015.</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;">2. Que el funcionamiento de SAITEMP S.A. fue autorizado por el Ministerio del Trabajo, por autorización que se
                encuentra vigente a la fecha y que hace parte integral del presente contrato</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;">3. Que la prestación a cargo de SAITEMP S.A. consiste en colaborar temporalmente en la actividad del contratante,
                mediante el envío de trabajadores en misión, en los términos establecidos en los Artículos 71 y 77 de la Ley 50 de
                1990, en concordancia con el Parágrafo del Artículo 2.2.6.5.6 del Decreto 1072 de 2015, prestando todos aquellos
                servicios temporales de colaboración reseñados allí, cuando hubiere lugar a ello, en forma efciente y oportuna y en
                cumplir respecto de aquél, con todas las obligaciones legales, contractuales y/o convencionales que le correspondan
                o pudieren corresponder en el futuro.</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;">4. Que SAITEMP S.A. cumple con todas y cada una de las obligaciones impuestas por la Ley</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;">5. Que entre SAITEMP S.A. sociedad comercial identifcada con NIT. 811.025.401-0, con matrícula mercantil
                No.21-274659-04, legalmente constituida por Escritura Pública No.3550 del 29 de diciembre 2006, otorgada en la
                Notaría 18ª de Medellín, registrada en la Cámara de Comercio de Medellín el 22 de febrero 2007, en el libro IX, bajo el
                No. 2060, con sede principal en la ciudad de Medellín y sedes alternas en Bogotá, Cartagena y Barranquilla, con
                autorización de funcionamiento como Empresa de Servicios Temporales por el Ministerio de Trabajo, Resolución
                N°000385 de 8 marzo 2001 : en adelante la “EST” y la empresa usuaria en adelante “EU”, suscriben el presente
                CONTRATO DE PRESTACIÓN DE SERVICIOS TEMPORALES DE COLABORACIÓN, que se regirá por las cláusulas que a
                continuación se expresan y en general por las disposiciones legales del Código de comercio, del Código Civil
                colombiano y demás normas aplicables a la materia de qué trata este contrato:</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">  <tr>
                <td colspan="2" style="text-left: center;"><h4>Cláusulas:</h4></td>
                </tr></table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>1. PRIMERA. OBJETO.</b> El servicio que desarrolla “EST” como empresa de servicio temporal consiste en colaborar temporalmente en
                la actividad de “EU” mediante el envío de empleados en misión a los sitios de trabajo que indique “EU” para que ejecuten las
                actividades de colaboración temporal bajo la subordinación delegada de “EU”, de conformidad con el numeral 3º del Artículo 77 de
                la Ley 50 de 1990, en concordancia con el parágrafo del Artículo 2.2.6.5.6 del Decreto 1072 de 2015. en las condiciones, requisitos
                y asignaciones salariales de conformidad con lo solicitado por la misma “EU”.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>NOTA ACLARATORIA N° 1.</b> El número de empleados en misión que deba suministrar a la “EST” al igual que las competencias y
                características que éste deba reunir serán determinadas en cada requerimiento de personal que “EU” haga a la “EST” en todas y
                cada una de las oportunidades que le solicite sus servicios durante el desarrollo del presente contrato.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>NOTA ACLARATORIA N° 2.</b> Expresamente manifesta la “EU” que se abstendrá de solicitar a la “EST” el suministro de personal en
                misión para reemplazar trabajadores que se encuentren en huelga, en virtud de la prohibición establecida en el Artículo 89 de la Ley
                50 de 1990. </td>
    
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>2. SEGUNDA. TARIFA(S) EMPRESA DE SERVICIOS TEMPORALES.
                COBRO DE ADMINISTRACIÓN POR LA PRESTACIÓN DEL SERVICIO TEMPORAL “AIU”:</b> El porcentaje de administración o la cuota
                fja aceptada en la propuesta comercial para el pago de la factura electrónica es de 7,5% </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>PLAZO DE PAGO DE LA FACTURA ELECTRONICA:</b> Los días negociados en la propuesta comercial para el pago de la factura
                electrónica es de 8 días.
                <p>CONDICIONES ESPECIALES:</p>
                <p>VER ANEXO N° 3: PROPUESTA ECONÓMICA VIGENTE.</p></td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;">PARÁGRAFO PRIMERO. Una vez fnalicen las pruebas técnicas de conocimiento y la “EU” decide seleccionar alguno de los
                aspirantes o candidatos remitidos, previa iniciación de las actividades ya sean de inducción, entrenamiento o etapa de evaluación
                de aptitudes, se deberá informar a la “EST” para suscribir contrato de trabajo, efectuar afliaciones a la Seguridad Social, y además
                se deben garantizar todas las prestaciones al trabajador. (Art. 80 C.S.T.). La “EU” deberá abstenerse de ensayar a cualquier
                candidato remitido por la “EST” en la ejecución real de la labor misional sin que este cuente con las respectivas afliaciones a la
                seguridad social, en caso de accidente o reclamación como consecuencia de una prueba técnica de conocimiento que implique
                ejecución de la labor misional dentro o fuera de las instalaciones de la “EU” será exclusiva responsabilidad la “EU” las
                reclamaciones que por ello llegaran a presentar los candidatos en su momento.
                PARÁGRAFO SEGUNDO. Si del grupo de candidatos fltrados y reclutados por el equipo de Selección de la “EST”, la “EU” desea
                vincular directamente alguno para el cargo solicitado, La “EU” deberá pagar a la “EST”, la suma equivalente al cien por ciento (100%)
                del salario que el candidato devengará mensualmente una única vez, suma que será facturada por la empresa aliada en servicios
                de outsourcing de selección SERVICIOS ADMINISTRATIVOS AL INSTANTE S.A.S.
                PARÁGRAFO TERCERO. La “EU” se obliga reembolsar a la “EST” los costos de las evaluaciones médicas ocupacionales de ingreso,
                periódicas, de retiro, certifcados de alturas, pruebas de idoneidad de conductores, de manipulación de alimentos y demás pruebas
                o valoraciones médicas complementarias que se requieran para el ejercicio de la labor misional, en función de las condiciones de
                trabajo y factores de riesgo a las que estaría expuesto el trabajador en misión, acorde con los requerimientos de la tarea y el perfl
                del cargo, además la “EU” asume el costo de dichas evaluaciones y en ningún caso, podrán ser cobrados ni solicitados al trabajador
                en misión, dando cumplimiento a lo estipulado en la Resolución 2346 de 2007. De igual manera, asume el costo de las
                evaluaciones médicas en los casos en que no sean efectivos los ingresos.</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>3.TERCERA. COBROS, PAGOS, FORMA DE PAGO.</b> Cada vez que la “EST” pague salarios a los empleados en misión, lo hará de
                acuerdo con las novedades efectivamente reportadas por la “EU”, las cuales en virtud de la transmisión de nómina electrónica
                deberán ser reportadas dentro del mismo periodo que se causaron por parte de la “EU” y al momento de emitir paz y salvo sin dejar
                novedades pendiente, suma que serán facturadas por “EST” y pagadas por la “EU” en un término no superior a los días indicados en la propuesta comercial 8 días y en la cláusula segunda de este contrato, pago que podrá llevarse a cabo a través de consignación/
                transferencia en una de las cuentas bancarias de la “EST” Las partes aceptan que la factura presta mérito ejecutivo y que en caso
                de incumplimiento generará de manera inmediata el cobro de interés moratorios a la máxima tasa legal defnida por la Super
                Intendencia Financiera de Colombia. La factura aquí indicada Incluye los costos inherentes a los contratos de trabajo, como son:</p>
                <p>A) Salario básico y todo tipo de pago constitutivo de salario efectivamente causados y pagados.</p>
                <p>B) El porcentaje correspondiente a los aportes al sistema de Seguridad Social que se viere obligado a pagar la “EST” a las entidades
                de la Seguridad Social según lo que establezca la Ley Colombiana y el porcentaje correspondiente a la totalidad de las prestaciones
                sociales que se discriminan en la aceptación de la propuesta comercial.</p>
                <p>C) Sobre los valores indicados en los literales anteriores la “EST” facturará a la “EU” un porcentaje de administración según lo
                indicado en la propuesta comercial y en la cláusula segunda de este contrato (Ver Anexo N° 3. Propuesta Económica vigente) por
                concepto de administración sobre los costos totales por cada trabajador, como pago correspondiente al servicio objeto del
                presente contrato.</p>
                <p>D) Para las cuotas fjas “EST” efectuará los reajustes de dicha cuota, al inicio de cada año de acuerdo incremento del Salario
                Mínimo Legal Mensual Vigente defnido por el gobierno nacional.</p>
                <p>PARÁGRAFO PRIMERO. En caso de presentarse ausentismo o licencias no remuneradas por parte del empleado en misión, se
                facturará igualmente el valor de la administración. Es decir, le suma al subtotal el salario que deja de devengar el trabajador como
                base para el cálculo de la administración.</p>
                <p>PARÁGRAFO SEGUNDO. De conformidad con lo establecido en los Artículos 78 y 81, numeral 4, de la Ley 50 de 1990 y 13 y el
                decreto reglamentario 1530 de 1996, la “EU” reconocerá a la “EST” la misma tarifa por riesgos profesionales que debe aportar a la
                ARL por sus trabajadores de planta y de conformidad a lo establecido en el Decreto 768 de 2022.</p>
                <p>PARÁGRAFO TERCERO. En caso de cambio en la legislación o jurisprudencia colombiana, que genere incremento en las
                obligaciones para los empleadores, en materia tributaria, de seguridad social, porcentajes de riesgos de ARL, aportes parafscales,
                salario mínimo, auxilio de transporte, horas extras, dominicales, festivos u otros pagos de carácter obligatorio, la “EST” trasladará
                inmediatamente a la “EU” el mayor costo debidamente soportado y éste deberá asumirlo dentro de los plazos convenidos en el
                presente contrato.</p>
                <p>PARÁGRAFO CUARTO. las vacaciones del trabajador en misión se facturarán incluyendo el concepto de auxilio de transporte, esto
                con el ánimo de provisionar los cambios de salario que pueda llegar a tener por los incrementos obligatorios de Ley. Las
                vacaciones del trabajador siempre se liquidarán con el último salario devengado.</p></td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>4. CUARTA. RECEPCIÓN DE FACTURAS ELECTRÓNICAS.</b> Sin perjuicio de la radicación en físico, “EU” manifesta que recibirá las
                facturas electrónicas que se generen por la prestación de los servicios objetos del presente contrato al correo electrónico:</p>
                <p>' . $contrato_data['correo_facturacion_electronica'] . '</p> </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>5. QUINTA. ACEPTACIÓN DE LA FACTURA ELECTRÓNICA DE VENTA COMO TÍTULO VALOR.</b> Atendiendo a lo indicado en los
                Artículos 772, 773 y 774 del Código de Comercio, la factura electrónica de venta como título valor, una vez recibida, se entiende
                irrevocablemente aceptada por LA EMPRESA USUARIA en los siguientes eventos: a) Aceptación expresa: Cuando, por medios
                electrónicos, acepte de manera expresa el contenido de ésta, dentro de los tres (3) días hábiles siguientes al recibo de la mercancía
                o del servicio. b) Aceptación tácita: Cuando no reclamare al emisor en contra de su contenido, dentro de los tres (3) días hábiles
                siguientes a la recepción de la documentación o del servicio. El reclamo se hará por escrito en documento electrónico.</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>6. SEXTA. REAJUSTES.</b> En aras del equilibrio económico, si durante la prestación del servicio se generara un cambio en la
                propuesta económica que incida en el presente contrato, las partes procederán de manera conjunta a reajustar los valores de la
                cotización inicial en una suma igual a la propuesta económica.</td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>7. SÉPTIMA. DURACIÓN.</b> El presente contrato tendrá la duración prevista de doce (12) meses. En caso de no comunicar la intención
                de terminación del contrato en los términos indicados en la Cláusula Octava y Cláusula Novena, una vez llegado el plazo de
                vencimiento, el mismo podrá prorrogarse en los mismos términos de la vigencia inicial y se mantendrá vigente mientras se den
                ordenes/solicitudes de servicios o haya requerimientos de colaboración en ejecución, y en general, mientras haya personal en
                misión prestando sus servicios en la “EU”.</p>
                <p>PARÁGRAFO PRIMERO. Si pasados dos meses calendario después de la celebración del contrato, no se ha emitido facturación por
                la prestación del servicio temporal, se terminará el presente contrato de forma automática por inejecución del objeto contractual. Si
                posteriormente la “EU” requiere de los servicios de la “EST” tendrá que celebrar un nuevo contrato surtiendo nuevamente la etapa
                precontractual correspondiente, según el proceso de contratación establecido por la “EST” para la prestación del servicio.</p>
                <p>PARÁGRAFO SEGUNDO. A menos que por disposición expresa o tácita ambas partes decidan de manera mancomunada ejecutar el
                contrato en los términos convenidos desde la suscripción y celebración del contrato. En estos casos tendrá prevalencia los actos
                positivos directamente encaminados a la ejecución del contrato que la terminación del mismo por las razones y los términos
                indicados en el parágrafo primero de la presente clausula.</p>
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>8. OCTAVA. TERMINACION CON JUSTA CAUSA.</b> Así mismo cualquiera de las partes se reserva el derecho de darlo por terminado
                por el incumplimiento de las obligaciones contractuales por cualquiera de las partes, para este caso la notifcación de la
                terminación se podrá hacer de manera inmediata sin que se requiera preaviso alguno, sin que la terminación genere ningún tipo de
                indemnización para quien la invoca. La inactivación de los servicios de colaboración por más de treinta (30) días se tendrá como justa causal para dar por terminado el contrato misional y en consecuencia en caso de requerir con posterioridad el envío de
                trabajadores misionales, se deberá suscribir un nuevo contrato de prestación de servicios.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                            <td style="font-size:9px;"><b>9. NOVENA. TERMINACION SIN JUSTA CAUSA.</b> En los casos de terminación unilateral sin la invocación de una justa causal, por
                            alguna de las partes, bastará para ello el aviso previo y escrito dado a la otra parte con treinta (30) días calendario de anticipación a
                            la fecha en la cual se dará por terminado el servicio, sin que la terminación genere ningún tipo de indemnización para quien la
                            invoca. NOTA ACLARATORIA N° 3: Toda terminación con o sin justa causa la “EU” deberá colocarse al día con el pago de nóminas
                            realizados hasta la fecha.
                            </td>
                        </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                            <td style="font-size:9px;"><p><b>10.DÉCIMA. DESTINACIÓN | SUBORDINACIÓN ADMINISTRATIVA DELEGADA.</b> Previo requerimiento de la “EU” a la “EST” contratará
                                los empleados en misión de conformidad con lo establecido en la Ley 50 de 1990 y las demás normas que modifquen, aclaren,
                                adicionen, complementen o subroguen estas disposiciones. Por consiguiente, dicho personal deberá ejecutar directa y
                                personalmente la labor que la “EST” le señale en su contrato y la “EU” debe destinar al empleado en misión única y exclusivamente
                                para el desempeño de las labores para las que fue enviado. Sin embargo, la “EST” delega en la “EU” la facultad de controlar
                                directamente el trabajo de dicho personal y la de impartir ordenes, instrucciones en cuanto tiempo modo y lugar, así como la de
                                cualquier otra indicación que facilite ejercer en nombre de la “EST” la supervisión de dicho personal debido al tipo de servicio que
                                prestará el empleado en misión, como también por su localización. No obstante, la “EU” no podrá destinar en ninguna circunstancia
                                al empleado en misión para el desempeño de labores distintas de aquellas para la que fue requerido y contratado.</p>
                                <p>PARÁGRAFO PRIMERO. En relación con los empleados en misión, éstos estarán sujetos a las obligaciones, prohibiciones, faltas y
                                sanciones que se establezcan en el Reglamento Interno de Trabajo tanto de la “EU” como de la “EST”, pero la acción disciplinaria
                                corresponde exclusivamente al EMPLEADOR, esto es a la “EST” a través del proceso disciplinario establecido para tal fn. Adicional,
                                la “EU” debe instruir a los empleados en misión, en cuanto a los procedimientos, guías, protocolos, normas y demás documentos
                                establecidos en la “EU” y de igual forma, no podrá ordenar ni permitir que el empleado en misión exceda los lineamientos señalados
                                en la Ley y la Jurisprudencia vigente que en materia laboral lo protege.</p>
                                <p>PARÁGRAFO SEGUNDO. De conformidad con lo establecido de manera exclusiva para los usuarios de las “EST”, en la Sentencia de
                                abril 24 de 1997, expediente 9435, de la Corte Suprema de Justicia, Sala Laboral, la “EU” podrá dar órdenes, impartir instrucciones a
                                los trabajadores en misión y exigir el cumplimiento de estas, en desarrollo del servicio específco de colaboración contratado, sin
                                que por esto adquieran el carácter de empleador.</p>
                            </td>
                        </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                            <td style="font-size:9px;"><b>11.DÉCIMA PRIMERA. RESPONSABILIDAD LABORAL.</b>  El vínculo entre la “EST” y los empleados que ejecutan labores misionales en
                            la “EU” será de carácter laboral, asumiendo las responsabilidades inherentes del empleador. En consecuencia, la “EST” manifesta
                            expresamente que cumple con todas y cada una de las obligaciones impuestas por la Ley, para efectos del pago de las acreencias
                            que se causen con ocasión del Contrato de Trabajo, como: afliaciones, pago de salarios, prestaciones sociales, horas extras,
                            recargos diurnos, nocturnos, dominicales, festivos y pago a las entidades de seguridad social. La “EST” informará a la “EU”
                            periódicamente los aportes a las entidades de seguridad social de todos los empleados en misión de la “EU”.
                            </td>
                        </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                            <td style="font-size:9px;"><b>12. DÉCIMA SEGUNDA. CONSIDERACIONES ESPECIALES CONTRATACIÓN Y/O VINCULACIÓN DIRECTA DE EMPLEADOS EN
                            MISIÓN.</b>  Si la “EU” informa a la “EST” la culminación de la obra o labor de un empleado en misión reclutado por el equipo de
                            selección de SAITEMP S.A. para contratarlo directamente para la misma labor, en un tiempo inferior a seis (6) meses de servicio
                            temporal, la “EU” deberá igual pagar la suma equivalente al cien por ciento (100%) de un mes de salario, según lo indicado en el
                            PARÁGRAFO SEGUNDO de la CLÁUSULA SEGUNDA de este contrato.
                            </td>
                        </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>13. DÉCIMA TERCERA. CONSIDERACIONES ESPECIALES DE RESPONSABILIDAD.</b> La “EST” no asume responsabilidad alguna por
                            daños, robos, sustracciones, hurtos y demás eventos delictivos a equipos, maquinarias, mercancías, vehículos o dinero y en general
                            a bienes o valores, incluso la información confdencial de la “EU” que se haya entregado o esté bajo custodia de los empleados en
                            misión, ni tampoco por abusos de confanza, falsifcación de documentos, falsedad y otros hechos similares; por lo cual la “EU”
                            será la única responsable de la protección de sus bienes y será ésta a quien le corresponde las acciones legales pertinentes en
                            contra de los empleados infractores tendientes al resarcimiento de perjuicios que se haya causado, sin que en ningún momento la
                            “EST” asuma responsabilidad alguna sobre estos bienes y valores. En caso de que la “EU” lo decida, podrá contratar, a su costo,
                            seguros que amparen esos valores y la responsabilidad contractual o extracontractual, frente a terceros, que se derive de las
                            labores de los empleados en misión será asumida directamente por la “EU”. Lo anterior en virtud que la “EST” está imposibilitado
                            técnica y administrativamente para supervisar, vigilar y custodiar los bienes, maquinarias, mercancías, vehículos, dineros y en
                            general cualquier bien susceptible de valoración propio de la actividad económica de la “EU”, porque es esta la que puede ejercer
                            directamente el control y supervisión sobre estos trabajadores en ejercicio de la subordinación delegada, y es la directamente
                            responsable del manejo de los bienes e instrumentos de labor, de los cuales es su propietaria o tenedora.</p>
                            <p>PARÁGRAFO PRIMERO. La “EST” acompañará las acciones disciplinarias en contra del trabajador misional que ejecuté alguna de
                            las conductas indicadas en este numeral, adelantará gestiones para conciliación o autorizaciones de descuento, tramite de
                            consignación de prestaciones sociales ante los jueces laborales, colaboración en la investigación, pero no asume la
                            responsabilidad extracontractual, penal por los daños, perjuicios o delitos de los trabajadores. La “EST” procurará mediante sus
                            diferentes dependencias adelantar acciones encaminadas a recuperar dineros, a ubicar el trabajador en caso de abandono, a
                            mediar entre la “EU” y el trabajador en caso divergencias, a buscar salidas negociadas tendientes a resarcir daños que se hayan
                            podido causar, entre otros. Todo dentro del marco de la legalidad y respetando los mínimos garantizados que otorga la Ley al trabajador</p>
                    </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>14. DÉCIMA CUARTA. OBLIGACIONES DE LA EMPRESA USUARIA (EU).</b> Se obliga a: (I) Informar antes de la ejecución del contrato a
                    la “EST” los exámenes médicos de ingreso, periódicos, de egreso y aquellos especiales que se requieran de acuerdo con los
                    servicios contratados por parte de la “EU”. (II) De ser necesario que los trabajadores en misión trabajen horas extras, la “EU” deberá
                    contar con la autorización expedida por el Ministerio del Trabajo, por parte de la “EST” a su vez, deberá informar, de manera veraz y
                    oportuna, y en los formatos que para el efecto señala la “EST” el tiempo realmente laborado por los trabajadores en misión y las
                    novedades que se presenten. La “EU” será responsable de la exactitud en el reporte del tiempo laborado por todos y cada uno de
                    los trabajadores en misión y deberá presentarlo a la “EST” para efectos del pago de nómina de estos. En caso de que se inicien
                    investigaciones por parte de entidades administrativas y judiciales a la “EST”, por el reporte erróneo, defectuoso o el
                    incumplimiento de esta obligación por parte de la “EU”, esta deberá responder por las sanciones económicas que se impongan en
                    virtud de las investigaciones adelantadas, así el contrato comercial haya fnalizado. (III) Pagar oportunamente la retribución que se
                    causa a favor de la “EST” de acuerdo con la cláusula vigésima- retiros a solicitud de la empresa usuaria. (IV) Incluir a los
                    trabajadores en misión en programas culturales, recreativos, deportivos que la “EU” tenga establecido para sus propios
                    trabajadores, de acuerdo con lo establecido en el Artículo 79 de la Ley 50 de 1990.(V) Entregar la dotación, los elementos de
                    protección personal y los elementos corporativos de la “EU” que se requieran para la ejecución del objeto de este contrato. (VI)
                    Asignar al trabajador en misión únicamente las funciones para las cuales fue contratado por la “EST”. (VII) Implementar el Decreto
                    1072 de 2015 y la Guía Técnica para la implementación del SG - SST frente a los trabajadores en misión de las EST y sus
                    EMPRESAS USUARIAS, expedida por la Dirección de Riesgos Laborales del Ministerio del Trabajo. (VIII) Informar inmediatamente la
                    “EST” vía telefónica y / o correo electrónico, sobre la ocurrencia de todo accidente de trabajo de los trabajadores en misión, y
                    diligenciar el reporte de accidente correspondiente.Las sanciones económicas derivadas de la demora en el Reporte del Accidente
                    de trabajo por culpa de la “EU”, serán asumidas por esta. (IX) Se compromete a dar cumplimiento a las políticas de SAGRILAFT y
                    HABEAS DATA defnidas por la “EST”. (X) La “EU” pagará todos los costos inherentes que se generen en virtud de la prestación
                    efectiva del servicio temporal de colaboración contratado, más el costo de administración que pacten las partes. (Ver Anexo N° 3.
                    Propuesta Económica vigente)(XI) Se obliga que los sitios de trabajo cumplan las exigencias de la legislación sobre Seguridad y
                    Salud en el Trabajo y la Guía Técnica para la implementación del SG - SST legal vigente frente a los trabajadores en misión de las
                    EST y sus usuarias. (XII) Suministrar periódicamente a la “EST” la documentación que acredite el cumplimiento de las normas de
                    Seguridad y Salud en el Trabajo de su empresa frente a los trabajadores en misión. (XIII) Se obliga con la ARL de la “EST” al
                    cumplimiento de las normas de Seguridad y Salud en el Trabajo y su implementación en los trabajadores en misión. (XIV) Se obliga
                    a informar a la “EST” sobre las posibles faltas disciplinarias en que incurra el trabajador en misión, para que la “EST” tome las
                    medidas pertinentes. (XV) Frente a la posible asociación de los trabajadores en misión a sindicatos de la “EU”, se obliga a: (I)
                    Informar inmediatamente a la “EST” cuando tenga conocimiento de la afliación de trabajadores en misión en sindicatos de su
                    industria o empresa. (II) No pagar ningún valor directamente por concepto de afliación de cuota sindical, negociaciones colectivas,
                    entre otros que se puedan llegar a causar, en caso de asumir estos valores sin consultar por escrito en los medios de atención a la
                    “EST”, la “EU” correrá con esos gastos, (III) Pagar la totalidad de los costos en que incurra la “EST” provenientes de descuentos de
                    cuota sindical, negociaciones colectivas, entre otros, que se puedan llegar a causar por un trabajador en misión sindicalizado en
                    sindicatos de la “EU”. (XVI) Acoger al trabajador en misión en las fechas que disponga en su empresa para el disfrute de la jornada
                    familiar a la que hace referencia la Ley 1857 de 2017. En caso de no disponer de tal fecha, la “EU” será la encargada de brindar la
                    jornada libre semestral tal como lo indica la Ley 1857 de 2017.(XVII) Informar a la “EST”, con treinta(30) días de anticipación sobre
                    la terminación defnitiva del contrato comercial de suministro de personal, para que la “EST”, pueda gestionar la terminación de los
                    contratos laborales por obra o labor de los trabajadores en misión. (XVIII) Los daños y perjuicios derivados de accidentes,
                    incidentes de trabajo ocasionados con culpa, dolo, negligencia o por falta de aplicación al SG - STT y este contrato, harán
                    responsable a la “EU” frente a los costos en los que incurra la “EST”, para reparar los daños y perjuicios al trabajador o a sus
                    familiares. (XIX).La “EU” deberá abstenerse de ejecutar actos propios del empleador tales como sancionar, fnalizar el contrato de
                    trabajo, dar directamente remuneraciones o bonifcaciones a los trabajadores en misión. (XX) OBLIGACIONES SG - SST. Sin
                    perjuicio de la responsabilidad de la “EST” como empleador de sus trabajadores en misión, la “EU” se obliga a que los sitios de
                    trabajo cumplan con las exigencias contempladas en el Decreto 1072 de 2015, en la Guía técnica para la implementación SG - SST
                    frente a los empleados en misión de EST y sus usuarias(expedida por el Ministerio de trabajo), en la resolución 1409 de 2012, y
                    demás normas que modifquen, deroguen, o actualicen este tema, por lo cual, la “EU” deben incluir los empleados en misión dentro
                    de sus Programas de Seguridad y Salud en el Trabajo, para lo cual deben suministrarles: una inducción completa e información
                    permanente para la prevención de los riesgos a que están expuestos dentro de, la “EU”, los elementos de protección personal,
                    dotación y entrenamiento que requiera el empleado en misión en el puesto de trabajo y velar por las condiciones de seguridad para
                    prevenir lesiones y enfermedades. (XXI) Dar respuesta en un plazo no mayor a tres(3) días hábiles al proceso de atracción y
                    selección, respecto a si las hojas de vida de los candidatos remitidos se ajustan al perfl de la vacante solicitada. (XXII) En caso de
                    presentarse queja formal por parte de un trabajador en misión de la “EST” denunciando presunto acoso laboral por parte de los
                    empleados directos de la “EU” o sus representantes, se compromete adelantar todos los correctivos necesarios, conducentes y
                    pertinentes para cesar los hechos generadores del supuesto acoso, del mismo modo, se compromete a gestionar las sugerencias y
                    solicitudes que realice el comité de convivencia laboral la “EST”. 
                    </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>15. DÉCIMA QUINTA. OBLIGACIONES DE LA EMPRESA DE SERVICIOS TEMPORALES SAITEMP S.A. (EST).</b> (I) Contratar
                    laboralmente y por escrito el personal en misión requerido para ejecutar el servicio temporal de colaboración contratado comercialmente por la “EST” con la “EU”, de modo que se ajuste a los requerimientos fjados por éste; para cumplir con el objeto del
                    presente Contrato. La “EST” se obliga a efectuar un proceso de selección acorde con la naturaleza de este Contrato y a poner en
                    consideración de la “EU” las hojas de vida para la selección de los trabajadores en misión. Del mismo modo se enviarán las hojas
                    de vida disponibles de los candidatos que acepten las condiciones de la oferta y se ajusten a la vacante solicitada, sin garantizar un
                    número máximo o mínimo de hojas de vida. (II) Afliar al trabajador en misión al Sistema de Seguridad Social Integral. (III) Pagar
                    oportunamente al personal en misión los salarios, prestaciones sociales, el pago de los aportes de cada uno de los trabajadores en
                    misión tanto a la Seguridad Social como a los parafscales y demás normas concordantes, así como las demás acreencias
                    laborales que por ley correspondan. (IV) Llevar una carpeta con los documentos de ingreso e historia laboral de cada trabajador en
                    misión que reposarán en las dependencias de la “EST”. (V) Retirar del servicio al trabajador o trabajadores cuya remoción sea
                    solicitada por la “EU”, dando estricto cumplimiento a las disposiciones jurisprudenciales y legales para cada caso. (VI) Facilitar en
                    caso de requerir los aportes de todos los trabajadores en misión y las cotizaciones al Sistema Integral de Seguridad Social Integral.
                    (VII) Vigilar y responder por la Seguridad y Salud en el Trabajo en los términos de la ley y la Guía Técnica para la implementación del
                    SG-SST frente a los trabajadores en misión de las EST y sus usuarias, expedida por la Dirección de Riesgos Laborales del Ministerio
                    del Trabajo. (VIII) Investigar los accidentes de trabajo y enfermedades laborales de los trabajadores en misión de acuerdo con la
                    normatividad vigente. (IX) Informar a la “EU” de cualquier novedad que se presente con los trabajadores designados para el
                    desarrollo del presente Contrato (X) En general, a cumplir estrictamente las disposiciones laborales vigentes. (XI) Cumplir los
                    acuerdos pactados con la “EU” relacionados con la prestación del servicio, en los procesos de atracción, selección, preingreso,
                    contratación y nómina.
                    </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>16. DÉCIMA SEXTA. INSTRUCCIONES Y RECOMENDACIONES PARA LA EMPRESA USUARIA (EU).</b> La “EU” debe respetar y acatar todas
                        las instrucciones y recomendaciones que desde el proceso de Seguridad y Salud en el Trabajo de la “EST” tenga implementadas,
                        siendo exclusiva responsabilidad de la “EU” reportar a la “EST” cualquier condición que pueda poner en riesgo la salud de los
                        empleados en misión y los accidentes de trabajo que se presenten por causa u ocasión del trabajo.</p>
                        <p>NOTA ACLARATORIA N° 4. La “EU” debe incluir a los empleados en misión que realizan actividades que generan riesgo de caída
                        mayor a 1.50mt, tales como trabajos en escaleras, andamios, plataformas sin barandas, estanterías, elevadores, bordes de losas,
                        trabajos en suspensión, entre otras, dentro de su programa de protección contra caídas, asegurando las condiciones en las cuales
                        se ejecutan dichas actividades y garantizando la seguridad del personal en misión que se encuentra en las instalaciones donde
                        desarrolla la actividad; cumpliendo tanto con la realización de los exámenes médicos con énfasis en trabajo en altura como con la
                        entrega de los elementos de protección contra caídas – EPCC programación del curso de altura por cuenta de la “EU” de
                        conformidad a lo establecido en resolución 4272 de 2021 y demás requisitos contemplados en la Resolución 1409 del 2012. En
                        caso de no cumplir con lo anteriormente especifcado, la “EU” será la directamente responsable por las reclamaciones de tipo
                        económico que puede generar por su omisión.</p>
                        <p>NOTA ACLARATORIA N° 5. Si la “EU” requiere que la” EST” se haga cargo de la consecución y entrega de la dotación, de los
                        elementos para la protección personal, curso de alturas u otro semejante para el ejercicio de la labor misional, la “EU” deberá hacer
                        la solicitud al momento del ingreso o durante la vigencia del contrato misional, contando la “EST” con treinta (30) días hábiles para
                        atender el requerimiento y los costos serán facturados con pago anticipado a ordenes de la “EU” ya que los mismos no se
                        encuentran contemplados en el porcentaje de administración de la propuesta comercial. (Ver Anexo N° 3 Propuesta Económica
                        Vigente). Además, la” EST” cobrara a la “EU” el equivalente en porcentaje de la administración negociada en la propuesta comercial
                        sobre el valor de la dotación, cursos, elementos de protección personal o semejante solicitados por la “EU”.</p>
                    </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>17. DÉCIMA SÉPTIMA. PÓLIZA DE SEGURO DE CUMPLIMIENTO DISPOSICIONES LEGALES.</b> La “EST” tiene para garantizar sus
                    obligaciones una Póliza, expedida por la compañía de SEGUROS DEL ESTADO S.A., Dicha póliza rige desde el primero (1) de enero
                    del año en curso a las cero (00:00) horas, hasta el treinta y uno (31) de diciembre a las 24 horas del mismo año y garantiza el pago
                    de salarios, prestaciones sociales e indemnizaciones laborales de los empleados en misión, en caso de iliquidez de la “EST” de
                    conformidad con la Ley 50/90 en su Art. 83 Numeral 5º y el decreto 4369/2006. Esta póliza se actualiza anualmente y podrá ser
                    solicitada por la “EU” en cualquier momento.
                    </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>18. DÉCIMA OCTAVA. REPORTE DE NOVEDADES.</b> La “EU” se compromete hacer llegar los reportes de novedades con dos (2) días
                    hábiles de anticipación al pago de la nómina y es responsable de la exactitud en dicho reporte, en materia de tiempo laborado para
                    efectos de horas extras, dominicales, festivos, recargos nocturnos, compensatorios, ausentismos (licencias, incapacidades),
                    auxilios, bonifcaciones y cualquier otro ingreso extra percibido por el empleado en misión o deducción autorizada por el mismo. En
                    el evento en que la “EU” no informe con la debida antelación los recargos generados por cualquiera de las causas antes
                    especifcadas, la “EST” solo pagará al empleado en misión el salario básico de conformidad con el periodo de pago y exime a la
                    “EST” de dichas responsabilidades. Las incapacidades cuyo origen sea de enfermedad general serán reconocidas por la EPS a
                    partir del tercer (3) día de esta, siempre y cuando al momento de ocurrencia del evento el empleado tenga cuatro (4) semanas
                    continuas efectivamente pagadas al sistema de seguridad social, de lo contrario, los salarios del empleado incapacitado serán
                    facturados a la “EU”. Cuando el trabajador en misión tiene derecho al pago de la prestación económica de la incapacidad médica,
                    se le restituirá a la “EU” por medio de una nota crédito cuando la EPS haya reconocido y pagado la correspondiente incapacidad.</p>
                    <p>PARÁGRAFO PRIMERO. Solamente la “EU” podrá programar el trabajo suplementario de horas extras, siempre y cuando no supere
                    el número de horas extras autorizadas por el Ministerio de Trabajo. Acatando lo dispuesto por La ley 2191 de 2022 desconexión
                    laboral y la Ley 2101 de 2021 reducción de la jornada.</p>
                    <p>PARÁGRAFO SEGUNDO. Los pagos que realiza la “EST” al empleado en misión, corresponden a las novedades de nómina
                    reportadas directamente por la “EU”. Por lo tanto, la “EST”. liquidará las prestaciones sociales, la seguridad social y los aportes
                    parafscales del empleado en misión de acuerdo con el IBC o salario promedio que generen dichas novedades.</p>
                    <p>PARÁGRAFO TERCERO. LA “EU” se obliga a comunicar a la “EST” durante el término de vigencia del presente contrato, todos los
                    benefcios extralegales que brinde al personal a su servicio relacionado con alimentación, recreación, capacitación y transporte, así
                    como su escala salarial, para preservar el principio de igualdad entre sus empleados y los empleados en misión.
                    </p>
                    <p>PARÁGRAFO CUARTO. En caso que se inicien investigaciones por parte de entidades administrativas y judiciales a la “EST” por el
                    incumplimiento de valores que correspondan a periodos de tiempo, jornadas de trabajo, auxilios y bonifcaciones que la “EU” no
                    hubiere incluido en los reportes periódicos para la liquidación de la nómina, la “EU” deberá responder por las sanciones económicas
                    que se impongan en virtud de las investigaciones adelantadas, así el contrato comercial haya fnalizado y exime a la “EST” de
                    responsabilidades</p>
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>19.DÉCIMA NOVENA. ESTABILIDAD LABORAL REFORZADA POR FUERO DE SALUD.</b> Todo trabajador en misión que presuntamente
                    ostente un fuero ocupacional, la “EST” junto con la “EU”, deberán mantener el vínculo laboral contractual hasta tanto el Ministerio
                    del Trabajo, proceda a autorizar dicha terminación, a menos que se presente renuncia voluntaria por parte del trabajador, momento
                    en el cual se procederá a su aceptación por escrito y tramite de desvinculación pertinente. Para la respectiva protección
                    constitucional respecto del derecho a la estabilidad ocupacional reforzada, por parte de la “EST” y la “EU”, se llevarán a cabo los
                    siguientes pasos:</p>
                    <p>1- Se verifcará que el fuero ocupacional o situación de debilidad manifesta cumple con los requisitos reconocidos por la ley y la
                    jurisprudencia para otorgar estabilidad laboral reforzada.</p>
                    <p>2- Si agotado este primer paso, se determina que el trabajador en misión goza de la protección constitucional, la “EU” se abstendrá
                    de dar por terminado el servicio misional hasta tanto no se obtenga la autorización de la entidad de supervisión vigilancia y control
                    administrativa de la que habla el artículo 26 de la Ley 361 de 1997.</p>
                    <p>3- En caso que la “EU” insista en terminar la obra o labor contratada del trabajador en misión y se niegue en recibirlo en sus
                    instalaciones, este asumirá de manera consecutiva y permanente el pago de los salarios, prestaciones sociales, seguridad social,
                    parafscales y el valor de la administración delegada del trabajador, mientras no se cumpla con la autorización señalada en el
                    numeral anterior de esta formalidad.</p>
                    <p>4- En relación a lo anterior, la empresa de servicios temporales iniciara con la solicitud ante el ministerio del trabajo de la
                    terminación de la relación contractual del trabajador en misión, siempre y cuando obtenga los requisitos establecidos por las
                    causales inmersas en el (Ver Anexo N° 3 Propuesta Económica Vigente) técnico vigente para la fecha de la solicitud y las demás
                    normas concomitantes vigentes al momento del requerimiento realizado por la EST.</p>
                    <p>5- Cabe anotar que la empresa de servicios temporales buscará los requisitos establecidos en la normatividad vigente de acuerdo a
                    las causales objetivas, justas causas incluyendo la establecida en el artículo 2.2.1.1.5 del Decreto 1072 de 2015 o el hecho
                    insuperable e incompatible, así como la aplicación de las distintas herramientas jurídicas para minimizar los costos para la “EU”
                    tales como la suspensión del contrato de trabajo en concordancia al Artículo 51 numeral 1 del CST, el IUS VARIANDI entre otras
                    fguras.</p>
                    <p>6- Una vez la empresa de servicios temporales obtenga los requisitos para solicitar el levantamiento del fuero ocupacional ante el
                    ministerio del trabajo, iniciará el trámite cuyo radicado será enviado a la “EU”, so pena de incumplimiento del contrato de la
                    referencia.</p>
                    <p>7- Lo mismo aplicara cuando el trabajador pueda reconocérsele la pensión de invalidez o afnes previa valoración de la proyección
                    de la PCLO o revisión de este valor porcentual.</p>
                    <p>8- Cada actuación que realice la “EST” en concordancia de los casos prioritarios que gocen del fuero ocupacional o se encuentren
                    en debilidad manifesta deberá ser informado a “EU” una vez se concreten.</p>
                    <p>9- Así mismo se concertará entre la “EST” y la “EU” reuniones periódicas para dar a conocer el avance del plan de acción de cada
                    trabajador que sea sujeto de la protección constitucional del fuero de salud o de quien se encuentre en debilidad manifesta.
                    </p>
                    <p>10- Una vez sea notifcada a la empresa de servicios temporales la autorización de la terminación de la relación contractual del
                    trabajador en misión por parte del ministerio del trabajo en cualquiera de sus instancias o reiteraciones de la solicitud, el
                    reconocimiento de la pensión de invalidez o afnes, así como la renuncia del trabajador el pago del emolumento económico por
                    parte de la “EU” cesara.</p>
                    <p>11- En los casos de estabilidad laboral reforzada la “EST” para el proceso médico, la rehabilitación o califcación del trabajador en
                    misión e igualmente, realizará bajo su costo toda gestión o trámite técnico o jurídico que se requiera para la fnalización del
                    contrato individual de trabajo y el retiro del trabajador en misión.
                    </p>
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>20. VIGÉSIMA. RETIROS A SOLICITUD DE LA EMPRESA USUARIA.</b> En el caso que la “EU” informe a la “EST” la cesación de la
                    necesidad de la obra y/o labor o labor del trabajador en misión, solo se reconocerá el valor de los salarios, prestaciones sociales y
                    demás novedades relacionadas con tal personal hasta la fecha de la solicitud de retiro, siempre y cuando la “EU” avise por escrito a
                    la “EST”, máximo el mismo día que se genera esta novedad, puesto que de lo contrario la “EU” asumirá los costos que esa omisión
                    demande. PARÁGRAFO PRIMERO. Las partes son conscientes de la amplia protección legal y jurisprudencial, que obliga a la
                    continuidad del contrato laboral, según el principio de estabilidad reforzada, en casos como: pre pensionados, empleados que se
                    encuentren incapacitados por accidentes de trabajo, enfermedad general o laboral, a los que estén en tratamiento médico o con
                    recomendaciones y/o restricciones, fuero de pre pensionado y a las mujeres en estado de embarazo, en licencia de maternidad, periodo de lactancia, licencia de paternidad y fuero de paternidad o los demás casos posteriores que la ley imponga. En dichos
                    casos, la “EU” no podrá notifcar la cesación de la necesidad de la obra y/o labor para la cual fue contratado el trabajador en misión,
                    ni requerir a la “EST” para terminar el servicio misional de un empleado con fuero, sin la previa autorización del Ministerio del
                    Trabajo y sin que sea levantada la novedad que le otorgó el fuero, ya que son protegidos y amparados legalmente y deberán
                    permanecer en las instalaciones de la “EU”, hasta que desaparezcan las causas que dieron origen a tal protección. En
                    consecuencia, la “EU” se compromete a mantener en al trabajador misional amparado con fuero de estabilidad laboral reforzada en
                    un cargo donde pueda acatar las restricciones o recomendaciones acorde al fuero que le da la protección, hasta la fnalización de
                    las causas que dieron origen o por autorización del Ministerio del Trabajo en los casos que aplique. En el evento, en que la “EU”
                    exija prescindir de un trabajador en misión protegido con fuero y se niegue a mantener la ejecución de la actividad misional en sus
                    instalaciones, la “EU” se obliga a rembolsar a la “EST” los salarios, provisión de prestaciones, seguridad social y demás sumas de
                    dinero que tuviere que asumir objeto de la reubicación laboral, un reintegro laboral o de una sanción por parte de las entidades
                    administrativas o judiciales.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>21. VIGÉSIMA PRIMERA. TRATAMIENTO Y PROTECCIÓN DE DATOS PERSONALES.</b> En concordancia con la Ley Estatutaria 1581 de
                    2012, SAITEMP S.A. asume las obligaciones como responsable del tratamiento de los datos personales de todas y cada una de las
                    personas naturales que estén involucradas en el marco de la ejecución del objeto del presente contrato. De la misma forma, y en
                    los casos en que la “EU”, en la ejecución del presente contrato (u otros celebrados con la “EST”), acceda o pueda llegar a acceder a
                    los datos personales de los empleados en misión, de conformidad con lo establecido en la Ley Estatutaria 1581 de 2012 obtendrá
                    la condición de Encargado del tratamiento de dichos datos. Por esta razón, deberá dar estricto cumplimiento a las obligaciones
                    surgidas de los deberes establecidos en el Artículo 18 de la mencionada ley, así como a los decretos que la reglamentan y demás
                    documentos concordantes.</p>
                    <p>PARÁGRAFO PRIMERO. la “EU”, podrá acceder sólo a los datos personales que permita la “EST” sobre su personal en misión y
                    únicamente para la fnalidad exclusiva de la realización del servicio contratado. Si existiere alguna solicitud específca relacionada
                    con información personal, esta será analizada bajo los parámetros de la normativa colombiana en protección de datos personales,
                    de tal manera que no se vulneren los derechos de Hábeas Data y la Intimidad de los titulares de dicha información personal.</p>
                    <p>PARÁGRAFO SEGUNDO. la “EU”, y cualquier miembro que haga parte de su personal, se obliga al secreto profesional respecto de
                    los datos personales y se obliga a no revelar la información que reciba durante la ejecución y cumplimiento del objeto del presente
                    contrato. Así mismo, LA “EU”, y cualquier miembro que haga parte de su personal, se abstendrán de obtener, compilar, sustraer,
                    ofrecer, vender, intercambiar, enviar, comprar, interceptar, divulgar, modifcar y/o emplear los mencionados datos para las
                    actividades y fnes diferentes a los contratadas.</p>
                    <p>PARÁGRAFO TERCERO. La “EU”, y cualquier miembro que haga parte de su personal, se compromete a devolver o suprimir los
                    datos personales suministrados, terminada la vigencia de las relaciones contractuales. Esta condición aplica aun después de la
                    vigencia de la relación contractual y se obliga a mantenerla de manera confdencial protegiendo los datos personales para evitar su
                    divulgación no autorizada.
                    </p>
                    <p>PARÁGRAFO CUARTO. En caso de que la “EU”, sea requerida por cualquier autoridad, para temas relacionados con los empleados
                    en misión, esta deberá dar respuesta a dicha autoridad, indicando que la solicitud se debe realizar directamente la “EST”.</p>
                    <p>PARÁGRAFO QUINTO. La “EU”, se obliga a reportar o comunicar inmediatamente, al Ofcial de Protección de Datos Personales de
                    SAITEMP S.A., cuando tenga conocimiento de pérdida, vulneración, modifcación o cualquier otro tipo de incidente que ponga en
                    peligro la seguridad, integridad o confdencialidad de los datos personales a los que les dé tratamiento en la ejecución de las
                    actividades contratadas.
                    </p>
                    <p>PARÁGRAFO SEXTO. En caso de que la “EU” no haya implementado las disposiciones establecidas en Régimen Colombiano de
                    Protección de Datos Personales, se obliga a adoptar las medidas establecidas por la “EST” para garantizar la seguridad de los
                    datos personales a los que le dará tratamiento (recolección, almacenamiento, uso, circulación y/o eliminación) y evitar su
                    alteración, pérdida y/o tratamiento no autorizado. El incumplimiento de lo aquí dispuesto, así como lo daños y perjuicios que se
                    deriven de ello, será asumido por la parte que incumple, pudiendo constituir causal de terminación unilateral del contrato por la otra
                    parte.</p>
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>22. VIGÉSIMA SEGUNDA. INTEGRIDAD Y CONFIDENCIALIDAD.</b> Este contrato documenta de manera exhaustiva, el acuerdo total y
                    completo entre las partes que lo constituyen y, por lo tanto, reemplaza, sustituye, deroga y deja sin efectos cualquier otro contrato,
                    acuerdo verbal o escrito, expreso o tácito, que exista o pudiera existir entre las partes o alguno de sus integrantes. El presente
                    contrato, incluyendo sus anexos (propuesta comercial, modifcaciones contractuales que las partes llegasen a suscribir de manera
                    bilateral, otro sí y acuerdos), cuando sea del caso, podrá ser modifcado por las partes por mutuo acuerdo, consignado por escrito y
                    previa evidencia de que quienes representan a las partes están debidamente facultados para tal efecto. Además, acuerdan las
                    partes que se obligan a mantener la confdencialidad sobre el presente contrato.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>23. VIGÉSIMA TERCERA. PREVENCIÓN LAVADO DE ACTIVOS Y FINANCIACIÓN DEL TERRORISMO.</b> Las partes se obligan a realizar
                    todas las actividades encaminadas a asegurar que todo su personal a cargo, empleados, socios, accionistas, administradores,
                    clientes, proveedores, etc., y los recursos de estos, no se encuentren relacionados o provengan, de actividades ilícitas;
                    particularmente, de lavado de activos o fnanciación del terrorismo. En todo caso, si durante el plazo de vigencia del convenio se
                    encontraren en alguna de las partes, dudas razonables sobre sus operaciones, así como el origen de sus activos y/o que alguna de
                    ellas, llegare a resultar inmiscuido en una investigación de cualquier tipo (penal, administrativa, etc.) relacionada con actividades ilícitas, lavado de dinero o fnanciamiento del terrorismo, o fuese incluida en las listas internacionales vinculantes para Colombia,
                    de conformidad con el derecho internacional (listas de naciones unidas- ONU), en listas de la OFAC o Clinton, etc., la parte libre de
                    reclamo tendrá derecho de terminar unilateralmente el convenio sin que por este hecho, esté obligado a indemnizar ningún tipo de
                    perjuicio a la parte que lo generó. La “EU”, manifesta que la información aportada verbalmente y/o por escrito, relacionada con el
                    Sistema para la Administración del Riesgo del Lavado de Activos y Financiación del Terrorismo-SAGRILAFT- es veraz y verifcable, y
                    se obliga de acuerdo las normas vigentes en la materia a: (1). Proporcionar toda la información, Formularios, anexos y soportes
                    que la Empresa considere necesarios para controlar el riesgo de LAFT/FAPDM. (2). Actualizar una vez al año, la documentación e
                    información aportada que exige el CONTRATANTE para el conocimiento del cliente, dando cumplimiento a las disposiciones
                    contenidas tanto en el Manual SAGRILAFT de la EMPRESA, las cuales son concordantes con las normas vigentes en la materia.</p>
                    <p>PARÁGRAFO PRIMERO. El incumplimiento por parte de la “EU” de lo establecido, en esta cláusula, dará lugar a la terminación
                    anticipada del presente contrato.</p>
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>24. VIGÉSIMA CUARTA. MÉRITO EJECUTIVO.</b> El presente contrato presta mérito ejecutivo por ser una obligación clara, expresa y
                    exigible para las partes, y no requerirá de constitución en mora para ser ejecutado a través de la jurisdicción ordinaria.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>25. VIGÉSIMA QUINTA. HONORARIOS DE ABOGADOS.</b> En el caso de que la “EST” requiera iniciar acciones legales por
                    incumplimiento de la “EU” de las disposiciones de este contrato, al igual que por el impago de las facturas en los términos
                    indicados en esta cláusula y en el contrato, la “EST” cobrará a la “EU”, sobre el valor total de la deuda más intereses, el quince por
                    ciento (15%) de los honorarios que se llegaran a causar por concepto de acciones pre jurídicas y jurídicas.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>26. VIGÉSIMA SEXTA. SOLUCIÓN DE CONTROVERSIAS.</b> Toda diferencia o controversia que surja entre las partes a causa de este
                    contrato se resolverá directamente entre las partes en disputa. Empero, si ello no fuere posible o cualquiera de las partes
                    involucradas en la disputa no estuviere en disposición de arreglar directamente tales diferencias, deberá agotarse una diligencia de
                    conciliación extrajudicial en derecho ante cualquier entidad legalmente autorizada para efectuarla y en caso de no llegar a un
                    acuerdo, se acudirá a la administración de justicia por la vía ejecutiva.
                    PARÁGRAFO PRIMERO. El presente contrato, se rige por las leyes de la República de Colombia y deberá ser interpretado conforme a
                    ellas. La solución de controversias que puedan derivarse de su ejecución se rige por dichas leyes.
                    PARÁGRAFO SEGUNDO. Se deja constancia que entre los contratantes no existe vinculación económica, en los términos del
                    capítulo XI del libro 2do. Del Código de Comercio.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>27. VIGÉSIMA SÉPTIMA. ACUERDO Y ACEPTACIÓN PARA USO E IMPLEMENTACIÓN DE LA FIRMA ELECTRÓNICA.</b> Como
                    representantes legales de las Partes, aceptamos frmar electrónicamente los formatos de registro de proveedores, contratos, y
                    demás documentos que se adelanten o tramiten entre ellas, así como la implementación de la frma electrónica para todas las
                    transacciones de índole laboral que se celebren con los trabajadores que prestan servicios misionales. En consecuencia,
                    autorizamos al proveedor defnido por la “EST” para tal menester, como proveedor tecnológico para enviar documentos, mensajes,
                    avisos o notifcaciones a nuestros correos electrónicos; realizar controles de verifcación y seguridad mediante preguntas; enviar
                    mensajes de texto o SMS al teléfono móvil registrado en la base de datos de proveedores; e identifcar la ubicación, la dirección IP
                    o los datos del ordenador, entre otros.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>28. VIGÉSIMA OCTAVA. DIRECCIONES DE NOTIFICACIÓN.</b> Para todos los efectos a los que haya lugar se tendrán como direcciones
                    las indicadas en el encabezado del contrato.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>29. VIGÉSIMA NOVENA. DOMICILIO CONTRACTUAL.</b> Se señala como domicilio contractual para todos los efectos del presente
                    contrato la Ciudad donde opera con cobertura a nivel nacional en las sedes que actualmente tenga o preste servicio la “EST”.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>30.TRIGÉSIMA. PERFECCIONAMIENTO.</b>El presente contrato se entiende perfeccionado con la suscripción de este, reconociendo
                    ambas partes la autenticidad de sus frmas, por lo que renuncian a la diligencia previa de reconocimiento de contenido y frmas.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>31.TRIGÉSIMA PRIMERA. AUTORIZACION ESPECIAL.</b> La “EU”, autoriza a la “EST” y/o a quien represente sus derechos u ostente en
                    el futuro la calidad de acreedor para: reportar, procesar, solicitar y divulgar toda la información referente al comportamiento
                    crediticio de la “EU” a las centrales de Información del Sector Financiero y de crédito como: CIFIN, FENALCO- PROCREDITO,
                    DATACREDITO o cualquier otra entidad que maneje o administre bases de datos con los mismos fines.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>32.TRIGÉSIMA SEGUNDA. HABEAS DATA.SAITEMP S.A | | SERVICIOS ADMINISTRATIVOS AL INSTANTE S.A.S.</b> o cualquier
                    organización de la familia empresarial, como organización, tiene como objetivo primordial el prestar sus servicios bajo los
                    estándares más altos de calidad, efciencia y respeto por la Ley. Por ende, no puede desconocer derechos y prerrogativas que son
                    inherentes al tratamiento de los datos personales y que están en cabeza de los titulares de los mismos. “HABEAS DATA”. En
                    cumplimiento de lo dispuesto en la Ley 1581 de 2012 y la Ley 1273 de 2009, sus decretos reglamentarios, y demás normas que la
                    adicionen o modifquen, queda informado que los datos personales entregados por “EST” a través de la documentación necesaria
                    para proceder a la contratación, o en cualquier momento de la relación contractual serán tratados por “EST”, con la fnalidad de
                    garantizar la celebración, ejecución y liquidación del presente contrato o satisfacer los requerimientos que afecten de forma directa
                    o indirecta el mismo. PROTECCIÓN DE DATOS WEB: La protección de datos web de SAITEMP S.A | SERVICIOS ADMINISTRATIVOS
                    AL INSTANTE S.A.S. o cualquier organización de la familia empresarial, está en la obligación de acuerdo a la Ley 1581 de 2012, sus
                    decretos reglamentarios y cualquier normatividad que la aclare, modifque o derogue, a garantizar el buen y adecuado uso de los datos personales registrados de manera virtual o través de cualquier portal informático, como son las páginas web administradas
                    por la organización. PROTECCIÓN DE DATOS DE FORMULARIOS: Cualquier información obtenida a través del diligenciamiento de
                    formularios solicitados por SAITEMP S.A | SERVICIOS ADMINISTRATIVOS AL INSTANTE S.A.S. o cualquier organización del grupo
                    empresarial, está igualmente protegida por las políticas contenidas por el presente manual, así como por las demás disposiciones
                    que el ordenamiento jurídico colombiano trata sobre el particular. CON RESPECTO A LA PROTECCIÓN DE DATOS PERSONALES, A
                    PROPÓSITO DE CUALQUIER PETICIÓN, QUEJA O RECLAMO POR PARTE DEL TITULAR. SAITEMP S.A | AL INSTANTE S.A.S. o
                    cualquier organización del grupo empresarial, se regirá por el procedimiento especial dispuesto para este tipo de actuaciones,
                    contenido en el Documento para el manejo de quejas y solicitudes de esta misma corporación. Se garantiza la protección de
                    cualquier información obtenida por este medio, de acuerdo a las políticas y procedimientos contenidos en el presente manual de
                    protección de datos personales y demás disposiciones legales o reglamentarias que tengan alguna incidencia sobre el particular.
                    En todo caso, se ha establecido que el tiempo límite para dar una respuesta de fondo es de diez (10) días hábiles, en cumplimiento
                    de lo dispuesto por la Ley 1581 de 2012. DEL DERECHO AL ACCESO O CONSULTA, A RECLAMAR, A SUPRIMIR, A LA
                    RECTIFICACIÓN Y ACTUALIZACIÓN DE DATOS. SAITEMP S.A | AL INSTANTE S.A.S. o cualquier organización del grupo empresarial,
                    garantiza el derecho de acceso y consulta, rectifcación, supresión y actualización, según lo estipulado por la Ley 1581 de 2012. En
                    el evento en que el titular, previa identifcación, legitimidad o la de su representante debidamente acreditado, desee consultar,
                    acceder, rectifcar o actualizar cualquier información que repose en la base de datos, podrá hacerlo a través de una PQR (petición,
                    queja o reclamo). DEL DERECHO A REVOCAR LA AUTORIZACIÓN. En el evento en que el titular, previa identifcación, legitimidad o la
                    de su representante debidamente acreditado, desee revocar la autorización para el uso de la totalidad de sus datos personales,
                    podrá hacerlo a través de una PQR. PROTECCIÓN DE DATOS EN LOS CONTRATOS/DE LA CLÁUSULA DE PROTECCIÓN DE DATOS
                    PERSONALES O CLÁUSULA HABEAS DATA. SAITEMP S.A | AL INSTANTE S.A.S. o cualquier organización del grupo empresarial
                    cuenta con un tipo específco de cláusula de habeas data, dándole cumplimiento a lo estipulado en la Ley 1581 de 2012, sus
                    decretos reglamentarios y demás disposiciones que la complementen y/o modifque, al garantizar la protección de los datos
                    personales de los titulares que tienen algún tipo de vínculo de naturaleza contractual con la organización.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>33. TRIGÉSIMA TERCERA. USO DE IDENTIDAD GRÁFICA.</b> Las partes Autorizan y ofcializan por este medio el uso de su identidad
                    visual tanto impresa como virtual entendida como Logotipo y Naming de Marca, que en el momento este manejando de su
                    identidad corporativa, con la fnalidad que pueda ser utilizado por SAITEMP S.A | AL INSTANTE S.A.S. en su portafolio de servicios
                    tanto físico como digital en el espacio “Empresas que confían en nuestra Marca”. Así mismo la “EU” podrá utilizar el logo de
                    SAITEMP S.A | AL INSTANTE S.A.S con fnes de estrategias comerciales de fdelización, tanto físico como digital bajo el
                    lineamiento de marca dado del logotipo y “naming” nombre comercial de Marca que será solicitado de manera inmediata después
                    de haber sido ofcializado el CONTRATO DE PRESTACIÓN DE SERVICIOS TEMPORALES DE COLABORACIÓN en Formato PNG, JPG,
                    PDF, archivo editable Corel, Ilustrador. Cualquier cambio de la identidad visual, deberá darla a conocer, con el ánimo de actualizarla
                    en medios digitales y en su defecto en impresos de ambas partes.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>34.TRIGÉSIMA CUARTA. MERCADEO & MARKTING.</b> La “EU” autoriza a la “EST”, el envió de acciones publicitarias tales como
                    mensajería de texto, mensajes de chat de WhatsApp, campañas de Mailing, etiquetar en redes sociales ofciales (Campañas
                    masivas de empleo / solicitud de vacantes); dependiendo de la creación de campañas o estrategias en común acuerdo de
                    promoción y posicionamiento, con el objetivo de fortalecer relaciones comerciales, logrando así tener una comunicación asertiva y
                    de apoyo, se solicita los correos y números de WhatsApp de los líderes de procesos de la “EU” tales como: Presidencia, Gerencia
                    General, Comercial, Mercadeo y Talento Humano. Para evitar tener interceptación de datos informáticos por parte de terceros,
                    manteniendo los lineamientos para combatir la violación de datos personales.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><b>35.TRIGÉSIMA QUINTA. FACTORING.</b> La “EU” en caso de incumplimiento con el pago de las facturas emitidas, autoriza mediante el
                    presente contrato, a la “EST” o quien representé sus derechos, para vender o ceder el derecho de cobro de la deuda a un tercero el
                    cual operará bajo los lineamientos y sometimiento a la inspección y vigilancia de la Superintendencia Bancaria Y Financiera, es
                    preciso mencionar, que la empresa que actué en representación de la “EST” es una empresa fnanciera legalmente autorizada para
                    prestar el conjunto de servicios que comprende la operación de cobro, a título de venta, en frme, defnitiva y como una
                    universalidad jurídica a partir de la fecha de incumplimiento de o los pagos acordados y plasmados en las cláusulas: segunda,
                    tercera, cuarta, décima cuarta del presente contrato, de acuerdo a la fecha de mora, donde las cuentas de cobro hacen parte
                    integral del presente contrato a título de cláusula penal que ampara a quien represente sus derechos, la cual procederá al cobro de
                    las facturas pendientes, costas judiciales en virtud de la Ley 45 de 1923, Ley 74 de 1989, Ley 35 de 1993 y el Decreto 3039 de 1989.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $html .= '<tr>
                <td style="font-size:9px;"><p><b>DOCUMENTOS ANEXOS QUE HACEN PARTE INTEGRAL DEL CONTRATO</b></p> 
                <p>1) Empresa Usuaria (EU):<br>
                - RUT (Actualizado).<br>
                - Cámara de comercio actualizada.<br>
                - Cédula al 150% representante legal. <br>
                - Certifcado bancario.
                </p>
                <p>2) Empresa de Servicios Temporales (EST):<br>
                 - RUT (Actualizado).<br>
                 - Cámara de comercio actualizada.<br>
                 - Cédula al 150% representante legal. <br>
                 - Certifcado bancario.
                </p>
                <p>3) Propuesta Económica:<br>
                - FUC FEGC 01-02 Formato único conocimiento contraparte
                </p>
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $fechaActual = Carbon::now()->format('d/m/Y');
            $html .= '<tr>
                <td style="font-size:9px;"><b>FIRMAS:</b>
                    Para constancia, el presente contrato se frma en la Ciudad/Municipio de MEDELLÍN del Departamento ANTIOQUIA, el ' . $fechaActual . ',
                     en dos (2) ejemplares de igual valor, cada uno de ellos con destino a cada una de las partes.
                </td>
                </tr>';
            $html .= '</table>';
            $html .= '<table style="border: 1px #000000 solid; padding:10px; border-collapse: collapse;">';
            $fechaActual = Carbon::now()->format('d/m/Y');
            $html .= '<tr>
                <td style="font-size:9px; border:1px black solid"><b>EMPRESA USUARIA (EU)</b><br><br><br><br><br>
                   <p><b>' . $contrato_data['representante_legal'] . '</b><br>' . $contrato_data['identificacion'] . '
                   </p>
    
                </td>
                <td style="font-size:9px; border:1px black solid"><b>EMPRESA DE SERVICIOS TEMPORALES</b><br><br><br><br><br>
                  <p><b>HUBER ANTONIO BAENA MEJÍA</b><br>CC 71703511 de Medellín
                   </p>
                </td>
                </tr>';
            $html .= '</table>';
            /*        $margen_izquierdo = 15;
            $margen_derecho = 15;
            $pdf->SetMargins($margen_izquierdo, 40, $margen_derecho);
            $pdf->SetAutoPageBreak(true, 50); */
            // Generar PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            $totalPages = 0;
            if ($pdf->getNumPages() == 1) {
                $totalPages = 1;
            } else {
                $totalPages = $pdf->getNumPages();
            }

            // Agregar membrete en cada página después de la primera
            for ($i = 1; $i <= $totalPages; $i++) {
                // Cambiar a la página correspondiente
                $pdf->setPage($i);

                if ($i > 1) {
                    $url = public_path('/upload/MEMBRETE.png');
                    /*          $pdf->SetMargins(0, 0, 0);
                    $pdf->SetAutoPageBreak(false, 40); */
                    $pdf->SetMargins(0, 0, 0);
                    $pdf->SetAutoPageBreak(false, 0);
                    $pdf->Image($url, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
                    $pdf->SetMargins(15, 40, 15);
                    $pdf->SetAutoPageBreak(true, 40);
                }
                // Ajustar los márgenes para que el membrete no interfiera con el contenido

            }
            if ($outputToBrowser) {
                $pdf->Output('Reporte_Cliente_' . $id . '.pdf', 'I');
            } else {
                $tempFilePath = tempnam(sys_get_temp_dir(), 'tcpdf_' . Carbon::now()->timestamp) . '.pdf';
                $pdf->Output($tempFilePath, 'F');
                return $tempFilePath;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el PDF: ' . $e->getMessage()], 500);
        }
    }
}