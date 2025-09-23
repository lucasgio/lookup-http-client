<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Tests\Feature;

use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Http\Controllers\LookupController;
use Flowstore\Lookup\Resolvers\EntityMapperResolver;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;
use Flowstore\Lookup\Services\LookupService;
use Flowstore\Lookup\Tests\TestCase;
use Illuminate\Routing\Router;

final class LookupControllerTest extends TestCase
{
	protected function defineRoutes($router): void
	{
		/** @var Router $router */
		$router->post('/tenant-integrations/lookup', LookupController::class);
	}

	public function test_controller_returns_mapped_dto(): void
	{
		app('config')->set('lookup.conventions.providers_namespace', 'App\\Lookup\\Providers');
		app('config')->set('lookup.conventions.mappers_namespace', 'App\\Lookup\\Mappers');

		$this->app->instance(IntegrationContextResolver::class, new class implements IntegrationContextResolver {
			public function resolve($integrationId): IntegrationContext
			{
				return new IntegrationContext('foo', []);
			}
		});

		$this->app->bind(LookupService::class, function ($app) {
			return new LookupService(
				$app->make(LookupProviderResolver::class),
				new \Flowstore\Lookup\Actions\PerformLookupAction($app->make(EntityMapperResolver::class)),
				$app->make(IntegrationContextResolver::class),
			);
		});

		$response = $this->postJson('/tenant-integrations/lookup', [
			'integration_id' => 1,
			'entity' => 'seller',
			'params' => ['value' => 5],
		]);

		$response->assertOk();
		$response->assertJsonPath('data.value', 5);
	}
}


