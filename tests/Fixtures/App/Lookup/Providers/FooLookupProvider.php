<?php

declare(strict_types=1);

namespace App\Lookup\Providers;

use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Support\AbstractLookupProvider;

final class FooLookupProvider extends AbstractLookupProvider
{
	public function resources(): array
	{
		return ['seller'];
	}

	public function testConnection(IntegrationContext $context): void {}

	public function lookup(IntegrationContext $context, string $entity, array $params = [])
	{
		return ['value' => $params['value'] ?? 42, 'entity' => $entity];
	}
}


