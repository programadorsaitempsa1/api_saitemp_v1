<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ApiPilaController extends Controller
{
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
        $usuario = Config::get('app.USUARIO_PILA');
        $password = Config::get('app.PASSWORD_PILA');
        $url_validart = Config::get('app.PILA_URL');
        $pila_aplicacion = Config::get('app.PILA_APLICACION');
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
                ])->post($url_validart . $endpoint, $datos);

                if ($response != null && count($response['data']) > 0) {
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
    public function consultaBdReferenciasMasivo(Request $request)
    {

        $url_validart = Config::get('app.PILA_URL');
        $pila_aplicacion = Config::get('app.PILA_APLICACION');
        $endpoint = "/api/Persona/ConsultarPersonaEnBaseDatosReferenciaMasivo";
        $takenToken = $this->getToken();
        // Validación del archivo
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        // Leer el archivo Excel
        $ruta = $request->file('archivo')->store('temporal');

        $path = storage_path("app/" . $ruta);
        $coleccion = Excel::toCollection(null, $path)->first(); // primera hoja

        $datos = [];

        // Convertimos filas a formato requerido
        foreach ($coleccion as $index => $fila) {
            // Saltar la primera fila (encabezado)
            if ($index === 0) continue;

            $datos[] = [
                'TipoDocumento' => trim($fila[0]),
                'NumeroDocumento' => trim((string)$fila[1]), // convertir a string para evitar errores en la API
            ];
        }

        // Estructura final del JSON
        $payload = ['data' => $datos];
        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['data'];
            try {
                $response = Http::withHeaders([
                    'token' => $token
                ])->post($url_validart . $endpoint, $payload);

                // Opcional: borrar el archivo temporal
                Storage::delete($ruta);

                // Retornar respuesta de la API
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
    public function consultarRespuestaMasiva(Request $request)
    {

        $url_validart = Config::get('app.PILA_URL');
        $endpoint = "/api/Persona/ConsultarRespuestaSolicitudPersonaMasivo";
        $takenToken = $this->getToken();
    }
}