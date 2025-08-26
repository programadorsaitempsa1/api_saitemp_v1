<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicioServicioSolicitado;

class OrdenServicioServicioSolicitadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $cliente_id = null)
    {
        try {
            foreach ($request->servicios as $servicio) {
                $result = new OrdenServicioServicioSolicitado;
                $result->cliente_id = $cliente_id;
                $result->nombres_candidato = $servicio['nombres'];
                $result->apellidos_candidato = $servicio['apellidos'];
                $result->celular_candidato = $servicio['celular'];
                $result->correo_candidato = $servicio['correo'];
                $result->municipio_nacimiento = intval($servicio['municipio_nacimiento_id']);
                $result->numero_identificacion = $servicio['documento_identidad_candidato'];
                $result->salario = $servicio['salario'];
                $result->auxilio_transporte = $servicio['auxilio_transporte'];
                $result->fecha_inicio_labores = $servicio['fecha_inicio_labores'];
                $result->especificaciones_vinculacion = $servicio['especificaciones_vinculacion'];
                $result->especificaciones_seleccion_personal = $servicio['especificaciones_seleccion'];
                $result->estado_solicitud = intval($servicio['estado_solicitud_id']);
                $result->motivo_cancelacion = $servicio['motivo_cancelacion'];
                $result->nombre_laboratorio = intval($servicio['laboratorio_id']);
                $result->direccion_laboratorio = $servicio['direccion_laboratorio'];
                $result->municipio_ubicacion_laborario = intval($servicio['municipio_laboratorio_id']);
                $result->fecha_hora_examen_medico = $servicio['fecha_examen_medico'];
                $result->recomendaciones_examen_medico = $servicio['recomendaciones_examen_medico'];
                $result->orientacion_ubicacion_laboratorio = $servicio['orientacion_laboratorio'];
                $result->save();

                $bonificacion = new OrdenServicioBonificacionController();
                $guardado_exitoso2 = $bonificacion->create($request, $result->id);

                $cargo = new OrdenServicioCargoController();
                $guardado_exitoso3 = $cargo->create($request, $result->id);

                // $hojaVida = new OrdenServicioHojaVidaController();
                // $guardado_exitoso2 = $hojaVida->create($request);

            }
            // return $guardado_exitoso2;
            // return $guardado_exitoso2;
            if (!$guardado_exitoso2 || !$guardado_exitoso3) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            // return $e;
            return false;
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
