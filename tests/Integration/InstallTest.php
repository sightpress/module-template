<?php

declare(strict_types=1);

namespace Acme\MyModule\Tests\Integration;

use Acme\MyModule\MyModule;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SightPress\Core\Kernel;
use SightPress\Core\Tenant\TenantConfigLoader;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * The module installed in a SightPress project the way Composer installs it (its package in
 * `vendor/composer/installed.json`), with SightPress core from the dev dependencies: SightPress finds it, a new site
 * starts with it on, the Features screen and `site:module:list` show it, and it can be turned off and on again.
 */
#[CoversNothing]
final class InstallTest extends TestCase
{
    private const string SECRET = 'a-test-secret-of-at-least-32-characters';

    private string $project;

    protected function setUp(): void
    {
        $this->project = sys_get_temp_dir() . '/my-module-' . bin2hex(random_bytes(6));
        mkdir($this->project . '/config', 0o777, true);
        mkdir($this->project . '/vendor/composer', 0o777, true);
        mkdir($this->project . '/sites', 0o777, true);
        file_put_contents($this->project . '/config/tenant.php', '<?php return ' . var_export(['log' => ['path' => $this->project . '/var/log/test.log']], true) . ';');
        file_put_contents($this->project . '/config/sites.php', '<?php return ' . var_export(['version' => 2, 'sites' => []], true) . ';');
        $package = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);
        file_put_contents($this->project . '/vendor/composer/installed.json', json_encode(['packages' => [$package]], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->project, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            \assert($file instanceof \SplFileInfo);
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->project);
    }

    public function testSightPressFindsTheModuleAndSitesCanTurnItOffAndOn(): void
    {
        $kernel = Kernel::fromConfig(new TenantConfigLoader()->load($this->project, ['APP_ENV' => 'test', 'APP_SECRET' => self::SECRET]));
        self::assertTrue($kernel->modules()->has(MyModule::ID), 'SightPress found it and it can be used');

        [$status, $output] = $this->console($kernel, [
            'command' => 'site:create',
            'slug' => 'demo',
            '--domain' => 'demo.example',
            '--db' => 'sqlite:///' . $this->project . '/sites/demo.sqlite',
            '--owner-type' => 'organization',
            '--admin-email' => 'ada@example.org',
            '--admin-first-name' => 'Ada',
            '--admin-last-name' => 'Lovelace',
        ]);
        self::assertSame(0, $status, $output);

        self::assertStringContainsString('acme.my_module (My module): on', $this->console($kernel, ['command' => 'site:module:list', 'slug' => 'demo'])[1]);
        self::assertSame(0, $this->console($kernel, ['command' => 'site:module:disable', 'slug' => 'demo', 'module' => MyModule::ID])[0]);
        self::assertStringContainsString('acme.my_module (My module): off', $this->console($kernel, ['command' => 'site:module:list', 'slug' => 'demo'])[1]);
        self::assertSame(0, $this->console($kernel, ['command' => 'site:module:enable', 'slug' => 'demo', 'module' => MyModule::ID])[0]);
        self::assertStringNotContainsString('unavailable', $this->console($kernel, ['command' => 'site:module:list', 'slug' => 'demo'])[1]);
    }

    /**
     * @param array<string, string> $input
     *
     * @return array{int, string} exit status and everything it wrote
     */
    private function console(Kernel $kernel, array $input): array
    {
        $application = $kernel->console();
        $application->setAutoExit(false);
        $tester = new ApplicationTester($application);
        $status = $tester->run($input, ['interactive' => false]);

        return [$status, $tester->getDisplay()];
    }
}
