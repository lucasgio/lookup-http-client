<?php

declare(strict_types=1);

namespace App\Lookup\Mappers\Foo;

use Flowstore\Lookup\Contracts\EntityMapperInterface;
use Flowstore\Lookup\DTO\IntegrationContext;

/** @implements EntityMapperInterface<object> */
final class SellerMapper implements EntityMapperInterface
{
	public function entity(): string
	{
		return 'seller';
	}

	public function map($payload, IntegrationContext $context): object
	{
		return (object) [
			'channel' => $context->channelKey,
			'value' => $payload['value'] ?? null,
		];
	}
}


