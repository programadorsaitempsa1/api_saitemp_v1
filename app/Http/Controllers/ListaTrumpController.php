<?php

namespace App\Http\Controllers;

use App\Models\ListaTrump;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListaTrumpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($codigo)
    {
        $result = ListaTrump::select(
            'cod_emp',
            'nombre',
            'observacion',
            'fecha',
            'bloqueado',
        )
            ->where('cod_emp', '=', $codigo)
            ->paginate(10);

        $result->transform(function ($item) {
            if ($item->bloqueado == 1) {
                $item->bloqueado = 'Si';
            } else {
                $item->bloqueado = 'No';
            }
            return $item;
        });

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
            DB::beginTransaction();
            $candidato = ListaTrump::where('cod_emp', $request->numero_documento_candidato)->select()->first();
            if (!isset($candidato)) {
                $result = new ListaTrump;
                $result->cod_emp = $request->numero_documento_candidato;
                $result->nombre = $request->nombre_candidato . ' ' . $request->apellido_candidato;
                $result->observacion = $request->motivo;
                $result->fecha = now();
                $result->cod_conv = 'NA';
                $result->usuario = 1;
                $result->bloqueado = 1;
                if ($result->save()) {
                    DB::commit();
                    return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'El candidato ya cuenta con un registro en lista trump.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro']);
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
