<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicioliente;
use App\Models\OrdenServcio;
use App\Models\CandidatoServicioModel;
use App\Models\UsuarioDisponibleServicioModel;
use App\Models\FormularioIngresoTipoServicio;
use App\Models\TipoAsignacionServicioModel;
use App\Models\UsuarioPermiso;
use App\Models\User;
use App\Models\UsuariosCandidatosModel;
use App\Models\EstadoCandidatoServicioModel;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use App\Traits\AutenticacionGuard;
use  Illuminate\Support\Facades\Crypt;
use App\Traits\Permisos;



class OrdenServiciolienteController extends Controller
{

    use Permisos;
    use AutenticacionGuard;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getServiciosByProfesional()
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

        $permisos = $this->Permisos();

        $result = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            ->join('usr_app_lista_cargos as car', 'car.id', '=', 'usr_app_orden_servicio.cargo_solicitado_id')
            ->when($tipo_usuario == '1' && !in_array('44', $permisos), function ($query) use ($usuario_id) {
                return $query->where('usr_app_orden_servicio.responsable_id', $usuario_id);
            })->whereIn('usr_app_orden_servicio.linea_servicio_id', [3, 4])
            ->select(
                'usr_app_orden_servicio.id',
                'usr_app_orden_servicio.numero_radicado',
                DB::raw("CONCAT(usr_app_orden_servicio.numero_radicado,' ',car.nombre)  AS numero_radicado"),
                'usr_app_orden_servicio.razon_social',
            )
            ->orderby('usr_app_orden_servicio.id', 'DESC')
            ->get();
        return response()->json($result);
    }
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

        $result = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            ->when($tipo_usuario == '2' && $nit != null, function ($query) use ($nit) {
                return $query->where('usr_app_orden_servicio.nit', $nit);
            })
            ->when($tipo_usuario == '1' && !in_array('46', $permisos), function ($query) use ($usuario_id) {
                return $query->where('usr_app_orden_servicio.responsable_id', $usuario_id);
            })
            ->select(
                'usr_app_orden_servicio.id',
                'usr_app_orden_servicio.numero_radicado',
                'usr_app_orden_servicio.created_at',
                'usr_app_orden_servicio.radicador',
                'usr_app_orden_servicio.fecha_inicio',
                'usr_app_orden_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ciu.nombre as ciudad_prestacion_servicio',
                'ms.nombre as motivo_servicio',
                'usr_app_orden_servicio.cantidad_contrataciones',
                'usr_app_orden_servicio.salario',
            )
            ->orderby('usr_app_orden_servicio.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }


    public function byid($id)
    {
        $result = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'ciu.departamento_id')
            ->join('usr_app_lista_cargos as car', 'car.id', '=', 'usr_app_orden_servicio.cargo_solicitado_id')
            ->join('usr_app_sector_economico as sec', 'sec.id', '=', 'usr_app_orden_servicio.sector_economico_id')
            ->leftJoin('usr_app_estado_servicio as est', 'est.id', '=', 'usr_app_orden_servicio.estado_servicio_id')
            ->where('usr_app_orden_servicio.id', '=', $id)
            ->select(
                'usr_app_orden_servicio.id',
                'usr_app_orden_servicio.cliente_id',
                'usr_app_orden_servicio.numero_radicado as radicado',
                'usr_app_orden_servicio.nit',
                'usr_app_orden_servicio.razon_social',
                'usr_app_orden_servicio.ciiu',
                'usr_app_orden_servicio.sector_economico_id',
                'sec.nombre as sector_economico',
                'usr_app_orden_servicio.created_at',
                'usr_app_orden_servicio.radicador',
                'usr_app_orden_servicio.fecha_inicio',
                'usr_app_orden_servicio.fecha_fin',
                'ts.nombre_servicio as linea_servicio',
                'ts.id as linea_servicio_id',
                'ciudad_prestacion_servicio_id',
                'ciu.nombre as ciudad_prestacion_servicio',
                'dep.nombre as departamento_prestacion_servicio',
                'dep.id as departamento_prestacion_servicio_id',
                'ms.nombre as motivo_servicio',
                'usr_app_orden_servicio.motivo_servicio_id',
                'usr_app_orden_servicio.cantidad_contrataciones',
                'usr_app_orden_servicio.cargo_solicitado_id',
                'usr_app_orden_servicio.salario',
                'car.nombre as cargo_solicitado',
                'car.subcategoria_cargo_id as subcategoria_cargo_id',
                'usr_app_orden_servicio.cargo_solicitado_id',
                'usr_app_orden_servicio.funciones_cargo',
                'usr_app_orden_servicio.nombre_contacto',
                'usr_app_orden_servicio.telefono_contacto',
                'usr_app_orden_servicio.cargo_contacto',
                'usr_app_orden_servicio.responsable',
                'usr_app_orden_servicio.numero_radicado',
                'est.nombre as estado_servicio',
                'usr_app_orden_servicio.estado_servicio_id',
                'usr_app_orden_servicio.motivo_cancelacion',
                'usr_app_orden_servicio.descripcion_cancelacion',
                'usr_app_orden_servicio.usuario_cancela',
                'usr_app_orden_servicio.fecha_cancelacion',
            )->first();
        $candidatos = CandidatoServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_candadato_servicio.usuario_id')
            ->join('usr_app_candidatos_c as can', 'can.usuario_id', 'usr_app_candadato_servicio.usuario_id')
            ->leftjoin('gen_tipide as ti', 'ti.cod_tip', '=', 'can.tip_doc_id')
            ->leftjoin('usr_app_estado_candidato_servicio as est', 'est.id', '=', 'usr_app_candadato_servicio.estado_id')
            ->where('usr_app_candadato_servicio.servicio_id', '=', $id,)
            ->select(
                'usr_app_candadato_servicio.id',
                'usr_app_candadato_servicio.servicio_id',
                'usr_app_candadato_servicio.usuario_id',
                DB::RAW("CONCAT(can.primer_nombre,' ',can.segundo_nombre) as nombre_candidato"),
                DB::RAW("CONCAT(can.primer_apellido,' ',can.segundo_apellido) as apellido_candidato"),
                'can.num_doc as numero_documento_candidato',
                'can.num_doc as confirma_numero_documento_candidato',
                'can.celular as celular_candidato',
                'us.email as correo_candidato',
                'can.tip_doc_id as tipo_identificacion_id',
                'ti.des_tip as consulta_tipo_identificacion',
                'usr_app_candadato_servicio.en_proceso',
                'est.id as estado_candidato_id',
                'est.nombre as estado_candidato',
            )->get();
        $result['candidatos'] =  $candidatos;
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
            DB::beginTransaction();
            $ordenServicio = new OrdenServcio;
            $ordenServicio->tipo_usuario = $data['tipo_usuario_id'];
            $ordenServicio->usuario_id = $data['usuario_id'];
            $ordenServicio->radicador = $data['nombres'];
            $ordenServicio->nit = $request->nit;
            $ordenServicio->razon_social = $request->razon_social;
            $ordenServicio->cliente_id = $request->cliente_id;
            $ordenServicio->ciiu = $request->actividad_ciiu;
            $ordenServicio->sector_economico_id = $request->sector_economico;
            $ordenServicio->fecha_inicio = $request->fecha_inicio;
            $ordenServicio->fecha_fin = $request->fecha_fin;
            $ordenServicio->linea_servicio_id = $request->linea_servicio_id;
            $ordenServicio->ciudad_prestacion_servicio_id = $request->ciudad_prestacion_servicio_id;
            $ordenServicio->nombre_contacto = $request->nombre_contacto;
            $ordenServicio->telefono_contacto = $request->telefono_contacto;
            $ordenServicio->cargo_contacto = $request->cargo_contacto;
            $ordenServicio->motivo_servicio_id = $request->motivo_servicio_id;
            $ordenServicio->cantidad_contrataciones = $request->cantidad_contrataciones;
            $ordenServicio->cargo_solicitado_id = $request->cargo_solicitado_id;
            $ordenServicio->funciones_cargo = $request->funciones_cargo;
            $ordenServicio->salario = $request->salario;
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
            foreach ($request['candidatos'] as $item) {
                if (isset($item['correo_candidato'])) {
                    $correo_candidato_validado = $valida_candidato->validaCorreoCandidato($item['correo_candidato']);
                    if (isset($correo_candidato_validado)) {
                        $correo_candidato_validado = $correo_candidato_validado->getData(true);
                        $correos_candidatos .= ' ' . $correo_candidato_validado['correo'] . ',';
                        continue;
                    }
                }
                if ($item['registrado'] == 1) {
                    $result = $this->candidatoRegistradoServicio($item['usuario_id'], $ordenServicio->id, 1);
                } else if ($item['registrado'] == 0) {
                    $result = $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                } else if ($item['registrado'] == 2) {
                    $candidato_validado = $valida_candidato->validacandidato($item['numero_documento_candidato'], 0, $item['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                    if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '1') {
                        $result = $this->candidatoRegistradoServicio($candidato_validado['usuario']['usuario_id'], $ordenServicio->id, 1);
                    } else if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '2') {
                        $result = $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                    } else if ($candidato_validado['status'] == 'error') {
                        $cantidad_errores++;
                        $numeros_documento .= ' ' . $item['numero_documento_candidato'] . ',';
                        if ($cantidad_errores == count($request['candidatos'])) {
                            DB::rollback();
                            return response()->json(['status' => 'error', 'titulo' => 'Frmulario no puedo ser guardado', 'message' => 'Los candidatos con numero de documento de identidad' . $numeros_documento . ' no pudieron ser registrados, para más información, por favor comuniquese con un asesor.']);
                        }
                    }
                }
            }

            DB::commit();
            if ($correos_candidatos != '') {
                return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente, sin embargo los candidatos con correo electrónico. $correos_candidatos. no pudieron ser registrados ya que el correo se encuentra en uso por otro usuario.", 'id' => $ordenServicio->id]);
            }
            if ($cantidad_errores > 0) {
                return response()->json(["status" => "success", "message" => "El formulario fue guardado exitosamente, pero los candidatos con numero de documento $numeros_documento no pudieron ser registrados, para más información, por favor comuniquese con un asesor.", 'id' => $ordenServicio->id]);
            }
            return response()->json(["status" => "success",  'titulo' => 'Frmulario guardado', "message" => "Formulario guardado exitosamente.", 'id' => $ordenServicio->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(["status" => "error", "message" => "Error al guadar los datos del formulario."]);
        }
    }

    public function candidatoNoRegistradoServicioCopia(array $usuario, string $ordenServicio_id)
    {
        try {
            DB::beginTransaction();
            $rol = Rol::select('id')->where('clave', 'candidato')->first();
            // Se registra el usuario que no está creado en la tabla de login
            $user = new User;
            // $user->email = $usuario['correo_candidato']; este campo es para regiatrar un nuevo usuario con correo no borrar
            // $user->password =  Crypt::encryptString($usuario['numero_documento_candidato']);
            $user->email = $usuario['celular_candidato'];
            $user->password =  bcrypt($usuario['celular_candidato']);
            $user->confirma_correo =  1;
            $user->rol_id = $rol->id;
            $user->tipo_usuario_id = 3;
            $user->save();
            // Se registran otros detos de usuario en la tabla de candidatos
            $candidato = new UsuariosCandidatosModel;
            $nombres = explode(" ", $usuario['nombre_candidato']);
            $apellidos =  explode(" ", $usuario['apellido_candidato']);
            $candidato->usuario_id =  $user->id;
            $candidato->primer_nombre =  $nombres[0];
            $candidato->segundo_nombre = isset($nombres[1]) ? $nombres[1] : '';
            $candidato->primer_apellido =  $apellidos[0];
            $candidato->segundo_apellido =   isset($apellidos[1]) ? $apellidos[1] : '';
            $candidato->num_doc = $usuario['numero_documento_candidato'];
            $candidato->celular = $usuario['celular_candidato'];
            $candidato->tip_doc_id = $usuario['tipo_identificacion_id'];
            $candidato->save();
            // Se relaciona el usuario creado con el nuevo servicio creado
            $estado_candidato = EstadoCandidatoServicioModel::select('id')->where('clave', 'radicado')->first();
            $candidato = new CandidatoServicioModel;
            $candidato->servicio_id = $ordenServicio_id;
            $candidato->usuario_id =  $user->id;
            $candidato->en_proceso = 0;
            $candidato->estado_id = $estado_candidato->id;
            $candidato->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function candidatoNoRegistradoServicio(array $usuario, string $ordenServicio_id)
    {
        try {
            DB::beginTransaction();
            $rol = Rol::select('id')->where('clave', 'candidato')->first();
            // Se registra el usuario que no está creado en la tabla de login
            $user = new User;
            $user->email = $usuario['celular_candidato'];
            $user->password =  bcrypt($usuario['celular_candidato']);
            $user->confirma_correo =  1;
            $user->rol_id = $rol->id;
            $user->tipo_usuario_id = 3;
            $user->save();
            // Se registran otros detos de usuario en la tabla de candidatos
            $candidato = new UsuariosCandidatosModel;
            $candidato->usuario_id =  $user->id;
            if (isset($usuario['nombre_candidato'])) {
                $nombres = explode(" ", $usuario['nombre_candidato']);
                $candidato->primer_nombre =  $nombres[0];
                $candidato->segundo_nombre = isset($nombres[1]) ? $nombres[1] : '';
            }
            if (isset($usuario['apellido_candidato'])) {
                $apellidos =  explode(" ", $usuario['apellido_candidato']);
                $candidato->primer_apellido =  $apellidos[0];
                $candidato->segundo_apellido =   isset($apellidos[1]) ? $apellidos[1] : '';
            }
            if (isset($usuario['numero_documento_candidato'])) {
                $candidato->num_doc = $usuario['numero_documento_candidato'];
            }
            if (isset($usuario['celular_candidato'])) {
                $candidato->celular = $usuario['celular_candidato'];
            }
            if (isset($usuario['tipo_identificacion_id'])) {
                $candidato->tip_doc_id = $usuario['tipo_identificacion_id'];
            }
            $candidato->save();
            // Se relaciona el usuario creado con el nuevo servicio creado
            $estado_candidato = EstadoCandidatoServicioModel::select('id')->where('clave', 'radicado')->first();
            $candidato = new CandidatoServicioModel;
            $candidato->servicio_id = $ordenServicio_id;
            $candidato->usuario_id =  $user->id;
            $candidato->en_proceso = 0;
            $candidato->estado_id = $estado_candidato->id;
            $candidato->motivo_cancelacion = $usuario['celular_candidato'];
            $candidato->descripcion_cancelacion = $usuario['celular_candidato'];
            $candidato->usuario_cancela = $usuario['celular_candidato'];
            $candidato->fecha_cancelacion = $usuario['celular_candidato'];
            $candidato->ciudad_prestacion_servicio_id = $usuario['celular_candidato'];
            $candidato->direccion_prestacion_servicio = $usuario['celular_candidato'];
            $candidato->fecha_ingreso = $usuario['celular_candidato'];
            $candidato->cargo_solicitado_id = $usuario['celular_candidato'];
            $candidato->salario = $usuario['celular_candidato'];
            $candidato->subsidio_transporte = $usuario['celular_candidato'];
            $candidato->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function candidatoRegistradoServicio(string $candidato_id, string $ordenServicio_id, int $estado_id, $retorna_respuesta = false)
    {

        try {
            $candidato_servicio = CandidatoServicioModel::where('servicio_id', $ordenServicio_id)->where('usuario_id', $candidato_id)->first();
            DB::beginTransaction();
            if (isset($candidato_servicio)) {
                $candidato_servicio->estado_id = $estado_id;
                $candidato_servicio->save();
                DB::commit();
                return false;
            } else {
                $candidato_servicio = CandidatoServicioModel::select('id', 'estado_id')->where('usuario_id', $candidato_id)->get();
                foreach ($candidato_servicio as $candidato) {
                    if (in_array($candidato->estado_id, [1, 2])) {
                        return true;
                    }
                }
                $candidato = new CandidatoServicioModel;
                $candidato->servicio_id = $ordenServicio_id;
                $candidato->usuario_id =  $candidato_id;
                $candidato->en_proceso = 0;
                $candidato->estado_id = $estado_id;
                $candidato->save();
                DB::commit();

                if ($retorna_respuesta) {
                    return $candidato->id;
                } else {
                    return false;
                }
            }
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
        $orden_servicio = OrdenServcio::select()->orderby('id', 'DESC')
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




    // public function mensaje($bandera)
    // {
    //     if ($bandera) {
    //         return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente"]);
    //     } else {
    //         return response()->json(["status" => "error", "message" => "Error al guadar los datos del formulario"]);
    //     }
    // }

    // public function cargamasivaservicio(Request $request)
    // {
    //     // Validar que el archivo sea un Excel
    //     $request->validate([
    //         'archivo' => 'required|file|mimes:xlsx,csv,xls'
    //     ]);

    //     try {
    //         $archivo = $request->file('archivo');
    //         $datos = Excel::toArray([], $archivo)[0];


    //         if (count($datos) <= 1) {
    //             return response()->json(['message' => 'El archivo está vacío o no tiene encabezados'], 400);
    //         }

    //         $headers = array_map('strtolower', $datos[0]);
    //         $numero_documento = array_search('numero_documento', $headers);
    //         $nombres = array_search('nombres', $headers);
    //         $apellidos = array_search('apellidos', $headers);
    //         $celular = array_search('celular', $headers);
    //         $correo = array_search('correo', $headers);
    //         $tipo_identificacion_id = array_search('tipo_identificacion_id', $headers);
    //         $tipo_identificacion = array_search('tipo_identificacion', $headers);

    //         if ($numero_documento === false || $nombres === false || $apellidos === false || $celular === false || $correo === false || $tipo_identificacion_id === false || $tipo_identificacion === false) {
    //             return response()->json(['message' => 'El archivo debe contener las columnas: codigo_centro_trabajo, nombre, nit, actividades_ciu'], 400);
    //         }

    //         // Procesar filas
    //         $candidatos = [];

    //         foreach (array_slice($datos, 1) as $fila) {
    //             if (!isset($fila[$numero_documento], $fila[$nombres], $fila[$apellidos], $fila[$celular], $fila[$correo], $fila[$tipo_identificacion_id], $fila[$tipo_identificacion])) {
    //                 continue; // Saltar filas con datos incompletos
    //             }

    //             $registro = (object) [
    //                 'numero_documento' => ltrim($fila[$numero_documento]),
    //                 'nombres' => trim($fila[$nombres]),
    //                 'apellidos' => trim($fila[$apellidos]),
    //                 'celular' => trim($fila[$celular]),
    //                 'correo' => trim($fila[$correo]),
    //                 'tipo_identificacion_id' => trim($fila[$tipo_identificacion_id]),
    //                 'tipo_identificacion' => trim($fila[$tipo_identificacion]),
    //             ];

    //             $candidatos[] = $registro;
    //         }

    //         return $candidatos;
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Error en la importación', 'error' => $e->getMessage()], 500);
    //     }
    // }



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

            $query = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
                ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
                ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
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
                    'ciu.nombre as ciudad_prestacion_servicio',
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
        $result = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'ciu.departamento_id')
            ->join('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_orden_servicio.cargo_solicitado_id')
            ->where('usr_app_orden_servicio.id', '=', $id)
            ->select(
                'usr_app_orden_servicio.id',
                'usr_app_orden_servicio.numero_radicado',
                'ts.nombre_servicio as linea_servicio',
                'usr_app_orden_servicio.linea_servicio_id',
                'ciu.nombre as ciudad_prestacion_servicio',
                'ciu.id as ciudad_prestacion_servicio_id',
                'dep.nombre as departamento_prestacion_servicio',
                'dep.id as departamento_prestacion_servicio_id',
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
    public function updateCopia(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ordenServicio = OrdenServcio::find($id);
            $ordenServicio->fecha_inicio = $request->fecha_inicio;
            $ordenServicio->fecha_fin = $request->fecha_fin;
            $ordenServicio->linea_servicio_id = $request->linea_servicio_id;
            $ordenServicio->ciudad_prestacion_servicio_id = $request->ciudad_prestacion_servicio_id;
            $ordenServicio->motivo_servicio_id = $request->motivo_servicio_id;
            $ordenServicio->cantidad_contrataciones = $request->cantidad_contrataciones;
            $ordenServicio->cargo_solicitado_id = $request->cargo_solicitado_id;
            $ordenServicio->funciones_cargo = $request->funciones_cargo;
            $ordenServicio->salario = $request->salario;
            $ordenServicio->estado_servicio_id = $request->estado_servicio_id;
            $ordenServicio->save();

            $cantidad_errores = 0;
            $numeros_documento = '';
            $candidatos_con_servicio = '';
            $valida_candidato = new RecepcionEmpleadoController();
            foreach ($request['candidatos'] as $item) {
                if ($item['registrado'] == 1) {
                    $candiidato_en_proceso =  $this->candidatoRegistradoServicio($item['usuario_id'], $ordenServicio->id, $item['estado_candidato_id']);
                    if ($candiidato_en_proceso) {
                        $candidatos_con_servicio .= $item['numero_documento_candidato'] . ', ';
                    }
                } else if ($item['registrado'] == 0) {
                    $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                } else if ($item['registrado'] == 2) {
                    $candidato_validado = $valida_candidato->validacandidato($item['numero_documento_candidato'], 0, $item['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                    if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '1') {
                        $this->candidatoRegistradoServicio($candidato_validado['usuario']['usuario_id'], $ordenServicio->id, $item['estado_candidato_id']);
                    } else if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '2') {
                        $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                    } else if ($candidato_validado['status'] == 'error') {
                        $cantidad_errores++;
                        $numeros_documento .= ' ' . $item['numero_documento_candidato'] . ',';
                        if ($cantidad_errores == count($request['candidatos'])) {
                            DB::rollback();
                            return response()->json(["status" => "error", "message" => "Los candidatos con numero de documento de identidad $numeros_documento no pudieron ser registrados, para más innformación, por favor comuniquese con un asesor."]);
                        }
                    }
                }
            }

            DB::commit();
            if ($candidatos_con_servicio != '') {
                return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente, sin embargo los candidatos con numero de doucmento de identidad $candidatos_con_servicio no pudieron ser registrados ya que se encuentran en otro proceso."]);
            }
            return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente"]);
        } catch (\Exception $e) {
            DB::rollback();
            // return $e;
            return response()->json(["status" => "error", "message" => "Error al guadar los datos del formulario"]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ordenServicio = OrdenServcio::find($id);
            $ordenServicio->fecha_inicio = $request->fecha_inicio;
            $ordenServicio->fecha_fin = $request->fecha_fin;
            $ordenServicio->linea_servicio_id = $request->linea_servicio_id;
            $ordenServicio->ciudad_prestacion_servicio_id = $request->ciudad_prestacion_servicio_id;
            $ordenServicio->motivo_servicio_id = $request->motivo_servicio_id;
            $ordenServicio->cantidad_contrataciones = $request->cantidad_contrataciones;
            $ordenServicio->cargo_solicitado_id = $request->cargo_solicitado_id;
            $ordenServicio->funciones_cargo = $request->funciones_cargo;
            $ordenServicio->salario = $request->salario;
            $ordenServicio->estado_servicio_id = $request->estado_servicio_id;
            $ordenServicio->save();

            $cantidad_errores = 0;
            $numeros_documento = '';
            $candidatos_con_servicio = '';
            $valida_candidato = new RecepcionEmpleadoController();
            foreach ($request['candidatos'] as $item) {
                if ($item['registrado'] == 1) {
                    $candiidato_en_proceso =  $this->candidatoRegistradoServicio($item['usuario_id'], $ordenServicio->id, $item['estado_candidato_id']);
                    if ($candiidato_en_proceso) {
                        $candidatos_con_servicio .= $item['numero_documento_candidato'] . ', ';
                    }
                } else if ($item['registrado'] == 0) {
                    $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                } else if ($item['registrado'] == 2) {
                    $candidato_validado = $valida_candidato->validacandidato($item['numero_documento_candidato'], 0, $item['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                    if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '1') {
                        $this->candidatoRegistradoServicio($candidato_validado['usuario']['usuario_id'], $ordenServicio->id, $item['estado_candidato_id']);
                    } else if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '2') {
                        $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                    } else if ($candidato_validado['status'] == 'error') {
                        $cantidad_errores++;
                        $numeros_documento .= ' ' . $item['numero_documento_candidato'] . ',';
                        if ($cantidad_errores == count($request['candidatos'])) {
                            DB::rollback();
                            return response()->json(["status" => "error", "message" => "Los candidatos con numero de documento de identidad $numeros_documento no pudieron ser registrados, para más innformación, por favor comuniquese con un asesor."]);
                        }
                    }
                }
            }

            DB::commit();
            if ($candidatos_con_servicio != '') {
                return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente, sin embargo los candidatos con numero de doucmento de identidad $candidatos_con_servicio no pudieron ser registrados ya que se encuentran en otro proceso."]);
            }
            return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente"]);
        } catch (\Exception $e) {
            DB::rollback();
            // return $e;
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
