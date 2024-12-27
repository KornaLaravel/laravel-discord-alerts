<?php

namespace Spatie\DiscordAlerts;

class DiscordAlert
{
    protected string $webhookUrlName = 'default';

    protected int $delay = 0; // minutes

    public function to(string $webhookUrlName): self
    {
        $this->webhookUrlName = $webhookUrlName;

        return $this;
    }

    public function delayMinutes(int $minutes = 0){
        $this->delay = $minutes;

        return $this;
    }

    public function delayHours(int $hours = 0){
        $this->delay = $hours * 60;

        return $this;
    }

    public function message(string $text, array $embeds = []): void
    {
        $webhookUrl = Config::getWebhookUrl($this->webhookUrlName);

        $text = $this->parseNewline($text);

        foreach ($embeds as $key => $embed) {
            if (array_key_exists('description', $embed)) {
                $embeds[$key]['description'] = $this->parseNewline($embeds[$key]['description']);
            }

            if (array_key_exists('color', $embed)) {
                $embeds[$key]['color'] = hexdec(str_replace('#', '', $embed['color'])) ;
            }
        }

        $jobArguments = [
            'text' => $text,
            'webhookUrl' => $webhookUrl,
            'embeds' => $embeds,
        ];

        $job = Config::getJob($jobArguments);

        dispatch($job)->delay(now()->addMinutes($this->delay))->onConnection(Config::getConnection());
    }

    private function parseNewline(string $text): string
    {
        return str_replace('\n', PHP_EOL, $text);
    }
}
