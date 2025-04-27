<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Enums\ChatStateEnum;
use App\Services\WebsiteConnector;
use Illuminate\Support\Stringable;

trait GeneralInfoTrait
{
    public function start(): void
    {
        $this->reply('Пользоваться ботом может только предварительно одобренный пользователь. Свяжитесь с администратором.');
    }

    public function help(): void
    {
        $this->reply('Этот бот может взаимодействовать с различными сервисами, такими как LinkAce, EasyList, EasyWords.

Он может сохранять ссылки в сервис LinkAce. Также он может вывести последние сохранённые ссылки.

Чтобы сохранить ссылку, просто отправьте её мне командой /link. Ссылка должна начинаться с /link https:// 🔗

Чтобы получить последние сохранённые ссылки, отправьте команду /links . В качестве аргумента вы можете передать количество ссылок, которые вы хотели бы видеть в сообщении. По умолчанию 6 ссылок. 🌎

Чтобы получить ссылки из определённого списка, используйте команду /listlinks. Затем выберите интересующий вас список. 🌍

Чтобы посмотреть списки, введите команду /lists . 📂

Хотите начать учить новые слова? Отправьте команду /random, которая также принимает в качестве параметра количество слов для изучения. Система получит слова в рандомном порядке из сервиса EasyWords. Если в настройках EasyWords указано, что изученные слова скрываются, их в выдаче не будет.

Чтобы сохранить слово, наберите команду /saveword . После этого мы спросим вас оригинальное значение слова, его перевод и язык.

Для получения информации по вашему пользователю (часто нужно для интеграции со сторонними системами), отправьте команду /me 😎

Для получения всех список сервиса EasyList, нужно передать команду /buylists , которая принимает в качестве параметра количество элементов в сообщении

Для получения списка покупок из списка, вызовите команду /items , в которую необходимо передать ID списка

Чтобы узнать состояние баланса по счетам в Firefly, введите /balance. В качестве параметра можно передать отступ на количество месяцев назад. Например, чтобы получить баланс за предыдущий месяц, введите /balance 1.

Для получения остатков по счетам в Firefly, существует команда /accounts.

Для вывода последних финансовых трансакций есть команда /transactions.

Чтобы посмотреть финансовую статистику по категориям, введите команду /categories. В качестве параметра она также принимает отступ, цифра, в количестве месяцев. Например /categories 1 - даёт статистику за прошлый месяц.

Аналогичным образом работает команда /budgets.

Запуск процесса создания новой транзакции происходит посредством команды /expense.

Чтобы удалить транзакцию, введите команду /delete, передав в качестве параметра номер транзакции.

Вы всегда можете вернуться в главное меню при помощи команды /cancel . 🔙');
    }

    public function cancel(): void
    {
        $this->chat->state = ChatStateEnum::ACTIVE;
        $this->chat->context = [];
        $this->chat->save();
        $this->chat->message('Контекст пользователя успешно сброшен')->removeReplyKeyboard()->send();
    }

    public function me(): void
    {
        $service = app(WebsiteConnector::class);
        $profile = $service->getProfileInfo();
        $msg = 'Данные по текущему пользователю:' . PHP_EOL;
        $msg .= '- Имя: ' . $profile->name . PHP_EOL;
        $msg .= '- Email: ' . $profile->email . PHP_EOL;
        $msg .= '- Номер телефона: ' . $profile->phone . PHP_EOL;
        $msg .= '- Основной телефон: ' . $profile->main_phone . PHP_EOL;
        $msg .= '- VK: ' . $profile->vk . PHP_EOL;
        $msg .= '- Youtube: ' . $profile->youtube . PHP_EOL;
        $msg .= '- Facebook: ' . $profile->facebook . PHP_EOL;
        $msg .= '- Адрес: ' . $profile->address . PHP_EOL;
        $msg .= '- TGID: ' . $profile->telegram_id . PHP_EOL;
        $msg .= '- Telegram: ' . $profile->telegram_login . PHP_EOL;
        $this->reply($msg);
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    protected function active(Stringable $text, ?array $context): void
    {
        $this->chat->message('Подобное взаимодействие с ботом не поддерживается. Для получения списка доступных команд, введите /help.')->removeReplyKeyboard()->send();
    }
}
