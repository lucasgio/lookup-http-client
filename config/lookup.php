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

	'routes' => [
		'enabled' => true,
		// Default API route path for lookup POST endpoint
		'path' => '/tenant-integrations/lookup',
		'prefix' => null,
		'middleware' => ['api'],
	],
];


