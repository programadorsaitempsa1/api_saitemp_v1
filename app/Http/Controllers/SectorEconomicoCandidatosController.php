<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SectorEconomicoCandidatosModel;

class SectorEconomicoCandidatosController extends Controller
{
    public function index()
    {
        $sector = SectorEconomicoCandidatosModel::orderby('usr_app_sector_econimico_c.nombre', 'ASC')->get();
        return response()->json($sector);
    }
    public function create(Request $request)
    {
        try {
            $sector =  new SectorEconomicoCandidatosModel;
            $sector->nombre = $request->nombre;
            $sector->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
}