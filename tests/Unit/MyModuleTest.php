<?php

declare(strict_types=1);

namespace Acme\MyModule\Tests\Unit;

use Acme\MyModule\Migrations;
use Acme\MyModule\MyModule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\ModuleManifest;
use SightPress\Module\ModuleRegistrar;
use SightPress\Module\Registrar\MigrationRegistrar;
use SightPress\Module\Registrar\TranslationRegistrar;
use SightPress\Module\Registrar\ViewRegistrar;

#[CoversClass(MyModule::class)]
#[CoversClass(Migrations::class)]
final class MyModuleTest extends TestCase
{
    public function testItsManifestIsValidAndStable(): void
    {
        $manifest = new MyModule()->manifest();

        self::assertSame(MyModule::ID, $manifest->id);
        self::assertMatchesRegularExpression(ModuleManifest::ID_PATTERN, $manifest->id);
        self::assertSame(MyModule::VERSION, $manifest->version);
        self::assertFalse($manifest->required, 'sites can turn it on and off');
        self::assertEquals($manifest, new MyModule()->manifest());
    }

    public function testComposerJsonNamesTheModuleAndDescribesIt(): void
    {
        /** @var array{require: array<string, string>, extra: array{sightpress: array{module: string, features: array<string, array<string, string>>}}} $composer */
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);

        self::assertSame(MyModule::class, $composer['extra']['sightpress']['module']);
        self::assertSame([MyModule::ID], array_keys($composer['extra']['sightpress']['features']), 'the Features screen finds it by its id');
        self::assertNotSame('', $composer['extra']['sightpress']['features'][MyModule::ID]['name'] ?? '');
        self::assertArrayHasKey('sightpress/module-interface', $composer['require']);
        self::assertArrayNotHasKey('sightpress/core', $composer['require'], 'a module depends on the contract only');
    }

    public function testItRegistersItsTemplatesTranslationsAndMigrations(): void
    {
        $root = dirname(__DIR__, 2);
        $views = $this->createMock(ViewRegistrar::class);
        $views->expects(self::once())->method('add')->with(MyModule::NAMESPACE, $root . '/templates');
        $translations = $this->createMock(TranslationRegistrar::class);
        $translations->expects(self::once())->method('add')->with($root . '/translations');
        $migrations = $this->createMock(MigrationRegistrar::class);
        $migrations->expects(self::once())->method('add')->with(self::isInstanceOf(Migrations::class));
        $registrar = self::createStub(ModuleRegistrar::class);
        $registrar->method('views')->willReturn($views);
        $registrar->method('translations')->willReturn($translations);
        $registrar->method('migrations')->willReturn($migrations);

        new MyModule()->register($registrar);

        self::assertDirectoryExists($root . '/templates');
        self::assertFileExists($root . '/translations/' . MyModule::NAMESPACE . '.en-GB.xlf', 'SightPress starts in British English');
    }

    public function testItsMigrationsAreInTheirOwnNamespaceAndFolder(): void
    {
        $migrations = new Migrations();

        self::assertSame('Acme\MyModule\Migrations', $migrations->namespace());
        self::assertDirectoryExists($migrations->directory());
    }
}
