<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;




class EnvioCorreoController extends Controller
{
    public function sendEmail(Request $request)
    {
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

        $nombres = $user->nombres != 'null' ? $user->nombres:'';
        $apellidos = $user->apellidos != 'null' ? $user->apellidos:'';
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

        foreach ($destinatarios as $destinatario) {
            $email->addTo($destinatario);
        }

        if (count($cc) > 1) {
            foreach ($cc as $ccs) {
                $email->addCc($ccs);
            }
        }
        if (count($cco) > 1) {
            foreach ($cco as $ccos) {
                $email->addCc($ccos);
            }
        }

        foreach ($archivos as $archivo) {
         
            if ($archivo instanceof UploadedFile) {
                array_push($adjuntos,$archivo->getClientOriginalName());
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
}
