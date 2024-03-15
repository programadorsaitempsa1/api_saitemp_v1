<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\formularioGestionIngreso;
use App\Models\FormularioIngresoArchivos;
use App\Models\FormularioIngresoResponsable;
use Carbon\Carbon;
// use Illuminate\Support\Carbon;

class formularioGestionIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {

        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'mun.nombre as ciudad',
                'usr_app_formulario_ingreso.laboratorio',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id'
            )
            ->orderby('usr_app_formulario_ingreso.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function actualizaestadoingreso($item_id, $estado_id)
    {
        $usuarios = FormularioIngresoResponsable::where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado_id)
            ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->select(
                'usuario_id',
                'usr.nombres'
            )
            ->get();

        // Obtener el número total de responsables
        $numeroResponsables = $usuarios->count();

        // Obtener el registro de ingreso
        $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
            ->first();

        // Asignar a cada registro de ingreso un responsable
        $indiceResponsable = $registro_ingreso->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable];

        // Actualizar el registro de ingreso con el estado y el responsable
        $registro_ingreso->estado_ingreso_id = $estado_id;
        // $registro_ingreso->responsable = $responsable->nombres;
        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }

    public function actualizaResponsableingreso($item_id, $nombre_responsable)
    {
        // return $nombre_responsable;
        $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
            ->first();

        $registro_ingreso->responsable = $nombre_responsable;
        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }
    public function responsableingresos($estado)
    {
        // return $estado;
        $usuarios = FormularioIngresoResponsable::join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado)
            ->select(
                'usuario_id',
                'usr.nombres as nombre'
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
            ->where('usr_app_formulario_ingreso.id', '=', $id)
            ->select(
                'usr_app_formulario_ingreso.id',
                'esti.nombre as estado_ingreso',
                'esti.id as estado_ingreso_id',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'usr_app_formulario_ingreso.cliente_id',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'usr_app_formulario_ingreso.salario',
                'usr_app_formulario_ingreso.municipio_id',
                'mun.nombre as municipio',
                'usr_app_formulario_ingreso.numero_contacto',
                'usr_app_formulario_ingreso.eps',
                'usr_app_formulario_ingreso.afp_id',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novadades',
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
                'usr_app_formulario_ingreso.cambio_fecha',
                'usr_app_formulario_ingreso.numero_radicado',
            )
            ->first();

        $archivos = FormularioIngresoArchivos::join('usr_app_archivos_formulario_ingreso as fi', 'fi.id', '=', 'usr_app_formulario_ingreso_archivos.arhivo_id')
            ->where('ingreso_id', $id)
            ->select(
                'usr_app_formulario_ingreso_archivos.arhivo_id',
                'usr_app_formulario_ingreso_archivos.ruta',
                'usr_app_formulario_ingreso_archivos.observacion',
                'fi.nombre',
                'fi.tipo_archivo'
            )
            ->get();
        $result['archivos'] = $archivos;
        return response()->json($result);
    }


    public function filtro($cadena)
    {
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];

        $query = FormularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'mun.nombre',
                'usr_app_formulario_ingreso.laboratorio',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id'
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
                    // Suponiendo que $valor_comparar2 contiene el segundo valor en el rango

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
                    // return $prefijoCampo . $campoActual . 'LIKE' . '%' . $valorCompararActual . '%';
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
                    // default:
                    //     // Manejar el operador desconocido
                    //     break;
            }
        }

        // Al final, ejecutar la consulta y obtener los resultados
        $resultados = $query->paginate(); // paginamos los resultados
        return $resultados;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $result = new formularioGestionIngreso;
        // $result->fecha_ingreso = Carbon::createFromFormat('Y-m-d\TH:i', $request->fecha_ingreo)->format('Y-m-d H:i:s');
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
        $result->novadades = $request->novedades;
        $result->laboratorio = $request->laboratorio;
        $result->examenes = $request->examenes;
        if ($request->fecha_examen != null) {
            $result->fecha_examen = Carbon::createFromFormat('Y-m-d\TH:i', $request->fecha_examen)->format('Y-m-d H:i:s');
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
        if ($request->cambio_fecha != null) {
            $result->cambio_fecha = Carbon::createFromFormat('Y-m-d\TH:i', $request->cambio_fecha)->format('Y-m-d H:i:s');
        }
        $result->responsable = $request->consulta_encargado;

        if ($result->save()) {
            return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $result->id]);
        } else {
            return response()->json(['status' => 'success', 'message' => 'error']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $ingreso_id)
    {
        try {

            $documentos = $request->all();
            $value = '';
            $id = '';
            $ids = [];
            $observacion = '';
            $observaciones = [];
            $rutas = [];

            $directorio = public_path('upload/');
            $archivos = glob($directorio . '*');
            foreach ($archivos as $archivo) {
                $nombreArchivo = basename($archivo);

                if (strpos($nombreArchivo, '_' . $ingreso_id . '_') !== false) {
                    unlink($archivo);
                }
            }

            foreach ($documentos as $item) {
                $contador = 0;
                if (!is_numeric($item) && !is_string($item)) {
                    $nombreArchivoOriginal = $item->getClientOriginalName();
                    $nuevoNombre = '_' . $ingreso_id . "_" . $nombreArchivoOriginal;

                    $carpetaDestino = './upload/';
                    $item->move($carpetaDestino, $nuevoNombre);
                    $item = ltrim($carpetaDestino, '.') . $nuevoNombre;
                    array_push($rutas, $item);
                    $value .= $item . ' ';
                } else {
                    if (is_numeric($item)) {
                        array_push($ids, $item);
                        $id .= $item . ' ';
                    } else {
                        array_push($observaciones, $item);
                        $observacion .= $item . ' ';
                    }
                }
                $contador++;
            }

            for ($i = 0; $i < count($ids); $i++) {
                $documento = new FormularioIngresoArchivos;
                $documento->arhivo_id = $ids[$i];
                $documento->ruta = $rutas[$i];
                $documento->observacion = $observaciones[$i];
                $documento->ingreso_id = $ingreso_id;
                $documento->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Formulario guardado exitosamente']);
        } catch (\Exception $e) {
            //throw $th;
            // $cliente = cliente::find($ingreso_id);
            // $cliente->delete();
            return $e;
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
        $user = auth()->user();
        $result = formularioGestionIngreso::find($id);
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
        $result->novadades = $request->novedades;
        $result->laboratorio = $request->laboratorio;
        $result->examenes = $request->examenes;
        $result->fecha_examen = $request->fecha_examen;
        $result->estado_ingreso_id = 1;
        $result->tipo_servicio_id = $request->tipo_servicio_id;
        $result->numero_vacantes = $request->numero_vacantes;
        $result->numero_contrataciones = $request->numero_contrataciones;
        $result->citacion_entrevista = $request->citacion_entrevista;
        $result->profesional = $request->profesional;
        $result->informe_seleccion = $request->informe_seleccion;
        $result->cambio_fecha = $request->cambio_fecha;
        $result->responsable = $request->consulta_encargado;
        $result->estado_ingreso_id = $request->estado_id;

        if ($result->save()) {
            // return response()->json(['status' => 'success', 'message' => 'ok']);
            return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $result->id]);
        } else {
            return response()->json(['status' => 'success', 'message' => 'error']);
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
        $result = formularioGestionIngreso::find($id);
        if ($result->delete()) {
            return response()->json("registro borrado Con Exito");
        } else {
            return response()->json("Error al borrar registro");
        }
    }
}
