<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ExamenPruebaController extends Controller
{
    public function examen($cedula)
    {
        $result = DB::table('GTH_EvaDesemAsig')
            ->where('cod_emp_evado', $cedula)
            ->get();
        return response()->json($result);
    }

    public function create()
    {
        // try {


            $cedulas = [
                // ['cedula1' => '1000872159', 'cedula2' => '71703511'],
                // ['cedula1' => '1152221366', 'cedula2' => '71703511'],
                // ['cedula1'=>'43545098','cedula2'=>'44004356'],
                // ['cedula1'=>'1019983787','cedula2'=>'43192048'],
                // ['cedula1'=>'1084739014','cedula2'=>'32837315'],
                // ['cedula1'=>'1216728879','cedula2'=>'71703511'],
                // ['cedula1'=>'1001820628','cedula2'=>'32258641'],
                // ['cedula1'=>'1085165134','cedula2'=>'32258641'],
                // ['cedula1'=>'1214721038','cedula2'=>'32258641'],
                // ['cedula1'=>'1046669357','cedula2'=>'43192048'],
                // ['cedula1'=>'1069751654','cedula2'=>'43192048'],
                // ['cedula1'=>'1039100671','cedula2'=>'43192048'],
                // ['cedula1'=>'1057590360','cedula2'=>'44000713'],
                // ['cedula1'=>'1140883341','cedula2'=>'44000713'],
                // ['cedula1'=>'1037576804','cedula2'=>'44000713'],
                // ['cedula1'=>'35394602','cedula2'=>'71703511'],
                // ['cedula1'=>'1023956686','cedula2'=>'71703511'],
                // ['cedula1'=>'1024564825','cedula2'=>'71703511'],
                // ['cedula1'=>'1045434672','cedula2'=>'71703511'],
                // ['cedula1'=>'1013660856','cedula2'=>'71703511'],
                // ['cedula1'=>'1020463142','cedula2'=>'71703511'],
                // ['cedula1'=>'55249151','cedula2'=>'71703511'],
                // ['cedula1'=>'1214721716','cedula2'=>'71703511'],
                // ['cedula1'=>'1017235851','cedula2'=>'71703511'],
                // ['cedula1'=>'1152456884','cedula2'=>'71703511'],
                // ['cedula1'=>'1214724890','cedula2'=>'71703511'],
                // ['cedula1'=>'1014285878','cedula2'=>'71703511'],
                // ['cedula1'=>'1040743683','cedula2'=>'43974692'],
                // ['cedula1'=>'1017144560','cedula2'=>'32258641'],
                // ['cedula1'=>'1128472850','cedula2'=>'71703511'],
                // ['cedula1'=>'43918827','cedula2'=>'71703511'],
                // ['cedula1'=>'43111384','cedula2'=>'71703511'],
                // ['cedula1'=>'1017201085','cedula2'=>'98545306'],
                // ['cedula1'=>'32837315','cedula2'=>'71703511'],
                // # ['cedula1'=>'98545306','cedula2'=>''],
                // # ['cedula1'=>'71703511','cedula2'=>''],
                // ['cedula1'=>'43574279','cedula2'=>'32258641'],
                // ['cedula1'=>'1073159746','cedula2'=>'43811413'],
                // ['cedula1'=>'43662706','cedula2'=>'43811413'],
                // ['cedula1'=>'43096571','cedula2'=>'43811413'],
                // ['cedula1'=>'98576618','cedula2'=>'43811413'],
                // ['cedula1'=>'98671588','cedula2'=>'43811413'],
                // ['cedula1'=>'43974692','cedula2'=>'71703511'],
                // ['cedula1'=>'10258827','cedula2'=>'71703511'],
                // ['cedula1'=>'32258641','cedula2'=>'98545306'],
                // ['cedula1'=>'32140487','cedula2'=>'98545306'],
                // ['cedula1'=>'43811413','cedula2'=>'71703511'],
                // ['cedula1'=>'1128458654','cedula2'=>'71703511'],
                // ['cedula1'=>'1037594124','cedula2'=>'1128472850'],
                // ['cedula1'=>'44004356','cedula2'=>'71703511'],
                // ['cedula1'=>'1037644148','cedula2'=>'71703511'],
                // ['cedula1'=>'43192048','cedula2'=>'43974692'],
                // ['cedula1'=>'1017149456','cedula2'=>'32258641'],
                // ['cedula1'=>'20995857','cedula2'=>'32837315'],
                // ['cedula1'=>'1036631964','cedula2'=>'1128472850'],
                // ['cedula1'=>'80491626','cedula2'=>'10258827'],
                // ['cedula1'=>'1035427846','cedula2'=>'44004356'],
                // ['cedula1'=>'71335065','cedula2'=>'98545306'],
                // ['cedula1'=>'71687910','cedula2'=>'98545306'],
                // ['cedula1'=>'32352998','cedula2'=>'43111384'],
                // ['cedula1'=>'1037623573','cedula2'=>'43111384'],
                // ['cedula1'=>'1007314707','cedula2'=>'44004356'],
                // ['cedula1'=>'1216723872','cedula2'=>'44004356'],
                // ['cedula1'=>'1214741355','cedula2'=>'44004356'],
                // ['cedula1'=>'1017182895','cedula2'=>'44004356'],
                // ['cedula1'=>'1000099165','cedula2'=>'44004356'],
                // ['cedula1'=>'1000645399','cedula2'=>'44004356'],
                // ['cedula1'=>'98547213','cedula2'=>'43918827'],
                // ['cedula1'=>'71398914','cedula2'=>'43918827'],
                // ['cedula1'=>'71273807','cedula2'=>'43918827'],
                // ['cedula1'=>'43163828','cedula2'=>'43918827'],
                // ['cedula1'=>'1035857937','cedula2'=>'44000713'],
                // ['cedula1'=>'1039459135','cedula2'=>'44004356'],
                // ['cedula1'=>'1152220053','cedula2'=>'43918827'],
                // ['cedula1'=>'1017183299','cedula2'=>'43811413'],
                // ['cedula1'=>'98697602','cedula2'=>'43918827'],
                // ['cedula1'=>'1026149871','cedula2'=>'44004356'],
                // ['cedula1'=>'71797243','cedula2'=>'1128472850'],
                // ['cedula1'=>'1033653791','cedula2'=>'32258641'],
                // ['cedula1'=>'1020454596','cedula2'=>'32258641'],
                // ['cedula1'=>'32205917','cedula2'=>'32258641'],
                // ['cedula1'=>'1036664610','cedula2'=>'44004356'],
                // ['cedula1'=>'1035439571','cedula2'=>'44004356'],
                // ['cedula1'=>'1047399903','cedula2'=>'44004356'],
                // ['cedula1'=>'1036663724','cedula2'=>'44004356'],
                // ['cedula1'=>'1020835060','cedula2'=>'44004356'],
                // ['cedula1'=>'1036649489','cedula2'=>'44004356'],
                // ['cedula1'=>'1152221666','cedula2'=>'44004356'],
                // ['cedula1'=>'43115418','cedula2'=>'32140487'],
                // ['cedula1'=>'1037633838','cedula2'=>'32140487'],
                // ['cedula1'=>'1040738542','cedula2'=>'44004356'],
                // ['cedula1'=>'1152201753','cedula2'=>'44004356'],
                // ['cedula1'=>'43979346','cedula2'=>'44004356'],
                // ['cedula1'=>'1036646309','cedula2'=>'44004356'],
                // ['cedula1'=>'1000535148','cedula2'=>'44004356'],
                // ['cedula1'=>'43066883','cedula2'=>'44004356'],
                // ['cedula1'=>'1038771186','cedula2'=>'44004356'],
                // ['cedula1'=>'1152210407','cedula2'=>'43974692'],
                // # ['cedula1'=>'1088295448','cedula2'=>''],
                // ['cedula1'=>'71187499','cedula2'=>'43974692'],
                // ['cedula1'=>'1152701212','cedula2'=>'44000713'],
                // ['cedula1'=>'1036646583','cedula2'=>'44000713'],
                // ['cedula1'=>'1036600953','cedula2'=>'44000713'],
                // ['cedula1'=>'1040745617','cedula2'=>'44000713'],
                // ['cedula1'=>'42827858','cedula2'=>'44000713'],
                // ['cedula1'=>'1040756520','cedula2'=>'44000713'],
                // ['cedula1'=>'1022431323','cedula2'=>'44000713'],
                // ['cedula1'=>'1020446478','cedula2'=>'32258641'],
                // ['cedula1'=>'1152461354','cedula2'=>'44004356'],
                // ['cedula1'=>'1073241539','cedula2'=>'32258641'],
                // ['cedula1'=>'1035970583','cedula2'=>'32258641'],
                // ['cedula1'=>'1019091239','cedula2'=>'32258641'],
                // ['cedula1'=>'43841032','cedula2'=>'32258641'],
                // ['cedula1'=>'1036653266','cedula2'=>'32258641'],
                // ['cedula1'=>'1040031380','cedula2'=>'32258641'],
                // ['cedula1'=>'43115983','cedula2'=>'32258641'],
                // ['cedula1'=>'43259128','cedula2'=>'32258641'],
                // ['cedula1'=>'1214720392','cedula2'=>'32258641'],
                // ['cedula1'=>'1152706222','cedula2'=>'32258641'],
                // ['cedula1'=>'1039457445','cedula2'=>'32258641'],
                // ['cedula1'=>'1025644989','cedula2'=>'32258641'],
                // ['cedula1'=>'1020474072','cedula2'=>'32258641'],
                // ['cedula1'=>'1152434358','cedula2'=>'43111384'],
                // ['cedula1'=>'1045048503','cedula2'=>'43974692'],
                // ['cedula1'=>'1035441226','cedula2'=>'71703511'],
                // ['cedula1'=>'1017236660','cedula2'=>'71703511'],
                // ['cedula1'=>'1012388535','cedula2'=>'71703511'],
                // ['cedula1'=>'1036670187','cedula2'=>'43974692'],
                ['cedula1'=>'39580018','cedula2'=>'43192048'],
                // ['cedula1'=>'1128396626','cedula2'=>'98545306'],
                // ['cedula1'=>'44000713','cedula2'=>'71703511'],

            ];

            foreach ($cedulas as $item) {
                $result = DB::table('GTH_EvaDesemAsig')
                    ->where('cod_emp_evado', $item['cedula1'])
                    ->get();


                // if ($result->count() === 1) {


                    $registro = $result->first();
                    // return $registro;

                    $fecha = Carbon::now();


                    DB::table('GTH_EvaDesemAsig')
                        ->where('cod_emp_evado', $item['cedula1'])
                        ->insert([
                            'cod_eva_des' => $registro->cod_eva_des,
                            'cod_emp_evado' => $item['cedula1'],
                            'cod_emp_evador' => $item['cedula2'],
                            'cod_eva' => $registro->cod_eva,
                            'fec_eva' => Carbon::parse($registro->fec_eva)->format('Y-d-m'),
                            // 'fec_eva' => $registro->fec_eva,
                            'cod_rol' => 1,
                            'cod_cia' => $registro->cod_cia,
                            'cod_grup_val' => $registro->cod_grup_val,
                            'tip_asig' => $registro->tip_asig,
                            'nota_eva' => $registro->nota_eva,
                            'obs_eva' => $registro->obs_eva,
                        ]);
                // }
            }
            return 'Inserción exitosa';
        // } catch (\Exception $e) {
        //     return $e;
        // }
    }
}
