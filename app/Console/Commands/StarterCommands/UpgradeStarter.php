<?php

namespace App\Console\Commands\StarterCommands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class UpgradeStarter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:upgrade
                            {--upstream= : The upstream repository URL}
                            {--branch=main : The branch to upgrade from}
                            {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade your project with the latest changes from the starter template';

    /**
     * The default upstream repository URL.
     */
    protected string $defaultUpstream = 'https://github.com/aabidbyte/laravel-basic-setup.git';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $upstreamUrl = $this->option('upstream') ?? $this->defaultUpstream;
        $branch = $this->option('branch') ?? 'main';
        $dryRun = $this->option('dry-run');

        info('🚀 Laravel Basic Setup Upgrade Tool');
        info('');

        // Check if git is available
        if (! $this->isGitAvailable()) {
            error('Git is not available. Please install Git to use this command.');

            return self::FAILURE;
        }

        // Check if we're in a git repository
        if (! $this->isGitRepository()) {
            error('This command must be run in a Git repository.');

            return self::FAILURE;
        }

        // Check if upstream remote exists
        $upstreamRemote = $this->getUpstreamRemote();
        if (! $upstreamRemote) {
            info("Upstream remote not found. Adding 'upstream' remote...");
            if (! $dryRun && ! $this->addUpstreamRemote($upstreamUrl)) {
                error('Failed to add upstream remote.');

                return self::FAILURE;
            }
            info("✅ Added 'upstream' remote: {$upstreamUrl}");
        } else {
            info("✅ Found upstream remote: {$upstreamRemote}");
        }

        // Fetch updates from upstream
        info('');
        info('Fetching latest changes from upstream...');
        if (! $dryRun) {
            $result = Process::run('git fetch upstream');
            if (! $result->successful()) {
                error('Failed to fetch from upstream.');
                error($result->errorOutput());

                return self::FAILURE;
            }
        }
        info('✅ Fetched latest changes');

        // Get current branch
        $currentBranch = $this->getCurrentBranch();
        info("Current branch: {$currentBranch}");

        // Check if there are any changes to merge
        $hasChanges = $this->hasUpstreamChanges($branch);
        if (! $hasChanges) {
            info('');
            info('✅ Your project is already up to date!');

            return self::SUCCESS;
        }

        // Show what has changed
        info('');
        info('📋 Changes available from upstream:');
        $this->showUpstreamChanges($branch);

        if ($dryRun) {
            info('');
            warning('Dry run mode: No changes were made.');

            return self::SUCCESS;
        }

        // Ask user what they want to do
        info('');
        $action = select(
            label: 'What would you like to do?',
            options: [
                'merge' => 'Merge upstream changes into current branch',
                'diff' => 'View detailed diff before merging',
                'abort' => 'Abort upgrade',
            ],
            default: 'merge',
        );

        if ($action === 'abort') {
            info('Upgrade aborted.');

            return self::SUCCESS;
        }

        if ($action === 'diff') {
            $this->showDetailedDiff($branch);
            info('');
            if (! confirm('Do you want to merge these changes?', default: true)) {
                info('Upgrade aborted.');

                return self::SUCCESS;
            }
        }

        // Check for uncommitted changes
        if ($this->hasUncommittedChanges()) {
            warning('You have uncommitted changes. It is recommended to commit or stash them before upgrading.');
            if (! confirm('Continue anyway?', default: false)) {
                info('Upgrade aborted. Please commit or stash your changes first.');

                return self::SUCCESS;
            }
        }

        // Merge upstream changes
        info('');
        info('Merging upstream changes...');
        if ($this->mergeUpstreamChanges($branch)) {
            info('');
            info('✅ Successfully merged upstream changes!');
            info('');
            info('Next steps:');
            info('1. Review the changes: git status');
            info('2. Test your application thoroughly');
            info('3. Resolve any conflicts if they occurred');
            info('4. Commit the merge: git commit');
            info('5. Run: composer install && npm install (if dependencies changed)');
            info('6. Run: php artisan migrate (if migrations were added)');

            return self::SUCCESS;
        }

        error('Failed to merge upstream changes. You may need to resolve conflicts manually.');
        info('');
        info('To resolve conflicts manually:');
        info('1. Check conflicted files: git status');
        info('2. Resolve conflicts in the files');
        info('3. Stage resolved files: git add .');
        info('4. Complete the merge: git commit');

        return self::FAILURE;
    }

    /**
     * Check if git is available.
     */
    protected function isGitAvailable(): bool
    {
        $result = Process::run('git --version');

        return $result->successful();
    }

    /**
     * Check if we're in a git repository.
     */
    protected function isGitRepository(): bool
    {
        $result = Process::run('git rev-parse --git-dir 2>/dev/null');

        return $result->successful();
    }

    /**
     * Get the upstream remote URL if it exists.
     */
    protected function getUpstreamRemote(): ?string
    {
        $result = Process::run('git remote get-url upstream 2>/dev/null');

        if (! $result->successful()) {
            return null;
        }

        return trim($result->output());
    }

    /**
     * Add the upstream remote.
     */
    protected function addUpstreamRemote(string $url): bool
    {
        $result = Process::run("git remote add upstream {$url}");

        return $result->successful();
    }

    /**
     * Get the current branch name.
     */
    protected function getCurrentBranch(): string
    {
        $result = Process::run('git rev-parse --abbrev-ref HEAD');

        if (! $result->successful()) {
            return 'main';
        }

        return trim($result->output());
    }

    /**
     * Check if there are upstream changes to merge.
     */
    protected function hasUpstreamChanges(string $branch): bool
    {
        $currentBranch = $this->getCurrentBranch();
        $result = Process::run("git rev-list HEAD..upstream/{$branch} --count");

        if (! $result->successful()) {
            return false;
        }

        $count = (int) trim($result->output());

        return $count > 0;
    }

    /**
     * Show a summary of upstream changes.
     */
    protected function showUpstreamChanges(string $branch): void
    {
        $currentBranch = $this->getCurrentBranch();
        $result = Process::run("git log --oneline HEAD..upstream/{$branch}");

        if ($result->successful()) {
            $commits = explode("\n", trim($result->output()));
            foreach ($commits as $commit) {
                if (! empty(trim($commit))) {
                    info("  • {$commit}");
                }
            }
        }

        // Show file statistics
        $statResult = Process::run("git diff --stat HEAD..upstream/{$branch}");
        if ($statResult->successful() && ! empty(trim($statResult->output()))) {
            info('');
            info('File changes:');
            $this->line($statResult->output());
        }
    }

    /**
     * Show detailed diff.
     */
    protected function showDetailedDiff(string $branch): void
    {
        $result = Process::run("git diff HEAD..upstream/{$branch}");
        if ($result->successful()) {
            $output = $result->output();
            if (strlen($output) > 2000) {
                warning('Diff is very long. Showing first 2000 characters...');
                $this->line(substr($output, 0, 2000));
                info('');
                info('... (truncated - use "git diff HEAD..upstream/'.$branch.'" to see full diff)');
            } else {
                $this->line($output);
            }
        }
    }

    /**
     * Check if there are uncommitted changes.
     */
    protected function hasUncommittedChanges(): bool
    {
        $result = Process::run('git status --porcelain');

        if (! $result->successful()) {
            return false;
        }

        return ! empty(trim($result->output()));
    }

    /**
     * Merge upstream changes into current branch.
     */
    protected function mergeUpstreamChanges(string $branch): bool
    {
        $result = Process::run("git merge upstream/{$branch} --no-edit");

        return $result->successful();
    }
}
