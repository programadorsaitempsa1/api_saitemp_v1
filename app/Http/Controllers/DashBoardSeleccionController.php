<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashBoardSeleccionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function cargosVacantesHojasVida($anio)
    {
        $resultado = [];
        $result = new OservicioCargoController();
        $cargos = $result->cargoschar($anio);

        $result = new OservicioHojaVidaController();
        $hojas_vida = $result->HojaVidaChar($anio);

        $result = new OservicioCargoController();
        $vacantes = $result->cargosCantidadchar($anio);

        array_push($resultado, $cargos->original);
        array_push($resultado, $vacantes->original);
        array_push($resultado, $hojas_vida->original);
        return $resultado;
    }

    public function cantidadVacantesPorEstado($anio)
    {
        try {
            $registrosPorEstadoYMes = DB::table('usr_app_oservicio_cargos')
                ->select(
                    DB::raw('MONTH(created_at) as mes'),
                    'estado_cargo_id',
                    DB::raw('COUNT(*) as total')
                )
                ->whereYear('created_at', $anio)
                ->groupBy('estado_cargo_id', DB::raw('MONTH(created_at)'))
                ->get();

            // Inicializar arrays para cada estado
            $estados = DB::table('usr_app_oservicio_estado_cargo')->pluck('nombre', 'id')->all();
            $registrosPorEstadoArray = [];

            // Inicializar array adicional con los nombres de los estados
            $nombresEstadosArray = ['nombres' => $estados];

            // Inicializar arrays para cada estado, incluso si no tienen registros
            foreach ($estados as $estadoId => $estadoNombre) {
                $registrosPorEstadoArray[$estadoId] = array_fill(1, 12, 0);
            }

            // Actualizar las posiciones del array con los valores obtenidos de la consulta
            foreach ($registrosPorEstadoYMes as $registro) {
                $mes = $registro->mes;
                $estadoCargoId = $registro->estado_cargo_id;
                $cantidad = $registro->total;

                // Actualizar la posición del array con el total de registros por mes
                $registrosPorEstadoArray[$estadoCargoId][$mes] = $cantidad;
            }

            // Combinar el array de nombres de estados con el array principal
            $resultadoFinal = array_merge([$nombresEstadosArray], $registrosPorEstadoArray);

            return response()->json($resultadoFinal);
        } catch (\Exception $e) {
            //throw $th;
            return $e;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        //
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
