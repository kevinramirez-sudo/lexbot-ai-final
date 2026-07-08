<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public static function enviarAlChat(string $chatId, string $mensaje): bool
    {
        $token = (string) config('services.telegram.bot_token');

        if ($token === '' || trim($chatId) === '') {
            Log::info('Telegram no configurado o chat_id vacío.');
            return false;
        }

        try {
            $respuesta = Http::timeout(12)
                ->asForm()
                ->post(
                    'https://api.telegram.org/bot'.$token.'/sendMessage',
                    [
                        'chat_id' => $chatId,
                        'text' => $mensaje,
                    ]
                );

            if (!$respuesta->successful()) {
                Log::warning(
                    'Telegram respondió con error '
                    .$respuesta->status().': '
                    .$respuesta->body()
                );
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Error al enviar Telegram: '.$e->getMessage());
            return false;
        }
    }

    public static function enviarAlAdmin(string $mensaje): bool
    {
        $chatId = (string) config('services.telegram.chat_id');

        return $chatId !== ''
            ? self::enviarAlChat($chatId, $mensaje)
            : false;
    }

    public static function enviarAlCliente(string $correo, string $mensaje): bool
    {
        $telegramUser = TelegramUser::query()
            ->whereRaw('LOWER(TRIM(correo)) = ?', [
                strtolower(trim($correo)),
            ])
            ->where('estado', 'final')
            ->latest('id')
            ->first();

        if (!$telegramUser) {
            Log::info('Cliente sin Telegram registrado: '.strtolower(trim($correo)));
            return false;
        }

        return self::enviarAlChat((string) $telegramUser->chat_id, $mensaje);
    }

    public static function enviarAlClientePorNombre(string $nombre, string $mensaje): bool
    {
        $cliente = User::query()
            ->where('rol', 'cliente')
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [
                strtolower(trim($nombre)),
            ])
            ->latest('id')
            ->first();

        if (!$cliente) {
            Log::info('No se encontró cliente para Telegram: '.$nombre);
            return false;
        }

        return self::enviarAlCliente($cliente->email, $mensaje);
    }

    public static function enviar(string $mensaje): bool
    {
        return self::enviarAlAdmin($mensaje);
    }
}
