<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

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
        $takenToken = $this->getToken();

        if ($takenToken['status'] == 'success') {
            $token = $takenToken['token']['data'];

            return $token;
        }
    }
}
