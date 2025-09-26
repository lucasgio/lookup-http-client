<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Http\Controllers;

use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\Resolvers\LookupProviderResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TestConnectionController
{
	public function __construct(
		private readonly LookupProviderResolver $providerResolver,
		private readonly ?IntegrationContextResolver $contextResolver = null
	) {}

	public function __invoke(Request $request): JsonResponse
	{
		$idParamRaw = config('lookup.context.id_param', 'integration_id');
		$idParam = (is_string($idParamRaw) && $idParamRaw !== '') ? $idParamRaw : 'integration_id';
		$integrationIdInput = $request->input($idParam);
		$channelKeyInput = $request->input('channel_key');

		if (!is_string($channelKeyInput) || $channelKeyInput === '') {
			throw new \InvalidArgumentException('channel_key must be a non-empty string');
		}

		if (!(is_int($integrationIdInput) || is_string($integrationIdInput))) {
			throw new \InvalidArgumentException($idParam . ' must be an int or string');
		}

		if ($this->contextResolver === null) {
			throw new \LogicException('IntegrationContextResolver not bound. Configure lookup.context.resolver or context.eloquent.model');
		}

		$context = $this->contextResolver->resolve($integrationIdInput);
		$provider = $this->providerResolver->resolve($channelKeyInput);
		$provider->testConnection($context);

		return new JsonResponse(['success' => true]);
	}
}
