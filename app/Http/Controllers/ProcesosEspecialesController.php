<?php

namespace App\Http\Controllers;

use App\Models\ProcesosEspeciales;
use App\Models\FormularioProcesosEspeciales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcesosEspecialesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ProcesosEspeciales::select(
            'cod_proc',
            'nom_proc',
            'nom_sp',
        )
            ->get();
        return response()->json($result);
    }

    public function form($codigo)
    {
        $result = FormularioProcesosEspeciales::select(
            // 'nom_param',
            // 'des_param',
            // 'tip_obj',
            // 'val_def',
            // 'for_cmp',
            // 'req_cmp',
            // 'ord_cmp',
            // 'tab_hlp',
            // 'nom_hlp',
            // 'des_hlp',
            // 'fil_hlp',
            // 'lon_max',
            // 'lon_max',
        )
            ->where('cod_proc', '=', $codigo)
            ->get();
        return response()->json($result);
    }

    public function listasprocesosespeciales($tabla, $codigo1, $codigo2)
    {
        $result = DB::table($tabla)
            ->select($codigo1, $codigo2)
            ->paginate();
        return response()->json($result);
        // $query = DB::table($tabla);
        // $count = $query->count();

        // if ($count > 20) {
        //     $result = $query->paginate(20);
        // } else {
        //     $result = $query->get();
        // }

        // return response()->json($result);
    }

    public function listasprocesosespecialesfilter($tabla, $codigo1, $codigo2)
    {
        $result = DB::table($tabla)
            ->select($codigo1, $codigo2)
            ->paginate();
        return response()->json($result);
    }
    
    public function filtroprocesosespeciales($tabla, $search, $codigo1, $codigo2)
    {
        $result = DB::table($tabla)
            ->select($codigo1, $codigo2)
            ->where($codigo1, 'like', '%'.$search.'%')
            ->orwhere($codigo2, 'like', '%'.$search.'%')
            ->paginate();
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
