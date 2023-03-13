<?php

namespace App\Http\Controllers;

use App\Models\SigZonas;
use Illuminate\Http\Request;

class SigZonaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $zonas = SigZonas::select(

                "sig_zonas.id",
                "sig_zonas.nombre",
                "sig_zonas.observacion",
                "sig_zonas.departamento",
                "sig_zonas.municipios",
            )
            ->paginate($cantidad);
        return response()->json($zonas);
    }

    public function lista()
    {
        $zonas = SigZonas::select(

                "sig_zonas.id",
                "sig_zonas.nombre",
                "sig_zonas.observacion",
                "sig_zonas.departamento",
                "sig_zonas.municipios",
            )
            ->get();
        return response()->json($zonas);
    }

    public function filtro($cadena)
    {
        $consulta = base64_decode($cadena);
        $valores = explode("/", $consulta);
        $campo = $valores[0];
        $operador = $valores[1];
        $valor = $valores[2];

        if ($operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $valor . '%';
        } else if ($operador == 'Igual a') {
            $operador = '=';
        }
        $zonas = SigZonas::where('sig_zonas.'. $campo, $operador, $valor)
            ->select(

                "sig_zonas.id",
                "sig_zonas.nombre",
                "sig_zonas.observacion",
                "sig_zonas.departamento",
                "sig_zonas.municipios",
            )
            ->paginate();
        return response()->json($zonas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // return $request->municipios;

        $zona = new SigZonas;

        $zona->nombre = $request->nombre;
        $zona->observacion = $request->observacion;
        $zona->departamento = $request->departamento;
        foreach ($request->municipios as $valor) {
            $zona->municipios .= $valor . ', ';
        }
        $zona->municipios = substr($zona->municipios,0,-2);
        if ($zona->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
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

    public function actualizacionmasiva(Request $request)
    {
        try {
            foreach ($request->id as $valor) {
                $result = SigZonas::find($valor);

                foreach ($request->campos as $clave => $valor) {
                    if ($valor != "") {
                        $result->$clave = $valor;
                    }
                }
                $result->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros actualizados exitosamente']);

        } catch (\Exception$e) {
            return response()->json($e);
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
        }
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
        $zona = SigZonas::find($id);
        $municipios = '';
        $zona->nombre = $request->nombre;
        $zona->observacion = $request->observacion;
        $zona->departamento = $request->departamento;
        foreach ($request->municipios as $valor) {
            $municipios .= $valor . ', ';
        }
        $zona->municipios = substr($municipios,0,-2);
        if ($zona->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $zona = SigZonas::find($id);
        if ($zona->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = SigZonas::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
