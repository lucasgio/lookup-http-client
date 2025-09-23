<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Http\Controllers;

use Flowstore\Lookup\Services\LookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LookupController
{
	public function __construct(private readonly LookupService $service) {}

	public function __invoke(Request $request): JsonResponse
	{
		$integrationIdInput = $request->input('integration_id');
		$entityInput = $request->input('entity');
		$paramsInput = $request->input('params', []);

		$entity = is_string($entityInput) ? $entityInput : '';
		$params = is_array($paramsInput) ? $paramsInput : [];

		if (is_int($integrationIdInput) || is_string($integrationIdInput)) {
			$integrationId = $integrationIdInput;
		} else {
			throw new \InvalidArgumentException('integration_id must be an int or string');
		}

		$dto = $this->service->lookupById($integrationId, $entity, $params);

		return new JsonResponse([
			'data' => $dto,
		]);
	}
}


