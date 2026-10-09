<?php

namespace Amazee\LaravelAi;

use InvalidArgumentException;
use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Gateway\ImageGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Contracts\Providers\ImageProvider;
use Laravel\Ai\Gateway\OpenAi\OpenAiGateway;
use Laravel\Ai\Providers\Concerns;
use Laravel\Ai\Providers\OpenAiCompatibleProvider;

/**
 * amazee.ai regions are LiteLLM proxies: text, embeddings and transcription go over
 * Chat Completions; images and speech use the OpenAI-shaped endpoints LiteLLM exposes.
 */
class AmazeeAiProvider extends OpenAiCompatibleProvider implements AudioProvider, ImageProvider
{
    use Concerns\GeneratesAudio;
    use Concerns\GeneratesImages;
    use Concerns\HasAudioGateway;
    use Concerns\HasImageGateway;

    /**
     * Get the provider's image gateway.
     */
    public function imageGateway(): ImageGateway
    {
        return $this->imageGateway ??= new OpenAiGateway($this->events);
    }

    /**
     * Get the provider's audio gateway.
     */
    public function audioGateway(): AudioGateway
    {
        return $this->audioGateway ??= new OpenAiGateway($this->events);
    }

    /**
     * Get the name of the default text model.
     */
    public function defaultTextModel(): string
    {
        return $this->config['models']['text']['default'] ?? 'chat';
    }

    /**
     * Get the name of the default embeddings model.
     */
    public function defaultEmbeddingsModel(): string
    {
        return $this->config['models']['embeddings']['default'] ?? 'embeddings';
    }

    /**
     * Get the name of the default image model.
     */
    public function defaultImageModel(): string
    {
        return $this->config['models']['image']['default'] ?? 'text_to_image';
    }

    /**
     * Get the default / normalized image options for the provider.
     *
     * LiteLLM drops pixel sizes it cannot map for non-OpenAI backends (e.g. Gemini on Vertex),
     * so aspect ratios are also sent as "aspect_ratio", which those backends honour.
     */
    public function defaultImageOptions(?string $size = null, ?string $quality = null): array
    {
        $sizes = ['1:1' => '1024x1024', '2:3' => '1024x1536', '3:2' => '1536x1024'];

        return array_filter([
            'size' => $sizes[$size] ?? $size,
            'aspect_ratio' => isset($sizes[$size]) ? $size : null,
            'quality' => $quality,
        ]);
    }

    /**
     * Get the name of the default audio (TTS) model.
     */
    public function defaultAudioModel(): string
    {
        return $this->config['models']['audio']['default'] ?? throw new InvalidArgumentException(
            "The [{$this->name()}] amazee.ai provider requires a default audio model. Set [models.audio.default] in its configuration or pass a model explicitly."
        );
    }
}
