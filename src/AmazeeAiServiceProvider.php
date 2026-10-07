<?php

namespace Amazee\LaravelAi;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\AiManager;

class AmazeeAiServiceProvider extends ServiceProvider
{
    /**
     * Register the amazee.ai driver with the AI manager.
     */
    public function register(): void
    {
        $this->callAfterResolving(AiManager::class, function (AiManager $ai): void {
            $ai->extend('amazeeio', fn ($app, array $config): AmazeeAiProvider => new AmazeeAiProvider(
                $config,
                $app->make(Dispatcher::class),
            ));
        });
    }

    /**
     * Add the default "amazeeio" provider unless the application defines its own.
     */
    public function boot(): void
    {
        // Done in boot so laravel/ai's top-level mergeConfigFrom can't drop it.
        if (! $this->app->configurationIsCached() && ! config()->has('ai.providers.amazeeio')) {
            config()->set('ai.providers.amazeeio', require __DIR__.'/../config/amazeeio.php');
        }
    }
}
