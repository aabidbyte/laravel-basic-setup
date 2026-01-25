<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use App\Models\EmailTemplate\EmailTemplate;
use App\Models\EmailTemplate\EmailTranslation;
use App\Services\I18nService;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Email Renderer Service.
 *
 * Renders email templates by composing layout + content,
 * resolving merge tags, and handling locale fallback.
 */
class EmailRenderer
{
    public function __construct(
        protected MergeTagEngine $mergeTagEngine,
        protected I18nService $i18nService,
    ) {}

    /**
     * Render an email template.
     *
     * @param  array<string, Model>  $entities
     * @param  array<string, string>  $contextVariables
     *
     * @throws InvalidArgumentException
     */
    public function render(
        EmailTemplate $template,
        array $entities = [],
        array $contextVariables = [],
        ?string $locale = null,
    ): RenderedEmail {
        $translation = $this->getTranslationForLocale($template, $locale);
        $this->configureTagEngine($entities, $contextVariables);

        return $this->buildRenderedEmail($template, $translation, $locale);
    }

    /**
     * Get translation with locale fallback.
     */
    protected function getTranslationForLocale(EmailTemplate $template, ?string $locale): EmailTranslation
    {
        $locale = $this->i18nService->getValidLocale($locale);
        $fallbackLocale = $this->i18nService->getFallbackLocale();
        // The getTranslationWithFallback method now returns EmailTranslation
        $translation = $template->getTranslationWithFallback($locale, $fallbackLocale);

        if ($translation === null) {
            throw new InvalidArgumentException(
                "No translation found for template '{$template->name}' in locale '{$locale}' or fallback '{$fallbackLocale}'",
            );
        }

        return $translation;
    }

    /**
     * Configure merge tag engine with entities and context.
     *
     * @param  array<string, Model>  $entities
     * @param  array<string, string>  $contextVariables
     */
    protected function configureTagEngine(array $entities, array $contextVariables): void
    {
        $this->mergeTagEngine->reset();
        $this->mergeTagEngine->setEntities($entities);
        $this->mergeTagEngine->setContextVariables($contextVariables);
    }

    /**
     * Build rendered email from translation.
     */
    /**
     * Build rendered email from translation.
     */
    protected function buildRenderedEmail(
        EmailTemplate $template,
        EmailTranslation $translation,
        ?string $locale,
    ): RenderedEmail {
        // Resolve metadata immediately (they don't use layout)
        $subject = $this->mergeTagEngine->resolve($translation->subject);
        $preheader = $translation->preheader ? $this->mergeTagEngine->resolve($translation->preheader) : null;

        // Get raw content (generating text from HTML if needed)
        $rawHtml = $translation->html_content;
        $rawText = $translation->text_content ?: $this->generatePlainText($rawHtml);

        // Compose with layout
        $composed = $this->composeWithLayout($template, ['html' => $rawHtml, 'text' => $rawText]);

        // Resolve tags in the final composed content (Layout + Template)
        return new RenderedEmail(
            subject: $subject,
            html: $this->mergeTagEngine->resolve($composed['html']),
            text: $this->mergeTagEngine->resolve($composed['text']),
            preheader: $preheader,
            locale: $locale ?? $translation->locale,
            templateName: $template->name,
        );
    }

    /**
     * Resolve preheader if present.
     */
    protected function resolvePreheader(EmailTranslation $translation): ?string
    {
        return $translation->preheader
            ? $this->mergeTagEngine->resolve($translation->preheader)
            : null;
    }

    /**
     * Compose content with layout.
     *
     * @param  array{html: string, text: string}  $content
     * @return array{html: string, text: string}
     */
    protected function composeWithLayout(EmailTemplate $template, array $content): array
    {
        // If content is already a full HTML document, skip layout composition
        if (stripos($content['html'], '<!DOCTYPE html') !== false || stripos($content['html'], '<html') !== false) {
            return $content;
        }

        // Get the layout (self-referential relationship) or default layout
        $layout = $template->layout ?? EmailTemplate::getDefaultLayout();

        if ($layout === null) {
            return $content;
        }

        $composer = app(EmailLayoutComposer::class);

        return [
            'html' => $composer->compose($layout, $content['html']),
            'text' => $composer->composeText($layout, $content['text']),
        ];
    }

    /**
     * Render a template by name.
     *
     * @param  array<string, Model>  $entities
     * @param  array<string, string>  $contextVariables
     *
     * @throws InvalidArgumentException
     */
    public function renderByName(
        string $templateName,
        array $entities = [],
        array $contextVariables = [],
        ?string $locale = null,
    ): RenderedEmail {
        $template = $this->findTemplateByName($templateName);

        return $this->render($template, $entities, $contextVariables, $locale);
    }

    /**
     * Find template by name or throw.
     */
    protected function findTemplateByName(string $templateName): EmailTemplate
    {
        $template = EmailTemplate::findByName($templateName);

        if ($template === null) {
            throw new InvalidArgumentException("Email template '{$templateName}' not found or not published");
        }

        return $template;
    }

