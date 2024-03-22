<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\formularioGestionIngreso;
use App\Models\FormularioIngresoArchivos;
use App\Models\FormularioIngresoResponsable;
use App\Models\FormularioIngresoPendientes;
use App\Models\ListaTrump;
use Carbon\Carbon;
use TCPDF;
use App\Mail\EnvioCorreo;


class formularioGestionIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {

        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'usr_app_formulario_ingreso.responsable_anterior',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'mun.nombre as ciudad',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',
            )
            ->orderby('usr_app_formulario_ingreso.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function consulta_id_trump($id)
    {
        $result = ListaTrump::select(
            'cod_emp',
            'nombre',
            'observacion',
            'fecha',
            'bloqueado',
        )
            ->where('cod_emp', '=', $id)
            ->first();

        if ($result !== null) {
            if ($result->bloqueado == 1) {
                $result->bloqueado = 'Si';
            } else {
                $result->bloqueado = 'No';
            }
            return $result;
        } else {
            $result = formularioGestionIngreso::where('usr_app_formulario_ingreso.numero_identificacion', '=', $id)
                ->whereRaw('created_at BETWEEN DATEADD(MONTH, -2, GETDATE()) AND GETDATE()')
                ->select(
                    'created_at as fecha_radicado',
                    'numero_identificacion',
                    'usr_app_formulario_ingreso.responsable as responsable_ingreso'
                )
                ->first();
            return $result;
        }
    }

    public function actualizaestadoingreso($item_id, $estado_id)
    {
        $usuarios = FormularioIngresoResponsable::where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado_id)
            ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->select(
                'usuario_id',
                'usr.nombres'
            )
            ->get();

        // Obtener el número total de responsables
        $numeroResponsables = $usuarios->count();

        // Obtener el registro de ingreso
        $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
            ->first();

        // Asignar a cada registro de ingreso un responsable
        $indiceResponsable = $registro_ingreso->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable];

        // Actualizar el registro de ingreso con el estado y el responsable
        $registro_ingreso->estado_ingreso_id = $estado_id;
        // $registro_ingreso->responsable = $responsable->nombres;
        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }

    public function actualizaResponsableingreso($item_id, $nombre_responsable)
    {
        $user = auth()->user();
        $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
            ->first();

        $registro_ingreso->responsable_anterior = $registro_ingreso->responsable;
        $registro_ingreso->responsable = $nombre_responsable;
        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }
    public function responsableingresos($estado)
    {
        $usuarios = FormularioIngresoResponsable::join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado)
            ->select(
                'usuario_id',
                'usr.nombres as nombre'
            )
            ->get();
        return response()->json($usuarios);
    }


    public function byid($id)
    {
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_departamentos as dep', 'dep.id', 'mun.departamento_id')
            ->leftJoin('usr_app_paises as pais', 'pais.id', 'dep.pais_id')
            ->leftJoin('usr_app_afp as afp', 'afp.id', 'usr_app_formulario_ingreso.afp_id')
            ->leftJoin('usr_app_estados_ingreso as esti', 'esti.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->leftJoin('usr_app_formulario_ingreso_tipo_servicio as tiser', 'tiser.id', 'usr_app_formulario_ingreso.tipo_servicio_id')
            ->where('usr_app_formulario_ingreso.id', '=', $id)
            ->select(
                'usr_app_formulario_ingreso.id',
                'esti.nombre as estado_ingreso',
                'esti.id as estado_ingreso_id',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'usr_app_formulario_ingreso.cliente_id',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'usr_app_formulario_ingreso.salario',
                'usr_app_formulario_ingreso.municipio_id',
                'mun.nombre as municipio',
                'usr_app_formulario_ingreso.numero_contacto',
                'usr_app_formulario_ingreso.eps',
                'usr_app_formulario_ingreso.afp_id',
                'afp.nombre as afp',
                'usr_app_formulario_ingreso.estradata',
                'usr_app_formulario_ingreso.novedades',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.examenes',
                'usr_app_formulario_ingreso.fecha_examen',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
                'pais.id as pais_id',
                'pais.nombre as pais',
                'usr_app_formulario_ingreso.created_at as fecha_radicado',
                'tiser.nombre_servicio',
                'tiser.id as tipo_servicio_id',
                'usr_app_formulario_ingreso.numero_vacantes',
                'usr_app_formulario_ingreso.numero_contrataciones',
                'usr_app_formulario_ingreso.citacion_entrevista',
                'usr_app_formulario_ingreso.profesional',
                'usr_app_formulario_ingreso.informe_seleccion',
                'usr_app_formulario_ingreso.cambio_fecha',
                'usr_app_formulario_ingreso.numero_radicado',
                'usr_app_formulario_ingreso.direccion_empresa',
                'usr_app_formulario_ingreso.direccion_laboratorio',
                'usr_app_formulario_ingreso.recomendaciones_examen',
                'usr_app_formulario_ingreso.novedad_stradata',
                'usr_app_formulario_ingreso.correo_notificacion_empresa',
                'usr_app_formulario_ingreso.correo_notificacion_usuario',
                'usr_app_formulario_ingreso.novedades_examenes',
            )
            ->first();

        $archivos = FormularioIngresoArchivos::join('usr_app_archivos_formulario_ingreso as fi', 'fi.id', '=', 'usr_app_formulario_ingreso_archivos.arhivo_id')
            ->where('ingreso_id', $id)
            ->select(
                'usr_app_formulario_ingreso_archivos.arhivo_id',
                'usr_app_formulario_ingreso_archivos.ruta',
                'usr_app_formulario_ingreso_archivos.observacion',
                'fi.nombre',
                'fi.tipo_archivo'
            )
            ->get();
        $result['archivos'] = $archivos;
        return response()->json($result);
    }


    public function gestioningresospdf($modulo, $registro_id)
    {
        $formulario = $this->byid($registro_id)->getData();

        $pdf = new TCPDF();
        $pdf->SetTextColor(4, 66, 105);
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetMargins(0, 0, 0);

        $url = public_path('\/upload\/MEMBRETE.png');
        $img_file = $url;
        $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);

        $pdf->Ln(20);

        $html = '<table cellpadding="2" cellspacing="0" style="width: 100%;">
        <tr>
        <td style="text-align: center;">
        <div style="font-size: 16pt; font-weight: bold;">Solicitud de servicio:</div>
        </td>
        </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');


        $combinacion_correos = '';
        if ($formulario->correo_notificacion_usuario != null) {
            $combinacion_correos .=   $formulario->correo_notificacion_empresa . ',' . $formulario->correo_notificacion_usuario;
        } else {
            $combinacion_correos = $formulario->correo_notificacion_empresa;
        }


        $fecha_ingreso = $formulario->fecha_ingreso;
        $numero_identificacion = $formulario->numero_identificacion;
        $nombre_completo = $formulario->nombre_completo;
        $razon_social = $formulario->razon_social;
        $cargo = $formulario->cargo;
        $salario = $formulario->salario;
        $municipio = $formulario->municipio;
        $numero_contacto = $formulario->numero_contacto;
        $laboratorio = $formulario->laboratorio;
        $examenes = $formulario->examenes;
        $fecha_examen = $formulario->fecha_examen;
        $departamento = $formulario->departamento;
        $pais = $formulario->pais;
        $nombre_servicio = $formulario->nombre_servicio;
        $tipo_servicio_id = $formulario->tipo_servicio_id;
        $numero_vacantes = $formulario->numero_vacantes;
        $numero_contrataciones = $formulario->numero_contrataciones;
        $citacion_entrevista = $formulario->citacion_entrevista;
        $profesional = $formulario->profesional;
        $informe_seleccion = $formulario->informe_seleccion;
        $cambio_fecha = $formulario->cambio_fecha;
        $direccion_empresa = $formulario->direccion_empresa;
        $direccion_laboratorio = $formulario->direccion_laboratorio;
        $recomendaciones_examen = $formulario->recomendaciones_examen;
        $ancho_maximo = 70;


        if (strlen($razon_social) < 38) {

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Dirección empresa:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $razon_social != null ? $razon_social : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(3);

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(10);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($nombre_servicio);

            $pdf->MultiCell($ancho_texto + 7, 7, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 'L');
            $pdf->Ln(1);
        } else {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Empresa usuaria:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->Ln(10);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($razon_social);

            $pdf->MultiCell($ancho_texto + 7, 7, $razon_social != null ? $razon_social : 'Sin datos', 0, 'L');

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Dirección empresa:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Tipo de servicio:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $direccion_empresa != null ? $direccion_empresa : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $nombre_servicio != null ? $nombre_servicio : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(3);
        }

        if ($tipo_servicio_id == 2) {

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Número de contrataciones:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->Ln(10);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($numero_contrataciones);

            $pdf->MultiCell($ancho_texto + 7, 7, $numero_contrataciones != null ? $numero_contrataciones : 'Sin datos', 0, 'L');
        }


        if ($tipo_servicio_id == 3 || $tipo_servicio_id == 4) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Número de vacantes:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Citación entrevista:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $numero_vacantes != null ? $numero_vacantes : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $citacion_entrevista != null ? $citacion_entrevista : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(3);

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Profesional:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->Ln(10);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($profesional);

            $pdf->MultiCell($ancho_texto + 7, 7, $profesional != null ? $profesional : 'Sin datos', 0, 'L');


            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Informe selección:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(10);
            $pdf->SetX(20);
            $lineas = explode("\n", wordwrap($informe_seleccion != null ? $informe_seleccion : 'Sin datos', $ancho_maximo, "\n"));

            foreach ($lineas as $linea) {
                $ancho_texto = $pdf->GetStringWidth($linea);
                $pdf->SetX(20);
                $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
            }
        }

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX(20);
        $pdf->Cell(95, 10, 'Fecha de ingreso:', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(95, 10, 'Número de identificación:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->SetX(20);
        $pdf->Cell(10, 1, $fecha_ingreso != null ? $fecha_ingreso : 'Sin datos', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(65, 1, $numero_identificacion != null ? $numero_identificacion : 'Sin datos', 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX(20);
        $pdf->Cell(95, 10, 'Apellidos y nombres:', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(95, 10, 'Número contacto:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->SetX(20);
        $pdf->Cell(10, 1, $nombre_completo != null ? $nombre_completo : 'Sin datos', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(65, 1, $numero_contacto != null ? $numero_contacto : 'Sin datos', 0, 1, 'L');
        $pdf->Ln(2);


        if (strlen($cargo) < 38) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Salario:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $cargo != null ? $cargo : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $salario != null ? $salario : 'Sin datos', 0, 1, 'L');
            $pdf->Ln(2);
        } else {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Cargo:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(10);
            $pdf->SetX(20);
            $lineas = explode("\n", wordwrap($cargo != null ? $cargo : 'Sin datos', $ancho_maximo, "\n"));

            foreach ($lineas as $linea) {
                $ancho_texto = $pdf->GetStringWidth($linea);
                $pdf->SetX(20);
                $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
            }

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Salario:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->Ln(10);
            $pdf->SetX(20);
            $ancho_texto = $pdf->GetStringWidth($salario);

            $pdf->MultiCell($ancho_texto + 7, 7, $salario != null ? $salario : 'Sin datos', 0, 'L');
        }

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX(20);
        $pdf->Cell(95, 10, 'Departamento:', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(95, 10, 'Ciudad:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->SetX(20);
        $pdf->Cell(10, 1, $departamento, 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(65, 1, $municipio, 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX(20);
        $pdf->Cell(95, 10, 'Laboratorio:', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(95, 10, 'Dirección laboratorio:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->SetX(20);
        $pdf->Cell(10, 1, $laboratorio != null ? $laboratorio : 'Sin datos', 0, 0, 'L');

        $pdf->SetX(120);
        $pdf->Cell(65, 1, $direccion_laboratorio != null ? $direccion_laboratorio : 'Sin datos', 0, 1, 'L');
        $pdf->Ln(2);


        if ($tipo_servicio_id == 3 || $tipo_servicio_id == 4) {
            $pdf->AddPage();
            $url = public_path('\/upload\/MEMBRETE.png');
            $img_file = $url;
            $pdf->Image($img_file, -0.5, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
            $pdf->Ln(45);
            if (strlen($examenes) < 30 && strlen($recomendaciones_examen) < 30) {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $examenes != null ? $examenes : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', 0, 1, 'L');
                $pdf->Ln(2);
            } else {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');
                $pdf->SetFont('helvetica', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($examenes != null ? $examenes : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }

                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 0, 'L');
                $pdf->SetFont('helvetica', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }
            }
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Fecha examen:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Cambio fecha:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $fecha_examen != null ? $fecha_examen : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $cambio_fecha != null ? $cambio_fecha : 'N/A', 0, 1, 'L');
            $pdf->Ln(3);
        } else {
            if (strlen($examenes) < 30 && strlen($recomendaciones_examen) < 30) {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 11);

                $pdf->SetX(20);
                $pdf->Cell(10, 1, $examenes != null ? $examenes : 'Sin datos', 0, 0, 'L');

                $pdf->SetX(120);
                $pdf->Cell(65, 1, $recomendaciones_examen != null ? $recomendaciones_examen : 'Sin datos', 0, 1, 'L');
                $pdf->Ln(2);
            } else {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Exámenes:', 0, 0, 'L');
                $pdf->SetFont('helvetica', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($examenes != null ? $examenes : 'Sin datos', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }

                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetX(20);
                $pdf->Cell(95, 10, 'Recomendaciones exámenes:', 0, 0, 'L');
                $pdf->SetFont('helvetica', '', 11);
                $pdf->Ln(10);
                $pdf->SetX(20);
                $lineas = explode("\n", wordwrap($recomendaciones_examen != null ? $recomendaciones_examen : 'N/A', $ancho_maximo, "\n"));

                foreach ($lineas as $linea) {
                    $ancho_texto = $pdf->GetStringWidth($linea);
                    $pdf->SetX(20);
                    $pdf->MultiCell($ancho_texto + 7, 7, $linea, 0, 'L');
                }
            }
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(20);
            $pdf->Cell(95, 10, 'Fecha examen:', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(95, 10, 'Cambio fecha:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(20);
            $pdf->Cell(10, 1, $fecha_examen != null ? $fecha_examen : 'Sin datos', 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->Cell(65, 1, $cambio_fecha != null ? $cambio_fecha : 'N/A', 0, 1, 'L');
            $pdf->Ln(3);
        }


        $pdfPath = storage_path('app/temp.pdf');
        $pdf->Output($pdfPath, 'F');


        $correo = null;
        $correo['subject'] =  'Registro ingreso';
        $correo['body'] = 'Cordial saludo, envío informe de ingreso.';
        $correo['formulario_ingreso'] = $pdfPath;
        $correo['to'] = $combinacion_correos;
        // $correo['to'] = 'villaemanuel1020@gmail.com';
        $correo['cc'] = '';
        $correo['cco'] = '';
        $correo['modulo'] = $modulo;
        $correo['registro_id'] = $registro_id;

        $EnvioCorreoController = new EnvioCorreoController();
        $request = Request::createFromBase(new Request($correo));
        $result = $EnvioCorreoController->sendEmail($request);
        return $result;
    }




    public function filtro($cadena)
    {
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];
        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];

        $query = FormularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->leftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'mun.nombre',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id'
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
                    // Suponiendo que $valor_comparar2 contiene el segundo valor en el rango

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
                    // return $prefijoCampo . $campoActual . 'LIKE' . '%' . $valorCompararActual . '%';
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
                    // default:
                    //     // Manejar el operador desconocido
                    //     break;
            }
        }

        // Al final, ejecutar la consulta y obtener los resultados
        $resultados = $query->paginate(); // paginamos los resultados
        return $resultados;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $result = new formularioGestionIngreso;
        // $result->fecha_ingreso = Carbon::createFromFormat('Y-m-d\TH:i', $request->fecha_ingreo)->format('Y-m-d H:i:s');
        $result->fecha_ingreso = $request->fecha_ingreo;
        $result->numero_identificacion = $request->numero_identificacion;
        $result->nombre_completo = $request->nombre_completo;
        $result->cliente_id = $request->empresa_cliente_id;
        $result->cargo = $request->cargo;
        $result->salario = $request->salario;
        $result->municipio_id = $request->municipio_id;
        $result->numero_contacto = $request->numero_contacto;
        $result->eps = $request->eps;
        $result->afp_id = $request->afp_id;
        $result->estradata = $request->consulta_stradata;
        $result->novedades = $request->novedades;
        $result->laboratorio = $request->laboratorio;
        $result->examenes = $request->examenes;
        if ($request->fecha_examen != null) {
            $result->fecha_examen = Carbon::createFromFormat('Y-m-d\TH:i', $request->fecha_examen)->format('Y-m-d H:i:s');
        }
        if ($request->estado_id == '') {
            $result->estado_ingreso_id = 1;
        } else {
            $result->estado_ingreso_id = $request->estado_id;
        }
        $result->responsable = $user->nombres . ' ' . $user->apellidos;
        $result->tipo_servicio_id = $request->tipo_servicio_id;
        $result->numero_vacantes = $request->numero_vacantes;
        $result->numero_contrataciones = $request->numero_contrataciones;
        if ($request->citacion_entrevista != null) {
            $result->citacion_entrevista = Carbon::createFromFormat('Y-m-d\TH:i', $request->citacion_entrevista)->format('Y-m-d H:i:s');
        }
        $result->profesional = $request->profesional;
        $result->informe_seleccion = $request->informe_seleccion;
        if ($request->cambio_fecha != null) {
            $result->cambio_fecha = Carbon::createFromFormat('Y-m-d\TH:i', $request->cambio_fecha)->format('Y-m-d H:i:s');
        }
        $result->responsable = $request->consulta_encargado;
        $result->novedad_stradata = $request->novedades_stradata;
        $result->correo_notificacion_usuario = $request->correo_candidato;
        $result->correo_notificacion_empresa = $request->correo_empresa;
        $result->direccion_empresa = $request->direccion_empresa;
        $result->direccion_laboratorio = $request->direccion_laboratorio;
        $result->recomendaciones_examen = $request->recomendaciones_examen;
        $result->novedades_examenes = $request->novedades_examenes;

        if ($result->save()) {
            return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $result->id]);
        } else {
            return response()->json(['status' => 'success', 'message' => 'error']);
        }
    }

    public function pendientes(Request $request)
    {
        $user = auth()->user();
        $lista = $request->all();
        foreach ($lista as $item) {
            $existeIngreso = FormularioIngresoPendientes::where('registro_ingreso_id', $item)->first();

            if (!$existeIngreso) {
                $result = new FormularioIngresoPendientes;
                $result->registro_ingreso_id = $item;
                $result->usuario_id = $user->id;
                $result->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Tareas pendientes agregadas exitosamente.']);
    }

    public function pendientes2($cantidad)
    {

        // return 'prueba';
        $user = auth()->user();
        // return $user->id;
        $result = formularioGestionIngreso::leftJoin('usr_app_clientes as cli', 'cli.id', 'usr_app_formulario_ingreso.cliente_id')
            ->leftJoin('usr_app_municipios as mun', 'mun.id', 'usr_app_formulario_ingreso.municipio_id')
            ->LeftJoin('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->LeftJoin('usr_app_formulario_ingreso_pendientes as pen', 'pen.registro_ingreso_id', 'usr_app_formulario_ingreso.id')
            ->where('pen.usuario_id', '=', $user->id)
            ->select(
                'usr_app_formulario_ingreso.id',
                'usr_app_formulario_ingreso.created_at',
                'usr_app_formulario_ingreso.fecha_ingreso',
                'est.nombre as estado_ingreso',
                'usr_app_formulario_ingreso.responsable',
                'usr_app_formulario_ingreso.responsable_anterior',
                'usr_app_formulario_ingreso.numero_identificacion',
                'usr_app_formulario_ingreso.nombre_completo',
                'cli.razon_social',
                'usr_app_formulario_ingreso.cargo',
                'mun.nombre as ciudad',
                'usr_app_formulario_ingreso.laboratorio',
                'usr_app_formulario_ingreso.responsable as responsable_ingreso',
                'est.id as estado_ingreso_id',
                'est.color as color_estado',
            )
            ->orderby('usr_app_formulario_ingreso.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);

        // return response()->json(['status' => 'success', 'message' => 'Tareas pendientes agregadas exitosamente.']);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $ingreso_id)
    {

        try {
            $documentos = $request->all();
            $value = '';
            $id = '';
            $ids = [];
            $observacion = '';
            $observaciones = [];
            $rutas = [];

            // $directorio = public_path('upload/');
            // $archivos = glob($directorio . '*');

            // foreach ($archivos as $archivo) {
            //     $nombreArchivo = basename($archivo);
            //     if (strpos($nombreArchivo, '_' . $ingreso_id . '_') !== false) {
            //         unlink($archivo);
            //     }
            // }

            foreach ($documentos as $item) {
                $contador = 0;
                if (!is_numeric($item) && !is_string($item)) {
                    $nombreArchivoOriginal = $item->getClientOriginalName();
                    $nuevoNombre = '_' . $ingreso_id . "_" . $nombreArchivoOriginal;

                    $carpetaDestino = './upload/';
                    $item->move($carpetaDestino, $nuevoNombre);
                    $item = ltrim($carpetaDestino, '.') . $nuevoNombre;
                    array_push($rutas, $item);
                    $value .= $item . ' ';
                } else {
                    if (is_numeric($item)) {
                        array_push($ids, $item);
                        $id .= $item . ' ';
                    } else {
                        array_push($observaciones, $item);
                        $observacion .= $item . ' ';
                    }
                }
                $contador++;
            }

            for ($i = 0; $i < count($ids); $i++) {
                $documento = new FormularioIngresoArchivos;
                $documento->arhivo_id = $ids[$i];
                $documento->ruta = $rutas[$i];
                $documento->observacion = $observaciones[$i];
                $documento->ingreso_id = $ingreso_id;
                $documento->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Formulario guardado exitosamente']);
        } catch (\Exception $e) {
            //throw $th;
            // $cliente = cliente::find($ingreso_id);
            // $cliente->delete();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intente nuevamente, si el problema persiste por favor contacte al administrador del sitio']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $result = formularioGestionIngreso::find($id);
        $result->fecha_ingreso = $request->fecha_ingreo;
        $result->numero_identificacion = $request->numero_identificacion;
        $result->nombre_completo = $request->nombre_completo;
        $result->cliente_id = $request->empresa_cliente_id;
        $result->cargo = $request->cargo;
        $result->salario = $request->salario;
        $result->municipio_id = $request->municipio_id;
        $result->numero_contacto = $request->numero_contacto;
        $result->eps = $request->eps;
        $result->afp_id = $request->afp_id;
        $result->estradata = $request->consulta_stradata;
        $result->novedades = $request->novedades;
        $result->laboratorio = $request->laboratorio;
        $result->examenes = $request->examenes;
        $result->fecha_examen = $request->fecha_examen;
        $result->estado_ingreso_id = 1;
        $result->tipo_servicio_id = $request->tipo_servicio_id;
        $result->numero_vacantes = $request->numero_vacantes;
        $result->numero_contrataciones = $request->numero_contrataciones;
        $result->citacion_entrevista = $request->citacion_entrevista;
        $result->profesional = $request->profesional;
        $result->informe_seleccion = $request->informe_seleccion;
        $result->cambio_fecha = $request->cambio_fecha;
        $result->responsable = $request->consulta_encargado;
        $result->estado_ingreso_id = $request->estado_id;
        $result->novedad_stradata = $request->novedades_stradata;
        $result->correo_notificacion_usuario = $request->correo_candidato;
        $result->correo_notificacion_empresa = $request->correo_empresa;
        $result->direccion_empresa = $request->direccion_empresa;
        $result->direccion_laboratorio = $request->direccion_laboratorio;
        $result->recomendaciones_examen = $request->recomendaciones_examen;
        $result->novedades_examenes = $request->novedades_examenes;


        if ($result->save()) {
            // return response()->json(['status' => 'success', 'message' => 'ok']);
            return response()->json(['status' => '200', 'message' => 'ok', 'registro_ingreso_id' => $result->id]);
        } else {
            return response()->json(['status' => 'success', 'message' => 'error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = formularioGestionIngreso::find($id);
        if ($result->delete()) {
            return response()->json("registro borrado Con Exito");
        } else {
            return response()->json("Error al borrar registro");
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = FormularioIngresoPendientes::where('registro_ingreso_id', '=', $request->id[$i])->first();
                // return $result;
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
