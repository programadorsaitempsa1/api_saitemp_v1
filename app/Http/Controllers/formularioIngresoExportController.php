<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\formularioGestionIngreso;
use App\Exports\FormularioIngresoExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\RegistroIngresoLaboratorio;
use App\Models\FormularioIngresoSeguimiento;
use App\Models\FormularioIngresoSeguimientoEstado;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdenServicioExport;
use App\Exports\ExportMultipleSheets;




class formularioIngresoExportController extends Controller
{
    public function export3($cadena)
    {
        ini_set('memory_limit', '3000M');
        set_time_limit(600);

        // Decodificar filtros
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];

        // Base query
        $query = FormularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('gen_tipide as doc', 'doc.cod_tip', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.created_at',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'cli.razon_social',
                'usr_app_formulario_ingreso.direccion_empresa',
                'tiser.nombre_servicio',
                'doc.des_tip as tipo_identificacion',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'usr_app_formulario_ingreso.numero_contacto',
                'usr_app_formulario_ingreso.correo_notificacion_usuario',
                'usr_app_formulario_ingreso.cargo',
                'usr_app_formulario_ingreso.salario',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.informe_seleccion',
                'usr_app_formulario_ingreso.subsidio_transporte',
                'dep.nombre as departamento',
                'mun.nombre as ciudad',
                'usr_app_formulario_ingreso.eps',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novedad_stradata',
                'usr_app_formulario_ingreso.direccion_laboratorio',
                'usr_app_formulario_ingreso.examenes',
                'usr_app_formulario_ingreso.fecha_examen',
                'usr_app_formulario_ingreso.recomendaciones_examen',
                'usr_app_formulario_ingreso.novedades_examenes',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.observacion_estado',
                'usr_app_formulario_ingreso.afectacion_servicio',
                'usr_app_formulario_ingreso.responsable_corregir',
                'usr_app_formulario_ingreso.correo_notificacion_empresa',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.observacion_estado'
            )
            ->orderBy('usr_app_formulario_ingreso.created_at', 'desc');

        // Aplicar filtros
        $numElementos = count($campo);
        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorActual = $valor_comparar[$i];

            $prefijo = match ($campoActual) {
                'ciudad' => 'mun.',
                'estado_ingreso' => 'est.',
                'razon_social' => 'cli.',
                'nombre_servicio' => 'tiser.',
                default => 'usr_app_formulario_ingreso.',
            };

            $campoSQL = $prefijo . ($campoActual === 'ciudad' || $campoActual === 'estado_ingreso' ? 'nombre' : $campoActual);

