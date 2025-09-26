<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Contracts;

use Flowstore\Lookup\DTO\IntegrationContext;

/**
 * A provider knows how to talk to a specific channel (e.g., Shopify, WooCommerce).
 */
interface LookupProviderInterface
{
	/**
	 * Returns a list of supported entity keys, e.g. ["seller", "catalog", ...].
	 *
	 * @return array<int, string>
	 */
	public function resources(): array;

	/**
	 * Verifies the credentials can reach the remote API.
	 */
	public function testConnection(IntegrationContext $context): void;

	/**
	 * Performs a lookup on the remote channel and returns the raw payload to be mapped.
	 *
	 * @param array<string, mixed> $params
	 * @return mixed Raw payload; the mapper is responsible for shaping it into a domain DTO
	 */
	public function lookup(IntegrationContext $context, string $entity, array $params = []);

}


