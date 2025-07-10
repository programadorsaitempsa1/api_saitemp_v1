<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudServicioModel;
use App\Models\CandidatoSolicitudServicioModel;
use App\Models\TipoAsignacionServicioModel;
use App\Models\UsuarioDisponibleServicioModel;
use App\Models\EstadoCandidatoServicioModel;
use App\Models\UsuariosCandidatosModel;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Traits\AutenticacionGuard;
use App\Traits\Permisos;
use Illuminate\Support\Carbon;

class SolicitudServicioController extends Controller
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
        $user = $this->getUserRelaciones();
        $data = $user->getData(true);
        $tipo_usuario = $data['tipo_usuario_id'];
        $nit = null;
        $usuario_id = null;
        if (isset($data['id'])) {
            $usuario_id = $data['id'];
        }
        if (isset($data['nit'])) {
            $nit = $data['nit'];
        }

        $permisos = $this->permisos();

        // Paso 1: Buscar si el usuario es analista
        $facilitadores = DB::table('usr_app_facilitador_analistas')
            ->where('analista_id', $usuario_id)
            ->pluck('facilitador_id');

        $result = SolicitudServicioModel::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_solicitud_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_solicitud_servicio.motivo_servicio_id')
            ->join('usr_app_clientes as cli', 'cli.id', '=', 'usr_app_solicitud_servicio.cliente_id')
            ->when($tipo_usuario == '2', function ($query) use ($nit) {
                return $query->where('usr_app_solicitud_servicio.nit', $nit);
            })
            ->when($tipo_usuario == '1' && !in_array('46', $permisos), function ($query) use ($usuario_id) {
                return $query->where('usr_app_solicitud_servicio.responsable_id', $usuario_id);
            });

        // Paso 2: Si el usuario es analista y tiene facilitadores asociados, agregar filtro adicional
        if (!$facilitadores->isEmpty()) {
            $result->orWhere(function ($query) use ($facilitadores) {
                $query->whereIn('usr_app_solicitud_servicio.responsable_id', $facilitadores);
            });
        }

        // SELECTS por tipo de usuario
        if ($tipo_usuario == '1') {
            $result->select(
                'usr_app_solicitud_servicio.id',
                'usr_app_solicitud_servicio.numero_radicado',
                'usr_app_solicitud_servicio.created_at',
                'cli.razon_social',
                'usr_app_solicitud_servicio.nit',
                'usr_app_solicitud_servicio.responsable as responsable_servicio',
                'usr_app_solicitud_servicio.radicador',
                'usr_app_solicitud_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ms.nombre as motivo_servicio',
                'usr_app_solicitud_servicio.cantidad_contrataciones',
            );
        }

        if ($tipo_usuario == '2') {
            $result->select(
                'usr_app_solicitud_servicio.id',
                'usr_app_solicitud_servicio.numero_radicado',
                'usr_app_solicitud_servicio.created_at',
                'usr_app_solicitud_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ms.nombre as motivo_servicio',
                'usr_app_solicitud_servicio.cantidad_contrataciones',
            );
        }

        $result = $result->orderBy('usr_app_solicitud_servicio.id', 'DESC')
            ->paginate($cantidad);

        return response()->json($result);
    }


    public function byid($id)
    {
        $result = SolicitudServicioModel::leftJoin('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_solicitud_servicio.linea_servicio_id')
            ->leftJoin('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_solicitud_servicio.motivo_servicio_id')
            ->leftJoin('usr_app_estado_servicio as est', 'est.id', '=', 'usr_app_solicitud_servicio.estado_servicio_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', '=', 'usr_app_solicitud_servicio.ciudad_prestacion_servicio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
            ->leftJoin('usr_app_lista_cargos as car', 'car.id', '=', 'usr_app_solicitud_servicio.cargo_solicitado_id')
            ->where('usr_app_solicitud_servicio.id', '=', $id)
            ->select(
                'usr_app_solicitud_servicio.id',
                'usr_app_solicitud_servicio.cliente_id',
                'usr_app_solicitud_servicio.numero_radicado as radicado',
                'usr_app_solicitud_servicio.created_at',
                'usr_app_solicitud_servicio.radicador',
                'usr_app_solicitud_servicio.fecha_fin',
                'usr_app_solicitud_servicio.fecha_acuerdo_fin',
                'ts.nombre_servicio as linea_servicio',
                'ts.id as linea_servicio_id',
                'ts.clave as linea_servicio_clave',
                'ms.nombre as motivo_servicio',
                'usr_app_solicitud_servicio.motivo_servicio_id',
                'usr_app_solicitud_servicio.cantidad_contrataciones',
                'usr_app_solicitud_servicio.nombre_contacto',
                'usr_app_solicitud_servicio.telefono_contacto',
                'usr_app_solicitud_servicio.cargo_contacto',
                'usr_app_solicitud_servicio.responsable',
                'usr_app_solicitud_servicio.numero_radicado',
                'est.nombre as estado_servicio',
                'est.clave as estado_servicio_clave',
                'usr_app_solicitud_servicio.estado_servicio_id',
                'usr_app_solicitud_servicio.motivo_cancelacion',
                'usr_app_solicitud_servicio.descripcion_cancelacion',
                'usr_app_solicitud_servicio.usuario_cancela',
                'usr_app_solicitud_servicio.fecha_cancelacion',
                'mun.nombre as municipio_prestacion_servicio',
                'usr_app_solicitud_servicio.ciudad_prestacion_servicio_id',
                'dep.nombre as departamento_prestacion_servicio',
                'dep.id as departamento_prestacion_servicio_id',
                'car.nombre as consulta_cargo_solicitado',
                'usr_app_solicitud_servicio.cargo_solicitado_id',
                'usr_app_solicitud_servicio.direccion_presentacion',
                'usr_app_solicitud_servicio.fecha_hora_ingreso',
                'usr_app_solicitud_servicio.salario',
                'usr_app_solicitud_servicio.subsidio_transporte',

            )->first();
        $candidatos = CandidatoSolicitudServicioModel:: //LeftJoin('usr_app_usuarios as us', 'us.id', 'usr_app_candadato_solicitud_servicio.usuario_id')
            // ->leftJoin('usr_app_candidatos_c as can', 'can.usuario_id', 'usr_app_candadato_solicitud_servicio.usuario_id')
            leftjoin('gen_tipide as ti', 'ti.cod_tip', '=', 'usr_app_candadato_solicitud_servicio.tipo_identificacion_id')
            ->leftjoin('usr_app_estado_candidato_servicio as est', 'est.id', '=', 'usr_app_candadato_solicitud_servicio.estado_candidato_id')
            ->leftjoin('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_candadato_solicitud_servicio.ciudad_prestacion_servicio_id')
            ->leftjoin('usr_app_departamentos as dep', 'dep.id', '=', 'ciu.departamento_id')
            ->leftjoin('usr_app_lista_cargos as car', 'car.id', '=', 'usr_app_candadato_solicitud_servicio.cargo_solicitado_id')
            ->where('usr_app_candadato_solicitud_servicio.servicio_id', '=', $id,)
            ->select(
                'usr_app_candadato_solicitud_servicio.id as candidato_id',
                'usr_app_candadato_solicitud_servicio.servicio_id',
                'usr_app_candadato_solicitud_servicio.usuario_id',
                'usr_app_candadato_solicitud_servicio.direccion_prestacion_servicio as direccion_presentacion',
                'usr_app_candadato_solicitud_servicio.fecha_ingreso',
                'usr_app_candadato_solicitud_servicio.salario',
                'usr_app_candadato_solicitud_servicio.subsidio_transporte',
                'usr_app_candadato_solicitud_servicio.nombres_candidato as nombre_candidato',
                'usr_app_candadato_solicitud_servicio.apellidos_candidato as apellido_candidato',
                'usr_app_candadato_solicitud_servicio.celular_candidato',
                'usr_app_candadato_solicitud_servicio.correo_candidato',
                'usr_app_candadato_solicitud_servicio.numero_documento_identidad as numero_documento_candidato',
                'usr_app_candadato_solicitud_servicio.numero_documento_identidad as confirma_numero_documento_candidato',
                'usr_app_candadato_solicitud_servicio.tipo_identificacion_id',
                'usr_app_candadato_solicitud_servicio.subsidio_transporte',
                'usr_app_candadato_solicitud_servicio.hora_ingreso',
                'usr_app_candadato_solicitud_servicio.cargo_solicitado_id',
                'car.nombre as cargo_solicitado',
                'usr_app_candadato_solicitud_servicio.whatsapp',
                'ciu.nombre as ciudad_prestacion_servicio',
                'ciu.id as ciudad_prestacion_servicio_id',
                'dep.nombre as departamento_prestacion_servicio',
                'ti.des_tip as tipo_identificacion',
                'est.id as estado_candidato_id',
                'est.clave as estado_candidato_clave',
                'est.nombre as estado_candidato',
            )->get();
        $candidatos->transform(function ($item) {
            $item->registrado = !empty($item->usuario_id) ? 1 : 0;
            return $item;
        });

        $cliente_responsable = new AsignacionServicioController();
        $responsable = $cliente_responsable->responsableservicio($result->cliente_id);

        $result['candidatos'] =  $candidatos;
        $result->actividad_ciiu = $responsable->actividad_ciiu;
        $result->nit = $responsable->nit;
        $result->razon_social = $responsable->razon_social;
        $result->sector_economico = $responsable->sector_economico;
        $result->sector_economico_id = $responsable->sector_economico_id;
        $result->cliente_id = $result->cliente_id;
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
        $result = TipoAsignacionServicioModel::join('usr_app_asignacion_servicio as as', 'as.id', 'usr_app_tipo_asignacion_servicio.asignacion_servicio_id')
            ->join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', 'usr_app_tipo_asignacion_servicio.linea_servicio_id')
            ->where('usr_app_tipo_asignacion_servicio.linea_servicio_id', '=', $request->linea_servicio_id)
            ->where('as.checked', '=', 1)
            ->select()->first();
        $tipo_responsable = $result['rol_usuario_interno_id'];
        $asignacion_manual = $result['manual'];
        try {
            $user = $this->getUserRelaciones();
            $data = $user->getData(true);
            $ordenServicio = new SolicitudServicioModel;
            $ordenServicio->tipo_usuario = $data['tipo_usuario_id'];
            $ordenServicio->usuario_id = $data['usuario_id'];
            $ordenServicio->radicador = $data['nombres'];
            $ordenServicio->cliente_id = $request->cliente_id;
            $ordenServicio->nit = $request->nit;
            $ordenServicio->fecha_fin = $request->fecha_fin;
            $ordenServicio->linea_servicio_id = $request->linea_servicio_id;
            $ordenServicio->nombre_contacto = $request->nombre_contacto;
            $ordenServicio->telefono_contacto = $request->telefono_contacto;
            $ordenServicio->cargo_contacto = $request->cargo_contacto;
            $ordenServicio->motivo_servicio_id = $request->motivo_servicio_id;
            $ordenServicio->cantidad_contrataciones = $request->cantidad_contrataciones;
            $ordenServicio->fecha_acuerdo_fin = $request->fecha_acuerdo_fin;
            $ordenServicio->estado_servicio_id = 1;
            $ordenServicio->cargo_solicitado_id = $request->cargo_solicitado_id;
            $ordenServicio->ciudad_prestacion_servicio_id = $request->ciudad_prestacion_servicio_id;
            $ordenServicio->direccion_presentacion = $request->direccion_presentacion;
            $ordenServicio->fecha_hora_ingreso = Carbon::parse($request->fecha_hora_ingreso)->format('d-m-Y H:i:s');
            $ordenServicio->salario = $request->salario;
            $ordenServicio->subsidio_transporte = $request->subsidio_transporte;
            if ($data['tipo_usuario_id'] == '2') { // asignación de servicios cuando lo registra un cliente
                $responsable = $this->asignacionAutomatica($tipo_responsable, $request->sector_economico);
                if (isset($responsable[0]) && $responsable[0] === 'error') {
                    return response()->json(["status" => "error", "message" => "Los responsables no están parametrizados."]);
                }
                $ordenServicio->responsable_id = $responsable['responsable_id'];
                $ordenServicio->responsable = $responsable['responsable'];
            } else if ($data['tipo_usuario_id'] == '1') {  // asignación de servicios cuando lo registra un usuario interno de saitemp

                if ($asignacion_manual == 1) {
                    $ordenServicio->responsable_id = $request->responsable_id;
                    $ordenServicio->responsable = $request->responsable;
                } else {
                    $responsable = $this->asignacionAutomatica($tipo_responsable, $request->sector_economico);
                    if (isset($responsable[0]) && $responsable[0] === 'error') {
                        return response()->json(["status" => "error", "message" => "Los responsables no están parametrizados."]);
                    }
                    $ordenServicio->responsable_id = $responsable['responsable_id'];
                    $ordenServicio->responsable = $responsable['responsable'];
                }
            }
            $ordenServicio->save();
            $cantidad_errores = 0;
            $numeros_documento = '';
            $correos_candidatos = '';
            $valida_candidato = new RecepcionEmpleadoController();
            foreach ($request['candidatos'] as $candidato) {

                if (!empty($candidato['numero_documento_candidato']) || !empty($candidato['tipo_identificacion_id'])) {
                    $candidato_validado = $valida_candidato->validacandidato($candidato['numero_documento_candidato'], 0, $candidato['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                }
                if (isset($candidato_validado)) {
                    if ($candidato_validado['status'] == 'error') {
                        return;
                    }
                }
                if ($candidato['registrado'] == 0) {
                    $result = $this->candidatoNoRegistradoServicio($candidato, $ordenServicio->id);
                } else if ($candidato['registrado'] == 1) {
                    $result = $this->candidatoRegistradoServicio($candidato, $ordenServicio->id);
                }
            }

            DB::commit();
            if ($correos_candidatos != '') {
                return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente, sin embargo los candidatos con correo electrónico. $correos_candidatos. no pudieron ser registrados ya que el correo se encuentra en uso por otro usuario.", 'id' => $ordenServicio->id]);
            }
            if ($cantidad_errores > 0) {
                return response()->json(["status" => "success", "message" => "El formulario fue guardado exitosamente, pero los candidatos con numero de documento $numeros_documento no pudieron ser registrados, para más información, por favor comuniquese con un asesor.", 'id' => $ordenServicio->id]);
            }
            return response()->json(["status" => "success",  'titulo' => 'Formulario guardado', "message" => "Formulario guardado exitosamente.", 'id' => $ordenServicio->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(["status" => "error", "message" => "Error al guadar los datos del formulario."]);
        }
    }

    public function responsable(Request $request)
    {

        DB::beginTransaction();
        try {
            foreach ($request->registros as $id) {
                $result = SolicitudServicioModel::find($id);
                $result->responsable_id = $request->responsable_id;
                $result->responsable = $request->responsable;
                $result->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Hubo un error al asignar un nuevo responsable, por favor intente nuevamente."]);
        }
        DB::commit();
        return response()->json(["status" => "success", "message" => "Asignación realizada de manera exitosa."]);
    }

    public function candidatoRegistradoServicio(array $candidato, string $ordenServicio_id, $fromCandidato = false)
    {

        try {
            if (!empty($candidato['usuario_id']) && !$fromCandidato) {
                $candidato_servicio = CandidatoSolicitudServicioModel::where('id', $candidato['candidato_id'])->first();
            } else if (empty($candidato['usuario_id']) || $fromCandidato) {
                $candidato_servicio = new CandidatoSolicitudServicioModel;
            }
            $candidato_servicio->servicio_id = $ordenServicio_id;
            $candidato_servicio->en_proceso = 0;
            $candidato_servicio->usuario_id = $candidato['usuario_id'];
            $candidato_servicio->tipo_identificacion_id = $candidato['tipo_identificacion_id'] ?? null;
            $candidato_servicio->numero_documento_identidad = $candidato['numero_documento_candidato'];
            $candidato_servicio->nombres_candidato = $candidato['nombre_candidato'];
            $candidato_servicio->apellidos_candidato = $candidato['apellido_candidato'];
            $candidato_servicio->celular_candidato = $candidato['celular_candidato'];
            $candidato_servicio->correo_candidato = $candidato['correo_candidato'];
            $candidato_servicio->salario = $candidato['salario'];
            $candidato_servicio->subsidio_transporte = $candidato['subsidio_transporte'];
            $candidato_servicio->direccion_prestacion_servicio = $candidato['direccion_presentacion'];
            $candidato_servicio->hora_ingreso = $candidato['hora_ingreso'];
            $candidato_servicio->fecha_ingreso = Carbon::parse($candidato['fecha_ingreso'])->format('Y-d-m');
            $candidato_servicio->cargo_solicitado_id = $candidato['cargo_solicitado_id'];
            $candidato_servicio->ciudad_prestacion_servicio_id = $candidato['ciudad_prestacion_servicio_id'];
            $candidato_servicio->whatsapp = $candidato['whatsapp'];
            $candidato_servicio->save();
            if ($fromCandidato) {
                $candidato_servicio->cargo_solicitado_id = $candidato['cargo_solicitado_id'];
                $candidato_servicio->estado_candidato_id = $candidato['estado_candidato_id'];
                $candidato_servicio->save();
            }
            DB::commit();
            if ($fromCandidato) {
                return $candidato_servicio->id;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    public function candidatoNoRegistradoServicio(array $usuario, string $ordenServicio_id)
    {
        try {
            DB::beginTransaction();
            $rol = Rol::select('id')->where('clave', 'candidato')->first();
            // Se registra el usuario que no está creado en la tabla de login
            if (!empty($usuario['tipo_identificacion_id'] ?? null) && !empty($usuario['numero_documento_candidato']) && !empty($usuario['correo_candidato'])) {
                $user = new User;
                $user->email = $usuario['correo_candidato'];
                $user->password =  bcrypt($usuario['correo_candidato']);
                $user->confirma_correo =  1;
                $user->rol_id = $rol->id;
                $user->tipo_usuario_id = 3;
                $user->save();
                // Se registran otros datos de usuario en la tabla de candidatos
                $candidato = new UsuariosCandidatosModel;
                $candidato->usuario_id =  $user->id;
                if (!empty($usuario['nombre_candidato'])) {
                    $nombres = explode(" ", $usuario['nombre_candidato']);
                    $candidato->primer_nombre =  $nombres[0];
                    $candidato->segundo_nombre = isset($nombres[1]) ? $nombres[1] : '';
                }
                if (!empty($usuario['apellido_candidato'])) {
                    $apellidos =  explode(" ", $usuario['apellido_candidato']);
                    $candidato->primer_apellido =  $apellidos[0];
                    $candidato->segundo_apellido =   isset($apellidos[1]) ? $apellidos[1] : '';
                }
                if (!empty($usuario['numero_documento_candidato'])) {
                    $candidato->num_doc = $usuario['numero_documento_candidato'];
                }
                if (!empty($usuario['celular_candidato'])) {
                    $candidato->celular = $usuario['celular_candidato'];
                }
                if (!empty($usuario['tipo_identificacion_id'] ?? null)) {
                    $candidato->tip_doc_id = $usuario['tipo_identificacion_id'];
                }
                $candidato->save();
            }
            // Se relaciona el usuario creado con el nuevo servicio creado
            $estado_candidato = EstadoCandidatoServicioModel::select('id')->where('clave', 'radicado')->first();
            if (!empty($usuario['candidato_id'] ?? null)) {
                $candidato = CandidatoSolicitudServicioModel::find($usuario['candidato_id']);
            } else {
                $candidato = new CandidatoSolicitudServicioModel;
            }
            if (!empty($usuario['tipo_identificacion_id'] ?? null) && !empty($usuario['numero_documento_candidato']) && !empty($usuario['correo_candidato'])) {
                $candidato->usuario_id =  $user->id;
            }
            $candidato->servicio_id = $ordenServicio_id;
            $candidato->en_proceso = 0;
            $candidato->estado_candidato_id = $estado_candidato->id;
            $candidato->tipo_identificacion_id = $usuario['tipo_identificacion_id'] ?? null;
            $candidato->numero_documento_identidad = $usuario['numero_documento_candidato'];
            $candidato->nombres_candidato = $usuario['nombre_candidato'];
            $candidato->apellidos_candidato = $usuario['apellido_candidato'];
            $candidato->celular_candidato = $usuario['celular_candidato'];
            $candidato->correo_candidato = $usuario['correo_candidato'];
            $candidato->salario = $usuario['salario'];
            $candidato->hora_ingreso = $usuario['hora_ingreso'];
            $candidato->subsidio_transporte = $usuario['subsidio_transporte'];
            $candidato->direccion_prestacion_servicio = $usuario['direccion_presentacion'];
            $candidato->fecha_ingreso = Carbon::parse($usuario['fecha_ingreso'])->format('Y-d-m');
            $candidato->cargo_solicitado_id = $usuario['cargo_solicitado_id'];
            $candidato->ciudad_prestacion_servicio_id = $usuario['ciudad_prestacion_servicio_id'];
            $candidato->whatsapp = $usuario['whatsapp'];
            $candidato->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    public function asignacionAutomatica($tipo_responsable, $sector_economico_id)
    {
        $usuarios = UsuarioDisponibleServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_usuarios_disponibles_servicio.usuario_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->where('usr_app_usuarios_disponibles_servicio.rol_usuario_interno_id', '=', $tipo_responsable)
            ->when($tipo_responsable == 4, function ($query) {
                return $query->join('usr_app_sector_economico_profesional as sep', 'sep.profesional_id', 'us.id');
            })
            ->when($tipo_responsable == 4, function ($query) use ($sector_economico_id) {
                return $query->where('sep.sector_economico_id', '=', $sector_economico_id);
            })
            ->select(
                'usr_app_usuarios_disponibles_servicio.id',
                'usr_app_usuarios_disponibles_servicio.usuario_id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos) AS nombres")

            )->get();
        $numeroResponsables = $usuarios->count();
        if ($numeroResponsables === 0) {
            return ["error"];
        }
        $orden_servicio = SolicitudServicioModel::select()->orderby('id', 'DESC')
            ->first();

        if (!$orden_servicio) {
            $orden_servicio = (object) ['id' => 0]; // Evita el error asignando un objeto con id 0
        }

        $indiceResponsable = $orden_servicio->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable];


        $ordenServicio['responsable_id'] = $responsable->usuario_id;
        $ordenServicio['responsable'] =  $responsable->nombres;
        return $ordenServicio;
    }

    public function filtro($cadena, $cantidad)
    {
        try {
            $cadenaJSON = base64_decode($cadena);
            $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
            $arrays = explode('/', $cadenaUTF8);
            $arraysDecodificados = array_map('json_decode', $arrays);

            $campo = $arraysDecodificados[0];
            $operador = $arraysDecodificados[1];
            $valor_comparar = $arraysDecodificados[2];
            $valor_comparar2 = $arraysDecodificados[3];

            // return $campo;

            $user = $this->getUserRelaciones();
            $data = $user->getData(true);
            $tipo_usuario = $data['tipo_usuario_id'];
            $nit = null;
            $usuario_id = null;
            if (isset($data['id'])) {
                $usuario_id = $data['id'];
            }
            if (isset($data['nit'])) {
                $nit = $data['nit'];
            }

            $permisos = $this->permisos();

            $query = SolicitudServicioModel::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_solicitud_servicio.linea_servicio_id')
                ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_solicitud_servicio.motivo_servicio_id')
                ->join('usr_app_clientes as cli', 'cli.id', '=', 'usr_app_solicitud_servicio.cliente_id')
                ->when($tipo_usuario == '2', function ($query) use ($nit) {
                    return $query->where('usr_app_solicitud_servicio.nit', $nit);
                })
                ->when($tipo_usuario == '1' && !in_array('46', $permisos), function ($query) use ($usuario_id) {
                    return $query->where('usr_app_solicitud_servicio.responsable_id', $usuario_id);
                });
            if ($tipo_usuario == '1') {
                $query->select(
                    'usr_app_solicitud_servicio.id',
                    'usr_app_solicitud_servicio.numero_radicado',
                    'usr_app_solicitud_servicio.created_at',
                    'cli.razon_social',
                    'usr_app_solicitud_servicio.nit',
                    'usr_app_solicitud_servicio.responsable as responsable_servicio',
                    'usr_app_solicitud_servicio.radicador',
                    'usr_app_solicitud_servicio.fecha_fin',
                    'ts.nombre_servicio as linea_servicio',
                    'ms.nombre as motivo_servicio',
                    'usr_app_solicitud_servicio.cantidad_contrataciones',
                );
            }
            if ($tipo_usuario == '2') {
                $query->select(
                    'usr_app_solicitud_servicio.id',
                    'usr_app_solicitud_servicio.numero_radicado',
                    'usr_app_solicitud_servicio.created_at',
                    'usr_app_solicitud_servicio.fecha_fin',
                    'ts.nombre_servicio as linea_servicio',
                    'ms.nombre as motivo_servicio',
                    'usr_app_solicitud_servicio.cantidad_contrataciones',
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
                } elseif ($campoActual === 'linea_servicio') {
                    $prefijoCampo = 'ts.';
                    $campoActual = 'nombre_servicio';
                } elseif ($campoActual === 'motivo_servicio') {
                    $prefijoCampo = 'ms.';
                    $campoActual = 'nombre';
                } elseif ($campoActual === 'responsable_servicio') {
                    $prefijoCampo = 'usr_app_solicitud_servicio.';
                    $campoActual = 'responsable';
                } else {
                    $prefijoCampo = 'usr_app_solicitud_servicio.';
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
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function ordenservicioseiya($id)
    {

        $result = CandidatoSolicitudServicioModel::leftJoin('usr_app_solicitud_servicio as ss', 'ss.id', '=', 'usr_app_candadato_solicitud_servicio.servicio_id')
            ->LeftJoin('usr_app_clientes as cli', 'cli.id', '=', 'ss.cliente_id')
            ->join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'ss.linea_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_candadato_solicitud_servicio.ciudad_prestacion_servicio_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'ciu.departamento_id')
            ->join('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_candadato_solicitud_servicio.cargo_solicitado_id')
            ->where('ss.id', '=', $id)
            ->select(
                'ss.id',
                'ss.numero_radicado',
                'ts.nombre_servicio as linea_servicio',
                'ts.id as linea_servicio_id',
                'ciu.nombre as ciudad_prestacion_servicio',
                'ciu.id as ciudad_prestacion_servicio_id',
                'dep.nombre as departamento_prestacion_servicio',
                'dep.id as departamento_prestacion_servicio_id',
                'ss.cliente_id',
                'cli.razon_social',
                'usr_app_candadato_solicitud_servicio.salario',
                'usr_app_candadato_solicitud_servicio.subsidio_transporte',
                'lc.nombre as cargo_solicitado',
                'lc.id as cargo_solicitado_id',
                'usr_app_candadato_solicitud_servicio.fecha_ingreso',
                'ss.responsable',
                'ss.responsable_id',
                'ss.cantidad_contrataciones',
                'usr_app_candadato_solicitud_servicio.direccion_prestacion_servicio'
            )
            ->first();
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
            $ordenServicio = SolicitudServicioModel::find($id);
            $ordenServicio->fecha_acuerdo_fin = $request->fecha_acuerdo_fin;
            $ordenServicio->save();


            $candidatos_con_servicio = '';
            $result = '';
            $valida_candidato = new RecepcionEmpleadoController();
            foreach ($request['candidatos'] as $candidato) {

                if (!empty($candidato['numero_documento_candidato']) || !empty($candidato['tipo_identificacion_id'])) {
                    $candidato_validado = $valida_candidato->validacandidato($candidato['numero_documento_candidato'], 0, $candidato['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                }
                if (isset($candidato_validado)) {
                    if ($candidato_validado['status'] == 'error') {
                        $candidatos_con_servicio .= $candidato['confirma_numero_documento_candidato'] . ', ';
                    }
                }
                if ($candidato['registrado'] == 0) {
                    $result = $this->candidatoNoRegistradoServicio($candidato, $ordenServicio->id);
                } else if ($candidato['registrado'] == 1) {
                    $result = $this->candidatoRegistradoServicio($candidato, $ordenServicio->id);
                }
            }

            DB::commit();
            if ($candidatos_con_servicio != '') {
                return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente, sin embargo los candidatos con numero de doucmento de identidad $candidatos_con_servicio no pudieron ser registrados, por favor comuniquese con su asesor para mas información.", 'id' => $ordenServicio->id]);
            }
            return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente", 'id' => $ordenServicio->id]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(["status" => "error", "message" => "Error al guadar los datos del formulario"]);
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

    public function ordenServicioSeleccionbyPro()
    {

        $user = $this->getUserRelaciones();
        $data = $user->getData(true);
        $tipo_usuario = $data['tipo_usuario_id'];
        $nit = null;
        $usuario_id = null;
        if (isset($data['id'])) {
            $usuario_id = $data['id'];
        }
        if (isset($data['nit'])) {
            $nit = $data['nit'];
        }

        $permisos = $this->permisos();

        $result = SolicitudServicioModel::leftjoin('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_solicitud_servicio.linea_servicio_id')
            ->leftjoin('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_solicitud_servicio.motivo_servicio_id')
            ->leftjoin('usr_app_clientes as cli', 'cli.id', '=', 'usr_app_solicitud_servicio.cliente_id')
            ->leftjoin('usr_app_lista_cargos as car', 'car.id', '=', 'usr_app_solicitud_servicio.cargo_solicitado_id')
            ->when($tipo_usuario == '2', function ($query) use ($nit) {
                return $query->where('usr_app_solicitud_servicio.nit', $nit);
            })
            ->when($tipo_usuario == '1' && !in_array('46', $permisos), function ($query) use ($usuario_id) {
                return $query->where('usr_app_solicitud_servicio.responsable_id', $usuario_id);
            })->where('ts.clave', 'seleccion');
        if ($tipo_usuario == '1') {
            $result->select(
                'usr_app_solicitud_servicio.id',
                DB::raw("CONCAT(usr_app_solicitud_servicio.numero_radicado, ' - ', car.nombre) as numero_radicado"),
                'usr_app_solicitud_servicio.created_at',
                'cli.razon_social',
                'usr_app_solicitud_servicio.nit',
                'usr_app_solicitud_servicio.responsable as responsable_servicio',
                'usr_app_solicitud_servicio.radicador',
                'usr_app_solicitud_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ms.nombre as motivo_servicio',
                'usr_app_solicitud_servicio.cantidad_contrataciones',
                'car.nombre as cargo_solicitado'
            );
        }
        $result = $result->orderBy('usr_app_solicitud_servicio.id', 'DESC')->get();

        return response()->json($result);
    }
}