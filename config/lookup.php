<?php

return [

	'providers' => [
		// 'shopify' => App\Lookup\Providers\ShopifyLookupProvider::class,
	],

	'mappers' => [
		// 'shopify' => [
		// 	'seller' => App\Lookup\Mappers\Shopify\SellerMapper::class,
		// ],
	],

	'conventions' => [
		'providers_namespace' => 'App\\Lookup\\Providers',
		'mappers_namespace' => 'App\\Lookup\\Mappers',
	],

	'http' => [
		'timeout_seconds' => 10,
		'retry' => [
			'times' => 2,
			'sleep_milliseconds' => 250,
		],
	],

	'context' => [
		// Nombre del parámetro de entrada que contiene el id (por defecto integration_id)
		'id_param' => 'integration_id',
		// Clase opcional que implementa IntegrationContextResolver
		'resolver' => null,
		// Config opcional para resolver genérico basado en Eloquent
		'eloquent' => [
			// 'model' => App\\Models\\IntegrationTenant::class,
			// 'channel_column' => 'channel_key',
			// 'credentials_column' => 'credentials',
		],
	],

	'routes' => [
		'enabled' => true,
		// Default API route path for lookup POST endpoint
		'path' => '/tenant-integrations/lookup',
		'prefix' => null,
		'middleware' => ['api'],
	],
];


