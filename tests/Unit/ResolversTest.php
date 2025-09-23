<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Tests\Unit;

use Flowstore\Lookup\Resolvers\EntityMapperResolver;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;
use Flowstore\Lookup\Tests\TestCase;

final class ResolversTest extends TestCase
{
	public function test_provider_and_mapper_resolve_by_convention(): void
	{
		app('config')->set('lookup.providers', []);
		app('config')->set('lookup.mappers', []);
		app('config')->set('lookup.conventions.providers_namespace', 'App\\Lookup\\Providers');
		app('config')->set('lookup.conventions.mappers_namespace', 'App\\Lookup\\Mappers');

		$provider = app(LookupProviderResolver::class)->resolve('foo');
		$mapper = app(EntityMapperResolver::class)->resolve('foo', 'seller');

		self::assertSame('App\\Lookup\\Providers\\FooLookupProvider', $provider::class);
		self::assertSame('App\\Lookup\\Mappers\\Foo\\SellerMapper', $mapper::class);
	}
}


