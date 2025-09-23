<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Exceptions;

final class ProviderNotFoundException extends LookupException
{
	public static function for(string $channelKey): self
	{
		return new self("Lookup provider not found for channel '{$channelKey}'.");
	}
}