    /**
     * Preview a template with mock data.
     */
    public function preview(EmailTemplate $template, ?string $locale = null): RenderedEmail
    {
        $mockEntities = $this->generateMockEntities($template->entity_types ?? []);
        $mockContext = $this->generateMockContext($template->context_variables ?? []);

        return $this->render($template, $mockEntities, $mockContext, $locale);
    }

    /**
     * Preview a translation directly.
     *
     * @param  array<string, Model>  $entities
     * @param  array<string, mixed>  $contextVariables
     */
    public function previewTranslation(
        EmailTranslation $translation,
        array $entities = [],
        array $contextVariables = [],
    ): RenderedEmail {
        $translation->loadMissing('translatable');
        $template = $translation->translatable; // Changed from template to translatable (polymorphic)

        // Use provided entities or generate mocks
        $finalEntities = empty($entities)
            ? $this->generateMockEntities($template->entity_types ?? [])
            : $entities;

        // Use provided context or generate mocks
        $finalContext = empty($contextVariables)
            ? $this->generateMockContext($template->context_variables ?? [])
            : $contextVariables;

        $this->configureTagEngine($finalEntities, $finalContext);

        return $this->buildRenderedEmail($template, $translation, $translation->locale);
    }

    /**
     * Generate mock entity instances.
     *
     * @param  array<string>  $entityTypes
     * @return array<string, Model>
     */
    protected function generateMockEntities(array $entityTypes): array
    {
        $entities = [];

        foreach ($entityTypes as $type) {
            $entity = $this->createMockEntity($type);
            if ($entity !== null) {
                $entities[$type] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Create a single mock entity.
     */
    protected function createMockEntity(string $type): ?Model
    {
        $modelClass = app(EntityTypeRegistry::class)->getModelClass($type);

        if ($modelClass === null) {
            return null;
        }

        $model = new $modelClass;
        $this->fillModelWithMockData($model);

        return $model;
    }

    /**
     * Fill model with mock data.
     */
    protected function fillModelWithMockData(Model $model): void
    {
        foreach ($model->getFillable() as $attribute) {
            $value = $this->getMockValueForAttribute($attribute);
            if ($value !== null) {
                $model->setAttribute($attribute, $value);
            }
        }
    }

    /**
     * Get mock value for attribute.
     */
    protected function getMockValueForAttribute(string $attribute): ?string
    {
        return match (true) {
            str_contains($attribute, 'name') => 'John Doe',
            str_contains($attribute, 'email') && ! str_ends_with($attribute, '_at') => 'john.doe@example.com',
            str_contains($attribute, 'username') => 'johndoe',
            str_contains($attribute, 'title') => 'Example Title',
            str_contains($attribute, 'description') => 'Sample description.',
            default => null,
        };
    }

    /**
     * Generate mock context variables.
     *
     * @param  array<string>  $contextVariables
     * @return array<string, string>
     */
    protected function generateMockContext(array $contextVariables): array
    {
        $context = [];

        foreach ($contextVariables as $key) {
            $context[$key] = 'https://example.com/action/' . str_replace('_', '-', $key);
        }

        return $context;
    }

    /**
     * Generate plain text from HTML.
     */
    protected function generatePlainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Validate template tags.
     *
     * @return array{subject: array<string>, html: array<string>, text: array<string>}
     */
    public function validateTemplate(EmailTemplate $template): array
    {
        $errors = ['subject' => [], 'html' => [], 'text' => []];

        foreach ($template->translations as $translation) {
            $errors = $this->mergeTranslationErrors($errors, $template, $translation);
        }

        return $this->deduplicateErrors($errors);
    }

    /**
     * Merge errors from a single translation.
     *
     * @param  array{subject: array<string>, html: array<string>, text: array<string>}  $errors
     * @return array{subject: array<string>, html: array<string>, text: array<string>}
     */
    protected function mergeTranslationErrors(
        array $errors,
        EmailTemplate $template,
        EmailTranslation $translation,
    ): array {
        $entityTypes = $template->entity_types ?? [];
        $contextKeys = $template->context_variables ?? [];

        $errors['subject'] = array_merge(
            $errors['subject'],
            $this->mergeTagEngine->validateTags($translation->subject, $entityTypes, $contextKeys),
        );

        $errors['html'] = array_merge(
            $errors['html'],
            $this->mergeTagEngine->validateTags($translation->html_content, $entityTypes, $contextKeys),
        );

        if ($translation->text_content) {
            $errors['text'] = array_merge(
                $errors['text'],
                $this->mergeTagEngine->validateTags($translation->text_content, $entityTypes, $contextKeys),
            );
        }

        return $errors;
    }

    /**
     * Remove duplicate errors.
     *
     * @param  array{subject: array<string>, html: array<string>, text: array<string>}  $errors
     * @return array{subject: array<string>, html: array<string>, text: array<string>}
     */
    protected function deduplicateErrors(array $errors): array
    {
        return [
            'subject' => array_values(array_unique($errors['subject'])),
            'html' => array_values(array_unique($errors['html'])),
            'text' => array_values(array_unique($errors['text'])),
        ];
    }
}
