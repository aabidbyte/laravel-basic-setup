<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Translation\DynamicKeyResolver;
use App\Services\Translation\LocaleManager;
use App\Services\Translation\TranslationPruner;
use App\Services\Translation\TranslationScanner;
use App\Support\Translation\TranslationConfig;
use Illuminate\Console\Command;

class LangSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:sync
                            {--write : Actually write changes to files}
                            {--prune : Remove unused keys from non-protected files}
                            {--prune-all : Remove unused keys from all files (including ui.php and messages.php)}
                            {--allow-json : Allow JSON string keys for literal strings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync translation files across all locales, add missing keys, and optionally prune unused keys';

    protected TranslationScanner $scanner;

    protected DynamicKeyResolver $keyResolver;

    protected LocaleManager $localeManager;

    protected TranslationPruner $pruner;

    /**
     * Stats for display summary.
     */
    protected array $stats = [
        'keys_found' => 0,
        'keys_added' => 0,
        'keys_pruned' => 0,
        'files_updated' => 0,
    ];

    public function __construct(
        TranslationScanner $scanner,
        DynamicKeyResolver $keyResolver,
        LocaleManager $localeManager,
        TranslationPruner $pruner,
    ) {
        parent::__construct();
        $this->scanner = $scanner;
        $this->keyResolver = $keyResolver;
        $this->localeManager = $localeManager;
        $this->pruner = $pruner;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->loadConfiguration();

        $this->info('Scanning codebase for translation usage...');
        $this->scanner->scanCodebase();
        $this->stats['keys_found'] = \count($this->scanner->getFoundKeys());

        $this->info("Found {$this->stats['keys_found']} translation keys in codebase.");

        if (! $this->option('write')) {
            $this->warn('Running in dry-run mode. Use --write to apply changes.');
        }

        // First, sync source (fallback) locale with missing keys from codebase
        $this->info("Syncing source locale ({$this->localeManager->getSourceLocale()}) with missing keys...");
        $this->localeManager->syncSourceLocale(
            $this->scanner->getFoundKeys(),
            (bool) $this->option('write'),
            $this,
        );

        $this->info('Syncing locales...');
        $this->localeManager->syncLocales(
            (bool) $this->option('write'),
            (bool) $this->option('allow-json'),
            $this,
        );

        // Update stats from services
        $this->stats['keys_added'] = $this->localeManager->getKeysAdded();
        $this->stats['files_updated'] = $this->localeManager->getFilesUpdated();

        if ($this->option('prune') || $this->option('prune-all')) {
            $this->info('Pruning unused keys...');
            $this->pruner->pruneUnusedKeys(
                (bool) $this->option('write'),
                (bool) $this->option('prune-all'),
                $this,
            );
            $this->stats['keys_pruned'] = $this->pruner->getKeysPruned();
        }

        $this->displaySummary();

        return Command::SUCCESS;
    }

    /**
     * Load configuration from i18n config and pass to services.
     */
    protected function loadConfiguration(): void
    {
        $sourceLocale = config('i18n.fallback_locale', 'en_US');
        $supportedLocales = \array_keys(config('i18n.supported_locales', []));
        $protectedFiles = config('i18n.protected_translation_files', []);
        $extractedFile = config('i18n.extracted_file', 'extracted');

        // Configuration Injection
        $this->localeManager->setConfiguration(new TranslationConfig(
            sourceLocale: $sourceLocale,
            supportedLocales: $supportedLocales,
            namespaces: [],
            extractedFile: $extractedFile,
        ));

        $configuredNamespaces = config('i18n.namespaces', ['ui', 'messages']);
        $discoveredNamespaces = $this->localeManager->discoverNamespaces();
        $namespaces = \array_unique(\array_merge($configuredNamespaces, $discoveredNamespaces));

        // Re-configure LocaleManager with full namespaces
        $this->localeManager->setConfiguration(new TranslationConfig(
            sourceLocale: $sourceLocale,
            supportedLocales: $supportedLocales,
            namespaces: $namespaces,
            extractedFile: $extractedFile,
        ));

        // Configure Pruner
        $this->pruner->setConfiguration($supportedLocales, $protectedFiles, $namespaces);

        if (empty($supportedLocales)) {
            $this->error('No supported locales found in config/i18n.php');
            exit(1);
        }
    }

    /**
     * Display summary of sync operation.
     */
    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Sync Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Keys found in codebase', $this->stats['keys_found']],
                ['Keys added to locales', $this->stats['keys_added']],
                ['Keys pruned', $this->stats['keys_pruned']],
                ['Files updated', $this->stats['files_updated']],
            ],
        );

        if (! $this->option('write')) {
            $this->newLine();
            $this->warn('This was a dry run. Use --write to apply changes.');
        }
    }
}
