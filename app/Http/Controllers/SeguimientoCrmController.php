<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use App\Models\SeguimientoCrm;
use App\Models\SeguimientoCrmPendiente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Evidencia;
use App\Models\TemasVisitaCrm;
use App\Models\CompromisosVisitaCrm;
use App\Models\AsistenciaVisitaCrm;
use TCPDF;
use App\Exports\CrmExport;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use App\Models\AtencionInteraccion;

class SeguimientoCrmController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = SeguimientoCrm::join('usr_app_sedes_saitemp as sede', 'sede.id', 'usr_app_seguimiento_crm.sede_id')
            ->join('usr_app_procesos as proces', 'proces.id', 'usr_app_seguimiento_crm.proceso_id')
            ->join('usr_app_atencion_interacion as inter', 'inter.id', 'usr_app_seguimiento_crm.tipo_atencion_id')
            ->join('usr_app_estado_cierre_crm as cierre', 'cierre.id', 'usr_app_seguimiento_crm.estado_id')
            ->join('usr_app_pqrsf_crm as pqrsf', 'pqrsf.id', 'usr_app_seguimiento_crm.pqrsf_id')
            ->join('usr_app_solicitante_crm as soli', 'soli.id', 'usr_app_seguimiento_crm.solicitante_id')
            ->select(
                'usr_app_seguimiento_crm.id',
                'usr_app_seguimiento_crm.numero_radicado',
                'sede.nombre as sede',
                'proces.nombre as proceso',
                'soli.nombre as solicitante',
                'usr_app_seguimiento_crm.nombre_contacto',
                'inter.nombre as iteraccion',
                'pqrsf.nombre as pqrsf',
                'usr_app_seguimiento_crm.telefono',
                'usr_app_seguimiento_crm.correo',
                'cierre.nombre as estado',
            )
            ->orderby('usr_app_seguimiento_crm.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }


    public function byid($id)
    {
        $result = SeguimientoCrm::join('usr_app_sedes_saitemp as sede', 'sede.id', 'usr_app_seguimiento_crm.sede_id')
            ->join('usr_app_procesos as proces', 'proces.id', 'usr_app_seguimiento_crm.proceso_id')
            ->join('usr_app_atencion_interacion as inter', 'inter.id', 'usr_app_seguimiento_crm.tipo_atencion_id')
            ->join('usr_app_estado_cierre_crm as cierre', 'cierre.id', 'usr_app_seguimiento_crm.estado_id')
            ->join('usr_app_solicitante_crm as soli', 'soli.id', 'usr_app_seguimiento_crm.solicitante_id')
            ->join('usr_app_pqrsf_crm as pqrsf', 'pqrsf.id', 'usr_app_seguimiento_crm.pqrsf_id')
            ->join('usr_app_usuarios as usuario_responsable', 'usuario_responsable.id', 'usr_app_seguimiento_crm.responsable_id')
            ->where('usr_app_seguimiento_crm.id', '=', $id)
            ->select(
                'usuario_responsable.usuario as responsable_email',
                'usr_app_seguimiento_crm.id',
                'usr_app_seguimiento_crm.numero_radicado',
                'usr_app_seguimiento_crm.created_at',
                'usr_app_seguimiento_crm.fecha_cerrado',
                'sede.nombre as sede',
                'usr_app_seguimiento_crm.sede_id',
                'proces.nombre as proceso',
                'usr_app_seguimiento_crm.proceso_id',
                'soli.nombre as solicitante',
                'usr_app_seguimiento_crm.solicitante_id',
                'inter.nombre as iteraccion',
                'usr_app_seguimiento_crm.tipo_atencion_id',
                'usr_app_seguimiento_crm.nombre_contacto',
                'usr_app_seguimiento_crm.telefono',
                'usr_app_seguimiento_crm.correo',
                'cierre.nombre as estado',
                'usr_app_seguimiento_crm.estado_id',
                'usr_app_seguimiento_crm.observacion',
                'usr_app_seguimiento_crm.nit_documento',
                'usr_app_seguimiento_crm.pqrsf_id',
                'pqrsf.nombre as pqrsf',
                'usr_app_seguimiento_crm.creacion_pqrsf',
                'usr_app_seguimiento_crm.cierre_pqrsf',
                'usr_app_seguimiento_crm.responsable',
                'usr_app_seguimiento_crm.hora_inicio',
                'usr_app_seguimiento_crm.hora_cierre',
                'usr_app_seguimiento_crm.alcance',
                'usr_app_seguimiento_crm.objetivo',
                'usr_app_seguimiento_crm.cargo_visitado',
                'usr_app_seguimiento_crm.visitado',
                'usr_app_seguimiento_crm.cargo_visitante',
                'usr_app_seguimiento_crm.visitante',
                'usr_app_seguimiento_crm.latitud',
                'usr_app_seguimiento_crm.longitud',
                'usr_app_seguimiento_crm.responsable_id',
                'usr_app_seguimiento_crm.observacion2'
            )
            ->first();
        $result->observacion = $result->observacion . $result->observacion2;

        $evidencias = Evidencia::where('registro_id', $id)->get();
        $evidencias->transform(function ($item) {
            $item->edit = false;
            return $item;
        });
        $result["Evidencias"] = $evidencias;
        $temasPrincipales =  TemasVisitaCrm::where('registro_id', $id)->get();
        $temasPrincipales->transform(function ($item) {
            $item->edit = false;
            return $item;
        });
        $result["temasPrincipales"] = $temasPrincipales;
        $compromisos =  CompromisosVisitaCrm::join('usr_app_usuarios as usuario', 'usuario.id', 'usr_app_compromisos_generales.responsable_id')
            ->where('registro_id', $id)
            ->select(
                'usuario.usuario as email',
                'usr_app_compromisos_generales.titulo',
                'usr_app_compromisos_generales.id',
                'usr_app_compromisos_generales.descripcion',
                'usr_app_compromisos_generales.estado_cierre_id',
                'usr_app_compromisos_generales.fecha_cierre',
                'usr_app_compromisos_generales.responsable',
                'usr_app_compromisos_generales.observacion',
                'usr_app_compromisos_generales.responsable_id',
            )
            ->get();

        $result["compromisos"] = $compromisos;
        $asistencias =  AsistenciaVisitaCrm::where('registro_id', $id)->get();
        $asistencias->transform(function ($item) {
            $item->edit = false;
            return $item;
        });
        $result["asistencias"] = $asistencias;


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

            $query = SeguimientoCrm::join('usr_app_sedes_saitemp as sede', 'sede.id', 'usr_app_seguimiento_crm.sede_id')
                ->join('usr_app_procesos as proces', 'proces.id', 'usr_app_seguimiento_crm.proceso_id')
                ->join('usr_app_atencion_interacion as inter', 'inter.id', 'usr_app_seguimiento_crm.tipo_atencion_id')
                ->join('usr_app_estado_cierre_crm as cierre', 'cierre.id', 'usr_app_seguimiento_crm.estado_id')
                ->join('usr_app_pqrsf_crm as pqrsf', 'pqrsf.id', 'usr_app_seguimiento_crm.pqrsf_id')
                ->join('usr_app_solicitante_crm as soli', 'soli.id', 'usr_app_seguimiento_crm.solicitante_id')
                ->select(
                    'usr_app_seguimiento_crm.id',
                    'usr_app_seguimiento_crm.numero_radicado',
                    'sede.nombre as sede',
                    'proces.nombre as proceso',
                    'soli.nombre as solicitante',
                    'usr_app_seguimiento_crm.nombre_contacto',
                    'inter.nombre as iteraccion',
                    'pqrsf.nombre as pqrsf',
                    'usr_app_seguimiento_crm.telefono',
                    'usr_app_seguimiento_crm.correo',
                    'cierre.nombre as estado',
                )
                ->orderby('id', 'DESC');


            switch ($operador) {
                case 'Contiene':
                    if ($campo == "sede") {
                        $query->where('sede.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "proceso") {
                        $query->where('proces.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "iteraccion") {
                        $query->where('inter.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "pqrsf") {
                        $query->where('pqrsf.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "estado") {
                        $query->where('cierre.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "solicitante") {
                        $query->where('soli.nombre', 'like', '%' . $valor . '%');
                    } else {
                        $query->where($campo, 'like', '%' . $valor . '%');
                    }
                    break;
                case 'Igual a':
                    if ($campo == "sede") {
                        $query->where('sede.nombre', '=', $valor);
                    } else if ($campo == "proceso") {
                        $query->where('proces.nombre', '=', $valor);
                    } else if ($campo == "iteraccion") {
                        $query->where('inter.nombre', '=', $valor);
                    } else if ($campo == "pqrsf") {
                        $query->where('pqrsf.nombre', '=', $valor);
                    } else if ($campo == "estado") {
                        $query->where('cierre.nombre', '=', $valor);
                    } else if ($campo == "solicitante") {
                        $query->where('soli.nombre', '=', $valor);
                    } else {
                        $query->where($campo, '=', $valor);
                    }
                    break;
                case 'Igual a fecha':
                    $query->whereDate('usr_app_clientes.' . $campo, '=', $valor);
                    break;
                case 'Entre':
                    $query->whereDate('usr_app_clientes.' . $campo, '>=', $valor)
                        ->whereDate('usr_app_clientes.' . $campo, '<=', $valor2);
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function pendientes(Request $request)
    {
        $user = auth()->user();
        $lista = $request->all();
        foreach ($lista as $item) {
            $existeIngreso = SeguimientoCrmPendiente::where('registro_crm_id', $item)->where('usuario_id', $user->id)->first();

            if (!$existeIngreso) {
                $result = new SeguimientoCrmPendiente;
                $result->registro_crm_id = $item;
                $result->usuario_id = $user->id;
                $result->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => 'Tareas pendientes agregadas exitosamente.']);
    }

    public function pendientes2($cantidad)
    {
        $user = auth()->user();
        $result = SeguimientoCrm::join('usr_app_sedes_saitemp as sede', 'sede.id', 'usr_app_seguimiento_crm.sede_id')
            ->join('usr_app_procesos as proces', 'proces.id', 'usr_app_seguimiento_crm.proceso_id')
            ->join('usr_app_atencion_interacion as inter', 'inter.id', 'usr_app_seguimiento_crm.tipo_atencion_id')
            ->join('usr_app_estado_cierre_crm as cierre', 'cierre.id', 'usr_app_seguimiento_crm.estado_id')
            ->join('usr_app_solicitante_crm as soli', 'soli.id', 'usr_app_seguimiento_crm.solicitante_id')
            ->join('usr_app_seguimiento_crm_pendientes as pen', 'pen.registro_crm_id', 'usr_app_seguimiento_crm.id')
            ->where('pen.usuario_id', '=', $user->id)
            ->select(
                'usr_app_seguimiento_crm.id',
                'usr_app_seguimiento_crm.numero_radicado',
                'sede.nombre as sede',
                'proces.nombre as proceso',
                'soli.nombre as solicitante',
                'inter.nombre as iteraccion',
                'usr_app_seguimiento_crm.nombre_contacto',
                'usr_app_seguimiento_crm.telefono',
                'usr_app_seguimiento_crm.correo',
                'cierre.nombre as estado',
            )
            ->orderby('usr_app_seguimiento_crm.id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function createandroid(Request $request)
    {

        $datosFormulario = $request->all();
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $fechaHoraActual = Carbon::now();
            $result = new SeguimientoCrm; //::find("94");
            $result->sede_id = $request->sede_id;
            $result->proceso_id = $request->proceso_id;
            $result->solicitante_id = $request->solicitante_id;
            $result->nombre_contacto = $request->razon_social;
            $result->tipo_atencion_id = $request->medio_atencion_id;
            $result->telefono = $request->telefono;
            $result->correo = $request->correo;
            $result->estado_id = $request->estado_id;
            $result->nit_documento = $request->nit;
            if ($request->estado_id == 3) {
                $result->cierre_pqrsf = $request->cierra_radicado;
                $result->fecha_cerrado = $fechaHoraActual->format('d-m-Y H:i:s');
                $result->usuario_guarda_cierre = $user->nombres . " " . $user->apellidos;
            }
            $result->creacion_pqrsf = $user->nombres . ' ' . $user->apellidos;
            $result->responsable = $request->responsable;
            $result->pqrsf_id = $request->pqrsf_id;
            $result->responsable_id = $request->responsable_id;
            $result->visitante = $request->visitante;
            $result->visitado = $request->visitado;
            $result->hora_inicio = $request->hora_incio; //// falta este campo en el front android
            $result->hora_cierre = $horaActual = Carbon::now()->format('H:i'); //// falta este campo en el front android
            $result->cargo_visitante = $request->cargo_visitante;
            $result->cargo_visitado = $request->cargo_visitado;
            $result->objetivo = $request->objetivo;
            $result->alcance = $request->alcance;
            $result->longitud = $request->longitud;
            $result->latitud = $request->latitud;
            $result->formulario_web = 0;
            if ($request->observaciones != "") {
                $observacionFragmentada = str_split($request->observaciones, 4000);
                $result->observacion = $observacionFragmentada[0];
                if (isset($observacionFragmentada[1])) {
                    $result->observacion2 = $observacionFragmentada[1];
                }
            }
            if ($request->estado_id == 3) {
                $fechaHoraActual = Carbon::now();
                $result->fecha_cerrado = $fechaHoraActual->format('d-m-Y H:i:s');
            }

            $result->save();

            if (isset($datosFormulario['evidencias'])) {


                foreach ($datosFormulario['evidencias'] as $item) {
                    // Crea una nueva instancia del modelo Evidencia
                    $evidencia = new Evidencia;

                    // Decodifica el archivo Base64
                    $archivoBase64 = $item['path'];
                    $nombre_archivo =  $item['nombre'];
                    $archivoBinario = base64_decode($archivoBase64);

                    // Verifica si la decodificación fue exitosa
                    if ($archivoBinario === false) {
                        throw new \Exception("Error al decodificar el archivo base64.");
                    }

                    // Guarda el archivo temporalmente
                    $rutaTemp = storage_path('app/temp/');
                    if (!file_exists($rutaTemp)) {
                        mkdir($rutaTemp, 0777, true);
                    }
                    $tempFilePath = $rutaTemp . $nombre_archivo;
                    file_put_contents($tempFilePath, $archivoBinario);

                    // Llama a la función `comprimirArchivos` pasando el archivo temporal como argumento
                    try {
                        $evidencia->archivo = $this->comprimirArchivos($result->numero_radicado, new \Illuminate\Http\File($tempFilePath), './upload/evidenciasCrm2/', $nombre_archivo);
                        $evidencia->descripcion = $item['descripcion'];
                        $evidencia->registro_id = $result->id;
                        $evidencia->save();
                    } catch (\Exception $e) {

                        continue;
                    }

                    // Elimina el archivo temporal después de procesarlo
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                }
            }

            // Definir la estructura de correos en un array
            $correosResponsables = [
                'correos' => [
                    [
                        'correo' => $request->correo_responsable,
                        'observacion' => '',
                        'compromiso' => false,
                    ],
                    [
                        'correo' => $request->correo,
                        'observacion' => '',
                        'compromiso' => false,
                    ],
                ],
            ];

            $contador = 1;
            if (isset($datosFormulario['asistentes'])) {
                foreach ($datosFormulario['asistentes'] as $item) {
                    // Crea una nueva instancia del modelo Evidencia
                    $asistente = new AsistenciaVisitaCrm;

                    // Decodifica el archivo Base64
                    $archivoBase64 = $item['firma'];
                    $nombre_archivo = 'firma.png';
                    $archivoBinario = base64_decode($archivoBase64);

                    // Verifica si la decodificación fue exitosa
                    if ($archivoBinario === false) {
                        throw new \Exception("Error al decodificar el archivo base64.");
                    }

                    // Define la carpeta de destino en la carpeta public/upload/evidenciasCrm2
                    $carpetaDestino = public_path('upload/evidenciasCrm2/');
                    if (!file_exists($carpetaDestino)) {
                        mkdir($carpetaDestino, 0777, true); // Crea el directorio si no existe con permisos 0777
                    }

                    // Genera un nombre único para el archivo
                    $nombreArchivo = Carbon::now()->timestamp . '_' . $contador . '.png';
                    $rutaCompleta = $carpetaDestino . $nombreArchivo;

                    // Guarda el archivo directamente en la carpeta public/upload/evidenciasCrm2
                    if (file_put_contents($rutaCompleta, $archivoBinario)) {
                        // Guarda solo el path relativo en la base de datos para el acceso desde el frontend
                        $asistente->firma = 'upload/evidenciasCrm2/' . $nombreArchivo;
                        $asistente->nombre = $item['nombre'];
                        $asistente->correo = $item['correo'];
                        $asistente->cargo = $item['cargo'];
                        $asistente->registro_id = $result->id;
                        $asistente->save();

                        $correosResponsables['correos'][] = [
                            'correo' => $item['correo'],
                            'observacion' => '',
                            'compromiso' => false,
                        ];
                    } else {
                        throw new \Exception("Error al guardar el archivo en la carpeta public/upload/evidenciasCrm2.");
                    }
                    $contador++;
                }
            }

            foreach ($datosFormulario['compromisos'] as $item) {

                $compromiso = new CompromisosVisitaCrm;
                $compromiso->titulo = "titulo 1";
                $compromiso->descripcion = $item['compromiso'];
                $compromiso->registro_id = $result->id;
                $compromiso->estado_cierre_id = $item['estado_id'];
                $compromiso->responsable = $item['responsable'];
                $compromiso->observacion = $item['observacion'];
                $compromiso->responsable_id = $item['responsable_id'];
                $compromiso->save();

                $usuario = User::find($item['responsable_id']);
                $correosResponsables['correos'][] = [
                    'correo' => $usuario->usuario,
                    'observacion' => $item['observacion'],
                    'compromiso' => true,
                ];
            }
            $temaPrincipal = new TemasVisitaCrm;
            $temaPrincipal->titulo = "";
            $temaPrincipal->descripcion = $request->tema;
            $temaPrincipal->registro_id = $result->id;
            $temaPrincipal->save();

            DB::commit();


            return response()->json(["correos" => $correosResponsables, "formulario_id" => $result->id]);
        } catch (\Exception $e) {
            DB::rollback();
            // return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
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
            $fechaHoraActual = Carbon::now();
            $user = auth()->user();
            $result = new SeguimientoCrm;
            $result->sede_id = $request->sede_id;
            $result->proceso_id = $request->proceso_id;
            $result->solicitante_id = $request->solicitante_id;
            $result->nombre_contacto = $request->nombre_contacto;
            $result->tipo_atencion_id = $request->tipo_atencion_id;
            $result->telefono = $request->telefono;
            $result->correo = $request->correo;
            $result->estado_id = $request->estado_id;
            /*     $result->observacion = $request->observacion; */
            $result->nit_documento = $request->nit_documento;
            $result->pqrsf_id = $request->pqrsf_id;
            $result->creacion_pqrsf = $user->nombres . ' ' . $user->apellidos;
            $result->responsable = $request->responsable;
            $result->responsable_id = $request->responsable_id;

            //campos agregados para el formulario de visita
            $result->visitante = $request->visitante;
            $result->visitado = $request->visitado;
            $result->hora_inicio = $request->hora_inicio;
            $result->hora_cierre = $request->hora_cierre;
            $result->cargo_visitante = $request->cargo_visitante;
            $result->cargo_visitado = $request->cargo_atendio;
            $result->objetivo = $request->objetivo_visita;
            $result->alcance = $request->alcance_visita;
            $result->latitud = $request->latitud;
            $result->longitud = $request->longitud;

            if ($request->observacion != "") {
                $observacionFragmentada = str_split($request->observacion, 4000);
                $result->observacion = $observacionFragmentada[0];
                if (isset($observacionFragmentada[1])) {
                    $result->observacion2 = $observacionFragmentada[1];
                }
            }


            if ($request->estado_id == 3) {
                $result->cierre_pqrsf = $request->cierre_pqrsf;
                $result->fecha_cerrado = $fechaHoraActual->format('d-m-Y H:i:s');
                $result->usuario_guarda_cierre = $user->nombres . ' ' . $user->apellidos;
            }

            $result->save();
            /*    $manager = new ImageManager(new Driver());
            $zipNombre = $result->numero_radicado . '.zip';
            $zipGeneral = public_path('./upload/evidenciasCrm/' . $zipNombre);
            $zipCoincidencia = glob($zipGeneral); */
            foreach ($request->imagen as $item) {
                for ($i = 0; $i < count($item); $i++) {
                    if ($i > 0) {
                        $evidencia = new Evidencia;
                        $evidencia->descripcion = $item[0] ? $item[0] : "";
                        $evidencia->registro_id = $result->id;
                        /*  $nombreArchivoOriginal = $item[$i]->getClientOriginalName();
                        $nombreSinExtension = pathinfo($nombreArchivoOriginal, PATHINFO_FILENAME);
                        $extension = pathinfo($nombreArchivoOriginal, PATHINFO_EXTENSION);
                        $nombreLimpio = preg_replace('/[.\s]+/', '_', $nombreSinExtension) . '.' . $extension;
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreLimpio;
                        $carpetaDestino = './upload/evidenciasCrm/';
                        $zipPath = $carpetaDestino . $zipNombre;
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
                            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                $image = $manager->read($item[$i]->getPathname());
                                $image->resizeDown(800, 600, function ($constraint) {
                                    $constraint->aspectRatio();
                                })->save($carpetaDestino . $nuevoNombre, 70);
                            } elseif ($extension === 'pdf') {
                                $pdf = new Fpdi();
                                $pageCount = $pdf->setSourceFile($item[$i]->getPathname());
                                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                                    $pdf->AddPage();
                                    $templateId = $pdf->importPage($pageNo);
                                    $pdf->useTemplate($templateId);
                                }
                                $pdf->Output($carpetaDestino . $nuevoNombre, 'F');
                            }
                            if (count($zipCoincidencia) > 0) {
                                $zip = new ZipArchive;
                                $res = $zip->open($zipGeneral);
                                if ($res === true) {
                                    $zip->addFile($carpetaDestino . $nuevoNombre, $nuevoNombre);
                                    $zip->close();
                                } else {
                                    throw new \Exception('No se pudo acceder al ZIP');
                                }
                                $nuevoNombre = $zipNombre . $nuevoNombre;
                            } else {
                                $zip = new ZipArchive();
                                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                                    $zip->addFile($carpetaDestino . $nuevoNombre, $nuevoNombre);
                                    $zip->close();
                                }
                            }
                            if (file_exists($carpetaDestino . $nuevoNombre)) {
                                unlink($carpetaDestino . $nuevoNombre);
                            }
                        } else {
                            if (count($zipCoincidencia) > 0) {
                                $zip = new ZipArchive;
                                $res = $zip->open($zipGeneral);
                                if ($res === true) {
                                    $zip->addFile($item[$i]->getPathname(), $nuevoNombre);
                                    $zip->close();
                                } else {
                                    throw new \Exception('No se pudo acceder al ZIP');
                                }
                                $nuevoNombre = $zipNombre . $nuevoNombre;
                            } else {
                                $zip = new ZipArchive();
                                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                                    $zip->addFile($item[$i]->getPathname(), $nuevoNombre);
                                    $zip->close();
                                }
                            }
                        } */
                        $ruta = './upload/evidenciasCrm/';

                        $evidencia->archivo = $this->comprimirArchivos($result->numero_radicado, $item[$i], $ruta);
                        /*     $evidencia->archivo = ltrim($carpetaDestino, '.') . $nuevoNombre; */
                        $evidencia->save();
                    }
                }
            }
            if ($request->compromisos) {
                $decodeCompromisos = json_decode($request->compromisos, true);
                if (count($decodeCompromisos) > 0) {
                    foreach ($decodeCompromisos as $item) {
                        $compromiso = new CompromisosVisitaCrm;
                        if (isset($item['descripcion']) &&  $item['descripcion'] != "") {
                            $compromiso->titulo = isset($item['titulo']) ? $item['titulo'] : '';
                            $compromiso->descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                            $compromiso->registro_id = $result->id;
                            $compromiso->responsable = isset($item['responsable']) ? $item['responsable'] : '';
                            $compromiso->estado_cierre_id = isset($item['estado_cierre_id']) ? $item['estado_cierre_id'] : '';
                            $compromiso->observacion = isset($item['observacion']) ? $item['observacion'] : '';
                            $compromiso->responsable_id = isset($item['responsable_id']) ? $item['responsable_id'] : '';
                            /*      $fechaCierreFormatted = Carbon::parse($item['fecha_cierre'])->format('d-m-Y H:i:s');
                            $compromiso->fecha_cierre = isset($fechaCierreFormatted) ? $fechaCierreFormatted : ''; */
                            if (isset($item['estado_cierre_id']) && $item['estado_cierre_id'] != "") {
                                $fechaCierreFormatted = $fechaHoraActual->format('d-m-Y H:i:s');;
                                $compromiso->fecha_cierre = isset($fechaCierreFormatted) ? $fechaCierreFormatted : '';
                            }
                            $compromiso->save();
                        }
                    }
                }
            }
            if ($request->temasPrincipales) {
                $decodeTemas = json_decode($request->temasPrincipales, true);
                if (count($decodeTemas) > 0) {
                    foreach ($decodeTemas as $item) {

                        $temaPrincipal = new TemasVisitaCrm;
                        $temaPrincipal->titulo = isset($item['titulo']) ? $item['titulo'] : '';
                        $temaPrincipal->descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                        $temaPrincipal->registro_id = $result->id;
                        $temaPrincipal->save();
                    }
                }
            }

            // Procesar temas principales


            if ($request->asistencia) {
                foreach ($request->asistencia as $item) {
                    for ($i = 0; $i < count($item); $i++) {
                        if ($i > 0) {
                            $asistencia = new AsistenciaVisitaCrm;
                            $decodeFirma = json_decode($item[0], true);

                            $asistencia->nombre = $decodeFirma ? $decodeFirma["nombre"] : "";
                            $asistencia->registro_id = $result->id;
                            $asistencia->cargo = $decodeFirma ? $decodeFirma["cargo"] : "";
                            $asistencia->correo = $decodeFirma ? $decodeFirma["correo"] : "";
                            $nombreArchivoOriginal = $item[$i]->getClientOriginalName();
                            $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;
                            $carpetaDestino = './upload/evidenciasCrm/';
                            $item[$i]->move($carpetaDestino, $nuevoNombre);
                            $asistencia->firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
                            $asistencia->save();
                        }
                    }
                }
            }


            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa', 'id' => $result->id]);
        } catch (\Exception $e) {
            return $e;
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
        $user = auth()->user();
        DB::beginTransaction();
        try {
            $fechaHoraActual = Carbon::now();
            $result = SeguimientoCrm::find($id);
            $result->sede_id = $request->sede_id;
            $result->proceso_id = $request->proceso_id;
            $result->solicitante_id = $request->solicitante_id;
            $result->nombre_contacto = $request->nombre_contacto;
            $result->tipo_atencion_id = $request->tipo_atencion_id;
            $result->telefono = $request->telefono;
            $result->correo = $request->correo;
            $result->estado_id = $request->estado_id;
            $result->nit_documento = $request->nit_documento;
            $result->responsable = $request->responsable;
            $result->pqrsf_id = $request->pqrsf_id;
            $result->responsable_id = $request->responsable_id;

            //campos agregados para el formulario de visita
            $result->visitante = $request->visitante;
            $result->visitado = $request->visitado;
            $result->hora_inicio = $request->hora_inicio;
            $result->hora_cierre = $request->hora_cierre;
            $result->cargo_visitante = $request->cargo_visitante;
            $result->cargo_visitado = $request->cargo_atendio;
            $result->objetivo = $request->objetivo_visita;
            $result->alcance = $request->alcance_visita;

            if ($request->observacion != "") {
                $observacionFragmentada = str_split($request->observacion, 4000);
                $result->observacion = $observacionFragmentada[0];
                if (isset($observacionFragmentada[1])) {
                    $result->observacion2 = $observacionFragmentada[1];
                }
            }

            if ($request->estado_id == 3) {
                $result->cierre_pqrsf = $request->cierre_pqrsf;
                $result->fecha_cerrado = $fechaHoraActual->format('d-m-Y H:i:s');
                $result->usuario_guarda_cierre = $user->nombres . ' ' . $user->apellidos;
            }


            $result->save();
            /*  $manager = new ImageManager(new Driver());
            $zipNombre = $result->numero_radicado . '.zip';
            $zipGeneral = public_path('./upload/evidenciasCrm/' . $zipNombre);
            $zipCoincidencia = glob($zipGeneral); */
            foreach ($request->imagen as $item) {
                for ($i = 0; $i < count($item); $i++) {
                    if ($i > 0) {
                        $evidencia = new Evidencia;
                        $evidencia->descripcion = $item[0] ? $item[0] : "";
                        $evidencia->registro_id = $result->id;
                        /* $nombreArchivoOriginal = $item[$i]->getClientOriginalName();
                        $nombreSinExtension = pathinfo($nombreArchivoOriginal, PATHINFO_FILENAME);
                        $extension = pathinfo($nombreArchivoOriginal, PATHINFO_EXTENSION);
                        $nombreLimpio = preg_replace('/[.\s]+/', '_', $nombreSinExtension) . '.' . $extension;
                        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreLimpio;
                        $carpetaDestino = './upload/evidenciasCrm/';
                        $zipPath = $carpetaDestino . $zipNombre;
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
                            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                $image = $manager->read($item[$i]->getPathname());
                                $image->resizeDown(800, 600, function ($constraint) {
                                    $constraint->aspectRatio();
                                })->save($carpetaDestino . $nuevoNombre, 70);
                            } elseif ($extension === 'pdf') {
                                $pdf = new Fpdi();
                                $pageCount = $pdf->setSourceFile($item[$i]->getPathname());
                                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                                    $pdf->AddPage();
                                    $templateId = $pdf->importPage($pageNo);
                                    $pdf->useTemplate($templateId);
                                }
                                $pdf->Output($carpetaDestino . $nuevoNombre, 'F');
                            }
                            if (count($zipCoincidencia) > 0) {
                                $zip = new ZipArchive;
                                $res = $zip->open($zipGeneral);
                                if ($res === true) {
                                    $zip->addFile($carpetaDestino . $nuevoNombre, $nuevoNombre);
                                    $zip->close();
                                } else {
                                    throw new \Exception('No se pudo acceder al ZIP');
                                }
                            } else {
                                $zip = new ZipArchive();
                                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                                    $zip->addFile($carpetaDestino . $nuevoNombre, $nuevoNombre);
                                    $zip->close();
                                }
                            }
                            if (file_exists($carpetaDestino . $nuevoNombre)) {
                                unlink($carpetaDestino . $nuevoNombre);
                            }
                        } else {
                            if (count($zipCoincidencia) > 0) {
                                $zip = new ZipArchive;
                                $res = $zip->open($zipGeneral);
                                if ($res === true) {
                                    $zip->addFile($item[$i]->getPathname(), $nuevoNombre);
                                    $zip->close();
                                } else {
                                    throw new \Exception('No se pudo acceder al ZIP');
                                }
                            } else {
                                $zip = new ZipArchive();
                                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                                    $zip->addFile($item[$i]->getPathname(), $nuevoNombre);
                                    $zip->close();
                                }
                            }
                        } */
                        $ruta = './upload/evidenciasCrm/';
                        $evidencia->archivo = $this->comprimirArchivos($result->numero_radicado, $item[$i], $ruta);
                        $evidencia->save();
                    }
                }
            }
            if ($request->compromisos) {
                $decodeCompromisos = json_decode($request->compromisos, true);
                $compromisoCant = "";
                if (count($decodeCompromisos) > 0) {
                    foreach ($decodeCompromisos as $item) {
                        if ($item['id'] != "") {
                            $compromiso = CompromisosVisitaCrm::find($item['id']);
                            $compromiso->titulo = isset($item['titulo']) ? $item['titulo'] : '';
                            $compromiso->descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                            $compromiso->registro_id = $result->id;
                            $compromiso->estado_cierre_id = isset($item['estado_cierre_id']) ? $item['estado_cierre_id'] : '';
                            /* $fechaCierreFormatted = Carbon::parse($item['fecha_cierre'])->format('d-m-Y H:i:s'); */
                            $compromiso->responsable = isset($item['responsable']) ? $item['responsable'] : '';
                            $compromiso->observacion = isset($item['observacion']) ? $item['observacion'] : '';
                            /* $compromiso->fecha_cierre = isset($fechaCierreFormatted) ? $fechaCierreFormatted : ''; */
                            $compromiso->responsable_id = isset($item['responsable_id']) ? $item['responsable_id'] : '';
                            if (isset($item['estado_cierre_id']) && $item['estado_cierre_id'] != "") {
                                $fechaCierreFormatted = $fechaHoraActual->format('d-m-Y H:i:s');;
                                $compromiso->fecha_cierre = isset($fechaCierreFormatted) ? $fechaCierreFormatted : '';
                            }
                            $compromiso->save();
                        } else {
                            $compromiso = new CompromisosVisitaCrm;
                            if (isset($item['descripcion']) &&  $item['descripcion'] != "") {
                                $compromiso->titulo = isset($item['titulo']) ? $item['titulo'] : '';
                                $compromiso->descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                                $compromiso->registro_id = $result->id;
                                $compromiso->responsable = isset($item['responsable']) ? $item['responsable'] : '';
                                $compromiso->estado_cierre_id = isset($item['estado_cierre_id']) ? $item['estado_cierre_id'] : '';
                                $compromiso->observacion = isset($item['observacion']) ? $item['observacion'] : '';
                                $compromiso->responsable_id = isset($item['responsable_id']) ? $item['responsable_id'] : '';
                                /*         $fechaCierreFormatted = Carbon::parse($item['fecha_cierre'])->format('d-m-Y H:i:s');
                        $compromiso->fecha_cierre = isset($fechaCierreFormatted) ? $fechaCierreFormatted : ''; */
                                if (isset($item['estado_cierre_id']) && $item['estado_cierre_id'] != "") {
                                    $fechaCierreFormatted = $fechaHoraActual->format('d-m-Y H:i:s');;
                                    $compromiso->fecha_cierre = isset($fechaCierreFormatted) ? $fechaCierreFormatted : '';
                                }
                                $compromiso->save();
                            }
                        }
                    }
                }
            }
            if ($request->temasPrincipales) {
                $decodeTemas = json_decode($request->temasPrincipales, true);
                if (count($decodeTemas) > 0) {
                    foreach ($decodeTemas as $item) {
                        if ($item['id'] != "") {
                            $temaPrincipal = TemasVisitaCrm::find($item['id']);
                            $temaPrincipal->titulo = isset($item['titulo']) ? $item['titulo'] : '';
                            $temaPrincipal->descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                            $temaPrincipal->registro_id = $result->id;
                            $temaPrincipal->save();
                        } else {

                            $temaPrincipal = new TemasVisitaCrm;
                            $temaPrincipal->titulo = isset($item['titulo']) ? $item['titulo'] : '';
                            $temaPrincipal->descripcion = isset($item['descripcion']) ? $item['descripcion'] : '';
                            $temaPrincipal->registro_id = $result->id;
                            $temaPrincipal->save();
                        }
                    }
                }
            }
            if ($request->asistencia) {
                foreach ($request->asistencia as $item) {
                    for ($i = 0; $i < count($item); $i++) {
                        if ($i > 0) {

                            $asistencia = new AsistenciaVisitaCrm;
                            $decodeFirma = json_decode($item[0], true);
                            $asistencia->nombre = $decodeFirma ? $decodeFirma["nombre"] : "";
                            $asistencia->registro_id = $result->id;
                            $asistencia->cargo = $decodeFirma ? $decodeFirma["cargo"] : "";
                            $asistencia->correo = $decodeFirma ? $decodeFirma["correo"] : "";
                            $nombreArchivoOriginal = $item[$i]->getClientOriginalName();
                            $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;
                            $carpetaDestino = './upload/evidenciasCrm/';
                            $item[$i]->move($carpetaDestino, $nuevoNombre);
                            $asistencia->firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
                            $asistencia->save();
                        } else {
                            $asistencia = new AsistenciaVisitaCrm;
                            $decodeFirma = json_decode($item[0], true);
                            if ($decodeFirma["id"] != "") {
                                $asistencia = AsistenciaVisitaCrm::find($decodeFirma['id']);
                                $asistencia->nombre = $decodeFirma ? $decodeFirma["nombre"] : "";
                                $asistencia->cargo = $decodeFirma ? $decodeFirma["cargo"] : "";
                                $asistencia->correo = $decodeFirma ? $decodeFirma["correo"] : "";
                                $asistencia->firma = $asistencia->firma;
                                $asistencia->save();
                            }
                        }
                    }
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa', 'id' => $result->id]);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            $user = auth()->user();
            for ($i = 0; $i < count($request->id); $i++) {
                $result = SeguimientoCrmPendiente::where('registro_crm_id', '=', $request->id[$i])->where('usuario_id', $user->id)->first();
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
        $result = SeguimientoCrm::find($id);
        if (!$result) {
            return response()->json(['message' => 'El radicado no existe.'], 404);
        }
        try {
            $result->delete();
            return response()->json(['message' => 'Radicado eliminado con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el radicado.', 'error' => $e->getMessage()], 500);
        }
    }
    public function eliminararchivo($item, $id)
    {

        $result = Evidencia::where('usr_app_evidencia_crm.registro_id', '=', $item)
            ->where('usr_app_evidencia_crm.id', '=', $id)
            ->first();
        $registro = Evidencia::find($result->id);
        $rutaArchivo = $registro->archivo;
        $pos = strpos($rutaArchivo, '_');
        $nombreZip = substr($rutaArchivo, 0, $pos);
        $extension = pathinfo($nombreZip, PATHINFO_EXTENSION);
        $ultimoCaracter = strlen($rutaArchivo) - 1;
        $nombreArchivo = substr($rutaArchivo, $pos + 1, $ultimoCaracter);
        if ($registro->archivo != null) {
            $rutaZip = base_path('public') . $nombreZip;
            if ($extension === 'zip') {
                $zip = new ZipArchive;
                $res = $zip->open($rutaZip);
            }
            if ($res === true) {
                if ($zip->locateName($nombreArchivo) !== false) {
                    $zip->deleteName($nombreArchivo);
                    $zip->close();
                }
                if ($registro->delete()) {
                    return response()->json(['status' => 'success', 'message' => 'Registro eliminado con Exito']);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Error al eliminar registro']);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'El archivo no se encuentra dentro del ZIP']);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'No se encontró el archivo']);
        }
    }
    public function updateEvidencia(Request $request, $id)
    {
        $result = Evidencia::find($id);
        $result->descripcion = $request->descripcion;
        if ($result->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa', 'id' => $result->id]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro']);
        }
    }


    public function generarPdfCrm(Request $request, $registro_id, $btnId = 1)
    {

        $modulo = 46;
        // Obtener los datos del formulario

        $user = auth()->user();
        $atencionInteracion = "";
        $formulario = $this->byid($registro_id)->getData();
        if (isset($formulario->tipo_atencion_id)) {

            $atencionInteracion = DB::table('usr_app_atencion_interacion')
                ->where('id', $formulario->tipo_atencion_id)
                ->first();
        } else {
            // Manejar el caso donde tipo_atencion_id no exista
            $atencionInteracion = null;
        }
        $pdf = new \TCPDF();

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Saitemp');
        $pdf->SetTitle('Reporte CRM');
        $pdf->SetSubject('Detalles del CRM');
        $pdf->SetKeywords('TCPDF, PDF, CRM, reporte');
        $horaInicioFormateada = '';
        $horaCierreFormateada = '';
        $fechaCreacionFromated = '';
        if (!empty($formulario->created_at)) {
            $fechaCreacionFromated = Carbon::parse($formulario->created_at)->format('d-m-Y H:i:s');
        }

        if (!empty($formulario->hora_inicio)) {
            $horaInicioFormateada = Carbon::parse($formulario->hora_inicio)->format('H:i');
        }
        if (!empty($formulario->hora_cierre)) {
            $horaCierreFormateada = Carbon::parse($formulario->hora_cierre)->format('H:i');
        }
        $url = public_path('upload/logo1.png');
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(15, 30, 15);
        $pdf->SetAutoPageBreak(true, 40);
        $pdf->AddPage();
        $url2 = public_path('/upload/MEMBRETE.png');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->Image($url2, 0, 0, $pdf->getPageWidth() + 0.5, $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
        $pdf->SetMargins(15, 30, 15);
        $pdf->SetAutoPageBreak(true, 40);
        // Construir el contenido del PDF con las claves y valores del formulario y aplicando estilos

        $parrafo = str_replace("\n", "<br>", $formulario->observacion);

        $pdf->Ln(20);
        $html = '
                <style>
                    .divInit{
                    height: 200px;
                    color:transparent;
                    }
                    h1 {
                        color: #043c69;
                        text-align: center;
                        font-size: 16px;
                        margin-bottom: 20px;
                    }
                    .info {
                        font-size: 12px;
                        color: #000;
                        line-height: 1.5;
                        margin-bottom: 8px;
                        font-weight: normal;
                        margin-top:20px;
                        
                        
                    }
                    .data-label {
                        font-weight: bold;
                        color: #043c69;
                    }
                    .section-title {
                        font-size: 16px;
                        color: #043c69;
                        margin-top: 15px;
                        margin-bottom: 10px;
                    }
                    table {
                        width: 100%;
                        higth:100%
                        border-collapse: collapse;
                    }
                    td {
                        border: 1px solid #ddd;
                        font-size: 12px;
                    }
                    .signature {
                        width: 100px;
                        height: 50px;
                        border: 1px solid #ddd;
                        text-align: center;
                        vertical-align: middle;
                    }
                    .asistencia_title{
                        color: #043c69; 
                    }
                    .divImg{
                    
                
    }
                </style>
                <div class="divInit">
         <h1> Registro de servicio </h1>
         </div>
                <table>
        <tr>
            <td class="data-label">Número Radicado:<br> <span class="info">' . $formulario->numero_radicado . '</span></td>
             <td class="data-label">Fecha de Creación:<br><span class="info"> ' . $fechaCreacionFromated . '</span></td>
        </tr>
        </table>
        <table>
        <tr>
          <td class="data-label">Sede:<br><span class="info"> ' . $formulario->sede . '</span></td>
          <td class="data-label">Medio de atencion:<br><span class="info"> ' . $atencionInteracion->nombre . '</span></td>
          ' . ($formulario->tipo_atencion_id == 5 || $formulario->tipo_atencion_id == 6  ? '
          <td class="data-label">Hora inicio:<br><span class="info"> ' . $horaInicioFormateada . '</span></td>
           <td class="data-label">Hora cierre:<br> <span class="info">' . $horaCierreFormateada  . '</span></td>' : '') . '
       </tr>
</table>
       <table>
        <tr>
            <td class="data-label">Empresa usuaria:<br><span class="info"> ' . $formulario->nombre_contacto . '</span></td>
            <td class="data-label">Nit:<br><span class="info"> ' . $formulario->nit_documento . '</span></td>
            
        </tr>
        <tr>
             <td class="data-label">Teléfono:<br><span class="info"> ' . $formulario->telefono . '</span></td>
             <td class="data-label">Correo:<br><span class="info"> ' . $formulario->correo . '</span></td>
        </tr>
         <tr>
            <td class="data-label">Tipo PQRSF:<br><span class="info"> ' . $formulario->pqrsf . '</span></td>
            <td class="data-label">Responsable:<br><span class="info"> ' . $formulario->responsable . '</span></td>
        </tr>
        </table>
        ' . ($formulario->tipo_atencion_id == 5 || $formulario->tipo_atencion_id == 6  ? '
        <table>
        <tr>
            <td class="data-label">Visita realizada por:<br><span class="info"> ' . $formulario->visitante . '</span></td>
            <td class="data-label">Cargo:<br><span class="info"> ' . $formulario->cargo_visitante . '</span></td>
        </tr>
         <tr>
            <td class="data-label">Visita atendida por:<br><span class="info"> ' . $formulario->visitado . '</span></td>
            <td class="data-label">Cargo:<br><span class="info"> ' . $formulario->cargo_visitado . '</span></td>
        </tr>

        </table>
        <table>
        <tr>
        <td class="data-label">Objetivo:<br><span class="info"> ' . $formulario->objetivo . '</span></td>
        </tr>
       <tr>
        <td class="data-label">Alcance:<br><span class="info"> ' . $formulario->alcance . '</span></td>
        </tr>
        </table>
        
        ' : '') . '
    
            <div class="data-label">Observación:<span class="info"> ' . $parrafo . '</span></div>
       
            ';

        // Mostrar evidencias en una tabla
        if (!empty($formulario->temasPrincipales)) {
            $html .= '
                <h2 class="section-title">Presentación y revision de temas</h2>
                ';
            foreach ($formulario->temasPrincipales as $tema) {
                $html .= '
                            <tr>
                                <h4>' . "Tema" . ':</h4>
                                <p>' . $tema->descripcion . '</p>';
            }
            $html .= '
                    <h2 class="section-title">Compromisos Generales</h2>';
            foreach ($formulario->compromisos as $compromiso) {
                $tituloFormateado = preg_replace('/([a-zA-Z])([0-9])/', '$1 $2', $compromiso->titulo);
                $tituloFormateado = preg_replace('/([0-9])([a-zA-Z])/', '$1 $2', $tituloFormateado);
                $tituloFormateado = ucwords(strtolower($tituloFormateado));
                $compromiso->descripcion != " " ?  $html .= ' 
                 
                <h4>' . $tituloFormateado . ':</h4>
                <p>' . $compromiso->descripcion . '</p>
        ' : "";
            }
        }
        if ($formulario->tipo_atencion_id == 5 || $formulario->tipo_atencion_id == 6) {
            $html .= '
                <h2 class="section-title">Asistencias</h2>
                <table>
                    <tr>
                        <td class="asistencia_title" ><strong>Nombre:</strong></td>
                        <td class="asistencia_title"><strong>Cargo:</strong></td>
                        <td class="asistencia_title"><strong>Firma:</strong></td>
                    </tr>';
            foreach ($formulario->asistencias as $asistencia) {
                $html .= '
                    <tr>
                        <td>' . $asistencia->nombre . '</td>
                        <td>' . $asistencia->cargo . '</td>
                        <td><div class="divImg"><img src="' . public_path($asistencia->firma) . '" class="signature" /></div></td>
                    </tr>';
            }
            $html .= '</table>';
        }
        $pdf->writeHTML($html, true, false, true, false, '');
        $totalPages = 0;
        if ($pdf->getNumPages() == 1) {
            $totalPages = 1;
        } else {
            $totalPages = $pdf->getNumPages();
        }

        // Agregar membrete en cada página después de la primera
        for ($i = 1; $i <= $totalPages; $i++) {
            // Cambiar a la página correspondiente
            $pdf->setPage($i);

            if ($i > 1) {
                $url = public_path('/upload/MEMBRETE.png');
                /*          $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false, 40); */
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false, 0);
                $pdf->Image($url, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
                $pdf->SetMargins(15, 30, 15);
                $pdf->SetAutoPageBreak(true, 40);
            }
            // Ajustar los márgenes para que el membrete no interfiera con el contenido

        }


        // Opción 1: Descargar el PDF
        if ($btnId == 2) {
            $pdf->Output('formulario.pdf', 'D');
        };

        // Opción 2: Mostrar el PDF en el navegador
        try {
            if ($btnId == 1) {
                $pdfPath = storage_path('app/temp.pdf');
                $pdf->Output($pdfPath, 'F');
                if (!file_exists($pdfPath)) {
                    return response()->json(['message' => 'Error al crear el PDF'], 500);
                }


                // Enviar correos a cada uno en el request
                foreach ($request->correos as $correoData) {
                    try {
                        if ($correoData['correo'] != "" && $correoData['correo'] != "null") {

                            $resultCorreo = $this->enviarCorreo($correoData['correo'], $formulario, $pdfPath, $registro_id, $modulo, $correoData['observacion'], $user->usuario, $correoData['compromiso']);
                        }
                    } catch (\Exception $th) {
                        return response()->json(['status' => 'error', 'message' => 'No fue posible enviar el registro verifique el correo de contacto o de los responsables']);
                    }
                }
                return $resultCorreo;
                return response()->json(['status' => 'success', 'message' => 'Registro enviado de manera exitosa']);
            }
        } catch (\Exception $th) {
            return $th;
            return response()->json(['status' => 'error', 'message' => 'No fue posible enviar el registro verifique el correo de contacto o de los responsables']);
        }
    }


    private function enviarCorreo($destinatario, $formulario, $pdfPath, $registro_id, $modulo, $observacion = '', $user, $booleanCompromiso)
    {


        $numeroRadicado = $formulario->numero_radicado;
        $tipo_atencion_id = $formulario->tipo_atencion_id;
        if ($tipo_atencion_id == 5 || $tipo_atencion_id == 6) {
            if ((($formulario->nit_documento == "900032514" || $formulario->nit_documento == "811025401") && $formulario->correo == $destinatario) || (($formulario->nit_documento == "900032514" || $formulario->nit_documento == "811025401") && $booleanCompromiso == false)) {
                $body = "Coordial saludo, Informamos que el acta de la reunion interna ha sido creado satisfactoriamente con radicado:  <b><i>$numeroRadicado</i></b>";
            } else {
                $body = "Cordial saludo, esperamos se encuentren muy bien.\n\n Informamos que el registro de visita ha sido creado satisfactoriamente con número de radicado: <b><i>$numeroRadicado</i></b>, Cualquier información adicional puede comunicarse con:
                    Katerin Andrea Nuno: (+57) 311-437-0207
                    William Hernán Hernandez: (+57) 311-586-4835
                    o a nuestra línea de atención general (604) 4485744, con gusto uno de nuestros facilitadores atenderá su llamada.\n\n simplificando conexiones, facilitando experiencias.
                    \n\n Atentamente:";
            }
        } else {
            $body = "Cordial saludo, esperamos se encuentren muy bien.\n\n Informamos que el registro de servicio ha sido creado satisfactoriamente con número de radicado: <b><i>$numeroRadicado</i></b>, Cualquier información adicional podrá ser atendida en la línea Servisai de Saitemp S.A. marcando  al (604) 4485744, con gusto uno de nuestros facilitadores atenderá su llamada.\n\n simplificando conexiones, facilitando experiencias.";
        }

        $body = nl2br($body);

        if ($booleanCompromiso == true) {
            $body = "Cordial saludo, tiene nuevos compromisos asignados en el radicado CRM número: <b><i>$numeroRadicado</i></b> adjunto con las siguientes observaciones: $observacion.
        \n\n Atentamente:";
        }


        $subject = 'Confirmación registro de servicio.';
        $nomb_membrete = 'Informe de servicio';

        // Datos del correo
        $correo = [
            'subject' => $subject,
            'body' => $body,
            'formulario_ingreso' => $pdfPath,
            'to' => $destinatario,
            'cc' => '',
            'cco' => $user,
            'modulo' => $modulo,
            'registro_id' => $registro_id,
            'nom_membrete' => $nomb_membrete
        ];

        // Instanciar el controlador de envío de correo
        $EnvioCorreoController = new EnvioCorreoController();
        $requestEmail = Request::createFromBase(new Request($correo));

        // Enviar el correo
        return $EnvioCorreoController->sendEmail($requestEmail);
    }

    public function getAllCompromisos()
    {
        $result = CompromisosVisitaCrm::select()->get();
        return response()->json($result);
    }

    public function verEvidencia($id)
    {
        $evidencia = Evidencia::find($id);

        if (!$evidencia) {
            return response()->json(['error' => 'Archivo no encontrado']);
        }

        $rutaArchivo = public_path($evidencia->archivo);
        /* $extension = pathinfo($rutaArchivo, PATHINFO_EXTENSION); */
        $nombreSinExtension = pathinfo($rutaArchivo, PATHINFO_FILENAME);
        $pos = strpos($rutaArchivo, '_');
        $nombreZip = substr($rutaArchivo, 0, $pos);
        $extension = pathinfo($nombreZip, PATHINFO_EXTENSION);
        $ultimoCaracter = strlen($rutaArchivo) - 1;
        $nombreArchivo = substr($rutaArchivo, $pos + 1, $ultimoCaracter);
        // Verificar si es un archivo ZIP
        if ($extension === 'zip') {
            $zip = new ZipArchive;
            $res = $zip->open($nombreZip);
            if ($res === true) {
                // Extraer el contenido del archivo ZIP
                $zip->extractTo(public_path('/upload/tmp/'), $nombreArchivo);
                $zip->close();
                $archivoExtraido = glob(public_path('/upload/tmp/' . $nombreArchivo));

                if (count($archivoExtraido) > 0) {
                    $archivoMsg = $archivoExtraido[0];
                    $extensionArchivoExtraido = pathinfo($archivoMsg, PATHINFO_EXTENSION);
                    /* return response()->download($archivoMsg, basename($archivoMsg))->deleteFileAfterSend(true); */
                    if ($extensionArchivoExtraido === 'msg') {
                        return response()->download($archivoMsg, basename($archivoMsg))->deleteFileAfterSend(true);
                    }
                    return response()->file($archivoMsg)->deleteFileAfterSend(true);
                } else {
                    return response()->json(['error' => 'No se encontró archivo .msg dentro del ZIP'], 404);
                }
            } else {
                return response()->json(['error' => 'No se pudo abrir el archivo ZIP'], 500);
            }
        }
    }
    public function recortarObservacion()
    {
        DB::beginTransaction();
        try {
            // Obtener todos los registros del modelo SeguimientoCrm
            $registros = SeguimientoCrm::all();

            foreach ($registros as $registro) {
                // Verificar si el campo 'observacion' tiene contenido
                if ($registro->observacion) {
                    // Dividir el contenido de 'observacion' en partes de 4000 caracteres
                    $observacionFragmentada = str_split($registro->observacion, 4000);

                    // Guardar la primera parte en el campo 'observacion'
                    $registro->observacion = $observacionFragmentada[0];

                    // Si hay una segunda parte, guardarla en el campo 'observacion2'
                    if (isset($observacionFragmentada[1])) {
                        $registro->observacion2 = $observacionFragmentada[1];
                    }

                    // Guardar los cambios
                    $registro->save();
                }
            }

            // Confirmar la transacción
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Observaciones divididas correctamente.']);
        } catch (\Exception $e) {
            // En caso de error, revertir la transacción
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al dividir las observaciones: ' . $e->getMessage()]);
        }
    }
    public function comprimirArchivos($numero_radicado, $archivo, $ruta, $nombre_archivo = null)
    {
        $manager = new ImageManager(new Driver());
        $zipNombre = $numero_radicado . '.zip';
        $zipGeneral = public_path($ruta . $zipNombre);
        $zipCoincidencia = glob($zipGeneral);
        if ($nombre_archivo == null) {
            $nombreArchivoOriginal = $archivo->getClientOriginalName();
        } else {
            $nombreArchivoOriginal = $nombre_archivo;
        }
        $nombreSinExtension = pathinfo($nombreArchivoOriginal, PATHINFO_FILENAME);
        $extension = pathinfo($nombreArchivoOriginal, PATHINFO_EXTENSION);
        $nombreLimpio = preg_replace('/[.\s]+/', '_', $nombreSinExtension) . '.' . $extension;
        $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreLimpio;
        $carpetaDestino = $ruta;
        $zipPath = $carpetaDestino . $zipNombre;
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $image = $manager->read($archivo->getPathname());
                $image->resizeDown(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($carpetaDestino . $nuevoNombre, 70);
            } elseif ($extension === 'pdf') {
                $pdf = new Fpdi();
                $pageCount = $pdf->setSourceFile($archivo->getPathname());
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pdf->AddPage();
                    $templateId = $pdf->importPage($pageNo);
                    $pdf->useTemplate($templateId);
                }
                $pdf->Output($carpetaDestino . $nuevoNombre, 'F');
            }
            if (count($zipCoincidencia) > 0) {
                $zip = new ZipArchive;
                $res = $zip->open($zipGeneral);
                if ($res === true) {
                    $zip->addFile($carpetaDestino . $nuevoNombre, $nuevoNombre);
                    $zip->close();
                } else {
                    throw new \Exception('No se pudo acceder al ZIP');
                }
                /* $nuevoNombre = $zipNombre . $nuevoNombre; */
            } else {
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                    $zip->addFile($carpetaDestino . $nuevoNombre, $nuevoNombre);
                    $zip->close();
                    /*   $nuevoNombre = $zipNombre . $nuevoNombre; */
                }
            }
            if (file_exists($carpetaDestino . $nuevoNombre)) {
                unlink($carpetaDestino . $nuevoNombre);
            }
        } else {
            if (count($zipCoincidencia) > 0) {
                $zip = new ZipArchive;
                $res = $zip->open($zipGeneral);
                if ($res === true) {
                    $zip->addFile($archivo->getPathname(), $nuevoNombre);
                    $zip->close();
                } else {
                    throw new \Exception('No se pudo acceder al ZIP');
                }
                /* $nuevoNombre = $zipNombre . $nuevoNombre; */
            } else {
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                    $zip->addFile($archivo->getPathname(), $nuevoNombre);
                    $zip->close();
                    /* $nuevoNombre = $zipNombre . $nuevoNombre; */
                }
            }
        }
        $ruta_zip_archivo = ltrim($carpetaDestino, '.') . $zipNombre . '_' . $nuevoNombre;
        return $ruta_zip_archivo;
    }
    public function exportarExcelCrm()
    {
        $result = SeguimientoCrm::leftjoin('usr_app_sedes_saitemp as sede', 'sede.id', 'usr_app_seguimiento_crm.sede_id')
            ->leftjoin('usr_app_procesos as proces', 'proces.id', 'usr_app_seguimiento_crm.proceso_id')
            ->leftjoin('usr_app_atencion_interacion as inter', 'inter.id', 'usr_app_seguimiento_crm.tipo_atencion_id')
            ->leftjoin('usr_app_estado_cierre_crm as cierre', 'cierre.id', 'usr_app_seguimiento_crm.estado_id')
            ->leftjoin('usr_app_pqrsf_crm as pqrsf', 'pqrsf.id', 'usr_app_seguimiento_crm.pqrsf_id')
            ->leftjoin('usr_app_solicitante_crm as soli', 'soli.id', 'usr_app_seguimiento_crm.solicitante_id')
            ->where('usr_app_seguimiento_crm.tipo_atencion_id', 5) // Condición para excluir registros con pqrsf_id = 6
            /*  ->where('usr_app_seguimiento_crm.tipo_atencion_id', 5) */
            ->select(
                'usr_app_seguimiento_crm.id',
                'usr_app_seguimiento_crm.numero_radicado',
                'sede.nombre as sede',
                'proces.nombre as proceso',
                'soli.nombre as solicitante',
                'usr_app_seguimiento_crm.nombre_contacto',
                'inter.nombre as iteraccion',
                'usr_app_seguimiento_crm.responsable',
                'pqrsf.nombre as pqrsf',
                'usr_app_seguimiento_crm.telefono',
                'usr_app_seguimiento_crm.correo',
                'cierre.nombre as estado',
                'usr_app_seguimiento_crm.observacion',
                'usr_app_seguimiento_crm.created_at',
            )
            ->orderby('usr_app_seguimiento_crm.id', 'DESC')
            ->get();

        // Convertir 'observacion' a texto plano
        $result = $result->map(function ($item) {
            $item->observacion = strip_tags($item->observacion); // Eliminar HTML del campo 'observacion'
            return $item;
        });

        return Excel::download(new CrmExport($result), 'crm.xlsx');
    }
}