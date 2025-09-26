<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Support;

use Flowstore\Lookup\Contracts\LookupProviderInterface;
use Flowstore\Lookup\DTO\IntegrationContext;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractLookupProvider implements LookupProviderInterface
{
	public function __construct(private readonly ConfigRepository $config) {}

	protected function http(IntegrationContext $context): PendingRequest
	{
		$timeoutValue = $this->config->get('lookup.http.timeout_seconds', 10);
		$retryTimesValue = $this->config->get('lookup.http.retry.times', 2);
		$retrySleepValue = $this->config->get('lookup.http.retry.sleep_milliseconds', 250);

		$timeout = is_int($timeoutValue) ? $timeoutValue : 10;
		$retryTimes = is_int($retryTimesValue) ? $retryTimesValue : 2;
		$retrySleep = is_int($retrySleepValue) ? $retrySleepValue : 250;

		return Http::timeout($timeout)
			->retry($retryTimes, $retrySleep);
	}

	/**
	 * Persist helpers for providers
	 * @param class-string<Model> $modelClass
	 * @param array<string, mixed> $attributes
	 */
	protected function persistCreate(string $modelClass, array $attributes): Model
	{
		return (new ModelWriter())->create($modelClass, $attributes);
	}

	/**
	 * @param class-string<Model> $modelClass
	 * @param array<string, mixed> $where
	 * @param array<string, mixed> $attributes
	 */
	protected function persistUpdateOrCreate(string $modelClass, array $where, array $attributes): Model
	{
		return (new ModelWriter())->updateOrCreate($modelClass, $where, $attributes);
	}

	/**
	 * @param class-string<Model> $modelClass
	 * @param array<int, array<string, mixed>> $rows
	 * @param array<int, string> $uniqueBy
	 * @param array<int, string> $update
	 */
	protected function persistUpsert(string $modelClass, array $rows, array $uniqueBy, array $update): int
	{
		return (new ModelWriter())->upsert($modelClass, $rows, $uniqueBy, $update);
	}
}


