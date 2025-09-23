<?php

declare(strict_types=1);

namespace Flowstore\Lookup\DTO;

final class IntegrationContext
{
	public function __construct(
		public readonly string $channelKey,
		/** @var array<string, mixed> */
		public readonly array $credentials
	) {}
}


