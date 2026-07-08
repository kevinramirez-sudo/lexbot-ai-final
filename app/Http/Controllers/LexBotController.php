<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Cita;
use App\Models\TelegramUser;
use App\Models\User;
use App\Services\CaseAnalysisService;
use App\Services\CaseWorkflowService;
use App\Services\CustomerNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LexBotController extends Controller
{
    private const MENU_CASO = 'Registrar caso';
    private const MENU_CASOS = 'Ver mis casos';
    private const MENU_CITA = 'Ver mi próxima cita';
    private const MENU_AYUDA = 'Ayuda';

    public function analizar(
        Request $request,
        CaseAnalysisService $analizador,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        $mensaje = $request->input('message');

        if (!is_array($mensaje) || !isset($mensaje['chat']['id'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $mensaje['chat']['id'];
        $texto = trim((string) ($mensaje['text'] ?? ''));

        if ($texto === '') {
            return response()->json(['ok' => true]);
        }

        $usuario = TelegramUser::firstOrCreate(
            ['chat_id' => $chatId],
            ['estado' => 'inicio']
        );

        if (mb_strtolower($texto, 'UTF-8') === '/start') {
            return $this->inicio($usuario);
        }

        if ($usuario->estado === 'esperando_nombre') {
            $usuario->nombre = $texto;
            $usuario->estado = 'esperando_correo';
            $usuario->save();

            $this->responder(
                $chatId,
                'Perfecto, '.$usuario->nombre.".\n\nAhora escribe tu correo electrónico."
            );

            return response()->json(['ok' => true]);
        }

        if ($usuario->estado === 'esperando_correo') {
            return $this->guardarCorreo($usuario, $texto);
        }

        if ($usuario->estado === 'esperando_caso') {
            return $this->registrarCasoTelegram(
                $usuario,
                $texto,
                $analizador,
                $flujo,
                $notificador
            );
        }

        if ($usuario->estado !== 'final') {
            $usuario->estado = 'esperando_nombre';
            $usuario->save();
            $this->responder($chatId, 'Escribe tu nombre completo para continuar.');

            return response()->json(['ok' => true]);
        }

        return $this->procesarMenu(
            $usuario,
            $texto,
            $analizador,
            $flujo,
            $notificador
        );
    }

    private function inicio(TelegramUser $usuario)
    {
        if ($usuario->estado === 'final') {
            $this->mostrarMenu($usuario, 'Hola de nuevo, '.$usuario->nombre.'. ¿Qué deseas hacer?');
        } else {
            $usuario->estado = 'esperando_nombre';
            $usuario->save();
            $this->responder(
                (string) $usuario->chat_id,
                "Hola, soy LexBot AI.\n\nPara registrarte, escribe tu nombre completo."
            );
        }

        return response()->json(['ok' => true]);
    }

    private function guardarCorreo(TelegramUser $usuario, string $texto)
    {
        $correo = strtolower(trim($texto));

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->responder(
                (string) $usuario->chat_id,
                "El correo no es válido.\n\nEjemplo: nombre@correo.com"
            );

            return response()->json(['ok' => true]);
        }

        $usuario->correo = $correo;
        $usuario->estado = 'final';
        $usuario->save();

        $this->mostrarMenu(
            $usuario,
            "Registro completado.\n\nUsa este mismo correo en la web de LexBot AI para vincular tus notificaciones."
        );

        return response()->json(['ok' => true]);
    }

    private function procesarMenu(
        TelegramUser $usuario,
        string $texto,
        CaseAnalysisService $analizador,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        $opcion = mb_strtolower(trim($texto), 'UTF-8');

        if ($opcion === mb_strtolower(self::MENU_CASO, 'UTF-8')) {
            $cliente = $this->buscarClienteWeb($usuario);

            if (!$cliente) {
                $this->responder(
                    (string) $usuario->chat_id,
                    "Primero crea una cuenta como Cliente en la página web usando el correo:\n"
                    .$usuario->correo
                );

                return response()->json(['ok' => true]);
            }

            $usuario->estado = 'esperando_caso';
            $usuario->save();

            $this->responder(
                (string) $usuario->chat_id,
                "Describe tu problema jurídico con el mayor detalle posible.\n\nLexBot analizará el caso antes de registrarlo."
            );

            return response()->json(['ok' => true]);
        }

        if ($opcion === mb_strtolower(self::MENU_CASOS, 'UTF-8')) {
            $this->mostrarCasos($usuario);
            return response()->json(['ok' => true]);
        }

        if ($opcion === mb_strtolower(self::MENU_CITA, 'UTF-8')) {
            $this->mostrarCita($usuario);
            return response()->json(['ok' => true]);
        }

        if ($opcion === mb_strtolower(self::MENU_AYUDA, 'UTF-8')) {
            $this->mostrarMenu(
                $usuario,
                "Puedes registrar un caso, consultar tus casos o ver tu próxima cita."
            );

            return response()->json(['ok' => true]);
        }

        $this->mostrarMenu($usuario, 'Selecciona una opción del menú.');

        return response()->json(['ok' => true]);
    }

    private function registrarCasoTelegram(
        TelegramUser $usuario,
        string $descripcion,
        CaseAnalysisService $analizador,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        if (mb_strlen($descripcion, 'UTF-8') < 10) {
            $this->responder(
                (string) $usuario->chat_id,
                'Escribe una descripción más detallada para poder analizar el caso.'
            );

            return response()->json(['ok' => true]);
        }

        $cliente = $this->buscarClienteWeb($usuario);

        if (!$cliente) {
            $usuario->estado = 'final';
            $usuario->save();
            $this->mostrarMenu(
                $usuario,
                'No encontramos una cuenta Cliente con tu correo. Regístrala primero en la página web.'
            );

            return response()->json(['ok' => true]);
        }

        try {
            $analisis = $analizador->analizar($descripcion);
            $resultado = $flujo->crearCasoConCita($cliente, $descripcion, $analisis);
            $notificador->casoRegistrado($cliente, $resultado['caso'], $resultado['cita']);

            $usuario->estado = 'final';
            $usuario->save();

            $this->mostrarMenu(
                $usuario,
                "Caso registrado correctamente.\n\n"
                ."Especialidad: ".$resultado['caso']->especialidad."\n"
                ."Prioridad: ".$resultado['caso']->prioridad."\n"
                ."Abogado: ".$resultado['caso']->abogado."\n"
                ."Cita: ".$resultado['cita']->fecha.' '.substr((string) $resultado['cita']->hora, 0, 5)
            );
        } catch (Throwable $e) {
            Log::warning('No se pudo registrar caso desde Telegram: '.$e->getMessage());
            $usuario->estado = 'final';
            $usuario->save();
            $this->mostrarMenu($usuario, 'No se pudo registrar el caso: '.$e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    private function mostrarCasos(TelegramUser $usuario): void
    {
        $cliente = $this->buscarClienteWeb($usuario);

        if (!$cliente) {
            $this->mostrarMenu($usuario, 'No encontramos una cuenta Cliente vinculada a tu correo.');
            return;
        }

        $casos = Caso::query()
            ->where(function ($query) use ($cliente) {
                $query->where('cliente', $cliente->nombre);

                if (Schema::hasColumn('casos', 'cliente_email')) {
                    $query->orWhere('cliente_email', $cliente->email);
                }
            })
            ->latest('id')
            ->limit(5)
            ->get();

        if ($casos->isEmpty()) {
            $this->mostrarMenu($usuario, 'Todavía no tienes casos registrados.');
            return;
        }

        $detalle = $casos->map(function (Caso $caso, int $indice) {
            return ($indice + 1).'. '
                .$caso->especialidad
                .' · '
                .$caso->estado
                ."\nAbogado: "
                .$caso->abogado;
        })->implode("\n\n");

        $this->mostrarMenu($usuario, "Tus casos recientes:\n\n".$detalle);
    }

    private function mostrarCita(TelegramUser $usuario): void
    {
        $cliente = $this->buscarClienteWeb($usuario);

        if (!$cliente) {
            $this->mostrarMenu($usuario, 'No encontramos una cuenta Cliente vinculada a tu correo.');
            return;
        }

        $cita = Cita::query()
            ->where(function ($query) use ($cliente) {
                $query->where('cliente', $cliente->nombre);

                if (Schema::hasColumn('citas', 'cliente_email')) {
                    $query->orWhere('cliente_email', $cliente->email);
                }
            })
            ->whereNotIn('estado', ['finalizada', 'cancelada'])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->first();

        if (!$cita) {
            $this->mostrarMenu($usuario, 'No tienes una cita próxima registrada.');
            return;
        }

        $this->mostrarMenu(
            $usuario,
            "Tu próxima cita:\n\n"
            ."Fecha: ".$cita->fecha."\n"
            ."Hora: ".substr((string) $cita->hora, 0, 5)."\n"
            ."Abogado: ".$cita->abogado."\n"
            ."Especialidad: ".$cita->especialidad
        );
    }

    private function buscarClienteWeb(TelegramUser $usuario): ?User
    {
        return User::query()
            ->where('rol', 'cliente')
            ->whereRaw('LOWER(TRIM(email)) = ?', [
                strtolower(trim((string) $usuario->correo)),
            ])
            ->first();
    }

    private function mostrarMenu(TelegramUser $usuario, string $mensaje): void
    {
        $teclado = [
            [self::MENU_CASO, self::MENU_CASOS],
            [self::MENU_CITA, self::MENU_AYUDA],
        ];

        $this->responder((string) $usuario->chat_id, $mensaje, $teclado);
    }

    private function responder(string $chatId, string $mensaje, ?array $teclado = null): void
    {
        try {
            $datos = [
                'chat_id' => $chatId,
                'text' => $mensaje,
            ];

            if ($teclado) {
                $datos['reply_markup'] = json_encode([
                    'keyboard' => $teclado,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]);
            }

            Http::timeout(12)
                ->asForm()
                ->post(
                    'https://api.telegram.org/bot'
                    .config('services.telegram.bot_token')
                    .'/sendMessage',
                    $datos
                );
        } catch (Throwable $e) {
            Log::warning('Error al responder Telegram: '.$e->getMessage());
        }
    }
}
