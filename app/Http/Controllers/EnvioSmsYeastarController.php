<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class EnvioSmsYeastarController extends Controller


{

    private function enviarSmsYeastar($numero, $mensaje)
    {
        $yeastar_ip = Config::get('app.YEASTAR_IP');
        $api_user = Config::get('app.YEASTAR_USER');
        $api_password = Config::get('app.YEASTAR_PASSWORD');

        $gsm_port = 2;
        $encoded_message = urlencode($mensaje);
        $api_url = "http://{$yeastar_ip}/cgi/WebCGI?1500101=" .
            "account={$api_user}&" .
            "password={$api_password}&" .
            "port={$gsm_port}&" .
            "destination={$numero}&" .
            "content={$encoded_message}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            return ['status' => 'error', 'message' => 'Error de cURL: ' . $curl_error];
        }

        if (strpos($response, 'Success') !== false || strpos($response, 'OK') !== false) {
            return ['status' => 'success', 'message' => 'Mensaje enviado correctamente'];
        }

        return ['status' => 'error', 'message' => 'Respuesta inesperada: ' . $response];
    }
    public function envioIndividual(Request $request)
    {
        $numero = $request->num_cel;
        $mensaje = $request->mensaje;
        $response = $this->enviarSmsYeastar($numero, $mensaje);

        if ($response['status'] === 'success') {
            return response()->json($response, 200);
        } else {
            return response()->json($response, 500);
        }
    }

    public function envioMasivo(Request $request)
    {
        if (!$request->hasFile('excel')) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se ha enviado un archivo Excel.'
            ], 400);
        }

        $file = $request->file('excel');

        try {
            $rows = Excel::toCollection(null, $file)[0]; // Lee la primera hoja
            /*    return $rows; */
            $enviados = 0;
            $fallidos = 0;
            $errores = [];

            foreach ($rows as $index => $row) {
                // Saltar la fila 0 si contiene encabezado
                if ($index === 0) continue;

                $numero = trim($row[0] ?? '');
                $mensaje = trim($row[1] ?? '');

                if (!$numero || !$mensaje) {
                    $fallidos++;
                    $errores[] = "Fila " . ($index + 1) . ": Número o mensaje vacío.";
                    continue;
                }

                $response = $this->enviarSmsYeastar($numero, $mensaje);

                if ($response['status'] === 'success') {
                    $enviados++;
                } else {
                    $fallidos++;
                    $errores[] = "Fila " . ($index + 1) . ": " . $response['message'];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Proceso finalizado. Enviados: $enviados, Fallidos: $fallidos",
                'errores' => $errores
            ]);
        } catch (\Exception $e) {
            /*  Log::error("Error al procesar Excel: " . $e->getMessage()); */
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar el archivo Excel.'
            ], 500);
        }
    }
}
