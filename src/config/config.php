<?php

return [
    /*
    | --------------------------------------------------------------------------
    | Default connection name
    | --------------------------------------------------------------------------
    | Database connection name. Valid connection names are defined in
    | database.php. If value is null default connection is used.
    |
    */
    'connection' => null,
    /*
    | --------------------------------------------------------------------------
    | Default table prefix
    | --------------------------------------------------------------------------
    | This table prefix is used in generated migration files or in generated
    | models.
    |
    */
    'prefix'     => '',
    /*
    | --------------------------------------------------------------------------
    | Default selected tables
    | --------------------------------------------------------------------------
    | Select specified database tables only. No work with ignore option
    | together. In commands with --select option can add another tables.
    |
    */
    'select'     => [],
    /*
    | --------------------------------------------------------------------------
    | Default ignored tables
    | --------------------------------------------------------------------------
    | Ignore specified database tables. No work with select option together.
    | In commands with --ignore option can add another tables.
    |
    */
    'ignore'     => [
        'migrations',
    ],
    /*
    | --------------------------------------------------------------------------
    | Model options
    | --------------------------------------------------------------------------
    */
    'model'      => [
        /*
        | --------------------------------------------------------------------------
        | Default namespace
        | --------------------------------------------------------------------------
        | Namespace for generated models. In command with --namespace option can
        | overwrite.
        |
        */
        'namespace' => 'App\Models',
        /*
        | --------------------------------------------------------------------------
        | Default path
        | --------------------------------------------------------------------------
        | Generated models destination path. Is relative to projects base path. In
        | command with --path option can overwrite.
        |
        */
        'path'      => app_path('Models'),
        /*
        | --------------------------------------------------------------------------
        | Custom map
        | --------------------------------------------------------------------------
        | Map is useful for organizing generated models to multiple namespaces and
        | directories. Each map entry is array with following associative keys:
        |
        | * tablePattern - regex pattern for selecting tables by name
        | * namespace    - generated models namespace from selected tables
        | * path         - generated models destination path from selected tables
        | * className    - array containing pattern and replacement for preg_match
        |                  to generate models class name from table name. If value
        |                  is null original table name is used. The result is camel
        |                  cased. The subject table name is snake cased
        |
        */
        'map'       => [
//            [
//                'tablePattern' => '.*',
//                'namespace'    => 'App\Models',
//                'path'         => 'app/Models',
//                'className'    => [
//                    'pattern'     => '',
//                    'replacement' => '',
//                ],
//            ],
        ],
    ],
    /*
    | --------------------------------------------------------------------------
    | Migration options
    | --------------------------------------------------------------------------
    */
    'migration'  => [
        /*
        | --------------------------------------------------------------------------
        | Default path
        | --------------------------------------------------------------------------
        | Generated migration destination path. Is relative to projects base path.
        | In command with --path option can overwrite.
        |
        */
        'path' => base_path('database/migrations'),
    ],
    /*
    | --------------------------------------------------------------------------
    | Seed options
    | --------------------------------------------------------------------------
    */
    'seed'       => [
        /*
        | --------------------------------------------------------------------------
        | Default path
        | --------------------------------------------------------------------------
        | Generated seed destination path. Is relative to projects base path.
        | In command with --path option can overwrite.
        |
        */
        'path' => base_path('database/seeds'),
    ],
];