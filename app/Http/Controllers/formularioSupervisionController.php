<?php

namespace App\Http\Controllers;

use App\Mail\EnvioCorreo;
use App\Models\FormularioSupervision;
use App\Models\ConceptoFormularioSup;
use App\Models\ImagenObservacion;
use App\Models\Municipios;
use App\Models\User;
use App\Models\ListaConceptosFormularioSup;
use App\Models\CorreoClienteFormularioSup;
use App\Models\ElementoeppFormularioSub;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Undefined;
use TCPDF;


class formularioSupervisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = FormularioSupervision::join('cxc_cliente', 'cxc_cliente.cod_cli', '=', 'usr_app_formulario_supervision.cliente_id')
            ->join('Saitemp_V3.dbo.usr_app_municipios as mun', 'mun.id', '=', 'usr_app_formulario_supervision.municipio')
            ->join('Saitemp_V3.dbo.usr_app_departamentos as dep', 'dep.id', '=', 'mun.departamento_id')
            ->join('Saitemp_V3.dbo.usr_app_usuarios as user', 'user.id', '=', 'usr_app_formulario_supervision.supervisor_id')
            ->select(
                'usr_app_formulario_supervision.id',
                'cxc_cliente.nom_cli as nombre_cliente',
                'cxc_cliente.nit_cli as nit',
                'usr_app_formulario_supervision.direccion',
                'mun.nombre as municipio',
                'dep.nombre as departamento',
                'usr_app_formulario_supervision.descripcion',
                'user.nombres as nombres_supervisor',
                'user.apellidos as apellidos_supervisor',
            )
            ->orderBy('usr_app_formulario_supervision.id', 'DESC')
            ->paginate();

        $result->transform(function ($item) {
            $item->nombre_completo = $item->nombres_supervisor . ' ' . $item->apellidos_supervisor;
            unset($item->nombres_supervisor);
            unset($item->apellidos_supervisor);
            return $item;
        });

        return response()->json($result);
    }

    public function formById($id)
    {
        $formulario = FormularioSupervision::join('cxc_cliente', 'cxc_cliente.cod_cli', '=', 'usr_app_formulario_supervision.cliente_id')
            ->select(
                'usr_app_formulario_supervision.id',
                'usr_app_formulario_supervision.fecha_hora',
                'usr_app_formulario_supervision.supervisor_id',
                'usr_app_formulario_supervision.persona_contactada',
                'usr_app_formulario_supervision.direccion',
                'usr_app_formulario_supervision.municipio',
                'usr_app_formulario_supervision.firma_supervisor',
                'usr_app_formulario_supervision.firma_persona_contactada',
                'cxc_cliente.cod_cli',
                'cxc_cliente.nom_cli as nombre_cliente',
                'usr_app_formulario_supervision.descripcion',
            )
            ->where('usr_app_formulario_supervision.id', '=', $id)
            ->get();

        $conceptos = ConceptoFormularioSup::select(
            'concepto_id',
            'estado_concepto_id',
        )
            ->where('formulario_id', '=', $id)
            ->get();

        $elementospp = ElementoeppFormularioSub::select(
            'concepto_id',
            'estado_concepto_id',
            'observacion',
        )
            ->where('formulario_id', '=', $id)
            ->get();

        $observaciones = ImagenObservacion::select(
            'imagen_observacion',
            'observacion',
        )
            ->where('formulario_id', '=', $id)
            ->get();

        $supervisor = User::select(
            'nombres',
            'apellidos',
        )
            ->where('id', '=', $formulario[0]->supervisor_id)
            ->get();

        $ubicacion = Municipios::join('usr_app_departamentos as dep', 'dep.id', '=', 'usr_app_municipios.departamento_id')
            ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
            ->select(
                'usr_app_municipios.id as municipio_id',
                'usr_app_municipios.nombre as municipio',
                'dep.id as departamento_id',
                'dep.nombre as departamento',
                'pais.id as pais_id',
                'pais.nombre as pais',
            )
            ->where('usr_app_municipios.id', '=', $formulario[0]->municipio)
            ->get();

        $formulario[0]['supervisor'] = $supervisor[0]->nombres . ' ' . $supervisor[0]->apellidos;
        $formulario[0]['municipio_id'] = $ubicacion[0]->municipio_id;
        $formulario[0]['municipio'] = $ubicacion[0]->municipio;
        $formulario[0]['departamento_id'] = $ubicacion[0]->departamento_id;
        $formulario[0]['departamento'] = $ubicacion[0]->departamento;
        $formulario[0]['pais_id'] = $ubicacion[0]->pais_id;
        $formulario[0]['pais'] = $ubicacion[0]->pais;


        $formulario[0]['conceptos'] = $conceptos;
        $formulario[0]['observaciones'] = $observaciones;
        $formulario[0]['elementos_pp'] = $elementospp;
        // return response()->json($conceptos);
        return response()->json($formulario[0]);
    }

    public function crearPdf($formulario_id, $correo_cliente = null)
    {

        $formulario = $this->formById($formulario_id)->getData();

        $ListaConceptosFormularioSupController = new ListaConceptosFormularioSupController;
        $lista_conceptos = $ListaConceptosFormularioSupController->index()->getData();

        $lista_conceptos_epp = $ListaConceptosFormularioSupController->lementospp()->getData();


        $pdf = new TCPDF();

        $pdf->AddPage();
        $pdf->SetTextColor(52, 51, 51);


        $url = public_path('\/upload\/logo_alinstante.JPG');
        $image_file = $url;

        $html = '<table cellpadding="2" cellspacing="0" border="1">
        <tr>
            <td style="width: 130px; text-align: center; vertical-align: middle;">
                <img src="' . $image_file . '" width="70" height="auto" style="margin: 0 auto; display: block;">
            </td>
            <td style="font-size: 16pt; font-weight: bold; width: 279px; text-align: center; vertical-align: middle;">
            <div style="position: relative; top: 50%; transform: translateY(-50%);">
                    Formulario de supervisión
                </div>
            </td>
            <td style="font-size: 12pt; width: 130px; text-align: center; vertical-align: middle;">
            <p>Versión:1</p>
        </td>
        </tr>
     </table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->SetFont('helvetica', '', 11);
        $texto_encargado = $formulario->supervisor;
        $texto_contactada = $formulario->persona_contactada;
        $texto_fecha = $formulario->fecha_hora;
        $texto_cliente = $formulario->nombre_cliente;
        $texto_direccion = $formulario->direccion;
        $texto_departamento = $formulario->departamento;
        $texto_ciudad = $formulario->municipio;
        $texto_asunto = $formulario->descripcion;

        $pdf->SetMargins(10, 10, 10, 10);

        $anchoPagina = $pdf->getPageWidth();
        $alturaPagina = $pdf->getPageHeight();

        $pdf->Rect(10, 10, $anchoPagina - 20, $alturaPagina - 25);

        $fechaActual = date("d-m-Y, H:i");

        $texto_fecha_hora = $fechaActual;

        if (strlen($texto_direccion) < 38) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Fecha y hora:', 0, 0, 'L');

            $pdf->SetX(110);
            $pdf->Cell(95, 10, 'Dirección:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);


            $pdf->SetX(10);
            $pdf->Cell(27, 1, $texto_fecha_hora, 0, 0, 'L');

            $pdf->SetX(110);

            $pdf->Cell(30, 1, $texto_direccion, 0, 1, 'L');
            $pdf->Ln(3);
        } else {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Fecha y hora:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->Ln(10);
            $pdf->SetX(10);
            $ancho_texto = $pdf->GetStringWidth($texto_fecha_hora);

            $altura_celda = 7;

            $pdf->MultiCell($ancho_texto + 7, $altura_celda, $texto_fecha_hora, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Dirección:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(10);
            $pdf->SetX(10);
            $ancho_texto = $pdf->GetStringWidth($texto_direccion);

            $altura_celda = 7;

            $pdf->MultiCell($ancho_texto + 7, $altura_celda, $texto_direccion, 0, 'L');
        }

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Asunto:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Ln(10);
        $pdf->SetX(10);
        $ancho_texto = $pdf->GetStringWidth($texto_asunto);

        $altura_celda = 7;

        $pdf->MultiCell($ancho_texto + 7, $altura_celda, $texto_asunto, 0, 'L');
        $pdf->Ln(1);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Supervisor encargado:', 0, 0, 'L');

        $pdf->SetX(110);
        $pdf->Cell(95, 10, 'Persona contactada:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetX(10);
        $pdf->Cell(10, 1, $texto_encargado, 0, 0, 'L');
        $pdf->SetX(110);
        $pdf->Cell(65, 1, $texto_contactada, 0, 1, 'L');

        $pdf->Ln(2);

        if (strlen($texto_departamento) < 20) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Departamento:', 0, 0, 'L');

            $pdf->SetX(110);
            $pdf->Cell(95, 10, 'Ciudad:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->SetX(10);
            $pdf->Cell(27, 1, $texto_departamento, 0, 0, 'L');

            $pdf->SetX(110);

            $pdf->Cell(30, 1, $texto_ciudad, 0, 1, 'L');
            $pdf->Ln(3);
        } else {

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Departamento:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Ln(10);
            $pdf->SetX(10);
            $ancho_texto = $pdf->GetStringWidth($texto_departamento);

            $altura_celda = 7;

            $pdf->MultiCell($ancho_texto + 7, $altura_celda, $texto_departamento, 0, 'L');

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetX(10);
            $pdf->Cell(95, 10, 'Ciudad:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->Ln(10);
            $pdf->SetX(10);
            $ancho_texto = $pdf->GetStringWidth($texto_ciudad);

            $altura_celda = 7;

            $pdf->MultiCell($ancho_texto + 7, $altura_celda, $texto_ciudad, 0, 'L');
        }
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Rect(10, 10, $anchoPagina - 20, $alturaPagina - 25);
        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Cliente:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Ln(10);

        $pdf->SetX(10);
        $ancho_texto = $pdf->GetStringWidth($texto_cliente);
        $altura_celda = 7;

        $pdf->MultiCell($ancho_texto + 7, $altura_celda, $texto_cliente, 0, 'L');

        $pdf->Ln(3);

        $texto_Conceptos = 'Conceptos';
        $html = '<table cellpadding="5" cellspacing="0" border="1">
        <tr>
        <td style="font-size: 12pt; font-weight: bold; width: 538.5; text-align: center;">' . $texto_Conceptos . '</td>
        </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');


        for ($i = 0; $i < count($lista_conceptos); $i++) {
            for ($i = 0; $i < count($formulario->conceptos); $i++) {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->Cell(35, 5, $lista_conceptos[$i]->nombre);
                $pdf->SetFont('helvetica', '', 11);
                $pdf->SetX(55);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Excelente', $formulario->conceptos[$i]->estado_concepto_id == '1' ? true : false);
                $pdf->Cell(35, 5, 'Excelente');
                $pdf->SetX(95);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Bueno', $formulario->conceptos[$i]->estado_concepto_id == '2' ? true : false);
                $pdf->Cell(35, 5, 'Bueno');
                $pdf->SetX(130);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Regular', $formulario->conceptos[$i]->estado_concepto_id == '3' ? true : false);
                $pdf->Cell(35, 5, 'Regular');
                $pdf->SetX(165);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'No_aplica', $formulario->conceptos[$i]->estado_concepto_id == '4' ? true : false);
                $pdf->Cell(35, 5, 'No aplica');
                $pdf->Ln(13);
            }
        }

        $pdf->Ln(2);

        $texto_elementos  = 'Elementos de protección personal';
        $html = '<table cellpadding="5" cellspacing="0" border="1">
        <tr>
        <td style="font-size: 12pt; font-weight: bold; width: 538.5; text-align: center;">' . $texto_elementos . '</td>
        </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->SetMargins(10, 10, 10, 10);

        $anchoPagina = $pdf->getPageWidth();
        $alturaPagina = $pdf->getPageHeight();

        $pdf->Rect(10, 10, $anchoPagina - 20, $alturaPagina - 25);

        $pdf->Ln(2);


        for ($i = 0; $i < count($lista_conceptos_epp); $i++) {
            for ($i = 0; $i < count($formulario->elementos_pp); $i++) {
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->Cell(35, 5, $lista_conceptos_epp[$i]->nombre);
                $pdf->SetFont('helvetica', '', 11);
                $pdf->SetX(40);
                $pdf->RadioButton($lista_conceptos_epp[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Completo', $formulario->elementos_pp[$i]->estado_concepto_id == '6' ? true : false);
                $pdf->Cell(35, 5, 'Completo');
                $pdf->SetX(75);
                $pdf->RadioButton($lista_conceptos_epp[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Imcompleto', $formulario->elementos_pp[$i]->estado_concepto_id == '7' ? true : false);
                $pdf->Cell(35, 5, 'Incompleto');
                $pdf->SetX(113);
                $pdf->setTextColor($formulario->elementos_pp[$i]->observacion == '' ? 150 : 52, $formulario->elementos_pp[$i]->observacion == '' ? 145 : 51, $formulario->elementos_pp[$i]->observacion == '' ? 145 : 51);
                $pdf->MultiCell(73, 7, $formulario->elementos_pp[$i]->observacion == '' ? 'Observaciones' : $formulario->elementos_pp[$i]->observacion, 1, 'L');
                $pdf->SetTextColor(52, 51, 51);
                $pdf->Ln(10);
            }
        }

        $pdf->Ln(2);
        $pdf->SetMargins(10, 10, 10, 10);

        $anchoPagina = $pdf->getPageWidth();
        $alturaPagina = $pdf->getPageHeight();

        $pdf->Rect(10, 10, $anchoPagina - 20, $alturaPagina - 25);

        $html = '<table cellpadding="5" cellspacing="0" border="0.5">';
        $html .= '<tr>';
        $html .= '<td style="margin-top: 0 !important; text-align: center; font-size: 12px;">Observaciones</td>';
        $html .= '</tr>';
        foreach ($formulario->observaciones as $imagen) {
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">';
            $html .= '<img src="' . public_path($imagen->imagen_observacion) . '" width="300" /><br>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td colspan="2">Observacion: ' . $imagen->observacion . '</td>';
            $html .= '</tr>';
        }

        $pdf->Ln(7);

        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Ln(3);

        $html = '<table border="0" style="border-collapse: collapse;">';
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<img src="' . public_path($formulario->firma_supervisor) . '" width="120" /><br>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<img src="' . public_path($formulario->firma_persona_contactada) . '" width="120" /><br>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $anchoPagina = $pdf->getPageWidth();
        $alturaPagina = $pdf->getPageHeight();
        $pdf->Rect(10, 10, $anchoPagina - 20, $alturaPagina - 25);
        $pdf->SetMargins(10, 10, 10, 10);

        $texto_encargado = preg_replace('/:/', ':<br>', 'Nombre y firma supervisor encargado:' . $texto_encargado);
        $texto_contactada = preg_replace('/:/', ':<br>',  'Nombre y firma persona contactada:' . $texto_contactada);

        $html = '<style>
                        .sin-margen {
                            border-collapse: collapse;
                        }
                        .sin-margen td {
                            border: none;
                            padding: 5px;
                        }
                        .centrado {
                            margin: 0 auto;
                            text-align: center;
                            vertical-align: middle;
                            height: 100vh;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                        }
                    </style>
                    <div class="centrado">
                        <table class="sin-margen">
                            <tr>
                                <td width="50%">' . $texto_encargado . '</td>
                                <td width="50%">' . $texto_contactada . '</td>
                            </tr>
                        </table>
                    </div>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // $pdfPath = storage_path('app/temp.pdf');
        // $pdf->Output($pdfPath, 'F');

        if ($correo_cliente == null) {
            $pdf->Output('I');
        }

        // $correo = null;
        // $correo['subject'] =  $texto_asunto;
        // $correo['body'] = 'Cordial saludo, envío informe visita de supervision, quedamos atentos a sus comentarios, muchas gracias.';
        // $correo['formulario_supervision'] = $pdfPath;
        // // $correo['to'] = $correo_cliente;
        // $correo['to'] = 'andres.duque01@gmail.com';
        // $correo['cc'] = '';
        // $correo['cco'] = '';

        // $EnvioCorreoController = new EnvioCorreoController();
        // $request = Request::createFromBase(new Request($correo));
        // $result = $EnvioCorreoController->sendEmail($request);
        // return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        // return explode('*',$request->concepto_estado_epp[0])[2];

        DB::beginTransaction();
        try {
            $formulario = new FormularioSupervision;
            $formulario->fecha_hora = $request->fecha_hora;
            $formulario->supervisor_id = $request->supervisor;
            $formulario->persona_contactada = $request->persona_contactada;
            $formulario->direccion = $request->direccion;
            $formulario->municipio = $request->ciudad;
            $formulario->descripcion = $request->descripcion;
            $formulario->firma_supervisor = $request->firma_supervisor;
            $formulario->firma_persona_contactada = $request->firma_persona_contactada;
            $formulario->cliente_id = $request->cliente;

            if ($request->hasFile('firma_supervisor')) {

                $nombreArchivoOriginal = $request->file('firma_supervisor')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('firma_supervisor')->move($carpetaDestino, $nuevoNombre);
                $formulario->firma_supervisor = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            if ($request->hasFile('firma_persona_contactada')) {

                $nombreArchivoOriginal = $request->file('firma_persona_contactada')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('firma_persona_contactada')->move($carpetaDestino, $nuevoNombre);
                $formulario->firma_persona_contactada = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }


            $formulario->save();


            foreach ($request->concepto_estado as $item) {
                $conceptos = new ConceptoFormularioSup;
                $conceptos->concepto_id = explode('*', $item)[0];
                $conceptos->estado_concepto_id = explode('*', $item)[1];
                $conceptos->formulario_id = $formulario->id;
                $conceptos->save();
            }

            foreach ($request->concepto_estado_epp as $item) {
                $conceptos = new ElementoeppFormularioSub;
                $conceptos->concepto_id = explode('*', $item)[0];
                $conceptos->estado_concepto_id = explode('*', $item)[1];
                try {
                    $conceptos->observacion = explode('*', $item)[2];
                } catch (\Exception $e) {
                    //throw $th;
                }
                $conceptos->formulario_id = $formulario->id;
                $conceptos->save();
            }

            foreach ($request->imagen as $item) {
                for ($i = 0; $i < count($item); $i++) {
                    if ($i > 0) {
                        $imagen_observacion = new ImagenObservacion;
                        $imagen_observacion->observacion = $item[0];
                        $imagen_observacion->formulario_id = $formulario->id;

                        $nombreArchivoOriginal = $item[$i]->getClientOriginalName();
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                        $carpetaDestino = './upload/';
                        $item[$i]->move($carpetaDestino, $nuevoNombre);
                        $imagen_observacion->imagen_observacion = ltrim($carpetaDestino, '.') . $nuevoNombre;
                        $imagen_observacion->save();
                    }
                }
            }
            DB::commit();
            //Descomentar las lineas comentadas para activar la función de envío de correo
            // $correo = CorreoClienteFormularioSup::where('cod_cli', '=', $request->cliente)
            //     ->select('email_fe')
            //     ->first();
            return response()->json(['status' => 'success', 'message' => 'Formulario guardado con exito.']); // si se activa la función de envío de correo quitar esta liena
            // if (str_contains(strtolower($correo), 'aplica')) {
            //     return response()->json(['status'=>'error','message'=>'El cliente no cuenta con un correo electrónico registrado, por tal motivo no puede ser notificado.']);
            // }else{
            // $result = $this->crearPdf($formulario->id, $correo->email_fe);
            // return $result;
            // }
        } catch (\Exception $e) {
            //throw $th;
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
