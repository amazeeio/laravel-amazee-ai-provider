<?php

use Amazee\LaravelAi\AmazeeAiServiceProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\AiServiceProvider;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Image;
use Orchestra\Testbench\TestCase;

use function Laravel\Ai\agent;

uses(TestCase::class);

beforeEach(function (): void {
    $this->app->register(AiServiceProvider::class);
    $this->app->register(AmazeeAiServiceProvider::class);

    config([
        'ai.providers.amazeeio.url' => 'https://llm.us103.amazee.ai/v1/',
        'ai.providers.amazeeio.key' => 'sk-test',
    ]);
});

test('text goes to the region chat completions endpoint with the chat alias', function (): void {
    Http::fake(['*' => Http::response([
        'id' => 'x', 'model' => 'chat',
        'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'Hi'], 'finish_reason' => 'stop']],
        'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
    ])]);

    expect(agent()->prompt('Hello', provider: 'amazeeio')->text)->toBe('Hi');

    Http::assertSent(fn (Request $r): bool => $r->url() === 'https://llm.us103.amazee.ai/v1/chat/completions'
        && $r['model'] === 'chat'
        && $r->hasHeader('Authorization', 'Bearer sk-test'));
});

test('embeddings use the embeddings alias', function (): void {
    Http::fake(['*' => Http::response(['data' => [['embedding' => [0.1, 0.2]]], 'usage' => ['prompt_tokens' => 1]])]);

    expect(Embeddings::for(['a'])->generate('amazeeio')->embeddings)->toBe([[0.1, 0.2]]);

    Http::assertSent(fn (Request $r): bool => $r->url() === 'https://llm.us103.amazee.ai/v1/embeddings'
        && $r['model'] === 'embeddings');
});

test('images use the text_to_image alias', function (): void {
    Http::fake(['*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

    Image::of('A red apple')->generate('amazeeio');

    Http::assertSent(fn (Request $r): bool => $r->url() === 'https://llm.us103.amazee.ai/v1/images/generations'
        && $r['model'] === 'text_to_image');
});

test('image aspect ratios are sent as aspect_ratio alongside the pixel size', function (string $method, string $ratio, string $size): void {
    Http::fake(['*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

    Image::of('A red apple')->{$method}()->generate('amazeeio');

    Http::assertSent(fn (Request $r): bool => ($r->data()['aspect_ratio'] ?? null) === $ratio && $r['size'] === $size);
})->with([
    'square' => ['square', '1:1', '1024x1024'],
    'portrait' => ['portrait', '2:3', '1024x1536'],
    'landscape' => ['landscape', '3:2', '1536x1024'],
]);

test('explicit pixel sizes are sent without aspect_ratio', function (): void {
    Http::fake(['*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

    Image::of('A red apple')->size('1024x1024')->generate('amazeeio');

    Http::assertSent(fn (Request $r): bool => $r['size'] === '1024x1024' && ! isset($r['aspect_ratio']));
});
