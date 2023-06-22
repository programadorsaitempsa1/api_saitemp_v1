<?php

namespace App\Http\Controllers;
use App\Models\CodigoCiiu;
use App\Models\ActividadCiiu;
use Illuminate\Http\Request;

class ActividadCiiuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ActividadCiiu::select(
            'codigo_actividad',
            'descripcion'
        )
        ->paginate();

        $result->transform(function ($item) {
            unset($item->row_num);
            return $item;
        });
        return response()->json($result);
    }

    public function activityBycode ($id){
        $result = ActividadCiiu::join('usr_app_codigos_ciiu','usr_app_codigos_ciiu.id','=','usr_app_actividades_ciiu.codigo_ciiu_id')
        ->where('usr_app_actividades_ciiu.codigo_ciiu_id','=',$id)
        ->select(
            'usr_app_actividades_ciiu.codigo_actividad',
            'usr_app_actividades_ciiu.descripcion'
        )
        ->paginate();
        return response()->json($result);
    }

    public function filter ($id){
        $result = ActividadCiiu::where('codigo_actividad','=',$id)
        ->select(
            'codigo_actividad',
            'descripcion'
        )
        ->paginate();

        // $result->transform(function ($item) {
        //     unset($item->row_num);
        //     return $item;
        // });
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
