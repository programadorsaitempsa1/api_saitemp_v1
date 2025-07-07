<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CiudadLaboratorio;
use App\Models\Municipios;

class CiudadLaboratorioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = CiudadLaboratorio::select(
            'id',
            'ciudad_id',
            'laboratorio as nombre',
        )
            ->get();
        return response()->json($result);
    }

    public function byid($id)
    {
        $result = CiudadLaboratorio::where('ciudad_id', $id)
            ->select(
                'id',
                'ciudad_id',
                'laboratorio as nombre',
            )
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

    public function rellenarNoAplica()
    {
        $municipios = Municipios::select(
            'id'
        )
            ->get();
        try {
            foreach ($municipios as $municipio) {
                $ciudadLaboratorio = new CiudadLaboratorio();
                $ciudadLaboratorio->ciudad_id = $municipio['id'];
                $ciudadLaboratorio->laboratorio = "No aplica";
                $ciudadLaboratorio->save();
            }
            return "Proceso creado correctamente";
        } catch (\Exception $e) {
            return $e;
        }
    }
}