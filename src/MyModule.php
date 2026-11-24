<?php

declare(strict_types=1);

namespace Acme\MyModule;

use SightPress\Module\ModuleInterface;
use SightPress\Module\ModuleManifest;
use SightPress\Module\ModuleRegistrar;

/**
 * My module for SightPress. Rename the namespace, the class and the values below (see README.md, "Make it yours").
 *
 * It adds nothing yet: it only tells SightPress where its templates, translations and database migrations are. Add
 * what your module does in register(), through the registrar (routes, admin screens, permissions, block types,
 * settings, search, SEO …). Use only `SightPress\Module\…`: a module never depends on SightPress core.
 */
final class MyModule implements ModuleInterface
{
    /** Lower-case words joined by ".", "_" or "-", starting with your vendor name. */
    public const string ID = 'acme.my_module';

    /** The module's version (release-please keeps it up to date). */
    public const string VERSION = '0.1.0'; // x-release-please-version

    /** Where its templates and translations are found: `@my_module/…` and the `my_module` text domain. */
    public const string NAMESPACE = 'my_module';

    public function manifest(): ModuleManifest
    {
        // Optional: sites can turn it on and off on the Features screen. New sites start with it on.
        return new ModuleManifest(self::ID, self::VERSION, enabledByDefault: true);
    }

    public function register(ModuleRegistrar $registrar): void
    {
        $root = dirname(__DIR__);
        $registrar->views()->add(self::NAMESPACE, $root . '/templates');
        $registrar->translations()->add($root . '/translations');
        $registrar->migrations()->add(new Migrations());
    }
}
