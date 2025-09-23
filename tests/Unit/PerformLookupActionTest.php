<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Tests\Unit;

use App\Lookup\Providers\FooLookupProvider;
use Flowstore\Lookup\Actions\PerformLookupAction;
use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Resolvers\EntityMapperResolver;
use Flowstore\Lookup\Tests\TestCase;

final class PerformLookupActionTest extends TestCase
{
	public function test_maps_payload_to_domain_dto(): void
	{
		app('config')->set('lookup.conventions.mappers_namespace', 'App\\Lookup\\Mappers');
		$action = new PerformLookupAction(app(EntityMapperResolver::class));

		$provider = app(FooLookupProvider::class);
		$context = new IntegrationContext('foo', []);
		$dto = $action($provider, $context, 'seller', ['value' => 7]);

		self::assertIsObject($dto);
		self::assertSame('foo', $dto->channel);
		self::assertSame(7, $dto->value);
	}
}


