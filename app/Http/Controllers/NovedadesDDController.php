<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NovedadesDD;
use Illuminate\Support\Facades\DB;

class NovedadesDDController extends Controller
{
    public function index($cantidad)
    {
        $result = NovedadesDD::join('usr_app_usuarios_internos as us', 'us.usuario_id', 'usr_app_novedades_dd.usuario_corrige')
            ->join('usr_app_clientes as cli', 'cli.id', 'usr_app_novedades_dd.registro_cliente_id')
            ->select(
                'usr_app_novedades_dd.id',
                'cli.razon_social',
                'cli.numero_radicado',
                DB::raw("CONCAT(us.nombres,' ',us.apellidos)  AS nombre_usuario_corregir"),
                'usr_app_novedades_dd.observaciones',
                'usr_app_novedades_dd.usuario_guarda',
                'usr_app_novedades_dd.created_at',
            )
            ->orderby('usr_app_novedades_dd.id', 'DESC')
            ->paginate($cantidad);



        return response()->json($result);
    }
}