<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;
use Stripe\Stripe;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        seo()
            ->site(config('app.name'))
            ->title(
                modify: fn (string $title) => $title . ' - ' . config('app.name'),
                default: 'Filament - The elegant TALLkit for Laravel artisans.',
            )
            ->description(default: 'Filament is a collection of tools for rapidly building beautiful TALL stack apps, designed for humans.')
            ->twitterSite('filamentphp');

        Model::preventLazyLoading(! app()->isProduction());
        Model::unguard();

        URL::forceScheme('https');

        app()->singleton('markdown.environment', function (Container $app): Environment {
            $config = require __DIR__ . '/../../config/markdown.php';

            $environment = new Environment(Arr::except($config, ['extensions', 'views']));

            collect($config['extensions'])
                ->each(fn (string $extension) => $environment->addExtension(app($extension)));

            return $environment;
        });

        app()->singleton('markdown.converter', function (Container $app): MarkdownConverter {
            $environment = app('markdown.environment');

            return new MarkdownConverter($environment);
        });

        Filament::registerRenderHook('styles.before', function (): string {
            return Blade::render('<x-comments::styles />');
        });

        Filament::registerRenderHook('scripts.before', function (): string {
            return Blade::render('<x-comments::scripts />');
        });
    }
}
