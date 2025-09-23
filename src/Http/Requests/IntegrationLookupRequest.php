<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IntegrationLookupRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function rules(): array
	{
		return [
			'integration_id' => ['required'],
			'entity' => ['required', 'string'],
			'params' => ['array'],
		];
	}
}


