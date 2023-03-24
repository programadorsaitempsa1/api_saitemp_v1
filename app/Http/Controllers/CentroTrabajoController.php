<?php

namespace App\Http\Controllers;
use App\Models\CentroTrabajo;
use Illuminate\Http\Request;

class CentroTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http \Response
     */
    public function index()
    {
        $result = CentroTrabajo::select(
            'cod_CT',
            'descripcion'
        )
        ->orderby('cod_CT')
        ->paginate(12);
        return response()->json($result);
    }

    public function search($texto)
    {
        $texto;
        $result = CentroTrabajo::select(
            'cod_CT',
            'descripcion'
        )
            ->where('cod_CT', 'like', '%' . $texto . '%')
            ->paginate(12);
        if (count($result) == 0) {
            $result = CentroTrabajo::select(
                'cod_CT',
                'descripcion'
            )
                ->where('descripcion', 'like', '%' . $texto . '%')
                ->paginate(12);
        }
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
