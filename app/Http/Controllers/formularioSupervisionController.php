<?php

namespace App\Http\Controllers;

use App\Models\FormularioSupervision;
use App\Models\ConceptoFormularioSup;
use App\Models\ImagenObservacion;
use App\Models\Municipios;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class formularioSupervisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = FormularioSupervision::all();
        return response()->json($result);
    }

    public function formById($id){
        $formulario = FormularioSupervision::join('cxc_cliente','cxc_cliente.cod_cli','=','usr_app_formulario_supervision.cliente_id')
        ->select(
            'usr_app_formulario_supervision.id',
            'usr_app_formulario_supervision.fecha_hora',
            'usr_app_formulario_supervision.supervisor_id',
            'usr_app_formulario_supervision.persona_contactada',
            'usr_app_formulario_supervision.direccion',
            'usr_app_formulario_supervision.municipio',
            'usr_app_formulario_supervision.firma_supervisor',
            'usr_app_formulario_supervision.firma_persona_contactada',
            'cxc_cliente.cod_cli',
            'cxc_cliente.nom_cli as nombre_cliente'
        )
        ->where('usr_app_formulario_supervision.id','=',$id)
        ->get();

        $conceptos = ConceptoFormularioSup::select(
            'concepto_id',
            'estado_concepto_id',
        )
        ->where('formulario_id','=',$id)
        ->get();

        $observaciones = ImagenObservacion::select(
            'imagen_observacion',
            'observacion',
        )
        ->where('formulario_id','=',$id)
        ->get();

        $supervisor = User::select( 
            'nombres',
            'apellidos',
        )
        ->where('id','=',$formulario[0]->supervisor_id)
        ->get();

        $ubicacion = Municipios::join('usr_app_departamentos as dep', 'dep.id', '=', 'usr_app_municipios.departamento_id')
        ->join('usr_app_paises as pais', 'pais.id', '=', 'dep.pais_id')
        ->select(
            'usr_app_municipios.id as municipio_id',
            'usr_app_municipios.nombre as municipio',
            'dep.id as departamento_id',
            'dep.nombre as departamento',
            'pais.id as pais_id',
            'pais.nombre as pais',
        )
        ->where('usr_app_municipios.id','=',$formulario[0]->municipio)
        ->get();
        
        $formulario[0]['supervisor'] = $supervisor[0]->nombres.' '. $supervisor[0]->apellidos;
        $formulario[0]['municipio_id'] = $ubicacion[0]->municipio_id;
        $formulario[0]['municipio'] = $ubicacion[0]->municipio;
        $formulario[0]['departamento_id'] = $ubicacion[0]->departamento_id;
        $formulario[0]['departamento'] = $ubicacion[0]->departamento;
        $formulario[0]['pais_id'] = $ubicacion[0]->pais_id;
        $formulario[0]['pais'] = $ubicacion[0]->pais;
       

        $formulario[0]['conceptos'] = $conceptos;
        $formulario[0]['observaciones'] = $observaciones;
        // return response()->json($conceptos);
        return response()->json($formulario[0]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $formulario = new FormularioSupervision;
            $formulario->fecha_hora = $request->fecha_hora;
            $formulario->supervisor_id = $request->supervisor;
            $formulario->persona_contactada = $request->persona_contactada;
            $formulario->direccion = $request->direccion;
            $formulario->municipio = $request->ciudad;
            $formulario->cliente_id = $request->cliente;

            if ($request->hasFile('firma_supervisor')) {

                $nombreArchivoOriginal = $request->file('firma_supervisor')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('firma_supervisor')->move($carpetaDestino, $nuevoNombre);
                $formulario->firma_supervisor = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            if ($request->hasFile('firma_persona_contactada')) {

                $nombreArchivoOriginal = $request->file('firma_persona_contactada')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('firma_persona_contactada')->move($carpetaDestino, $nuevoNombre);
                $formulario->firma_persona_contactada = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            $formulario->save();

            foreach ($request->concepto_estado as $item) {
                $conceptos = new ConceptoFormularioSup;
                $conceptos->concepto_id = explode('*', $item)[0];
                $conceptos->estado_concepto_id = explode('*', $item)[1];
                $conceptos->formulario_id = $formulario->id;
                $conceptos->save();
            }

            foreach ($request->imagen as $item) {
                for ($i = 0; $i < count($item); $i++) {
                    if ($i > 0) {
                        $imagen_observacion = new ImagenObservacion;
                        $imagen_observacion->observacion = $item[0];
                        $imagen_observacion->formulario_id = $formulario->id;

                        $nombreArchivoOriginal = $item[$i]->getClientOriginalName();
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                        $carpetaDestino = './upload/';
                        $item[$i]->move($carpetaDestino, $nuevoNombre);
                        $imagen_observacion->imagen_observacion = ltrim($carpetaDestino, '.') . $nuevoNombre;
                        $imagen_observacion->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Formulario guardado exitosamente']);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
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
