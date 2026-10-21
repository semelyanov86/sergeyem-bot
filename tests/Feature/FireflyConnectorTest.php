<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\FireflySummaryBasicData;
use App\Services\FireflyConnector;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FireflyConnectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.firefly.server' => 'https://firefly.test/api/v1',
            'services.firefly.token' => 'test-token',
        ]);
    }

    public function test_get_balance_parses_summary_without_local_icon(): void
    {
        Http::fake([
            'firefly.test/api/v1/summary/basic*' => Http::response([
                'balance-in-EUR' => $this->summaryItem('balance-in-EUR', '1 234,56 €'),
                'spent-in-EUR' => $this->summaryItem('spent-in-EUR', '-100,00 €') + ['no_available_budgets' => true],
            ]),
        ]);

        $balance = resolve(FireflyConnector::class)->getBalance(CarbonImmutable::parse('2026-10-21'));

        $this->assertCount(2, $balance);
        $this->assertInstanceOf(FireflySummaryBasicData::class, $balance['balance-in-EUR']);
        $this->assertSame('1 234,56 €', $balance['balance-in-EUR']->value_parsed);
        $this->assertNull($balance['balance-in-EUR']->local_icon);
        $this->assertTrue($balance['spent-in-EUR']->no_available_budgets);

        Http::assertSent(fn (Request $request): bool => $request['start'] === '2026-10-01'
            && $request['end'] === '2026-10-31');
    }

    public function test_get_balance_keeps_local_icon_when_present(): void
    {
        Http::fake([
            'firefly.test/api/v1/summary/basic*' => Http::response([
                'balance-in-EUR' => $this->summaryItem('balance-in-EUR', '10,00 €') + ['local_icon' => 'balance-scale'],
            ]),
        ]);

        $balance = resolve(FireflyConnector::class)->getBalance(CarbonImmutable::parse('2026-10-21'));

        $this->assertSame('balance-scale', $balance['balance-in-EUR']->local_icon);
    }

    public function test_get_balance_throws_exception_on_error(): void
    {
        Http::fake([
            'firefly.test/api/v1/summary/basic*' => Http::response('Unauthorized', 401),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not get firefly balance:');

        resolve(FireflyConnector::class)->getBalance(CarbonImmutable::parse('2026-10-21'));
    }

    /**
     * Mirrors an entry of the Firefly III `/summary/basic` response, which no longer carries `local_icon`.
     *
     * @return array<string, scalar>
     */
    private function summaryItem(string $key, string $valueParsed): array
    {
        return [
            'key' => $key,
            'title' => 'Balance (€)',
            'monetary_value' => '1234.56',
            'currency_id' => '1',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'currency_decimal_places' => 2,
            'value_parsed' => $valueParsed,
            'sub_title' => '',
        ];
    }
}
