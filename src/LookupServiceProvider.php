<?php

namespace Flowstore\Lookup;

use Flowstore\Lookup\Console\MakeLookupCommand;
use Flowstore\Lookup\Resolvers\EntityMapperResolver;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
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
	}

	public function boot(): void
	{
		$this->publishes([
			__DIR__ . '/../config/lookup.php' => $this->app->configPath('lookup.php'),
		], 'config');

		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeLookupCommand::class,
			]);
		}
	}
}


