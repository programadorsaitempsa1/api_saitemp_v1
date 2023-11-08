<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OservicioCargo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OservicioCargoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioCargo::select()
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        try {
            $cliente = new OservicioClienteController;
            $cliente_id = $cliente->getIdCliente($id);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Cliente no registrado']);
        }
        DB::beginTransaction();
        $cargos = $request->all();
        foreach ($cargos as $item) {
            try {
                $result = new OservicioCargo;
                $result->cliente_id = $cliente_id;
                $result->nombre = $item['nombre'];
                $result->cantidad_vacantes = $item['cantidad_personas'];
                $result->salario = $item['salario'];
                $result->fecha_inicio = $item['fecha_inicio'];
                $result->fecha_solicitud = Carbon::createFromFormat('Y-m-d\TH:i', $item['fecha_solicitud'], 'America/Bogota');
                $result->observaciones = $item['observaciones'];
                $result->ciudad_id = $item['ciudad_id'];
                $result->save();
            } catch (\Exception $e) {
                DB::rollback();
                return $e;
                return response()->json(['status' => 'success', 'message' => 'Error al guardar registro']);
            }
        }
        DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
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
        try {
            $cliente = new OservicioClienteController;
            $cliente_id = $cliente->getIdCliente($id);
            $nombre_cargos = OservicioCargo::select(
                'nombre'
            )
                ->where('cliente_id', $cliente_id)
                ->get();
            $cargos = $request->all();
            $existe_cargo = false;
            foreach ($cargos as $cargo) {
                foreach ($nombre_cargos as $nombre_cargo) {
                    if ($cargo['nombre'] == $nombre_cargo->nombre) {
                        $existe_cargo = true;
                    }
                }
                if (!$existe_cargo) {
                    $result = new OservicioCargo;
                    $result->cliente_id = $cliente_id;
                    $result->nombre = $cargo['nombre'];
                    $result->cantidad_vacantes = $cargo['cantidad_personas'];
                    $result->salario = $cargo['salario'];
                    $result->fecha_inicio = $cargo['fecha_inicio'];
                    $result->fecha_solicitud = Carbon::createFromFormat('Y-m-d\TH:i', $cargo['fecha_solicitud'], 'America/Bogota');
                    $result->observaciones = $cargo['observaciones'];
                    $result->ciudad_id = $cargo['ciudad_id'];
                    $result->save();
                }
                $existe_cargo = false;
            }
            if ($existe_cargo) {
                return response()->json(['status' => 'error', 'message' => 'Los cargos insertados ya están registrados']);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Los cargos fueron registrados de manera exitosa']);
            }
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar los cargos']);
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
        $result = OservicioCargo::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
