<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OservicioCargo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OservicioCargoController extends Controller
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
    public function cargoschar($anio)
    {
        $registrosPorMes = DB::table('usr_app_oservicio_cargos')
            ->select(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\')) as mes'), DB::raw('COUNT(*) as total'))
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

    public function vacantesEfectivas($anio)
    {
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
