<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\formularioGestionIngreso;
use App\Models\FormularioIngresoArchivos;
use App\Models\FormularioIngresoResponsable;
use App\Models\FormularioIngresoPendientes;
use App\Models\ListaTrump;
use App\Models\RegistroIngresoLaboratorio;
use App\Models\FormularioIngresoSeguimiento;
use App\Models\UsuarioPermiso;
use App\Models\FormularioIngresoSeguimientoEstado;
use App\Models\User;
use App\Models\CandidatoServicioModel;
use App\Models\OrdenServcio;
use Carbon\Carbon;
use App\Events\NotificacionSeiya;
use TCPDF;
use Illuminate\Support\Facades\DB;

// use App\Events\EventoPrueba2;


class formularioGestionIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {

        $permisos = $this->validaPermiso();
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->leftJoin('usr_app_usuarios as us', 'us.id', 'usr_app_formulario_ingreso.candidato_id')
            ->leftJoin('usr_app_candidatos_c as can', 'can.usuario_id', 'us.id')
            ->when(!in_array('42', $permisos), function ($query) {
                return $query->where(function ($query) {
                    $query->whereNotIn('cli.nit', ['811025401', '900032514'])
                        ->orWhereNull('cli.nit');
                });
            })
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.n_servicio',
                'tiser.nombre_servicio',
                'usr_app_formulario_ingreso.updated_at',
                'usr_app_formulario_ingreso.created_at',
                DB::RAW("CONCAT(can.num_doc,'',usr_app_formulario_ingreso.numero_identificacion) as numero_documento"),
                DB::RAW("CONCAT(primer_nombre,' ',segundo_nombre,' ',primer_apellido,' ',segundo_apellido,' ',usr_app_formulario_ingreso.nombre_completo) as nombre_completo"),
                'usr_app_formulario_ingreso.cargo',
                'cli.razon_social',
                'mun.nombre as ciudad',
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                DB::raw("FORMAT(CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE), 'dd/MM/yyyy') as fecha_ingreso"),
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',
                'cli.contratacion_hora_confirmacion as hora_confirmacion',
                'usr_app_formulario_ingreso.responsable_id',
            )
            ->orderby('usr_app_formulario_ingreso.id', 'DESC')
            ->paginate($cantidad);
        foreach ($result as $item) {
            $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;
        }
        return response()->json($result);
    }

    public function consulta_id_trump($id)
    {
        $result = ListaTrump::select(
            'cod_emp',
            'nombre',
            'observacion',
            'fecha',
            'bloqueado',
        )
            ->where('cod_emp', '=', $id)
            ->first();
        if ($result !== null) {
            if ($result->bloqueado == 1) {
                return response()->json(['status' => 'error', 'message' => 'Este candidato se encuentra en la lista trump', 'bloqueado' => 'Si']);
            } else {
                $result->bloqueado = 'No';
            }
            return $result;
        } else {
            $result = formularioGestionIngreso::where('usr_app_formulario_ingreso.numero_identificacion', '=', $id)
                ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
                ->select(
                    'usr_app_formulario_ingreso.responsable_id',
                    'numero_identificacion',
                    'esti.id as estado_ingreso_id',
                    'esti.nombre as estado_ingreso',
                    'usr_app_formulario_ingreso.responsable as responsable_ingreso'
                )
                ->first();
            if ($result != null) {
                if ($result->estado_ingreso_id == 31 || $result->estado_ingreso_id == 32 || $result->estado_ingreso_id == 44 || $result->estado_ingreso_id == 17 || $result->estado_ingreso_id == 12 && $result->responsable_id == 502) {
                    return response()->json(['status' => 'success', 'message' => 'Este candidato es apto para activar o ingresar.', 'apto' => '1']);
                } else if ($result->responsable_ingreso != null && $result->numero_identificacion != null) {
                    return response()->json(['status' => 'error', 'message' => 'Este candidato ya se encuentra registrado', 'no_apto' => '2']);
                }
            }
        }
    }


    public function actualizaestadoingreso($item_id, $estado_id, $responsable_id = null,  $responsable_actual = null, $estado_inicial = null)
    {

        $user = auth()->user();
        $usuarios = FormularioIngresoResponsable::where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado_id)
            ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->select(
                'usuario_id',
                'usr.nombres',
                'usr.apellidos'
            )
            ->get();

        // Obtener el número total de responsables
        $numeroResponsables = $usuarios->count();

        // Obtener el registro de ingreso
        $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
            ->first();

        $permisos = $this->validaPermiso();

        if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user->id && !in_array('31', $permisos)) {
            return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
        }

        // Asignar a cada registro de ingreso un responsable
        $indiceResponsable = $registro_ingreso->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable];

        $this->eventoSocket($responsable->usuario_id);

        $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
        $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable != null ?  str_replace("null", "", $registro_ingreso->responsable) : str_replace("null", "", $responsable_actual);
        $seguimiento_estado->responsable_final = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);;
        $seguimiento_estado->estado_ingreso_inicial = $estado_inicial != null ? $estado_inicial : $registro_ingreso->estado_ingreso_id;
        $seguimiento_estado->estado_ingreso_final =   $estado_id;
        $seguimiento_estado->formulario_ingreso_id =  $item_id;
        $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' .  $user->apellidos;
        $seguimiento_estado->save();

        // Actualizar el registro de ingreso con el estado y el responsable
        $registro_ingreso->estado_ingreso_id = $estado_id;
        $registro_ingreso->asignacion_manual = 0;
        $registro_ingreso->responsable_id = $responsable->usuario_id;
        $registro_ingreso->responsable = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);
        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }

    // public function actualizaResponsableingreso($item_id, $responsable_id, $nombre_responsable)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $user = auth()->user();
    //         $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
    //             ->first();

    //         $responsable = $this->validaPermiso();

    //         if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user->id && !in_array('31', $permisos)) {
    //             return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
    //         }

    //         $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
    //         $seguimiento_estado->responsable_inicial =  str_replace("null", "", $registro_ingreso->responsable);
    //         $seguimiento_estado->responsable_final = $nombre_responsable;
    //         $seguimiento_estado->estado_ingreso_inicial =  $registro_ingreso->estado_ingreso_id;
    //         $seguimiento_estado->estado_ingreso_final =  $registro_ingreso->estado_ingreso_id;
    //         $seguimiento_estado->formulario_ingreso_id =  $item_id;
    //         $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' . str_replace("null", "", $user->apellidos);

    //         $seguimiento_estado->save();

    //         $registro_ingreso->responsable_anterior =  str_replace("null", "", $registro_ingreso->responsable);
    //         $registro_ingreso->responsable =  str_replace("null", "", $nombre_responsable);
    //         $registro_ingreso->asignacion_manual = 1;
    //         $registro_ingreso->responsable_id = $responsable_id;
    //         $registro_ingreso->save();
    //         $seguimiento = new FormularioIngresoSeguimiento;
    //         $seguimiento->estado_ingreso_id = $registro_ingreso->estado_ingreso_id;
    //         $seguimiento->usuario = str_replace("null", "", $registro_ingreso->responsable);
    //         $seguimiento->formulario_ingreso_id = $item_id;
    //         $seguimiento->save();
    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    //     }
    // }
    public function actualizaResponsableingreso($item_id, $responsable_id, $nombre_responsable)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
                ->first();

            $permisos = $this->validaPermiso();


            if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user->id && !in_array('31', $permisos)) {
                return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
            }

            $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
            $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable;
            $seguimiento_estado->responsable_final = $nombre_responsable;
            $seguimiento_estado->estado_ingreso_inicial =  $registro_ingreso->estado_ingreso_id;
            $seguimiento_estado->estado_ingreso_final =  $registro_ingreso->estado_ingreso_id;
            $seguimiento_estado->formulario_ingreso_id =  $item_id;
            $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' .  $user->apellidos;

            $seguimiento_estado->save();

            $registro_ingreso->responsable_anterior = $registro_ingreso->responsable;
            $registro_ingreso->responsable = $nombre_responsable;
            $registro_ingreso->asignacion_manual = 1;
            $registro_ingreso->responsable_id = $responsable_id;
            $registro_ingreso->save();
            $seguimiento = new FormularioIngresoSeguimiento;
            $seguimiento->estado_ingreso_id = $registro_ingreso->estado_ingreso_id;
            $seguimiento->usuario = $registro_ingreso->responsable;
            $seguimiento->formulario_ingreso_id = $item_id;
            $seguimiento->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        } catch (\Exception $e) {
            DB::rollback();
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


    public function responsableingresos($estado)
    {
        $usuarios = FormularioIngresoResponsable::join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado)
            ->select(
                'usuario_id',
                DB::raw("CONCAT(nombres,' ',apellidos)  AS nombre")
            )
            ->get();
        return response()->json($usuarios);
    }


    public function byid($id)
    {
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_usuarios as us', 'us.id', 'usr_app_formulario_ingreso.candidato_id')
            ->leftJoin('usr_app_candidatos_c as can', 'can.usuario_id', 'us.id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'can.tip_doc_id')
            ->where('usr_app_formulario_ingreso.id', '=', $id)
            ->select(
                'usr_app_formulario_ingreso.id',
                'esti.nombre as estado_ingreso',
                'esti.id as estado_ingreso_id',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.fecha_ingreso',
                // 'usr_app_formulario_ingreso.numero_identificacion',
                // 'usr_app_formulario_ingreso.nombre_completo',
                DB::RAW("CONCAT(can.num_doc,'',usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::RAW("CONCAT(usr_app_formulario_ingreso.nombre_completo,'',primer_nombre,' ',segundo_nombre,' ',primer_apellido,' ',segundo_apellido) as nombre_completo"),
                'usr_app_formulario_ingreso.cliente_id',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'usr_app_formulario_ingreso.salario',
                'usr_app_formulario_ingreso.municipio_id',
                'mun.nombre as municipio',
                // 'usr_app_formulario_ingreso.numero_contacto',
                DB::RAW("CONCAT(can.celular,'',usr_app_formulario_ingreso.numero_contacto) as numero_contacto"),
                'usr_app_formulario_ingreso.eps',
                'usr_app_formulario_ingreso.afp_id',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.examenes',
                'usr_app_formulario_ingreso.fecha_examen',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
                'pais.id as pais_id',
                'pais.nombre as pais',
                'usr_app_formulario_ingreso.created_at as fecha_radicado',
                'tiser.nombre_servicio',
                'tiser.id as tipo_servicio_id',
                'usr_app_formulario_ingreso.numero_vacantes',
                'usr_app_formulario_ingreso.numero_contrataciones',
                'usr_app_formulario_ingreso.citacion_entrevista',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.informe_seleccion',
                // 'usr_app_formulario_ingreso.cambio_fecha',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.direccion_empresa',
                'usr_app_formulario_ingreso.direccion_laboratorio',
                'usr_app_formulario_ingreso.recomendaciones_examen',
                'usr_app_formulario_ingreso.novedad_stradata',
                'usr_app_formulario_ingreso.correo_notificacion_empresa',
                // 'usr_app_formulario_ingreso.correo_notificacion_usuario',
                DB::RAW("CONCAT(us.email,'',usr_app_formulario_ingreso.correo_notificacion_usuario) as correo_notificacion_usuario"),
                'usr_app_formulario_ingreso.novedades_examenes',
                // 'ti.des_tip as tipo_identificacion',
                'usr_app_formulario_ingreso.subsidio_transporte',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.responsable_id',
                // 'ti.cod_tip as tipo_identificacion_id',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.correo_laboratorio',
                'usr_app_formulario_ingreso.contacto_empresa',
                'usr_app_formulario_ingreso.responsable_corregir',
                'usr_app_formulario_ingreso.nc_hora_cierre',
                'usr_app_formulario_ingreso.n_servicio',
                // 'ti.des_tip'
                DB::RAW("CONCAT(ti.des_tip,'',ti2.des_tip) as tipo_identificacion"),
                DB::RAW("CONCAT(ti.cod_tip,'',ti2.cod_tip) as tipo_identificacion_id")
            )
            ->first();

        $laboratorios = RegistroIngresoLaboratorio::join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'usr_app_registro_ingreso_laboraorio.laboratorio_medico_id')
            ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
            ->where('usr_app_registro_ingreso_laboraorio.registro_ingreso_id', '=', $id)
            ->select(
                'ciulab.id',
                'ciulab.laboratorio as nombre',
                'mun.id as municipio_id',
                'mun.nombre as municipio',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
            )
            ->get();
        $result['laboratorios'] = $laboratorios;

        $archivos = FormularioIngresoArchivos::join('usr_app_archivos_formulario_ingreso as fi', 'fi.id', '=', 'usr_app_formulario_ingreso_archivos.arhivo_id')
            ->where('ingreso_id', $id)
            ->select(
                'usr_app_formulario_ingreso_archivos.arhivo_id',
                'usr_app_formulario_ingreso_archivos.ruta',
                'usr_app_formulario_ingreso_archivos.observacion',
                'fi.nombre',
                'fi.tipo_archivo'
            )
            ->orderby('usr_app_formulario_ingreso_archivos.arhivo_id', 'ASC')
            ->get();
        $result['archivos'] = $archivos;


        $seguimiento = FormularioIngresoSeguimiento::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento.estado_ingreso_id')
            ->where('usr_app_formulario_ingreso_seguimiento.formulario_ingreso_id', $id)
            ->select(
                'usr_app_formulario_ingreso_seguimiento.usuario',
                'ei.nombre as estado',
                'usr_app_formulario_ingreso_seguimiento.created_at',

            )
            ->orderby('usr_app_formulario_ingreso_seguimiento.id', 'desc')
            ->get();
        $result['seguimiento'] = $seguimiento;
        // return response()->json($result);


        $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
            ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
            ->where('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id', $id)
            ->select(
                'usr_app_formulario_ingreso_seguimiento_estado.responsable_inicial',
                'usr_app_formulario_ingreso_seguimiento_estado.responsable_final',
                'ei.nombre as estado_ingreso_inicial',
                'ef.nombre as estado_ingreso_final',
                'usr_app_formulario_ingreso_seguimiento_estado.actualiza_registro',
                'usr_app_formulario_ingreso_seguimiento_estado.created_at',


            )
            ->orderby('usr_app_formulario_ingreso_seguimiento_estado.id', 'desc')
            ->get();
        $result['seguimiento_estados'] = $seguimiento_estados;
        return response()->json($result);
    }





    public function gestioningresospdf($modulo = null, $registro_id, $id)
    {

        $formulario = $this->byid($registro_id)->getData();


        $pdf = new TCPDF();
        $pdf->SetTextColor(4, 66, 105);
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetMargins(0, 0, 0);

        $url = public_path('\/upload\/MEMBRETE.png');
        $img_file = $url;
        $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);

        $combinacion_correos = '';

        if ($formulario->correo_laboratorio != null && $id == 3) {
            $combinacion_correos =  $formulario->correo_laboratorio;
        } else {
            $combinacion_correos = $formulario->correo_notificacion_empresa;
        }

        $fecha_ingreso = $formulario->fecha_ingreso;
        $numero_identificacion = $formulario->numero_identificacion;
        $tipo_id = $formulario->tipo_identificacion;
        $nombre_completo = $formulario->nombre_completo;
        $razon_social = $formulario->razon_social;
        $cargo = $formulario->cargo;
        $salario = $formulario->salario;
        $municipio = $formulario->municipio;
        $numero_contacto = $formulario->numero_contacto;
        $otro_laboratorio = $formulario->laboratorio;
        $examenes = $formulario->examenes;

        $fecha_examenes = $formulario->fecha_examen;
        if (!empty($fecha_examenes)) {
            $timestamp = strtotime($fecha_examenes);
            $fecha_examen = date('d/m/Y', $timestamp);
        } else {
            $fecha_examen = '';
        }


        $departamento = $formulario->departamento;
        $nombre_servicio = $formulario->nombre_servicio;
        $tipo_servicio_id = $formulario->tipo_servicio_id;

        $fecha_citacion_entrevista = $formulario->citacion_entrevista;

        if (!empty($fecha_citacion_entrevista)) {
            $timestamp2 = strtotime($fecha_citacion_entrevista);
            $citacion_entrevista = date('d/m/Y, H:i', $timestamp2);
        } else {
            $citacion_entrevista = '';
        }

        $profesional = $formulario->profesional;
        $informe_seleccion = $formulario->informe_seleccion;
        $direccion_empresa = $formulario->direccion_empresa;
        $direccion_laboratorio = $formulario->direccion_laboratorio;
        $recomendaciones_examen = $formulario->recomendaciones_examen;
        $ancho_maximo = 70;
        $ancho_infor_texto = 90;
        $ancho_examenes = 105;
        $ancho_labnoratorio = 115;
        $ancho_seleccion = 97;

        if (isset($formulario->laboratorios[0])) {
            $departamento_laboratorio = $formulario->laboratorios[0]->departamento;
            $municipio_laboratorio = $formulario->laboratorios[0]->municipio;
            $laboratorio_medico = $formulario->laboratorios[0]->nombre;
        }


        if ($id == 1 || $id == 4) {

            $pdf->Ln(20);

            if ($id == 1) {
                $html = '<table cellpadding="2" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="text-align: center;">
                        <div style="font-size: 14pt; font-weight: bold; font-style: italic; font-family: Arial;">Orden de servicio:</div>
                    </td>
                </tr>
            </table>';
                $pdf->writeHTML($html, true, false, true, false, '');
            } elseif ($id == 4) {
                $html = '<table cellpadding="2" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="text-align: center;">
                        <div style="font-size: 14pt; font-weight: bold; font-style: italic; font-family: Arial;">Citación candidato:</div>
                    </td>
                </tr>
            </table>';
                $pdf->writeHTML($html, true, false, true, false, '');
            }

            if (strlen($razon_social) < 35 && strlen($direccion_empresa) < 35) {

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Dirección empresa:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $razon_social != null ? $razon_social : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(3);

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($nombre_servicio);

                $pdf->MultiCell($ancho_texto + 7, 7, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 'L');
                $pdf->Ln(1);
            } else if (strlen($direccion_empresa) < 35) {

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($razon_social);

                $pdf->MultiCell($ancho_texto + 7, 7, $razon_social != null ? $razon_social : 'Sin datos', 0, 'L');

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Dirección empresa:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(3);
            } else {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($razon_social);
                $pdf->MultiCell($ancho_texto + 7, 7, $razon_social != null ? $razon_social : 'Sin datos', 0, 'L');


                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Dirección empresa:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($direccion_empresa);
                $pdf->MultiCell($ancho_texto + 7, 7, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 'L');


                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($nombre_servicio);
                $pdf->MultiCell($ancho_texto + 7, 7, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 'L');
            }

            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Número de identificación:', 0, 0, 'L');
            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Tipo de identificación:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 11);

            $pdf->ln(2);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($numero_identificacion);
            $pdf->Cell(10, 1, $numero_identificacion != null ? $numero_identificacion : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $tipo_id != null ? $tipo_id : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(2);

            /*  
            tipo_id */

            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Apellidos y Nombres:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Número contacto:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $nombre_completo != null ? $nombre_completo : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $numero_contacto != null ? $numero_contacto : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(2);


            if (strlen($cargo) < 35) {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Salario:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $cargo != null ? $cargo : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $salario != null ? $salario : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(2);
            } else {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($cargo != null ? $cargo : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Salario:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($salario);

                $pdf->MultiCell($ancho_texto + 7, 7, $salario != null ? $salario : 'Sin datos', 0, 'L');
            }

            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Departamento de prestación de servicios:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Ciudad de prestación de servicios:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $departamento, 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $municipio, 0, 1, 'L');
            $pdf->Ln(2);

            if (isset($formulario->laboratorios[0])) {

                if (!empty($formulario->laboratorios && $otro_laboratorio == '')) {

                    $pdf->SetFont('helveticaI', 'B', 11);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Departamento ubicación laboratorio médico:', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(95, 10, 'Ciudad ubicación laboratorio médico:', 0, 1, 'L');
                    $pdf->SetFont('helveticaI', '', 11);

                    $pdf->SetX(20);
                    $pdf->Cell(10, 1, $departamento_laboratorio != '' ? $departamento_laboratorio : 'Sin datos', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(65, 1, $municipio_laboratorio != '' ? $municipio_laboratorio : 'Sin datos', 0, 1, 'L');
                    $pdf->Ln(2);

                    $pdf->SetFont('helveticaI', 'B', 11);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Laboratorio médico:', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(95, 10, 'Fecha de exámenes:', 0, 1, 'L');
                    $pdf->SetFont('helveticaI', '', 11);

                    $pdf->SetX(20);
                    $pdf->Cell(10, 1, $laboratorio_medico != null ? $laboratorio_medico : 'Sin datos', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(65, 1, $fecha_examen != null ? $fecha_examen : 'Sin datos', 0, 1, 'L');
                    $pdf->Ln(2);

                    $pdf->SetFont('helveticaI', 'B', 11);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Dirección laboratorio:', 0, 0, 'L');
                    $pdf->SetFont('helveticaI', '', 11);
                    $pdf->Ln(10);
                    $pdf->SetX(20);
                    $lineas = explode("\n", wordwrap($direccion_laboratorio != null ? $direccion_laboratorio : 'Sin datos', $ancho_maximo, "\n"));

                    foreach ($lineas as $linea) {
                        $ancho_texto = $pdf->GetStringWidth($linea);
                        $pdf->SetX(20);
                        $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    }
                }
            }

            if ($otro_laboratorio != '') {

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Laboratorio médico:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Fecha de exámenes:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $otro_laboratorio != null ? $otro_laboratorio : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $fecha_examen != null ? $fecha_examen : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(2);

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Dirección laboratorio:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($direccion_laboratorio != null ? $direccion_laboratorio : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }
            }


            if ($tipo_servicio_id == 3 || $tipo_servicio_id == 4) {
                $pdf->AddPage();
                $url = public_path('\/upload\/MEMBRETE.png');
                $img_file = $url;
                $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
                $pdf->Ln(45);
                if (strlen($examenes) < 30 && strlen($recomendaciones_examen) < 30) {
                    $pdf->SetFont('helveticaI', 'B', 11);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 1, 'L');
                    $pdf->SetFont('helveticaI', '', 11);

                    $pdf->SetX(20);
                    $pdf->Cell(10, 1, $examenes != null ? $examenes : 'Sin datos', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(65, 1, $recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', 0, 1, 'L');
                    $pdf->Ln(2);
                } else {
                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');
                    $pdf->SetFont('helveticaI', '', 10);
                    $pdf->Ln(10);
                    $pdf->SetX(20);
                    $lineas = explode("\n", wordwrap($examenes != null ? $examenes : 'Sin datos', $ancho_examenes, "\n"));

                    foreach ($lineas as $linea) {
                        $linea = mb_strtolower($linea, 'UTF-8');

                        $linea = preg_replace_callback('/(?:^|,)\s*\K\w/u', function ($match) {
                            return mb_strtoupper($match[0], 'UTF-8');
                        }, $linea);

                        $ancho_texto = $pdf->GetStringWidth($linea);
                        $pdf->SetX(20);
                        $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    }

                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 0, 'L');
                    $pdf->SetFont('helveticaI', '', 10);
                    $pdf->Ln(10);
                    $pdf->SetX(20);
                    $lineas = explode("\n", wordwrap($recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', $ancho_examenes, "\n"));

                    foreach ($lineas as $linea) {
                        $linea = mb_strtolower($linea, 'UTF-8');

                        $linea = preg_replace_callback('/(?:^|,)\s*\K\w/u', function ($match) {
                            return mb_strtoupper($match[0], 'UTF-8');
                        }, $linea);

                        $ancho_texto = $pdf->GetStringWidth($linea);
                        $pdf->SetX(20);
                        $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    }
                }
            } else {
                if (strlen($examenes) < 30 && strlen($recomendaciones_examen) < 30 && strlen($direccion_empresa) < 30) {
                    $pdf->SetFont('helveticaI', 'B', 11);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 1, 'L');
                    $pdf->SetFont('helveticaI', '', 11);

                    $pdf->SetX(20);
                    $pdf->Cell(10, 1, $examenes != null ? $examenes : 'Sin datos', 0, 0, 'L');

                    $pdf->SetX(120);
                    $pdf->Cell(65, 1, $recomendaciones_examen != null ? $recomendaciones_examen : 'Sin datos', 0, 1, 'L');
                    $pdf->Ln(2);
                } else {
                    $pdf->AddPage();
                    $url = public_path('\/upload\/MEMBRETE.png');
                    $img_file = $url;
                    $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
                    $pdf->Ln(45);

                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');
                    $pdf->SetFont('helveticaI', '', 10);
                    $pdf->Ln(20);
                    $pdf->SetX(10);
                    $lineas = explode("\n", wordwrap($examenes != null ? $examenes : 'Sin datos', $ancho_examenes, "\n"));

                    foreach ($lineas as $linea) {
                        $linea = mb_strtolower($linea, 'UTF-8');

                        $linea = preg_replace_callback('/(?:^|,)\s*\K\w/u', function ($match) {
                            return mb_strtoupper($match[0], 'UTF-8');
                        }, $linea);

                        $ancho_texto = $pdf->GetStringWidth($linea);
                        $pdf->SetX(20);
                        $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    }

                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(20);
                    $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 0, 'L');
                    $pdf->SetFont('helveticaI', '', 10);
                    $pdf->Ln(10);
                    $pdf->SetX(20);
                    $lineas = explode("\n", wordwrap($recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', $ancho_examenes, "\n"));

                    foreach ($lineas as $linea) {
                        $linea = mb_strtolower($linea, 'UTF-8');

                        $linea = preg_replace_callback('/(?:^|,)\s*\K\w/u', function ($match) {
                            return mb_strtoupper($match[0], 'UTF-8');
                        }, $linea);

                        $ancho_texto = $pdf->GetStringWidth($linea);
                        $pdf->SetX(20);
                        $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    }
                }
            }
            if ($id == 4) {
                $teeexto = '"Solicitamos nos proporcione una copia de su documento de identidad y un certificado bancario para completar el proceso de contratación dentro del plazo establecido. Estos documentos son necesarios para proceder con la contratación en la fecha prevista. En caso de necesitar una carta de apertura de cuenta, le recomendamos ponerse en contacto con el facilitador de servicio asignado para asistirle en este proceso."';
                $pdf->Ln(10);
                $pdf->SetX(10);
                $lineas = explode("\n", wordwrap($teeexto != null ? $teeexto : 'Sin datos', $ancho_infor_texto, "\n"));

                foreach ($lineas as $linea) {
                    $pdf->SetFont('helveticaI', 'B', 11);
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $espacio_adicional = 20; // Puedes ajustar este valor según lo necesites
                    $ancho_celda = $ancho_texto + 7 + $espacio_adicional;
                    $posicion_x = (210 - $ancho_celda) / 2; // 210 es el ancho predeterminado de una página A4
                    $pdf->SetX($posicion_x);
                    $pdf->MultiCell($ancho_celda, 7, $linea, 0, 'C'); // Cambiado 'L' a 'C' para centrar
                    $pdf->SetFont('helveticaI', '', 11);
                }
            }
        } else if ($id == 2) {

            $pdf->Ln(20);

            $html = '<table cellpadding="2" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="text-align: center;">
                    <div style="font-size: 14pt; font-weight: bold; font-style: italic; font-family: Arial;">Informe de seleccion:</div>
                </td>
            </tr>
        </table>';
            $pdf->writeHTML($html, true, false, true, false, '');

            if (strlen($razon_social) < 35 && strlen($direccion_empresa) < 35) {

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Dirección empresa:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $razon_social != null ? $razon_social : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(3);


                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Citación entrevista:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $citacion_entrevista != null ? $citacion_entrevista : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(3);
            } else {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($razon_social);
                $pdf->MultiCell($ancho_texto + 7, 7, $razon_social != null ? $razon_social : 'Sin datos', 0, 'L');


                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Dirección empresa:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($direccion_empresa);
                $pdf->MultiCell($ancho_texto + 30, 7, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 'L');


                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Citación entrevista:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $citacion_entrevista != null ? $citacion_entrevista : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(3);
            }

            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Número de identificación:', 0, 0, 'L');
            $pdf->SetFont('helveticaI', '', 11);
            $pdf->SetX(120);
            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->Cell(95, 10, 'Tipo de identificación:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 11);
            $pdf->ln(2);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($numero_identificacion);
            $pdf->Cell(10, 1, $numero_identificacion != null ? $numero_identificacion : 'Sin datos', 0, 0, 'L');
            $pdf->SetX(120);
            $pdf->Cell(65, 1, $tipo_id != null ? $tipo_id : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(2);
            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Apellidos y nombres:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Número contacto:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $nombre_completo != null ? $nombre_completo : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $numero_contacto != null ? $numero_contacto : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(2);


            if (strlen($cargo) < 35) {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Salario:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $cargo != null ? $cargo : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $salario != null ? $salario : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(2);
            } else {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($cargo != null ? $cargo : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Salario:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX(20);
                $ancho_texto = $pdf->GetStringWidth($salario);

                $pdf->MultiCell($ancho_texto + 7, 7, $salario != null ? $salario : 'Sin datos', 0, 'L');
            }

            $pdf->SetFont('helveticaI', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Departamento de prestación de servicios:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Ciudad de prestación de servicios:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $departamento, 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $municipio, 0, 1, 'L');
            $pdf->Ln(2);

            $cantidad_saltos_linea = substr_count($informe_seleccion, "\n");
            $longitud_informe_ajustada = mb_strlen($informe_seleccion) + ($cantidad_saltos_linea * 90);

            // return $longitud_informe_ajustada;

            // Verificar si la longitud ajustada cumple con la condición
            if ($longitud_informe_ajustada <= 600) {
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Informe selección:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);

                $lineas = explode("\n", wordwrap($informe_seleccion != null ? $informe_seleccion : 'Sin datos', $ancho_seleccion, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Profesional:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX(20);

                $pdf->MultiCell($ancho_maximo + 7, 7, $profesional != null ? $profesional : 'Sin datos', 0, 'L');
            } else {

                $max_caracteres = 2600;
                $margen_izquierdo = 15;
                $margen_derecho = 15;

                $pdf->SetMargins($margen_izquierdo, 0, $margen_derecho);
                $pdf->SetAutoPageBreak(true, 0);

                $pdf->AddPage();

                $url = public_path('\/upload\/MEMBRETE.png');
                $img_file = $url;
                $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
                $pdf->Ln(40);
                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX($margen_izquierdo);
                $pdf->Cell(95, 10, 'Informe selección:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);
                $pdf->Ln(10);

                $caracteres_restantes = $max_caracteres;

                $lineas_restantes = ceil($caracteres_restantes / $ancho_seleccion);

                $lineas2 = explode("\n", wordwrap($informe_seleccion, $ancho_seleccion, "\n"));
                foreach ($lineas2 as $linea) {
                    $pdf->SetX($margen_izquierdo);
                    $ancho_texto = $pdf->GetStringWidth($linea);

                    if ($lineas_restantes <= 0) {
                        $pdf->AddPage();
                        $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
                        $pdf->Ln(30);
                        $pdf->SetFont('helveticaI', 'B', 11);
                        $pdf->SetX($margen_izquierdo);
                        $pdf->Cell(95, 10, '', 0, 0, 'L');
                        $pdf->SetFont('helveticaI', '', 11);
                        $pdf->Ln(10);
                        $caracteres_restantes = $max_caracteres;
                        $lineas_restantes = ceil($caracteres_restantes / $ancho_seleccion);
                    }

                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    $caracteres_restantes -= strlen($linea);
                    $lineas_restantes--;
                }

                $pdf->SetFont('helveticaI', 'B', 11);
                $pdf->SetX($margen_izquierdo);
                $pdf->Cell(95, 10, 'Profesional:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 11);

                $pdf->Ln(10);
                $pdf->SetX($margen_izquierdo);
                $ancho_texto = $pdf->GetStringWidth($profesional);

                $pdf->MultiCell($ancho_texto + 7, 7, $profesional != null ? $profesional : 'Sin datos', 0, 'L');
            }
        } else if ($id == 3) {

            $pdf->Ln(20);

            $html = '<table cellpadding="2" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="text-align: center;">
                    <div style="font-size: 13pt; font-weight: bold; font-style: italic; font-family: Arial;">Orden laboratorio:</div>
                </td>
            </tr>
                    </table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->SetFont('helveticaI', 'B', 10);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Nombre de la Empresa:', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(95, 10, 'Empresa en Misión:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 10);

            $pdf->SetX(10);
            $pdf->Cell(65, 1, 'SAITEMP SA', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(10, 1, $razon_social != null ? $razon_social : 'Sin datos', 0, 1, 'L');

            $pdf->SetFont('helveticaI', 'B', 10);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Apellidos y nombres:', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(95, 10, '', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 10);

            $pdf->SetX(10);
            $pdf->Cell(10, 1, $nombre_completo != null ? $nombre_completo : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(65, 1, "", 0, 1, 'L');
            $pdf->Ln(2);

            $pdf->SetFont('helveticaI', 'B', 10);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Número de identificación', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(95, 10, 'Tipo de identificación', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 10);


            $pdf->SetFont('helveticaI', '', 10);
            $pdf->SetX(10);
            $pdf->Cell(10, 1, $numero_identificacion != null ? $numero_identificacion : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(65, 1, $tipo_id != null ? $tipo_id : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(2);

            if (strlen($cargo) < 35) {
                $pdf->SetFont('helveticaI', 'B', 10);
                $pdf->SetX(10);
                $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 10);

                $pdf->Ln(10);
                $pdf->SetX(10);
                $ancho_texto = $pdf->GetStringWidth($cargo);

                $pdf->MultiCell($ancho_texto + 7, 7, $cargo != null ? $cargo : 'Sin datos', 0, 'L');
            } else {
                $pdf->SetFont('helveticaI', 'B', 10);
                $pdf->SetX(10);
                $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $lineas = explode("\n", wordwrap($cargo != null ? $cargo : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(10);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }
            }

            $pdf->SetFont('helveticaI', 'B', 10);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Departamento de prestación de servicios:', 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(95, 10, 'Ciudad de prestación de servicios:', 0, 1, 'L');
            $pdf->SetFont('helveticaI', '', 10);

            $pdf->SetX(10);
            $pdf->Cell(10, 1, $departamento, 0, 0, 'L');

            $pdf->SetX(100);
            $pdf->Cell(65, 1, $municipio, 0, 1, 'L');
            $pdf->Ln(2);

            if (isset($formulario->laboratorios[0])) {

                if (!empty($formulario->laboratorios && $otro_laboratorio == '')) {

                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(10);
                    $pdf->Cell(95, 10, 'Departamento ubicación laboratorio médico:', 0, 0, 'L');

                    $pdf->SetX(100);
                    $pdf->Cell(95, 10, 'Ciudad ubicación laboratorio médico:', 0, 1, 'L');
                    $pdf->SetFont('helveticaI', '', 10);

                    $pdf->SetX(10);
                    $pdf->Cell(10, 1, $departamento_laboratorio != '' ? $departamento_laboratorio : 'Sin datos', 0, 0, 'L');

                    $pdf->SetX(100);
                    $pdf->Cell(65, 1, $municipio_laboratorio != '' ? $municipio_laboratorio : 'Sin datos', 0, 1, 'L');
                    $pdf->Ln(2);

                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(10);
                    $pdf->Cell(95, 10, 'Laboratorio médico:', 0, 0, 'L');

                    $pdf->SetX(100);
                    $pdf->Cell(95, 10, 'Fecha de exámenes:', 0, 1, 'L');
                    $pdf->SetFont('helveticaI', '', 10);

                    $pdf->SetX(10);
                    $pdf->Cell(10, 1, $laboratorio_medico != null ? $laboratorio_medico : 'Sin datos', 0, 0, 'L');

                    $pdf->SetX(100);
                    $pdf->Cell(65, 1, $fecha_examen != null ? $fecha_examen : 'Sin datos', 0, 1, 'L');
                    $pdf->Ln(2);

                    $pdf->SetFont('helveticaI', 'B', 10);
                    $pdf->SetX(10);
                    $pdf->Cell(95, 10, 'Dirección laboratorio:', 0, 0, 'L');
                    $pdf->SetFont('helveticaI', '', 10);
                    $pdf->Ln(10);
                    $pdf->SetX(10);
                    $lineas = explode("\n", wordwrap($direccion_laboratorio != null ? $direccion_laboratorio : 'Sin datos', $ancho_maximo, "\n"));

                    foreach ($lineas as $linea) {
                        $ancho_texto = $pdf->GetStringWidth($linea);
                        $pdf->SetX(10);
                        $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                    }
                }
            }

            if ($otro_laboratorio != '') {

                $pdf->SetFont('helveticaI', 'B', 10);
                $pdf->SetX(10);
                $pdf->Cell(95, 10, 'Laboratorio médico:', 0, 0, 'L');

                $pdf->SetX(100);
                $pdf->Cell(95, 10, 'Fecha de exámenes:', 0, 1, 'L');
                $pdf->SetFont('helveticaI', '', 10);

                $pdf->SetX(10);
                $pdf->Cell(10, 1, $otro_laboratorio != null ? $otro_laboratorio : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(100);
                $pdf->Cell(65, 1, $fecha_examen != null ? $fecha_examen : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(2);

                $pdf->SetFont('helveticaI', 'B', 10);
                $pdf->SetX(10);
                $pdf->Cell(95, 10, 'Dirección laboratorio:', 0, 0, 'L');
                $pdf->SetFont('helveticaI', '', 10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $lineas = explode("\n", wordwrap($direccion_laboratorio != null ? $direccion_laboratorio : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(10);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }
            }
            $pdf->SetFont('helveticaI', 'B', 10);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');
            $pdf->SetFont('helveticaI', '', 10);
            $pdf->Ln(10);
            $pdf->SetX(10);
            $lineas = explode("\n", wordwrap($examenes != null ? $examenes : 'Sin datos', $ancho_labnoratorio, "\n"));

            foreach ($lineas as $linea) {
                $linea = mb_strtolower($linea, 'UTF-8');

                $linea = preg_replace_callback('/(?:^|,)\s*\K\w/u', function ($match) {
                    return mb_strtoupper($match[0], 'UTF-8');
                }, $linea);

                $ancho_texto = $pdf->GetStringWidth($linea);
                $pdf->SetX(10);
                $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
            }
            $pdf->SetFont('helveticaI', 'B', 10);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 0, 'L');
            $pdf->SetFont('helveticaI', '', 10);
            $pdf->Ln(10);
            $pdf->SetX(10);
            $lineas = explode("\n", wordwrap($recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', $ancho_labnoratorio, "\n"));

            foreach ($lineas as $linea) {
                $linea = mb_strtolower($linea, 'UTF-8');

                $linea = preg_replace_callback('/(?:^|,)\s*\K\w/u', function ($match) {
                    return mb_strtoupper($match[0], 'UTF-8');
                }, $linea);

                $ancho_texto = $pdf->GetStringWidth($linea);
                $pdf->SetX(10);
                $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
            }
        }

        if ($modulo != 'null') {
            $pdfPath = storage_path('app/temp.pdf');
            $pdf->Output($pdfPath, 'F');
        } else {
            $nombre_archivo = "";
            if ($id == 1) {
                $nombre_archivo = "Orden_servicio";
            } else if ($id == 2) {
                $nombre_archivo = "Informe_seleccion";
            } else if ($id == 3) {
                $nombre_archivo = "Orden_laboratorio";
            } else if ($id == 4) {
                $nombre_archivo = "Citacion_candidato";
            }
            $pdf->Output($nombre_archivo . '.pdf', 'I');
        }

        $body = '';
        $subject = '';
        $nomb_membrete = '';
        if ($id == 3 && $formulario->correo_laboratorio != null) {
            $body = "Cordial saludo, esperamos se encuentren bien.\n\nAutorizamos exámenes médicos en solicitud de servicio adjunta, cualquier información adicional que se requiera, comunicarse a la línea Servisai de Saitemp S.A. marcando al (604) 4485744, donde con gusto uno de nuestros facilitadores atenderá su llamada.\n\nSimplificando conexiones, facilitando experiencias.";
            $body = nl2br($body);
            $subject = 'AUTORIZACIÓN DE EXÁMENES MEDICOS DE ' . $nombre_completo . ' IDENTIFICADO/A CON NUMERO DE DOCUMENO ' . $numero_identificacion;
            $nomb_membrete = 'Autorizacion';
        } elseif ($id == 1 && $formulario->correo_notificacion_empresa != null) {
            $body = "Cordial saludo, esperamos se encuentren muy bien.\n\n Informamos que su solicitud de servicio ha sido recibida satisfactoriamente, Cualquier información adicional podrá ser atendida en la línea Servisai de Saitemp S.A. marcando  al (604) 4485744, con gusto uno de nuestros facilitadores atenderá su llamada.\n\n simplificando conexiones, facilitando experiencias.";
            $body = nl2br($body);
            $subject = 'Confirmación de servicio recibido .';
            $nomb_membrete = 'Confirmacion';
        } elseif ($id == 2 && $formulario->correo_notificacion_empresa != null) {
            $body = "Cordial saludo, esperamos se encuentren muy bien.\n\n
            De acuerdo a su solicitud me permito enviar el formato para el cargo " . $cargo . "\n\n
            Quedo atenta a sus comentarios; es importante recordar que la retroalimentación debe hacerse de manera escrita a través de correo electrónico y dentro de los tres (3) días hábiles siguientes al envío del formato.\n\n
            SAITEMP S.A. no se hace responsable en caso de que el candidato acuerde o consienta con la empresa usuaria hacer ensayos  en la ejecución real de la labor misional sin que EL CANDIDATO cuente con contrato suscrito con SAITEMP S.A. y con  las respectivas afiliaciones a la seguridad social, en caso de accidente o reclamación como consecuencia de una prueba técnica de conocimiento que implique ejecución  de la labor misional dentro o fuera de las instalaciones de la “Empresa usuaria”, será exclusiva responsabilidad del Candidato las consecuencias  que tal convenio con la empresa usuaria llegaran a presentar.\n\n     
            Agradezco ponerme en copia en caso de enviar autorización de ingreso
            Cordialmente, " . ' ' . $profesional . '.';
            $body = nl2br($body);
            // return $body;
            $subject = 'Comparto Hojas de vida para el Cargo ' . $cargo . '.';
            $nomb_membrete = 'Informe de seleccion';
        }



        $correo = [];
        $correo['subject'] =  $subject;
        $correo['body'] = $body;
        $correo['formulario_ingreso'] = $pdfPath;
        $correo['to'] = $combinacion_correos;
        $correo['cc'] = '';
        $correo['cco'] = '';
        $correo['modulo'] = $modulo;
        $correo['registro_id'] = $registro_id;
        $correo['nom_membrete'] = $nomb_membrete;

        $EnvioCorreoController = new EnvioCorreoController();
        $request = Request::createFromBase(new Request($correo));
        $result = $EnvioCorreoController->sendEmail($request);
        return $result;
    }


    public function filtroFechaIngreso(Request $request, $cantidad = null)
    {
        $user = auth()->user();
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->leftJoin('usr_app_usuarios as us', 'us.id', 'usr_app_formulario_ingreso.candidato_id')
            ->leftJoin('usr_app_candidatos_c as can', 'can.usuario_id', 'us.id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.n_servicio',
                'tiser.nombre_servicio',
                'usr_app_formulario_ingreso.updated_at',
                'usr_app_formulario_ingreso.created_at',
                DB::RAW("CONCAT(can.num_doc,'',usr_app_formulario_ingreso.numero_identificacion) as numero_documento"),
                DB::RAW("CONCAT(primer_nombre,' ',segundo_nombre,' ',primer_apellido,' ',segundo_apellido,' ',usr_app_formulario_ingreso.nombre_completo) as nombre_completo"),
                'usr_app_formulario_ingreso.cargo',
                'cli.razon_social',
                'mun.nombre as ciudad',
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                DB::raw("FORMAT(CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE), 'dd/MM/yyyy') as fecha_ingreso"),
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',
                'cli.contratacion_hora_confirmacion as hora_confirmacion',
                'usr_app_formulario_ingreso.responsable_id',
            );

        if ($request['ordenar_prioridad'] == true) {
            $result->whereNotNull('usr_app_formulario_ingreso.fecha_ingreso');
            $result->orderByRaw("CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE) DESC")
                ->orderBy('cli.contratacion_hora_confirmacion', 'ASC');
        }
        if ($request['filtro_mios'] == true) {
            $result->where('usr_app_formulario_ingreso.responsable_id', '=', $user->id);
        } else if ($request['ordenar_prioridad'] == false) {
            $result->orderby('usr_app_formulario_ingreso.id', 'DESC');
        }

        $registros = $result->paginate($cantidad);

        foreach ($result as $item) {
            $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;
        }
        return response()->json($registros);
    }

    public function filtro($cadena, $cantidad = null)
    {
        if ($cantidad == null) {
            $cantidad = 15;
        }
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];

        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];
        $permisos = $this->validaPermiso();
        $query = FormularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->leftJoin('usr_app_usuarios as us', 'us.id', 'usr_app_formulario_ingreso.candidato_id')
            ->leftJoin('usr_app_candidatos_c as can', 'can.usuario_id', 'us.id')
            ->when(!in_array('42', $permisos), function ($query) {
                return $query->where(function ($query) {
                    $query->whereNotIn('cli.nit', ['811025401', '900032514'])
                        ->orWhereNull('cli.nit');
                });
            })
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.n_servicio',
                'tiser.nombre_servicio',
                'usr_app_formulario_ingreso.updated_at',
                'usr_app_formulario_ingreso.created_at',
                DB::RAW("CONCAT(can.num_doc,'',usr_app_formulario_ingreso.numero_identificacion) as numero_documento"),
                DB::RAW("CONCAT(primer_nombre,' ',segundo_nombre,' ',primer_apellido,' ',segundo_apellido,' ',usr_app_formulario_ingreso.nombre_completo) as nombre_completo"),
                'usr_app_formulario_ingreso.cargo',
                'cli.razon_social',
                'mun.nombre as ciudad',
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                DB::raw("FORMAT(CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE), 'dd/MM/yyyy') as fecha_ingreso"),
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                // 'usr_app_formulario_ingreso.citacion_entrevista',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',

                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                // 'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',

            )
            ->orderBy('usr_app_formulario_ingreso.created_at', 'DESC');
        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];

            $prefijoCampo = '';
            if ($campoActual === 'ciudad') {
                $prefijoCampo = 'mun.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'estado_ingreso') {
                $prefijoCampo = 'est.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'razon_social') {
                $prefijoCampo = 'cli.';
            } elseif ($campoActual === 'nombre_servicio') {
                $prefijoCampo = 'tiser.';
            } elseif ($campoActual === 'otro_laboratorio') {
                $prefijoCampo = 'usr_app_formulario_ingreso.';
                $campoActual = 'laboratorio';
            } elseif ($campoActual === 'laboratorio') {
                $prefijoCampo = 'ciulab.';
                $campoActual = 'laboratorio';
            } else {
                $prefijoCampo = 'usr_app_formulario_ingreso.';
            }

            switch ($operadorActual) {
                case 'Menor que':
                    $query->where($prefijoCampo . $campoActual, '<', $valorCompararActual);
                    break;
                case 'Mayor que':
                    $query->where($prefijoCampo . $campoActual, '>', $valorCompararActual);
                    break;
                case 'Menor o igual que':
                    $query->where($prefijoCampo . $campoActual, '<=', $valorCompararActual);
                    break;
                case 'Mayor o igual que':
                    $query->where($prefijoCampo . $campoActual, '>=', $valorCompararActual);
                    break;
                case 'Igual a número':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Entre':
                    $valorComparar2Actual = $valor_comparar2[$i];
                    $query->whereDate($prefijoCampo . $campoActual, '>=', $valorCompararActual);
                    $query->whereDate($prefijoCampo . $campoActual, '<=', $valorComparar2Actual);
                    break;
                case 'Igual a':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Igual a fecha':
                    $query->whereDate($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Contiene':
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
            }
        }

        // Al final, ejecutar la consulta y obtener los resultados
        $resultados = $query->paginate($cantidad); // paginamos los resultados

        foreach ($resultados as $item) {
            $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;
        }

        return $resultados;
    }

    public function buscardocumentoformularioi($cedula)
    {

        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_usuarios as us', 'us.id', 'usr_app_formulario_ingreso.candidato_id')
            ->leftJoin('usr_app_candidatos_c as can', 'can.usuario_id', 'us.id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'can.tip_doc_id')
            ->where('usr_app_formulario_ingreso.numero_identificacion', '=', $cedula)
            ->select(
                'usr_app_formulario_ingreso.id',
                'esti.nombre as estado_ingreso',
                'esti.id as estado_ingreso_id',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.fecha_ingreso',
                // 'usr_app_formulario_ingreso.numero_identificacion',
                // 'usr_app_formulario_ingreso.nombre_completo',
                DB::RAW("CONCAT(can.num_doc,'',usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::RAW("CONCAT(usr_app_formulario_ingreso.nombre_completo,'',primer_nombre,' ',segundo_nombre,' ',primer_apellido,' ',segundo_apellido) as nombre_completo"),
                'usr_app_formulario_ingreso.cliente_id',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'usr_app_formulario_ingreso.salario',
                'usr_app_formulario_ingreso.municipio_id',
                'mun.nombre as municipio',
                // 'usr_app_formulario_ingreso.numero_contacto',
                DB::RAW("CONCAT(can.celular,'',usr_app_formulario_ingreso.numero_contacto) as numero_contacto"),
                'usr_app_formulario_ingreso.eps',
                'usr_app_formulario_ingreso.afp_id',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.examenes',
                'usr_app_formulario_ingreso.fecha_examen',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
                'pais.id as pais_id',
                'pais.nombre as pais',
                'usr_app_formulario_ingreso.created_at as fecha_radicado',
                'tiser.nombre_servicio',
                'tiser.id as tipo_servicio_id',
                'usr_app_formulario_ingreso.numero_vacantes',
                'usr_app_formulario_ingreso.numero_contrataciones',
                'usr_app_formulario_ingreso.citacion_entrevista',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.informe_seleccion',
                // 'usr_app_formulario_ingreso.cambio_fecha',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.direccion_empresa',
                'usr_app_formulario_ingreso.direccion_laboratorio',
                'usr_app_formulario_ingreso.recomendaciones_examen',
                'usr_app_formulario_ingreso.novedad_stradata',
                'usr_app_formulario_ingreso.correo_notificacion_empresa',
                // 'usr_app_formulario_ingreso.correo_notificacion_usuario',
                DB::RAW("CONCAT(us.email,'',usr_app_formulario_ingreso.correo_notificacion_usuario) as correo_notificacion_usuario"),
                'usr_app_formulario_ingreso.novedades_examenes',
                // 'ti.des_tip as tipo_identificacion',
                'usr_app_formulario_ingreso.subsidio_transporte',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.responsable_id',
                // 'ti.cod_tip as tipo_identificacion_id',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.correo_laboratorio',
                'usr_app_formulario_ingreso.contacto_empresa',
                'usr_app_formulario_ingreso.responsable_corregir',
                'usr_app_formulario_ingreso.nc_hora_cierre',
                'usr_app_formulario_ingreso.n_servicio',
                // 'ti.des_tip'
                DB::RAW("CONCAT(ti.des_tip,'',ti2.des_tip) as tipo_identificacion"),
                DB::RAW("CONCAT(ti.cod_tip,'',ti2.cod_tip) as tipo_identificacion_id")
            )
            ->first();

        $laboratorios = RegistroIngresoLaboratorio::join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'usr_app_registro_ingreso_laboraorio.laboratorio_medico_id')
            ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
            ->where('usr_app_registro_ingreso_laboraorio.registro_ingreso_id', '=',  $result->id)
            ->select(
                'ciulab.id',
                'ciulab.laboratorio as nombre',
                'mun.id as municipio_id',
                'mun.nombre as municipio',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
            )
            ->get();
        $result['laboratorios'] = $laboratorios;

        $archivos = FormularioIngresoArchivos::join('usr_app_archivos_formulario_ingreso as fi', 'fi.id', '=', 'usr_app_formulario_ingreso_archivos.arhivo_id')
            ->where('ingreso_id',  $result->id)
            ->select(
                'usr_app_formulario_ingreso_archivos.arhivo_id',
                'usr_app_formulario_ingreso_archivos.ruta',
                'usr_app_formulario_ingreso_archivos.observacion',
                'fi.nombre',
                'fi.tipo_archivo'
            )
            ->get();
        $result['archivos'] = $archivos;


        $seguimiento = FormularioIngresoSeguimiento::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento.estado_ingreso_id')
            ->where('usr_app_formulario_ingreso_seguimiento.formulario_ingreso_id', $result->id)
            ->select(
                'usr_app_formulario_ingreso_seguimiento.usuario',
                'ei.nombre as estado',
                'usr_app_formulario_ingreso_seguimiento.created_at',
            )
            ->orderby('usr_app_formulario_ingreso_seguimiento.id', 'desc')
            ->get();
        $result['seguimiento'] = $seguimiento;

        $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
            ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
            ->where('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id', $result->id)
            ->select(
                'usr_app_formulario_ingreso_seguimiento_estado.responsable_inicial',
                'usr_app_formulario_ingreso_seguimiento_estado.responsable_final',
                'ei.nombre as estado_ingreso_inicial',
                'ef.nombre as estado_ingreso_final',
                'usr_app_formulario_ingreso_seguimiento_estado.actualiza_registro',
                'usr_app_formulario_ingreso_seguimiento_estado.created_at',


            )
            ->orderby('usr_app_formulario_ingreso_seguimiento_estado.id', 'desc')
            ->get();
        $result['seguimiento_estados'] = $seguimiento_estados;
        return response()->json($result);
    }

    public function buscardocumentolistai($cedula)
    {
        $result = FormularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->where('usr_app_formulario_ingreso.numero_identificacion', 'like', '%' . $cedula . '%')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.n_servicio',
                'tiser.nombre_servicio',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'usr_app_formulario_ingreso.cargo',
                'cli.razon_social',
                'mun.nombre as ciudad',
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                DB::raw("FORMAT(CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE), 'dd/MM/yyyy') as fecha_ingreso"),
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                // 'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',

            )
            ->orderBy('usr_app_formulario_ingreso.created_at', 'DESC')
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
        $replica = $request->replica;
        if ($replica == "") {
            $replica = 1;
        }
        DB::beginTransaction();
        $user = auth()->user();
        $ids = [];
        $responsable_actual =  $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
        for ($i = 0; $i < $replica; $i++) {
            try {
                $result = new formularioGestionIngreso;
                $result->fecha_ingreso = $request->fecha_ingreo;
                $result->numero_identificacion = $request->numero_identificacion;
                $result->nombre_completo = $request->nombre_completo;
                $result->cliente_id = $request->empresa_cliente_id;
                $result->cargo = $request->cargo;
                $result->salario = $request->salario;
                $result->municipio_id = $request->municipio_id;
                $result->numero_contacto = $request->numero_contacto;
                $result->eps = $request->eps;
                $result->afp_id = $request->afp_id;
                $result->estradata = $request->consulta_stradata;
                $result->novedades = $request->novedades;
                $result->laboratorio = $request->laboratorio;
                $result->examenes = $request->examenes;
                $result->afectacion_servicio = $request->afectacion_servicio;
                if ($request->fecha_examen != null) {
                    $result->fecha_examen = $request->fecha_examen;
                }
                if ($request->estado_id == '') {
                    $result->estado_ingreso_id = 1;
                } else {
                    $result->estado_ingreso_id = $request->estado_id;
                }
                $result->responsable = $user->nombres . ' ' . $user->apellidos;
                $result->tipo_servicio_id = $request->tipo_servicio_id;
                $result->numero_vacantes = $request->numero_vacantes;
                $result->numero_contrataciones = $request->numero_contrataciones;
                if ($request->citacion_entrevista != null) {
                    $result->citacion_entrevista = Carbon::createFromFormat('Y-m-d\TH:i', $request->citacion_entrevista)->format('Y-m-d H:i:s');
                }
                $result->profesional = $request->profesional;
                $result->informe_seleccion = $request->informe_seleccion;
                $result->responsable = $request->consulta_encargado;
                $result->novedad_stradata = $request->novedades_stradata;
                $result->correo_notificacion_usuario = $request->correo_candidato;
                $result->correo_notificacion_empresa = $request->correo_empresa;
                $result->direccion_empresa = $request->direccion_empresa;
                $result->direccion_laboratorio = $request->direccion_laboratorio;
                $result->recomendaciones_examen = $request->recomendaciones_examen;
                $result->novedades_examenes = $request->novedades_examenes;
                $result->subsidio_transporte = $request->consulta_subsidio;
                $result->estado_vacante = $request->consulta_vacante;
                $result->tipo_documento_id = $request->tipo_identificacion;
                $result->observacion_estado = $request->consulta_observacion_estado;
                $result->correo_laboratorio = $request->correo_laboratorio;
                $result->contacto_empresa = $request->contacto_empresa;
                $result->responsable_id = $request->encargado_id;
                $result->responsable_corregir = $request->consulta_encargado_corregir;
                if ($request->variableX == 1) {
                    $result->nc_hora_cierre = 'Servicio no conforme';
                }
                if ($request->n_servicio != null) {
                    $result->n_servicio = $request->n_servicio;
                }
                $result->save();

                $laboratorio = new RegistroIngresoLaboratorio;
                $laboratorio->registro_ingreso_id  = $result->id;
                $laboratorio->laboratorio_medico_id = $request->laboratorio_medico_id;
                $laboratorio->save();

                $seguimiento = new FormularioIngresoSeguimiento;
                $seguimiento->estado_ingreso_id = $request->estado_id;
                $seguimiento->usuario = $user->nombres . ' ' . $user->apellidos;
                $seguimiento->formulario_ingreso_id = $result->id;
                $seguimiento->save();

                array_push($ids, $result->id);

                if ($result->responsable == null) {
                    $this->actualizaestadoingreso($result->id, $result->estado_ingreso_id, $result->responsable_id, $responsable_actual);
                }
                if ($request->consulta_encargado != null) {
                    $this->eventoSocket($request->encargado_id);
                }
            } catch (\Exception $e) {
                // Revertir la transacción si se produce alguna excepción
                DB::rollback();
                // return $e;
                return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor intente nuevamente']);
            }
        }

        DB::commit();
        return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
    }


    public function formularioingresoservicio(Request $request)
    {
        set_time_limit(0);
        $candidatos = CandidatoServicioModel::
            where('servicio_id', '=', $request->servicio_id)
            ->select(
                'usr_app_candadato_servicio.usuario_id',
                'usr_app_candadato_servicio.en_proceso',
            )->get();
        $tipo_servicio = $request->tipo_servicio_id;
        if ($tipo_servicio == 2) {
            $replica = $candidatos->count();
        } else if ($tipo_servicio == 3 ||  $tipo_servicio == 4) {
            $replica = $request->replica;
            if ($replica == "") {
                $replica = 1;
            }
        }

        DB::beginTransaction();
        $user = auth()->user();
        $ids = [];
        $responsable_actual =  $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
        for ($i = 0; $i < $replica; $i++) {
            try {
                $result = new formularioGestionIngreso;
                $result->fecha_ingreso = $request->fecha_ingreo;
                $result->cliente_id = $request->empresa_cliente_id;
                $result->cargo = $request->cargo;
                $result->salario = $request->salario;
                $result->municipio_id = $request->municipio_id;
                $result->eps = $request->eps;
                $result->afp_id = $request->afp_id;
                $result->estradata = $request->consulta_stradata;
                $result->novedades = $request->novedades;
                $result->laboratorio = $request->laboratorio;
                $result->examenes = $request->examenes;
                $result->afectacion_servicio = $request->afectacion_servicio;
                if ($request->fecha_examen != null) {
                    $result->fecha_examen = $request->fecha_examen;
                }
                if ($request->estado_id == '') {
                    $result->estado_ingreso_id = 1;
                } else {
                    $result->estado_ingreso_id = $request->estado_id;
                }
                $result->responsable = $user->nombres . ' ' . $user->apellidos;
                $result->tipo_servicio_id = $request->tipo_servicio_id;
                $result->numero_vacantes = $request->numero_vacantes;
                $result->numero_contrataciones = $request->numero_contrataciones;
                if ($request->citacion_entrevista != null) {
                    $result->citacion_entrevista = Carbon::createFromFormat('Y-m-d\TH:i', $request->citacion_entrevista)->format('Y-m-d H:i:s');
                }
                $result->profesional = $request->profesional;
                $result->informe_seleccion = $request->informe_seleccion;
                $result->responsable = $request->consulta_encargado;
                $result->novedad_stradata = $request->novedades_stradata;
                $result->correo_notificacion_empresa = $request->correo_empresa;
                $result->direccion_empresa = $request->direccion_empresa;
                $result->direccion_laboratorio = $request->direccion_laboratorio;
                $result->recomendaciones_examen = $request->recomendaciones_examen;
                $result->novedades_examenes = $request->novedades_examenes;
                $result->subsidio_transporte = $request->consulta_subsidio;
                $result->estado_vacante = $request->consulta_vacante;
                $result->tipo_documento_id = $request->tipo_identificacion;
                $result->observacion_estado = $request->consulta_observacion_estado;
                $result->correo_laboratorio = $request->correo_laboratorio;
                $result->contacto_empresa = $request->contacto_empresa;
                $result->responsable_id = $request->encargado_id;
                $result->responsable_corregir = $request->consulta_encargado_corregir;
                if ($request->variableX == 1) {
                    $result->nc_hora_cierre = 'Servicio no conforme';
                }
                if ($request->n_servicio != null) {
                    $result->n_servicio = $request->n_servicio;
                }

                if ($tipo_servicio == 2) {
                    if ($candidatos[$i]['en_proceso'] != 1) {
                        // $result->nombre_completo = $candidatos[$i]['nombre_candidato'] . ' ' . $candidatos[$i]['apellido_candidato'];
                        // $result->numero_contacto = $candidatos[$i]['celular_candidato'];
                        // $result->correo_notificacion_usuario = $candidatos[$i]['correo_candidato'];
                        // $result->tipo_documento_id = $candidatos[$i]['tipo_identificacion_id'];
                        // $result->numero_identificacion = $candidatos[$iF]['numero_documento_candidato'];
                        $result->candidato_id = $candidatos[$i]['usuario_id'];
                        $candidato = CandidatoServicioModel::where('usuario_id', '=', $candidatos[$i]['usuario_id'])->first();
                        if ($candidato) {
                            $candidato->en_proceso = 1;
                            $candidato->save();
                        }
                    } else {
                        continue;
                    }
                } else if ($tipo_servicio == 3 ||  $tipo_servicio == 4) {
                    $result->nombre_completo = $request->nombre_completo;
                    $result->numero_contacto = $request->numero_contacto;
                    $result->correo_notificacion_usuario = $request->correo_candidato;
                    $result->tipo_documento_id = $request->tipo_identificacion;
                    $result->numero_identificacion = $request->numero_identificacion;
                }
                $result->save();

                $laboratorio = new RegistroIngresoLaboratorio;
                $laboratorio->registro_ingreso_id  = $result->id;
                $laboratorio->laboratorio_medico_id = $request->laboratorio_medico_id;
                $laboratorio->save();

                $seguimiento = new FormularioIngresoSeguimiento;
                $seguimiento->estado_ingreso_id = $request->estado_id;
                $seguimiento->usuario = $user->nombres . ' ' . $user->apellidos;
                $seguimiento->formulario_ingreso_id = $result->id;
                $seguimiento->save();

                array_push($ids, $result->id);

                if ($result->responsable == null) {
                    $this->actualizaestadoingreso($result->id, $result->estado_ingreso_id, $result->responsable_id, $responsable_actual);
                }
                if ($request->consulta_encargado != null) {
                    $this->eventoSocket($request->encargado_id);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return $e;
                return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor intente nuevamente']);
            }
        }

        DB::commit();
        $numero_radicados_seiya = formularioGestionIngreso::select('id')->where('n_servicio', '=', $request->n_servicio)->get();
        $orden_servicio = OrdenServcio::where('numero_radicado', '=', $request->n_servicio)->first();
        $orden_servicio->numero_radicados_seiya = $numero_radicados_seiya->count();
        $orden_servicio->save();
        return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
    }

    public function eventoSocket($id)
    {
        try {
            $data = [
                'encargado_id' => $id,
                'mensaje' => 'Te han asignado una nueva actividad en el módulo Seiya.'
            ];
            event(new NotificacionSeiya($data));
        } catch (\Throwable $th) {
        }
        return;
    }

    public function pendientes(Request $request)
    {
        $user = auth()->user();
        $lista = $request->all();
        foreach ($lista as $item) {
            $existeIngreso = FormularioIngresoPendientes::where('registro_ingreso_id', $item)->where('usuario_id', $user->id)->first();

            if (!$existeIngreso) {
                $result = new FormularioIngresoPendientes;
                $result->registro_ingreso_id = $item;
                $result->usuario_id = $user->id;
                $result->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Tareas pendientes agregadas exitosamente.']);
    }

    public function pendientes2($cantidad)
    {

        $user = auth()->user();
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->LeftJoin('usr_app_formulario_ingreso_pendientes as pen', 'pen.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->where('pen.usuario_id', '=', $user->id)
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'est.nombre as estado',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'mun.nombre as ciudad',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',
            )
            ->orderby('usr_app_formulario_ingreso.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        try {
            $documentos = $request->all();
            $array = [];
            if (empty($documentos)) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente.']);
            }

            for ($i = 0;; $i++) {
                $index = 'formulario' . $i;
                if (isset($documentos[$index])) {
                    $array = strpos($documentos[$index], ',') !== false ? explode(",", $documentos[$index]) : [$documentos[$index]];
                    break;
                }
            }

            // Crear un array para almacenar archivos temporalmente
            $tempFiles = [];

            // Almacenar temporalmente los archivos
            foreach ($documentos as $key => $item) {
                if (strpos($key, 'documento') !== false && $item instanceof \Illuminate\Http\UploadedFile) {
                    $tempFiles[$key] = $item;
                }
            }

            foreach ($array as $ingreso_id) {
                $value = '';
                $id = '';
                $ids = [];
                $observacion = '';
                $observaciones = [];
                $rutas = [];

                foreach ($documentos as $key => $item) {
                    if (strpos($key, 'documento') !== false && isset($tempFiles[$key])) {
                        $item = $tempFiles[$key];

                        try {
                            $microtime = microtime(true);
                            $microtimeString = (string) $microtime;
                            $microtimeWithoutDecimal = str_replace('.', '', $microtimeString);

                            $nombreArchivoOriginal = $item->getClientOriginalName();
                            $nuevoNombre = '_' . $ingreso_id . '_' . $microtimeWithoutDecimal . "_" . $nombreArchivoOriginal;

                            $carpetaDestino = public_path('upload/');
                            $contenido = file_get_contents($item->getRealPath());
                            file_put_contents($carpetaDestino . $nuevoNombre, $contenido);

                            $ruta = 'upload/' . $nuevoNombre; // Construye la ruta relativa
                            $rutas[] = $ruta;
                            $value .= $ruta . ' ';
                        } catch (\Exception $e) {
                            return response()->json(['status' => 'error', 'message' => 'Error al copiar el archivo: ' . $e->getMessage()]);
                        }
                    } else {
                        if (strpos($key, 'id') !== false) {
                            $ids[] = $item;
                            $id .= $item . ' ';
                        } else if (strpos($key, 'observacion') !== false) {
                            $observaciones[] = $item;
                            $observacion .= $item . ' ';
                        }
                    }
                }

                $permisos = $this->validaPermiso();
                $result = formularioGestionIngreso::where('id', '=', $ingreso_id)->first();

                if (in_array($result->estado_ingreso_id, [11, 12, 17]) && in_array('33', $permisos)) {
                    for ($i = 0; $i < count($ids); $i++) {
                        $documento = new FormularioIngresoArchivos;
                        $documento->arhivo_id = $ids[$i];
                        $documento->ruta = $rutas[$i];
                        $documento->observacion = $observaciones[$i] != 'undefined' ? $observaciones[$i] : '';
                        $documento->ingreso_id = $ingreso_id;
                        $documento->save();
                    }
                } else if (!in_array($result->estado_ingreso_id, [11, 12, 17])) {
                    for ($i = 0; $i < count($ids); $i++) {
                        $documento = new FormularioIngresoArchivos;
                        $documento->arhivo_id = $ids[$i];
                        $documento->ruta = $rutas[$i];
                        $documento->observacion = $observaciones[$i] != 'undefined' ? $observaciones[$i] : '';
                        $documento->ingreso_id = $ingreso_id;
                        $documento->save();
                    }
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción después de que el proceso esté cerrado.']);
                }
            }
            return response()->json(['status' => 'success', 'message' => 'Los archivos adjuntos del formulario fueron actualizados de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intente nuevamente, si el problema persiste por favor contacte al administrador del sitio: ' . $e->getMessage()]);
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
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $ids = [];
            array_push($ids, $id);
            $estado_id = $request->estado_id;
            $result = formularioGestionIngreso::find($id);
            $responsable_inicial = str_replace("null", "", $result->responsable);
            $estado_inicial = $result->estado_ingreso_id;

            $permisos = $this->validaPermiso();

            if ($result->responsable_id != null && $result->responsable_id != $user->id && !in_array('31', $permisos)) {

                $seguimiento = new FormularioIngresoSeguimiento;
                $seguimiento->estado_ingreso_id = $request->estado_id;
                $seguimiento->usuario =  $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
                $seguimiento->formulario_ingreso_id = $id;
                $seguimiento->save();

                return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
            }

            $result->fecha_ingreso = $request->fecha_ingreo;
            $result->numero_identificacion = $request->numero_identificacion;
            $result->nombre_completo = $request->nombre_completo;
            $result->cliente_id = $request->empresa_cliente_id;
            $result->cargo = $request->cargo;
            $result->salario = $request->salario;
            $result->municipio_id = $request->municipio_id;
            $result->numero_contacto = $request->numero_contacto;
            $result->eps = $request->eps;
            $result->afp_id = $request->afp_id;
            $result->estradata = $request->consulta_stradata;
            $result->novedades = $request->novedades;
            $result->laboratorio = $request->laboratorio;
            $result->examenes = $request->examenes;
            $result->fecha_examen = $request->fecha_examen;
            $result->tipo_servicio_id = $request->tipo_servicio_id;
            $result->numero_vacantes = $request->numero_vacantes;
            $result->numero_contrataciones = $request->numero_contrataciones;
            $result->citacion_entrevista = $request->citacion_entrevista;
            $result->profesional = $request->profesional;
            $result->informe_seleccion = $request->informe_seleccion;
            $result->responsable = str_replace("null", "", $request->consulta_encargado);
            $result->estado_ingreso_id = $request->estado_id;
            $result->novedad_stradata = $request->novedades_stradata;
            $result->correo_notificacion_usuario = $request->correo_candidato;
            $result->correo_notificacion_empresa = $request->correo_empresa;
            $result->direccion_empresa = $request->direccion_empresa;
            $result->direccion_laboratorio = $request->direccion_laboratorio;
            $result->recomendaciones_examen = $request->recomendaciones_examen;
            $result->novedades_examenes = $request->novedades_examenes;
            $result->subsidio_transporte = $request->consulta_subsidio;
            $result->observacion_estado = $request->consulta_observacion_estado;
            $result->tipo_documento_id = $request->tipo_identificacion;
            $result->correo_laboratorio = $request->correo_laboratorio;
            $result->contacto_empresa = $request->contacto_empresa;
            $result->responsable_id = $request->encargado_id;

            if ($result->observacion_estado == 'Servicio no conforme') {
                $result->afectacion_servicio = $request->afectacion_servicio;
            } else {
                $result->afectacion_servicio = null;
            }

            if ($result->observacion_estado == 'Servicio no conforme') {
                $result->responsable_corregir = $request->consulta_encargado_corregir;
            } else {
                $result->responsable_corregir = null;
            }

            if ($request->estado_id == 10) {
                $result->estado_vacante = 'Cerrado';
            } else if (in_array($request->estado_id, [19, 44, 47, 12, 31, 32, 45])) {
                $result->estado_vacante = 'Cancelado';
            } else {
                $result->estado_vacante = $request->consulta_vacante;
            }

            if ($request->variableX == 1) {
                $result->nc_hora_cierre = 'Servicio no conforme';
            }
            $result->n_servicio = $request->n_servicio == null ? null : $request->n_servicio;
            $result->save();

            $seguimiento = new FormularioIngresoSeguimiento;
            $seguimiento->estado_ingreso_id = $request->estado_id;
            $seguimiento->usuario =  $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
            $seguimiento->formulario_ingreso_id = $id;
            $seguimiento->save();

            $laboratorio = RegistroIngresoLaboratorio::where('registro_ingreso_id', $id)->get();

            if ($request->filled('laboratorio_medico_id')) {
                if ($laboratorio->isEmpty()) {
                    $laboratorio = new RegistroIngresoLaboratorio;
                    $laboratorio->registro_ingreso_id = $id;
                    $laboratorio->laboratorio_medico_id = $request->laboratorio_medico_id;
                    $laboratorio->save();
                } else {
                    foreach ($laboratorio as $item) {
                        $item->delete();
                    }
                    $laboratorio = new RegistroIngresoLaboratorio;
                    $laboratorio->registro_ingreso_id = $id;
                    $laboratorio->laboratorio_medico_id = $request->laboratorio_medico_id;
                    $laboratorio->save();
                }
            }


            if ($estado_id != $result->estado_ingreso_id ||  $result->responsable == null) {
                $this->actualizaestadoingreso($id, $estado_id, $result->responsable_id, $responsable_inicial, $estado_inicial);
            } else {
                $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
                $seguimiento_estado->responsable_inicial =  $responsable_inicial;
                $seguimiento_estado->responsable_final = str_replace("null", "", $result->responsable);
                $seguimiento_estado->estado_ingreso_inicial = $estado_inicial;
                $seguimiento_estado->estado_ingreso_final =   $request->estado_id;
                $seguimiento_estado->formulario_ingreso_id =  $id;
                $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' . str_replace("null", "", $user->apellidos);
                $seguimiento_estado->save();
            }
            DB::commit();
            return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
        } catch (\Exception $e) {
            // Revertir la transacción si se produce alguna excepción
            DB::rollback();
            // return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor verifique el llenado de todos los campos e intente nuevamente']);
        }
    }

    public function borrar_nc($id)
    {
        $result = formularioGestionIngreso::find($id);
        $result->nc_hora_cierre = null;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'No conformidad borrada de manera exitosa.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al borrar la no conformidad.']);
        }
    }
    public function hora()
    {
        $permisos = $this->validaPermiso();

        $hora_actual = date("H:i:s");
        $hora_limite = strtotime('15:00:00');

        if (strtotime($hora_actual) > $hora_limite && !in_array('31', $permisos)) {
            return 1;
        } else {
            return 2;
        }
    }

    public function buscarcedula(Request $request)
    {
        // Subconsulta para obtener la primera aparición de cada formulario_ingreso_id con estado_ingreso_id = 10
        $firstOccurrence = DB::table('usr_app_formulario_ingreso_seguimiento as u1')
            ->select('u1.formulario_ingreso_id', DB::raw('MIN(u1.created_at) as first_created_at'))
            ->where('u1.estado_ingreso_id', 10)
            ->groupBy('u1.formulario_ingreso_id');

        // Inicializar un array vacío para almacenar los meses con valores
        $meses_con_valores = [];
        $valores_por_mes = [];

        // Consulta principal para obtener los registros que coinciden con la primera aparición en cada mes del año
        for ($mes = 1; $mes <= 12; $mes++) {
            $result = DB::table('usr_app_formulario_ingreso_seguimiento as u2')
                ->joinSub($firstOccurrence, 'first_occurrence', function ($join) {
                    $join->on('u2.formulario_ingreso_id', '=', 'first_occurrence.formulario_ingreso_id')
                        ->on('u2.created_at', '=', 'first_occurrence.first_created_at');
                })
                ->select('u2.formulario_ingreso_id', 'u2.created_at')
                ->where('u2.estado_ingreso_id', 10)
                ->whereMonth('u2.created_at', $mes) // Filtrar por el mes en la tabla principal
                ->exists(); // Verificar si existen registros en este mes

            // Si hay resultados para este mes, almacenar el nombre del mes y un array con 12 posiciones
            if ($result) {
                $mes_nombre = date('F', mktime(0, 0, 0, $mes, 1));
                $total = DB::table('usr_app_formulario_ingreso_seguimiento as u2')
                    ->joinSub($firstOccurrence, 'first_occurrence', function ($join) {
                        $join->on('u2.formulario_ingreso_id', '=', 'first_occurrence.formulario_ingreso_id')
                            ->on('u2.created_at', '=', 'first_occurrence.first_created_at');
                    })
                    ->where('u2.estado_ingreso_id', 10)
                    ->whereMonth('u2.created_at', $mes)
                    ->count();

                // Almacenar el nombre del mes en un array
                $meses_con_valores[] = $mes_nombre;

                // Crear un array con 12 posiciones y colocar el total si es diferente de cero
                $array_valores = array_fill(0, 12, 0);
                if ($total !== 0) {
                    $array_valores[$mes - 1] = $total;
                }

                // Almacenar los valores en el array correspondiente al mes
                $valores_por_mes[] = $array_valores;
            }
        }

        array_unshift($valores_por_mes, ["nombres" => $meses_con_valores]);
        return $valores_por_mes;
    }

    public function asignacionmasiva(Request $request, $id_estado, $id_encargado)
    {
        $array = $request->all();
        $user = auth()->user();
        $permisos = $this->validaPermiso();
        $responsable = User::find($id_encargado);
        $cantidad = count($array);
        $bandera = true;
        if ($cantidad <= 10) {
            DB::beginTransaction();
            foreach ($array as $id) {
                try {
                    $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $id)
                        ->first();
                    if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user->id && !in_array('31', $permisos)) {
                        $bandera = false;
                    }
                    if (!in_array('35', $permisos)) {
                        $bandera = false;
                    }

                    $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
                    $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable;
                    $seguimiento_estado->responsable_final = $responsable->nombres . ' ' . $responsable->apellidos;
                    $seguimiento_estado->estado_ingreso_inicial =  $registro_ingreso->estado_ingreso_id;
                    $seguimiento_estado->estado_ingreso_final =  $id_estado;
                    $seguimiento_estado->formulario_ingreso_id =  $id;
                    $seguimiento_estado->actualiza_registro =   $user->nombres . ' ' .  str_replace('null', '', $responsable->apellidos);

                    $seguimiento_estado->save();

                    $registro_ingreso->responsable_anterior = $registro_ingreso->responsable;
                    $registro_ingreso->responsable = $responsable->nombres . ' ' . str_replace('null', '', $responsable->apellidos);
                    $registro_ingreso->asignacion_manual = 1;
                    $registro_ingreso->responsable_id = $responsable->id;
                    $registro_ingreso->estado_ingreso_id = $id_estado;
                    $registro_ingreso->save();

                    $seguimiento = new FormularioIngresoSeguimiento;
                    $seguimiento->estado_ingreso_id = $id_estado;
                    $seguimiento->usuario = $responsable->nombres . ' ' . str_replace('null', '', $responsable->apellidos);
                    $seguimiento->formulario_ingreso_id = $id;
                    $seguimiento->save();
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
                }
            }
            if ($bandera) {
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
            }
        } else {
            return response()->json(['status' => 'success', 'message' => 'La cantidad permitida de registros a actualizar es de 10.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function eliminararchivo($item, $id)
    {
        $result = FormularioIngresoArchivos::where('usr_app_formulario_ingreso_archivos.ingreso_id', '=', $item)
            ->where('usr_app_formulario_ingreso_archivos.arhivo_id', '=', $id)
            ->first();
        $registro = FormularioIngresoArchivos::find($result->id);
        if ($registro->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado con Exito']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
    public function destroy($id)
    {
        $result = formularioGestionIngreso::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado con Exito']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            $user = auth()->user();
            for ($i = 0; $i < count($request->id); $i++) {
                $result = FormularioIngresoPendientes::where('registro_ingreso_id', '=', $request->id[$i])->where('usuario_id', $user->id)->first();
                // return $result;
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function consultaseguimiento($id)
    {
        // $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
        //     ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
        //     ->where('usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final', $id)
        //     ->select(
        //         'usr_app_formulario_ingreso_seguimiento_estado.responsable_inicial',
        //         'usr_app_formulario_ingreso_seguimiento_estado.responsable_final',
        //         'ei.nombre as estado_ingreso_inicial',
        //         'ef.nombre as estado_ingreso_final',
        //         'usr_app_formulario_ingreso_seguimiento_estado.actualiza_registro',
        //         DB::raw("FORMAT(usr_app_formulario_ingreso_seguimiento_estado.created_at, 'dd/MM/yyyy HH:mm:ss') as fecha_radicado"),
        //         'usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id',


        //     )
        //     ->orderby('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id', 'desc')
        //     ->get();

        // $array = [];

        // for ($i = 0; $i < count($seguimiento_estados) - 1; $i++) {
        //     if ($seguimiento_estados[$i]->formulario_ingreso_id != $seguimiento_estados[$i + 1]->formulario_ingreso_id) {
        //         // Si los IDs no son iguales, inserta el actual
        //         array_push($array, $seguimiento_estados[$i]);
        //     } else {
        //         // Si los IDs son iguales, compara las fechas
        //         if (Carbon::parse($seguimiento_estados[$i]->created_at) > Carbon::parse($seguimiento_estados[$i + 1]->created_at)) {
        //             array_push($array, $seguimiento_estados[$i]);
        //         } else {
        //             array_push($array, $seguimiento_estados[$i + 1]);
        //         }

        //         // Salta el siguiente elemento ya que ha sido comparado e insertado
        //         $i++;
        //     }
        // }

        // // Asegúrate de agregar el último elemento si no ha sido comparado
        // if ($i == count($seguimiento_estados) - 1) {
        //     array_push($array, $seguimiento_estados[$i]);
        // }
        // $result['cantidad'] = count($array);
        // $result['seguimiento_estados'] = $array;
        // return response()->json($result);
        $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
            ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
            ->join('usr_app_formulario_ingreso as formulario', 'formulario.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id')
            ->join('usr_app_formulario_ingreso_tipo_servicio as tipo_servicio', 'tipo_servicio.id', '=', 'formulario.tipo_servicio_id')
            ->join('usr_app_clientes as cli', 'cli.id', '=', 'formulario.cliente_id')
            ->where('usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final', $id)
            ->whereDate('usr_app_formulario_ingreso_seguimiento_estado.created_at', '>=', Carbon::parse('2024-09-1'))
            ->whereDate('usr_app_formulario_ingreso_seguimiento_estado.created_at', '<=', Carbon::parse('2024-10-30'))
            ->select(
                'cli.razon_social',
                'usr_app_formulario_ingreso_seguimiento_estado.responsable_inicial',
                'usr_app_formulario_ingreso_seguimiento_estado.responsable_final',
                'ei.nombre as estado_ingreso_inicial',
                'ef.nombre as estado_ingreso_final',
                'usr_app_formulario_ingreso_seguimiento_estado.actualiza_registro',
                DB::raw("FORMAT(usr_app_formulario_ingreso_seguimiento_estado.created_at, 'dd/MM/yyyy HH:mm:ss') as fecha_radicado"),
                'usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id',
                'usr_app_formulario_ingreso_seguimiento_estado.created_at',
                'tipo_servicio.nombre_servicio',
                // 'formulario.profesional',
                DB::raw("COALESCE(formulario.profesional, '') as profesional"),
                // 'formulario.n_servicio'
                DB::raw("COALESCE(formulario.n_servicio, '') as n_servicio"),
                DB::raw("COALESCE(formulario.afectacion_servicio, '') as afectacion_servicio"),
            )
            ->orderby('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id', 'desc')
            ->orderby('usr_app_formulario_ingreso_seguimiento_estado.created_at', 'desc')
            ->get();

        // Agrupamos por formulario_ingreso_id y seleccionamos el registro más reciente
        $filtered = $seguimiento_estados->groupBy('formulario_ingreso_id')->map(function ($items) {
            return $items->sortByDesc('created_at')->first();
        })->values();

        $result['cantidad'] = $filtered->count();
        $result['seguimiento_estados'] = $filtered;
        return response()->json($result);
    }
}
