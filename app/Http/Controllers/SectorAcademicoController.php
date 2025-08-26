<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SectorAcademicoModel;
use Illuminate\Support\Str;

class SectorAcademicoController extends Controller
{
    public function index()
    {
        $sector = SectorAcademicoModel::orderby('usr_app_sector_academico_c.nombre', 'ASC')->get()
            ->map(function ($sector) {
                $sector->nombre = Str::ucfirst(Str::lower($sector->nombre));
                return $sector;
            });;
        return response()->json($sector);
    }
    public function create(Request $request)
    {
        try {
            $sector =  new SectorAcademicoModel;
            $sector->nombre = $request->nombre;
            $sector->save();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
}