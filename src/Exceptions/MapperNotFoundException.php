<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Exceptions;

final class MapperNotFoundException extends LookupException
{
	public static function for(string $channelKey, string $entity): self
	{
		return new self("Entity mapper not found for channel '{$channelKey}' and entity '{$entity}'.");
	}
}


