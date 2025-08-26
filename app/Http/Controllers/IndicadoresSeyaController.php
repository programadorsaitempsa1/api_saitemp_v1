<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OservicioCargo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IndicadoresSeyaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioCargo::select()
            ->get();
        return response()->json($result);
    }
    public function ordenservicio($anio)
    {

        $registrosPorMes = DB::table('usr_app_formulario_ingreso')
            ->select(
                DB::raw('MONTH(usr_app_formulario_ingreso.created_at) as mes'),
                DB::raw('COUNT(usr_app_formulario_ingreso.id) as total')
            )
            ->whereYear('usr_app_formulario_ingreso.created_at', $anio)
            ->groupBy(DB::raw('MONTH(usr_app_formulario_ingreso.created_at)'))
            ->orderBy(DB::raw('MONTH(usr_app_formulario_ingreso.created_at)'))
            ->pluck('total', 'mes')
            ->all();

        // Construir array de nombres de los meses
        $nombresMesesArray = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Inicializar el array resultado con los nombres de los meses activos
        $nombresMesesActivos = [];

        // Iterar sobre los registros y construir los nombres de meses activos
        foreach ($registrosPorMes as $mes => $total) {
            if ($total > 0) {
                $nombresMesesActivos[$mes] = $nombresMesesArray[$mes];
            }
        }

        // Inicializar el array resultado final
        $resultadoFinal = [['nombres' => $nombresMesesActivos]];

        // Iterar sobre los registros y construir los objetos correspondientes
        foreach ($registrosPorMes as $mes => $total) {
            if ($total > 0) {
                $registro = array_fill(1, 12, 0);
                $registro[$mes] = $total;
                $resultadoFinal[] = $registro;
            }
        }

        return response()->json($resultadoFinal);
    }

    public function vacantesOcupadasTipoServicio() //******************************************************/
    {
        // Obtener los registros por estado y mes filtrados por el estado 10 en el seguimiento
        $registrosPorEstadoYMes = DB::table('usr_app_formulario_ingreso')
            ->leftJoin('usr_app_formulario_ingreso_seguimiento as fs', 'fs.formulario_ingreso_id', '=', 'usr_app_formulario_ingreso.id')
            ->select(
                DB::raw('MONTH(usr_app_formulario_ingreso.created_at) as mes'),
                'usr_app_formulario_ingreso.tipo_servicio_id',
                DB::raw('COUNT(DISTINCT usr_app_formulario_ingreso.id) as total') // Usar DISTINCT para evitar duplicados
            )
            ->where('fs.estado_ingreso_id', 10)
            ->groupBy('usr_app_formulario_ingreso.tipo_servicio_id', DB::raw('MONTH(usr_app_formulario_ingreso.created_at)'))
            ->get();

        // Obtener los nombres de los tipos de servicios
        $tipoServicio = DB::table('usr_app_formulario_ingreso_tipo_servicio')
            ->pluck('nombre_servicio', 'id')
            ->all();

        // Inicializar arrays para cada estado
        $registrosPorEstadoArray = [];

        // Inicializar array adicional con los nombres de los estados
        $nombresEstadosArray = array_values($tipoServicio);

        // Inicializar arrays para cada estado, incluso si no tienen registros
        foreach ($tipoServicio as $estadoId => $estadoNombre) {
            $registrosPorEstadoArray[$estadoNombre] = array_fill(0, 12, 0);
        }

        // Actualizar las posiciones del array con los valores obtenidos de la consulta
        foreach ($registrosPorEstadoYMes as $registro) {
            $mes = $registro->mes;
            $estadoCargoId = $registro->tipo_servicio_id;
            $cantidad = $registro->total;

            // Obtener el nombre del estado por su ID
            $estadoNombre = $tipoServicio[$estadoCargoId];

            // Actualizar la posición del array con el total de registros por mes
            $registrosPorEstadoArray[$estadoNombre][$mes - 1] = $cantidad; // Restar 1 para ajustar el índice del array (0-11)
        }

        // Crear la estructura final para el gráfico
        $datasets = [];
        foreach ($registrosPorEstadoArray as $estadoNombre => $datosMensuales) {
            $datasets[] = [
                'label' => $estadoNombre,
                'data' => $datosMensuales,
                //  'backgroundColor' => $this->getRandomColor(), // Método para obtener un color aleatorio
            ];
        }

        $resultadoFinal = [
            'labels' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            'data' => $datasets
        ];

        return response()->json($resultadoFinal);
    }

    public function vacantesocupadas()
    {
        // Array de nombres de los meses en español
        $nombres_meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        // Subconsulta para obtener la primera aparición de cada formulario_ingreso_id con estado_ingreso_id = 10
        $firstOccurrence = DB::table('usr_app_formulario_ingreso_seguimiento as u1')
            ->select('u1.formulario_ingreso_id', DB::raw('MIN(u1.created_at) as first_created_at'))
            ->where('u1.estado_ingreso_id', 10)
            ->groupBy('u1.formulario_ingreso_id');

        // Inicializar arrays para almacenar los meses con valores y los valores por mes
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
                $total = DB::table('usr_app_formulario_ingreso_seguimiento as u2')
                    ->joinSub($firstOccurrence, 'first_occurrence', function ($join) {
                        $join->on('u2.formulario_ingreso_id', '=', 'first_occurrence.formulario_ingreso_id')
                            ->on('u2.created_at', '=', 'first_created_at');
                    })
                    ->where('u2.estado_ingreso_id', 10)
                    ->whereMonth('u2.created_at', $mes)
                    ->count();

                // Almacenar el nombre del mes en un array
                $meses_con_valores[$mes] = $nombres_meses[$mes];

                // Crear un array con 12 posiciones y colocar el total si es diferente de cero
                $array_valores = array_fill(0, 12, 0);
                if ($total !== 0) {
                    $array_valores[$mes - 1] = $total;
                }

                // Almacenar los valores en el array correspondiente al mes
                $valores_por_mes[] = $array_valores;
            }
        }

        // Crear la estructura final
        $resultado = $valores_por_mes;

        array_unshift($resultado, ['nombres' => $meses_con_valores]);

        return $resultado;
    }

    public function ingresoempledosmes()
    {

        // Array de nombres de los meses en español
        $nombres_meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        // Subconsulta para obtener la primera aparición de cada formulario_ingreso_id con estado_ingreso_id = 10
        $firstOccurrence = DB::table('usr_app_formulario_ingreso_seguimiento as u1')
            ->select('u1.formulario_ingreso_id', DB::raw('MIN(u1.created_at) as first_created_at'))
            ->where('u1.estado_ingreso_id', 10)
            ->groupBy('u1.formulario_ingreso_id');

        // Inicializar arrays para almacenar los meses con valores y los valores por mes
        $meses_con_valores = [];
        $valores_por_mes = [];

        // Consulta principal para obtener los registros que coinciden con la primera aparición en cada mes del año
        for ($mes = 1; $mes <= 12; $mes++) {
            $result = DB::table('usr_app_formulario_ingreso_seguimiento as u2')
                ->joinSub($firstOccurrence, 'first_occurrence', function ($join) {
                    $join->on('u2.formulario_ingreso_id', '=', 'first_occurrence.formulario_ingreso_id')
                        ->on('u2.created_at', '=', 'first_occurrence.first_created_at');
                })
                ->join('usr_app_formulario_ingreso as u3', 'u2.formulario_ingreso_id', '=', 'u3.id')
                ->select('u2.formulario_ingreso_id', 'u3.fecha_ingreso')
                ->where('u2.estado_ingreso_id', 10)
                ->whereMonth('u3.fecha_ingreso', $mes) // Filtrar por el mes en la tabla usr_pp_formulario_ingreso
                ->exists(); // Verificar si existen registros en este mes

            // Si hay resultados para este mes, almacenar el nombre del mes y un array con 12 posiciones
            if ($result) {
                $total = DB::table('usr_app_formulario_ingreso_seguimiento as u2')
                    ->joinSub($firstOccurrence, 'first_occurrence', function ($join) {
                        $join->on('u2.formulario_ingreso_id', '=', 'first_occurrence.formulario_ingreso_id')
                            ->on('u2.created_at', '=', 'first_created_at');
                    })
                    ->join('usr_app_formulario_ingreso as u3', 'u2.formulario_ingreso_id', '=', 'u3.id')
                    ->where('u2.estado_ingreso_id', 10)
                    ->whereMonth('u3.fecha_ingreso', $mes)
                    ->count();

                // Almacenar el nombre del mes en un array
                $meses_con_valores[$mes] = $nombres_meses[$mes];

                // Crear un array con 12 posiciones y colocar el total si es diferente de cero
                $array_valores = array_fill(0, 12, 0);
                if ($total !== 0) {
                    $array_valores[$mes - 1] = $total;
                }

                // Almacenar los valores en el array correspondiente al mes
                $valores_por_mes[] = $array_valores;
            }
        }

        $resultado = [];
        $resultado = $valores_por_mes;
        array_unshift($resultado, ['nombres' => $meses_con_valores]);
        return $resultado;
    }


    public function cargosCantidadchar($anio)
    {
        $registrosPorMes = DB::table('usr_app_oservicio_cargos')
            ->select(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\')) as mes'), DB::raw('SUM(TRY_CAST(cantidad_vacantes AS INT)) AS total'))
            ->whereYear('created_at', $anio)
            ->groupBy(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\'))'))
            ->pluck('total', 'mes')
            ->all();

        // Inicializar un array con 12 posiciones, todas con valor 0
        $registrosPorMesArray = array_fill(1, 12, 0);

        // Actualizar las posiciones del array con los valores obtenidos de la consulta
        foreach ($registrosPorMes as $mes => $cantidad) {
            $registrosPorMesArray[$mes] = $cantidad;
        }
        return response()->json($registrosPorMesArray);
    }

    public function cargosCantidadchar2($anio)
    {
        $cargos_cantidad = OservicioCargo::select(
            'nombre',
            'cantidad_vacantes'
        )
            ->get();

        $cargos = [];
        foreach ($cargos_cantidad as $cargo) {
            // Si el cargo ya existe en el array, suma la cantidad, de lo contrario, crea una nueva entrada.
            if (array_key_exists($cargo->nombre, $cargos)) {
                $cargos[$cargo->nombre] += $cargo->cantidad_vacantes;
            } else {
                $cargos[$cargo->nombre] = $cargo->cantidad_vacantes;
            }
        }

        // Convierte el array asociativo en dos arrays separados para los nombres de los cargos y las cantidades.
        $nombresCargos = array_keys($cargos);
        $cantidades = array_values($cargos);

        $resultado = [
            'cargos' => $nombresCargos,
            'cantidad' => $cantidades
        ];

        return response()->json($resultado);
    }

    public function resgistrosporestado()
    {
        $registrosPorEstado = DB::table('usr_app_formulario_ingreso')
            ->leftJoin('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.estado_ingreso_id',
                'ei.nombre as estado_nombre',
                'ei.posicion',
                DB::raw('COUNT(usr_app_formulario_ingreso.id) as total')
            )
            ->groupBy('usr_app_formulario_ingreso.estado_ingreso_id', 'ei.nombre', 'ei.posicion')
            ->orderBy(DB::raw('CAST(ei.posicion AS INT)'))
            ->pluck('total', 'estado_nombre')
            ->all();

        // Crear un array de nombres con valor y otro array con solo los valores
        $nombresConValores = [];
        $valores = [];

        foreach ($registrosPorEstado as $estado => $total) {
            $nombresConValores[] = [
                $estado . ': ' . $total
            ];
            $valores[] = $total;
        }

        // Crear el array final con los dos arrays separados
        $resultado = [
            $nombresConValores,
            $valores
        ];

        // Retornar la respuesta JSON
        return response()->json($resultado);
    }

    public function registrosporresponsable()
    {
        $registrosPorEstado = DB::table('usr_app_formulario_ingreso')
            // ->leftJoin('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.responsable',
                // 'ei.nombre as estado_nombre',
                // 'ei.posicion',
                DB::raw('COUNT(usr_app_formulario_ingreso.id) as total')
            )
            ->groupBy('usr_app_formulario_ingreso.responsable')
            ->orderBy('usr_app_formulario_ingreso.responsable')
            ->pluck('total', 'responsable')
            ->all();

        // Crear un array de nombres con valor y otro array con solo los valores
        $nombresConValores = [];
        $valores = [];

        foreach ($registrosPorEstado as $responsable => $total) {
            $nombresConValores[] = [
                $responsable . ': ' . $total
            ];
            $valores[] = $total;
        }

        // Crear el array final con los dos arrays separados
        $resultado = [
            $nombresConValores,
            $valores
        ];

        // Retornar la respuesta JSON
        return response()->json($resultado);
    }

    public function estadosapilados()
    {
        $registrosPorEstado = DB::table('usr_app_formulario_ingreso')
            ->leftJoin('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.responsable',
                'ei.nombre as estado_nombre',
                DB::raw('COUNT(usr_app_formulario_ingreso.id) as total')
            )
            ->groupBy('usr_app_formulario_ingreso.responsable', 'ei.nombre')
            ->orderBy('usr_app_formulario_ingreso.responsable')
            ->orderBy('ei.nombre')
            ->get();

        // Crear un array para almacenar los responsables y los estados
        $responsables = [];
        $datosPorEstado = [];

        foreach ($registrosPorEstado as $registro) {
            $responsable = $registro->responsable;
            $estado = $registro->estado_nombre;
            $total = (int)$registro->total;

            // Si el responsable aún no está en el array, agregarlo
            if (!in_array($responsable, $responsables)) {
                $responsables[] = $responsable;
            }

            // Inicializar el array para el estado si no existe
            if (!isset($datosPorEstado[$estado])) {
                $datosPorEstado[$estado] = array_fill(0, count($responsables), 0);
            }

            // Actualizar el array de todos los estados para cada nuevo responsable
            foreach ($datosPorEstado as &$registros) {
                if (count($registros) < count($responsables)) {
                    $registros[] = 0;
                }
            }

            // Encontrar el índice del responsable actual
            $indiceResponsable = array_search($responsable, $responsables);

            // Asignar el total al índice correspondiente del estado
            $datosPorEstado[$estado][$indiceResponsable] = $total;
        }

        // Crear el array final para la cantidad de registros por estado incluyendo el nombre del estado
        $cantidadRegistrosPorEstado = [];
        foreach ($datosPorEstado as $estado => $registros) {
            $cantidadRegistrosPorEstado[] = [
                'label' => $estado,
                'data' => $registros
            ];
        }

        // Retornar la respuesta JSON
        return response()->json([
            'responsables' => $responsables,
            'data' => $cantidadRegistrosPorEstado
        ]);
    }



    public function vacantesEfectivas($anio)
    {
        return 'prueba';
        // Inicializar un array con ceros para cada mes
        $resultado = array_fill(1, 12, 0);

        // Obtener los datos de la base de datos
        $consulta = DB::table('usr_app_oservicio_cargos')
            ->select(DB::raw('MONTH(created_at) as mes'), DB::raw('SUM(CASE WHEN estado_cargo_id = 3 THEN vacantes_ocupadas ELSE 0 END) as total_vacantes_finalizadas'))
            ->whereYear('created_at', $anio)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Actualizar el array con los resultados de la consulta
        foreach ($consulta as $fila) {
            $mes = $fila->mes;
            $totalVacantesFinalizadas = $fila->total_vacantes_finalizadas;
            // Asignar el total de vacantes finalizadas al mes correspondiente
            $resultado[$mes] = $totalVacantesFinalizadas;
        }

        return $resultado;
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        try {
            $usuario_id = auth()->user()->id;
            $cliente = new OservicioClienteController;
            $cliente_id = $cliente->getIdCliente($id);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Cliente no registrado']);
        }
        DB::beginTransaction();
        $cargos = $request->all();
        foreach ($cargos as $item) {
            try {
                $result = new OservicioCargo;
                $result->cliente_id = $cliente_id->id;
                $result->nombre = $item['nombre'];
                $result->cantidad_vacantes = $item['cantidad_personas'];
                $result->salario = $item['salario'];
                $result->fecha_inicio = $item['fecha_inicio'];
                $result->fecha_solicitud = Carbon::createFromFormat('Y-d-m\TH:i', $item['fecha_solicitud'], 'America/Bogota');
                $result->observaciones = $item['observaciones'];
                $result->ciudad_id = $item['ciudad_id'];
                $result->estado_cargo_id = $item['estado_cargo_id'] == '' ? 1 : $item['estado_cargo_id'];
                $result->motivo_cancelacion = $item['observaciones'];
                // $result->estado_cargo_id = $item['vacanates_ocupadas'] == '' ? '0' : $item['estado_cargo_id'];
                $result->usuario_id = $usuario_id;
                $result->save();
            } catch (\Exception $e) {
                DB::rollback();
                return $e;
                return response()->json(['status' => 'success', 'message' => 'Error al guardar registro']);
            }
        }
        DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
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
            $usuario_id = auth()->user()->id;
            $cliente = new OservicioClienteController;
            $cliente_id = $cliente->getIdCliente($id);
            $nombre_cargos = OservicioCargo::select(
                'nombre'
            )
                ->where('cliente_id', $cliente_id->id)
                ->get();
            $cargos = $request->all();
            $existe_cargo = false;
            foreach ($cargos as $cargo) {
                foreach ($nombre_cargos as $nombre_cargo) {
                    if ($cargo['id'] != '' && $cargo['id'] != null) {
                        // if ($cargo['nombre'] == $nombre_cargo->nombre) {
                        $existe_cargo = true;
                        $vacantes_ocupadas = '0';
                        if ($cargo['estado_cargo_id'] == '3') {
                            // return $cargo['estado_cargo_id'];
                            $vacantes_ocupadas =  $cargo['vacantes_ocupadas'];
                        }
                        if ($cargo['estado_cargo_id'] == 1 || $cargo['estado_cargo_id'] == 3) {
                            //Se puede eliminar para que no actualicen la información

                            OservicioCargo::where('id', $cargo['id'])
                                ->update([
                                    'nombre' => $cargo['nombre'],
                                    'estado_cargo_id' => $cargo['estado_cargo_id'],
                                    'cantidad_vacantes' => $cargo['cantidad_personas'],
                                    'ciudad_id' => $cargo['ciudad_id'],
                                    'fecha_inicio' => $cargo['fecha_inicio'],
                                    'fecha_solicitud' => Carbon::createFromFormat('Y-d-m\TH:i', $cargo['fecha_solicitud'], 'America/Bogota'),
                                    'nombre' => $cargo['nombre'],
                                    'observaciones' => $cargo['observaciones'],
                                    'salario' => $cargo['salario'],
                                ]);
                        }
                        try {
                            OservicioCargo::where('nombre', $cargo['nombre'])
                                ->update([
                                    'estado_cargo_id' => $cargo['estado_cargo_id'],
                                    'motivo_cancelacion' => $cargo['motivo_cancelacion']
                                ]);
                        } catch (\Exception $e) {
                            //throw $th;
                        }
                        try {
                            OservicioCargo::where('nombre', $cargo['nombre'])
                                ->update([
                                    'estado_cargo_id' => $cargo['estado_cargo_id'],
                                    'vacantes_ocupadas' => $vacantes_ocupadas,
                                    // 'vacantes_ocupadas' => $cargo['vacantes_ocupadas'],
                                ]);
                        } catch (\Exception $e) {
                            //throw $th;
                        }
                    }
                }
                if (!$existe_cargo) {
                    $result = new OservicioCargo;
                    $result->cliente_id = $cliente_id->id;
                    $result->nombre = $cargo['nombre'];
                    $result->cantidad_vacantes = $cargo['cantidad_personas'];
                    $result->salario = $cargo['salario'];
                    $result->fecha_inicio = $cargo['fecha_inicio'];
                    $result->fecha_solicitud = Carbon::createFromFormat('Y-d-m\TH:i', $cargo['fecha_solicitud'], 'America/Bogota');
                    $result->observaciones = $cargo['observaciones'];
                    $result->ciudad_id = $cargo['ciudad_id'];
                    $result->estado_cargo_id = $cargo['estado_cargo_id'] == '' ? 1 : $cargo['estado_cargo_id'];
                    $result->motivo_cancelacion = $cargo['observaciones'];
                    // $result->vacantes_ocupadas = $cargo['vacanates_ocupadas'] == '' ? '0' : $cargo['vacanates_ocupadas'];
                    $result->usuario_id = $usuario_id;
                    $result->save();
                }
                $existe_cargo = false;
            }
            if ($existe_cargo) {
                return response()->json(['status' => 'error', 'message' => 'Los cargos insertados ya están registrados']);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Los cargos fueron registrados de manera exitosa']);
            }
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar los cargos']);
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
        $result = OservicioCargo::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
