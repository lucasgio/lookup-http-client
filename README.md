## Lookup (Paquete Laravel)

**Qué es**: Capa agnóstica para consultar APIs externas por "canal" (ecommerce/ERP/marketplace) y convertir respuestas en DTOs de dominio unificados. Se usa para iniciar integraciones (probar conexión, traer datos base, etc.). No persiste ni configura integraciones.

### Instalación

- Requerimientos: PHP ^8.1+, Laravel ^9|^10|^11|^12.
- Instala vía Composer:

```bash
composer require flowstore/lookup
```

- Publica la configuración:

```bash
php artisan vendor:publish --provider="Flowstore\\Lookup\\LookupServiceProvider" --tag=config
```

### Configuración

Archivo `config/lookup.php`:

- **providers**: mapa `channel_key => ClaseProvider`.
- **mappers**: mapa `channel_key => [entity => ClaseMapper]`.
- **conventions**: namespaces por defecto para resolución por convención.
- **http**: timeouts y retries por defecto para el cliente HTTP.

### Contratos principales

- `LookupProviderInterface`: `resources()`, `testConnection(IntegrationContext)`, `lookup(IntegrationContext, entity, params)`.
- `EntityMapperInterface<TDomain>`: `entity()`, `map(payload, IntegrationContext): TDomain`.
- `IntegrationContext`: DTO con `channelKey` y `credentials`.
- `IntegrationContextResolver`: contrato que la app host implementa para resolver un contexto desde un `integrationId`.

### Resolución y orquestación

- `LookupProviderResolver`: resuelve Provider por `channel_key` usando `config('lookup.providers')` o la convención `App\\Lookup\\Providers\\{Canal}LookupProvider`.
- `EntityMapperResolver`: resuelve Mapper por `channel_key + entity` usando `config('lookup.mappers')` o la convención `App\\Lookup\\Mappers\\{Canal}\\{Entidad}Mapper`.
- `PerformLookupAction`: invoca `provider->lookup(...)` y mapea con `mapper->map(...)`.
- `LookupService`: resuelve contexto (si se usa `IntegrationContextResolver`) y devuelve el DTO de dominio.

### Resolución de contexto (configurable)

El paquete es agnóstico de tu modelo. Define cómo construir `IntegrationContext` desde el request:

```php
// config/lookup.php
'context' => [
	// Nombre del parámetro de entrada con el id
	'id_param' => 'integration_id',

	// (opcional) Clase que implementa IntegrationContextResolver
	// 'resolver' => App\Resolvers\MyContextResolver::class,
	'resolver' => null,

	// (opcional) Resolver genérico basado en Eloquent
	'eloquent' => [
		// 'model' => App\\Models\\IntegrationTenant::class,
		// 'channel_column' => 'channel_key',
		// 'credentials_column' => 'credentials',
	],
],
```

Si usas el resolver genérico Eloquent con `IntegrationTenant`:

```php
// config/lookup.php
'context' => [
	'id_param' => 'integration_id',
	'resolver' => null,
	'eloquent' => [
		'model' => App\Models\IntegrationTenant::class,
		'channel_column' => 'channel_key',
		'credentials_column' => 'credentials',
	],
],
```

Modelo sugerido:

```php
// app/Models/IntegrationTenant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationTenant extends Model
{
	protected $casts = [
		'credentials' => 'array',
	];
}
```

El controlador del paquete leerá el id desde `config('lookup.context.id_param')` (por defecto `integration_id`) y resolverá el contexto usando el `resolver` configurado o el resolver Eloquent si se define `context.eloquent.model`.

### HTTP (LookupController)

- La ruta se habilita por defecto al instalar el paquete.
- Endpoint por defecto: `POST /tenant-integrations/lookup` (middleware `api`).
- Puedes deshabilitar u overrides en `config/lookup.php`:

```php
'routes' => [
	'enabled' => true,          // pon false para deshabilitar
	'path' => '/tenant-integrations/lookup',
	'prefix' => null,
	'middleware' => ['api'],
],
```

Ejemplo de request (POST JSON):
```json
{
  "integration_id": 123,
  "entity": "seller",
  "params": { "limit": 1 }
}
```
Ejemplo de respuesta:
```json
{
  "data": { /* DTO de dominio mapeado */ }
}
```

Nota: Debes implementar y bindear `IntegrationContextResolver` para traducir `integration_id` a `IntegrationContext`.

### Test de conexión (TestConnectionController)

- Endpoint por defecto: `POST /tenant-integrations/test-connection`
- Body (JSON):
```json
{ "channel_key": "shopify", "integration_id": 123 }
```
- Respuesta:
```json
{ "success": true }
```
- Requiere un `IntegrationContextResolver` configurado (propio con `context.resolver` o genérico con `context.eloquent.model`).
- Ajustes en `config/lookup.php`:
```php
'routes' => [
	'enabled' => true,
	'path' => '/tenant-integrations/lookup',
	'test_path' => '/tenant-integrations/test-connection',
	'prefix' => null,          // p.ej. 'api'
	'middleware' => ['api'],
],
'context' => [
	'id_param' => 'integration_id', // p.ej. 'tenant_id'
],
```

### Puntos de extensión

- **Agregar un canal**: crear `App\\Lookup\\Providers\\{Canal}LookupProvider` y registrarlo en `config/lookup.php` (o seguir la convención).
- **Agregar una entidad**: crear `App\\Lookup\\Mappers\\{Canal}\\{Entidad}Mapper` y registrarlo (o usar la convención).
- `testConnection(...)` permite "ping" de credenciales antes de usar `lookup`.

