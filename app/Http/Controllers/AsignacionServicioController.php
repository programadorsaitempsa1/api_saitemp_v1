<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionServicioModel;
use App\Models\UsuarioDisponibleServicioModel;
use App\Models\UsuarioDebidaDiligencia;
use App\Models\cliente;
use App\Models\OrdenServcio;
use App\Models\CandidatoServicioModel;
use App\Models\CandidatoSolicitudServicioModel;
use App\Models\formularioGestionIngreso;
use App\Models\EstadoServicioModel;
use App\Models\estadosIngreso;
use App\Models\EstadoCandidatoServicioModel;
use App\Models\SolicitudServicioModel;
use Illuminate\Support\Facades\DB;
use App\Traits\AutenticacionGuard;
use Illuminate\Support\Carbon;

class AsignacionServicioController extends Controller
{
    use AutenticacionGuard;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = AsignacionServicioModel::select(
            'id',
            'nombre',
            'checked',
            'tipo'
        )->get();
        return response()->json($result);
    }

    public function ordenservicio(Request $request)
    {
        $result = $this->getUserRelaciones();
        $result = $result->getdata(true);
        $id = $request->id;
        $campo = AsignacionServicioModel::join('usr_app_tipo_asignacion_servicio as tas', 'tas.asignacion_servicio_id', '=', 'usr_app_asignacion_servicio.id')
            ->where('tas.linea_servicio_id', '=', $id)
            ->where('usr_app_asignacion_servicio.checked', '=', '1')
            ->select(
                'usr_app_asignacion_servicio.id',
                'usr_app_asignacion_servicio.nombre',
                'usr_app_asignacion_servicio.checked',
                'usr_app_asignacion_servicio.manual',
                'tas.linea_servicio_id'
            )->first();
        if ($result['tipo_usuario_id'] == 2) {
            $campo->manual = false;
        }
        $usuarios = $this->responsables($campo->linea_servicio_id);
        $campo_lista['usuarios'] = $usuarios;
        $campo_lista['campo'] = $campo;
        return $campo_lista;
    }

    public function cancelaservicio(Request $request, $id)
    {

        try {

            $fecha_actual =  Carbon::now();
            DB::beginTransaction();
            $result = $this->getUserRelaciones();
            $usuario = $result->getdata(true);
            $orden_servicio = SolicitudServicioModel::where('id', '=', $id)->first();
            $estado_solicitud_servicio = EstadoServicioModel::where('clave', '=', 'cancelado')->first();
            $orden_servicio->fecha_cancelacion =  $fecha_actual->format('d-m-Y H:i:s');
            $orden_servicio->usuario_cancela =  $usuario['usuario_id'];
            $orden_servicio->motivo_cancelacion =  $request->motivo_cancelacion_id;
            $orden_servicio->descripcion_cancelacion =  $request->descripcion_cancelacion;
            $orden_servicio->estado_servicio_id = $estado_solicitud_servicio->id; // se coloca el registro en estado cancelado

            $candidatos_servicio = CandidatoServicioModel::where('servicio_id', '=', $id)->select()->get();
            $estado_candidato = EstadoCandidatoServicioModel::where('clave', '=', 'cancelado')->first();

            $estado_orden_servicio = estadosIngreso::where('clave', '=', 'cancelado')->first();
            foreach ($candidatos_servicio as $candidato) {
                $candidato->estado_id = $estado_candidato->id;
                $candidato->save();
                $candidatos_seiya = formularioGestionIngreso::where('n_servicio', '=', $orden_servicio->numero_radicado)->where('candidato_id', '=', $candidato->usuario_id)->select()->get();
                foreach ($candidatos_seiya as $candidato) {
                    $candidato->estado_ingreso_id = $estado_orden_servicio->id;
                    $candidato->save();
                }
            }
            if ($orden_servicio->save()) {
                DB::commit();
                return response()->json(["status" => "success", "message" => "Servicio cancelado de manera exitosa"]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json(["status" => "error", "message" => "Hubo un error al caneclar el servicio, por favor intente nuevamente"]);
        }
    }

    public function cancelacandidatoservicio(Request $request, $servicio_id, $candidato_id)
    {
        try {
            $fecha_actual =  Carbon::now();
            $result = $this->getUserRelaciones();
            $usuario = $result->getdata(true);
            $candidato = CandidatoSolicitudServicioModel::where('servicio_id', $servicio_id)->where('usuario_id', $candidato_id)->first();
            $estado_candidato = EstadoCandidatoServicioModel::where('clave', '=', 'cancelado')->first();
            $candidato->fecha_cancelacion =  $fecha_actual->format('d-m-Y H:i:s');
            $candidato->usuario_cancela =  $usuario['usuario_id'];
            $candidato->motivo_cancelacion =  $request->motivo_cancelacion_id;
            $candidato->descripcion_cancelacion =  $request->descripcion_cancelacion;
            $candidato->estado_candidato_id = $estado_candidato->id; // se coloca el registro en estado cancelado
            if ($candidato->save()) {
                return response()->json(["status" => "success", "message" => "Candidato cancelado de manera exitosa"]);
            }
        } catch (\Exception $e) {
            return $e;
            return response()->json(["status" => "error", "message" => "Hubo un error al caneclar el candidato, por favor intente nuevamente"]);
        }
    }

    public function responsables($id)
    {
        $tipo_usuario = '';
        if ($id == 2) {
            $tipo_usuario = 5;
        } else if ($id == 3 || $id == 4) {
            $tipo_usuario = 4;
        }
        $usuarios = UsuarioDisponibleServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_usuarios_disponibles_servicio.usuario_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->where('usr_app_usuarios_disponibles_servicio.rol_usuario_interno_id', '=', $tipo_usuario)
            ->select(
                'usr_app_usuarios_disponibles_servicio.usuario_id',
                'usr_app_usuarios_disponibles_servicio.rol_usuario_interno_id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
            )->get();


        // $usuarios = UsuarioDisponibleServicioModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_usuarios_disponibles_servicio.usuario_id')
        // ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
        // ->where('usr_app_usuarios_disponibles_servicio')
        // ->select(
        //     'usr_app_usuarios_disponibles_servicio.usuario_id',
        //     'usr_app_usuarios_disponibles_servicio.rol_usuario_interno_id',
        //     DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
        // )->get();
        return $usuarios;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function clienteservicio()
    {
        $result = cliente::join('usr_app_actividades_ciiu as acu', 'acu.id', 'usr_app_clientes.actividad_ciiu_id')
            // ->join('usr_app_codigos_ciiu as ccu','ccu.id','usr_app_actividades_ciiu.codigo_ciiu_id')
            ->select(
                'usr_app_clientes.id',
                'usr_app_clientes.razon_social',
                'acu.codigo_ciiu_id'
            )
            ->get();
        return response()->json($result);
    }

    public function responsableservicio($id)
    {
        $result = cliente::join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
            ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
            ->join('usr_app_sector_economico as se', 'se.id', '=', 'cc.sector_economico_id')
            ->where('usr_app_clientes.id', '=', $id)
            ->select(
                'usr_app_clientes.id',
                'usr_app_clientes.razon_social',
                DB::raw('COALESCE(nit, numero_identificacion) as nit'),
                'ac.codigo_actividad as actividad_ciiu',
                'se.nombre as sector_economico',
                'se.id as sector_economico_id'
            )->first();
        $cliente = UsuarioDebidaDiligencia::where('nit', '=', $result->nit)
            ->select(
                'nombre_contacto',
                'telefono_contacto',
                'cargo_contacto'
            )->first();
        if ($cliente) {
            $result->nombre_contacto = $cliente['nombre_contacto'];
            $result->telefono_contacto = $cliente['telefono_contacto'];
            $result->cargo_contacto = $cliente['cargo_contacto'];
        }
        return $result;
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
