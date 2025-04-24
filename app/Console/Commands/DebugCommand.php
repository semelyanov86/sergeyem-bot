<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FireflyConnector;
use Illuminate\Console\Command;

final class DebugCommand extends Command
{
    protected $signature = 'debug';

    protected $description = 'Run custom code';

    public function handle(): void
    {
        ray(app(FireflyConnector::class)->getCategoriesStat());
        $this->info('Command executed successfully');
    }
}