### Comando de scaffolding

```bash
php artisan make:lookup {channel} {entity} {--provider}
```
### Contribuir

- Issues y PRs en `https://github.com/flowstore/lookup`.
- Ejecuta tests y static analysis antes de commitear:

```bash
composer test
composer stan
```


Crea stubs para Provider y/o Mapper en tu app (`app/Lookup/...`).

### Buenas prácticas

- Evita dependencia dura a modelos: usa `IntegrationContext` o implementa `IntegrationContextResolver` en tu app.
- Usa el Http Client de Laravel con timeouts/retries; el paquete provee un `AbstractLookupProvider` de ayuda.
- Mantén el retorno como DTO de dominio consistente y documentado por entidad.

### Uso en la app host

```php
$context = new IntegrationContext(channelKey: 'shopify', credentials: ['token' => '...']);
$dto = app(\Flowstore\Lookup\Services\LookupService::class)
    ->lookup($context, 'seller', ['limit' => 1]);
```

O vía HTTP con el `LookupController` opcional.

### IntegrationContext (qué es y cómo personalizar)

`IntegrationContext` es un DTO inmutable que describe el contexto de una integración:
- `channelKey` (string): identifica el canal (p.ej. `shopify`, `mercadoLibre`).
- `credentials` (array<string, mixed>): credenciales y datos necesarios para llamar al canal (token, apiKey, sellerId, etc.).

Se utiliza en `LookupProviderInterface::testConnection(...)` y `lookup(...)`, y también en `EntityMapperInterface::map(...)` para proveer contexto al mapeo.

Personalización:
- Cambiar el parámetro de ID de entrada (nombre del campo en el request):
```php
// config/lookup.php
'context' => [
	'id_param' => 'tenant_id', // en vez de integration_id
	'resolver' => null,
	'eloquent' => [ /* ... opcional ... */ ],
],
```
En este caso, el controlador leerá `tenant_id` en el body.

- Proveer tu propio resolver (sin Eloquent genérico):
```php
// app/Resolvers/MyContextResolver.php
namespace App\Resolvers;

use App\Models\IntegrationTenant;
use Flowstore\Lookup\Contracts\IntegrationContextResolver;
use Flowstore\Lookup\DTO\IntegrationContext;

final class MyContextResolver implements IntegrationContextResolver
{
	public function resolve($integrationId): IntegrationContext
	{
		$tenant = IntegrationTenant::findOrFail($integrationId);
		return new IntegrationContext(
			channelKey: (string) $tenant->channel_key,
			credentials: (array) $tenant->credentials,
		);
	}
}
```
Regístralo por configuración (no hace falta bind manual):
```php
// config/lookup.php
'context' => [
	'id_param' => 'integration_id',
	'resolver' => App\Resolvers\MyContextResolver::class,
],
```

- Resolver genérico con Eloquent (sin escribir una clase):
```php
// config/lookup.php
'context' => [
	'id_param' => 'integration_id',
	'resolver' => null,
	'eloquent' => [
		'model' => App\Models\IntegrationTenant::class,
		'channel_column' => 'channel_key',
		'credentials_column' => 'credentials',
	],****
],
```
Sugerencia de modelo:
```php
class IntegrationTenant extends \Illuminate\Database\Eloquent\Model
{
	protected $casts = [
		'credentials' => 'array',
	];
}
```

Ejemplo de request con `tenant_id`:
```http
POST /tenant-integrations/lookup
Content-Type: application/json

{ "tenant_id": 42, "entity": "seller", "params": { "limit": 1 } }
```

### Persistencia desde providers (helpers)

Para facilitar inserciones/actualizaciones en tus modelos Eloquent desde un provider custom, `AbstractLookupProvider` expone métodos protegidos que usan `ModelWriter` internamente:

- `persistCreate(string $modelClass, array $attributes): Model`
- `persistUpdateOrCreate(string $modelClass, array $where, array $attributes): Model`
- `persistUpsert(string $modelClass, array $rows, array $uniqueBy, array $update): int`

Ejemplos:

```php
use Flowstore\Lookup\DTO\IntegrationContext;
use Flowstore\Lookup\Support\AbstractLookupProvider;

final class ShopifyLookupProvider extends AbstractLookupProvider
{
    public function resources(): array { return ['product']; }
    public function testConnection(IntegrationContext $context): void {}

    public function lookup(IntegrationContext $context, string $entity, array $params = [])
    {
        // ... obtén $payload remoto y mapea los campos de tu modelo

        // Crear o actualizar un registro único por external_id
        $product = $this->persistUpdateOrCreate(
            \App\Models\Product::class,
            ['external_id' => $payload['id']],
            [
                'name' => $payload['title'],
                'price' => $payload['price'],
            ]
        );

        // Upsert masivo
        $this->persistUpsert(
            \App\Models\Product::class,
            $rows /* [[ 'external_id' => '...', 'name' => '...' ], ...] */,
            ['external_id'],
            ['name','price']
        );

        return $product; // o devuelve el payload para que lo mapee el mapper
    }
}
```

Notas:
- `modelClass` es el FQCN del modelo (`App\Models\...`).
- Asegúrate de que tu modelo tenga fillable/casts adecuados para los `attributes`.
- `persistUpsert` sigue la firma de `Eloquent\Builder::upsert($rows, $uniqueBy, $update)`.


