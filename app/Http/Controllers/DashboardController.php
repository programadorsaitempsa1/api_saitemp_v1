<?php

namespace App\Http\Controllers;

use App\Models\DashboardActivos;
use App\Models\DashboardEmpleadosPlanta;
use App\Models\DashboardIngresosRetiros;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\EmpleadosExport;

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

    function historicoempleado($cedula, $cantidad)
    {

        $query = DB::table('rhh_hislab as hl')
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
            ->leftJoin('usr_conv_analista as ca', 'ca.cod_conv', '=', 'ec.cod_conv')
            ->leftJoin('usr_analista as a', 'a.cod_analista', '=', 'ca.cod_analista')
            ->when(is_numeric($cedula), function ($query) {
                return $query->select(
                    DB::raw("FORMAT(hl.fec_ini,'dd/MM/yyyy') AS fec_ini"),
                    DB::raw("FORMAT(hl.fec_ret,'dd/MM/yyyy') AS fec_ret"),
                    'r.nom_ret AS nom_ret',
                    'c.nom_car AS nom_car',
                    'cv.nom_conv AS nom_conv',
                    'hl.sal_bas AS sal_bas',
                    'tc.nom_con',
                    'Ct.not_con',
                    DB::raw("CONCAT(a.nombre,'-',ca.cod_analista)  AS analista")
                );
            })
            ->when(!is_numeric($cedula), function ($query) {
                return $query->select(
                    't.ter_nit',
                    't.ter_nombre'
                );
            })
            ->when(is_numeric($cedula), function ($query) use ($cedula) {
                return $query->where('t.ter_nit', $cedula);
            })
            ->when(!is_numeric($cedula), function ($query) use ($cedula) {
                return $query->where('t.ter_nombre', 'like', '%' . $cedula . '%');
            })
            ->whereNotIn('ec.cod_conv', ['0001-00Q'])
            ->orderBy('hl.fec_ini', 'asc');

        $result = $query->paginate($cantidad);

        return response()->json($result);
    }

    public function analista($id){
        $result = DB::table('usr_analista')
        ->where('cod_analista','=',$id)
        ->select(
            'Nombre',
            'Telefono',
            'Correo',
            'Ext',
        )
        ->paginate();
        return response()->json($result);
    }

    public function historicoempleadoexport($cedula)
    {
        $query = DB::table('rhh_hislab as hl')
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
            ->leftJoin('usr_conv_analista as ca', 'ca.cod_conv', '=', 'ec.cod_conv')
            ->leftJoin('usr_analista as a', 'a.cod_analista', '=', 'ca.cod_analista')
            ->when(is_numeric($cedula), function ($query) {
                return $query->select(
                    DB::raw("FORMAT(hl.fec_ini,'dd/MM/yyyy') AS fec_ini"),
                    DB::raw("FORMAT(hl.fec_ret,'dd/MM/yyyy') AS fec_ret"),
                    'r.nom_ret AS nom_ret',
                    'c.nom_car AS nom_car',
                    'cv.nom_conv AS nom_conv',
                    'hl.sal_bas AS sal_bas',
                    'tc.nom_con',
                    'Ct.not_con',
                    'a.nombre as analista',
                );
            })
            ->when(!is_numeric($cedula), function ($query) {
                return $query->select(
                    't.ter_nit',
                    't.ter_nombre'
                );
            })
            ->when(is_numeric($cedula), function ($query) use ($cedula) {
                return $query->where('t.ter_nit', $cedula);
            })
            ->when(!is_numeric($cedula), function ($query) use ($cedula) {
                return $query->where('t.ter_nombre', 'like', '%' . $cedula . '%');
            })
            ->orderBy('hl.fec_ini', 'asc');

        $data = $query->get();

        return (new EmpleadosExport($data))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function username($cedula)
    {
        $result = DB::table('rhh_emplea')
            ->select(
                DB::raw("CONCAT(nom_emp, ' ', ap1_emp , ' ', ap2_emp) AS nombre"),
                'cod_emp'
            )
            ->where('cod_emp', '=', $cedula)
            ->first();
        return response()->json($result);
    }

    public function datosEmpleado($cedula)
    {
        $resultado = DB::table('rhh_emplea as e')
            ->select(
                'e.dir_res',
                'e.barrio',
                DB::raw("FORMAT(e.fec_nac, 'dd/MM/yyyy') AS fec_nac"),
                'c.nom_ciu',
                'd.nom_dep',
                'sal_bas',
                'num_ide',
                'ti.des_tip',
                DB::raw("FORMAT(e.fec_expdoc, 'dd/MM/yyyy') AS fec_expdoc"),
                DB::raw("CONCAT(ap1_emp, ' ', ap2_emp, ' ', nom_emp) AS nombre"),
                DB::raw("IIF(sex_emp=2, 'Hombre', 'Mujer') AS sexo"),
                'e_mail',
                'tel_cel',
                'tel_res',
                'avi_emp',
                'fs.nom_fdo AS salud',
                'fp.nom_fdo AS pension',
                'fc.nom_fdo AS caja'
            )
            ->join('rhh_tbfondos as fs', 'fs.cod_fdo', '=', 'e.fdo_sal')
            ->join('rhh_tbfondos as fp', 'fp.cod_fdo', '=', 'e.fdo_pen')
            ->join('rhh_tbfondos as fc', 'fc.cod_fdo', '=', 'e.ccf_emp')
            ->join('gen_tipide as ti', 'ti.cod_tip', '=', 'e.tip_ide')
            ->join('gen_paises as p', 'p.cod_pai', '=', 'e.pai_res')
            ->join('gen_deptos as d', function ($join) {
                $join->on('d.cod_dep', '=', 'e.dpt_res')
                    ->on('d.cod_pai', '=', 'e.pai_res');
            })
            ->join('gen_ciudad as c', function ($join) {
                $join->on('c.cod_ciu', '=', 'e.ciu_res')
                    ->on('c.cod_dep', '=', 'e.dpt_res')
                    ->on('c.cod_pai', '=', 'e.pai_res');
            })
            ->where('num_ide', '=', $cedula)
            ->first();

        return response()->json($resultado);
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
