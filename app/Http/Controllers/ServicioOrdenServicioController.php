<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicioOrdenServicio;
use App\Traits\AutenticacionGuard;
use App\Models\cliente;
use Illuminate\Support\Facades\DB;
use App\Models\user;

class ServicioOrdenServicioController extends Controller
{
    use AutenticacionGuard;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ServicioOrdenServicio::select(
            'id',
            'nombre'
        )
            ->get();
        return response()->json($result);
    }

    public function datoscliente()
    {
        $user = $this->getUserRelaciones();
        $data = $user->getData(true);
        if ($data['tipo_usuario_id'] == 2) {
            $result = cliente::join('usr_app_actividades_ciiu as ac', 'ac.id', '=', 'usr_app_clientes.actividad_ciiu_id')
                ->join('usr_app_codigos_ciiu as cc', 'cc.id', '=', 'ac.codigo_ciiu_id')
                ->join('usr_app_sector_economico as se', 'se.id', '=', 'cc.sector_economico_id')
                ->where('usr_app_clientes.nit', '=', $data['nit'])
                ->orWhere('usr_app_clientes.numero_identificacion', '=', $data['nit'])
                ->select(
                    'usr_app_clientes.id',
                    'usr_app_clientes.razon_social',
                    DB::raw('COALESCE(nit, numero_identificacion) as nit'),
                    'ac.codigo_actividad as actividad_ciiu',
                    'se.nombre as sector_economico',
                    'se.id as sector_economico_id'
                )->first();
            $result->nombre_contacto = $data['nombre_contacto'];
            $result->telefono_contacto = $data['telefono_contacto'];
            $result->cargo_contacto = $data['cargo_contacto'];
            return $result;
        }
        if ($data['tipo_usuario_id'] == 1) {
            return '';
        }
        return '';
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
