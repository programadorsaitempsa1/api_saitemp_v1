<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicioliente;
use Illuminate\Support\Facades\DB;

class OrdenServiciolienteController extends Controller
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
    public function create(Request $request)
    {

        try {

            DB::beginTransaction();
            $OrdenServicioliente = new OrdenServicioliente;
            $OrdenServicioliente->tipo_persona = $request->tipo_persona_id;
            $OrdenServicioliente->numero_identificacion = $request->numero_identificacion;
            $OrdenServicioliente->nit = $request->nit;
            $OrdenServicioliente->vacantes_disponibles = $request->numero_vacantes_id;
            $OrdenServicioliente->nombre_razon_social = $request->razon_social;
            $OrdenServicioliente->nombre_solicitante = $request->nombre_solicitante;
            $OrdenServicioliente->cargo_solicitante = $request->cargo_solicitante;
            $OrdenServicioliente->celular_solicitante = $request->celular_solicitante;
            $OrdenServicioliente->correo_solicitante = $request->correo_solicitante;
            $OrdenServicioliente->servicio_solicitado = $request->servicio_solicitado_id;
            $OrdenServicioliente->municipio_solicitud = $request->ciudad_prestacion_servicio_id;
            $OrdenServicioliente->save();


            $servicioSolicitado = new OrdenServicioServicioSolicitadoController();
            $guardado_exitoso1 = $servicioSolicitado->create($request, $OrdenServicioliente->id);

            // return $guardado_exitoso1;
            if (!$guardado_exitoso1) {
                DB::rollback();
                return $this->mensaje(false);
            }
            // if(!$guardado_exitoso1 || !$guardado_exitoso2 || !$guardado_exitoso3 || !$guardado_exitoso4){
            //     DB::rollback();
            //     return $this->mensaje(false);
            // }
            DB::commit();
            return $this->mensaje(true);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return $this->mensaje(false);
            //throw $th;
        }
    }

    public function mensaje($bandera)
    {
        if ($bandera) {
            return response()->json(["status" => "success", "message" => "Formulario guardado exitosamente"]);
        } else {
            return response()->json(["status" => "error", "message" => "Error al guadar los datos del formulario"]);
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
