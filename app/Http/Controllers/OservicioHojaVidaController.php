<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use Illuminate\Http\Request;
use App\Models\OservicioHojaVida;
use App\Models\OservicioCargo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class OservicioHojaVidaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = OservicioHojaVida::select()
            ->get();
        return response()->json($result);
    }

    public function envioHojaVida($cargos, $archivos)
    {
        $nombre_cargo = '';
        $archivo = null;
        $mensaje = '';
        // $hojas_vida = [];
        foreach ($cargos['cargo'] as $items) {
            foreach ($items as $item) {
                if (is_string($item)) {
                    $nombre_cargo .= $item . ' ';
                } elseif ($item != null) {
                    $archivo .= '<br>' . $item->getClientOriginalName();
                    // array_push($hojas_vida, $item->getClientOriginalName());
                }
            }
            if ($archivo != null) {
                $mensaje .= '<br>Para el cargo ' .  $nombre_cargo . ' se adjuntaron las siguientes hojas de vida ' . $archivo;
            }
            $nombre_cargo = null;
            $archivo = null;
        }
        $correo = null;
        $correo['subject'] = 'envio correo';
        // $correo['subject'] = $texto_asunto;
        $correo['body'] = $mensaje;
        $correo['orden_servicio'] = $archivos;
        // $correo['to'] = $correo_cliente;
        $correo['to'] = 'andres.duque01@gmail.com';
        $correo['cc'] = '';
        $correo['cco'] = '';

        $EnvioCorreoController = new EnvioCorreoController();
        $request = Request::createFromBase(new Request($correo));
        $result = $EnvioCorreoController->sendEmail($request);
        return $result;
    }

    public function HojaVidaChar($anio)
    {
        $registrosPorMes = DB::table('usr_app_oservicio_envio_hojasvida')
            ->select(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\')) as mes'), DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', $anio)
            ->groupBy(DB::raw('MONTH(FORMAT(created_at, \'yyyy-MM-dd\'))'))
            ->pluck('total', 'mes')
            ->all();

        // Inicializar un array con 12 posiciones, todas con valor 0
        $registrosPorMesArray = array_fill(1, 12, 0);

        // Actualizar las posiciones del array con los valores obtenidos de la consulta
        foreach ($registrosPorMes as $mes => $cantidad) {
            $registrosPorMesArray[$mes] = $cantidad;
        }
        return response()->json($registrosPorMesArray);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $archivos = [];
        $cliente = new OservicioClienteController;
        try {
            $cliente_id = $cliente->getIdCliente($id);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Cliente no registrado']);
        }
        DB::beginTransaction();
        $hojas_vida = $request->all();
        try {
            $cargo = null;
            $registros = 0;
            foreach ($hojas_vida['cargo'] as $items) {
                foreach ($items as $item) {
                    if (is_string($item)) {
                        $cargo = $item;
                        OservicioCargo::where('nombre', $item)
                            ->update(['estado_cargo_id' => 2]);
                    } elseif ($item != null) {
                        array_push($archivos,$item);
                        $registros++;
                        $result = new OservicioHojaVida;
                        $result->nombre_cargo = $cargo;
                        $result->cliente_id = $cliente_id->id;
                        $result->fecha_hora_envio = Carbon::now();
                        $result->datos_solicitante = $cliente_id->datos_solicitante;

                        $nombreArchivoOriginal = $item->getClientOriginalName();
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                        $carpetaDestino = './upload/hojas_vida/' . $id . '/';
                        $item->move($carpetaDestino, $nuevoNombre);
                        $item->ruta_documento = ltrim($carpetaDestino, '.') . $nuevoNombre;
                        $result->ruta_documento = $item->ruta_documento;
                        $result->save();
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'success', 'message' => 'Error al enviar hojas de vida']);
        }
        // return $this->envioHojaVida($hojas_vida, $archivos);
        if ($registros == 0) {
            return response()->json(['status' => 'error', 'message' => 'No se adjuntaron hojas de vida']);
        }
        DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Hojas de vida enviadas de manera exitosa']);
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
        $result = OservicioHojaVida::find($id);
        $result->cliente_id = $request->cliente_id;
        $result->nombre_cargo = $request->nombre_cargo;
        $result->fecha_hora_envio = $request->fecha_hora_envio;
        $result->ruta_documento = $request->ruta_documento;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al actualizar registro']);
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
        $result = OservicioHojaVida::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Error al eliminar registro']);
        }
    }
}
