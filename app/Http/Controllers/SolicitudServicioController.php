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

        $result = SolicitudServicioModel::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_solicitud_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_solicitud_servicio.motivo_servicio_id')
            ->when($tipo_usuario == '2', function ($query) use ($nit) {
                return $query->where('usr_app_solicitud_servicio.nit', $nit);
            })
            ->when($tipo_usuario == '1' && !in_array('46', $permisos), function ($query) use ($usuario_id) {
                return $query->where('usr_app_solicitud_servicio.responsable_id', $usuario_id);
            })
            ->select(
                'usr_app_solicitud_servicio.id',
                'usr_app_solicitud_servicio.numero_radicado',
                'usr_app_solicitud_servicio.created_at',
                'usr_app_solicitud_servicio.radicador',
                'usr_app_solicitud_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ms.nombre as motivo_servicio',
                'usr_app_solicitud_servicio.cantidad_contrataciones',
            )
            ->orderby('usr_app_solicitud_servicio.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function byid($id)
    {

        $result = SolicitudServicioModel::leftJoin('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_solicitud_servicio.linea_servicio_id')
            ->leftJoin('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_solicitud_servicio.motivo_servicio_id')
            ->leftJoin('usr_app_estado_servicio as est', 'est.id', '=', 'usr_app_solicitud_servicio.estado_servicio_id')
            ->where('usr_app_solicitud_servicio.id', '=', $id)
            ->select(
                'usr_app_solicitud_servicio.id',
                'usr_app_solicitud_servicio.cliente_id',
                'usr_app_solicitud_servicio.numero_radicado as radicado',
                'usr_app_solicitud_servicio.created_at',
                'usr_app_solicitud_servicio.radicador',
                'usr_app_solicitud_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ts.id as linea_servicio_id',
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
                // 'usr_app_candadato_solicitud_servicio.salario as salario_candidato',
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
                // DB::RAW("CONCAT(can.primer_nombre,' ',can.segundo_nombre) as nombre_candidato"),
                // DB::RAW("CONCAT(can.primer_apellido,' ',can.segundo_apellido) as apellido_candidato"),
                // 'can.num_doc as numero_documento_candidato',
                // 'can.num_doc as confirma_numero_documento_candidato',
                // 'can.celular as celular_candidato',
                // 'us.email as correo_candidato',
                // 'can.tip_doc_id as tipo_identificacion_id',
                // 'ti.des_tip as consulta_tipo_identificacion',
                // 'usr_app_candadato_solicitud_servicio.en_proceso',
                // 'est.id as estado_candidato_id',
                // 'est.nombre as estado_candidato',
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

    public function candidatoRegistradoServicio(array $candidato, string $ordenServicio_id)
    {

        try {
            if (!empty($candidato['usuario_id'])) {
                $candidato_servicio = CandidatoSolicitudServicioModel::where('id', $candidato['candidato_id'])->first();
            } else {
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
            $candidato_servicio->fecha_ingreso = Carbon::parse($candidato['fecha_ingreso'])->format('Y-d-m H:i:s');
            $candidato_servicio->cargo_solicitado_id = $candidato['cargo_solicitado_id'];
            $candidato_servicio->ciudad_prestacion_servicio_id = $candidato['ciudad_prestacion_servicio_id'];
            $candidato_servicio->whatsapp = $candidato['whatsapp'];
            $candidato_servicio->save();
            DB::commit();
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
            $candidato->subsidio_transporte = $usuario['subsidio_transporte'];
            $candidato->direccion_prestacion_servicio = $usuario['direccion_presentacion'];
            $candidato->fecha_ingreso = Carbon::parse($usuario['fecha_ingreso'])->format('Y-d-m H:i:s');
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

    public function filtro($cadena)
    {
        try {
            $cadenaJSON = base64_decode($cadena);
            $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
            $valores = explode("/", $cadenaUTF8);
            $campo = $valores[0];
            $operador = $valores[1];
            $valor = $valores[2];
            $valor2 = isset($valores[3]) ? $valores[3] : null;

            $user = $this->getUserRelaciones();
            $data = $user->getData(true);
            $tipo_usuario = $data['tipo_usuario_id'];
            $nit = null;
            if (isset($data['nit'])) {
                $nit = $data['nit'];
            }

            $query = SolicitudServicioModel::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
                ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
                ->when($tipo_usuario == '2' && $nit != null, function ($query) use ($nit) {
                    return $query->where('usr_app_orden_servicio.nit', $nit);
                })
                ->select(
                    'usr_app_orden_servicio.id',
                    'usr_app_orden_servicio.numero_radicado',
                    'usr_app_orden_servicio.created_at',
                    'usr_app_orden_servicio.radicador',
                    'usr_app_orden_servicio.fecha_inicio',
                    'usr_app_orden_servicio.fecha_fin',
                    'ts.nombre_servicio as linea_servicio',
                    'ms.nombre as motivo_servicio',
                    'usr_app_orden_servicio.cantidad_contrataciones',
                    'usr_app_orden_servicio.salario',
                )
                ->orderby('usr_app_orden_servicio.id', 'DESC');

            switch ($operador) {
                case 'Contiene':
                    if ($campo == "linea_servicio") {
                        $query->where('ts.nombre_servicio', 'like', '%' . $valor . '%');
                    } else if ($campo == "ciudad_prestacion_servicio") {
                        $query->where('ciu.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "motivo_servicio") {
                        $query->where('ms.nombre', 'like', '%' . $valor . '%');
                    } else {
                        $query->where('usr_app_orden_servicio.' . $campo, 'like', '%' . $valor . '%');
                    }
                    break;
                case 'Igual a':
                    if ($campo == "linea_servicio") {
                        $query->where('ts.nombre_servicio', '=', $valor);
                    } else if ($campo == "ciudad_prestacion_servicio") {
                        $query->where('ciu.nombre', '=', $valor);
                    } else if ($campo == "motivo_servicio") {
                        $query->where('ms.nombre', '=', $valor);
                    } else {
                        $query->where('usr_app_orden_servicio.' . $campo, '=', $valor);
                    }
                    break;
                case 'Igual a fecha':
                    $query->whereDate('usr_app_orden_servicio.' . $campo, '=', $valor);
                    break;
                case 'Entre':
                    $query->whereDate('usr_app_orden_servicio.' . $campo, '>=', $valor)
                        ->whereDate('usr_app_orden_servicio.' . $campo, '<=', $valor2);
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function ordenservicioseiya($id)
    {
        return 'prueba2';
        $result = SolicitudServicioModel::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            // ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            // ->join('usr_app_departamentos as dep', 'dep.id', '=', 'ciu.departamento_id')
            ->join('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_orden_servicio.cargo_solicitado_id')
            ->where('usr_app_orden_servicio.id', '=', $id)
            ->select(
                'usr_app_orden_servicio.id',
                'usr_app_orden_servicio.numero_radicado',
                'ts.nombre_servicio as linea_servicio',
                'usr_app_orden_servicio.linea_servicio_id',
                // 'ciu.nombre as ciudad_prestacion_servicio',
                // 'ciu.id as ciudad_prestacion_servicio_id',
                // 'dep.nombre as departamento_prestacion_servicio',
                // 'dep.id as departamento_prestacion_servicio_id',
                'usr_app_orden_servicio.razon_social',
                'usr_app_orden_servicio.cliente_id',
                'usr_app_orden_servicio.salario',
                'lc.nombre as cargo_solicitado',
                'usr_app_orden_servicio.responsable',
                'usr_app_orden_servicio.responsable_id',
                'usr_app_orden_servicio.cantidad_contrataciones',
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
            if ($candidatos_con_servicio != '') {
                return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente, sin embargo los candidatos con numero de doucmento de identidad $candidatos_con_servicio no pudieron ser registrados ya que se encuentran en otro proceso.", 'id' => $ordenServicio->id]);
            }
            return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente", 'id' => $ordenServicio->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
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
}
