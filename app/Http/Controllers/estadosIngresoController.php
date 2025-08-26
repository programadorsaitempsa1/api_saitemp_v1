<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\estadosIngreso;

class estadosIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = estadosIngreso::where('id', '!=', 14)->select(
            'id',
            'nombre',
            'color'
        )
            ->orderby('posicion')
            ->get();
        return response()->json($result);
    }


    public function tabla($cantidad)
    {
        $result = estadosIngreso::where('id', '!=', 14)->select(
            'id',
            'nombre',
            'color',
            'posicion',
            'descripcion',
        )
            ->orderby('posicion')
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
        try {
            $posicion = estadosIngreso::orderby('posicion', 'DESC')->first();
            $estado = new estadosIngreso;
            $estado->nombre = $request->nombre;
            $estado->color = $request->color;
            $estado->posicion = intval($posicion->posicion+1);
            $estado->descripcion = $request->descripcion;
            $estado->save();
            return response()->json(["status" => "success", "message" => "Registro actualizado exitosamente."]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => "Error al guardar el registro."]);
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
        $estado = estadosIngreso::find($id);

        if ($request->posicion > $estado->posicion) {
            $estados = estadosIngreso::whereBetween('posicion', [intval($estado->posicion + 1), $request->posicion])->orderby('posicion', 'ASC')->get();
            $this->ordenaEstados($estados,  $estado->posicion);
        } else if ($request->posicion < $estado->posicion) {
            $estados = estadosIngreso::where('posicion', '>=', $request->posicion)->orderby('posicion', 'ASC')->get();
            $posicion = intval($request->posicion + 1);
            $this->ordenaEstados($estados, $posicion);
        }
        $estado->nombre = $request->nombre;
        $estado->color = $request->color;
        $estado->posicion = $request->posicion;
        $estado->descripcion = $request->descripcion;
        $estado->save();
        return response()->json(["status" => "success", "message" => "Registro actualizado exitosamente."]);
    }

    public function ordenaEstados($estados, $posicion)
    {
        foreach ($estados as $item) {
            $item->posicion = $posicion;
            $posicion++;
            $item->save();
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
        try {
            $result = estadosIngreso::find($id);
            $result->delete();
            return response()->json(["status" => "success", "message" => "Registro eliminado exitosamente."]);
        } catch (\Exception $e) {
            return response()->json(["status" => "success", "message" => "Error al eliminar registro."]);
        }
    }
}
