<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo2;
use App\Models\Cargo2Examen;
use App\Models\Cargo2Recomendacion;
use App\Models\Cargos2TareaAltoRiesgo;
use App\Models\CandidatoSolicitudServicioModel;
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
        $cargos = Cargo2::leftJoin('usr_app_riesgos_laborales as rl2', 'rl2.id', '=', 'usr_app_cargos2.riesgo_laboral_id')
            ->leftJoin('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_cargos2.cargo_id')
            ->leftJoin('usr_app_subcategoria_cargos as sc', 'sc.id', '=', 'lc.subcategoria_cargo_id')
            ->leftJoin('usr_app_categoria_cargos as cc', 'cc.id', '=', 'sc.categoria_cargo_id')
            ->leftJoin('usr_app_cargos2_recomendaciones as cr', 'cr.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_lista_recomendaciones as recom', 'recom.id', '=', 'cr.recomendacion_id')
            ->leftJoin('usr_app_cargos2_examenes as cx', 'cx.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_lista_examenes as exam', 'exam.id', '=', 'cx.examen_id')
            ->leftJoin('usr_app_perfil_cargo_examenes as pcexa', 'pcexa.id', '=', 'exam.examen_id')
            ->leftJoin('usr_app_cargos2_tareas_alto_riesgo as c2tar', 'c2tar.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_tareas_alto_riesgo_cargos as tar', 'tar.id', '=', 'c2tar.tarea_alto_riesgo_id')
            ->leftJoin('usr_app_examenes_recomendaciones as er', 'er.examen_id', '=', 'pcexa.id')
            ->leftJoin('usr_app_perfil_cargo_examenes_recomendaciones as pcer', 'pcer.id', '=', 'er.recomendacion_id')
            ->select(
                'usr_app_cargos2.id as cargo_cliente_id',  // id del cargo del cliente
                'usr_app_cargos2.cargo_id as cargo_id',
                'lc.nombre as cargo',
                'lc.subcategoria_cargo_id as categoria_cargo_id',
                'sc.categoria_cargo_id as tipo_cargo_id',
                'sc.nombre as categoria',
                'cc.nombre as tipo_cargo',
                'usr_app_cargos2.funcion_cargo',
                'usr_app_cargos2.tarea_cargo',
                'usr_app_cargos2.riesgo_laboral_id',
                'rl2.nombre as riesgo_laboral',
                'cr.recomendacion_id',
                'recom.recomendacion1 as recomendacion1',
                'recom.recomendacion2 as recomendacion2',
                'tar.nombre as tarea_alto_riesgo',
                'tar.id as tarea_alto_riesgo_id',
                'pcexa.id as examen_id',
                'pcexa.nombre as examen',
                'pcer.id as recomendacion_examen_id',
                'pcer.nombre as recomendacion_examen',

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
            $tarea_cargo = $objeto['tarea_cargo'];
            $idCargo = $objeto['cargo_id'];
            $cargo_cliente_id = $objeto['cargo_cliente_id'];
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
            $tarea_alto_riesgo_id = $objeto['tarea_alto_riesgo_id'];
            $tareas_alto_riesgo_seleccionadas = $objeto['tarea_alto_riesgo'];
            $recomendacion_examen_id = $objeto['recomendacion_examen_id'];
            $recomendacion_examen = $objeto['recomendacion_examen'];

            // Verificar si el cargo ya existe en los resultados
            if (!isset($resultados[$idCargo])) {
                // Si no existe, crear un nuevo objeto para el cargo
                $resultados[$idCargo] = [
                    'cargo_id' => $idCargo,
                    'cargo_cliente_id' => $cargo_cliente_id,
                    'cargo' => $cargo,
                    'riesgo_laboral_id' => $idRiesgoLaboral,
                    'riesgo_laboral' => $riesgoLaboral,
                    'examenes' => [],
                    'recomendaciones' => [],
                    'categoria_cargo_id' => $idSubcategoria,
                    'categoria' => $subcategoria,
                    'tipo_cargo_id' => $idCategoria,
                    'tipo_cargo' => $categoria,
                    'funcion_cargo' => $funcion_cargo,
                    'tarea_cargo' => $tarea_cargo,
                    'tareas_alto_riesgo_seleccionadas' => [],
                    'recomendacion_examen' => []
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

            if (
                $recomendacion_examen_id !== null &&
                $recomendacion_examen !== null &&
                !in_array($recomendacion_examen_id, array_column($resultados[$idCargo]['recomendacion_examen'], 'id'))
            ) {
                $resultados[$idCargo]['recomendacion_examen'][] = [
                    'id' => $recomendacion_examen_id,
                    'recomendacion_examen' => $recomendacion_examen,
                ];
            }


            if (!in_array($tarea_alto_riesgo_id, $resultados[$idCargo]['tareas_alto_riesgo_seleccionadas'])) {
                $resultados[$idCargo]['tareas_alto_riesgo_seleccionadas'][] = $tarea_alto_riesgo_id;
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
    // public function create(Request $request, $id)
    // {
    //     try {
    //         $cargos = Cargo2::where('cliente_id', '=', $id)->get();
    //         return $request;
    //         return $cargos;
    //         $request = $request->all();

    //         DB::Begintransaction();
    //         $contador = 0;
    //         foreach ($request as $item) {
    //             // if (!empty($cargo_candidato_id) && isset($item['cargo_cliente_id']) && $cargo_candidato_id->cargo_solicitado_id == $item['cargo_cliente_id']) {
    //             //     continue;
    //             // }

    //             if ($item['cargo_id'] != '' || $item['riesgo_laboral_id'] != '') {
    //                 $cargo = new cargo2;
    //                 $cargo->cargo_id = $item['cargo_id'];
    //                 $cargo->riesgo_laboral_id = $item['riesgo_laboral_id'];
    //                 $cargo->funcion_cargo = $item['funcion_cargo'];
    //                 $cargo->cliente_id = $id;
    //                 $cargo->save();


    //                 foreach ($request[$contador]['examenes'] as $item) {
    //                     if ($item['id'] != '') {
    //                         $cargoExamen = new Cargo2Examen;
    //                         $cargoExamen->examen_id = $item['id'];
    //                         $cargoExamen->cargo_id = $cargo->id;
    //                         $cargoExamen->save();
    //                     }
    //                 }

    //                 // Se eliminan los requisitos del formulario
    //                 foreach ($request[$contador]['recomendaciones'] as $item) {
    //                     if ($item['id'] != '') {
    //                         $cargoRecomendacion = new Cargo2Recomendacion;
    //                         $cargoRecomendacion->recomendacion_id = $item['id'];
    //                         $cargoRecomendacion->cargo_id = $cargo->id;
    //                         $cargoRecomendacion->save();
    //                     }
    //                 }

    //                 if (!empty($request[$contador]['tareas_alto_riesgo_seleccionadas'])) {
    //                     foreach ($request[$contador]['tareas_alto_riesgo_seleccionadas'] as $item) {
    //                         $tarea_alto_riesgo = new Cargos2TareaAltoRiesgo;
    //                         $tarea_alto_riesgo->tarea_alto_riesgo_id = $item;
    //                         $tarea_alto_riesgo->cargo_id = $cargo->id;
    //                         $tarea_alto_riesgo->save();
    //                     }
    //                 }
    //                 $contador++;
    //             }
    //         }
    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Registros insertados de manera exitosa.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $e;
    //         return response()->json(['status' => 'success', 'message' => 'Hubo un error al guardar los registros.']);
    //     }
    // }

    public function create(Request $request, $id)
    {

        $cargos = Cargo2::where('cliente_id', '=', $id)->get();
        $cargos_id = $cargos->pluck('id')->toArray();
        $cargoExamenes = Cargo2Examen::whereIn('cargo_id', $cargos_id)->select()->delete();
        // $cargoRecomendaciones = Cargo2Recomendacion::whereIn('cargo_id', $cargos_id)->select()->delete();
        $cargoRiesgoLaboral = Cargos2TareaAltoRiesgo::whereIn('cargo_id', $cargos_id)->select()->delete();
        // $cargo = Cargo2::where('cliente_id', '=', $id)->delete();

        $request = $request->all();
        try {

            DB::Begintransaction();
            $contador = 0;
            foreach ($request as $item) {
                if ($item['cargo_id'] != '' || $item['riesgo_laboral_id'] != '') {
                    $posicion = false;
                    if (isset($item['cargo_cliente_id'])) {
                        $posicion = array_search($item['cargo_cliente_id'], $cargos_id);
                    }
                    if ($posicion !== false) {
                        $cargos[$posicion]->funcion_cargo = $item['funcion_cargo'];
                        $cargos[$posicion]->tarea_cargo = $item['tarea_cargo'];
                        $cargos[$posicion]->save();
                    } else {
                        $cargo = new cargo2;
                        $cargo->cargo_id = $item['cargo_id'];
                        $cargo->riesgo_laboral_id = $item['riesgo_laboral_id'];
                        $cargo->funcion_cargo = $item['funcion_cargo'];
                        $cargo->tarea_cargo = $item['tarea_cargo'];
                        $cargo->cliente_id = $id;
                        $cargo->save();
                    }

                    $cargo_id = isset($cargo->id) ? $cargo->id : $cargos[$posicion]->id;

                    foreach ($request[$contador]['examenes'] as $item) {
                        if ($item['id'] != '') {
                            $cargoExamen = new Cargo2Examen;
                            $cargoExamen->examen_id = $item['id'];
                            $cargoExamen->cargo_id = $cargo_id;
                            $cargoExamen->save();
                        }
                    }

                    // Se eliminan los requisitos del formulario
                    foreach ($request[$contador]['recomendaciones'] as $item) {
                        if ($item['id'] != '') {
                            $cargoRecomendacion = new Cargo2Recomendacion;
                            $cargoRecomendacion->recomendacion_id = $item['id'];
                            $cargoRecomendacion->cargo_id = $cargo_id;
                            $cargoRecomendacion->save();
                        }
                    }

                    if (!empty($request[$contador]['tareas_alto_riesgo_seleccionadas'])) {
                        foreach ($request[$contador]['tareas_alto_riesgo_seleccionadas'] as $item) {
                            $tarea_alto_riesgo = new Cargos2TareaAltoRiesgo;
                            $tarea_alto_riesgo->tarea_alto_riesgo_id = $item;
                            $tarea_alto_riesgo->cargo_id = $cargo_id;
                            $tarea_alto_riesgo->save();
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
        $cargo = Cargo2::where('id', '=', $id)->first();
        $cargo_candidato_id = CandidatoSolicitudServicioModel::where('cargo_solicitado_id', $cargo->id)->first();
        if (empty($cargo_candidato_id)) {
            $cargo->delete();
            return response()->json(['status' => 'success', 'message' => 'Cargo eliminado de manera exitosa.', 'delete' => 1]);
        } else {
            return response()->json(['status' => 'success', 'message' => 'El cargo no puede ser eliminado.', 'delete' => 0]);
        }
    }

    public function cargoById($id, $cliente_id)
    {
        $cargos = Cargo2::leftJoin('usr_app_riesgos_laborales as rl2', 'rl2.id', '=', 'usr_app_cargos2.riesgo_laboral_id')
            ->leftJoin('usr_app_lista_cargos as lc', 'lc.id', '=', 'usr_app_cargos2.cargo_id')
            ->leftJoin('usr_app_subcategoria_cargos as sc', 'sc.id', '=', 'lc.subcategoria_cargo_id')
            ->leftJoin('usr_app_categoria_cargos as cc', 'cc.id', '=', 'sc.categoria_cargo_id')
            ->leftJoin('usr_app_cargos2_recomendaciones as cr', 'cr.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_lista_recomendaciones as recom', 'recom.id', '=', 'cr.recomendacion_id')
            ->leftJoin('usr_app_cargos2_examenes as cx', 'cx.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_lista_examenes as exam', 'exam.id', '=', 'cx.examen_id')
            ->leftJoin('usr_app_perfil_cargo_examenes as pcexa', 'pcexa.id', '=', 'exam.examen_id')
            ->leftJoin('usr_app_cargos2_tareas_alto_riesgo as c2tar', 'c2tar.cargo_id', '=', 'usr_app_cargos2.id')
            ->leftJoin('usr_app_tareas_alto_riesgo_cargos as tar', 'tar.id', '=', 'c2tar.tarea_alto_riesgo_id')
            ->leftJoin('usr_app_examenes_recomendaciones as er', 'er.examen_id', '=', 'pcexa.id')
            ->leftJoin('usr_app_perfil_cargo_examenes_recomendaciones as pcer', 'pcer.id', '=', 'er.recomendacion_id')
            ->select(
                'usr_app_cargos2.id as cargo_cliente_id',  // id del cargo del cliente
                'usr_app_cargos2.cargo_id as cargo_id',
                'lc.nombre as cargo',
                'lc.subcategoria_cargo_id as categoria_cargo_id',
                'sc.categoria_cargo_id as tipo_cargo_id',
                'sc.nombre as categoria',
                'cc.nombre as tipo_cargo',
                'usr_app_cargos2.funcion_cargo',
                'usr_app_cargos2.tarea_cargo',
                'usr_app_cargos2.riesgo_laboral_id',
                'rl2.nombre as riesgo_laboral',
                'cr.recomendacion_id',
                'recom.recomendacion1 as recomendacion1',
                'recom.recomendacion2 as recomendacion2',
                'tar.nombre as tarea_alto_riesgo',
                'tar.id as tarea_alto_riesgo_id',
                'pcexa.id as examen_id',
                'pcexa.nombre as examen',
                'pcer.id as recomendacion_examen_id',
                'pcer.nombre as recomendacion_examen',

            )
            ->where('usr_app_cargos2.cargo_id', '=', $id)
            ->where('cliente_id', '=', $cliente_id)
            ->distinct('exam.nombre as examen')
            ->get();

        // Array para almacenar los resultados
        $resultados = [];

        // Recorrer el array original
        foreach ($cargos as $objeto) {
            // Extraer los datos del objeto

            $funcion_cargo = $objeto['funcion_cargo'];
            $tarea_cargo = $objeto['tarea_cargo'];
            $idCargo = $objeto['cargo_id'];
            $cargo_cliente_id = $objeto['cargo_cliente_id'];
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
            $tarea_alto_riesgo_id = $objeto['tarea_alto_riesgo_id'];
            $tareas_alto_riesgo_seleccionadas = $objeto['tarea_alto_riesgo'];
            $recomendacion_examen_id = $objeto['recomendacion_examen_id'];
            $recomendacion_examen = $objeto['recomendacion_examen'];

            // Verificar si el cargo ya existe en los resultados
            if (!isset($resultados[$idCargo])) {
                // Si no existe, crear un nuevo objeto para el cargo
                $resultados[$idCargo] = [
                    'cargo_id' => $idCargo,
                    'cargo_cliente_id' => $cargo_cliente_id,
                    'cargo' => $cargo,
                    'riesgo_laboral_id' => $idRiesgoLaboral,
                    'riesgo_laboral' => $riesgoLaboral,
                    'examenes' => [],
                    'recomendaciones' => [],
                    'categoria_cargo_id' => $idSubcategoria,
                    'categoria' => $subcategoria,
                    'tipo_cargo_id' => $idCategoria,
                    'tipo_cargo' => $categoria,
                    'funcion_cargo' => $funcion_cargo,
                    'tarea_cargo' => $tarea_cargo,
                    'tareas_alto_riesgo_seleccionadas' => [],
                    'recomendacion_examen' => []
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

            if (
                $recomendacion_examen_id !== null &&
                $recomendacion_examen !== null &&
                !in_array($recomendacion_examen_id, array_column($resultados[$idCargo]['recomendacion_examen'], 'id'))
            ) {
                $resultados[$idCargo]['recomendacion_examen'][] = [
                    'id' => $recomendacion_examen_id,
                    'recomendacion_examen' => $recomendacion_examen,
                ];
            }


            if (!in_array($tarea_alto_riesgo_id, $resultados[$idCargo]['tareas_alto_riesgo_seleccionadas'])) {
                $resultados[$idCargo]['tareas_alto_riesgo_seleccionadas'][] = $tarea_alto_riesgo_id;
            }
        }

        // Resultado: Array final con cargos, exámenes y requisitos sin duplicados
        $resultados = array_values($resultados);
        return response()->json($resultados);
    }
}