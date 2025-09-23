<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Services;

use Flowstore\Lookup\Actions\PerformLookupAction;
use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;

final class LookupService
{
	public function __construct(
		private readonly LookupProviderResolver $providerResolver,
		private readonly PerformLookupAction $performLookup,
		private readonly ?IntegrationContextResolver $contextResolver = null
	) {}

	/**
	 * @param array<string, mixed> $params
	 */
	public function lookup(IntegrationContext $context, string $entity, array $params = []): object
	{
		$provider = $this->providerResolver->resolve($context->channelKey);
		return ($this->performLookup)($provider, $context, $entity, $params);
	}

	/**
	 * Convenience overload for host apps that prefer passing an integration id.
	 * @param string|int $integrationId
	 * @param array<string, mixed> $params
	 */
	public function lookupById($integrationId, string $entity, array $params = []): object
	{
		if ($this->contextResolver === null) {
			throw new \LogicException('IntegrationContextResolver not bound. Bind it or call lookup() with IntegrationContext.');
		}
		$context = $this->contextResolver->resolve($integrationId);
		return $this->lookup($context, $entity, $params);
	}
}


