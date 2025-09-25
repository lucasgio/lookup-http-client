<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Resolvers;

use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\DTO\IntegrationContext;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

final class ConfigModelContextResolver implements IntegrationContextResolver
{
	public function __construct(private readonly ConfigRepository $config) {}

	/**
	 * @param string|int $integrationId
	 */
	public function resolve($integrationId): IntegrationContext
	{
		$eloquent = (array) $this->config->get('lookup.context.eloquent', []);
		$modelClass = $eloquent['model'] ?? null;
		$channelColumnRaw = $eloquent['channel_column'] ?? 'channel_key';
		$credentialsColumnRaw = $eloquent['credentials_column'] ?? 'credentials';
		$channelColumn = is_string($channelColumnRaw) && $channelColumnRaw !== '' ? $channelColumnRaw : 'channel_key';
		$credentialsColumn = is_string($credentialsColumnRaw) && $credentialsColumnRaw !== '' ? $credentialsColumnRaw : 'credentials';

		if (!is_string($modelClass) || $modelClass === '' || !class_exists($modelClass)) {
			throw new \RuntimeException('lookup.context.eloquent.model must be a valid Eloquent model class');
		}

		/** @var \Illuminate\Database\Eloquent\Model $model */
		$model = $modelClass::query()->findOrFail($integrationId);

		$channelKeyRaw = $model->getAttribute($channelColumn);
		if (!is_string($channelKeyRaw)) {
			throw new \RuntimeException('Configured channel_column must resolve to a string');
		}
		$channelKey = $channelKeyRaw;

		$credentialsRaw = $model->getAttribute($credentialsColumn);
		$credentials = [];
		if (is_array($credentialsRaw)) {
			$credentials = $credentialsRaw;
		} elseif (is_string($credentialsRaw) && $credentialsRaw !== '') {
			try {
				$decoded = json_decode($credentialsRaw, true, 512, JSON_THROW_ON_ERROR);
				$credentials = is_array($decoded) ? $decoded : [];
			} catch (\Throwable) {
				$credentials = [];
			}
		}

		/** @var array<string, mixed> $credentials */
		return new IntegrationContext($channelKey, $credentials);
	}
}
