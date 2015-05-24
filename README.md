# Database exporter to Laravel 5

This is database table structure and data exporter to migration and seed files for **Laravel 5**

## Table of content

* [Features](#features)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [License](#license)

## Features

* create **migration** files from database table structure 
* handle foreign keys (watch for recursive foreign keys)
* create **model** files from database table structure (even foreign keys)
* **organize** generated models depending on database tabla name to **individual namespace** and **directory** structure via regullar expressions
* create **seed** files from database table content

---
[Back to top][top]

## Installation

### Base

To `composer.json` file add following lines:

```json
// to "require" object
"vi-kon/laravel-db-exporter": "~1.*"
```

Or run following command in project root:

```bash
composer require vi-kon/db-exporter
```

In Laravel 5 project add following lines to `app.php`:

```php
// to providers array
'ViKon\DbExporter\DbExporterServiceProvider',
```
### Configuration and migration

Installing configuration and migration files simple run:

```bash
php artisan vendor:publish --provider="ViKon\DbExporter\DbExporterServiceProvider"
```

---
[Back to top][top]

## Configuration

Configuration files help set up default values for commands.

```php
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
        'path' => 'database/seeds',
    ],
];
```

---
[Back to top][top]

## Usages

**Note**: Generated files may need some auto-formatting.

### Creating migration files

The `db-exporter:migrate` command is used for creating migration files from database. It has several options:

* **prefix** - database name prefix in migration files
* **select** - array of selected database table names (if set `ignore` option is ignored)
* **ignore** - array of ignored database table names
* **database** - specify database connection name (if option is not set the default connection is used)
* **force** - force overwriting existing migration files
* **path** - output destination path relative to project root (default is `database/migrations`)

The example assumes following database tables:

* **users**
* **groups**
* **pages** with foreign key to user id

Exports all tables from default database:

```bash
php artisan db-exporter:migrate
```

The above command will generate following files into `database/migrations` directory:

```bash
YYYY-MM-DD_000000_create_users_table.php
YYYY-MM-DD_000001_create_groups_table.php
YYYY-MM-DD_000002_create_pages_table.php
```

**Note**: Table names and column names are converted to snake cased

---
[Back to top][top]

### Creating models

The `db-exporter:models` command is used for creating models from database. It has several options:

* **prefix** - database name prefix in migration files
* **select** - array of selected database table names (if set `ignore` option is ignored)
* **ignore** - array of ignored database table names
* **connection** - specify database connection name (if option is not set the default connection is used)
* **force** - force overwriting existing migration files
* **namespace** - models namespace (default is `App\Models`)
* **path** - output destination path relative to project root (default is `database/migrations`)

**Note**: Some situation foreign methods name can match in models, so manual renaming is needed.

**Note**: In some cases relation guess (One to One, Many to One, One to Many) can generate same method name in single class.

Creating models from default database:

```bash
php artisan db-exporter:models
```

### Creating seed files

The `db-exporter:seed` command is used for creating seeds from database data. It has several options:

* **prefix** - database name prefix in migration files
* **select** - array of selected database table names (if set `ignore` option is ignored)
* **ignore** - array of ignored database table names
* **connection** - specify database connection name (if option is not set the default connection is used)
* **force** - force overwriting existing migration files
* **path** - output destination path relative to project root (default is `database/seeds`)

Creating seed files from default database:

```bash
php artisan db-exporter:seed
```

---
[Back to top][top]

## License

This package is licensed under the MIT License

---
[Back to top][top]

[top]: #database-exporter-to-laravel-5
