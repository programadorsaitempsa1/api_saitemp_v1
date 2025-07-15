<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AfpFormularioIngreso;

class AfpFormularioIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = AfpFormularioIngreso::select('id', 'nombre')
            ->whereIn('id', [2, 3, 4, 5, 6, 7, 8, 9])
            ->orderByRaw('CASE WHEN id = 8 THEN 0 ELSE 1 END ASC')
            ->orderBy('nombre', 'asc')
            ->get();
        return response()->json($result);
    }

    public function getByCodAfp($cod_afp)
    {
        try {
            $rol = AfpFormularioIngreso::select('*')->where('adres_codigo', $cod_afp)->first();

            return $rol;
        } catch (\Exception $e) {
            return false;
        }
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
