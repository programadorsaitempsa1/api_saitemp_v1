<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class cliente extends Model
{
    use HasFactory;
    protected $table = 'usr_app_clientes';

    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
        // return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m H:i:s');
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($registro) {
    //         // Obtener el año actual
    //         $ano_actual = date('Y');

    //         // Obtener el último registro creado en el año actual
    //         $ultimo_registro_ano_actual = self::where('numero_radicado', 'like', '%' . $ano_actual . '%')
    //             ->orderBy('id', 'desc')
    //             ->first();

    //         // Generar el nuevo número de registro
    //         if ($ultimo_registro_ano_actual) {
    //             $ultimo_numero = explode('-', $ultimo_registro_ano_actual->numero_radicado)[0];
    //             $nuevo_numero = str_pad((int)$ultimo_numero + 1, 4, '0', STR_PAD_LEFT);
    //         } else {
    //             $nuevo_numero = '0001';
    //         }

    //         // Establecer el nuevo número de registro
    //         $registro->numero_radicado = $nuevo_numero . '-' . $ano_actual;
    //     });
    // }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($registro) {

            if (!$registro->numero_radicado) {
                // Obtener el año actual
                $ano_actual = date('Y');

                // Obtener el último registro creado en el año actual
                $ultimo_registro_ano_actual = self::where('numero_radicado', 'like', '%' . $ano_actual . '%')
                    ->orderBy('numero_radicado', 'desc')
                    ->first();

                // Generar el nuevo número de registro
                if ($ultimo_registro_ano_actual) {
                    $ultimo_numero = explode('-', $ultimo_registro_ano_actual->numero_radicado)[0];
                    $nuevo_numero = str_pad((int)$ultimo_numero + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $nuevo_numero = '0001';
                }

                // Establecer el nuevo número de registro
                $registro->numero_radicado = $nuevo_numero . '-' . $ano_actual;


                if (!$registro->codigo_documento) {
                    $registro->codigo_documento = 'FEGC-01-01';
                }

                if (!$registro->fecha_documento) {
                    $registro->fecha_documento = '02/01/2024';
                }

                if (!$registro->version_documento) {
                    $registro->version_documento = 20;
                }

                $registro->created_at = now()->format('Y-m-d H:i:s.u');
            }
        });
    }
}
