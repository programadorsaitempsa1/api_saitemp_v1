<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamenesRecomendacionesModel;

class ExamenesRecomendacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = ExamenesRecomendacionesModel::leftJoin('usr_app_perfil_cargo_examenes_recomendaciones as pcer', 'pcer.id', 'usr_app_examenes_recomendaciones.recomendacion_id')
            ->leftJoin('usr_app_perfil_cargo_examenes as pce', 'pce.id', 'usr_app_examenes_recomendaciones.examen_id')
            ->select(
                'usr_app_examenes_recomendaciones.id',
                'pce.nombre as examenes',
                'pcer.nombre as rescomendaciones',
            )->orderby('pce.nombre')
            ->paginate($cantidad);
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $examenes_medicos = $request['examenes_medicos'];
        $recomendaciones = $request['recomendaciones_examenes'];
        foreach ($examenes_medicos as $examen) {
            foreach ($recomendaciones as $recomendacion) {
                $recomendaciones_examenes = new ExamenesRecomendacionesModel;
                $recomendaciones_examenes->recomendacion_id = $recomendacion['id'];
                $recomendaciones_examenes->examen_id = $examen['id'];
                $recomendaciones_examenes->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Reguistro guardado exitosamente.']);
    }

    public function filtro($cadena)
    {
        try {
            $cadenaJSON = base64_decode($cadena);
            $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
            $valores = explode("/", $cadenaUTF8);
            $campo = $valores[0];
            $operador = $valores[1];
            $valor = $valores[2];
            $valor2 = isset($valores[3]) ? $valores[3] : null;
            $query = ExamenesRecomendacionesModel::leftJoin('usr_app_perfil_cargo_examenes_recomendaciones as pcer', 'pcer.id', 'usr_app_examenes_recomendaciones.recomendacion_id')
                ->leftJoin('usr_app_perfil_cargo_examenes as pce', 'pce.id', 'usr_app_examenes_recomendaciones.examen_id')
                ->select(
                    'usr_app_examenes_recomendaciones.id',
                    'pce.nombre as examenes',
                    'pcer.nombre as rescomendaciones',
                )->orderby('usr_app_examenes_recomendaciones.id', 'DESC');
            switch ($operador) {
                case 'Contiene':
                   if ($campo == "examenes") {
                        $query->where('pce.nombre', 'like', '%'.$valor.'%');
                    } else if ($campo == "rescomendaciones") {
                        $query->where('pcer.nombre', 'like', '%'.$valor.'%');
                    } 
                    break;
                case 'Igual a':
                    if ($campo == "examenes") {
                        $query->where('pce.nombre', '=', $valor);
                    } else if ($campo == "rescomendaciones") {
                        $query->where('pcer.nombre', '=', $valor);
                    } 
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
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

     public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = ExamenesRecomendacionesModel::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
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
        $result = ExamenesRecomendacionesModel::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Reguistro eliminado exitosamente.']);
        }
    }
}
