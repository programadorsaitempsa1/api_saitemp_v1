<?php

namespace App\Http\Controllers;

use App\Models\HistoricoConsultasApiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\RecepcionEmpleadoController;
use App\Models\HistoricoFondosModel;
use App\Traits\Permisos;
use App\Traits\AutenticacionGuard;
use Carbon\Carbon;
use App\Models\fondoSP;
use App\Exports\ConsultaPilaExport;

class ApiPilaController extends Controller


{
    use Permisos;
    use AutenticacionGuard;
    public function getToken()
    {
        $usuario = Config::get('app.USUARIO_PILA');
        $password = Config::get('app.PASSWORD_PILA');
        $url_pila = Config::get('app.PILA_URL');
        $pila_aplicacion = Config::get('app.PILA_APLICACION');
        $datos = [
            "data" => [
                [
                    "NombreUsuario" => $usuario,
                    "Aplicacion" => $pila_aplicacion,
                    "Password" => $password
                ]
            ]
        ];

        try {
            $response = Http::post($url_pila . '/api/ControlAcceso/Autenticar', $datos);
            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'token' => $response->json()
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => $response->body(),
                    'http_status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function consultaBdReferencias(Request $request)
    {
        $url_pila = Config::get('app.PILA_URL');
        $endpoint = "/api/Persona/ConsultarPersonaEnBaseDatosReferencia";
        $takenToken = $this->getToken();

        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['data'];
            try {
                $datos = [
                    "data" => [
                        [
                            "TipoDocumento" => $request->tipo_doc,
                            "NumeroDocumento" => $request->num_doc,
                        ]
                    ]
                ];

                $response = Http::withHeaders([
                    'token' => $token
                ])->post($url_pila . $endpoint, $datos);

                if ($response != null && $response['data'] != null && count($response['data']) > 0) {
                    return response()->json([
                        'status' => 'success',
                        'data' => $response['data'][0]
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => "No existen datos para el número y tipo de documento especificado"
                    ], 404);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }

    public function consultaBdReferenciasMasivo()
    {
        $url_pila = Config::get('app.PILA_URL');
        $endpoint = "/api/Persona/ConsultarPersonaEnBaseDatosReferenciaMasivo";
        $takenToken = $this->getToken();
        try {
            $recepcionEmpleadoController = new RecepcionEmpleadoController();
            $clientes_activos = $recepcionEmpleadoController->idesEmpleadosactivos();
            if ($takenToken['status'] == 'success') {
                $token = $takenToken['token']['data'];
                $payload = ['data' => $clientes_activos];
                $response = Http::withHeaders([
                    'token' => $token
                ])->post($url_pila . $endpoint, $payload);
                if ($response['data']) {
                    $user = $this->getUserRelaciones();
                    $user = $user->getdata(true);
                    $historicoConsultas = new HistoricoConsultasApiModel();
                    $historicoConsultas->consulta_id = $response['data'];
                    $historicoConsultas->usuario_id = $user['id'];
                    $historicoConsultas->nombre_usuario = $user['nombres'] . ' ' .  $user['apellidos'];
                    $historicoConsultas->save();
                }
                return response()->json(['status' => 'success', 'message' => 'consulta generada correctamente'], 200);
            }
        } catch (\Exception $e) {

            return response()->json(['status' => 'error', 'message' => 'No es posible generar la consulta en este momento, intente más tarde'], 500);
        }
    }


    public function getConsultaHistoricoConsultaMasiva($cantidad)
    {
        $permisos = $this->permisos();
        $user = $this->getUserRelaciones();
        $user = $user->getdata(true);
        $result = HistoricoConsultasApiModel::select(
            'usr_app_historico_masivo_pila.id',
            'usr_app_historico_masivo_pila.consulta_id',
            'usr_app_historico_masivo_pila.created_at',
            'usr_app_historico_masivo_pila.nombre_usuario',
            'usr_app_historico_masivo_pila.usuario_id',


        )->when(!in_array('47', $permisos), function ($query) use ($user) {
            return $query->where(function ($subQuery) use ($user) {
                $subQuery->Where('usr_app_historico_masivo_pila.usuario_id', $user['id']);
            });
        })->orderby('usr_app_historico_masivo_pila.created_at', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function consultaBdReferenciasMasivoExcel(Request $request)
    {

        $url_pila = Config::get('app.PILA_URL');
        $endpoint = "/api/Persona/ConsultarPersonaEnBaseDatosReferenciaMasivo";
        $takenToken = $this->getToken();
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);
        $ruta = $request->file('archivo')->store('temporal');

        $path = storage_path("app/" . $ruta);
        $coleccion = Excel::toCollection(null, $path)->first();

        $datos = [];

        foreach ($coleccion as $index => $fila) {
            if ($index === 0) continue;

            $datos[] = [
                'TipoDocumento' => trim($fila[0]),
                'NumeroDocumento' => trim((string)$fila[1]),
            ];
        }
        $payload = ['data' => $datos];
        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['data'];
            try {
                $response = Http::withHeaders([
                    'token' => $token
                ])->post($url_pila . $endpoint, $payload);
                Storage::delete($ruta);
                if ($response['data']) {
                    $user = $this->getUserRelaciones();
                    $user = $user->getdata(true);
                    $historicoConsultas = new HistoricoConsultasApiModel();
                    $historicoConsultas->consulta_id = $response['data'];
                    $historicoConsultas->usuario_id = $user['id'];
                    $historicoConsultas->nombre_usuario = $user['nombres'] . ' ' .  $user['apellidos'];
                    $historicoConsultas->save();
                }
                return response()->json([
                    'payload_enviado' => $payload,
                    'respuesta_api' => $response->json()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }

    public function consultaByIdMasivo($consulta_id)
    {
        set_time_limit(600);

        $url_pila   = Config::get('app.PILA_URL');
        $endpoint   = '/api/Persona/ConsultarRespuestaSolicitudPersonaMasivo';

        $takenToken = $this->getToken();
        if ($takenToken['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => 'Token inválido'], 403);
        }

        // Normaliza documento: trim (si tienes puntos/guiones, cambia a preg_replace('/\D+/', '', $s))
        $normalize = function ($v) {
            if ($v === null) return null;
            $s = trim((string)$v);
            return $s;
        };

        try {
            $token  = $takenToken['token']['data'];

            $payload = ['data' => [['IdSolicitud' => $consulta_id]]];

            // HTTP robusto
            $response = Http::withHeaders(['token' => $token])
                ->timeout(30)                           // tiempo total
                ->withOptions(['connect_timeout' => 10]) // conexión
                ->retry(2, 200)                         // reintentos
                ->post($url_pila . $endpoint, $payload);

            if (!$response->ok()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Error desde servicio externo',
                    'detalle' => $response->json(),
                ], 502);
            }

            $data = $response->json('data');
            if (empty($data) || !is_array($data)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No existen datos para la solicitud',
                ], 404);
            }

            // --- Documentos únicos normalizados ---
            $documentos = collect($data)
                ->pluck('NumeroDocumento')
                ->filter()
                ->map($normalize)
                ->filter()
                ->unique()
                ->values();

            if ($documentos->isEmpty()) {
                return response()->json(['status' => 'success', 'data' => []], 200);
            }

            DB::disableQueryLog();

            // --- Consulta Novasoft por CHUNKS y solo el ÚLTIMO registro por empleado/tipo ---
            $fondos = collect();
            foreach ($documentos->chunk(900) as $chunk) {
                // Subquery: MAX(fec_afi) por (cod_emp, tip_fdo) en el lote
                $sub = DB::table('rhh_hisfon')
                    ->select('cod_emp', 'tip_fdo', DB::raw('MAX(fec_afi) AS max_fec'))
                    ->whereIn('cod_emp', $chunk->values())
                    ->whereIn('tip_fdo', [1, 2]) // 1=AFP, 2=EPS
                    ->groupBy('cod_emp', 'tip_fdo');

                // Join contra la tabla real para obtener cod_fdo de ese último registro, y luego el nombre/código del fondo
                $rows = DB::table('rhh_hisfon as h')
                    ->joinSub($sub, 'm', function ($join) {
                        $join->on('h.cod_emp', '=', 'm.cod_emp')
                            ->on('h.tip_fdo', '=', 'm.tip_fdo')
                            ->on('h.fec_afi', '=', 'm.max_fec');
                    })
                    ->leftJoin('rhh_tbfondos as f', 'f.cod_fdo', '=', 'h.cod_fdo')
                    ->select(
                        'h.cod_emp',
                        'h.tip_fdo',
                        'h.fec_afi',
                        'f.cod_sgs',
                        'f.nom_fdo'
                    )
                    ->get();

                // Normaliza llave
                $rows = $rows->map(function ($r) use ($normalize) {
                    $r->cod_emp = $normalize($r->cod_emp);
                    return $r;
                });

                $fondos = $fondos->concat($rows);
            }

            // Mapas por documento
            $epsByDoc = $fondos->where('tip_fdo', 2)
                ->keyBy('cod_emp')
                ->map(fn($r) => [
                    'cod'  => $r->cod_sgs,
                    'nom'  => $r->nom_fdo,
                    'fec'  => $r->fec_afi,
                ]);

            $afpByDoc = $fondos->where('tip_fdo', 1)
                ->keyBy('cod_emp')
                ->map(fn($r) => [
                    'cod'  => $r->cod_sgs,
                    'nom'  => $r->nom_fdo,
                    'fec'  => $r->fec_afi,
                ]);

            // --- Enriquecer sin más queries ---
            $datosEnriquecidos = collect($data)->map(function ($item) use ($normalize, $epsByDoc, $afpByDoc) {
                $docKey = $normalize($item['NumeroDocumento'] ?? null);

                $eps = $docKey ? ($epsByDoc[$docKey] ?? null) : null;
                $afp = $docKey ? ($afpByDoc[$docKey] ?? null) : null;

                // Inicializa claves esperadas por tu export
                $item['eps_novasoft']                  = $eps['cod'] ?? null;
                $item['eps_nombre_novasoft']           = $eps['nom'] ?? null;
                $item['eps_fecha_afiliacion_novasoft'] = $eps['fec'] ?? null;

                $item['afp_novasoft']                  = $afp['cod'] ?? null;
                $item['afp_nombre_novasoft']           = $afp['nom'] ?? null;
                $item['afp_fecha_afiliacion_novasoft'] = $afp['fec'] ?? null;

                // Advertencias
                $advertencias = [];
                $codigoEpsApi = $item['CodigoEps'] ?? null;
                $codigoAfpApi = $item['CodigoAfp'] ?? null;

                if (
                    $codigoAfpApi != trim((string)($item['afp_novasoft'] ?? '')) &&
                    $codigoEpsApi != trim((string)($item['eps_novasoft'] ?? ''))
                ) {
                    $advertencias[] = 'La AFP y la EPS actuales son diferentes a las de la consulta en ADRES/RUAF; verifique la información.';
                } elseif ($codigoEpsApi != trim((string)($item['eps_novasoft'] ?? ''))) {
                    $advertencias[] = 'La EPS actual es diferente a la obtenida en ADRES; verifique la información.';
                } elseif ($codigoAfpApi != trim((string)($item['afp_novasoft'] ?? ''))) {
                    $advertencias[] = 'La AFP actual es diferente a la obtenida en RUAF; verifique la información.';
                }

                if (is_null($item['eps_novasoft']) && is_null($item['afp_novasoft'])) {
                    $advertencias[] = 'El empleado no registra ni AFP ni EPS en Novasoft.';
                } elseif (is_null($item['eps_novasoft'])) {
                    $advertencias[] = 'El empleado no registra EPS en Novasoft.';
                } elseif (is_null($item['afp_novasoft'])) {
                    $advertencias[] = 'El empleado no registra AFP en Novasoft.';
                }

                $item['advertencias'] = $advertencias;

                return $item;
            })
                ->filter(fn($item) => !empty($item['advertencias'])) // si quieres solo con advertencias
                ->values();

            return response()->json([
                'status' => 'success',
                'data'   => $datosEnriquecidos,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function actualizaNovasoftwithAdres(Request $request)
    {



        $fecha_actual = Carbon::now()->subMonthNoOverflow()->endOfMonth()->format('d-m-Y');
        $fecha_actual_format = Carbon::now()->startOfMonth()->format('d-m-Y');
        $empleados = $request['actualiza_empleados'];
        if (count($empleados) > 0) {
            $array_resultado = [];
            foreach ($empleados as $empleado) {
                try {
                    DB::beginTransaction();
                    $ultimo_registro_eps = HistoricoFondosModel::leftJoin('rhh_tbfondos as f', 'f.cod_fdo', '=', 'rhh_hisfon.cod_fdo')
                        ->select(
                            'rhh_hisfon.cod_fdo',
                            'rhh_hisfon.cod_emp',
                            'rhh_hisfon.fec_afi',
                            'rhh_hisfon.tip_nov',
                            'rhh_hisfon.fec_ter',
                            'rhh_hisfon.tip_fdo',
                            'f.nom_fdo as nombre_fondo',
                            'f.cod_sgs as codigo_adres'
                        )
                        ->where('cod_emp', $empleado['NumeroDocumento'])
                        ->where('rhh_hisfon.tip_fdo', 2)
                        ->orderByDesc('fec_afi')
                        ->first();
                    $ultimo_registro_afp = HistoricoFondosModel::leftJoin('rhh_tbfondos as f', 'f.cod_fdo', '=', 'rhh_hisfon.cod_fdo')
                        ->select(
                            'rhh_hisfon.cod_fdo',
                            'rhh_hisfon.cod_emp',
                            'rhh_hisfon.fec_afi',
                            'rhh_hisfon.tip_nov',
                            'rhh_hisfon.fec_ter',
                            'rhh_hisfon.tip_fdo',
                            'f.nom_fdo as nombre_fondo',
                            'f.cod_sgs as codigo_adres'
                        )
                        ->where('cod_emp', $empleado['NumeroDocumento'])
                        ->where('rhh_hisfon.tip_fdo', 1)
                        ->orderByDesc('fec_afi')
                        ->first();

                    if ($ultimo_registro_eps && $ultimo_registro_eps->codigo_adres != $empleado['CodigoEps'] && $empleado['CodigoEps'] != null) {
                        $solo_fecha = \Carbon\Carbon::parse($ultimo_registro_eps->fec_afi)->format('d-m-Y');

                        $registro = DB::table('rhh_hisfon')
                            ->where('cod_emp', $ultimo_registro_eps->cod_emp)
                            ->whereDate('fec_afi', $solo_fecha)
                            ->where('cod_fdo', $ultimo_registro_eps->cod_fdo)

                            ->update([
                                'tip_nov' => 2,
                                'fec_ter' => $fecha_actual
                            ]);

                        $eps_nueva = fondoSP::select('*')->where('cod_sgs', $empleado['CodigoEps'])->first();
                        $historicoModel = new HistoricoFondosModel();
                        $historicoModel->cod_emp = $empleado['NumeroDocumento'];
                        $historicoModel->cod_fdo = $eps_nueva->cod_fdo;
                        $historicoModel->fec_afi = $fecha_actual_format;
                        $historicoModel->tip_nov = 1;
                        $historicoModel->tip_fdo = 2;
                        $historicoModel->save();
                    }
                    if ($ultimo_registro_afp && $ultimo_registro_afp->codigo_adres != $empleado['CodigoAfp'] && $empleado['CodigoAfp'] != null) {
                        $solo_fecha = \Carbon\Carbon::parse($ultimo_registro_afp->fec_afi)->format('d-m-Y');
                        $afp_nueva = fondoSP::select('*')->where('cod_sgs', $empleado['CodigoAfp'])->first();
                        $registro2 = DB::table('rhh_hisfon')
                            ->where('cod_emp', $ultimo_registro_afp->cod_emp)
                            ->whereDate('fec_afi', $solo_fecha)
                            ->where('cod_fdo', $ultimo_registro_afp->cod_fdo)

                            ->update([
                                'tip_nov' => 2,
                                'fec_ter' => $fecha_actual
                            ]);

                        $historicoModel = new HistoricoFondosModel();
                        $historicoModel->cod_emp = $empleado['NumeroDocumento'];
                        $historicoModel->cod_fdo = $afp_nueva->cod_fdo;
                        $historicoModel->fec_afi = $fecha_actual;
                        $historicoModel->tip_nov = 1;
                        $historicoModel->tip_fdo = 1;
                        $historicoModel->save();
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $error_message = $e->getMessage();
                    $datos = [
                        'num_doc' => $empleado['NumeroDocumento'],
                        'message' => $error_message
                    ];
                    array_push($array_resultado, $datos);
                }
            }
            if (count($array_resultado) > 0) {
                return response()->json([
                    'status' => 'error',
                    'data'   => $datos,
                    'message' => 'No se actualizaron algunos documentos'
                ], 500);
            } else {
                return response()->json([
                    'status' => 'success',
                    'data'   => null,
                    'message' => 'Documentos actualizados correctamente'
                ], 200);
            }
        }
    }
    public function descargarExcelAdres(Request $request)
    {
        try {
            // 1) Lee la carga útil (puede venir como string JSON o array)
            $raw  = $request->input('data');
            $rows = is_array($raw) ? $raw : (json_decode($raw, true) ?? []);

            // 2) Defaults EXACTOS según tu export
            $defaults = [
                'TipoDocumento'                   => null,
                'NumeroDocumento'                 => null,
                'PrimerNombre'                    => null,
                'SegundoNombre'                   => null,
                'PrimerApellido'                  => null,
                'SegundoApellido'                 => null,
                'CodigoEps'                       => null,
                'NombreEps'                       => null,
                'eps_novasoft'                    => null,
                'eps_nombre_novasoft'             => null,
                'FechaAfiliacionEps'              => null,
                'eps_fecha_afiliacion_novasoft'   => null,
                'CertificadoEps'                  => false,
                'CodigoAfp'                       => null,
                'NombreAfp'                       => null,
                'afp_novasoft'                    => null,
                'afp_nombre_novasoft'             => null,
                'FechaAfiliacionAfp'              => null,
                'afp_fecha_afiliacion_novasoft'   => null,
                'CertificadoAfp'                  => false,
                'EsPensionado'                    => false,
                'EnTramitePension'                => null,
                'TipoPension'                     => null,
                'advertencias'                    => [],
            ];

            // 3) Normaliza cada fila
            $data = array_map(function ($row) use ($defaults) {
                // merge defaults para garantizar todas las claves
                $row = $row + $defaults;

                // Booleanos consistentes (acepta "true"/"false", 1/0, etc.)
                $row['CertificadoEps'] = filter_var($row['CertificadoEps'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                $row['CertificadoAfp'] = filter_var($row['CertificadoAfp'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                $row['EsPensionado']   = filter_var($row['EsPensionado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

                // Asegura que advertencias sea array
                if (!is_array($row['advertencias'])) {
                    $row['advertencias'] = array_filter([(string) $row['advertencias']]);
                }

                // (Opcional) Normaliza fechas a YYYY-MM-DD si vienen en otro formato
                foreach (['FechaAfiliacionEps', 'eps_fecha_afiliacion_novasoft', 'FechaAfiliacionAfp', 'afp_fecha_afiliacion_novasoft'] as $k) {
                    if (!empty($row[$k])) {
                        try {
                            $row[$k] = Carbon::parse($row[$k])->toDateString();
                        } catch (\Throwable $e) {
                            // si no parsea, déjalo tal cual
                        }
                    }
                }

                return $row;
            }, $rows);

            return Excel::download(new ConsultaPilaExport($data), 'EmpleadosAdres.xlsx');
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function filtroConsultaMasiva($cadena, $cantidad = null)
    {
        try {
            $permisos = $this->permisos();
            $user = $this->getUserRelaciones();
            $user = $user->getdata(true);
            if ($cantidad == null) {
                $cantidad = 10;
            }
            $consulta = base64_decode($cadena);
            $valores = explode("/", $consulta);
            $campo = $valores[0];
            $operador = $valores[1];
            $valor = $valores[2];
            $valor2 = isset($valores[3]) ? $valores[3] : null;

            $query = HistoricoConsultasApiModel::select(
                'usr_app_historico_masivo_pila.id',
                'usr_app_historico_masivo_pila.consulta_id',
                'usr_app_historico_masivo_pila.created_at',
                'usr_app_historico_masivo_pila.nombre_usuario',
                'usr_app_historico_masivo_pila.usuario_id',


            )->when(!in_array('47', $permisos), function ($query) use ($user) {
                return $query->where(function ($subQuery) use ($user) {
                    $subQuery->Where('usr_app_historico_masivo_pila.usuario_id', $user['id']);
                });
            })
                ->orderby('usr_app_historico_masivo_pila.created_at', 'DESC');
            switch ($operador) {
                case 'Contiene':
                    if ($campo == "consulta_id") {
                        $query->where('usr_app_historico_masivo_pila.consulta_id', 'like', '%' . $valor . '%');
                    } else if ($campo == "nombre_usuario") {
                        $query->where('usr_app_historico_masivo_pila.nombre_usuario', 'like', '%' . $valor . '%');
                    } else {
                        $query->where($campo, 'like', '%' . $valor . '%');
                    }
                    break;
                case 'Igual a':
                    if ($campo == "consulta_id") {
                        $query->where('usr_app_historico_masivo_pila.consulta_id', '=', $valor);
                    } else if ($campo == "nombre_usuario") {
                        $query->where('usr_app_historico_masivo_pila.nombre_usuario', '=', $valor);
                    } else {
                        $query->where($campo, '=', $valor);
                    }
                    break;
                case 'Igual a fecha':
                    $query->whereDate("usr_app_historico_masivo_pila." . $campo, '=', $valor);
                    break;
                case 'Entre':
                    $query->whereDate("usr_app_historico_masivo_pila." . $campo, '>=', $valor)
                        ->whereDate("usr_app_historico_masivo_pila." . $campo, '<=', $valor2);
                    break;
            }

            $result = $query->paginate();

            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }
}