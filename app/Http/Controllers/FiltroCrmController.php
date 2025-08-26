<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class FiltroCrmController extends Controller
{
    public function getAllRadicadosByMes($anio){

        $registrosPorMes= DB::table('usr_app_seguimiento_crm')
        ->select(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\')) as mes'), DB::raw('COUNT(*) as total'))
        ->whereYear('created_at', $anio)
            ->groupBy(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\'))'))
            ->pluck('total', 'mes')
            ->all();
            $registrosPorMesArray = array_fill(1, 12, 0);
            foreach ($registrosPorMes as $mes => $cantidad) {
                $registrosPorMesArray[$mes] = $cantidad;
            }
            return response()->json($registrosPorMesArray);
    }

    public function getRadicadosByMedio($anio)
    {
        // Obtener registros agrupados por mes y tipo de atención
        $registrosPorMes = DB::table('usr_app_seguimiento_crm')
            ->select(DB::raw('MONTH(created_at) as mes'), 'tipo_atencion_id', DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', $anio)
            ->groupBy('tipo_atencion_id', DB::raw('MONTH(created_at)')) // Agrupar por tipo_atencion_id y el mes de created_at
            ->get();
    
        // Obtener los nombres de los medios de atención
        $tipoMedio = DB::table('usr_app_atencion_interacion')->pluck('nombre', 'id')->all();
    
        // Inicializar un array para almacenar los registros por tipo de atención y mes
        $registrosPorMedioArray = [];
    
        // Llenar el array de registros con ceros, por cada tipo de atención y mes
        foreach ($tipoMedio as $estadoId => $estadoNombre) {
            $registrosPorMedioArray[$estadoId] = array_fill(1, 12, 0); // Inicializa con 12 ceros (un cero por mes)
        }
    
        // Actualizar los registros por mes y tipo de atención
        foreach ($registrosPorMes as $registro) {
            $mes = $registro->mes;
            $estadoCargoId = $registro->tipo_atencion_id; // Corregido el nombre de la columna
            $cantidad = $registro->total;
    
            // Actualizar el valor en el array por mes
            $registrosPorMedioArray[$estadoCargoId][$mes] = $cantidad;
        }
    
        // Devolver los nombres de medios y los registros
        $resultadoFinal = [
            'nombres' => $tipoMedio,
            'registros' => $registrosPorMedioArray
        ];
    
        return response()->json($resultadoFinal);
    }

    public function getCompromisosByMes($cedula){
        
    }
}