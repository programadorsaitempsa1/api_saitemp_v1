<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientesSeguimientoEstado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\EstadosExport;
use Maatwebsite\Excel\Facades\Excel;



class HistoricoEstadosDdController extends Controller
{
    public function index($cantidad)
    {
        // Consulta total de registros oportunos, no oportunos y pendientes a nivel global
        $totalRegistros = ClientesSeguimientoEstado::count();
        $oportunosGlobal = ClientesSeguimientoEstado::where('oportuno', '1')->count();
        $noOportunosGlobal = ClientesSeguimientoEstado::where('oportuno', '0')->count();
        $pendientesGlobal = $totalRegistros - ($oportunosGlobal + $noOportunosGlobal);
        $porcentajeOportunoGlobal = $totalRegistros > 0 ? round(($oportunosGlobal / $totalRegistros) * 100, 2) : 0;
        $porcentajeNoOportunoGlobal = $totalRegistros > 0 ? round(($noOportunosGlobal / $totalRegistros) * 100, 2) : 0;
        $porcentajePendientesGlobal = round(100 - $porcentajeOportunoGlobal - $porcentajeNoOportunoGlobal, 2);

        $estados = ClientesSeguimientoEstado::leftJoin(
            'usr_app_clientes as cliente',
            'cliente.id',
            '=',
            'usr_app_clientes_seguimiento_estado.cliente_id'
        )->leftJoin(
            'usr_app_estados_firma as estado',
            'estado.id',
            '=',
            'usr_app_clientes_seguimiento_estado.estados_firma_final'
        )->select(
            'usr_app_clientes_seguimiento_estado.id',
            'usr_app_clientes_seguimiento_estado.responsable_inicial',
            'usr_app_clientes_seguimiento_estado.responsable_final',
            'usr_app_clientes_seguimiento_estado.estados_firma_inicial',
            'usr_app_clientes_seguimiento_estado.estados_firma_final',
            'usr_app_clientes_seguimiento_estado.actualiza_registro',
            'usr_app_clientes_seguimiento_estado.cliente_id',
            'usr_app_clientes_seguimiento_estado.created_at',
            'usr_app_clientes_seguimiento_estado.updated_at',
            'usr_app_clientes_seguimiento_estado.oportuno',
            'usr_app_clientes_seguimiento_estado.inactivo',
            'cliente.id as id',
            'cliente.numero_radicado as radicado',
            'estado.nombre as nombre_estado'
        )
            ->orderby('usr_app_clientes_seguimiento_estado.cliente_id', 'DESC')
            ->paginate($cantidad);


        $estados->getCollection()->transform(function ($item) {
            $created = \Carbon\Carbon::parse($item->created_at);
            $updated = \Carbon\Carbon::parse($item->updated_at);

            $dif_seconds = $created->diffInMilliseconds($updated);
            if ($dif_seconds != 0) {
                $item->tiempo = $created->diffInMinutes($updated);
            } else {
                $item->tiempo = "Estado pendiente";
            }
            return $item;
        });

        $response = $estados->toArray();

        $response['porcentaje_oportuno'] = $porcentajeOportunoGlobal;
        $response['porcentaje_no_oportuno'] = $porcentajeNoOportunoGlobal;
        $response['porcentaje_pendientes'] = $porcentajePendientesGlobal;

        return response()->json($response);
    }

