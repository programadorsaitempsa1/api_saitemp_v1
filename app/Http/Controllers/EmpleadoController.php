<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Empleado::select(
            'cod_emp',
            // 'ap1_emp',
            // 'ap2_emp',
            // 'nom_emp',
            DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
        )
            ->orderby('cod_emp')
            ->paginate(12);
        return response()->json($result);
    }

    public function search($texto)
    {
        if (is_numeric($texto)) {
            $campo = 'cod_emp';
            $result = Empleado::select(
                'cod_emp',
                DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
            )
                ->where($campo, 'like', '%' . $texto . '%')
                ->paginate(12);
            return response()->json($result);
        } else {

            $campos = explode(" ", trim($texto));
            if (count($campos) == 4) {
                $result = Empleado::select(
                    'cod_emp',
                    DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                )
                    ->where('ap1_emp', '=', $campos[0])
                    ->where('ap2_emp', '=', $campos[1])
                    ->where('nom_emp', '=', $campos[2] . ' ' . $campos[3])
                    ->paginate(12);
                return response()->json($result);
            } else if (count($campos) == 3) {
                $result = Empleado::select(
                    'cod_emp',
                    DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                )
                    ->where('ap1_emp', '=', $campos[0])
                    ->where('ap2_emp', '=', $campos[1])
                    ->where('nom_emp', 'like', '%' . $campos[2] . '%')
                    ->paginate(12);
                if (count($result) == 0) {
                    $result = Empleado::select(
                        'cod_emp',
                        DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                    )
                        ->where('ap1_emp', '=', $campos[0])
                        ->where('nom_emp', '=', $campos[1] . ' ' . $campos[2])
                        ->paginate(12);
                }
                if (count($result) == 0) {
                    $result = Empleado::select(
                        'cod_emp',
                        DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                    )
                        ->where('ap2_emp', '=', $campos[0])
                        ->where('nom_emp', '=', $campos[1] . ' ' . $campos[2])
                        ->paginate(12);
                }
                return response()->json($result);
            } else if (count($campos) == 2) {
                $result = Empleado::select(
                    'cod_emp',
                    DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                )
                    ->where('ap1_emp', '=', $campos[0])
                    ->where('nom_emp', 'like', '%' . $campos[1] . '%')
                    ->paginate(12);
                if (count($result) == 0) {
                    $result = Empleado::select(
                        'cod_emp',
                        DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                    )
                        ->where('ap2_emp', '=', $campos[0])
                        ->where('nom_emp', 'like', '%' . $campos[1] . '%')
                        ->paginate(12);
                }
                if (count($result) == 0) {
                    $result = Empleado::select(
                        'cod_emp',
                        DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                    )
                        ->where('ap1_emp', '=', $campos[0])
                        ->where('ap2_emp', '=', $campos[1])
                        ->paginate(12);
                }
                if (count($result) == 0) {
                    $result = Empleado::select(
                        'cod_emp',
                        DB::raw("CONCAT(ap1_emp,' ',ap2_emp,' ',nom_emp)  AS fullname")
                    )
                        ->where('nom_emp', '=', $campos[0] . ' ' . $campos[1])
                        ->paginate(12);
                }
                return response()->json($result);
            }
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
