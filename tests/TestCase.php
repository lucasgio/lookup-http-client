<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Tests;

use Flowstore\Lookup\LookupServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	protected function getPackageProviders($app): array
	{
		return [LookupServiceProvider::class];
	}
}


