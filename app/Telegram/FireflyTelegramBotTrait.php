<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Data\FireflyTransactionCreateData;
use App\Enums\ChatStateEnum;
use App\Enums\TransactionTypeEnum;
use App\Services\FireflyConnector;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Stringable;

trait FireflyTelegramBotTrait
{
    public function balance(string $months): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $date = new CarbonImmutable();
        if ($months) {
            $date = $date->subMonths((int) $months);
        }
        $msg = '<b>Состояние счёта за ' . $date->monthName . ' месяц</b>' . PHP_EOL;
        $balance = app(FireflyConnector::class)->getBalance($date);
        if ($balance) {
            if (isset($balance['balance-in-EUR'])) {
                $msg .= 'Баланс в евро: ' . $balance['balance-in-EUR']->value_parsed . PHP_EOL;
            }
            if (isset($balance['balance-in-RUB'])) {
                $msg .= 'Баланс в рублях: ' . $balance['balance-in-RUB']->value_parsed . PHP_EOL;
            }
            if (isset($balance['spent-in-EUR'])) {
                $msg .= 'Расходы в этом месяце в евро: ' . $balance['spent-in-EUR']->value_parsed . PHP_EOL;
            }
            if (isset($balance['spent-in-RUB'])) {
                $msg .= 'Расходы в этом месяце в рублях: ' . $balance['spent-in-RUB']->value_parsed . PHP_EOL;
            }
            if (isset($balance['earned-in-EUR'])) {
                $msg .= 'Заработано в этом месяце в евро: ' . $balance['earned-in-EUR']->value_parsed . PHP_EOL;
            }
            if (isset($balance['earned-in-RUB'])) {
                $msg .= 'Заработано в этом месяце в рублях: ' . $balance['earned-in-RUB']->value_parsed . PHP_EOL;
            }
            if (isset($balance['bills-paid-in-EUR'])) {
                $msg .= 'Оплачено по счетам в евро: ' . $balance['bills-paid-in-EUR']->value_parsed . PHP_EOL;
            }
            if (isset($balance['net-worth-in-EUR'])) {
                $msg .= 'Чистая прибыль (EUR): ' . $balance['net-worth-in-EUR']->value_parsed . PHP_EOL;
            }
            if (isset($balance['net-worth-in-RUB'])) {
                $msg .= 'Чистая прибыль (₽): ' . $balance['net-worth-in-RUB']->value_parsed . PHP_EOL;
            }
        }
        $this->reply($msg);
    }

    public function accounts(): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $msg = '<b>Доступные остатки по счетам:</b>' . PHP_EOL;
        $accounts = app(FireflyConnector::class)->getAccounts();
        foreach ($accounts as $key => $account) {
            if (in_array($account->attributes->type, ['initial-balance', 'revenue', 'expense'])) {
                continue;
            }
            $msg .= $account->id . '. ' . $account->attributes->name . ': ' . $account->attributes->current_balance . ' ' . $account->attributes->currency_symbol . PHP_EOL;
        }

        $this->reply($msg);
    }

    public function transactions(): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $msg = '<b>Операции за последниe несколько дней:</b>' . PHP_EOL;
        $totalEur = 0;
        $totalRub = 0;
        $transactions = app(FireflyConnector::class)->getTransactions();
        foreach ($transactions as $transaction) {
            foreach ($transaction->attributes->transactions as $transactionDetail) {
                $amount = (float) $transactionDetail->amount;
                if ($transactionDetail->currency_id === '1') {
                    $totalEur += $amount;
                }
                if ($transactionDetail->currency_id === '20') {
                    $totalRub += $amount;
                }
                $msg .= '- <b>' . $transactionDetail->description . '</b>: ' . number_format($amount, 2, ',', ' ') . ' ' . $transactionDetail->currency_symbol . '. ' . Carbon::parse($transactionDetail->date)->diffForHumans() . '. (ID: ' . $transaction->id . ')' . PHP_EOL;
            }
        }


        $msg .= PHP_EOL;
        $msg .= PHP_EOL . '<b>Итого в EUR</b>: ' . number_format($totalEur, 2, ',', ' ');
        $msg .= PHP_EOL . '<b>Итого в RUB</b>: ' . number_format($totalRub, 2, ',', ' ');
        $this->reply($msg);
    }

    public function categories(int|string|null $num): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $service = app(FireflyConnector::class);
        if ($num) {
            $num = (int) $num;
            $start = \Illuminate\Support\Carbon::now()->subMonths($num)->firstOfMonth()->format('Y-m-d');
            $end = \Illuminate\Support\Carbon::now()->subMonths($num)->endOfMonth()->format('Y-m-d');
            $categories = $service->getCategoriesStat($start, $end);
            $msg = '<b>Статистика по категориям за период ' . $start . ' - ' . $end . '</b>' . PHP_EOL;
        } else {
            $categories = $service->getCategoriesStat();
            $msg = '<b>Статистика по категориям за текущий месяц</b>' . PHP_EOL;
        }
        $totalRub = 0;
        $totalEur = 0;
        foreach ($categories as $category) {
            $expense = $category->difference_float * -1;
            if ($expense > 0) {
                $msg .= '- <b>' . $category->name . '</b>: ' . number_format($expense, 2, ',', ' ') . ' ' . $category->currency_code . PHP_EOL;
                if ($category->currency_id === '1') {
                    $totalEur += $expense;
                }
                if ($category->currency_id === '20') {
                    $totalRub += $expense;
                }
            }
        }

        $msg .= PHP_EOL;
        $msg .= PHP_EOL . '<b>Итого в EUR</b>: ' . number_format($totalEur, 2, ',', ' ');
        $msg .= PHP_EOL . '<b>Итого в RUB</b>: ' . number_format($totalRub, 2, ',', ' ');
        $this->reply($msg);
    }

    public function budgets(int|string|null $num): void
    {
        $service = app(FireflyConnector::class);
        if ($num) {
            $num = (int) $num;
            $start = \Illuminate\Support\Carbon::now()->subMonths($num)->firstOfMonth()->format('Y-m-d H:i:s');
            $end = \Illuminate\Support\Carbon::now()->subMonths($num)->endOfMonth()->format('Y-m-d H:i:s');
            $budgets = $service->getBudgetStat($start, $end);
            $msg = '<b>Статистика по бюджетам за период ' . $start . ' - ' . $end . '</b>' . PHP_EOL;
        } else {
            $budgets = $service->getBudgetStat();
            $msg = '<b>Статистика по бюджетам за текущий месяц</b>' . PHP_EOL;
        }
        foreach ($budgets as $budget) {
            $amountVal = $budget->difference_float * -1;
            $msg .= $budget->id . '. ' . $budget->name . ': ' . number_format($amountVal, 2, ',', ' ') . ' ' . $budget->currency_code . PHP_EOL;
        }

        $this->reply($msg);
    }

    public function delete(string $id): void
    {
        app(FireflyConnector::class)->deleteTransaction((int) $id);
        $this->reply('Транзакция успешно удалена');
    }

    public function expense(): void
    {
        $this->chat->state = ChatStateEnum::ASK_TRANSACTION_AMOUNT;
        $this->chat->context = [];
        $this->chat->save();
        $this->reply('Введите сумму расхода');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    protected function askTransactionAmount(Stringable $text, ?array $context): void
    {
        $this->chat->state = ChatStateEnum::ASK_TRANSACTION_DESCRIPTION;
        $context['amount'] = (float) $text->value();
        $this->chat->context = $context;
        $this->chat->save();
        $this->reply('Опишите, на что вы потратили деньги');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    protected function askTransactionDescription(Stringable $text, ?array $context): void
    {
        $this->chat->state = ChatStateEnum::ASK_TRANSACTION_ACCOUNT;
        $context['description'] = $text->value();
        $this->chat->context = $context;
        $this->chat->save();
        $accounts = app(FireflyConnector::class)->getAccountValuesByType('asset');
        $buttons = [];
        foreach ($accounts as $id => $account) {
            $buttons[] = ReplyButton::make($id . '|' . $account);
        }
        $this->chat->message('С какого счёта списать деньги?')->replyKeyboard(ReplyKeyboard::make()->buttons($buttons)->oneTime())->send();
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    protected function askTransactionAccount(Stringable $text, ?array $context): void
    {
        $accounts = explode('|', $text->value());
        if (count($accounts) < 2) {
            $this->chat->message('Введите корректный счёт')->send();

            return;
        }
        $this->chat->state = ChatStateEnum::ASK_TRANSACTION_RECIPIENT;
        $context['source_id'] = (int) $accounts[0];
        $this->chat->context = $context;
        $this->chat->save();
        $accounts = app(FireflyConnector::class)->getAccountValuesByType('expense');
        $buttons = [];
        foreach ($accounts as $id => $account) {
            $buttons[] = ReplyButton::make($id . '|' . $account);
        }
        $this->chat->message('На какой счёт списать деньги?')->replyKeyboard(ReplyKeyboard::make()->buttons($buttons)->oneTime()->chunk(2))->send();
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    protected function askTransactionRecipient(Stringable $text, ?array $context): void
    {
        $accounts = explode('|', $text->value());
        if (count($accounts) < 2) {
            $this->chat->message('Введите корректный счёт')->send();

            return;
        }
        $this->chat->state = ChatStateEnum::ASK_TRANSACTION_CATEGORY;
        $context['destination_id'] = (int) $accounts[0];
        $this->chat->context = $context;
        $this->chat->save();
        $categories = app(FireflyConnector::class)->getCategories();
        $buttons = [];
        foreach ($categories as $category) {
            $buttons[] = ReplyButton::make($category->id . '|' . $category->attributes->name);
        }
        $this->chat->message('На какой категории учесть деньги?')->replyKeyboard(ReplyKeyboard::make()->buttons($buttons)->oneTime()->chunk(2))->send();
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    protected function askTransactionCategory(Stringable $text, ?array $context): void
    {
        $accounts = explode('|', $text->value());
        if (count($accounts) < 2) {
            $this->chat->message('Введите корректную категорию')->send();

            return;
        }
        $this->chat->state = ChatStateEnum::ASK_TRANSACTION_BUDGET;
        $context['category_id'] = (int) $accounts[0];
        $this->chat->context = $context;
        $this->chat->save();
        $budgets = app(FireflyConnector::class)->getBudgets();
        $buttons = [
            ReplyButton::make('0|Без бюджета'),
        ];
        foreach ($budgets as $budget) {
            $buttons[] = ReplyButton::make($budget->id . '|' . $budget->attributes->name);
        }
        $this->chat->message('На какой бюджет учесть деньги?')->replyKeyboard(ReplyKeyboard::make()->buttons($buttons)->oneTime()->chunk(2))->send();
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    protected function askTransactionBudget(Stringable $text, ?array $context): void
    {
        $accounts = explode('|', $text->value());
        if (count($accounts) < 2) {
            $this->chat->message('Введите корректный бюджет')->send();

            return;
        }
        $this->chat->state = ChatStateEnum::ACTIVE;
        $this->chat->context = [];
        $this->chat->save();
        $budgetId = (int) $accounts[0];
        if (! $budgetId) {
            $context['budget_id'] = null;
        } else {
            $context['budget_id'] = $budgetId;
        }
        $context['type'] = TransactionTypeEnum::WITHDRAWAL;
        $transaction = app(FireflyConnector::class)->sendTransaction(FireflyTransactionCreateData::from($context));

        $this->chat->message('Транзакция успешно создана под номером ' . $transaction)->removeReplyKeyboard()->send();
    }
}
