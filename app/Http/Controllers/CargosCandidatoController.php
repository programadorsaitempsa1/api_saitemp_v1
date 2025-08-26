<?php

namespace App\Http\Controllers;

use App\Models\CargosCandidatoModel;
use Illuminate\Http\Request;
use App\Imports\CargosCandidatoImport;


use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class CargosCandidatoController extends Controller
{
    //
    public function index()
    {
        $cargo = CargosCandidatoModel::orderby('usr_app_cargos_candidatos.nombre', 'ASC')->get();
        return response()->json($cargo);
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new CargosCandidatoImport, $request->file('archivo_excel'));

            return response()->json([
                'message' => 'Cargos importados exitosamente'
            ], 200);
        } catch (ValidationException $e) {
            $errors = $e->failures();
            return response()->json([
                'message' => 'Errores en los datos',
                'errors' => collect($errors)->map(function ($item) {
                    return [
                        'fila' => $item->row(),
                        'campo' => $item->attribute(),
                        'errores' => $item->errors(),
                        'valores' => $item->values()
                    ];
                })
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al importar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}