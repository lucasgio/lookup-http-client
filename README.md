## Lookup (Paquete Laravel)

**Qué es**: Capa agnóstica para consultar APIs externas por "canal" (ecommerce/ERP/marketplace) y convertir respuestas en DTOs de dominio unificados. Se usa para iniciar integraciones (probar conexión, traer datos base, etc.). No persiste ni configura integraciones.

### Instalación

- Requerimientos: PHP ^8.2, Laravel ^10|^11.
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

### Flujo típico (HTTP opcional)

1. Frontend llama al endpoint de lookup con `integration_id`, `entity` y `params`.
2. Se resuelve el contexto (tenant + integración) para obtener `channel_key` y `credentials`.
3. Provider hace la llamada externa; Mapper convierte a un DTO de dominio; la API responde con `data = domain`.

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


