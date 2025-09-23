<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Resolvers;

use Flowstore\Lookup\Contracts\LookupProviderInterface;
use Flowstore\Lookup\Exceptions\ProviderNotFoundException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;

final class LookupProviderResolver
{
	public function __construct(
		private readonly Container $container,
		private readonly ConfigRepository $config
	) {}

	public function resolve(string $channelKey): LookupProviderInterface
	{
		$configProviders = $this->config->get('lookup.providers', []);
		if (is_array($configProviders) && isset($configProviders[$channelKey]) && is_string($configProviders[$channelKey])) {
			/** @var class-string<LookupProviderInterface> $class */
			$class = $configProviders[$channelKey];
			return $this->container->make($class);
		}

		$nsConfig = $this->config->get('lookup.conventions.providers_namespace', 'App\\Lookup\\Providers');
		$ns = is_string($nsConfig) ? $nsConfig : 'App\\Lookup\\Providers';
		$class = $ns . '\\' . ucfirst($channelKey) . 'LookupProvider';
		if (class_exists($class)) {
			/** @var class-string<LookupProviderInterface> $class */
			return $this->container->make($class);
		}

		throw ProviderNotFoundException::for($channelKey);
	}
}


