<?php

namespace App\Http\Controllers;

use App\Models\Municipios;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Municipios::select()
        ->get();
        return response()->json($result);
    }

    public function municipiodepartamento($id)
    {
        $municipios = Municipios::where('departamento_id', '=', $id)
            ->select()
            ->get();
        return response()->json($municipios);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $documento = $request->file('municipios')->getContent();
            $lineas = explode("\n", $documento);
            $numero_municipios = count($lineas) - 1;
            $lineas = explode(",", $documento);
            $lineas = explode("\"", $documento);
            $posicion = 3;
            set_time_limit(0);
            for ($municipio = 0; $municipio < $numero_municipios; $municipio++) {
                $tabla_municipio = new Municipios;
                $tabla_municipio->nombre = $lineas[$posicion];
                $tabla_municipio->descripcion = $lineas[$posicion + 2];
                $tabla_municipio->departamento_id = $lineas[$posicion + 4];
                $tabla_municipio->save();
                $posicion += 12;
            }
            return response()->json(['status'=>'success','message'=>'Registros guardados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status'=>'error','message'=>'Error al guardar los datos, por favor comuniquese con el administrador del sistema']);
        }

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
