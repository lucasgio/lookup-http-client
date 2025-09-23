<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Resolvers;

use Flowstore\Lookup\Contracts\EntityMapperInterface;
use Flowstore\Lookup\Exceptions\MapperNotFoundException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;

final class EntityMapperResolver
{
	public function __construct(
		private readonly Container $container,
		private readonly ConfigRepository $config
	) {}

	/**
	 * @return EntityMapperInterface<object>
	 */
	public function resolve(string $channelKey, string $entity): EntityMapperInterface
	{
		$configMappers = $this->config->get('lookup.mappers', []);
		if (is_array($configMappers) && isset($configMappers[$channelKey]) && is_array($configMappers[$channelKey]) && isset($configMappers[$channelKey][$entity]) && is_string($configMappers[$channelKey][$entity])) {
			/** @var class-string<EntityMapperInterface<object>> $class */
			$class = $configMappers[$channelKey][$entity];
			return $this->container->make($class);
		}

		$nsConfig = $this->config->get('lookup.conventions.mappers_namespace', 'App\\Lookup\\Mappers');
		$ns = is_string($nsConfig) ? $nsConfig : 'App\\Lookup\\Mappers';
		$class = $ns . '\\' . ucfirst($channelKey) . '\\' . ucfirst($entity) . 'Mapper';
		if (class_exists($class)) {
			/** @var class-string<EntityMapperInterface<object>> $class */
			return $this->container->make($class);
		}

		throw MapperNotFoundException::for($channelKey, $entity);
	}
}


