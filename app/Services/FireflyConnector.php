<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\FireflyAccountData;
use App\Data\FireflyBudgetData;
use App\Data\FireflyCategoryData;
use App\Data\FireflyCategoryExpenseData;
use App\Data\FireflyCurrencyData;
use App\Data\FireflySummaryBasicData;
use App\Data\FireflyTransactionCreateData;
use App\Data\TransactionData;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class FireflyConnector
{
    /**
     * @return FireflyCategoryData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getCategories(): array
    {
        $response = $this->getRequest()->get($this->getServer() . '/categories');

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly categories: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return FireflyCategoryData::collect($response->json('data'), 'array');
    }

    public function deleteTransaction(int $id): void
    {
        $response = $this->getRequest()->delete($this->getServer() . '/transactions/' . $id);
        if ($response->clientError() || $response->serverError()) {
            throw new \DomainException('Can not delete transaction: ' . $response->body());
        }
    }

    /**
     * @return FireflyBudgetData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getBudgets(): array
    {
        $response = $this->getRequest()->get($this->getServer() . '/budgets');

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly budgets: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return FireflyBudgetData::collect($response->json('data'), 'array');
    }

    /**
     * @return FireflyAccountData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getAccounts(): array
    {
        $response = $this->getRequest()->get($this->getServer() . '/accounts?limit=100');

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly accounts: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return FireflyAccountData::collect($response->json('data'), 'array');
    }

    /**
     * @return FireflyCurrencyData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getCurrencies(): array
    {
        $response = $this->getRequest()->get($this->getServer() . '/currencies');

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly accounts: ' . $response->body());
        }
        $result = [];
        // @phpstan-ignore-next-line
        foreach ($response->json('data') as $item) {
            // @phpstan-ignore-next-line
            if ($item['attributes']['enabled']) {
                $result[] = FireflyCurrencyData::from($item);
            }
        }

        return $result;
    }

    /**
     * @return array<non-empty-string, FireflySummaryBasicData>
     */
    public function getBalance(CarbonImmutable $carbon): array
    {
        $response = $this->getRequest()->get($this->getServer() . '/summary/basic', [
            'start' => $carbon->startOfMonth()->format('Y-m-d'),
            'end' => $carbon->endOfMonth()->format('Y-m-d'),
        ]);

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly balance: ' . $response->body());
        }
        $result = [];
        // @phpstan-ignore-next-line
        foreach ($response->json() as $key => $item) {
            if ($key) {
                $result[$key] = FireflySummaryBasicData::from($item);
            }
        }

        return $result;
    }

    /**
     * @return TransactionData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getTransactions(): array
    {
        $response = $this->getRequest()->get($this->getServer() . '/transactions', [
            'start' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'end' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly transactions: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return TransactionData::collect($response->json('data'), 'array');
    }

    /**
     * @return FireflyCategoryExpenseData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getCategoriesStat(?string $start = null, ?string $end = null): array
    {
        if (! $start) {
            $start = Carbon::now()->firstOfMonth()->format('Y-m-d');
        }
        if (! $end) {
            $end = Carbon::now()->format('Y-m-d');
        }

        $response = $this->getRequest()->get($this->getServer() . '/insight/expense/category', [
            'start' => $start,
            'end' => $end,
        ]);

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly transactions: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return FireflyCategoryExpenseData::collect($response->json(), 'array');
    }

    /**
     * @return FireflyCategoryExpenseData[]
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getBudgetStat(?string $start = null, ?string $end = null): array
    {
        if (! $start) {
            $start = Carbon::now()->firstOfMonth()->format('Y-m-d');
        }
        if (! $end) {
            $end = Carbon::now()->format('Y-m-d');
        }

        $response = $this->getRequest()->get($this->getServer() . '/insight/expense/budget', [
            'start' => $start,
            'end' => $end,
        ]);

        if (! $response->ok()) {
            throw new \DomainException('Can not get firefly budgets stat: ' . $response->body());
        }

        // @phpstan-ignore-next-line
        return FireflyCategoryExpenseData::collect($response->json(), 'array');
    }

    /**
     * @return array<string, string>
     */
    public function getAccountValuesByType(string $type): array
    {
        $accounts = $this->getAccounts();
        $result = [];

        foreach ($accounts as $account) {
            if ($account->attributes->type === $type) {
                $result[$account->id] = $account->attributes->name;
            }
        }

        return $result;
    }

    public function sendTransaction(FireflyTransactionCreateData $data): int
    {
        $response = $this->getRequest()->post($this->getServer() . '/transactions', ['transactions' => [$data->toArray()]]);
        if ($response->ok()) {
            $res = $response->json();

            // @phpstan-ignore-next-line
            return (int) $res['data']['id'];
        }

        return 0;
    }

    public function convertAmount(string $amount): float
    {
        $sums = explode('+', $amount);
        if (count($sums) < 2) {
            $converted = (float) $amount;
        } else {
            $value = 0;
            foreach ($sums as $sum) {
                $value += (float) $sum;
            }
            $converted = (float) $value;
        }

        return $converted;
    }

    protected function getServer(): string
    {
        /** @var string $server */
        $server = config('services.firefly.server');

        return $server;
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.firefly.token');

        return Http::withToken($token)->acceptJson()->asJson();
    }
}
