<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // $result = Sucursal::select()
        // ->get();
        // return response()->json($result);
        // Realiza la consulta
        $result = Sucursal::all();

        // Define las ciudades deseadas
        $ciudadesDeseadas = ['NO APLICA','MEDELLIN', 'BOGOTA', 'CARTAGENA'];

        // Filtra las ciudades deseadas
        $ciudadesFiltradas = $result->filter(function ($sucursal) use ($ciudadesDeseadas) {
            return in_array(trim($sucursal->nom_suc), $ciudadesDeseadas);
        });

        // Limita el resultado a los primeros tres elementos después de filtrar
        $ciudadesFiltradas = $ciudadesFiltradas->take(4);

        // Convertir a JSON para la respuesta
        return response()->json($ciudadesFiltradas->values());
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
