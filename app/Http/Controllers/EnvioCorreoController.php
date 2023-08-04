<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Mime\Part\Attachment;
use Symfony\Component\Mime\Part\InlinePart;



class EnvioCorreoController extends Controller
{
    public function sendEmail(Request $request)
    {

        $user = auth()->user();

        $nombreArchivo1 = pathinfo($user->imagen_firma_1, PATHINFO_BASENAME);
        $nombreArchivo2 = pathinfo($user->imagen_firma_2, PATHINFO_BASENAME);

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

        $email = (new Email())
            ->from(new Address($user->usuario, $user->nombres.' '.$user->apellidos))
            ->subject($request->subject)
            ->html($request->body);

            $email->attachFromPath(public_path($user->imagen_firma_1), $nombreArchivo1);
            $email->attachFromPath(public_path($user->imagen_firma_2), $nombreArchivo2);

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
                $email->attachFromPath($archivo->getPathname(), $archivo->getClientOriginalName(), $archivo->getClientMimeType());
            }
        }

        $mailer->send($email);
        if ($mailer) {
            return response()->json(['status' => 'success', 'message' => 'El correo electrónico se ha enviado correctamente.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Hubo un error al enviar el correo electrónico.']);
        }
    }
}
