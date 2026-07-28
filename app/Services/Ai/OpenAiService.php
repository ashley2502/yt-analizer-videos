<?php

namespace App\Services\Ai;

use OpenAI;
use OpenAI\Client;
use RuntimeException;

class OpenAiService
{
    public function enabled(): bool
    {
        return (bool) config('ai.api_key');
    }

    public function client(): Client
    {
        if (! $this->enabled()) {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $factory = OpenAI::factory()
            ->withApiKey(config('ai.api_key'))
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 90]));

        if ($baseUrl = config('ai.base_url')) {
            $factory = $factory->withBaseUri(rtrim($baseUrl, '/'));
        }

        return $factory->make();
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<string, mixed>
     */
    public function chatJson(array $messages, ?string $model = null): array
    {
        $response = $this->client()->chat()->create([
            'model' => $model ?? config('ai.model'),
            'response_format' => ['type' => 'json_object'],
            'messages' => $messages,
            'temperature' => 0.7,
        ]);

        $content = $response->choices[0]->message->content ?? '{}';
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI returned invalid JSON.');
        }

        return $decoded;
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<string, mixed>
     */
    public function visionJson(array $messages): array
    {
        return $this->chatJson($messages, config('ai.vision_model'));
    }
}