    public function filtrarEstados(Request $request, $cantidad)
    {
        $query = ClientesSeguimientoEstado::leftJoin(
            'usr_app_clientes as cliente',
            'cliente.id',
            '=',
            'usr_app_clientes_seguimiento_estado.cliente_id'
        )->leftjoin(
            'usr_app_estados_firma as estado',
            'estado.id',
            '=',
            'usr_app_clientes_seguimiento_estado.estados_firma_final'
        )->select(
            'usr_app_clientes_seguimiento_estado.id',
            'usr_app_clientes_seguimiento_estado.responsable_inicial',
            'usr_app_clientes_seguimiento_estado.responsable_final',
            'usr_app_clientes_seguimiento_estado.estados_firma_inicial',
            'usr_app_clientes_seguimiento_estado.estados_firma_final',
            'usr_app_clientes_seguimiento_estado.actualiza_registro',
            'usr_app_clientes_seguimiento_estado.cliente_id',
            'usr_app_clientes_seguimiento_estado.tiempo_estimado',
            'usr_app_clientes_seguimiento_estado.created_at as estado_created_at',
            'usr_app_clientes_seguimiento_estado.updated_at as estado_updated_at',
            'usr_app_clientes_seguimiento_estado.oportuno',
            'usr_app_clientes_seguimiento_estado.inactivo',
            'cliente.numero_radicado as radicado',
            'cliente.id as id',
            'estado.nombre as nombre_estado',
            DB::raw('DATEDIFF(MINUTE, usr_app_clientes_seguimiento_estado.created_at, usr_app_clientes_seguimiento_estado.updated_at) as tiempo') // Cálculo de tiempo
        );

        // Aplicar filtros dinámicos
        if ($request->has('filtros') && is_array($request->filtros)) {
            foreach ($request->filtros as $filtro) {
                if (isset($filtro['campo'], $filtro['comparacion'], $filtro['valor']) && $filtro['valor'] !== '') {
                    $campo = $filtro['campo'];
                    $comparacion = $filtro['comparacion'];
                    $valor = $filtro['valor'];

                    // Mapear campos
                    if ($campo === 'radicado') {
                        $campo = 'cliente.numero_radicado';
                    } elseif ($campo === 'nombre_estado') {
                        $campo = 'estado.nombre';
                    } elseif (in_array($campo, ['created_at', 'updated_at'])) {
                        $campo = 'usr_app_clientes_seguimiento_estado.' . $campo;
                    }

                    // Manejo especial para el campo "tiempo"
                    if ($campo === 'tiempo') {
                        switch ($comparacion) {
                            case 'Entre':
                                if (is_array($valor) && count($valor) === 2) {
                                    $query->whereRaw('DATEDIFF(MINUTE, usr_app_clientes_seguimiento_estado.created_at, usr_app_clientes_seguimiento_estado.updated_at) BETWEEN ? AND ?', [$valor[0], $valor[1]]);
                                }
                                break;
                            case 'Igual a':

                                $query->whereRaw('DATEDIFF(MINUTE, usr_app_clientes_seguimiento_estado.created_at, usr_app_clientes_seguimiento_estado.updated_at) = ?', [$valor]);
                                break;
                            default:
                                break;
                        }
                    } else if ($campo == "oportuno") {
                        // Mapear valores
                        $valorMapeado = null;
                        if ($valor === 'Si') {
                            $valorMapeado = 1;
                        } elseif ($valor === 'No') {
                            $valorMapeado = 0;
                        } elseif ($valor === 'Estado pendiente') {
                            $valorMapeado = 2;
                        }

                        // Aplicar filtro con el valor mapeado
                        if ($valorMapeado !== null) {
                            if ($comparacion === 'Igual a') {
                                $query->where('usr_app_clientes_seguimiento_estado.oportuno', '=', $valorMapeado);
                            } elseif ($comparacion === 'Contiene') {
                                $query->where('usr_app_clientes_seguimiento_estado.oportuno', 'LIKE', '%' . $valorMapeado . '%');
                            }
                        }
                    } else {
                        // Otros filtros
                        switch ($comparacion) {
                            case 'Igual a':
                                if ($campo === 'usr_app_clientes_seguimiento_estado.created_at' || $campo === 'usr_app_clientes_seguimiento_estado.updated_at') {
                                    $fechaInicio = Carbon::createFromFormat('Y-m-d', $valor)->startOfDay()->format('d-m-Y H:i:s');
                                    $fechaFin = Carbon::createFromFormat('Y-m-d', $valor)->endOfDay()->format('d-m-Y H:i:s');
                                    $query->whereBetween($campo, [$fechaInicio, $fechaFin]);
                                } else {
                                    $query->where($campo, '=', $valor);
                                }
                                break;
                            case 'Contiene':
                                $query->where($campo, 'LIKE', '%' . $valor . '%');
                                break;
                            case 'Entre':
                                if (is_array($valor) && count($valor) === 2) {
                                    if ($campo === 'usr_app_clientes_seguimiento_estado.created_at' || $campo === 'usr_app_clientes_seguimiento_estado.updated_at') {
                                        $fechaInicio = Carbon::createFromFormat('Y-m-d', $valor[0])->startOfDay()->format('d-m-Y H:i:s');
                                        $fechaFin = Carbon::createFromFormat('Y-m-d', $valor[1])->endOfDay()->format('d-m-Y H:i:s');
                                        $query->whereBetween($campo, [$fechaInicio, $fechaFin]);
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        // Obtener resultados paginados


        // Calcular porcentajes
        $totalRegistros = $query->count();
        $oportunosQuery = clone $query;
        $noOportunosQuery = clone $query;
        $oportunos = $oportunosQuery->where('usr_app_clientes_seguimiento_estado.oportuno', '1')->count();
        $noOportunos = $noOportunosQuery->where('usr_app_clientes_seguimiento_estado.oportuno', '0')->count();
        $pendientes = $totalRegistros - ($oportunos + $noOportunos);

        $porcentajeOportuno = $totalRegistros > 0 ? round(($oportunos / $totalRegistros) * 100, 2) : 0;
        $porcentajeNoOportuno = $totalRegistros > 0 ? round(($noOportunos / $totalRegistros) * 100, 2) : 0;
        $porcentajePendiente = round((100 - ($porcentajeOportuno + $porcentajeNoOportuno)), 2);

        $estados = $query->orderby('usr_app_clientes_seguimiento_estado.cliente_id', 'DESC')
            ->paginate($cantidad);
        // Preparar la respuesta
        $response = $estados->toArray();
        $response['total_oportuno'] = $oportunos;
        $response['total_no_oportuno'] = $noOportunos;
        $response['total_pendientes'] = $pendientes;
        $response['porcentaje_oportuno'] = $porcentajeOportuno;
        $response['porcentaje_no_oportuno'] = $porcentajeNoOportuno;
        $response['porcentaje_pendientes'] = $porcentajePendiente;

        return response()->json($response);
    }

    public function exportExcel(Request $request)
    {

        $query = ClientesSeguimientoEstado::leftJoin(
            'usr_app_clientes as cliente',
            'cliente.id',
            '=',
            'usr_app_clientes_seguimiento_estado.cliente_id'
        )->leftJoin(
            'usr_app_estados_firma as estado',
            'estado.id',
            '=',
            'usr_app_clientes_seguimiento_estado.estados_firma_inicial'
        )->select(
            'usr_app_clientes_seguimiento_estado.id',
            'usr_app_clientes_seguimiento_estado.responsable_inicial',
            'usr_app_clientes_seguimiento_estado.responsable_final',
            'usr_app_clientes_seguimiento_estado.estados_firma_inicial',
            'usr_app_clientes_seguimiento_estado.estados_firma_final',
            'usr_app_clientes_seguimiento_estado.actualiza_registro',
            'usr_app_clientes_seguimiento_estado.cliente_id',
            'usr_app_clientes_seguimiento_estado.created_at as estado_created_at',
            'usr_app_clientes_seguimiento_estado.updated_at as estado_updated_at',
            DB::raw("CASE 
            WHEN usr_app_clientes_seguimiento_estado.oportuno = 0 THEN 'No'
            WHEN usr_app_clientes_seguimiento_estado.oportuno = 1 THEN 'Si'
            WHEN usr_app_clientes_seguimiento_estado.oportuno = 2 THEN 'Estado pendiente'
            ELSE 'Desconocido'
        END as oportuno"),
            'usr_app_clientes_seguimiento_estado.inactivo',
            'cliente.numero_radicado as radicado',
            'estado.nombre as nombre_estado',
            DB::raw('DATEDIFF(MINUTE, usr_app_clientes_seguimiento_estado.created_at, usr_app_clientes_seguimiento_estado.updated_at) as tiempo') // Cálculo de tiempo
        );

        // Aplicar filtros dinámicos
        if ($request->has('filtros') && is_array($request->filtros)) {
            foreach ($request->filtros as $filtro) {
                if (isset($filtro['campo'], $filtro['comparacion'], $filtro['valor']) && $filtro['valor'] !== '') {
                    $campo = $filtro['campo'];
                    $comparacion = $filtro['comparacion'];
                    $valor = $filtro['valor'];

                    // Mapear campos
                    if ($campo === 'radicado') {
                        $campo = 'cliente.numero_radicado';
                    } elseif ($campo === 'nombre_estado') {
                        $campo = 'estado.nombre';
                    } elseif (in_array($campo, ['created_at', 'updated_at'])) {
                        $campo = 'usr_app_clientes_seguimiento_estado.' . $campo;
                    }

                    // Manejo especial para el campo "tiempo"
                    if ($campo === 'tiempo') {
                        switch ($comparacion) {
                            case 'Entre':
                                if (is_array($valor) && count($valor) === 2) {
                                    $query->whereRaw('DATEDIFF(MINUTE, usr_app_clientes_seguimiento_estado.created_at, usr_app_clientes_seguimiento_estado.updated_at) BETWEEN ? AND ?', [$valor[0], $valor[1]]);
                                }
                                break;
                            case 'Igual a':

                                $query->whereRaw('DATEDIFF(MINUTE, usr_app_clientes_seguimiento_estado.created_at, usr_app_clientes_seguimiento_estado.updated_at) = ?', [$valor]);
                                break;
                            default:
                                break;
                        }
                    } else {
                        // Otros filtros
                        switch ($comparacion) {
                            case 'Igual a':
                                if ($campo === 'usr_app_clientes_seguimiento_estado.created_at' || $campo === 'usr_app_clientes_seguimiento_estado.updated_at') {
                                    $fechaInicio = Carbon::createFromFormat('Y-m-d', $valor)->startOfDay()->format('d-m-Y H:i:s');
                                    $fechaFin = Carbon::createFromFormat('Y-m-d', $valor)->endOfDay()->format('d-m-Y H:i:s');
                                    $query->whereBetween($campo, [$fechaInicio, $fechaFin]);
                                } else {
                                    $query->where($campo, '=', $valor);
                                }
                                break;
                            case 'Contiene':
                                $query->where($campo, 'LIKE', '%' . $valor . '%');
                                break;
                            case 'Entre':
                                if (is_array($valor) && count($valor) === 2) {
                                    if ($campo === 'usr_app_clientes_seguimiento_estado.created_at' || $campo === 'usr_app_clientes_seguimiento_estado.updated_at') {
                                        $fechaInicio = Carbon::createFromFormat('Y-m-d', $valor[0])->startOfDay()->format('d-m-Y H:i:s');
                                        $fechaFin = Carbon::createFromFormat('Y-m-d', $valor[1])->endOfDay()->format('d-m-Y H:i:s');
                                        $query->whereBetween($campo, [$fechaInicio, $fechaFin]);
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        // Obtener resultados paginados
        $estados = $query->orderby('usr_app_clientes_seguimiento_estado.cliente_id', 'DESC');
        return Excel::download(new EstadosExport($estados), 'estados.xlsx');
    }

    public function deleteAll()
    {
        try {
            // Eliminar todos los registros de la tabla asociada al modelo
            DB::table('usr_app_clientes_seguimiento_estado')->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todos los registros han sido eliminados correctamente.',
            ]);
        } catch (\Exception $e) {
            // Manejar errores y devolver una respuesta adecuada
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al intentar eliminar los registros.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
