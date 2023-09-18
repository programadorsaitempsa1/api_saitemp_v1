<?php

namespace App\Http\Controllers;

use App\Mail\EnvioCorreo;
use App\Models\FormularioSupervision;
use App\Models\ConceptoFormularioSup;
use App\Models\ImagenObservacion;
use App\Models\Municipios;
use App\Models\User;
use App\Models\ListaConceptosFormularioSup;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // return response()->json($conceptos);
        return response()->json($formulario[0]);
    }

    public function crearPdf($formulario_id)
    {

        $formulario = $this->formById($formulario_id)->getData();


        $ListaConceptosFormularioSupController = new ListaConceptosFormularioSupController;
        $lista_conceptos = $ListaConceptosFormularioSupController->index()->getData();



        $pdf = new TCPDF();

        // $border_style = array('all' => array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'phase' => 0));


        $pdf->SetTextColor(81, 90, 90);
       

        $pdf->SetCreator('Al isnstante');
        $pdf->SetAuthor('Al isnstante');
        $pdf->SetTitle('Formulario de supervisión');
        $pdf->SetSubject('Formulario de supervisión');
        $pdf->SetKeywords('formulario, supervision');


        $pdf->AddPage();
        $pdf->Ln(8);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 10, 'Formulario de supervisión', 0, 1, 'C');

        $texto_encargado = $formulario->supervisor;
        $texto_contactada = $formulario->persona_contactada;
        $texto_fecha = $formulario->fecha_hora;
        $texto_cliente = $formulario->nombre_cliente;
        $texto_direccion = $formulario->direccion;
        $texto_departamento = $formulario->departamento;
        $texto_ciudad = $formulario->municipio;
       
        $pdf->Ln(5);
        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Fecha y hora:', 0, 0, 'L');
        $pdf->Ln(10);
      

        $pdf->SetX(10);
        $ancho_texto = $pdf->GetStringWidth($texto_fecha);
        $margen_izquierdo = 10;
        $altura_celda = 10;

        $margen_superior = $pdf->GetY();
        $espacio_superior = 2;
        $y_centro = $margen_superior + $espacio_superior + ($altura_celda / 2);

        $pdf->Rect($margen_izquierdo, $margen_superior, $ancho_texto + 10, $altura_celda);
        $pdf->SetXY($margen_izquierdo, $y_centro - ($altura_celda / 2));
        $pdf->MultiCell($ancho_texto + 10, $altura_celda, $texto_fecha, 0, 'C');
        $pdf->SetY($y_centro + ($altura_celda / 2));


        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Supervisor encargado:', 0, 0, 'L');

        $pdf->SetX(110);
        $pdf->Cell(95, 10, 'Persona contactada: * :', 0, 1, 'L');

        $pdf->SetX(10);
        $pdf->Rect(10, $pdf->GetY(), 95, 10);
        $pdf->Cell(60, 10, $texto_encargado, 0, 0, 'C');

        $pdf->SetX(110);
        $pdf->Rect(110, $pdf->GetY(), 95, 10);
        $pdf->Cell(65, 10, $texto_contactada, 0, 1, 'C');

        $pdf->Ln(8);
        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Dirección:', 0, 0, 'L');

        $pdf->Ln(10);
        $pdf->SetX(10);
        $ancho_texto = $pdf->GetStringWidth($texto_direccion);
        $margen_izquierdo = 10;
        $altura_celda = 10;


        $margen_superior = $pdf->GetY();
        $espacio_superior = 2;
        $y_centro = $margen_superior + $espacio_superior + ($altura_celda / 2);

        $pdf->Rect($margen_izquierdo, $margen_superior, $ancho_texto + 10, $altura_celda);
        $pdf->SetXY($margen_izquierdo, $y_centro - ($altura_celda / 2));
        $pdf->MultiCell($ancho_texto + 10, $altura_celda, $texto_direccion, 0, 'C');
        $pdf->SetY($y_centro + ($altura_celda / 2));

        $pdf->Ln(5);

        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Departamento *:', 0, 0, 'L');

        $pdf->SetX(110);
        $pdf->Cell(95, 10, 'Ciudad: *', 0, 1, 'L');

        $pdf->SetX(10);
        $pdf->Rect(10, $pdf->GetY(), 95, 10);
        $pdf->Cell(27, 10, $texto_departamento, 0, 0, 'C');

        $pdf->SetX(110);
        $pdf->Rect(110, $pdf->GetY(), 95, 10);
        $pdf->Cell(30, 10, $texto_ciudad, 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Cliente: *:', 0, 0, 'L');
        $pdf->Ln(10);

        $pdf->SetX(10);
        $ancho_texto = $pdf->GetStringWidth($texto_cliente);
        $margen_izquierdo = 10;
        $altura_celda = 10;


        $margen_superior = $pdf->GetY();
        $espacio_superior = 2;
        $y_centro = $margen_superior + $espacio_superior + ($altura_celda / 2);

        $pdf->Rect($margen_izquierdo, $margen_superior, $ancho_texto + 10, $altura_celda);
        $pdf->SetXY($margen_izquierdo, $y_centro - ($altura_celda / 2));
        $pdf->MultiCell($ancho_texto + 10, $altura_celda, $texto_cliente, 0, 'C');
        $pdf->SetY($y_centro + ($altura_celda / 2));

        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'Conceptos', 0, 1, 'C');
        $pdf->Ln(10);

        // Usar un ciclo for para generar una lista numerada
        $html = '<table cellpadding="5" cellspacing="0" border="0.5">';
        for ($i = 1; $i < count($lista_conceptos); $i++) {
            for ($i = 1; $i < count($formulario->conceptos); $i++) {
                $html .= '<tr>';
                $html .= '<td>';
                $pdf->Cell(35, 5, $lista_conceptos[$i]->nombre);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Excelente', $formulario->conceptos[$i]->estado_concepto_id == '1' ? true : false);
                $pdf->Cell(35, 5, 'Excelente');
                $pdf->SetX(85);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Bueno', $formulario->conceptos[$i]->estado_concepto_id == '2' ? true : false);
                $pdf->Cell(35, 5, 'Bueno');
                $pdf->SetX(120);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'Regular', $formulario->conceptos[$i]->estado_concepto_id == '3' ? true : false);
                $pdf->Cell(35, 5, 'Regular');
                $pdf->SetX(160);
                $pdf->RadioButton($lista_conceptos[$i]->nombre, 5, array('checked' => false, 'readonly' => true), array(), 'No_aplica', $formulario->conceptos[$i]->estado_concepto_id == '4' ? true : false);
                $pdf->Cell(35, 5, 'No_aplica');
                $pdf->Ln(15);
                $html .= '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Ln(20);
        $pdf->Cell(0, 10, 'Observaciones', 0, 1, 'C');



        $html = '<table cellpadding="5" cellspacing="0" border="0.5">';
        foreach ($formulario->observaciones as $imagen) {
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<img src="' . public_path($imagen->imagen_observacion) . '" width="300" /><br>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td colspan="2">' . $imagen->observacion . '</td>';
            $html .= '</tr>';
        }
        $pdf->Ln(20);

        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Ln(15);



        $pdf->SetX(10);
        $pdf->Cell(95, 10, 'Firma *:', 0, 0, 'L');

        $pdf->SetX(110);
        $pdf->Cell(95, 10, 'Firma *:', 0, 1, 'L');
        $pdf->Ln(5);

        $html = '<table cellpadding="5" cellspacing="0" border="0.5">';
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<img src="' . public_path($formulario->firma_supervisor) . '" width="350" /><br>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<img src="' . public_path($formulario->firma_persona_contactada) . '" width="350" /><br>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');


        $pdf->SetX(2);
        $pdf->Cell(95, 10, 'Nombre y firma supervisor encargado:', 0, 0, 'C');

        $pdf->SetX(97);
        $pdf->Cell(95, 10, 'Nombre y firma persona contactada:', 0, 1, 'C');

        $pdf->SetX(10);
        $pdf->Rect(10, $pdf->GetY(), 90, 10);
        $pdf->Cell(60, 10, $texto_encargado, 0, 0, 'C');

        $pdf->SetX(110);
        $pdf->Rect(110, $pdf->GetY(), 90, 10);
        $pdf->Cell(65, 10, $texto_contactada, 0, 1, 'C');

        $pdfPath = storage_path('app/temp.pdf');
        $pdf->Output($pdfPath, 'F');

        $correo = null;
        $correo['subject'] = 'envio pdf';
        $correo['body'] = 'Esta es una prueba de creación y envio de pdf en php';
        $correo['formulario_supervision'] = $pdfPath;
        $correo['to'] = 'andres.duque01@gmail.com';
        $correo['cc'] = '';
        $correo['cco'] = '';

        $EnvioCorreoController = new EnvioCorreoController();
        $request = Request::createFromBase(new Request($correo));
        $result = $EnvioCorreoController->sendEmail($request);
        return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
            $result = $this->crearPdf($formulario->id);
            return $result;
            // return response()->json(['status' => 'success', 'message' => 'Formulario guardado exitosamente']);
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
