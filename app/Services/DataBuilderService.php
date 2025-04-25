<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TelegraphChat;

final class DataBuilderService
{
    public function generateMessage(array $submissionData, string $website = 'itvolga'): string // @phpstan-ignore-line
    {
        $mauticData = $submissionData['results'];
        $msg = '<b>Новый подписчик на сайт ' . $website . '!</b>';
        if (isset($submissionData['mautic.form_on_submit']['timestamp'])) {
            $msg .= PHP_EOL . 'Date' . ': ' . $submissionData['mautic.form_on_submit']['timestamp'];
        } elseif (isset($submissionData['mautic.form_on_submit'][0]['timestamp'])) {
            $msg .= PHP_EOL . 'Date' . ': ' . $submissionData['mautic.form_on_submit'][0]['timestamp'];
        }
        $msg .= PHP_EOL . 'Email' . ': ' . $mauticData['email'];
        if (isset($mauticData['phone'])) {
            $msg .= PHP_EOL . 'Phone' . ': ' . $mauticData['phone'];
        }
        if (isset($mauticData['f_name'])) {
            $msg .= PHP_EOL . 'Name' . ': ' . $mauticData['f_name'];
        }
        if (isset($mauticData['subject'])) {
            $msg .= PHP_EOL . 'Subject' . ': ' . $mauticData['subject'];
        }
        if (isset($mauticData['comment'])) {
            $msg .= PHP_EOL . 'Subject' . ': ' . $mauticData['comment'];
        }
        if (isset($mauticData['ip'])) {
            $msg .= PHP_EOL . 'IP address' . ': ' . $mauticData['ip'];
        }
        if (isset($mauticData['country'])) {
            $msg .= PHP_EOL . 'Country' . ': ' . $mauticData['country'];
        }
        if (isset($mauticData['zipcode'])) {
            $msg .= PHP_EOL . 'Zipcode' . ': ' . $mauticData['zipcode'];
        }
        if (isset($mauticData['referer'])) {
            $msg .= PHP_EOL . 'Referer' . ': ' . $mauticData['referer'];
        }
        if (! isset($mauticData['referer']) && ! isset($mauticData['phone'])) {
            throw new \DomainException('Not all data provided for chatbot', 422);
        }

        return $msg;
    }

    public function sendMessage(string $message): void
    {
        $chat = TelegraphChat::firstOrFail();
        $chat->message($message)->send();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function validateData(array $data): void
    {
        if (! isset($data['mautic.form_on_submit'])) {
            throw new \DomainException('No data!');
        }
    }
}
