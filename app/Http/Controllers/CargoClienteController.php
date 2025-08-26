<?php

namespace App\Http\Controllers;

use App\Models\CargoCliente;
use Illuminate\Http\Request;
use App\Models\Cargo2;
use App\Models\Cargo2Examen;
use App\Models\Cargo2Recomendacion;
use App\Models\Cargos;
use Illuminate\Support\Facades\DB;

class CargoClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        // $result = CargoCliente::select()
        //     ->get();
        // return response()->json($result);
        $cargos = Cargo2::join('usr_app_riesgos_laborales as rl2', 'rl2.id', '=', 'usr_app_cargos2.riesgo_laboral_id')
            ->join('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_cargos2.cargo_id')
            ->join('usr_app_subcategoria_cargos as sc', 'sc.id', '=', 'lc.subcategoria_cargo_id')
            ->join('usr_app_categoria_cargos as cc', 'cc.id', '=', 'sc.categoria_cargo_id')
            ->join('usr_app_cargos2_recomendaciones as cr', 'cr.cargo_id', '=', 'usr_app_cargos2.id')
            ->join('usr_app_lista_recomendaciones as recom', 'recom.id', '=', 'cr.recomendacion_id')
            ->join('usr_app_cargos2_examenes as cx', 'cx.cargo_id', '=', 'usr_app_cargos2.id')
            ->join('usr_app_lista_examenes as exam', 'exam.id', '=', 'cx.examen_id')
            ->select(
                'usr_app_cargos2.cargo_id as cargo_id',
                'lc.nombre as cargo',
                'lc.subcategoria_cargo_id as categoria_cargo_id',
                'sc.categoria_cargo_id as tipo_cargo_id',
                'sc.nombre as categoria',
                'cc.nombre as tipo_cargo',
                'usr_app_cargos2.funcion_cargo as funcion_cargo',
                'usr_app_cargos2.riesgo_laboral_id',
                'rl2.nombre as riesgo_laboral',
                'cr.recomendacion_id',
                'recom.recomendacion1 as recomendacion1',
                'recom.recomendacion2 as recomendacion2',
                'cx.examen_id',
                'exam.nombre as examen',

            )
            ->where('cliente_id', '=', $id)
            ->distinct('exam.nombre as examen')
            ->get();

        // Array para almacenar los resultados
        $resultados = [];

        // Recorrer el array original
        foreach ($cargos as $objeto) {
            // Extraer los datos del objeto

            $funcion_cargo = $objeto['funcion_cargo'];
            $idCargo = $objeto['cargo_id'];
            $cargo = $objeto['cargo'];
            $idSubcategoria = $objeto['categoria_cargo_id'];
            $subcategoria = $objeto['categoria'];
            $idCategoria = $objeto['tipo_cargo_id'];
            $categoria = $objeto['tipo_cargo'];
            $idRiesgoLaboral = $objeto['riesgo_laboral_id'];
            $riesgoLaboral = $objeto['riesgo_laboral'];
            $idRequisito = $objeto['recomendacion_id'];
            $recomendacion1 = $objeto['recomendacion1'];
            $recomendacion2 = $objeto['recomendacion2'];
            $idExamen = $objeto['examen_id'];
            $examen = $objeto['examen'];

            // Verificar si el cargo ya existe en los resultados
            if (!isset($resultados[$idCargo])) {
                // Si no existe, crear un nuevo objeto para el cargo
                $resultados[$idCargo] = [
                    'cargo_id' => $idCargo,
                    'cargo' => $cargo,
                    'riesgo_laboral_id' => $idRiesgoLaboral,
                    'riesgo_laboral' => $riesgoLaboral,
                    'examenes' => [],
                    'recomendaciones' => [],
                    'categoria_cargo_id' => $idSubcategoria,
                    'categoria' => $subcategoria,
                    'tipo_cargo_id' => $idCategoria,
                    'tipo_cargo' => $categoria,
                    'funcion_cargo' => $funcion_cargo
                ];
            }

            // Verificar si el examen ya existe en los resultados del cargo
            if (!in_array($idExamen, array_column($resultados[$idCargo]['examenes'], 'id'))) {
                $resultados[$idCargo]['examenes'][] = [
                    'id' => $idExamen,
                    'nombre' => $examen,
                ];
            }

            // Verificar si el requisito ya existe en los resultados del cargo
            if (!in_array($idRequisito, array_column($resultados[$idCargo]['recomendaciones'], 'id'))) {
                $resultados[$idCargo]['recomendaciones'][] = [
                    'id' => $idRequisito,
                    'recomendacion1' => $recomendacion1,
                    'recomendacion2' => $recomendacion2,
                ];
            }
        }

        // Resultado: Array final con cargos, exámenes y requisitos sin duplicados
        $resultados = array_values($resultados);
        return response()->json($resultados);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $cargoExamen = Cargo2Examen::where('cargo_id', '=', $id)->delete();
        $cargoRecomendacion = Cargo2Recomendacion::where('cargo_id', '=', $id)->delete();
        $cargo = Cargo2::where('cliente_id', '=', $id)->delete();

        $request = $request->all();
        try {

            DB::Begintransaction();
            $contador = 0;
            foreach ($request as $item) {
                if ($item['cargo_id'] != '' || $item['riesgo_laboral_id'] != '') {
                    $cargo = new cargo2;
                    $cargo->cargo_id = $item['cargo_id'];
                    $cargo->riesgo_laboral_id = $item['riesgo_laboral_id'];
                    $cargo->funcion_cargo = $item['funcion_cargo'];
                    $cargo->cliente_id = $id;
                    $cargo->save();


                    foreach ($request[$contador]['examenes'] as $item) {
                        if ($item['id'] != '') {
                            $cargoExamen = new Cargo2Examen;
                            $cargoExamen->examen_id = $item['id'];
                            $cargoExamen->cargo_id = $cargo->id;
                            $cargoExamen->save();
                        }
                    }

                    // Se eliminan los requisitos del formulario
                    foreach ($request[$contador]['recomendaciones'] as $item) {
                        if ($item['id'] != '') {
                            $cargoRecomendacion = new Cargo2Recomendacion;
                            $cargoRecomendacion->recomendacion_id = $item['id'];
                            $cargoRecomendacion->cargo_id = $cargo->id;
                            $cargoRecomendacion->save();
                        }
                    }
                    $contador++;
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registros insertados de manera exitosa.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json(['status' => 'success', 'message' => 'Hubo un error al guardar los registros.']);
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
