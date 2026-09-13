<?php

namespace App\Logging;

use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * Envía las alertas a un chat de Telegram. Alternativa gratuita a Slack,
 * sin dependencias ni cuentas de pago. Nunca relanza errores para no
 * romper la respuesta ni provocar bucles de logging.
 */
class TelegramHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly ?string $botToken = null,
        private readonly ?string $chatId = null,
        int|string|Level $level = Level::Error,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->botToken || !$this->chatId) {
            return;
        }

        try {
            Http::timeout(5)->asJson()->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'chat_id' => $this->chatId,
                    'text' => $this->formatear($record),
                    'disable_web_page_preview' => true,
                ]
            );
        } catch (Throwable) {
            // Silencio intencional: una alerta no debe romper la aplicación.
        }
    }

    private function formatear(LogRecord $record): string
    {
        $contexto = $record->context;
        unset($contexto['stack']);

        $linea = "[{$record->level->getName()}] {$record->message}";
        if (!empty($contexto)) {
            $linea .= "\n" . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $linea;
    }
}
