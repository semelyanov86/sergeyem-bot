<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Services\ClaudeConnector;
use DefStudio\Telegraph\Enums\ChatActions;

trait ClaudeTelegramTrait
{
    public function ask(string $parameter): void
    {
        $document = $this->message?->document();
        $prompt = trim($parameter);

        if ($prompt === '' && $document === null) {
            $this->reply('Использование: /ask <ваш вопрос>' . PHP_EOL . 'Также можно прикрепить файл к сообщению.');

            return;
        }

        $this->chat->action(ChatActions::TYPING)->send();

        $filePath = null;
        $filename = null;

        try {
            if ($document !== null) {
                $filePath = $this->bot->store($document, storage_path('app/temp'));
                $filename = $document->filename();
            }

            $result = resolve(ClaudeConnector::class)->ask($prompt, $filePath, $filename);

            $this->reply($result);
        } catch (\Throwable $e) {
            $this->reply('Ошибка при обращении к Claude: ' . $e->getMessage());
        } finally {
            if ($filePath !== null && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
