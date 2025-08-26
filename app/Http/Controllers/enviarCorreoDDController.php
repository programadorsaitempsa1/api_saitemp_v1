<?php

namespace App\Http\Controllers;

use App\Http\Controllers\formularioDebidaDiligenciaController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class enviarCorreoDDController extends Controller
{
    public function enviarCorreosDD(Request $request, $registro_id)
    {
        $modulo = 15;
        $formularioDebidaDiligenciaController = new formularioDebidaDiligenciaController;
        $formulario = $formularioDebidaDiligenciaController->getbyid($registro_id)->getData();
        $user = auth()->user();
        $resultCorreo = response()->json(['status' => 'success', 'message' => 'No ha añadido correos']);
        foreach ($request->correos as $correoData) {
            try {
                if ($correoData['correo'] != "" && $correoData['correo'] != "null") {

                    $resultCorreo = $this->enviarCorreo($correoData['correo'], $formulario,  $registro_id, $modulo, $correoData['observacion'], $user->usuario, $correoData['corregir']);
                }
            } catch (\Exception $e) {
                $resultCorreo = response()->json(['status' => 'error', 'message' => 'No fue posible enviar el registro verifique el correo de contacto o de los responsables']);
            }
        }
        return $resultCorreo;
    }

    public function enviarCorreo($destinatario, $formulario,  $registro_id, $modulo, $observacion = '', $user, $booleanCorregir, $booleanCancelado = false)
    {


        $numeroRadicado = $formulario->numero_radicado;
        $observaciones = '<html>
        <body>
      ';

        if (count($formulario->seguimiento) > 0) {
            $maxIterations = min(4, count($formulario->seguimiento)); // Limitar a 4 ciclos máximo
            for ($i = 0; $i < $maxIterations; $i++) {
                $item = $formulario->seguimiento[$i]; // Acceder al elemento actual
                $formattedDate = Carbon::parse($item->updated_at)
                    ->setTimezone('America/Bogota')
                    ->format('d/m/Y H:i:s');
                $observaciones .= '<tr>
                <td style="border: 1px #000000 solid; padding:5px; border-collapse: collapse;">
                    <strong>Fecha: </strong>' . $formattedDate . '<br>
                    <strong>Actualiza: </strong>' . htmlspecialchars($item->actualiza) . '<br>
                    <strong>Observaciones: </strong>' . htmlspecialchars($item->observaciones) . '
                </td>
            </tr>';
            }
        } else {
            $observaciones .= '<tr>
            <td colspan="3" style="border: 1px #000000 solid; padding:5px; text-align:center;">No hay observaciones</td>
        </tr>';
        }

        $observaciones .= '
            </table>
        </body>
    </html>';


        if ($booleanCorregir == true) {
            $body = "Cordial saludo, tiene novedades en el radicado Debida Diligencia número: <b><i>$numeroRadicado</i></b> con las siguientes observaciones: $observacion.
            \n\npara acceder al radicado ingrese al siguiente link: <a href='http://srv-saitemp03:8181/aplicaciones/?#/navbar/debida-diligencia/formulario-clientes/$registro_id'>Click aquí</a> *Debe encontrarse logueado con su usuario y contraseña en SEIYA.
            
            \n\n Atentamente:";
        } else {
            $body = "Cordial saludo, tiene nuevas tareas asignadas en el radicado Debida Diligencia número: <b><i>$numeroRadicado</i></b>.
             \n\npara acceder al radicado ingrese al siguiente link: <a href='http://srv-saitemp03:8181/aplicaciones/?#/navbar/debida-diligencia/formulario-clientes/$registro_id'>Click aquí</a> *Debe encontrarse logueado con su usuario y contraseña en SEIYA.
             \n\n Con el siguiente historial de actualizaciones: \n\n$observaciones
             \n\n
            Atentamente:";
        }

        if ($booleanCancelado == true) {
            $body = "Cordial saludo, el radicado Debida Diligencia número: <b><i>$numeroRadicado</i></b> ha sido cancelado.
            \n\n Atentamente:";
        }

        $body = nl2br($body);
        $subject = 'Confirmación registro de servicio.';
        $nomb_membrete = 'Informe de servicio';

        // Datos del correo
        $correo = [
            'subject' => $subject,
            'body' => $body,
            'formulario_ingreso' => "",
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
}