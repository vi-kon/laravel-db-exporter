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
        'path'      => 'app/Models',

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
            [
                'tablePattern' => '^agt_',
                'namespace'    => 'App\Models\Agent',
                'path'         => 'app/Models/Agent',
                'className'    => [
                    'pattern'     => '^agt_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^chr_',
                'namespace'    => 'App\Models\Character',
                'path'         => 'app/Models/Character',
                'className'    => [
                    'pattern'     => '^chr_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^crp_',
                'namespace'    => 'App\Models\Corporation',
                'path'         => 'app/Models/Corporation',
                'className'    => [
                    'pattern'     => '^crp_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^dgm_',
                'namespace'    => 'App\Models\Damage',
                'path'         => 'app/Models/Damage',
                'className'    => [
                    'pattern'     => '^dgm_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^inv_',
                'namespace'    => 'App\Models\Inventory',
                'path'         => 'app/Models/Invenroty',
                'className'    => [
                    'pattern'     => '^inv_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^map_',
                'namespace'    => 'App\Models\Map',
                'path'         => 'app/Models/Map',
                'className'    => [
                    'pattern'     => '^map_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^ram_',
                'namespace'    => 'App\Models\Ram',
                'path'         => 'app/Models/Ram',
                'className'    => [
                    'pattern'     => '^ram_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^sta_',
                'namespace'    => 'App\Models\Station',
                'path'         => 'app/Models/Station',
                'className'    => [
                    'pattern'     => '^sta_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^trn_',
                'namespace'    => 'App\Models\Translation',
                'path'         => 'app/Models/Translation',
                'className'    => [
                    'pattern'     => '^trn_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^war_',
                'namespace'    => 'App\Models\War',
                'path'         => 'app/Models/War',
                'className'    => [
                    'pattern'     => '^war_',
                    'replacement' => '',
                ],
            ],
            [
                'tablePattern' => '^planet_',
                'namespace'    => 'App\Models\Planet',
                'path'         => 'app/Models/Planet',
                'className'    => [
                    'pattern'     => '^planet_',
                    'replacement' => '',
                ],
            ],
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
        'path' => 'database/migrations',
    ],
];