<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VersionTablasAndroid;
use App\Models\CargosCrm;
use App\Models\AtencionInteraccion;
use App\Models\EstadoCierreCrm;
use App\Models\PqrsfCRM;
use App\Models\SolicitanteCrm;
use App\Models\Sede;
use App\Models\Procesos;
use App\Models\User;
use App\Models\cliente;
use Illuminate\Support\Facades\DB;


class VersionTablasAndroidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = VersionTablasAndroid::all();
        return response()->json($result);
    }

    public function index2()
    {
        $tablas = [];
        $result = VersionTablasAndroid::all();
        $sede = Sede::all();
        $proceso = Procesos::all();
        $solicitanteCrm = SolicitanteCrm::all();
        $atencion = AtencionInteraccion::all();
        $responsable = User::select('id', 'nombres', 'apellidos', 'email')->where('lider', 1)->get();
        $visitante = User::select('id', 'nombres', 'apellidos', 'email')->get();
        $cargos = CargosCrm::all();
        $estado_cierre = EstadoCierreCrm::select('id', 'nombre', 'tipo_estado')->where('tipo_estado', 2)->get();
        $estado_compromiso = EstadoCierreCrm::all()->where('tipo_estado', 1);
        $pqrsf = PqrsfCRM::all();
        $clientes_debida_diligencia = cliente::select('id', DB::raw('COALESCE(nit, numero_identificacion) as nit'),'razon_social')->get();
        $tablas['usr_app_tablas_android'] = $result;
        $tablas['usr_app_sedes_saitemp'] = $sede;
        $tablas['usr_app_procesos'] = $proceso;
        $tablas['usr_app_solicitante_crm'] = $solicitanteCrm;
        $tablas['usr_app_atencion_interacion'] = $atencion;
        $tablas['usr_app_usuarios_responsable'] = $responsable;
        $tablas['usr_app_usuarios_visitante'] = $visitante;
        $tablas['usr_app_cargos_crm'] = $cargos;
        $tablas['usr_app_estado_cierre_crm'] = $estado_cierre;
        $tablas['usr_app_estado_compromiso_crm'] = $estado_compromiso;
        $tablas['usr_app_pqrsf_crm'] = $pqrsf;
        $tablas['usr_app_clientes'] = $clientes_debida_diligencia;
        return response()->json($tablas);
    }

    public function usr_app_tablas_android()
    {
        $result = VersionTablasAndroid::all();
        return response()->json($result);
    }
    public function usr_app_sedes_saitemp()
    {
        $result = Sede::all();
        return response()->json($result);
    }
    public function usr_app_procesos()
    {
        $result = Procesos::all();
        return response()->json($result);
    }
    public function usr_app_solicitante_crm()
    {
        $result = SolicitanteCrm::all();
        return response()->json($result);
    }
    public function usr_app_atencion_interacion()
    {
        $result = AtencionInteraccion::all();
        return response()->json($result);
    }
    public function usr_app_usuarios_responsable()
    {
        $result = User::all();
        return response()->json($result);
    }
    public function usr_app_usuarios_visitante()
    {
        $result = User::all();
        return response()->json($result);
    }
    public function usr_app_cargos_crm()
    {
        $result = CargosCrm::all();
        return response()->json($result);
    }
    public function usr_app_estado_cierre_crm()
    {
        $result = EstadoCierreCrm::where('tipo_estado', 2)->all();
        return response()->json($result);
    }
    public function usr_app_estado_compromiso_crm()
    {
        $result = EstadoCierreCrm::all();
        return response()->json($result);
    }
    public function usr_app_pqrsf_crm()
    {
        $result = PqrsfCRM::all();
        return response()->json($result);
    }
    public function usr_app_clientes()
    {
        $result = cliente::select('id', DB::raw('COALESCE(nit, numero_identificacion) as nit'),'razon_social')->get();
        return response()->json($result);
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
