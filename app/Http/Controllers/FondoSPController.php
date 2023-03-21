<?php

namespace App\Http\Controllers;
use App\Models\fondoSP;
use Illuminate\Http\Request;

class FondoSPController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fondosalud()
    {
        $result = fondoSP::select(
            'cod_fdo',
            'nom_fdo'
        )
        ->where('cod_fdo','>',199)
        ->Where('cod_fdo','<=',299)
        ->paginate(12);
        return response()->json($result);
    }
    
    public function fondopension()
    {
        $result = fondoSP::select(
            'cod_fdo',
            'nom_fdo'
        )
        ->where('cod_fdo','<=',199)
        ->get();
        return response()->json($result);
    }
    
    public function CajaCompensacion()
    {
        $result = fondoSP::select(
            'cod_fdo',
            'nom_fdo'
        )
        ->where('nom_fdo','like','CCF%')
        ->get();
        return response()->json($result);
    }
    
    public function riesgoLaboral()
    {
        $result = fondoSP::select(
            'cod_fdo',
            'nom_fdo'
        )
        ->where('cod_fdo','>=',300)
        ->Where('cod_fdo','<=',399)
        ->get();
        return response()->json($result);
    }

    public function fondoCesantias()
    {
        $result = fondoSP::select(
            'cod_fdo',
            'nom_fdo'
        )
        ->where('cod_fdo','>=',500)
        ->where('cod_fdo','<=',599)
        ->get();
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