            switch ($operadorActual) {
                case 'Menor que':
                    $query->where($campoSQL, '<', $valorActual);
                    break;
                case 'Mayor que':
                    $query->where($campoSQL, '>', $valorActual);
                    break;
                case 'Menor o igual que':
                    $query->where($campoSQL, '<=', $valorActual);
                    break;
                case 'Mayor o igual que':
                    $query->where($campoSQL, '>=', $valorActual);
                    break;
                case 'Igual a número':
                case 'Igual a':
                    $query->where($campoSQL, '=', $valorActual);
                    break;
                case 'Igual a fecha':
                    $query->whereDate($campoSQL, '=', $valorActual);
                    break;
                case 'Entre':
                    $valor2 = $valor_comparar2[$i];
                    $query->whereDate($campoSQL, '>=', $valorActual)->whereDate($campoSQL, '<=', $valor2);
                    break;
                case 'Contiene':
                    $query->where($campoSQL, 'like', '%' . $valorActual . '%');
                    break;
            }
        }


        $data1 = $query->get();
        $ids = $data1->pluck('id');
        $chunks = collect($ids)->chunk(2000);

        $data2 = collect();
        foreach ($chunks as $chunk) {
            $result = DB::table('usr_app_formulario_ingreso_seguimiento_estado AS see')
                ->join('usr_app_estados_ingreso AS ei', 'ei.id', '=', 'see.estado_ingreso_inicial')
                ->join('usr_app_estados_ingreso AS ef', 'ef.id', '=', 'see.estado_ingreso_final')
                ->whereIn('see.formulario_ingreso_id', $chunk->all())
                ->select(
                    'see.formulario_ingreso_id',
                    'see.responsable_inicial',
                    'see.responsable_final',
                    'ei.nombre AS estado_ingreso_inicial',
                    'ef.nombre AS estado_ingreso_final',
                    'see.actualiza_registro',
                    'see.created_at'
                )
                ->orderBy('see.id', 'desc')
                ->get();

            $data2 = $data2->merge($result);
        }

        $data3 = collect();

        foreach ($chunks as $chunk) {
            $result = DB::table('usr_app_registro_ingreso_laboraorio as lab')
                ->join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'lab.laboratorio_medico_id')
                ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
                ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                ->whereIn('lab.registro_ingreso_id', $chunk->all())
                ->select(
                    'lab.registro_ingreso_id',
                    'ciulab.laboratorio as nombre',
                    'mun.nombre as municipio',
                    'dep.nombre as departamento'
                )
                ->get();

            $data3 = $data3->merge($result);
        }

        $data4 = collect();

        foreach ($chunks as $chunk) {
            $result = DB::table('usr_app_formulario_ingreso_seguimiento as se')
                ->join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'se.estado_ingreso_id')
                ->whereIn('se.formulario_ingreso_id', $chunk->all())
                ->select(
                    'se.formulario_ingreso_id',
                    'se.usuario',
                    'ei.nombre as estado',
                    'se.created_at'
                )
                ->orderBy('se.id', 'desc')
                ->get();

            $data4 = $data4->merge($result);
        }

        // Exportar a múltiples hojas
        return Excel::download(new ExportMultipleSheets($data1, $data4, $data3, $data2), 'exporte_multiple.xlsx');

        // 5) Agrupa por formulario para tener un arreglo de seguimientos por ID
        // $allSeguimientos = $allSeguimientos->groupBy('formulario_ingreso_id');

        // foreach ($resultados as $item) {
        //     $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;

        // $laboratorios = RegistroIngresoLaboratorio::join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'usr_app_registro_ingreso_laboraorio.laboratorio_medico_id')
        //     ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
        //     ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
        //     ->where('usr_app_registro_ingreso_laboraorio.registro_ingreso_id', '=', $item->id)
        //     ->select(
        //         'ciulab.id',
        //         'ciulab.laboratorio as nombre',
        //         'mun.id as municipio_id',
        //         'mun.nombre as municipio',
        //         'dep.id as departamento_id',
        //         'dep.nombre as departamento',
        //     )
        //     ->get();

        // foreach ($laboratorios as $laboratorio) {
        //     $item->departamento_lab = $laboratorio->departamento;
        //     $item->municipio_lab = $laboratorio->municipio;
        //     $item->nombre_lab = $laboratorio->nombre;
        // }

        // if ($item->nombre_lab == '') {
        //     $item->departamento_lab = ' ';
        //     $item->municipio_lab = ' ';
        //     $item->nombre_lab = ' ';
        // }

        // $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
        //     ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
        //     ->whereIn('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id',  $ids)
        //     ->select(
        //         'formulario_ingreso_id',
        //         'usr_app_formulario_ingreso_seguimiento_estado.responsable_inicial',
        //         'usr_app_formulario_ingreso_seguimiento_estado.responsable_final',
        //         'ei.nombre as estado_ingreso_inicial',
        //         'ef.nombre as estado_ingreso_final',
        //         'usr_app_formulario_ingreso_seguimiento_estado.actualiza_registro',
        //         'usr_app_formulario_ingreso_seguimiento_estado.created_at',


        //     )
        //     ->orderby('usr_app_formulario_ingreso_seguimiento_estado.id', 'desc')
        //     ->get();

        // $item->seguimiento_e = '';

        // foreach ($seguimiento_estados as $seguimientos_estado) {
        //     $fecha_original = $seguimientos_estado->created_at;
        //     $fecha = $fecha_original->format('d-m-Y, H:i:s');

        //     $seguimiento_e = $seguimientos_estado->estado_ingreso_final . "\n" .
        //         $seguimientos_estado->responsable_final . "\n\n" .
        //         '↑ : Fecha: ' . $fecha . "\n\n" .
        //         $seguimientos_estado->estado_ingreso_inicial . "\n" .
        //         $seguimientos_estado->responsable_inicial . "\n\n" . '-----------------------------------------------' . "\n\n";

        //     $item->seguimiento_e .= $seguimiento_e;
        // }

        // $item->espacio1 = ' ';

        // $seguimiento = FormularioIngresoSeguimiento::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento.estado_ingreso_id')
        //     ->where('usr_app_formulario_ingreso_seguimiento.formulario_ingreso_id', $item->id)
        //     ->select(
        //         'usr_app_formulario_ingreso_seguimiento.usuario',
        //         'ei.nombre as estado',
        //         'usr_app_formulario_ingreso_seguimiento.created_at',

        //     )
        //     ->orderby('usr_app_formulario_ingreso_seguimiento.id', 'desc')
        //     ->get();
        // unset($item->id);

        // $item->nuevaVariable = '';

        // foreach ($seguimiento as $seguimientos) {
        //     $nuevaVariable = $seguimientos->usuario . ' | ' . $seguimientos->estado . ' | ' . $seguimientos->created_at . "\n" . "\n" . "\n";
        //     $item->nuevaVariable .= $nuevaVariable;
        // }

        // $item->espacio2 = ' ';
        // }

        // $data1 = collect([$resultados]);
        // $data2 = collect([$allSeguimientos]);

        // // // return  $resultados;
        // return Excel::download(new OrdenServicioExport($data1, $data2), 'exporte_multiple.xlsx');

        // return (new FormularioIngresoExport($resultados))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
