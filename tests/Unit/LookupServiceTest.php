<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Tests\Unit;

use App\Lookup\Providers\FooLookupProvider;
use Flowstore\Lookup\Actions\PerformLookupAction;
use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Resolvers\EntityMapperResolver;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;
use Flowstore\Lookup\Services\LookupService;
use Flowstore\Lookup\Tests\TestCase;

final class LookupServiceTest extends TestCase
{
	public function test_lookup_uses_provider_and_mapper(): void
	{
		app('config')->set('lookup.conventions.providers_namespace', 'App\\Lookup\\Providers');
		app('config')->set('lookup.conventions.mappers_namespace', 'App\\Lookup\\Mappers');

		$service = new LookupService(
			app(LookupProviderResolver::class),
			new PerformLookupAction(app(EntityMapperResolver::class)),
		);

		$dto = $service->lookup(new IntegrationContext('foo', []), 'seller', ['value' => 9]);
		self::assertSame(9, $dto->value);
	}

	public function test_lookup_by_id_uses_context_resolver(): void
	{
		app('config')->set('lookup.conventions.providers_namespace', 'App\\Lookup\\Providers');
		app('config')->set('lookup.conventions.mappers_namespace', 'App\\Lookup\\Mappers');

		$resolver = new class implements IntegrationContextResolver {
			public function resolve($integrationId): IntegrationContext
			{
				return new IntegrationContext('foo', ['integration_id' => $integrationId]);
			}
		};

		$service = new LookupService(
			app(LookupProviderResolver::class),
			new PerformLookupAction(app(EntityMapperResolver::class)),
			$resolver,
		);

		$dto = $service->lookupById(123, 'seller', ['value' => 11]);
		self::assertSame(11, $dto->value);
	}
}


