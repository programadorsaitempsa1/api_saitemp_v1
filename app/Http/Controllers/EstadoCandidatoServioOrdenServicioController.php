<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstadoCandidatoServioOrdenServicioModel;
use Illuminate\Support\Facades\DB;

class EstadoCandidatoServioOrdenServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = EstadoCandidatoServioOrdenServicioModel::join('usr_app_estados_ingreso as est', 'est.id', 'usr_app_estados_candidato_orden_servicio.estado_orden_servicio_id')
            ->join('usr_app_estado_candidato_servicio as estcan', 'estcan.id', 'usr_app_estados_candidato_orden_servicio.usr_app_estado_candidato_servicio')
            ->select(
                'usr_app_estados_candidato_orden_servicio.id',
                'estcan.nombre  as estadocandidato',
                'est.nombre as estadoseiya',

            )
            ->get();
        return response()->json($result);
    }

    public function tabla($cantidad)
    {
        $result = EstadoCandidatoServioOrdenServicioModel::join('usr_app_estados_ingreso as est', 'est.id', 'usr_app_estados_candidato_orden_servicio.estado_orden_servicio_id')
            ->join('usr_app_estado_candidato_servicio as estcan', 'estcan.id', 'usr_app_estados_candidato_orden_servicio.estado_candidato_servicio_id')
            ->select(
                'usr_app_estados_candidato_orden_servicio.id',
                'estcan.nombre  as estadocandidato',
                'est.nombre as estadoseiya',

            )
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function filtro($cadena, $cantidad = null)
    {
        if ($cantidad == null) {
            $cantidad = 10;
        }
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];

        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];
        $query = EstadoCandidatoServioOrdenServicioModel::join('usr_app_estados_ingreso as est', 'est.id', 'usr_app_estados_candidato_orden_servicio.estado_orden_servicio_id')
            ->join('usr_app_estado_candidato_servicio as estcan', 'estcan.id', 'usr_app_estados_candidato_orden_servicio.estado_candidato_servicio_id')
            ->select(
                'usr_app_estados_candidato_orden_servicio.id',
                'estcan.nombre  as estadocandidato',
                'est.nombre as estadoseiya',

            )
            ->orderby('usr_app_estados_candidato_orden_servicio.id');
        $numElementos = count($campo);

        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];
            $esUsuario = $campoActual === 'usuario';

            $prefijoCampo = '';
            if ($campoActual === 'estadocandidato') {
                $prefijoCampo = 'estcan.';
                $campoActual = 'nombre';
            } elseif ($campoActual === 'estadoseiya') {
                $prefijoCampo = 'est.';
                $campoActual = 'nombre';
            }


            switch ($operadorActual) {
                case 'Igual a':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    break;
                case 'Contiene':
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    break;
            }
        }
        $resultados = $query->paginate($cantidad);

        return $resultados;
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
            $estados = $request->all();
            foreach ($estados[0] as  $estados_solicitud_servicio) {
                foreach ($estados[1] as  $estados_orden_servicio) {
                    $result = new EstadoCandidatoServioOrdenServicioModel;
                    $result->estado_candidato_servicio_id = $estados_solicitud_servicio['id'];
                    $result->estado_orden_servicio_id = $estados_orden_servicio['id'];
                    $result->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'ya existe uno de los estados ingresados.']);
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
    public function update(Request $request, $id) {}

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = EstadoCandidatoServioOrdenServicioModel::find($request->id[$i]);
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
        try {
            $result = EstadoCandidatoServioOrdenServicioModel::find($id);
            $result->delete();
            return response()->json(["status" => "success", "message" => "Registro eliminado exitosamente."]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => "Error al eliminar el registro."]);
        }
    }
}
