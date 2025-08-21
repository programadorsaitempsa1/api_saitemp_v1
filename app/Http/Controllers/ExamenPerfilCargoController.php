<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamenPerfilCargoModel;

class ExamenPerfilCargoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      
        $result = ExamenPerfilCargoModel::select(
            'id',
            'nombre',
        )->get();
        return response()->json($result);
    }

    public function tabla($cantidad)
    {
      
        $result = ExamenPerfilCargoModel::select(
            'id',
            'nombre',
        )->paginate($cantidad);
        return response()->json($result);
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
            $query = ExamenPerfilCargoModel::select(
                'id',
                'nombre'
            )->orderby('id', 'DESC');
            switch ($operador) {
                case 'Contiene':
                    $query->where($campo, 'like', '%' . $valor . '%');

                    break;
                case 'Igual a':
                    $query->where($campo, '=', $valor);
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
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
