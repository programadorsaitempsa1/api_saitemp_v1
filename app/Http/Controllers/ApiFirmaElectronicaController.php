<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use App\Models\cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\HistoricoContratosDDModel;
use App\Http\Controllers\formularioDebidaDiligenciaController;
use App\Http\Controllers\EstadosFirmaController;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Carbon;

class ApiFirmaElectronicaController extends Controller

{


    public function takeTokenValidart()
    {
        $usuario = Config::get('app.USUARIO_VALIDART');
        $password =  Config::get('app.PASSWORD_VALIDART');
        $url_validart = Config::get('app.VALIDART_URL');
        $datos = [
            "username" => $usuario,
            "password" => $password
        ];
        try {

            $response = Http::post($url_validart . '/api/users/authenticate', $datos);
            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'token' => $response->json()
                ];
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function uploadFileValidarT($id)
    {
        $url_validart = Config::get('app.VALIDART_URL');
        $end_point = '/api/Transaccion/upload/doc';
        $takenToken = $this->takeTokenValidart();

        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['token'];



            try {
                // Generar el PDF adicional llamando a `generarPdf`
                $controllerDD = new formularioDebidaDiligenciaController;
                $generatedPdfPath = $controllerDD->generarPdf($id, false);
                $generatedContratoPath = $controllerDD->generarContrato2($id, false);


                // Combinar PDFs usando FPDI
                $fpdi = new Fpdi();
                $outputPdfPath = tempnam(sys_get_temp_dir(), 'fpdi_' . Carbon::now()->timestamp) . '.pdf';

                // Importar el PDF recibido y agregar sus páginas

                $pageCount = $fpdi->setSourceFile($generatedContratoPath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $fpdi->AddPage();
                    $templateId = $fpdi->importPage($i);
                    $fpdi->useTemplate($templateId);
                }

                // Importar el PDF generado y agregar sus páginas
                $pageCount2 = $fpdi->setSourceFile($generatedPdfPath);
                for ($i = 1; $i <= $pageCount2; $i++) {
                    $fpdi->AddPage();
                    $templateId = $fpdi->importPage($i);
                    $fpdi->useTemplate($templateId);
                }

                // Guardar el PDF combinado

                $fpdi->Output($outputPdfPath, 'F');

                // Subir el PDF combinado
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])
                    ->asMultipart()
                    ->post($url_validart . $end_point, [
                        'file' => fopen($outputPdfPath, 'r'),
                        'filename' => 'combined_' . $id . Carbon::now()->timestamp,
                    ]);

                if ($response->successful()) {
                    DB::table('usr_app_historico_contratos_dd')
                        ->where('cliente_id', $id)
                        ->update(['activo' => 0]);

                    $contrato = new HistoricoContratosDDModel();
                    $contrato->contrato_firma_id = $response->json()['id'];
                    $contrato->cliente_id = $id;
                    $contrato->activo = 1;
                    $contrato->save();

                    return [
                        'status' => 'success',
                        'response' => $response->json(),
                    ];
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $response->body()
                    ], $response->status());
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $takenToken['message'] ?? 'No se pudo obtener el token'
            ]);
        }
    }

    public function firmaEstandar(Request $request, $id)
    {
        /*   $contratos = HistoricoContratosDDModel::all();

        // Retornar la respuesta en formato JSON
        return response()->json([
            'status' => 'success',
            'data' => $contratos,
        ], 200);
 */
        $user = auth()->user();
        $url_validart = Config::get('app.VALIDART_URL');
        $end_point = '/api/Transaccion/create';
        $firmantes = $request['firmantes'];
        $contrato = HistoricoContratosDDModel::where('usr_app_historico_contratos_dd.cliente_id', '=', $id)->where('activo', '=', 1)
            ->first();
        if ($contrato['contrato_firma_id'] != "" && $contrato['contrato_firma_id'] != null) {
            $datos = [
                "documentoid" => $contrato['contrato_firma_id'],
                "Modificar" => true,
                "Firmantes" => $firmantes,
                "NombreCreador" => "Saitemp S.A",
                "Notificacion" => "5",
                "Callback" => "https://debidadiligencia.saitempsa.com:8484/aplicaciones/api2/public/api/v1/seguimientocrm2",
                "DiasVence" => "30",
                "FirmaGrafica" => "0"
            ];
            try {
                $takenToken = $this->takeTokenValidart();
                if ($takenToken['status'] == 'success') {
                    $token = $takenToken['token']['token'];
                    $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])->post($url_validart . $end_point, $datos);
                    if ($response->successful()) {
                        $contrato->transaccion_id = $response->json()['id'];
                        $contrato->usuario_envia = $user->id;
                        $contrato->correo_enviado_cliente = $firmantes[0]['Email'];
                        $contrato->correo_enviado_empresa = $firmantes[1]['Email'];
                        $contrato->estado_contrato = "Enviado";
                        $contrato->save();
                        return [
                            'status' => 'success',
                            'token' => $response->json(),
                            'cliente' => $contrato
                        ];
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => $response->json()
                        ], $response->status());
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $takenToken['message'] ?? 'No se pudo obtener el token'
                    ]);
                }
            } catch (\Exception $e) {
                return $e;
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }
    public function callBackFirmado(Request $request)
    {
        $id = $request['id'];
        $contrato = HistoricoContratosDDModel::where('usr_app_historico_contratos_dd.transaccion_id', '=', $id)->where('activo', '=', 1)
            ->first();


        if (!$contrato) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado para la transacción especificada',
            ], 404);
        }

        $estado = $request['estado'];
        if ($estado === "true") {
            $url = $request['url'];
            $controllerDD = new formularioDebidaDiligenciaController;
            $formulario = $controllerDD->getbyid($id, false);
            $posicion_estado_firma_actual = $formulario->posicion_estado_firma;
            $posicion_estado_firma_nuevo = $posicion_estado_firma_actual + 1;
            $estadosController = new EstadosFirmaController;
            $estado_firma_nuevo = $estadosController->byOrder($posicion_estado_firma_nuevo);
            if ($estado_firma_nuevo) {
                $controllerDD->actualizaestadofirma($id, $estado_firma_nuevo, null, $formulario->responsable_id, $formulario->estado_firma_id);
            }
            try {
                // Descargar archivo desde la URL
                $response = Http::get($url);
                if ($response->successful()) {
                    $fileContent = $response->body();
                    $fileName = $id . '_evidencia.pdf';
                    $filePath = public_path('upload/contratosFirmados/' . $fileName);
                    if (!file_exists(public_path('upload/contratosFirmados'))) {
                        mkdir(public_path('upload/contratosFirmados'), 0755, true);
                    }
                    file_put_contents($filePath, $fileContent);
                    $relativePath = 'upload/contratosFirmados/' . $fileName;
                    $contrato->ruta_contrato = $relativePath;
                    $contrato->firmado_empresa = 1;
                    $contrato->firmado_cliente = 1;
                    $contrato->estado_contrato =  "Firmado";
                    $contrato->save();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Archivo descargado y guardado exitosamente',
                        'file_path' => asset($relativePath),
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No se pudo descargar el archivo desde la URL proporcionada',
                    ], 404);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'El estado no permite procesar la solicitud',
            ], 404);
        }
    }
    public function reenvioFirmantes($id)
    {
        $url_validart = Config::get('app.VALIDART_URL');
        $end_point = '/api/Transaccion/reenvio/' . $id;
        $takenToken = $this->takeTokenValidart();

        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['token'];
            try {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])->get($url_validart . $end_point);
                if ($response->successful()) {
                    return [
                        'status' => 'success',
                        'response' => $response->json(),
                    ];
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $response->json()
                    ], $response->status());
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $takenToken['message'] ?? 'No se pudo obtener el token'
            ]);
        }
    }
    public function anularContrato(Request $request, $id)
    {
        $contrato = HistoricoContratosDDModel::where('usr_app_historico_contratos_dd.transaccion_id', '=', $id)->where('activo', '=', 1)
            ->first();
        $url_validart = Config::get('app.VALIDART_URL');
        $end_point = '/api/Transaccion/transaccionanular/anular';
        $takenToken = $this->takeTokenValidart();
        $motivo = $request['motivo'];
        $datos = [
            'Id' => $id,
            'Motivo' => $motivo
        ];
        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['token'];
            try {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])->post($url_validart . $end_point, $datos);
                if ($response->successful()) {
                    $contrato->estado_contrato = "Anulado";
                    $contrato->activo = 0;
                    $contrato->save();
                    return [
                        'status' => 'success',
                        'response' => $response->json(),

                    ];
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $response->json()
                    ], $response->status());
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $takenToken['message'] ?? 'No se pudo obtener el token'
            ]);
        }
    }

    public function consultaFirmantes($id)
    {
        $contrato = HistoricoContratosDDModel::where('usr_app_historico_contratos_dd.transaccion_id', '=', $id)->where('activo', '=', 1)
            ->first();
        $url_validart = Config::get('app.VALIDART_URL');
        $end_point = '/api/Transaccion/firmantes/' . $id;
        $takenToken = $this->takeTokenValidart();

        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['token'];
            try {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])->get($url_validart . $end_point);
                if ($response->successful()) {
                    $firmantes = $response->json();
                    foreach ($firmantes as $firmante) {
                        if ($firmante['email'] == $contrato->correo_enviado_empresa) {
                            if ($firmante['estado'] == 1) {
                                $contrato->firmado_empresa = 1;
                                $contrato->estado_contrato = "Firmado";
                                $fechaOriginal = $firmante['fecha'];
                                $fechaConvertida = Carbon::parse($fechaOriginal)->format('Y-m-d H:i:s');
                                $contrato->fecha_firma_empresa = $fechaConvertida;
                            }
                            if ($firmante['estado'] == 2) {
                                $contrato->firmado_empresa = 0;
                                $contrato->estado_contrato = "Anulado";
                            }
                        }
                        if ($firmante['email'] == $contrato->correo_enviado_cliente) {
                            if ($firmante['estado'] == 1) {
                                $contrato->firmado_cliente = 1;
                                $fechaOriginal = $firmante['fecha'];
                                $fechaConvertida = Carbon::parse($fechaOriginal)->format('Y-m-d H:i:s');
                                $contrato->estado_contrato = "Firmado por el cliente";
                                $contrato->fecha_firma_cliente = $fechaConvertida;
                            }
                            if ($firmante['estado'] == 2) {
                                $contrato->firmado_cliente = 0;
                                $contrato->estado_contrato = "Anulado";
                            }
                        }
                    }
                    if ($contrato->firmado_cliente == 1 && $contrato->firmado_empresa = 1) {
                        $contrato->estado_contrato = "Firmado";
                    }
                    $contrato->save();
                    return [
                        'status' => 'success',
                        'response' => $response->json(),
                        'contrato' => $contrato
                    ];
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $response->json()
                    ], $response->status());
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $takenToken['message'] ?? 'No se pudo obtener el token'
            ]);
        }
    }
    public function consultaProcesoFirma($id)
    {
        $contrato = HistoricoContratosDDModel::where('usr_app_historico_contratos_dd.transaccion_id', '=', $id)->where('activo', '=', 1)
            ->first();
        $url_validart = Config::get('app.VALIDART_URL');
        $end_point = '/api/Transaccion/' . $id;
        $takenToken = $this->takeTokenValidart();
        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['token'];
            try {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])->get($url_validart . $end_point);
                if ($response->successful()) {
                    if ($response['estado'] == true) {
                        $url = $response['url'];
                        if ($url) {
                            $respuesta = Http::get($url);
                            if ($respuesta->successful()) {
                                $fileContent = $respuesta->body();
                                $fileName = $id . '_evidencia.pdf';
                                $filePath = public_path('upload/contratosFirmados/' . $fileName);
                                if (!file_exists(public_path('upload/contratosFirmados'))) {
                                    mkdir(public_path('upload/contratosFirmados'), 0755, true);
                                }
                                file_put_contents($filePath, $fileContent);
                                $relativePath = 'upload/contratosFirmados/' . $fileName;
                                $contrato->ruta_contrato = $relativePath;
                                $contrato->firmado_empresa = 1;
                                $contrato->firmado_cliente = 1;
                                $contrato->estado_contrato = "Firmado";
                                $contrato->save();
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Archivo descargado y guardado exitosamente',
                                    'file_path' => asset($relativePath),
                                ]);
                            }
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'message' => 'No se pudo descargar el archivo desde la URL proporcionada',
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'El archivo no se encuentra firmado',
                        ], 400);
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
    }
    public function verContrato($id)
    {
        $contrato = HistoricoContratosDDModel::where('usr_app_historico_contratos_dd.transaccion_id', '=', $id)->where('activo', '=', 1)
            ->first();
        if ($contrato) {
            $rutaArchivo = public_path($contrato->ruta_contrato);

            if (file_exists($rutaArchivo)) {
                return response()->file($rutaArchivo, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($rutaArchivo) . '"',
                ]);
            }
        }
        return response()->json(['error' => 'Archivo no encontrado']);
    }
}
