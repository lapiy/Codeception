<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\Extension\RunFailed;
use Codeception\InitTemplate;
use Codeception\Module\Asserts;
use Codeception\Module\PhpBrowser;
use Symfony\Component\Yaml\Yaml;

class Bootstrap extends InitTemplate
{
    protected string $supportDir = 'tests/_support';

    protected string $outputDir = 'tests/_output';

    protected string $dataDir = 'tests/_data';

    protected string $envsDir = 'tests/_envs';

    public function setup()
    {
        $this->checkInstalled($this->workDir);

        $input = $this->input;
        if ($input->getOption('namespace')) {
            $this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
        }

        if ($input->hasOption('actor') && $input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        $this->say(
            "<fg=white;bg=magenta> Bootstrapping Codeception </fg=white;bg=magenta>\n"
        );

        $this->createGlobalConfig();
        $this->say("File codeception.yml created       <- global configuration");

        $this->createDirs();

        if ($input->hasOption('empty') && $input->getOption('empty')) {
            return;
        }

        if (!class_exists(Asserts::class) || !class_exists(PhpBrowser::class)) {
            $this->addModulesToComposer(['PhpBrowser', 'Asserts']);
        }

        $this->createUnitSuite();
        $this->createFunctionalSuite();
        $this->createAcceptanceSuite();

        $this->say(" --- ");
        $this->say();
        $this->saySuccess('Codeception is installed for acceptance, functional, and unit testing');
        $this->say();

        $this->say("<bold>Next steps:</bold>");
        $this->say('1. Edit <bold>tests/acceptance.suite.yml</bold> to set url of your application. Change PhpBrowser to WebDriver to enable browser testing');
        $this->say("2. Edit <bold>tests/functional.suite.yml</bold> to enable a framework module. Remove this file if you don't use a framework");
        $this->say("3. Create your first acceptance tests using <comment>codecept g:cest acceptance First</comment>");
        $this->say("4. Write first test in <bold>tests/acceptance/FirstCest.php</bold>");
        $this->say("5. Run tests using: <comment>codecept run</comment>");
    }

    protected function createDirs(): void
    {
        $this->createDirectoryFor('tests');
        $this->createEmptyDirectory($this->outputDir);
        $this->createEmptyDirectory($this->dataDir);
        $this->createDirectoryFor($this->supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->createDirectoryFor($this->supportDir . DIRECTORY_SEPARATOR . "Helper");
        $this->gitIgnore('tests/_output');
        $this->gitIgnore('tests/_support/_generated');
    }

    protected function createFunctionalSuite(string $actor = 'Functional'): void
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for functional tests
# Emulate web requests and make application process them
# Include one of framework modules (Symfony, Yii2, Laravel, Phalcon4) to use it
# Remove this suite if you don't use frameworks

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        # add a framework module here
        - \\{$this->namespace}Helper\Functional
    step_decorators: ~        
EOF;
        $this->createSuite('functional', $actor, $suiteConfig);
        $this->say("tests/functional created           <- functional tests");
        $this->say("tests/functional.suite.yml written <- functional tests suite configuration");
    }

    protected function createAcceptanceSuite(string $actor = 'Acceptance'): void
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for acceptance tests.
# Perform tests in browser using the WebDriver or PhpBrowser.
# If you need both WebDriver and PHPBrowser tests - create a separate suite.

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        - PhpBrowser:
            url: http://localhost/myapp
        - \\{$this->namespace}Helper\Acceptance
step_decorators: ~        
EOF;
        $this->createSuite('acceptance', $actor, $suiteConfig);
        $this->say("tests/acceptance created           <- acceptance tests");
        $this->say("tests/acceptance.suite.yml written <- acceptance tests suite configuration");
    }

    protected function createUnitSuite(string $actor = 'Unit'): void
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests.

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        - Asserts
        - \\{$this->namespace}Helper\Unit
    step_decorators: ~        
EOF;
        $this->createSuite('unit', $actor, $suiteConfig);
        $this->say("tests/unit created                 <- unit tests");
        $this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
    }

    public function createGlobalConfig(): void
    {
        $basicConfig = [
            'paths'    => [
                'tests'   => 'tests',
                'output'  => $this->outputDir,
                'data'    => $this->dataDir,
                'support' => $this->supportDir,
                'envs'    => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'extensions' => [
                'enabled' => [RunFailed::class]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace !== '') {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: {$namespace}\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }

    protected function createSuite(string $suite, string $actor, string $config): void
    {
        $this->createDirectoryFor("tests/{$suite}", "{$suite}.suite.yml");
        $this->createHelper($actor, $this->supportDir);
        $this->createActor($actor . $this->actorSuffix, $this->supportDir, Yaml::parse($config));
        $this->createFile('tests' . DIRECTORY_SEPARATOR . "{$suite}.suite.yml", $config);
    }
}
