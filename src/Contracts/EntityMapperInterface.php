<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Contracts;

use Flowstore\Lookup\DTO\IntegrationContext;

/**
 * @template TDomain of object
 */
interface EntityMapperInterface
{
	/**
	 * Returns the entity key this mapper supports, e.g. "seller".
	 */
	public function entity(): string;

	/**
	 * @param mixed $payload Raw payload from provider
	 * @return TDomain Domain DTO
	 */
	public function map($payload, IntegrationContext $context): object;
}


