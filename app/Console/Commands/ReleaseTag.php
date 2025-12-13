<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class ReleaseTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:tag
                            {--tag-version= : Specific version to tag (e.g., 1.2.3)}
                            {--message= : Tag message (defaults to "Release {version}")}
                            {--push : Automatically push the tag to remote}
                            {--fix : Increment patch version (1.1.X) instead of minor version}
                            {--force : Skip uncommitted changes check}
                            {--dry-run : Show what would be done without creating the tag}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and optionally push a new release tag with automatic version increment (minor by default, patch with --fix)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $push = $this->option('push');
        $customMessage = $this->option('message');

        info('🏷️  Release Tag Creator');
        info('');

        // Check if git is available
        $gitCheck = Process::run('git --version');
        if (! $gitCheck->successful()) {
            error('Git is not available. Please install Git to use this command.');

            return self::FAILURE;
        }

        // Check if we're in a git repository
        $repoCheck = Process::run('git rev-parse --git-dir');
        if (! $repoCheck->successful()) {
            error('Not in a git repository. Please run this command from a git repository.');

            return self::FAILURE;
        }

        // Check if there are uncommitted changes
        $force = $this->option('force');
        $statusCheck = Process::run('git status --porcelain');
        if ($statusCheck->successful() && ! empty(trim($statusCheck->output()))) {
            if (! $force) {
                warning('⚠️  You have uncommitted changes.');
                if (! confirm('Do you want to continue anyway?', default: false)) {
                    info('Aborted.');

                    return self::SUCCESS;
                }
            } else {
                warning('⚠️  Uncommitted changes detected (--force flag used, continuing anyway)');
            }
        }

        // Get the version
        $version = $this->getVersion();

        if (! $this->isValidVersion($version)) {
            error("Invalid version format: {$version}. Use semantic versioning (e.g., 1.2.3)");

            return self::FAILURE;
        }

        $tagName = "v{$version}";
        $message = $customMessage ?? "Release {$version}";

        // Check if tag already exists
        $tagExists = Process::run("git tag -l {$tagName}");
        if ($tagExists->successful() && ! empty(trim($tagExists->output()))) {
            error("Tag {$tagName} already exists!");

            return self::FAILURE;
        }

        if ($dryRun) {
            $isFix = $this->option('fix');
            info("📋 Dry run - would create tag: {$tagName}");
            info("📝 Message: {$message}");
            info('📦 Type: '.($isFix ? 'Patch release (fix)' : 'Minor release'));
            if ($push) {
                info('🚀 Would push to remote');
            } else {
                info('ℹ️  Tag would be created locally only (use --push to push to remote)');
            }

            return self::SUCCESS;
        }

        // Create the tag
        info("Creating tag: {$tagName}...");
        $createTag = Process::run("git tag -a {$tagName} -m \"{$message}\"");

        if (! $createTag->successful()) {
            error("Failed to create tag: {$createTag->errorOutput()}");

            return self::FAILURE;
        }

        info("✅ Tag {$tagName} created successfully!");

        // Push if requested
        if ($push) {
            info('Pushing tag to remote...');
            $pushTag = Process::run("git push origin {$tagName}");

            if (! $pushTag->successful()) {
                error("Failed to push tag: {$pushTag->errorOutput()}");
                warning("Tag {$tagName} was created locally but not pushed.");

                return self::FAILURE;
            }

            info("✅ Tag {$tagName} pushed to remote successfully!");
        } else {
            info("ℹ️  Tag created locally. Use 'git push origin {$tagName}' to push it, or run with --push flag");
        }

        return self::SUCCESS;
    }

    /**
     * Get the version to use for the tag.
     */
    protected function getVersion(): string
    {
        // If version is provided via option, use it
        if ($version = $this->option('tag-version')) {
            return $version;
        }

        // Get the latest tag
        $latestTag = $this->getLatestTag();

        if (! $latestTag) {
            // No tags exist, start with 1.0.0
            info('No existing tags found. Starting with version 1.0.0');

            return '1.0.0';
        }

        // Parse and increment version
        $isFix = $this->option('fix');
        $incremented = $isFix ? $this->incrementPatchVersion($latestTag) : $this->incrementMinorVersion($latestTag);
        info("Latest tag: {$latestTag}");
        info("Next version: {$incremented}");

        return $incremented;
    }

    /**
     * Get the latest git tag.
     */
    protected function getLatestTag(): ?string
    {
        // Get all tags sorted by version
        $tags = Process::run('git tag -l --sort=-version:refname');
        if (! $tags->successful() || empty(trim($tags->output()))) {
            return null;
        }

        $tagList = array_filter(explode("\n", trim($tags->output())));
        if (empty($tagList)) {
            return null;
        }

        // Get the first (latest) tag and remove 'v' prefix if present
        $latestTag = trim($tagList[0]);
        $latestTag = ltrim($latestTag, 'v');

        return $latestTag;
    }

    /**
     * Increment the minor version.
     */
    protected function incrementMinorVersion(string $version): string
    {
        // Remove 'v' prefix if present
        $version = ltrim($version, 'v');

        // Parse version parts
        $parts = explode('.', $version);

        // Ensure we have at least major.minor
        if (count($parts) < 2) {
            $parts = array_merge($parts, array_fill(0, 2 - count($parts), '0'));
        }

        // Ensure we have patch version
        if (count($parts) < 3) {
            $parts[] = '0';
        }

        // Increment minor version
        $parts[1] = (string) ((int) $parts[1] + 1);

        // Reset patch version to 0 when incrementing minor
        $parts[2] = '0';

        return implode('.', array_slice($parts, 0, 3));
    }

    /**
     * Increment the patch version.
     */
    protected function incrementPatchVersion(string $version): string
    {
        // Remove 'v' prefix if present
        $version = ltrim($version, 'v');

        // Parse version parts
        $parts = explode('.', $version);

        // Ensure we have at least major.minor
        if (count($parts) < 2) {
            $parts = array_merge($parts, array_fill(0, 2 - count($parts), '0'));
        }

        // Ensure we have patch version
        if (count($parts) < 3) {
            $parts[] = '0';
        }

        // Increment patch version
        $parts[2] = (string) ((int) $parts[2] + 1);

        return implode('.', array_slice($parts, 0, 3));
    }

    /**
     * Validate version format (semantic versioning).
     */
    protected function isValidVersion(string $version): bool
    {
        // Remove 'v' prefix if present for validation
        $version = ltrim($version, 'v');

        // Match semantic versioning pattern: major.minor.patch
        return (bool) preg_match('/^\d+\.\d+\.\d+$/', $version);
    }
}
