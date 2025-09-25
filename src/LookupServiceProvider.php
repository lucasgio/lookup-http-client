<?php

namespace Flowstore\Lookup;

use Flowstore\Lookup\Console\MakeLookupCommand;
use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\Http\Controllers\LookupController;
use Flowstore\Lookup\Resolvers\ConfigModelContextResolver;
use Flowstore\Lookup\Resolvers\EntityMapperResolver;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class LookupServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/lookup.php', 'lookup');

		$this->app->singleton(LookupProviderResolver::class, function (Container $app): LookupProviderResolver {
			return new LookupProviderResolver($app, $app->make(ConfigRepository::class));
		});

		$this->app->singleton(EntityMapperResolver::class, function (Container $app): EntityMapperResolver {
			return new EntityMapperResolver($app, $app->make(ConfigRepository::class));
		});

		// Bind IntegrationContextResolver from config
		$config = $this->app->make(ConfigRepository::class);
		$resolverClass = $config->get('lookup.context.resolver');
		$eloquentModel = $config->get('lookup.context.eloquent.model');

		if (is_string($resolverClass) && class_exists($resolverClass)) {
			$this->app->bind(IntegrationContextResolver::class, $resolverClass);
		} elseif (is_string($eloquentModel) && class_exists($eloquentModel)) {
			$this->app->bind(IntegrationContextResolver::class, function () {
				return new ConfigModelContextResolver($this->app->make(ConfigRepository::class));
			});
		}
	}

	public function boot(): void
	{
		$this->publishes([
			__DIR__ . '/../config/lookup.php' => $this->app->configPath('lookup.php'),
		], 'config');

		$routes = (array) config('lookup.routes', []);
		$enabled = (bool) ($routes['enabled'] ?? false);
		if ($enabled) {
			$path = (string) ($routes['path'] ?? '/tenant-integrations/lookup');
			$prefix = $routes['prefix'] ?? null;
			$middleware = $routes['middleware'] ?? ['api'];

			Route::group([
				'middleware' => is_array($middleware) ? $middleware : [$middleware],
				'prefix' => is_string($prefix) ? $prefix : '',
			], function () use ($path): void {
				Route::post($path, LookupController::class);
			});
		}

		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeLookupCommand::class,
			]);
		}
	}
}


