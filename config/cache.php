<?php

<<<<<<< Updated upstream
use Illuminate\Support\Str;
=======
use Fabricate\NutsAndBolts\Str;
>>>>>>> Stashed changes

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

<<<<<<< Updated upstream
    'default' => env('CACHE_STORE', 'redis'),
=======
    'default' => env('CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Limiter Store
    |--------------------------------------------------------------------------
    |
    | This option controls which cache store the RateLimiter uses when no
    | store is explicitly provided. Defaults to the array store for local
    | CLI workloads without depending on Redis availability.
    |
    */

    'limiter' => env('CACHE_LIMITER_STORE', 'array'),
>>>>>>> Stashed changes

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
<<<<<<< Updated upstream
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "file",
    |                    "redis",  "storage",
    |                    "null"
=======
    | You may define all of the cache "stores" for your application as well
    | as their drivers. Supported for ScrapyardIO / Fabricate (non-server):
    | "array", "file", "redis", "storage", "failover", "null"
    |
    | Deferred: database, memcached, dynamodb, session, apc, octane
>>>>>>> Stashed changes
    |
    */

    'stores' => [
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
<<<<<<< Updated upstream
        /*'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],*/
=======

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],
>>>>>>> Stashed changes

        'storage' => [
            'driver' => 'storage',
            'disk' => env('CACHE_STORAGE_DISK'),
            'path' => env('CACHE_STORAGE_PATH', 'framework/cache/data'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],
<<<<<<< Updated upstream
=======

        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'redis',
                'file',
                'array',
            ],
        ],

        'null' => [
            'driver' => 'null',
        ],

>>>>>>> Stashed changes
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
<<<<<<< Updated upstream
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),

    /*
    |--------------------------------------------------------------------------
    | Serializable Classes
    |--------------------------------------------------------------------------
    |
    | This value determines the classes that can be unserialized from cache
    | storage. By default, no PHP classes will be unserialized from your
    | cache to prevent gadget chain attacks if your APP_KEY is leaked.
    |
    */

    'serializable_classes' => false,
=======
    | When utilizing Redis (and similar) cache stores, there might be other
    | applications using the same cache. Prefix keys to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'scrapyard-io')).'-cache-'),
>>>>>>> Stashed changes

];
