<?php

namespace App\Http\Controllers;

use App\Models\ProcesosEspeciales;
use App\Models\FormularioProcesosEspeciales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exports\ProcesosEspecialesExport;


class ProcesosEspecialesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = ProcesosEspeciales::select(
            'cod_proc',
            'nom_proc',
            'nom_sp',
        )
            ->get();
        return response()->json($result);
    }

    public function form($codigo)
    {
        $result = FormularioProcesosEspeciales::select(
            // 'nom_param',
            // 'des_param',
            // 'tip_obj',
            // 'val_def',
            // 'for_cmp',
            // 'req_cmp',
            // 'ord_cmp',
            // 'tab_hlp',
            // 'nom_hlp',
            // 'des_hlp',
            // 'fil_hlp',
            // 'lon_max',
            // 'lon_max',
        )
            ->where('cod_proc', '=', $codigo)
            ->get();
        return response()->json($result);
    }

    public function listasprocesosespeciales($tabla, $codigo1, $codigo2)
    {
        $result = DB::table($tabla)
            ->select($codigo1, $codigo2)
            ->paginate();
        return response()->json($result);
        // $query = DB::table($tabla);
        // $count = $query->count();

        // if ($count > 20) {
        //     $result = $query->paginate(20);
        // } else {
        //     $result = $query->get();
        // }

        // return response()->json($result);
    }

    public function listasprocesosespecialesfilter($tabla, $codigo1, $codigo2)
    {
        $result = DB::table($tabla)
            ->select($codigo1, $codigo2)
            ->paginate();
        return response()->json($result);
    }

    public function filtroprocesosespeciales($tabla, $search, $codigo1, $codigo2)
    {
        $result = DB::table($tabla)
            ->select($codigo1, $codigo2)
            ->where($codigo1, 'like', '%' . $search . '%')
            ->orwhere($codigo2, 'like', '%' . $search . '%')
            ->paginate();
        return response()->json($result);
    }


    public function ejecutaprocesosespeciales(Request $request)
    {
        $valoresParametros = $request->parametros;
        set_time_limit(0);
        $numParametros = count($valoresParametros) - 1; // Excluye el último elemento
        $parametros = [];

        for ($i = 0; $i < $numParametros; $i++) {
            $parametros[] = $valoresParametros[$i];
        }

        $nombreProcedimiento = $valoresParametros[$numParametros];

        // Creamos una cadena que contiene los marcadores de posición para los parámetros
        $marcadores = implode(',', array_fill(0, $numParametros, '?'));

        // Concatenamos el nombre del procedimiento almacenado con los marcadores de posición
        $sql = "EXEC $nombreProcedimiento $marcadores";

        // Ejecutamos la consulta SQL y obtenemos los resultados
        $resultados = DB::select($sql, $parametros);

        // Convertimos los resultados a una colección de Laravel
        $coleccionResultados = collect($resultados);

        // Obtenemos el número de página actual de la consulta
        $paginaActual = LengthAwarePaginator::resolveCurrentPage();

        // Especificamos el número de elementos por página
        $elementosPorPagina = 14;

        // Creamos una instancia de LengthAwarePaginator y paginamos los resultados
        $resultadosPaginados = new LengthAwarePaginator(
            $coleccionResultados->forPage($paginaActual, $elementosPorPagina),
            $coleccionResultados->count(),
            $elementosPorPagina,
            $paginaActual
        );

        if ($resultadosPaginados instanceof LengthAwarePaginator) {
            $resultadosPaginados->withPath($request->fullUrl());
        }

        // Agregamos cualquier parámetro de consulta adicional
        $resultadosPaginados->appends($request->except('page'));

        // Retornamos una respuesta JSON con los resultados paginados
        return $resultadosPaginados->toJson();
    }


    public function procesosespecialesexport($request)
    {

        $decoded = base64_decode($request);
        $separator = "*";
        $array = explode($separator, $decoded);
        // return $array[0];
        // $result = base64_decode($request);
        $valoresParametros = $array;
        set_time_limit(0);
        $numParametros = count($valoresParametros) - 1; // Excluye el último elemento
        $parametros = [];

        for ($i = 0; $i < $numParametros; $i++) {
            $parametros[] = $valoresParametros[$i];
        }

        $nombreProcedimiento = $valoresParametros[$numParametros];

        // Creamos una cadena que contiene los marcadores de posición para los parámetros
        $marcadores = implode(',', array_fill(0, $numParametros, '?'));

        // Concatenamos el nombre del procedimiento almacenado con los marcadores de posición
        $sql = "EXEC $nombreProcedimiento $marcadores";

        // Ejecutamos la consulta SQL y obtenemos los resultados
        $items = DB::select($sql, $parametros);

        // return $items;

        // return (new ProcesosEspecialesExport($items))->download('items.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        // Obtén la colección de resultados
        $collection = collect($items);

        // Obtén los nombres de las columnas dinámicamente
        $columns = array_keys(get_object_vars($collection->first()));

        // Crea un arreglo con los nombres de las columnas
        $exportData = [$columns];

        // Agrega los datos de cada fila al arreglo
        foreach ($collection as $row) {
            $rowData = [];
            foreach ($columns as $column) {
                $rowData[] = $row->$column;
            }
            $exportData[] = $rowData;
        }
        $data = collect($exportData);
        return (new ProcesosEspecialesExport($data))->download('exportData.xlsx', \Maatwebsite\Excel\Excel::XLSX);
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
