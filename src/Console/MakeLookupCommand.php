<?php

declare(strict_types=1);

namespace Flowstore\Lookup\Console;

use Illuminate\Console\Command;

final class MakeLookupCommand extends Command
{
	protected $signature = 'make:lookup {channel} {entity} {--provider}';
	protected $description = 'Scaffold a Lookup Provider and/or Entity Mapper in the host app';


	public function handle(): int
	{
		$channelArg = $this->argument('channel');
		$entityArg = $this->argument('entity');
		if (!is_string($channelArg) || $channelArg === '') {
			throw new \InvalidArgumentException('The {channel} argument must be a non-empty string.');
		}
		if (!is_string($entityArg) || $entityArg === '') {
			throw new \InvalidArgumentException('The {entity} argument must be a non-empty string.');
		}
		// Enforce PascalCase/CamelCase for channel, e.g., MercadoLibre, Shopify
		if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', $channelArg)) {
			throw new \InvalidArgumentException('The {channel} argument must be CamelCase (e.g., MercadoLibre, Shopify).');
		}
		$channel = $channelArg; // already validated CamelCase
		$entity = ucfirst($entityArg);
		$makeProvider = ($this->option('provider') === true);

		$base = app_path('Lookup');
		$providersDir = $base . DIRECTORY_SEPARATOR . 'Providers';
		$mappersDir = $base . DIRECTORY_SEPARATOR . 'Mappers' . DIRECTORY_SEPARATOR . $channel;

		if ($makeProvider) {
			if (!is_dir($providersDir)) {
				mkdir($providersDir, 0775, true);
			}
			$providerClass = $providersDir . DIRECTORY_SEPARATOR . $channel . 'LookupProvider.php';
			if (!file_exists($providerClass)) {
				file_put_contents($providerClass, $this->providerStub($channel));
				$this->info("Provider created: {$providerClass}");
			}
		}

		if (!is_dir($mappersDir)) {
			mkdir($mappersDir, 0775, true);
		}
		$mapperClass = $mappersDir . DIRECTORY_SEPARATOR . $entity . 'Mapper.php';
		if (!file_exists($mapperClass)) {
			file_put_contents($mapperClass, $this->mapperStub($channel, $entity));
			$this->info("Mapper created: {$mapperClass}");
		}

		return self::SUCCESS;
	}

	private function providerStub(string $channel): string
	{
		$stub = <<<'PHP'
<?php



namespace App\Lookup\Providers;

use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Support\AbstractLookupProvider;

final class {Channel}LookupProvider extends AbstractLookupProvider
{
	public function resources(): array
	{
		return ['seller'];
	}

	public function testConnection(IntegrationContext $context): void
	{
		// $this->http($context)->get('https://api.example.com/ping')->throw();
	}

	public function lookup(IntegrationContext $context, string $entity, array $params = [])
	{
		return ['ok' => true, 'entity' => $entity, 'params' => $params];
	}
}
PHP;
		return strtr($stub, ['{Channel}' => $channel]);
	}

	private function mapperStub(string $channel, string $entity): string
	{
		$stub = <<<'PHP'
<?php



namespace App\Lookup\Mappers\{Channel};

use Flowstore\Lookup\Contracts\EntityMapperInterface;
use Flowstore\Lookup\DTO\IntegrationContext;

/** @implements EntityMapperInterface<object> */
final class {Entity}Mapper implements EntityMapperInterface
{
	public function entity(): string
	{
		return '{entity}';
	}

	public function map($payload, IntegrationContext $context): object
	{
		return (object) $payload;
	}
}
PHP;
		return strtr($stub, [
			'{Channel}' => $channel,
			'{Entity}' => $entity,
			'{entity}' => strtolower($entity),
		]);
	}
}


