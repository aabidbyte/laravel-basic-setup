<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use App\Services\EmailTemplate\BladeTemplateParser;
use App\Services\I18nService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

/**
 * Email Template Seeder.
 *
 * Scans the resources/views/emails folder and seeds
 * email templates (both layouts and contents) into the database using
 * Blade rendering for all supported locales.
 */
class EmailTemplateSeeder extends Seeder
{
    protected string $templatesPath = 'resources/views/emails/templates';

    protected string $layoutsPath = 'resources/views/emails/layouts';

    public function __construct(
        protected BladeTemplateParser $parser,
        protected I18nService $i18nService,
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $originalLocale = App::getLocale();

        try {
            $this->seedLayouts();
            $this->seedContents();
        } finally {
            App::setLocale($originalLocale);
        }
    }

    /**
     * Seed all email layouts from the layouts folder.
     */
    protected function seedLayouts(): void
    {
        $path = base_path($this->layoutsPath);

        if (! File::isDirectory($path)) {
            $this->command->warn("Layouts directory not found: {$path}");

            return;
        }

        foreach (File::files($path) as $file) {
            $this->seedLayoutFile($file);
        }
    }

    /**
     * Seed a single layout file.
     */
    protected function seedLayoutFile(SplFileInfo $file): void
    {
        if (! $this->isBladeFile($file)) {
            return;
        }

        // Still parse metadata for DB columns
        try {
            $metadata = $this->parser->parse($file->getPathname());
        } catch (Exception $e) {
            $metadata = [];
        }

        $name = $metadata['name'] ?? $this->extractName($file);

        $layout = EmailTemplate::updateOrCreate(
            ['name' => $name],
            [
                'is_layout' => true,
                'description' => $metadata['description'] ?? null,
                'type' => $metadata['type'] ?? EmailTemplateType::TRANSACTIONAL,
                'entity_types' => $metadata['entity_types'] ?? [],
                'context_variables' => $metadata['context_variables'] ?? [],
                'status' => EmailTemplateStatus::PUBLISHED,
                'is_system' => true,
                'is_default' => $metadata['is_default'] ?? ($name === 'default'),
                'all_teams' => true,
            ],
        );

        $this->command->info("Seeded layout: {$name}");

        $this->seedTranslations($layout, $file);
    }

    /**
     * Seed all email contents from the templates folder.
     */
    protected function seedContents(): void
    {
        $path = base_path($this->templatesPath);

        if (! File::isDirectory($path)) {
            $this->command->warn("Templates directory not found: {$path}");

            return;
        }

        // Recursive scan
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if (! $this->isBladeFile($file)) {
                continue;
            }
            $this->seedContent($file);
        }
    }

    /**
     * Seed a single content.
     */
    protected function seedContent(SplFileInfo $file): void
    {
        try {
            $metadata = $this->parser->parse($file->getPathname());
        } catch (Exception $e) {
            $this->command->error("Failed to parse {$file->getFilename()}: {$e->getMessage()}");

            return;
        }

        if (empty($metadata)) {
            // Skip files without metadata (might be partials)
            return;
        }

        $content = EmailTemplate::updateOrCreate(
            ['name' => $metadata['name']],
            [
                'is_layout' => false,
                'description' => $metadata['description'] ?? null,
                'type' => $metadata['type'] ?? EmailTemplateType::TRANSACTIONAL,
                'entity_types' => $metadata['entity_types'] ?? [],
                'context_variables' => $metadata['context_variables'] ?? [],
                'is_system' => true, // Seeding implies system content
                'status' => EmailTemplateStatus::PUBLISHED,
                'all_teams' => true,
                'layout_id' => $this->resolveLayoutId($metadata['layout'] ?? null),
            ],
        );

        $this->command->info("Seeded content: {$content->name}");

        $this->seedTranslations($content, $file);
    }

    /**
     * Resolve layout ID by name.
     */
    protected function resolveLayoutId(?string $layoutName): ?int
    {
        if (! $layoutName) {
            return EmailTemplate::query()->layouts()->where('is_default', true)->value('id');
        }

        return EmailTemplate::query()->layouts()->where('name', $layoutName)->value('id');
    }

    /**
     * Seed translations for all supported locales.
     */
    protected function seedTranslations(EmailTemplate $entity, SplFileInfo $file): void
    {
        // Get view path relative to resources/views
        $relativePath = Str::after($file->getPathname(), 'resources/views/');
        $viewName = str_replace(['/', '.blade.php'], ['.', ''], $relativePath);

        $locales = config('i18n.supported_locales', ['en_US' => 'English']);

        foreach (array_keys($locales) as $locale) {
            App::setLocale($locale);

            try {
                // Re-parse metadata in current locale context to resolve translations in PHP block
                $metadata = $this->parser->parse($file->getPathname());

                // Generate mock variables to prevent "Undefined variable" errors during seeding
                $entityTypes = $entity->entity_types ?? [];
                $contextVariables = $entity->context_variables ?? [];

                $mockData = $this->generateMockData($entityTypes, $contextVariables);
                $mockData['slot'] = '{{ $slot }}'; // Preserve slot tag in DB

                $html = view($viewName, $mockData)->render();
            } catch (Exception $e) {
                $name = $entity->name ?? 'Unknown';
                $this->command->error("  Failed to render {$locale} for {$name}: {$e->getMessage()}");

                continue;
            }

            // Use translated subject from metadata, fallback only for contents
            $subject = $metadata['subject'] ?? null;
            if ($subject === null && $entity->isContent()) {
                $subject = __('emails.' . Str::slug($entity->name, '_') . '.subject');
                // Note: If translations are missing in 'emails' file, this might return key name.
            }

            $preheader = $metadata['preheader'] ?? null;

            $entity->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'subject' => $subject,
                    'preheader' => $preheader,
                    'html_content' => $html,
                    'text_content' => strip_tags($html), // Auto-generate text version
                ],
            );

            $this->command->info("  - Seeded {$locale}");
        }
    }

    protected function isBladeFile(SplFileInfo $file): bool
    {
        return Str::endsWith($file->getFilename(), '.blade.php');
    }

    protected function extractName(SplFileInfo $file): string
    {
        return Str::beforeLast($file->getFilename(), '.blade.php');
    }

    /**
     * Generate mock data for view rendering.
     * Returns placeholder objects that behave like strings or properties but output merge tags.
     */
    protected function generateMockData(array $entityTypes, array $contextVariables): array
    {
        $data = [];

        // For each entity type (e.g. 'user'), create a proxy object
        foreach ($entityTypes as $type) {
            $data[$type] = new class($type)
            {
                public function __construct(private string $type) {}

                public function __get($name)
                {
                    // Return the merge tag string: {{ type.name }}
                    return '{{ ' . $this->type . ".{$name} }}";
                }

                public function __toString()
                {
                    return '{{ ' . $this->type . ' }}';
                }
            };
        }

        // For context variables, return the merge tag string directly
        foreach ($contextVariables as $var) {
            $data[$var] = "{{ action.{$var} }}";
        }

        return $data;
    }
}
