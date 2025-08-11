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
        $url_pila   = Config::get('app.PILA_URL');
        $endpoint   = '/api/Persona/ConsultarRespuestaSolicitudPersonaMasivo';

        $takenToken = $this->getToken();

        if ($takenToken['status'] !== 'success') {
            return response()->json(['status' => 'error', 'message' => 'Token inválido'], 403);
        }

        try {
            $token  = $takenToken['token']['data'];

            $payload = [
                'data' => [
                    ['IdSolicitud' => $consulta_id],
                ],
            ];

            $response = Http::withHeaders(['token' => $token])
                ->post($url_pila . $endpoint, $payload);


            if (empty($response['data']) || !is_array($response['data'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No existen datos para la solicitud',
                ], 404);
            }
            $datosEnriquecidos = collect($response['data'])->map(function ($item) {
                $documento = $item['NumeroDocumento'] ?? null;

                $item['eps_novasoft'] = null;
                $item['eps_nombre_novasoft'] = null;
                $item['eps_fecha_afiliacion_novasoft'] = null;
                $item['afp_novasoft'] = null;
                $item['afp_nombre_novasoft'] = null;
                $item['afp_fecha_afiliacion'] = null;

                if (!$documento) return $item;

                // EPS
                $registro = DB::table('rhh_hisfon as h')
                    ->leftJoin('rhh_tbfondos as f', 'f.cod_fdo', '=', 'h.cod_fdo')
                    ->where('h.cod_emp', $documento)
                    ->where('h.tip_fdo', 2)
                    ->orderByDesc('h.fec_afi')
                    ->select('f.cod_sgs', 'f.nom_fdo', 'h.fec_afi')
                    ->first();

                if ($registro) {
                    $item['eps_novasoft'] = $registro->cod_sgs ?? null;
                    $item['eps_nombre_novasoft'] = $registro->nom_fdo ?? null;
                    $item['eps_fecha_afiliacion_novasoft'] = $registro->fec_afi ?? null;
                }

                // AFP
                $afp = DB::table('rhh_hisfon as h')
                    ->leftJoin('rhh_tbfondos as f', 'f.cod_fdo', '=', 'h.cod_fdo')
                    ->where('h.cod_emp', $documento)
                    ->where('h.tip_fdo', 1)
                    ->orderByDesc('h.fec_afi')
                    ->select('f.cod_sgs', 'f.nom_fdo', 'h.fec_afi')
                    ->first();

                if ($afp) {
                    $item['afp_novasoft'] = $afp->cod_sgs ?? null;
                    $item['afp_nombre_novasoft'] = $afp->nom_fdo ?? null;
                    $item['afp_fecha_afiliacion_novasoft'] = $afp->fec_afi ?? null;
                }

                // Advertencias
                $advertencias = [];

                if (
                    $item['CodigoAfp'] != $item['afp_novasoft'] &&
                    $item['CodigoEps'] != $item['eps_novasoft']
                ) {
                    $advertencias[] = 'La AFP y la EPS actuales son diferentes a las de la consulta en ADRES/RUAF; verifique la información.';
                } elseif ($item['CodigoEps'] != $item['eps_novasoft']) {
                    $advertencias[] = 'La EPS actual es diferente a la obtenida en ADRES; verifique la información.';
                } elseif ($item['CodigoAfp'] != $item['afp_novasoft']) {
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
                ->filter(function ($item) {
                    return !empty($item['advertencias']);
                })

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

        $data = json_decode($request->input('data'), true); // convertir JSON string a array
        return Excel::download(new ConsultaPilaExport($data), 'EmpleadosAdres.xlsx');
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
