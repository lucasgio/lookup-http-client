<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Actions;

use Flowstore\Lookup\Contracts\LookupProviderInterface;
use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Resolvers\EntityMapperResolver;

final class PerformLookupAction
{
	public function __construct(private readonly EntityMapperResolver $mapperResolver) {}

	/**
	 * @param array<string, mixed> $params
	 * @return object Domain DTO
	 */
	public function __invoke(LookupProviderInterface $provider, IntegrationContext $context, string $entity, array $params = []): object
	{
		$payload = $provider->lookup($context, $entity, $params);
		$mapper = $this->mapperResolver->resolve($context->channelKey, $entity);
		return $mapper->map($payload, $context);
	}
}


