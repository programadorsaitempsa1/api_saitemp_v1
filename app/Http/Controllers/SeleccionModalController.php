<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeleccionModal;
use Illuminate\Support\Facades\DB;
use App\Models\RegistroCambio;

class SeleccionModalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = SeleccionModal::select(
            'id',
            'contacto',
            'caracteristicas_contacto',
            'tipo_proceso',
            'pagina_oficial',
            'actividad',
            'fabricacion',
            'ubicacion_empresa',
            'empresa_personal_retirado',
            'empresa_personal_no_retirado',
            'unidades_negocio_crystal',
            'marcas_trabajan',
            'perfiles_autorizados',
            'perfiles_no_autorizados',
            'presencia_ubicacion',
            'experiencia',
            'edades',
            'nivel_academico',
            'ubicacion_candidato',
            'horarios',
            'pruebas_psicotecnicas',
            'pruebas_tecnicas',
            'candidatos_por_vacante',
            'vacunacion',
            'consumo',
            'formatos_adicionales_peoceso',
            'documentos',
            'perfil_oculto',
            'beneficios_empresa',
            'observaciones',
        )
            ->get();
        return response()->json($result);
    }

    public function byid($id)
    {
        $result = SeleccionModal::select(
            'id',
            'contacto',
            'caracteristicas_contacto',
            'tipo_proceso',
            'pagina_oficial',
            'actividad',
            'fabricacion',
            'ubicacion_empresa',
            'empresa_personal_retirado',
            'empresa_personal_no_retirado',
            'unidades_negocio_crystal',
            'marcas_trabajan',
            'perfiles_autorizados',
            'perfiles_no_autorizados',
            'presencia_ubicacion',
            'experiencia',
            'edades',
            'nivel_academico',
            'ubicacion_candidato',
            'horarios',
            'pruebas_psicotecnicas',
            'pruebas_tecnicas',
            'candidatos_por_vacante',
            'vacunacion',
            'consumo',
            'formatos_adicionales_peoceso',
            'documentos',
            'perfil_oculto',
            'beneficios_empresa',
            'observaciones',
        )
            ->where('cliente_id', $id)
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $result = new SeleccionModal;
        $result->contacto = $request->contacto;
        $result->caracteristicas_contacto = $request->caracteristicas_contacto;
        $result->tipo_proceso = $request->tipo_proceso;
        $result->pagina_oficial = $request->pagina_oficial;
        $result->actividad = $request->actividad;
        $result->fabricacion = $request->fabricacion;
        $result->ubicacion_empresa = $request->ubicacion_empresa;
        $result->empresa_personal_retirado = $request->empresa_personal_retirado;
        $result->empresa_personal_no_retirado = $request->empresa_personal_no_retirado;
        $result->unidades_negocio_crystal = $request->unidades_negocio_crystal;
        $result->marcas_trabajan = $request->marcas_trabajan;
        $result->perfiles_autorizados = $request->perfiles_autorizados;
        $result->perfiles_no_autorizados = $request->perfiles_no_autorizados;
        $result->presencia_ubicacion = $request->presencia_ubicacion;
        $result->experiencia = $request->experiencia;
        $result->edades = $request->edades;
        $result->nivel_academico = $request->nivel_academico;
        $result->ubicacion_candidato = $request->ubicacion_candidato;
        $result->horarios = $request->horarios;
        $result->pruebas_psicotecnicas = $request->pruebas_psicotecnicas;
        $result->pruebas_tecnicas = $request->pruebas_tecnicas;
        $result->candidatos_por_vacante = $request->candidatos_por_vacante;
        $result->vacunacion = $request->vacunacion;
        $result->consumo = $request->consumo;
        $result->formatos_adicionales_peoceso = $request->formatos_adicionales_peoceso;
        $result->documentos = $request->documentos;
        $result->perfil_oculto = $request->perfil_oculto;
        $result->beneficios_empresa = $request->beneficios_empresa;
        $result->observaciones = $request->observaciones;
        $result->cliente_id = $request->cliente_id;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro aguardado con exito.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar registro por favor ingrese nuevamente.']);
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
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $result = SeleccionModal::where('cliente_id', '=', $id)->first();
            $result->contacto = $request->contacto;
            $result->caracteristicas_contacto = $request->caracteristicas_contacto;
            $result->tipo_proceso = $request->tipo_proceso;
            $result->pagina_oficial = $request->pagina_oficial;
            $result->actividad = $request->actividad;
            $result->fabricacion = $request->fabricacion;
            $result->ubicacion_empresa = $request->ubicacion_empresa;
            $result->empresa_personal_retirado = $request->empresa_personal_retirado;
            $result->empresa_personal_no_retirado = $request->empresa_personal_no_retirado;
            $result->unidades_negocio_crystal = $request->unidades_negocio_crystal;
            $result->marcas_trabajan = $request->marcas_trabajan;
            $result->perfiles_autorizados = $request->perfiles_autorizados;
            $result->perfiles_no_autorizados = $request->perfiles_no_autorizados;
            $result->presencia_ubicacion = $request->presencia_ubicacion;
            $result->experiencia = $request->experiencia;
            $result->edades = $request->edades;
            $result->nivel_academico = $request->nivel_academico;
            $result->ubicacion_candidato = $request->ubicacion_candidato;
            $result->horarios = $request->horarios;
            $result->pruebas_psicotecnicas = $request->pruebas_psicotecnicas;
            $result->pruebas_tecnicas = $request->pruebas_tecnicas;
            $result->candidatos_por_vacante = $request->candidatos_por_vacante;
            $result->vacunacion = $request->vacunacion;
            $result->consumo = $request->consumo;
            $result->formatos_adicionales_peoceso = $request->formatos_adicionales_peoceso;
            $result->documentos = $request->documentos;
            $result->perfil_oculto = $request->perfil_oculto;
            $result->beneficios_empresa = $request->beneficios_empresa;
            $result->observaciones = $request->observaciones;
            $result->cliente_id = $request->cliente_id;
            $result->save();

            $nombres = str_replace("null", "", $user->nombres);
            $apellidos = str_replace("null", "", $user->apellidos);

            $registroCambio = new RegistroCambio;
            $registroCambio->observaciones = $request['registro_cambios']['observaciones'];
            $registroCambio->solicitante = $request['registro_cambios']['solicitante'];
            $registroCambio->autoriza = $request['registro_cambios']['autoriza'];
            $registroCambio->actualiza = $nombres . ' ' . $apellidos;
            $registroCambio->cliente_id = $request->cliente_id;
            $registroCambio->save();


            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro aguardado con exito.']);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar registro por favor ingrese nuevamente.']);
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
        $result = SeleccionModal::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado con exito.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar registro por favor ingrese nuevamente.']);
        }
    }
}
