<?php

namespace Tests\Feature\Console;

use App\Console\Commands\LangSyncCommand;
use App\Services\Translation\DynamicKeyResolver;
use App\Services\Translation\LocaleManager;
use App\Services\Translation\TranslationPruner;
use App\Services\Translation\TranslationScanner;
use Illuminate\Support\Facades\Config;
// Use 'pest' for testing
use Illuminate\Support\Facades\File;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    // Setup temporary lang directory
    $this->tempLangPath = base_path('tests/temp_lang');
    if (File::exists($this->tempLangPath)) {
        File::deleteDirectory($this->tempLangPath);
    }
    // We mock File facade partially, so we don't actually need real dirs for some tests
    // But integration tests might need them.

    // For strict unit testing of the command orchestration:
});

test('lang:sync command orchestrates strict services correctly', function () {
    // Mock Services
    $scanner = Mockery::mock(TranslationScanner::class);
    $keyResolver = Mockery::mock(DynamicKeyResolver::class);
    $localeManager = Mockery::mock(LocaleManager::class);
    $pruner = Mockery::mock(TranslationPruner::class);

    // Bind mocks
    $this->app->instance(TranslationScanner::class, $scanner);
    $this->app->instance(DynamicKeyResolver::class, $keyResolver);
    $this->app->instance(LocaleManager::class, $localeManager);
    $this->app->instance(TranslationPruner::class, $pruner);

    // Scanner Expectations
    $scanner->shouldReceive('scanCodebase')
        ->once();
    $scanner->shouldReceive('getFoundKeys')
        ->twice() // Once for count, once for syncDefault
        ->andReturn(['key.test' => ['file.php' => [1]]]);

    // LocaleManager Configuration Expectations
    $localeManager->shouldReceive('setConfiguration')
        ->atLeast()->times(1);
    $localeManager->shouldReceive('discoverNamespaces')
        ->once()
        ->andReturn(['ui', 'messages']);

    // LocaleManager Execution Expectations
    $localeManager->shouldReceive('syncSourceLocale')
        ->once();
    $localeManager->shouldReceive('syncLocales')
        ->once();
    $localeManager->shouldReceive('getSourceLocale')->andReturn('en_US');
    $localeManager->shouldReceive('getKeysAdded')->andReturn(5);
    $localeManager->shouldReceive('getFilesUpdated')->andReturn(2);

    // Pruner Configuration/Execution Expectations - Default run (no prune option)
    $pruner->shouldReceive('setConfiguration')
        ->atLeast()->times(1);
    $pruner->shouldReceive('pruneUnusedKeys')
        ->times(0); // Not called without --prune

    // Config Mocks
    Config::set('i18n.default_locale', 'en_US');
    Config::set('i18n.supported_locales', ['en_US' => []]);

    // Manual instantiation to ensure mocks are used
    $command = new LangSyncCommand($scanner, $keyResolver, $localeManager, $pruner);
    $command->setLaravel($this->app);

    // Run command
    $input = new ArrayInput(['--write' => false], $command->getDefinition());
    $output = new BufferedOutput();
    $command->run($input, $output);
});

test('lang:sync prunes when requested', function () {
    // Mock Services
    $scanner = Mockery::mock(TranslationScanner::class);
    $keyResolver = Mockery::mock(DynamicKeyResolver::class);
    $localeManager = Mockery::mock(LocaleManager::class);
    $pruner = Mockery::mock(TranslationPruner::class);

    // Bind mocks
    $this->app->instance(TranslationScanner::class, $scanner);
    $this->app->instance(DynamicKeyResolver::class, $keyResolver);
    $this->app->instance(LocaleManager::class, $localeManager);
    $this->app->instance(TranslationPruner::class, $pruner);

    // Stubs
    $scanner->shouldReceive('scanCodebase');
    $scanner->shouldReceive('getFoundKeys')->andReturn([]);
    $localeManager->shouldReceive('setConfiguration');
    $localeManager->shouldReceive('discoverNamespaces')->andReturn([]);
    $localeManager->shouldReceive('syncSourceLocale');
    $localeManager->shouldReceive('syncLocales');
    $localeManager->shouldReceive('getSourceLocale')->andReturn('en_US');
    $localeManager->shouldReceive('getKeysAdded')->andReturn(0);
    $localeManager->shouldReceive('getFilesUpdated')->andReturn(0);

    // Pruner Expectations
    $pruner->shouldReceive('setConfiguration');
    $pruner->shouldReceive('pruneUnusedKeys')
        ->with(false, false, Mockery::type('object')) // Expect write=false
        ->once();
    $pruner->shouldReceive('getKeysPruned')->andReturn(10);

    // Manual instantiation
    $command = new LangSyncCommand($scanner, $keyResolver, $localeManager, $pruner);
    $command->setLaravel($this->app);

    // Run command
    $input = new ArrayInput(['--prune' => true], $command->getDefinition());
    $output = new BufferedOutput();
    $command->run($input, $output);
});
