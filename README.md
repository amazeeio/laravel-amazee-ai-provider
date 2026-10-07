# amazee.ai Provider for the Laravel AI SDK

Adds [amazee.ai](https://amazee.ai), the private, data-sovereign AI platform from amazee.io, as a provider for [`laravel/ai`](https://github.com/laravel/ai). Each amazee.ai region is a LiteLLM endpoint, so you point the provider at your region and use agents, streaming, tools, structured output, embeddings, images and the rest of the SDK as normal.

## Installation

```bash
composer require amazeeio/laravel-amazee-ai-provider
```

Get your region endpoint and LLM token from [my.amazee.io](https://my.amazee.io), then add them to `.env`:

```dotenv
AMAZEE_ENDPOINT_URL=https://llm.us103.amazee.ai/v1
AMAZEE_LLM_TOKEN=sk-...
```

That registers an `amazeeio` provider. Use it directly, or make it the default in `config/ai.php`:

```php
use function Laravel\Ai\agent;

agent()->prompt('Hello', provider: 'amazeeio');
```

```php
'default' => 'amazeeio',
'default_for_images' => 'amazeeio',
'default_for_embeddings' => 'amazeeio',
```

## Configuration

To change models or add more regions, define providers with the `amazeeio` driver in `config/ai.php`. A provider named `amazeeio` there replaces the default one.

```php
'providers' => [
    'amazeeio' => [
        'driver' => 'amazeeio',
        'url' => env('AMAZEE_ENDPOINT_URL'),
        'key' => env('AMAZEE_LLM_TOKEN'),
        'models' => [
            'text' => ['default' => 'chat', 'cheapest' => 'chat', 'smartest' => 'claude-4-6-opus'],
            'embeddings' => ['default' => 'embeddings', 'dimensions' => 1024],
            'image' => ['default' => 'text_to_image'],
            'audio' => ['default' => 'tts-1'],
            'transcription' => ['default' => 'whisper-1'],
        ],
    ],

    'amazeeio-eu' => [
        'driver' => 'amazeeio',
        'url' => env('AMAZEE_EU_ENDPOINT_URL'),
        'key' => env('AMAZEE_EU_LLM_TOKEN'),
    ],
],
```

## Capabilities

| Capability            | Endpoint                | Default model   |
| --------------------- | ----------------------- | --------------- |
| Text, tools, streaming, structured output | `chat/completions` | `chat` |
| Embeddings            | `embeddings`            | `embeddings`    |
| Image generation      | `images/generations`    | `text_to_image` |
| Text to speech        | `audio/speech`          | none, configure `models.audio.default` |
| Transcription         | `audio/transcriptions`  | none, configure `models.transcription.default` |

`chat`, `embeddings` and `text_to_image` are the aliases every amazee.ai region defines. Any other model in your region's catalog can be passed by name. Provider-specific options are keyed by `amazeeio`.

## Testing

```bash
composer test
```

## License

MIT
