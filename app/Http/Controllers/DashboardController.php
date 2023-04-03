<?php

namespace App\Http\Controllers;

use App\Models\DashboardActivos;
use App\Models\DashboardEmpleadosPlanta;
use App\Models\DashboardIngresosRetiros;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
    }

    public function empleadosactivos()
    {
        $result = DashboardActivos::select()
            ->where('est_lab', '=', '01')
            ->get()
            ->count();
        return response()->json($result);
    }
    
    public function empleadosplanta()
    {
        $result = DashboardEmpleadosPlanta::select()
            ->where('cod_conv', '=', '0001-00Q')
            ->get()
            ->count();
        return response()->json($result);
    }
    
    public function ingresosmescurso()
    {
        $result = DB::table('rhh_hislab')
        ->where('nue_con', '=', 1)
        ->whereBetween('fec_ini', [
           \Carbon\Carbon::now()->startOfDay(),
           \Carbon\Carbon::now()
        ])
        ->count('cod_emp');
        return response()->json($result);
    }
    
    public function retirosmescurso()
    {
        $result = DB::table('rhh_hislab')
        ->where('nue_con', '=', 1)
        ->whereBetween('fec_ini', [
            DB::raw("CONVERT(varchar,dateadd(d,-(day(dateadd(m,-1,getdate()-2))),dateadd(m,-1,getdate()-1)),106)"),
            DB::raw("CONVERT(varchar,dateadd(d,-(day(getdate())),getdate()),106)")
        ])
        ->count();
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
