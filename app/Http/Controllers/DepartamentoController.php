<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Departamento::select()
        ->get();
        return response()->json($result);
    }

    public function departamentopais($id)
    {
        $result = Departamento::where('pais_id', '=', $id)
            ->select()
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        try {
            $documento = $request->file('departamentos')->getContent();
            $lineas = explode("\n", $documento);
            $numero_departamentos = count($lineas) - 1;
            $posicion = 3;
            $lineas = explode(",", $documento);
            $lineas = explode("\"", $documento);
            for ($departamento = 0; $departamento < $numero_departamentos; $departamento++) {
                $tabla_departamentos = new Departamento;
                $tabla_departamentos->nombre = $lineas[$posicion];
                $tabla_departamentos->descripcion = $lineas[$posicion + 2];
                $tabla_departamentos->save();
                $posicion += 10;
            }
            return response()->json(['status' => 'success', 'message' => 'Registros guardados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar los datos, por favor comuniquese con el administrador del sistema']);
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
