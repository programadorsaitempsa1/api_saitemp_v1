<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NovedadesDD;
use Illuminate\Support\Facades\DB;
use App\Exports\SalidasNoConformesDDExport;

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


    public function sncddfiltro($cadena, $cantidad, $export = false)
    {
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];



        $query = NovedadesDD::join('usr_app_usuarios_internos as us', 'us.usuario_id', 'usr_app_novedades_dd.usuario_corrige')
            ->join('usr_app_clientes as cli', 'cli.id', 'usr_app_novedades_dd.registro_cliente_id')
            ->orderby('usr_app_novedades_dd.id', 'DESC');
        if ($export) {
            $query->select(
                'usr_app_novedades_dd.id',
                'cli.razon_social',
                'cli.numero_radicado',
                DB::raw("CONCAT(us.nombres,' ',us.apellidos)  AS nombre_usuario_corregir"),
                'usr_app_novedades_dd.observaciones',
                'usr_app_novedades_dd.usuario_guarda',
                'usr_app_novedades_dd.created_at',
            );
        } else {
            $query->select(
                'usr_app_novedades_dd.id',
                'cli.razon_social',
                'cli.numero_radicado',
                DB::raw("CONCAT(us.nombres,' ',us.apellidos)  AS nombre_usuario_corregir"),
                'usr_app_novedades_dd.observaciones',
                'usr_app_novedades_dd.usuario_guarda',
                'usr_app_novedades_dd.created_at',
            );
        }
        /*         return $campo;
 */
        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];

            $prefijoCampo = '';
            if ($campoActual === 'razon_social') {
                $prefijoCampo = 'cli.';
                $campoActual = 'razon_social';
            } elseif ($campoActual === 'numero_radicado') {
                $prefijoCampo = 'cli.';
                $campoActual = 'numero_radicado';
            } elseif ($campoActual === 'nombre_usuario_corregir') {
                $prefijoCampo = 'us.';
                $campoActual = 'nombres';
            } else {
                $prefijoCampo = 'usr_app_novedades_dd.';
            }

            switch ($operadorActual) {
                case 'Menor que':
                    $query->where($prefijoCampo . $campoActual, '<', $valorCompararActual);
                    break;
                case 'Mayor que':
                    $query->where($prefijoCampo . $campoActual, '>', $valorCompararActual);
                    break;
                case 'Menor o igual que':
                    $query->where($prefijoCampo . $campoActual, '<=', $valorCompararActual);
                    break;
                case 'Mayor o igual que':
                    $query->where($prefijoCampo . $campoActual, '>=', $valorCompararActual);
                    break;
                case 'Igual a número':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Entre':
                    $valorComparar2Actual = $valor_comparar2[$i];
                    $query->whereDate($prefijoCampo . $campoActual, '>=', $valorCompararActual);
                    $query->whereDate($prefijoCampo . $campoActual, '<=', $valorComparar2Actual);
                    break;
                case 'Igual a':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Igual a fecha':
                    $query->whereDate($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Contiene':
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
            }
        }

        $result = $query->paginate($cantidad);
        if ($export) {
            return $result;
        }
        return response()->json($result);
    }

    public function exportsncdd($cadena, $cantidad = 10,)
    {
        $result = $this->sncddfiltro($cadena, $cantidad, true);
        return (new SalidasNoConformesDDExport($result))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}