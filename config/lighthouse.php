<?php

return [
    'route' => [
        'prefix' => 'graphql',
        'middleware' => ['web'],
    ],
    'schema' => [
        'register' => base_path('graphql/schema.graphql'),
    ],
    'namespaces' => [
        'models' => ['App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'types' => 'App\\GraphQL\\Types',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
    ],
    'security' => [
        'max_query_complexity' => 0,
        'max_query_depth' => 0,
        'disable_introspection' => false,
    ],
    'pagination' => [
        'default_count' => 15,
        'max_count' => 100,
    ],
    'debug' => env('APP_DEBUG', false) ? \Nuwave\Lighthouse\Execution\ErrorHandler::ALL : \Nuwave\Lighthouse\Execution\ErrorHandler::handlers(),
    'error_handlers' => [
        \Nuwave\Lighthouse\Execution\AuthenticationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\AuthorizationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\ValidationErrorHandler::class,
        \Nuwave\Lighthouse\Execution\ReportingErrorHandler::class,
    ],
    'global_id_field' => 'id',
    'persist_id' => true,
    'cache' => [
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', false),
        'key' => 'lighthouse_schema',
        'ttl' => null,
    ],
];
