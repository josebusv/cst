<?php

namespace App\Logging;

use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * Envía las alertas a un webhook HTTP genérico (Discord, Google Chat,
 * Microsoft Teams o cualquier endpoint propio). Alternativa a Slack.
 * Nunca relanza errores para no romper la respuesta ni provocar bucles.
 */
class WebhookHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly ?string $url = null,
        int|string|Level $level = Level::Error,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->url) {
            return;
        }

        try {
            Http::timeout(5)->asJson()->post($this->url, [
                'level' => $record->level->getName(),
                'message' => $record->message,
                'context' => $record->context,
                'channel' => $record->channel,
                'time' => $record->datetime->format(DATE_ATOM),
            ]);
        } catch (Throwable) {
            // Silencio intencional: una alerta no debe romper la aplicación.
        }
    }
}
