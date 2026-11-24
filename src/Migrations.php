<?php

declare(strict_types=1);

namespace Acme\MyModule;

use SightPress\Module\Migration\MigrationProvider;

/**
 * Where the module's database migrations are: classes in `migrations/`, in the `Acme\MyModule\Migrations` namespace.
 * SightPress runs the pending ones when a site is created and when the module is turned on, and undoes them (newest
 * first) when someone deletes the module's data.
 */
final class Migrations implements MigrationProvider
{
    public function namespace(): string
    {
        return 'Acme\MyModule\Migrations';
    }

    public function directory(): string
    {
        return dirname(__DIR__) . '/migrations';
    }
}
