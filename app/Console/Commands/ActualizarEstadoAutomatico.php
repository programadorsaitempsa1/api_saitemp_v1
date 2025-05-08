<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ClientesSeguimientoEstado;
use App\Models\cliente;
use Illuminate\Support\Carbon;
use App\Http\Controllers\formularioDebidaDiligenciaController;

class ActualizarEstadoAutomatico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actualizar:estado-automatico';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza automáticamente el estado de firma a 16(cancelado) después de dos meses en estado 4(Gestión SGI)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Iniciando verificación...");

        $user = User::where('email', 'programador2')->first();
        if (!$user) {
            $this->error('Usuario sistema no configurado');
            return;
        }
        auth()->login($user);

        $this->info("Usando usuario: {$user->email}");

        $clientes2 = Cliente::where('estado_firma_id', 5)->get();
        $this->info("Clientes en estado 5: " . $clientes2->count());


        $clientes = Cliente::where('estado_firma_id', 4)->get();
        $this->info("Clientes en estado 4: " . $clientes->count());

        foreach ($clientes2 as $cliente) {
            $this->info("Procesando cliente: {$cliente->id}");

            $seguimiento2 = ClientesSeguimientoEstado::where('cliente_id', $cliente->id)
                ->where('estados_firma_final', 4)
                ->latest('created_at')
                ->first();

            if (!$seguimiento2) {
                $this->warn("Cliente {$cliente->id} sin seguimiento en estado 5");
                continue;
            }

            $meses = $seguimiento2->created_at->diffInMonths(Carbon::now());
            $this->info("minutos desde último estado 4: {$meses}");

            if ($meses >= 2) {
                $this->info("Actualizando cliente {$cliente->id}...");
                $controller = new formularioDebidaDiligenciaController();
                $controller->actualizaestadofirma(
                    $cliente->id,
                    16,
                    null,
                    null,
                    $cliente->estado_firma_id
                );
            }
        }

        foreach ($clientes as $cliente) {
            $this->info("Procesando cliente: {$cliente->id}");

            $seguimiento = ClientesSeguimientoEstado::where('cliente_id', $cliente->id)
                ->where('estados_firma_final', 4)
                ->latest('created_at')
                ->first();

            if (!$seguimiento) {
                $this->warn("Cliente {$cliente->id} sin seguimiento en estado 4");
                continue;
            }

            $meses = $seguimiento->created_at->diffInMonths(Carbon::now());
            $this->info("minutos desde último estado 4: {$meses}");

            if ($meses >= 1) {
                $this->info("Actualizando cliente {$cliente->id}...");
                $controller = new formularioDebidaDiligenciaController();
                $controller->actualizaestadofirma(
                    $cliente->id,
                    1,
                    null,
                    null,
                    $cliente->estado_firma_id
                );
            }
        }
    }
}
