<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Support;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
final class ModelWriter
{
	/**
	 * @param class-string<TModel> $modelClass
	 * @param array<string, mixed> $attributes
	 * @return TModel
	 */
	public function create(string $modelClass, array $attributes): object
	{
		/** @var TModel $model */
		$model = $modelClass::query()->create($attributes);
		return $model;
	}

	/**
	 * @param class-string<TModel> $modelClass
	 * @param array<string, mixed> $where
	 * @param array<string, mixed> $attributes
	 * @return TModel
	 */
	public function updateOrCreate(string $modelClass, array $where, array $attributes): object
	{
		/** @var TModel $model */
		$model = $modelClass::query()->updateOrCreate($where, $attributes);
		return $model;
	}

	/**
	 * @param class-string<TModel> $modelClass
	 * @param array<int, array<string, mixed>> $rows
	 * @param array<int, string> $uniqueBy
	 * @param array<int, string> $update
	 */
	public function upsert(string $modelClass, array $rows, array $uniqueBy, array $update): int
	{
		return (int) $modelClass::query()->upsert($rows, $uniqueBy, $update);
	}
}
