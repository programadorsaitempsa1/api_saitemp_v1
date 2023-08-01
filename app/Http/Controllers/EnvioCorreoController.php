<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;
// use Swift_SmtpTransport;
// use App\Mail\EnvioCorreo;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Illuminate\Support\Facades\Crypt;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class EnvioCorreoController extends Controller
{
    public function sendEmail(Request $request)
    {

        $user = auth()->user();
    
        $destinatarios = explode(',', $request->to);

        $archivos = $request->files->all();

        if ($user->usuario == '' || $user->usuario == null) {
            return 'Usuario no cuenta con correo electrónico';
        }

        if ($user->contrasena_correo == '' || $user->contrasena_correo == null) {
            return 'Usuario no cuenta con contraseña del correo';
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
            ->from(new Address($user->usuario, $user->nombres))
            ->subject($request->subject)
            ->html($request->body);
        foreach ($destinatarios as $destinatario) {
            $email->addTo($destinatario);
        }
        if (!empty($request->cc)) {
            $email->cc($request->cc);
        }
        if (!empty($request->cco)) {
            $email->bcc($request->cco);
        }

        foreach ($archivos as $archivo) {
            if ($archivo instanceof UploadedFile) {
                $email->attachFromPath($archivo->getPathname(), $archivo->getClientOriginalName(), $archivo->getClientMimeType());
            }
        }

        $mailer->send($email);
        if ($mailer) {
            return 'El correo electrónico se ha enviado correctamente.';
        } else {
            return 'Hubo un error al enviar el correo electrónico.';
        }
    }
}
