<?php

require_once __DIR__ . '/autoload.php';

use Codeception\Application;

call_user_func(static function () {
    $app = new Application('Codeception', Codeception\Codecept::VERSION);
    $app->add(new Codeception\Command\Build('build'));
    $app->add(new Codeception\Command\Run('run'));
    $app->add(new Codeception\Command\Init('init'));
    $app->add(new Codeception\Command\Console('console'));
    $app->add(new Codeception\Command\Bootstrap('bootstrap'));
    $app->add(new Codeception\Command\GenerateCest('generate:cest'));
    $app->add(new Codeception\Command\GenerateTest('generate:test'));
    $app->add(new Codeception\Command\GenerateSuite('generate:suite'));
    $app->add(new Codeception\Command\GenerateHelper('generate:helper'));
    $app->add(new Codeception\Command\GenerateScenarios('generate:scenarios'));
    $app->add(new Codeception\Command\Clean('clean'));
    $app->add(new Codeception\Command\GenerateGroup('generate:groupobject'));
    $app->add(new Codeception\Command\GeneratePageObject('generate:pageobject'));
    $app->add(new Codeception\Command\GenerateStepObject('generate:stepobject'));
    $app->add(new Codeception\Command\GenerateSnapshot('generate:snapshot'));
    $app->add(new Codeception\Command\GenerateEnvironment('generate:environment'));
    $app->add(new Codeception\Command\GenerateFeature('generate:feature'));
    $app->add(new Codeception\Command\GherkinSnippets('gherkin:snippets'));
    $app->add(new Codeception\Command\GherkinSteps('gherkin:steps'));
    $app->add(new Codeception\Command\DryRun('dry-run'));
    $app->add(new Codeception\Command\ConfigValidate('config:validate'));

    // Suggests package	
    if (class_exists('Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand')) {
        $app->add(new Codeception\Command\Completion());
    } else {
        $app->add(new Codeception\Command\CompletionFallback());
    }

    $app->registerCustomCommands();

    // add only if within a phar archive.
    if ('phar:' === substr(__FILE__, 0, 5)) {
        $app->add(new Codeception\Command\SelfUpdate('self-update'));
    }

    $app->run();
});
