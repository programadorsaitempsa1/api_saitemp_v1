<?php

namespace App\Http\Controllers;
use App\Models\FormularioIngresoArchivos;
use ZipArchive;
use Illuminate\Support\Facades\File;

class GenerarZipController extends Controller
{
    public function descargarArchivosById($idRadicado, $idCliente){
     

        $files= FormularioIngresoArchivos::where('ingreso_id',$idRadicado )->get();

        if (count($files) == 0) {
            return response()->json(['message' => 'No hay archivos asociados a este radicado.'], 404);
        }

        $zipFileName = 'archivos_radicado_' . $idCliente .'.zip';
        $zipFilePath = storage_path('app/' . $zipFileName); 
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE){
            foreach ($files as $archivo) {
                $rutaArchivo = public_path( $archivo->ruta);
                if (File::exists($rutaArchivo)) {
                    $zip->addFile($rutaArchivo, basename($rutaArchivo));
                }
            }
            $zip->close();
        }
        else {
            return response()->json(['message' => 'No se pudo crear el archivo ZIP.'], 500);
        }
        if (File::exists($zipFilePath)) {
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el archivo ZIP.'], 500);
        }
    }
 
}