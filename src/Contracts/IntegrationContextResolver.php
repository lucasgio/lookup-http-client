<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Contracts;

use Flowstore\Lookup\DTO\IntegrationContext;

interface IntegrationContextResolver
{
	/**
	 * Resolve an IntegrationContext for a given integration id from the host app.
	 *
	 * @param string|int $integrationId
	 */
	public function resolve($integrationId): IntegrationContext;
}


