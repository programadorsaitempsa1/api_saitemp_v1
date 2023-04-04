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
        $result = DB::select(DB::raw('SELECT COUNT(cod_emp) AS total FROM rhh_hislab WHERE nue_con=1 AND fec_ini BETWEEN CONVERT(varchar, DATEADD(d, -(DAY(GETDATE()-1)), GETDATE()), 106) AND GETDATE()'));
        $total = $result[0]->total; // Acceder a la propiedad "total" del primer objeto StdClass en el array
        $total = (int) $total;
        return response()->json($total);
    }

    public function retirosmescurso()
    {
        $result = DB::select(DB::raw('SELECT COUNT(cod_emp) AS total FROM rhh_hislab WHERE fec_ret IS NOT NULL AND fec_ret BETWEEN CONVERT(varchar, DATEADD(d, -(DAY(GETDATE()-1)), GETDATE()), 106) AND GETDATE()'));
        $total = $result[0]->total; // Acceder a la propiedad "total" del primer objeto StdClass en el array
        $total = (int) $total;
        return response()->json($total);
    }

    public function ingresosmesanterior()
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

    public function retirosmesantrior()
    {
        $result = DB::table('rhh_hislab')
            ->where('fec_ret', '!=', null)
            ->whereBetween('fec_ini', [
                DB::raw("CONVERT(varchar,dateadd(d,-(day(dateadd(m,-1,getdate()-2))),dateadd(m,-1,getdate()-1)),106)"),
                DB::raw("CONVERT(varchar,dateadd(d,-(day(getdate())),getdate()),106)")
            ])
            ->count();
        return response()->json($result);
    }

    function historicoempleado($cedula)
    {
            $result = DB::table('rhh_hislab as hl')
            ->join('rhh_emplea as e', 'e.cod_emp', '=', 'hl.cod_emp')
            ->join('gen_terceros as t', 't.ter_nit', '=', 'e.num_ide')
            ->join('rhh_cargos as c', 'c.cod_car', '=', 'hl.cod_car')
            ->leftJoin('rhh_cauretiro as r', 'r.cau_ret', '=', 'hl.cau_ret')
            ->join('rhh_EmpConv as ec', function ($join) use ($cedula) {
                $join->on('ec.cod_emp', '=', 'hl.cod_emp')
                    ->where('hl.fec_ini', '>=', DB::raw('ec.fec_ini'))
                    ->where(function ($query) use ($cedula) {
                        $query->whereNull('ec.fec_fin')
                            ->orWhere('ec.fec_fin', '>=', DB::raw('hl.fec_ini'));
                    });
            })
            ->join('rhh_Convenio as cv', 'cv.cod_conv', '=', 'ec.cod_conv')
            ->join('rhh_tipcon as tc', 'tc.tip_con', '=', 'hl.tip_con')
            ->join('GTH_Contratos as ct', function ($join) {
                $join->on('ct.cod_emp', '=', 'hl.cod_emp')
                    ->on('ct.cod_con', '=', 'hl.cod_con');
            })
            ->select(
                DB::raw("FORMAT(hl.fec_ini,'dd/MM/yyyy') AS fec_ini"),
                DB::raw("FORMAT(hl.fec_ret,'dd/MM/yyyy') AS fec_ret"),
                'r.nom_ret AS nom_ret',
                'c.nom_car AS nom_car',
                'cv.nom_conv AS nom_conv',
                'hl.sal_bas AS sal_bas',
                'tc.nom_con',
                'Ct.not_con',
                // 'hl.cod_con',
                // 'ec.cod_conv',
                // 'hl.cod_emp',
            )
            ->where('t.ter_nit', '=', $cedula)
            ->orderBy('hl.fec_ini', 'asc')
            ->paginate(12);

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
