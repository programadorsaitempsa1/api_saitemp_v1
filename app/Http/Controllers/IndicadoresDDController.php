<?php

namespace App\Http\Controllers;

use App\Models\HistoricoOperacionesDDModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Operacion;

class IndicadoresDDController extends Controller
{
    public function numeroRadicadosMes($anio)
    {
        $registrosPorMes = DB::table('usr_app_clientes')
            ->select(
                DB::raw('MONTH(usr_app_clientes.created_at) as mes'),
                DB::raw('COUNT(usr_app_clientes.id) as total')
            )
            ->whereYear('usr_app_clientes.created_at', $anio)
            ->groupBy(DB::raw('MONTH(usr_app_clientes.created_at)'))
            ->orderBy(DB::raw('MONTH(usr_app_clientes.created_at)'))
            ->pluck('total', 'mes')
            ->all();

        // Construir array de nombres de los meses
        $nombresMesesArray = [
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
    public function tipoDeOperacionMes($anio)
    { {
            try {
                $registrosPorEstadoYMes = DB::table('usr_app_historico_operaciondd')
                    ->select(
                        DB::raw('MONTH(created_at) as mes'),
                        'tipo_operacion_id',
                        DB::raw('COUNT(*) as total')
                    )
                    ->whereYear('created_at', $anio)
                    ->groupBy('tipo_operacion_id', DB::raw('MONTH(created_at)'))
                    ->get();

                // Inicializar arrays para cada estado
                $tipoServicio = DB::table('usr_app_operaciones')->pluck('nombre', 'id')->all();
                $registrosPorEstadoArray = [];

                // Inicializar array adicional con los nombres de los estados
                $nombresEstadosArray = ['nombres' => $tipoServicio];

                // Inicializar arrays para cada estado, incluso si no tienen registros
                foreach ($tipoServicio as $estadoId => $estadoNombre) {
                    $registrosPorEstadoArray[$estadoId] = array_fill(1, 12, 0);
                }

                // Actualizar las posiciones del array con los valores obtenidos de la consulta
                foreach ($registrosPorEstadoYMes as $registro) {
                    $mes = $registro->mes;
                    $estadoCargoId = $registro->tipo_operacion_id;
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
    }
    public function getAllHistoricoOperaciones()
    {
        $contratos = Operacion::all();

        // Retornar la respuesta en formato JSON
        return response()->json([
            'status' => 'success',
            'data' => $contratos,
        ], 200);
    }

    public function estadoOportunoMes($anio)
    {
        try {
            $registrosPorEstadoYMes = DB::table('usr_app_clientes_seguimiento_estado')
                ->select(
                    DB::raw('MONTH(created_at) as mes'),
                    'oportuno',
                    DB::raw('COUNT(*) as total')
                )
                ->whereYear('created_at', $anio)
                ->groupBy('oportuno', DB::raw('MONTH(created_at)'))
                ->get();

            // Mapeo de los valores de 'oportuno' como un array asociativo con claves como cadenas
            $oportunoLabels = [
                "0" => 'No oportuno',
                "1" => 'Oportuno',
                "2" => 'Estado pendiente',
            ];

            // Inicializar array de resultados
            $nombresArray = ['nombres' => (object) $oportunoLabels]; // Convertir a objeto explícitamente
            $datosPorMesArray = [];

            // Inicializar arrays con 12 meses para cada estado
            foreach ($oportunoLabels as $id => $label) {
                $datosPorMesArray[$id] = array_fill(1, 12, 0);
            }

            // Procesar los registros obtenidos y actualizar los valores por mes
            foreach ($registrosPorEstadoYMes as $registro) {
                $mes = $registro->mes;
                $oportuno = (string) $registro->oportuno; // Convertir a cadena para garantizar coincidencia
                $cantidad = $registro->total;

                // Validar que 'oportuno' esté mapeado
                if (isset($datosPorMesArray[$oportuno])) {
                    $datosPorMesArray[$oportuno][$mes] = $cantidad;
                }
            }

            // Convertir el formato de datosPorMesArray para ajustarse a la estructura solicitada
            $resultadoFinal = [$nombresArray];
            foreach ($datosPorMesArray as $datosPorMes) {
                $resultadoFinal[] = $datosPorMes;
            }

            return response()->json($resultadoFinal);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}