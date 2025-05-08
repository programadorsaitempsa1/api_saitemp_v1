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
use Illuminate\Support\Facades\DB;
use App\Traits\AutenticacionGuard;
use Maatwebsite\Excel\Facades\Excel;
use  Illuminate\Support\Facades\Crypt;


class OrdenServiciolienteController extends Controller
{

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

        $permisos = $this->validaPermiso();

        $result = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            ->when($tipo_usuario == '2' && $nit != null, function ($query) use ($nit) {
                return $query->where('usr_app_orden_servicio.nit', $nit);
            })
            ->when($tipo_usuario == '1' && !in_array('43', $permisos), function ($query) use ($usuario_id) {
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


    public function byid($id)
    {
        $result = OrdenServcio::join('usr_app_formulario_ingreso_tipo_servicio as ts', 'ts.id', '=', 'usr_app_orden_servicio.linea_servicio_id')
            ->join('usr_app_motivos_servicio as ms', 'ms.id', '=', 'usr_app_orden_servicio.motivo_servicio_id')
            ->join('usr_app_municipios as ciu', 'ciu.id', '=', 'usr_app_orden_servicio.ciudad_prestacion_servicio_id')
            ->join('usr_app_departamentos as dep', 'dep.id', '=', 'ciu.departamento_id')
            ->join('usr_app_lista_cargos as car', 'car.id', '=', 'usr_app_orden_servicio.cargo_solicitado_id')
            ->join('usr_app_sector_economico as sec', 'sec.id', '=', 'usr_app_orden_servicio.sector_economico_id')
            ->where('usr_app_orden_servicio.id', '=', $id)
            ->select(
                'usr_app_orden_servicio.id',
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
            )->first();
        $candidatos = CandidatoServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_candadato_servicio.usuario_id')
            ->join('usr_app_candidatos_c as can', 'can.usuario_id', 'usr_app_candadato_servicio.usuario_id')
            ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'can.tip_doc_id')
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
            if ($data['tipo_usuario_id'] == '2') { // asignación de servicios cuando lo registra un cliente
                $responsable = $this->asignacionAutomatica($tipo_responsable, $request->sector_economico);
                $ordenServicio->responsable_id = $responsable['responsable_id'];
                $ordenServicio->responsable = $responsable['responsable'];
            } else if ($data['tipo_usuario_id'] == '1') {  // asignación de servicios cuando lo registra un usuario interno de saitemp
                if ($asignacion_manual == 1) {
                    $ordenServicio->responsable_id = $request->responsable_id;
                    $ordenServicio->responsable = $request->responsable;
                } else {
                    $responsable = $this->asignacionAutomatica($tipo_responsable, $request->sector_economico);
                    $ordenServicio->responsable_id = $responsable['responsable_id'];
                    $ordenServicio->responsable = $responsable['responsable'];
                }
            }
            $ordenServicio->save();
            $cantidad_errores = 0;
            $numeros_documento = '';
            $valida_candidato = new RecepcionEmpleadoController();
            foreach ($request['candidatos'] as $item) {
                // $correo_candidato_validado = $valida_candidato->validaCorreoCandidato($item['correo_candidato']);
                // $correo_candidato_validado = $correo_candidato_validado->getData(true);
                if ($item['registrado'] == 1) {
                    $result = $this->candidatoRegistradoServicio($item['id'], $ordenServicio->id);
                } else if ($item['registrado'] == 0) {
                    $result = $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                } else if ($item['registrado'] == 2) {
                    $candidato_validado = $valida_candidato->validacandidato($item['numero_documento_candidato'], 0, $item['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                    if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '1') {
                        $result = $this->candidatoRegistradoServicio($candidato_validado['usuario']['usuario_id'], $ordenServicio->id);
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

    public function candidatoNoRegistradoServicio(array $usuario, string $ordenServicio_id)
    {
        try {
            DB::beginTransaction();
            // Se registra el usuario que no está creado en la tabla de login
            $user = new User;
            $user->email = $usuario['correo_candidato'];
            $user->password =  Crypt::encryptString($usuario['numero_documento_candidato']);
            $user->rol_id = 54;
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
            $candidato = new CandidatoServicioModel;
            $candidato->servicio_id = $ordenServicio_id;
            $candidato->usuario_id =  $user->id;
            $candidato->en_proceso = 0;
            $candidato->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
    public function candidatoRegistradoServicio(string $candidato_id, string $ordenServicio_id)
    {
        try {
            DB::beginTransaction();
            $candidato = new CandidatoServicioModel;
            $candidato->servicio_id = $ordenServicio_id;
            $candidato->usuario_id =  $candidato_id;
            $candidato->en_proceso = 0;
            $candidato->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function asignacionAutomatica($tipo_responsable, $sector_economico_id)
    {
        $usuarios = UsuarioDisponibleServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_usuarios_disponibles_servicio.usuario_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->where('usr_app_usuarios_disponibles_servicio.rol_usuario_interno_id', '=', $tipo_responsable)
            ->when($tipo_responsable == 3 || $tipo_responsable == 4, function ($query) {
                return $query->join('usr_app_sector_economico_profesional as sep', 'sep.profesional_id', 'us.id');
            })
            ->when($tipo_responsable == 3 || $tipo_responsable == 4, function ($query) use ($sector_economico_id) {
                return $query->where('sep.sector_economico_id', '=', $sector_economico_id);
            })
            ->select(
                'usr_app_usuarios_disponibles_servicio.id',
                'usr_app_usuarios_disponibles_servicio.usuario_id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos) AS nombres")

            )->get();
        $numeroResponsables = $usuarios->count();
        if ($numeroResponsables === 0) {
            return response()->json(['error' => 'No hay usuarios responsables disponibles'], 400);
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
            $ordenServicio->save();

            $cantidad_errores = 0;
            $numeros_documento = '';
            foreach ($request['candidatos'] as $item) {
                if ($item['registrado'] == 1) {
                    $this->candidatoRegistradoServicio($item['id'], $ordenServicio->id);
                } else if ($item['registrado'] == 0) {
                    $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                } else if ($item['registrado'] == 2) {
                    $valida_candidato = new RecepcionEmpleadoController();
                    $candidato_validado = $valida_candidato->validacandidato($item['numero_documento_candidato'], 0, $item['tipo_identificacion_id'], true);
                    $candidato_validado = $candidato_validado->getData(true);
                    if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '1') {
                        $this->candidatoRegistradoServicio($candidato_validado['usuario']['usuario_id'], $ordenServicio->id);
                    } else if ($candidato_validado['status'] == 'success' && $candidato_validado['motivo'] == '2') {
                        $this->candidatoNoRegistradoServicio($item, $ordenServicio->id);
                    } else if ($candidato_validado['status'] == 'error') {
                        $cantidad_errores++;
                        $numeros_documento .= ' ' . $item['numero_documento_candidato'] . ',';
                        if ($cantidad_errores == count($request['candidatos'])) {
                            DB::rollback();
                            return response()->json(['status' => 'error', 'message' => 'Los candidatos con numero de documento de identidad' . $numeros_documento . ' no pudieron ser registrados, para más innformación, por favor comuniquese con un asesor.']);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente"]);
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
