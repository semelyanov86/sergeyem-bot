<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\WebsiteChecker;
use Illuminate\Console\Command;

final class DebugCommand extends Command
{
    protected $signature = 'debug';

    protected $description = 'Run custom code';

    public function handle(): void
    {
        $websites = [
            'https://sergeyem.ru' => 'WEB-разработчик, внедрение CRM. Программист Laravel',
            'https://sergeyem.eu' => 'Ein erfahrener Webentwickler',
            'https://cloud.sergeyem.ru/index.php/login' => '<a href="https://owncloud.com" target="_blank" rel="noreferrer">ownCloud</a>',
            'https://keys.sergeyem.ru:8443/#/login' => '<i class="bwi bwi-spinner bwi-spin bwi-3x tw-text-muted" title="Loading" aria-hidden="true"></i>',
            'https://itvolga.com' => 'Вам не нужно платить за лицензии и абонентскую плату.',
            'https://creditcoop.ru' => 'Кредитная кооперация Чувашии: кредитные кооперативы и союзы',
            'https://mautic.sergeyem.ru/s/login' => 'keep me logged in',
            'https://mautic.itvolga.com/s/login' => 'keep me logged in',
        ];
        foreach ($websites as $link => $website) {
            $result = WebsiteChecker::run($link, $website);
            ray($result);
        }
        $this->info('Command executed successfully');
    }
}
