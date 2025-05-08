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


class formularioIngresoExportController extends Controller
{
    public function export3($cadena)
    {

        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        // return $arraysDecodificados;

        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];

        $query = FormularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->LeftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->leftJoin('gen_tipide as doc', 'doc.cod_tip', 'usr_app_formulario_ingreso.tipo_documento_id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.numero_radicado',
                DB::raw("FORMAT(CAST(usr_app_formulario_ingreso.created_at AS DATE), 'dd/MM/yyyy') as fecha_radicado"),
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'cli.razon_social',
                'usr_app_formulario_ingreso.direccion_empresa',
                'tiser.nombre_servicio',
                'doc.des_tip as Tipo de identificación',
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
                DB::raw("FORMAT(CAST(usr_app_formulario_ingreso.fecha_ingreso AS DATE), 'dd/MM/yyyy') as fecha_ingreso"),
                'usr_app_formulario_ingreso.estado_vacante',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.observacion_estado',



            )
            ->orderBy('usr_app_formulario_ingreso.created_at', 'DESC');
        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];

            $prefijoCampo = '';
            if ($campoActual === 'ciudad') {
                $prefijoCampo = 'mun.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'estado_ingreso') {
                $prefijoCampo = 'est.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'razon_social') {
                $prefijoCampo = 'cli.';
            } elseif ($campoActual === 'nombre_servicio') {
                $prefijoCampo = 'tiser.';
            } else {
                $prefijoCampo = 'usr_app_formulario_ingreso.';
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
                    // $fechaHora = date('Y-m-d H:i:s', strtotime($valorCompararActual));
                    // $query->whereRaw("TRY_CONVERT(datetime, $prefijoCampo$campoActual) = ?", [$fechaHora]);
                    // break;
                    //    return $prefijoCampo .''. $campoActual. '='. $valorCompararActual;
                    $query->whereDate($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Contiene':
                    // return $prefijoCampo . $campoActual . 'LIKE' . '%' . $valorCompararActual . '%';
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
            }
        }

        // Al final, ejecutar la consulta y obtener los resultados
        $resultados = $query->get(); // paginamos los resultados

        foreach ($resultados as $item) {
            $item->fecha_examen = $item->fecha_examen ? date('d/m/Y H:i', strtotime($item->fecha_examen)) : null;

            $laboratorios = RegistroIngresoLaboratorio::join('usr_app_ciudad_laboraorio as ciulab', 'ciulab.id', '=', 'usr_app_registro_ingreso_laboraorio.laboratorio_medico_id')
                ->join('usr_app_municipios as mun', 'mun.id', '=', 'ciulab.ciudad_id')
                ->join('usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
                ->where('usr_app_registro_ingreso_laboraorio.registro_ingreso_id', '=', $item->id)
                ->select(
                    'ciulab.id',
                    'ciulab.laboratorio as nombre',
                    'mun.id as municipio_id',
                    'mun.nombre as municipio',
                    'dep.id as departamento_id',
                    'dep.nombre as departamento',
                )
                ->get();

            foreach ($laboratorios as $laboratorio) {
                $item->departamento_lab = $laboratorio->departamento;
                $item->municipio_lab = $laboratorio->municipio;
                $item->nombre_lab = $laboratorio->nombre;
            }

            if ($item->nombre_lab == '') {
                $item->departamento_lab = ' ';
                $item->municipio_lab = ' ';
                $item->nombre_lab = ' ';
            }

            $seguimiento_estados = FormularioIngresoSeguimientoEstado::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_inicial')
                ->join('usr_app_estados_ingreso as ef', 'ef.id', '=', 'usr_app_formulario_ingreso_seguimiento_estado.estado_ingreso_final')
                ->where('usr_app_formulario_ingreso_seguimiento_estado.formulario_ingreso_id',  $item->id)
                ->select(
                    'usr_app_formulario_ingreso_seguimiento_estado.responsable_inicial',
                    'usr_app_formulario_ingreso_seguimiento_estado.responsable_final',
                    'ei.nombre as estado_ingreso_inicial',
                    'ef.nombre as estado_ingreso_final',
                    'usr_app_formulario_ingreso_seguimiento_estado.actualiza_registro',
                    'usr_app_formulario_ingreso_seguimiento_estado.created_at',


                )
                ->orderby('usr_app_formulario_ingreso_seguimiento_estado.id', 'desc')
                ->get();

            $item->seguimiento_e = '';

            foreach ($seguimiento_estados as $seguimientos_estado) {
                $fecha_original = $seguimientos_estado->created_at;
                $fecha = $fecha_original->format('d-m-Y, H:i:s');

                $seguimiento_e = $seguimientos_estado->estado_ingreso_final . "\n" .
                    $seguimientos_estado->responsable_final . "\n\n" .
                    '↑ : Fecha: ' . $fecha . "\n\n" .
                    $seguimientos_estado->estado_ingreso_inicial . "\n" .
                    $seguimientos_estado->responsable_inicial . "\n\n" . '-----------------------------------------------' . "\n\n";

                $item->seguimiento_e .= $seguimiento_e;
            }

            $item->espacio1 = ' ';

            $seguimiento = FormularioIngresoSeguimiento::join('usr_app_estados_ingreso as ei', 'ei.id', '=', 'usr_app_formulario_ingreso_seguimiento.estado_ingreso_id')
                ->where('usr_app_formulario_ingreso_seguimiento.formulario_ingreso_id', $item->id)
                ->select(
                    'usr_app_formulario_ingreso_seguimiento.usuario',
                    'ei.nombre as estado',
                    'usr_app_formulario_ingreso_seguimiento.created_at',

                )
                ->orderby('usr_app_formulario_ingreso_seguimiento.id', 'desc')
                ->get();
            unset($item->id);

            $item->nuevaVariable = '';

            foreach ($seguimiento as $seguimientos) {
                $nuevaVariable = $seguimientos->usuario . ' | ' . $seguimientos->estado . ' | ' . $seguimientos->created_at . "\n" . "\n" . "\n";
                $item->nuevaVariable .= $nuevaVariable;
            }

            $item->espacio2 = ' ';
        }


        return (new FormularioIngresoExport($resultados))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}