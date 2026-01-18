<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Data\CreateWordData;
use App\Enums\ChatStateEnum;
use App\Services\EasywordsConnector;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Stringable;

trait EasywordsTelegramTrait
{
    public function random(string $number): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $msg = 'Список случайных слов 👇' . PHP_EOL;
        if (! $number) {
            $number = 10;
        }
        $number = (int) $number;
        $words = resolve(EasywordsConnector::class)->getRandomWords($number);
        foreach ($words as $key => $word) {
            $msg .= $key + 1 . '. <b>' . $word->attributes->original . '</b> - ' . $word->attributes->translated . PHP_EOL;
        }
        $this->reply($msg);
    }

    public function search(string $query): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $msg = 'Список найденных слов 👇' . PHP_EOL;

        $words = resolve(EasywordsConnector::class)->searchWords($query);
        foreach ($words as $key => $word) {
            $msg .= $key + 1 . '. <b>' . $word->attributes->original . '</b> - ' . $word->attributes->translated . PHP_EOL;
        }
        $this->reply($msg);
    }

    public function saveword(): void
    {
        $this->chat->state = ChatStateEnum::ASK_WORD_ORIGINAL;
        $this->chat->context = [];
        $this->chat->save();
        $this->reply('Запускаем процесс сохранения нового слова для изучения.' . PHP_EOL . 'Введите оригинальное значение слова.');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    public function askWordOriginal(Stringable $text, ?array $context): void
    {
        if (! $text->value()) {
            $this->reply('Введите корректное значение');

            return;
        }
        $this->chat->state = ChatStateEnum::ASK_WORD_TRANSLATION;
        $context['original'] = $text->value();
        $this->chat->context = $context;
        $this->chat->save();
        $this->reply('Введите перевод слова:');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    public function askWordTranslation(Stringable $text, ?array $context): void
    {
        if (! $text->value()) {
            $this->reply('Введите корректное значение');

            return;
        }
        $this->chat->state = ChatStateEnum::ASK_WORD_LANGUAGE;
        $context['translated'] = $text->value();
        $this->chat->context = $context;
        $this->chat->save();
        $this->chat->message('Выберите язык оригинала:')->replyKeyboard(ReplyKeyboard::make()->buttons([
            ReplyButton::make('DE'),
            ReplyButton::make('EN'),
        ])->oneTime())->send();
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    public function askWordLanguage(Stringable $text, ?array $context): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        if (! $context) {
            $this->chat->message('Некорректное значение контекста. Начните процесс заново')->removeReplyKeyboard()->send();

            return;
        }
        if (! $text->value()) {
            $this->chat->message('Введите корректное значение')->removeReplyKeyboard()->send();

            return;
        }
        $this->chat->state = ChatStateEnum::ACTIVE;
        $this->chat->context = [];
        $this->chat->save();
        $result = resolve(EasywordsConnector::class)->saveWord(CreateWordData::from($context));
        $this->chat->message('Слово успешно сохранено под идентификатором ' . $result->id)->removeReplyKeyboard()->send();
    }
}
