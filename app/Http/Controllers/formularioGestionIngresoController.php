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
use Carbon\Carbon;
use App\Events\NotificacionSeiya;
use App\Models\HistoricoConceptosCandidatosModel;
use App\Models\UsuariosCandidatosModel;
use App\Models\SalidaNoConformeseiya;
use App\Models\EstadoCandidatoServioOrdenServicioModel;
use App\Models\CandidatoSolicitudServicioModel;
use App\Models\SolicitudServicioModel;
use TCPDF;
use Illuminate\Support\Facades\DB;
use App\Traits\Permisos;
use App\Traits\AutenticacionGuard;
use App\Exports\SalidasNoConformeExport;
use App\Models\ExamenesRecomendacionesModel;
use App\Models\Cargo2;
use App\Models\UsuariosInternosModel;
use App\Models\Municipios;

class formularioGestionIngresoController extends Controller
{
    use Permisos;
    use AutenticacionGuard;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $permisos = $this->permisos();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);

        $result = formularioGestionIngreso::leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftjoin('usr_app_historico_concepto_candidatos as historico_candidatos', 'historico_candidatos.formulario_ingreso_id', '=', 'usr_app_formulario_ingreso.id')
            ->leftjoin('usr_app_candadato_solicitud_servicio as cs', 'cs.id', '=', 'usr_app_formulario_ingreso.candidato_servicio_id')
            ->leftjoin('usr_app_solicitud_servicio as sc', 'sc.id', '=', 'cs.servicio_id')
            ->leftJoin('usr_app_usuarios as us1', 'us1.id', 'cs.usuario_id')
            ->leftJoin('usr_app_candidatos_c as can1', 'can1.usuario_id', 'us1.id')
            ->leftJoin('usr_app_genero as genero', 'genero.id', '=', 'can1.genero_id')
            ->leftJoin('usr_app_clientes as cli', 'cli.id', 'sc.cliente_id')
            ->leftJoin('usr_app_clientes as cli2', 'cli2.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'cs.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_municipios as mun2', 'mun2.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'can1.tip_doc_id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'sc.linea_servicio_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser2', 'tiser2.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_lista_cargos as car', 'car.id', 'cs.cargo_solicitado_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->leftJoin('usr_app_conceptos_medicos as concepto_medico', 'concepto_medico.id', 'usr_app_formulario_ingreso.concepto_medico_id')
            ->when(!in_array('42', $permisos), function ($query) {
                return $query->where(function ($query) {
                    $query->whereNotIn('cli.nit', ['811025401', '900032514'])
                        ->orWhereNull('cli.nit');
                });
            })
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                DB::raw("COALESCE(usr_app_formulario_ingreso.n_servicio, sc.numero_radicado) as n_servicio"),
                DB::raw("COALESCE(tiser.nombre_servicio, tiser2.nombre_servicio) as nombre_servicio"),
                'usr_app_formulario_ingreso.updated_at',
                'usr_app_formulario_ingreso.created_at',
                'concepto_medico.nombre as concepto_medico',
                DB::RAW("COALESCE(can1.num_doc,usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::raw("COALESCE(usr_app_formulario_ingreso.nombre_completo,CONCAT(
                            ISNULL(can1.primer_apellido, ''), ' ',
                            ISNULL(can1.segundo_apellido, ''), ' ',
                            ISNULL(can1.primer_nombre, ''), ' ',
                            ISNULL(can1.segundo_nombre, '')
                        )
                    ) as nombre_completo
                "),
                DB::RAW("COALESCE(car.nombre,usr_app_formulario_ingreso.cargo) as cargo"),
                DB::raw("COALESCE(cli.razon_social, cli2.razon_social) as razon_social"),
                DB::raw("COALESCE(mun.nombre, mun2.nombre) as municipio"),
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                'cs.fecha_ingreso as fecha_hora_ingreso',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'esti.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'esti.id as estado_ingreso_id',
                'esti.color as color_estado',
                'cli.contratacion_hora_confirmacion as hora_confirmacion',
                'usr_app_formulario_ingreso.responsable_id',
                'concepto_medico.clave as concepto_medico_clave',
                /*   'genero.nombre as genero' */
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

        // $user = auth()->user();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
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


        $permisos = $this->permisos();

        if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user['id'] && !in_array('31', $permisos)) {
            return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
        }

        // Asignar a cada registro de ingreso un responsable
        $indiceResponsable = $registro_ingreso->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable];

        if ($responsable_id != null) {
            $responsable = UsuariosInternosModel::where('usuario_id', $responsable_id)->select('usuario_id', 'nombres', 'apellidos',)->first();
        }
        $this->eventoSocket($responsable->usuario_id);

        $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
        $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable != null ?  str_replace("null", "", $registro_ingreso->responsable) : str_replace("null", "", $responsable_actual);
        $seguimiento_estado->responsable_final = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);;
        $seguimiento_estado->estado_ingreso_inicial = $estado_inicial != null ? $estado_inicial : $registro_ingreso->estado_ingreso_id;
        $seguimiento_estado->estado_ingreso_final =   $estado_id;
        $seguimiento_estado->formulario_ingreso_id =  $item_id;
        $seguimiento_estado->actualiza_registro =   $user['nombres'] . ' ' .  $user['apellidos'];
        $seguimiento_estado->save();

        // Actualiza el estado del candidato en la solicitud de servicio
        $candidato_servicio = CandidatoSolicitudServicioModel::where('id', '=', $registro_ingreso->candidato_servicio_id)->first();
        $estado_candidato = EstadoCandidatoServioOrdenServicioModel::where('estado_orden_servicio_id', '=', $estado_id)->first();
        if ($estado_candidato) {
            $candidato_servicio->estado_candidato_id =  $estado_candidato->estado_candidato_servicio_id;
            $candidato_servicio->save();
        }

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
            $user = $this->getUserRelaciones();
            $user = $user->getData(true);
            $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
                ->first();

            $permisos = $this->permisos();


            if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user['id'] && !in_array('31', $permisos)) {
                return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
            }

            $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
            $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable;
            $seguimiento_estado->responsable_final = $nombre_responsable;
            $seguimiento_estado->estado_ingreso_inicial =  $registro_ingreso->estado_ingreso_id;
            $seguimiento_estado->estado_ingreso_final =  $registro_ingreso->estado_ingreso_id;
            $seguimiento_estado->formulario_ingreso_id =  $item_id;
            $seguimiento_estado->actualiza_registro =    $user['nombres'] . ' ' .  $user['apellidos'];

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
        // $user = auth()->user();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $responsable = UsuarioPermiso::where('usr_app_permisos_usuarios.usuario_id', '=', $user['id'])
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
            ->orderby('nombres')
            ->get();
        return response()->json($usuarios);
    }

    public function byid($id)
    {
        $result = formularioGestionIngreso::leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftjoin('usr_app_historico_concepto_candidatos as historico_candidatos', 'historico_candidatos.formulario_ingreso_id', '=', 'usr_app_formulario_ingreso.id')
            ->leftjoin('usr_app_candadato_solicitud_servicio as cs', 'cs.id', '=', 'usr_app_formulario_ingreso.candidato_servicio_id')
            ->leftjoin('usr_app_solicitud_servicio as sc', 'sc.id', '=', 'cs.servicio_id')
            ->leftjoin('usr_app_motivos_servicio as ms', 'ms.id', '=', 'sc.motivo_servicio_id')
            ->leftJoin('usr_app_usuarios as us1', 'us1.id', 'cs.usuario_id')
            ->leftJoin('usr_app_candidatos_c as can1', 'can1.usuario_id', 'us1.id')
            ->leftJoin('usr_app_clientes as cli', 'cli.id', 'sc.cliente_id')
            ->leftJoin('usr_app_clientes as cli2', 'cli2.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'cs.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_municipios as mun2', 'mun2.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_departamentos as dep2', 'dep2.id', 'mun2.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'can1.tip_doc_id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'sc.linea_servicio_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser2', 'tiser2.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_subestados_orden_servicio as sub', 'sub.id', 'usr_app_formulario_ingreso.subestado_id')
            ->leftjoin('usr_app_cargos2 as car2', 'car2.id', '=', 'cs.cargo_solicitado_id')
            ->leftjoin('usr_app_lista_cargos as car', 'car.id', '=', 'car2.cargo_id')
            ->leftjoin('usr_app_conceptos_medicos as concepto_medico', 'concepto_medico.id', '=', 'usr_app_formulario_ingreso.concepto_medico_id')
            ->where('usr_app_formulario_ingreso.id', '=', $id)
            ->select(
                'historico_candidatos.id as historico_candidatos_id',
                'usr_app_formulario_ingreso.id',
                'esti.nombre as estado_ingreso',
                'esti.id as estado_ingreso_id',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'cs.fecha_ingreso as fecha_hora_ingreso',
                DB::RAW("COALESCE(can1.num_doc,usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::raw("COALESCE(usr_app_formulario_ingreso.nombre_completo,CONCAT(
                            ISNULL(can1.primer_apellido, ''), ' ',
                            ISNULL(can1.segundo_apellido, ''), ' ',
                            ISNULL(can1.primer_nombre, ''), ' ',
                            ISNULL(can1.segundo_nombre, '')
                        )
                    ) as nombre_completo
                "),

                DB::raw("COALESCE(cli.razon_social, cli2.razon_social) as razon_social"),
                DB::raw("COALESCE(cli.id, cli2.id) as cliente_id"),
                DB::raw("COALESCE(cs.direccion_prestacion_servicio, usr_app_formulario_ingreso.direccion_empresa) as direccion_empresa"),
                'car.id as cargo_id',
                'cs.salario',

                DB::raw("COALESCE(mun.nombre, mun2.nombre) as municipio"),
                DB::raw("COALESCE(mun.id, mun2.id) as municipio_id"),
                DB::raw("COALESCE(dep.nombre, dep2.nombre) as departamento"),
                DB::raw("COALESCE(dep.id, dep2.id) as departamento_id"),

                DB::raw("COALESCE(usr_app_formulario_ingreso.n_servicio, sc.numero_radicado) as n_servicio"),
                DB::RAW("COALESCE(cs.celular_candidato,usr_app_formulario_ingreso.numero_contacto) as numero_contacto"),
                DB::RAW("COALESCE(car.nombre,usr_app_formulario_ingreso.cargo) as cargo"),
                'usr_app_formulario_ingreso.eps',
                'usr_app_formulario_ingreso.afp_id',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.examenes',
                'usr_app_formulario_ingreso.fecha_examen',
                'pais.id as pais_id',
                'pais.nombre as pais',
                'usr_app_formulario_ingreso.created_at as fecha_radicado',
                DB::raw("COALESCE(tiser.nombre_servicio, tiser2.nombre_servicio) as nombre_servicio"),
                DB::raw("COALESCE(tiser.id, tiser2.id) as tipo_servicio_id"),
                'usr_app_formulario_ingreso.numero_vacantes',
                'usr_app_formulario_ingreso.numero_contrataciones',
                'usr_app_formulario_ingreso.citacion_entrevista',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.informe_seleccion',
                'usr_app_formulario_ingreso.direccion_laboratorio',
                'usr_app_formulario_ingreso.recomendaciones_examen',
                'usr_app_formulario_ingreso.novedad_stradata',
                'usr_app_formulario_ingreso.correo_notificacion_empresa',
                DB::RAW("COALESCE(us1.email, usr_app_formulario_ingreso.correo_notificacion_usuario) as correo_notificacion_usuario"),
                'usr_app_formulario_ingreso.novedades_examenes',
                DB::RAW("CONCAT(ti.des_tip,'',ti2.des_tip) as tipo_identificacion"),
                DB::RAW("CONCAT(ti.tip_tip,'',ti2.tip_tip) as tip_tip"),
                DB::RAW("CONCAT(ti.cod_tip,'',ti2.cod_tip) as tipo_identificacion_id"),
                'usr_app_formulario_ingreso.subsidio_transporte',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.responsable_id',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.correo_laboratorio',
                'usr_app_formulario_ingreso.contacto_empresa',
                'usr_app_formulario_ingreso.responsable_corregir',
                'usr_app_formulario_ingreso.nc_hora_cierre',
                'usr_app_formulario_ingreso.candidato_servicio_id',
                'cs.whatsapp',
                'sub.nombre as subestado',
                'sub.id as subestado_id',
                'sc.id as servicio_id',
                'cs.usuario_id as candidato_id',
                'car2.id as cargo_cliente_id',
                'ms.id as motivo_servicio_id',
                'ms.nombre as motivo_servicio',
                'concepto_medico.id as concepto_medico_id',
                'concepto_medico.nombre as concepto_medico'
            )
            ->first();


        // se comentan algunas lineas de código porque al parecer no están usando 14/08/2025
        $examenes = Cargo2::leftJoin('usr_app_riesgos_laborales as rl2', 'rl2.id', '=', 'usr_app_cargos2.riesgo_laboral_id')
            ->leftJoin('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_cargos2.cargo_id')
            ->leftJoin('usr_app_subcategoria_cargos as sc', 'sc.id', '=', 'lc.subcategoria_cargo_id')
            ->leftJoin('usr_app_categoria_cargos as cc', 'cc.id', '=', 'sc.categoria_cargo_id')
            // ->leftJoin('usr_app_cargos2_recomendaciones as cr', 'cr.cargo_id', '=', 'usr_app_cargos2.id')
            // ->leftJoin('usr_app_lista_recomendaciones as recom', 'recom.id', '=', 'cr.recomendacion_id')
            ->leftJoin('usr_app_cargos2_examenes as cx', 'cx.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_lista_examenes as exam', 'exam.id', '=', 'cx.examen_id')
            // ->leftJoin('usr_app_examenes_recomendaciones as er', 'er.examen_id', '=', 'exam.id')
            // ->leftJoin('usr_app_perfil_cargo_examenes_recomendaciones as pcer', 'pcer.id', '=', 'er.recomendacion_id')
            ->where('usr_app_cargos2.id', $result->cargo_cliente_id)
            ->select(
                // 'usr_app_cargos2.cargo_id as cargo_id',
                // 'lc.nombre as cargo',
                // 'lc.subcategoria_cargo_id as categoria_cargo_id',
                // 'sc.categoria_cargo_id as tipo_cargo_id',
                // 'sc.nombre as categoria',
                // 'cc.nombre as tipo_cargo',
                // 'usr_app_cargos2.funcion_cargo as funcion_cargo',
                // 'usr_app_cargos2.riesgo_laboral_id',
                // 'rl2.nombre as riesgo_laboral',
                // 'cr.recomendacion_id',
                // 'recom.recomendacion1 as recomendacion1',
                // 'recom.recomendacion2 as recomendacion2',
                'exam.examen_id',
                'exam.nombre as examen',
                // 'pcer.nombre as recomendaciones',

            )

            ->distinct('exam.nombre as examen')
            ->get();

        $result['lista_examenes'] = $examenes;
        $idsExamenes = $examenes->pluck('examen_id')->unique()->values();
        $recomendaciones = ExamenesRecomendacionesModel::leftJoin('usr_app_perfil_cargo_examenes_recomendaciones as pcer', 'pcer.id', 'usr_app_examenes_recomendaciones.recomendacion_id')
            ->whereIn('usr_app_examenes_recomendaciones.examen_id', $idsExamenes)->select(
                'pcer.id',
                'pcer.nombre'
            )->get();
        $result['recomendaciones'] = $recomendaciones;

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

        $salidasNoConforme = SalidaNoConformeseiya::join('usr_app_procesos_snc_seiya as pro', 'pro.id', 'usr_app_salida_n_conforme_seiya.proceso_id')
            ->join('usr_app_categorias_snc_seiya as cat', 'cat.id', 'usr_app_salida_n_conforme_seiya.categoria_snc_id')
            ->where('orden_servicio_id', $id)
            ->select(
                'usr_app_salida_n_conforme_seiya.id',
                'usr_app_salida_n_conforme_seiya.responsable_corregir',
                'usr_app_salida_n_conforme_seiya.descripcion',
                'usr_app_salida_n_conforme_seiya.subsanado',
                'pro.nombre as proceso',
                'cat.nombre as categoria',
                'usr_app_salida_n_conforme_seiya.created_at',
                'usr_app_salida_n_conforme_seiya.updated_at',
                'usr_app_salida_n_conforme_seiya.correcion_aplicada',
                'usr_app_salida_n_conforme_seiya.corrigio_novedad',
            )
            ->orderby('usr_app_salida_n_conforme_seiya.id', 'DESC')
            ->get();
        $result['salidas_no_conforme'] = $salidasNoConforme;
        // return 'cargo'.$result->cargo_id.' cliente ' .$result->cliente_id;

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
        $examenes = implode(", ", array_map(fn($o) => $o->examen, $formulario->lista_examenes));
        $recomendaciones_examen = implode(", ", array_map(fn($o) => $o->nombre, $formulario->recomendaciones));



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
                    $pdf->SetX(20); // margen izquierdo
                    $pdf->MultiCell(170, 10, "\n\n\n\"No se exige la práctica de pruebas específicas consideradas discriminatorias como obligatorias para el acceso o permanencia en cualquier actividad laboral acorde a la resolución 1843 de 2025\"", 0, 'L');
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
                $pdf->Cell(95, 10, 'Evaluador:', 0, 0, 'L');
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
                $pdf->Cell(95, 10, 'Evaluador:', 0, 0, 'L');
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
        $permisos = $this->permisos();
        // $user = auth()->user();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $result = formularioGestionIngreso::leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftjoin('usr_app_historico_concepto_candidatos as historico_candidatos', 'historico_candidatos.formulario_ingreso_id', '=', 'usr_app_formulario_ingreso.id')
            ->leftjoin('usr_app_candadato_solicitud_servicio as cs', 'cs.id', '=', 'usr_app_formulario_ingreso.candidato_servicio_id')
            ->leftjoin('usr_app_solicitud_servicio as sc', 'sc.id', '=', 'cs.servicio_id')
            ->leftJoin('usr_app_usuarios as us1', 'us1.id', 'cs.usuario_id')
            ->leftJoin('usr_app_candidatos_c as can1', 'can1.usuario_id', 'us1.id')
            ->leftJoin('usr_app_clientes as cli', 'cli.id', 'sc.cliente_id')
            ->leftJoin('usr_app_clientes as cli2', 'cli2.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'cs.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'can1.tip_doc_id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'sc.linea_servicio_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser2', 'tiser2.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_lista_cargos as car', 'car.id', 'cs.cargo_solicitado_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->leftJoin('usr_app_conceptos_medicos as concepto_medico', 'concepto_medico.id', 'usr_app_formulario_ingreso.concepto_medico_id')
            ->when(!in_array('42', $permisos), function ($query) {
                return $query->where(function ($query) {
                    $query->whereNotIn('cli.nit', ['811025401', '900032514'])
                        ->orWhereNull('cli.nit');
                });
            })
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                DB::raw("COALESCE(usr_app_formulario_ingreso.n_servicio, sc.numero_radicado) as n_servicio"),
                DB::raw("COALESCE(tiser.nombre_servicio, tiser2.nombre_servicio) as nombre_servicio"),
                'usr_app_formulario_ingreso.updated_at',
                'usr_app_formulario_ingreso.created_at',
                'concepto_medico.nombre as concepto_medico',
                DB::RAW("COALESCE(can1.num_doc,usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::raw("COALESCE(usr_app_formulario_ingreso.nombre_completo,CONCAT(
                            ISNULL(can1.primer_apellido, ''), ' ',
                            ISNULL(can1.segundo_apellido, ''), ' ',
                            ISNULL(can1.primer_nombre, ''), ' ',
                            ISNULL(can1.segundo_nombre, '')
                        )
                    ) as nombre_completo
                "),
                DB::RAW("COALESCE(car.nombre,usr_app_formulario_ingreso.cargo) as cargo"),
                DB::raw("COALESCE(cli.razon_social, cli2.razon_social) as razon_social"),
                'mun.nombre as municipio',
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                'cs.fecha_ingreso as fecha_hora_ingreso',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'esti.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'esti.id as estado_ingreso_id',
                'esti.color as color_estado',
                'cli.contratacion_hora_confirmacion as hora_confirmacion',
                'usr_app_formulario_ingreso.responsable_id',
                'concepto_medico.clave as concepto_medico_clave',
            );
        foreach ($result as $item) {
            $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;
        }
        if ($request['ordenar_prioridad'] == true) {
            $result->whereNotNull('usr_app_formulario_ingreso.fecha_ingreso');
            $result->orderByRaw("CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE) DESC")
                ->orderBy('cli.contratacion_hora_confirmacion', 'ASC');
        }
        if ($request['filtro_mios'] == true) {
            $result->where('usr_app_formulario_ingreso.responsable_id', '=', $user['id']);
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
        $permisos = $this->permisos();
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

        $query = formularioGestionIngreso::leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftjoin('usr_app_historico_concepto_candidatos as historico_candidatos', 'historico_candidatos.formulario_ingreso_id', '=', 'usr_app_formulario_ingreso.id')
            ->leftjoin('usr_app_candadato_solicitud_servicio as cs', 'cs.id', '=', 'usr_app_formulario_ingreso.candidato_servicio_id')
            ->leftjoin('usr_app_solicitud_servicio as sc', 'sc.id', '=', 'cs.servicio_id')
            ->leftJoin('usr_app_usuarios as us1', 'us1.id', 'cs.usuario_id')
            ->leftJoin('usr_app_candidatos_c as can1', 'can1.usuario_id', 'us1.id')
            ->leftJoin('usr_app_clientes as cli', 'cli.id', 'sc.cliente_id')
            ->leftJoin('usr_app_clientes as cli2', 'cli2.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'cs.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'can1.tip_doc_id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'sc.linea_servicio_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser2', 'tiser2.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_lista_cargos as car', 'car.id', 'cs.cargo_solicitado_id')
            ->leftJoin('usr_app_registro_ingreso_laboraorio as ilab', 'ilab.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->leftJoin('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', 'ilab.laboratorio_medico_id')
            ->leftJoin('usr_app_conceptos_medicos as concepto_medico', 'concepto_medico.id', 'usr_app_formulario_ingreso.concepto_medico_id')
            ->when(!in_array('42', $permisos), function ($query) {
                return $query->where(function ($query) {
                    $query->whereNotIn('cli.nit', ['811025401', '900032514'])
                        ->orWhereNull('cli.nit');
                });
            })
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                DB::raw("COALESCE(usr_app_formulario_ingreso.n_servicio, sc.numero_radicado) as n_servicio"),
                DB::raw("COALESCE(tiser.nombre_servicio, tiser2.nombre_servicio) as nombre_servicio"),
                'usr_app_formulario_ingreso.updated_at',
                'usr_app_formulario_ingreso.created_at',
                'concepto_medico.nombre as concepto_medico',
                DB::RAW("COALESCE(can1.num_doc,usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::raw("COALESCE(usr_app_formulario_ingreso.nombre_completo,CONCAT(
                            ISNULL(can1.primer_apellido, ''), ' ',
                            ISNULL(can1.segundo_apellido, ''), ' ',
                            ISNULL(can1.primer_nombre, ''), ' ',
                            ISNULL(can1.segundo_nombre, '')
                        )
                    ) as nombre_completo
                "),
                DB::RAW("COALESCE(car.nombre,usr_app_formulario_ingreso.cargo) as cargo"),
                DB::raw("COALESCE(cli.razon_social, cli2.razon_social) as razon_social"),
                'mun.nombre as municipio',
                'ciulab.laboratorio',
                'usr_app_formulario_ingreso.laboratorio as otro_laboratorio',
                'usr_app_formulario_ingreso.fecha_examen',
                'cs.fecha_ingreso as fecha_hora_ingreso',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'esti.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'esti.id as estado_ingreso_id',
                'esti.color as color_estado',
                'cli.contratacion_hora_confirmacion as hora_confirmacion',
                'usr_app_formulario_ingreso.responsable_id',
                'concepto_medico.clave as concepto_medico_clave',
            )
            ->orderby('usr_app_formulario_ingreso.id', 'DESC');

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
            } elseif ($campoActual === 'concepto_medico') {
                $prefijoCampo = 'concepto_medico.';
                $campoActual = 'nombre';
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
                    if ($campoActual === 'numero_identificacion') {
                        $query->where(function ($subquery) use ($valorCompararActual) {
                            $subquery->where('usr_app_formulario_ingreso.numero_identificacion', $valorCompararActual)
                                ->orWhere('can1.num_doc', $valorCompararActual);
                        });
                    } else if ($campoActual === 'nombre_servicio') {
                        $query->where(function ($subquery) use ($valorCompararActual) {
                            $subquery->where('tiser.nombre_servicio', $valorCompararActual)
                                ->orWhere('tiser2.nombre_servicio', $valorCompararActual);
                        });
                    } else if ($campoActual === 'nombre_completo') {
                        $query->where(function ($subquery) use ($valorCompararActual) {
                            $subquery->where('usr_app_formulario_ingreso.nombre_completo', $valorCompararActual)
                                ->orWhere('tiser2.nombre_servicio', $valorCompararActual);
                        });
                    } else {
                        $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    }
                    break;
                case 'Igual a fecha':
                    $query->whereDate($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Contiene':
                    if ($campoActual === 'numero_identificacion') {
                        $query->where(function ($subquery) use ($valorCompararActual) {
                            $subquery->where('usr_app_formulario_ingreso.numero_identificacion', 'like', '%' . $valorCompararActual . '%')
                                ->orWhere('can1.num_doc', 'like', '%' . $valorCompararActual . '%');
                        });
                    } else if ($campoActual === 'nombre_servicio') {
                        $query->where(function ($subquery) use ($valorCompararActual) {
                            $subquery->where('tiser.nombre_servicio',  'like', '%' . $valorCompararActual . '%')
                                ->orWhere('tiser2.nombre_servicio',  'like', '%' . $valorCompararActual . '%');
                        });
                    } else if ($campoActual === 'nombre_completo') {
                        $query->where(function ($subquery) use ($valorCompararActual) {
                            $subquery->where('tiser.nombre_servicio',  'like', '%' . $valorCompararActual . '%')
                                ->orWhere('tiser2.nombre_servicio',  'like', '%' . $valorCompararActual . '%');
                        });
                    } else {
                        $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    }
                    break;
            }
        }

        $resultados = $query->paginate($cantidad);

        foreach ($resultados as $item) {
            $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;
        }

        return $resultados;
    }

    public function buscardocumentoformularioi($cedula)
    {
        $formulario = formularioGestionIngreso::leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftjoin('usr_app_historico_concepto_candidatos as historico_candidatos', 'historico_candidatos.formulario_ingreso_id', '=', 'usr_app_formulario_ingreso.id')
            ->leftjoin('usr_app_candadato_solicitud_servicio as cs', 'cs.id', '=', 'usr_app_formulario_ingreso.candidato_servicio_id')
            ->leftjoin('usr_app_solicitud_servicio as sc', 'sc.id', '=', 'cs.servicio_id')
            ->leftJoin('usr_app_usuarios as us1', 'us1.id', 'cs.usuario_id')
            ->leftJoin('usr_app_candidatos_c as can1', 'can1.usuario_id', 'us1.id')
            ->leftJoin('usr_app_clientes as cli', 'cli.id', 'sc.cliente_id')
            ->leftJoin('usr_app_clientes as cli2', 'cli2.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'cs.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('gen_tipide as ti', 'ti.cod_tip', '=', 'can1.tip_doc_id')
            ->leftJoin('gen_tipide as ti2', 'ti2.cod_tip', '=', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'sc.linea_servicio_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser2', 'tiser2.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('usr_app_lista_cargos as car', 'car.id', 'cs.cargo_solicitado_id')
            ->where('can1.num_doc', '=', $cedula)
            ->orWhere('usr_app_formulario_ingreso.numero_identificacion', '=', $cedula)
            ->select(
                'historico_candidatos.id as historico_candidatos_id',
                'usr_app_formulario_ingreso.id',
                'esti.nombre as estado_ingreso',
                'esti.id as estado_ingreso_id',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'cs.fecha_ingreso as fecha_hora_ingreso',
                DB::RAW("COALESCE(can1.num_doc,usr_app_formulario_ingreso.numero_identificacion) as numero_identificacion"),
                DB::raw("COALESCE(usr_app_formulario_ingreso.nombre_completo,CONCAT(
                            ISNULL(can1.primer_apellido, ''), ' ',
                            ISNULL(can1.segundo_apellido, ''), ' ',
                            ISNULL(can1.primer_nombre, ''), ' ',
                            ISNULL(can1.segundo_nombre, '')
                        )
                    ) as nombre_completo
                "),

                DB::raw("COALESCE(cli.razon_social, cli2.razon_social) as razon_social"),
                DB::raw("COALESCE(cli.id, cli2.id) as cliente_id"),
                DB::raw("COALESCE(cs.direccion_prestacion_servicio, usr_app_formulario_ingreso.direccion_empresa) as direccion_empresa"),
                'car.id as cargo_id',
                'cs.salario',
                'mun.nombre as municipio',
                'mun.id as municipio_id',
                DB::raw("COALESCE(usr_app_formulario_ingreso.n_servicio, sc.numero_radicado) as n_servicio"),
                DB::RAW("COALESCE(cs.celular_candidato,usr_app_formulario_ingreso.numero_contacto) as numero_contacto"),
                DB::RAW("COALESCE(car.nombre,usr_app_formulario_ingreso.cargo) as cargo"),
                'usr_app_formulario_ingreso.eps',
                'usr_app_formulario_ingreso.afp_id',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.examenes',
                'usr_app_formulario_ingreso.fecha_examen',
                'dep.nombre as departamento',
                'dep.id as departamento_id',
                'pais.id as pais_id',
                'pais.nombre as pais',
                'usr_app_formulario_ingreso.created_at as fecha_radicado',
                DB::raw("COALESCE(tiser.nombre_servicio, tiser2.nombre_servicio) as nombre_servicio"),
                DB::raw("COALESCE(tiser.id, tiser2.id) as tipo_servicio_id"),
                'usr_app_formulario_ingreso.numero_vacantes',
                'usr_app_formulario_ingreso.numero_contrataciones',
                'usr_app_formulario_ingreso.citacion_entrevista',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.informe_seleccion',
                'usr_app_formulario_ingreso.direccion_laboratorio',
                'usr_app_formulario_ingreso.recomendaciones_examen',
                'usr_app_formulario_ingreso.novedad_stradata',
                'usr_app_formulario_ingreso.correo_notificacion_empresa',
                DB::RAW("COALESCE(us1.email, usr_app_formulario_ingreso.correo_notificacion_usuario) as correo_notificacion_usuario"),
                'usr_app_formulario_ingreso.novedades_examenes',
                DB::RAW("CONCAT(ti.des_tip,'',ti2.des_tip) as tipo_identificacion"),
                DB::RAW("CONCAT(ti.cod_tip,'',ti2.cod_tip) as tipo_identificacion_id"),
                'usr_app_formulario_ingreso.subsidio_transporte',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.responsable_id',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.correo_laboratorio',
                'usr_app_formulario_ingreso.contacto_empresa',
                'usr_app_formulario_ingreso.responsable_corregir',
                'usr_app_formulario_ingreso.nc_hora_cierre',
                'usr_app_formulario_ingreso.candidato_servicio_id',
            )
            ->first();

        $laboratorios = RegistroIngresoLaboratorio::join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'usr_app_registro_ingreso_laboraorio.laboratorio_medico_id')
            ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
            ->where('usr_app_registro_ingreso_laboraorio.registro_ingreso_id', '=', $formulario->id)
            ->select(
                'ciulab.id',
                'ciulab.laboratorio as nombre',
                'mun.id as municipio_id',
                'mun.nombre as municipio',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
            )
            ->get();
        $formulario['laboratorios'] = $laboratorios;

        $archivos = FormularioIngresoArchivos::join('usr_app_archivos_formulario_ingreso as fi', 'fi.id', '=', 'usr_app_formulario_ingreso_archivos.arhivo_id')
            ->where('ingreso_id', $formulario->id)
            ->select(
                'usr_app_formulario_ingreso_archivos.arhivo_id',
                'usr_app_formulario_ingreso_archivos.ruta',
                'usr_app_formulario_ingreso_archivos.observacion',
                'fi.nombre',
                'fi.tipo_archivo'
            )
            ->orderby('usr_app_formulario_ingreso_archivos.arhivo_id', 'ASC')
            ->get();
        $formulario['archivos'] = $archivos;


        $seguimiento = FormularioIngresoSeguimiento::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento.estado_ingreso_id')
            ->where('usr_app_formulario_ingreso_seguimiento.formulario_ingreso_id', $formulario->id)
            ->select(
                'usr_app_formulario_ingreso_seguimiento.usuario',
                'ei.nombre as estado',
                'usr_app_formulario_ingreso_seguimiento.created_at',

            )
            ->orderby('usr_app_formulario_ingreso_seguimiento.id', 'desc')
            ->get();
        $formulario['seguimiento'] = $seguimiento;
        // return response()->json($result);


        $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
            ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
            ->where('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id', $formulario->id)
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
        $formulario['seguimiento_estados'] = $seguimiento_estados;

        $salidasNoConforme = SalidaNoConformeseiya::join('usr_app_procesos_snc_seiya as pro', 'pro.id', 'usr_app_salida_n_conforme_seiya.proceso_id')
            ->join('usr_app_categorias_snc_seiya as cat', 'cat.id', 'usr_app_salida_n_conforme_seiya.categoria_snc_id')
            ->where('orden_servicio_id', $formulario->id)
            ->select(
                'usr_app_salida_n_conforme_seiya.id',
                'usr_app_salida_n_conforme_seiya.responsable_corregir',
                'usr_app_salida_n_conforme_seiya.descripcion',
                'usr_app_salida_n_conforme_seiya.subsanado',
                'pro.nombre as proceso',
                'cat.nombre as categoria',
                'usr_app_salida_n_conforme_seiya.created_at',
                'usr_app_salida_n_conforme_seiya.updated_at',
                'usr_app_salida_n_conforme_seiya.correcion_aplicada',
                'usr_app_salida_n_conforme_seiya.corrigio_novedad',
            )
            ->orderby('usr_app_salida_n_conforme_seiya.id', 'DESC')
            ->get();
        $formulario['salidas_no_conforme'] = $salidasNoConforme;
        return response()->json($formulario);
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
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $ids = [];
        $responsable_actual =  $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
        for ($i = 0; $i < $replica; $i++) {
            try {
                $result = new formularioGestionIngreso;
                $result->concepto_medico_id = $request->concepto_medico_id;
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
                $result->responsable = $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
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
                $result->observacion_estado = $request->consulta_categoria_snc;
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
                $seguimiento->usuario = $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
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
        $candidatos = CandidatoSolicitudServicioModel::where('servicio_id', '=', $request->servicio_id)->where('usuario_id', '!=', '')->where('en_proceso', '!=', 1)
            ->select(
                'usr_app_candadato_solicitud_servicio.usuario_id',
                'usr_app_candadato_solicitud_servicio.en_proceso',
                'usr_app_candadato_solicitud_servicio.id as candidato_id',
            )->get();
        $replica = $candidatos->count();
        DB::beginTransaction();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $ids = [];
        $responsable_actual =  $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);

        for ($i = 0; $i < $replica; $i++) {
            try {
                $result = new formularioGestionIngreso;
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
                $result->responsable = $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
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
                $result->direccion_laboratorio = $request->direccion_laboratorio;
                $result->recomendaciones_examen = $request->recomendaciones_examen;
                $result->novedades_examenes = $request->novedades_examenes;
                $result->estado_vacante = $request->consulta_vacante;
                $result->observacion_estado = $request->consulta_categoria_snc;
                $result->correo_laboratorio = $request->correo_laboratorio;
                $result->contacto_empresa = $request->contacto_empresa;
                $result->responsable_corregir = $request->consulta_encargado_corregir;
                if ($request->variableX == 1) {
                    $result->nc_hora_cierre = 'Servicio no conforme';
                }

                if ($request->subestado_id != null) {
                    $result->subestado_id = $request->subestado_id;
                }

                if ($candidatos[$i]['en_proceso'] != '1') {
                    $result->candidato_servicio_id = $candidatos[$i]['candidato_id'];
                    $candidato = CandidatoSolicitudServicioModel::where('usuario_id', '=', $candidatos[$i]['usuario_id'])->first();
                    if ($candidato) {
                        $candidato->en_proceso = 1;
                        $candidato->save();
                    }
                } else {
                    continue;
                }
                $result->save();

                // TODO:
                $orden_servicio = SolicitudServicioModel::where('numero_radicado', '=', $request->n_servicio)->first();
                $candidato_servicio = CandidatoSolicitudServicioModel::where('servicio_id', '=', $orden_servicio->id)->first();
                $estado_candidato = EstadoCandidatoServioOrdenServicioModel::where('estado_orden_servicio_id', '=', $request->estado_id)->first();
                if (isset($estado_candidato)) {
                    $candidato_servicio->estado_candidato_id =  $estado_candidato->estado_candidato_servicio_id;
                    $candidato_servicio->save();
                }

                $laboratorio = new RegistroIngresoLaboratorio;
                $laboratorio->registro_ingreso_id  = $result->id;
                $laboratorio->laboratorio_medico_id = $request->laboratorio_medico_id;
                $laboratorio->save();

                $seguimiento = new FormularioIngresoSeguimiento;
                $seguimiento->estado_ingreso_id = $request->estado_id;
                $seguimiento->usuario = $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
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
        // $numero_radicados_seiya = formularioGestionIngreso::select('id')->where('n_servicio', '=', $request->n_servicio)->get();
        // $orden_servicio = SolicitudServicioModel::where('numero_radicado', '=', $request->n_servicio)->first();
        // $orden_servicio->numero_radicados_seiya = $numero_radicados_seiya->count();
        // $orden_servicio->save();
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
        // $user = auth()->user();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $lista = $request->all();
        foreach ($lista as $item) {
            $existeIngreso = FormularioIngresoPendientes::where('registro_ingreso_id', $item)->where('usuario_id', $user['id'])->first();

            if (!$existeIngreso) {
                $result = new FormularioIngresoPendientes;
                $result->registro_ingreso_id = $item;
                $result->usuario_id = $user['id'];
                $result->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Tareas pendientes agregadas exitosamente.']);
    }

    public function pendientes2($cantidad)
    {

        // $user = auth()->user();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->LeftJoin('usr_app_formulario_ingreso_pendientes as pen', 'pen.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->where('pen.usuario_id', '=', $user['id'])
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

                $permisos = $this->permisos();
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

            $user = $this->getUserRelaciones();
            $user = $user->getData(true);
            $ids = [];
            array_push($ids, $id);
            $result = formularioGestionIngreso::find($id);
            $estado_id = $request->estado_id;
            $responsable_inicial = str_replace("null", "", $result->responsable);
            $estado_inicial = $result->estado_ingreso_id;

            $permisos = $this->permisos();

            if ($result->responsable_id != null && $result->responsable_id != $user['id'] && !in_array('44', $permisos)) {

                $seguimiento = new FormularioIngresoSeguimiento;
                $seguimiento->estado_ingreso_id = $request->estado_id;
                $seguimiento->usuario = $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
                $seguimiento->formulario_ingreso_id = $id;
                $seguimiento->save();

                return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
            }

            if ($request->subestado_id != null && $request->subestado_id != 1 && ($estado_id == 9)) {
                return response()->json(['status' => 'error', 'message' => 'No se puede cambiar el estado a contratar con documentos faltante o novedades.']);
            }

            $result->concepto_medico_id = $request->concepto_medico_id;
            $result->fecha_ingreso = $request->fecha_ingreo;
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
            $result->correo_notificacion_empresa = $request->correo_empresa;
            $result->direccion_empresa = $request->direccion_empresa;
            $result->direccion_laboratorio = $request->direccion_laboratorio;
            $result->recomendaciones_examen = $request->recomendaciones_examen;
            $result->novedades_examenes = $request->novedades_examenes;
            $result->subsidio_transporte = $request->consulta_subsidio;
            $result->observacion_estado = $request->consulta_categoria_snc;
            $result->correo_laboratorio = $request->correo_laboratorio;
            $result->contacto_empresa = $request->contacto_empresa;
            $result->responsable_id = $request->encargado_id;

            if ($request->historico_concepto_candidatos_id) {
                $historico_concepto = HistoricoConceptosCandidatosModel::find(
                    $request->historico_concepto_candidatos_id
                );
                if ($historico_concepto) {
                    $historico_concepto->concepto = $request->informe_seleccion;
                    $historico_concepto->save();
                }
            } else {
                $candidato = UsuariosCandidatosModel::where('num_doc', $request->numero_identificacion)->first();
                if ($candidato) {
                    $historico_concepto = new HistoricoConceptosCandidatosModel;
                    $historico_concepto->formulario_ingreso_id = $result->id;
                    $historico_concepto->concepto = $request->informe_seleccion;
                    $historico_concepto->candidato_id = $candidato->id;
                    $historico_concepto->tipo = 1;
                    $historico_concepto->save();
                }
            }

            if ($request->subestado_id != null) {
                $result->subestado_id = $request->subestado_id;
            }

            if ($request->proceso_snc_id != 1) {
                $salidaNoConforme = new SalidaNoConformeseiya;
                $salidaNoConforme->proceso_id = $request->proceso_snc_id;
                $salidaNoConforme->categoria_snc_id = $request->categoria_snc_id;
                $salidaNoConforme->responsable_corregir = $request->consulta_encargado_corregir;
                $salidaNoConforme->descripcion = $request->afectacion_servicio;
                $salidaNoConforme->subsanado = 0;
                $salidaNoConforme->orden_servicio_id = $id;
                $salidaNoConforme->save();
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

            $candidato_servicio = CandidatoSolicitudServicioModel::where('id', '=', $result->candidato_servicio_id)->first();
            $estado_candidato = EstadoCandidatoServioOrdenServicioModel::where('estado_orden_servicio_id', '=', $request->estado_id)->first();
            if ($estado_candidato) {
                $candidato_servicio->estado_candidato_id =  $estado_candidato->estado_candidato_servicio_id;
                $candidato_servicio->save();
            }

            $seguimiento = new FormularioIngresoSeguimiento;
            $seguimiento->estado_ingreso_id = $request->estado_id;
            $seguimiento->usuario =  $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
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


            if ($request->concepto_medico_id == 2) {
                $this->asignaNoApto($result);
            } else {
                if ($estado_id != $result->estado_ingreso_id ||  $result->responsable == null) {
                    $this->actualizaestadoingreso($id, $estado_id, $result->responsable_id, $responsable_inicial, $estado_inicial);
                } else {
                    $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
                    $seguimiento_estado->responsable_inicial =  $responsable_inicial;
                    $seguimiento_estado->responsable_final = str_replace("null", "", $result->responsable);
                    $seguimiento_estado->estado_ingreso_inicial = $estado_inicial;
                    $seguimiento_estado->estado_ingreso_final =   $request->estado_id;
                    $seguimiento_estado->formulario_ingreso_id =  $id;
                    $seguimiento_estado->actualiza_registro =   $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
                    $seguimiento_estado->save();
                }
            }
            DB::commit();
            return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $ids]);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor verifique el llenado de todos los campos e intente nuevamente']);
        }
    }


    public function asignaNoApto($result)
    {
        $departamento_id = CandidatoSolicitudServicioModel::leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_candadato_solicitud_servicio.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->where('usr_app_candadato_solicitud_servicio.id', $result->candidato_servicio_id)
            ->select('mun.departamento_id as id')->first();
        if (empty($departamento_id)) {
            $departamento_id = Municipios::join('usr_app_departamentos as dep', 'dep.id', 'usr_app_municipios.departamento_id')
                ->where('usr_app_municipios.id', $result->municipio_id)
                ->select('dep.id')
                ->first();
        }

        //Se envia a Karen Lizeth Peñuela Rodriguez cuando es no apto cambiar en caso de ser necesario
        if (in_array($departamento_id->id, ['8', '16', '11', '33', '22', '17', '18', '10', '32', '2'])) {
            $this->actualizaestadoingreso($result->id, 9, 443);
        } else{
            //Se envia a Guisela Maria Marin Diaz cuando es no apto cambiar en caso de ser necesario
            $this->actualizaestadoingreso($result->id, 9, 30);
        }
    }

    public function actualizasnc(Request $request)
    {

        try {
            DB::beginTransaction();
            $salidasNoConforme = $request->all();
            $user = $this->getUserRelaciones();
            $user = $user->getData(true);

            foreach ($salidasNoConforme as $key => $snc) {
                $salidaNoConforme = SalidaNoConformeseiya::find($snc['id']);
                if ($salidaNoConforme->subsanado == 1) {
                    continue;
                }
                $salidaNoConforme->corrigio_novedad =  $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
                $salidaNoConforme->corrigio_novedad_usuario_id = $user['id'];
                $salidaNoConforme->correcion_aplicada = $request[$key]['correcion_aplicada'];
                if ($salidaNoConforme->correcion_aplicada != "") {
                    $salidaNoConforme->subsanado = 1;
                }
                $salidaNoConforme->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "error al guardar el registro."]);
        }
        DB::commit();
        return response()->json(["status" => "success", "message" => "Registro guardadi exitosamente."]);
    }

    public function sncseiya()
    {
        $result = SalidaNoConformeseiya::join('usr_app_procesos_snc_seiya as pro', 'pro.id', 'usr_app_salida_n_conforme_seiya.proceso_id')
            ->join('usr_app_categorias_snc_seiya as cat', 'cat.id', 'usr_app_salida_n_conforme_seiya.categoria_snc_id')
            ->join('usr_app_formulario_ingreso as fi', 'fi.id', 'usr_app_salida_n_conforme_seiya.orden_servicio_id')
            ->join('usr_app_clientes as cli', 'cli.id', 'fi.cliente_id')
            ->select(
                'usr_app_salida_n_conforme_seiya.id',
                'cli.razon_social',
                'fi.numero_radicado',
                'usr_app_salida_n_conforme_seiya.responsable_corregir',
                'usr_app_salida_n_conforme_seiya.descripcion',
                'usr_app_salida_n_conforme_seiya.subsanado',
                'pro.nombre as proceso',
                'cat.nombre as categoria',
                'usr_app_salida_n_conforme_seiya.created_at',
                'usr_app_salida_n_conforme_seiya.updated_at',
                'usr_app_salida_n_conforme_seiya.correcion_aplicada',
                'usr_app_salida_n_conforme_seiya.corrigio_novedad',
            )
            ->orderby('usr_app_salida_n_conforme_seiya.id', 'DESC')
            ->paginate();

        $result->getCollection()->transform(function ($item) {
            $item->subsanado = $item->subsanado == 1 ? 'Sí' : 'No';
            return $item;
        });

        return response()->json($result);
    }

    public function sncseiyafiltro($cadena, $cantidad, $export = false)
    {
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];



        $query = SalidaNoConformeseiya::join('usr_app_procesos_snc_seiya as pro', 'pro.id', 'usr_app_salida_n_conforme_seiya.proceso_id')
            ->join('usr_app_categorias_snc_seiya as cat', 'cat.id', 'usr_app_salida_n_conforme_seiya.categoria_snc_id')
            ->join('usr_app_formulario_ingreso as fi', 'fi.id', 'usr_app_salida_n_conforme_seiya.orden_servicio_id')
            ->join('usr_app_clientes as cli', 'cli.id', 'fi.cliente_id')
            ->orderby('usr_app_salida_n_conforme_seiya.id', 'DESC');
        if ($export) {
            $query->select(
                'usr_app_salida_n_conforme_seiya.id',
                'cli.razon_social',
                'fi.numero_radicado',
                'usr_app_salida_n_conforme_seiya.responsable_corregir',
                'usr_app_salida_n_conforme_seiya.descripcion',
                'usr_app_salida_n_conforme_seiya.subsanado',
                'pro.nombre as proceso',
                'cat.nombre as categoria',
                'usr_app_salida_n_conforme_seiya.created_at',
                'usr_app_salida_n_conforme_seiya.updated_at',
                'usr_app_salida_n_conforme_seiya.correcion_aplicada',
                'usr_app_salida_n_conforme_seiya.corrigio_novedad'
            );
        } else {
            $query->select(
                'usr_app_salida_n_conforme_seiya.id',
                'cli.razon_social',
                'fi.numero_radicado',
                'usr_app_salida_n_conforme_seiya.responsable_corregir',
                'usr_app_salida_n_conforme_seiya.descripcion',
                'usr_app_salida_n_conforme_seiya.subsanado',
                'pro.nombre as proceso',
                'cat.nombre as categoria',
                'usr_app_salida_n_conforme_seiya.created_at',
                'usr_app_salida_n_conforme_seiya.updated_at',
                'usr_app_salida_n_conforme_seiya.correcion_aplicada',
                'usr_app_salida_n_conforme_seiya.corrigio_novedad'
            );
        }

        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];

            $prefijoCampo = '';
            if ($campoActual === 'razon_social') {
                $prefijoCampo = 'cli.';
                $campoActual = 'razon_social';
            } elseif ($campoActual === 'numero_radicado') {
                $prefijoCampo = 'fi.';
                $campoActual = 'numero_radicado';
            } elseif ($campoActual === 'proceso') {
                $prefijoCampo = 'pro.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'categoria') {
                $prefijoCampo = 'cat.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'subsanado') {
                if (strtolower(trim($valorCompararActual)) == 'si') {
                    $valorCompararActual = 1;
                } else if (strtolower(trim($valorCompararActual)) == 'no') {
                    $valorCompararActual = 0;
                }
            } else {
                $prefijoCampo = 'usr_app_salida_n_conforme_seiya.';
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

        $result = $query->paginate($cantidad);
        $result->getCollection()->transform(function ($item) {
            $item->subsanado = $item->subsanado == 1 ? 'Sí' : 'No';
            return $item;
        });
        if ($export) {
            return $result;
        }
        return response()->json($result);
    }

    public function exportsncseiya($cadena, $cantidad = 10,)
    {
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);


        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];

        $query = SalidaNoConformeseiya::join('usr_app_procesos_snc_seiya as pro', 'pro.id', 'usr_app_salida_n_conforme_seiya.proceso_id')
            ->join('usr_app_categorias_snc_seiya as cat', 'cat.id', 'usr_app_salida_n_conforme_seiya.categoria_snc_id')
            ->join('usr_app_formulario_ingreso as fi', 'fi.id', 'usr_app_salida_n_conforme_seiya.orden_servicio_id')
            ->join('usr_app_clientes as cli', 'cli.id', 'fi.cliente_id')
            ->select(
                'usr_app_salida_n_conforme_seiya.id',
                'cli.razon_social',
                'fi.numero_radicado',
                'usr_app_salida_n_conforme_seiya.responsable_corregir',
                'usr_app_salida_n_conforme_seiya.descripcion',
                'usr_app_salida_n_conforme_seiya.subsanado',
                'pro.nombre as proceso',
                'cat.nombre as categoria',
                'usr_app_salida_n_conforme_seiya.created_at',
                'usr_app_salida_n_conforme_seiya.updated_at',
                'usr_app_salida_n_conforme_seiya.correcion_aplicada',
                'usr_app_salida_n_conforme_seiya.corrigio_novedad',
            )
            ->orderby('usr_app_salida_n_conforme_seiya.id', 'DESC');


        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];

            $prefijoCampo = '';
            if ($campoActual === 'razon_social') {
                $prefijoCampo = 'cli.';
                $campoActual = 'razon_social';
            } elseif ($campoActual === 'numero_radicado') {
                $prefijoCampo = 'fi.';
                $campoActual = 'numero_radicado';
            } elseif ($campoActual === 'proceso') {
                $prefijoCampo = 'pro.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'categoria') {
                $prefijoCampo = 'cat.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'subsanado') {
                if (strtolower(trim($valorCompararActual)) == 'si') {
                    $valorCompararActual = 1;
                } else if (strtolower(trim($valorCompararActual)) == 'no') {
                    $valorCompararActual = 0;
                }
            } else {
                $prefijoCampo = 'usr_app_salida_n_conforme_seiya.';
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

        $result = $query->get();
        $result = $this->sncseiyafiltro($cadena, $cantidad, true);
        return (new SalidasNoConformeExport($result))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
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
        $permisos = $this->permisos();

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
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $permisos = $this->permisos();
        $responsable = User::find($id_encargado);
        $cantidad = count($array);
        $bandera = true;
        if ($cantidad <= 10) {
            DB::beginTransaction();
            foreach ($array as $id) {
                try {
                    $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $id)
                        ->first();
                    if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user['id'] && !in_array('31', $permisos)) {
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
                    $seguimiento_estado->actualiza_registro =   $user['nombres'] . ' ' .  str_replace('null', '', $user->apellidos);

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
            $user = $this->getUserRelaciones();
            $user = $user->getData(true);
            for ($i = 0; $i < count($request->id); $i++) {
                $result = FormularioIngresoPendientes::where('registro_ingreso_id', '=', $request->id[$i])->where('usuario_id', $user['id'])->first();
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function consultaseguimiento($id)
    {
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
                DB::raw("COALESCE(formulario.profesional, '') as profesional"),
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

    public function formularioingresoservicioCandidatoUnico(Request $request, $orden_servicio_candidato_id)
    {
        set_time_limit(0);
        $ordenServicioCandidato = CandidatoSolicitudServicioModel::find($orden_servicio_candidato_id);
        $SolicitudServiciolienteController = new SolicitudServicioController;
        $solicitud_servicio = $SolicitudServiciolienteController->byid($request->id_servicio)->getData();
        $RecepcionEmpleadoController = new RecepcionEmpleadoController;
        $candidato = $RecepcionEmpleadoController->searchByIdOnUsuariosCandidato($ordenServicioCandidato->usuario_id)->getData();
        $nombre_completo = $candidato->primer_nombre . " " . $candidato->primer_apellido;
        DB::beginTransaction();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $responsable_actual =  $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
        try {
            $result = new formularioGestionIngreso;
            $result->eps = $candidato->eps_nombre;
            $result->afp_id = $candidato->afp_id;
            /* $result->correo_notificacion_usuario = $candidato->email; */
            /* $result->tipo_documento_id = $candidato->tip_doc_id; */
            /* $result->numero_contacto = $candidato->celular; */
            $result->cliente_id = $solicitud_servicio->cliente_id;
            $result->cargo = $solicitud_servicio->consulta_cargo_solicitado;
            $result->salario = $solicitud_servicio->salario;
            $result->municipio_id = $solicitud_servicio->ciudad_prestacion_servicio_id;
            $result->estado_ingreso_id = $request->estado_id;
            $result->responsable = $request->nombre_responsable;
            $result->tipo_servicio_id = $solicitud_servicio->linea_servicio_id;
            $result->informe_seleccion = $candidato->concepto;
            $result->profesional = $solicitud_servicio->responsable;
            $result->contacto_empresa = $solicitud_servicio->telefono_contacto;
            $result->responsable_id = $request->responsable_id;
            /*  $result->nombre_completo = $nombre_completo;
            $result->numero_identificacion = $candidato->num_doc; */
            $result->candidato_id = $candidato->usuario_id;
            $result->n_servicio = $solicitud_servicio->numero_radicado;
            $result->candidato_servicio_id = $orden_servicio_candidato_id;
            $result->save();




            $seguimiento = new FormularioIngresoSeguimiento;
            $seguimiento->estado_ingreso_id = $request->estado_id;
            $seguimiento->usuario = $user['nombres'] . ' ' . str_replace("null", "", $user['apellidos']);
            $seguimiento->formulario_ingreso_id = $result->id;
            $seguimiento->save();



            if ($result->responsable == null) {
                $this->actualizaestadoingreso($result->id, $result->estado_ingreso_id, $result->responsable_id, $responsable_actual);
            }
            if ($request->consulta_encargado != null) {
                $this->eventoSocket($request->encargado_id);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar formulario, por favor intente nuevamente']);
        }

        $orden_servicio = SolicitudServicioModel::where('id', '=', $solicitud_servicio->id)->first();
        $numero_radicados_seiya = $orden_servicio->numero_radicados_seiya;
        $orden_servicio->numero_radicados_seiya = $numero_radicados_seiya + 1;
        $orden_servicio->save();
        return response()->json(['status' => '200', 'message' => 'ok']);
    }
}