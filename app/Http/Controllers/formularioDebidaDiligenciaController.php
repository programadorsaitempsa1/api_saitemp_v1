<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Mockery\Undefined;
use PhpParser\Node\Stmt\TryCatch;

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

    public function consultacliente($cantidad)
    {
        $result = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
            ->select(
                'usr_app_clientes.id',
                'usr_app_clientes.razon_social',
                'usr_app_clientes.numero_identificacion',
                'usr_app_clientes.nit',
                'ven.nom_ven as vendedor',
                'usr_app_clientes.telefono_empresa',
                'usr_app_clientes.created_at'
            )
            ->orderby('id', 'DESC')
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


    public function getbyid($id)
    {
        $result = Cliente::join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
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
                'dep1.nombre as departamento',
                'dep1.id as departamento_id',
                'dep2.nombre as departamento_prestacion_servicio',
                'dep2.id as departamento_prestacion_servicio_id',
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
                'usr_app_clientes.declaraciones_autirizaciones',
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
            )
            ->where('usr_app_clientes.id', '=', $id)
            ->first();
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

        return response()->json($result);
    }

    public function filtro($cadena)
    {
        $consulta = base64_decode($cadena);
        $valores = explode("/", $consulta);
        $campo = $valores[0];
        $operador = $valores[1];
        $valor = $valores[2];
        $valor2 = $valores[3];
        // return $campo . '' . $operador . '' . $valor.''.$valor2;
        if ($operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $valor . '%';
        } else if ($operador == 'Igual a') {
            $operador = '=';
        } else if ($operador == 'Igual a fecha') {
            $operador = '=';
            $result = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->whereDate('usr_app_clientes.' . $campo, $operador, $valor)
                ->select(
                    'usr_app_clientes.id',
                    'usr_app_clientes.razon_social as nombre',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.nit',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.created_at'
                )
                ->orderby('id', 'DESC')
                ->paginate();
            return response()->json($result);
        } else if ($operador == 'Entre') {
            $result = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->whereDate('usr_app_clientes.' . $campo, '>=', $valor)
                ->whereDate('usr_app_clientes.' . $campo, '<=', $valor2)
                ->select(
                    'usr_app_clientes.id',
                    'usr_app_clientes.razon_social as nombre',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.nit',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.created_at'
                )
                ->orderby('usr_app_clientes.id', 'DESC')
                ->paginate();
            return response()->json($result);
        }
        if ($campo == 'vendedor') {
            $result = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
                ->where('ven.nom_ven', $operador, $valor2)
                ->select(
                    'usr_app_clientes.id',
                    'usr_app_clientes.razon_social as nombre',
                    'usr_app_clientes.numero_identificacion',
                    'usr_app_clientes.nit',
                    'ven.nom_ven as vendedor',
                    'usr_app_clientes.telefono_empresa',
                    'usr_app_clientes.created_at'
                )
                ->orderby('id', 'DESC')
                ->paginate();
            return response()->json($result);
        }
        $result = cliente::join('gen_vendedor as ven', 'ven.cod_ven', '=', 'usr_app_clientes.vendedor_id')
            ->where('usr_app_clientes.' . $campo, $operador, $valor)
            ->select(
                'usr_app_clientes.id',
                'usr_app_clientes.razon_social as nombre',
                'usr_app_clientes.numero_identificacion',
                'usr_app_clientes.nit',
                'ven.nom_ven as vendedor',
                'usr_app_clientes.telefono_empresa',
                'usr_app_clientes.created_at'
            )
            ->orderby('id', 'DESC')
            ->paginate();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        DB::beginTransaction();

        try {
            // $request = $request[0];
            $actividad_ciiu = $this->actividades_ciiu($request['actividad_ciiu']);
            $cliente = new cliente;
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
            $cliente->declaraciones_autirizaciones = $request['declaraciones_autorizaciones'];
            $cliente->tratamiento_datos_personales = $request['tratamiento_datos_personales'];
            $cliente->operaciones_internacionales = $request['operaciones_internacionales'];
            $cliente->tipo_cliente_id = $request['tipo_cliente_id'];
            $cliente->tipo_proveedor_id = $request['tipo_proveedor_id'] == '' ? 1 : $request['tipo_proveedor_id'];
            $cliente->municipio_prestacion_servicio_id = $request['municipio_prestacion_servicio'];
            $cliente->save();

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
                // if ($item['nombre'] != '' || $item['contacto'] != '' || $item['telefono'] != '') {
                $ReferenciaComercial = new ReferenciaComercial;
                $ReferenciaComercial->razon_social = $item['nombre'];
                $ReferenciaComercial->contacto = $item['contacto'];
                $ReferenciaComercial->telefono = $item['telefono'];
                $ReferenciaComercial->cliente_id = $cliente->id;
                $ReferenciaComercial->save();
                // }
            }

            DB::commit();
            return response()->json(['status' => '200', 'message' => 'ok', 'client' => $cliente->id]);
        } catch (\Exception $e) {
            // Revertir la transacción si se produce alguna excepción
            DB::rollback();
            return $e;
            // return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor verifique el llenado de todos los campos e intente nuevamente']);
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
            foreach ($result as $item) {
                $item->delete();
            }
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
        $cliente = Cliente::where('usr_app_clientes.id', '=', $id)
            ->select()
            ->first();
        try {
            $actividad_ciiu = $this->actividades_ciiu($request['actividad_ciiu']);
            $cliente->operacion_id = $request['operacion'];
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
            $cliente->acuerdo_comercial = $request['observaciones'];
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
            $cliente->declaraciones_autirizaciones = $request['declaraciones_autorizaciones'];
            $cliente->tratamiento_datos_personales = $request['tratamiento_datos_personales'];
            $cliente->operaciones_internacionales = $request['operaciones_internacionales'];
            $cliente->tipo_cliente_id = $request['tipo_cliente_id'];
            $cliente->tipo_proveedor_id = $request['tipo_proveedor_id'];
            $cliente->municipio_prestacion_servicio_id = $request['municipio_prestacion_servicio'];
            $cliente->save();

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
                if ($item['nombre'] != '' || $item['tipo_identificacion'] != '' || $item['identificacion']) {
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
            if ($request['calidad_tributaria'][0]['opcion'] != '' ||  $request['calidad_tributaria'][1]['opcion'] != '' || $request['calidad_tributaria'][2]['opcion'] != '') {
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


            $origenFondo = OrigenFondo::where('cliente_id', '=', $id)
                ->select()
                ->first();
            $origenFondo->tipo_origen_fondos_id = $request['tipo_origen_fondo'];
            $origenFondo->otro_origen = $request['otro_tipo_origen_fondos'];
            $origenFondo->tipo_origen_medios_id = $request['tipo_origen_medios'];
            $origenFondo->tipo_origen_medios2_id = $request['otro_tipo_origen_medios'];
            $origenFondo->alto_manejo_efectivo = $request['alto_manejo_efectivo'];
            $origenFondo->cliente_id = $id;
            $origenFondo->save();


            $OperacionIternacional = OperacionIternacional::where('cliente_id', '=', $id)
                ->select()
                ->first();
            if ($request['tipo_operacion_internacional'] != '') {
                $OperacionIternacional->tipo_operaciones_id = $request['tipo_operacion_internacional'];
                $OperacionIternacional->cliente_id = $id;
                $OperacionIternacional->save();
            }

            $ReferenciaBancaria = ReferenciaBancaria::where('cliente_id', '=', $id)
                ->select()
                ->get();
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

            $personasExpuestas = PersonasExpuestas::where('cliente_id', '=', $id)
                ->select()
                ->get();
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

            $ReferenciaComercial = ReferenciaComercial::where('cliente_id', '=', $id)
                ->select()
                ->get();
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

            DB::commit();
            return response()->json(['status' => '200', 'message' => 'ok', 'client' => $cliente->id]);
        } catch (\Exception $e) {
            // Revertir la transacción si se produce alguna excepción
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor intente nuevamente']);
        }
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
}
