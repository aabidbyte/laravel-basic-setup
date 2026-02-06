<?php

use App\Enums\Ui\ThemeColorTypes;

/**
 * Generate Alpine.js class mapping for dynamic colors.
 * This uses literal strings to ensure Tailwind 4 scanner picks them up without a manual safelist.
 * The scanner will see the strings inside the match expression.
 */
function alpineColorClasses(string $expression, string $prefix = 'btn-'): string
{
    return match ($prefix) {
        'btn-' => "{
            'btn-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'btn-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'btn-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'btn-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'btn-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'btn-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'btn-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'btn-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'text-', 'loading-' => "{
            'text-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'text-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'text-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'text-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'text-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'text-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'text-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'text-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'badge-' => "{
            'badge-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'badge-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'badge-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'badge-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'badge-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'badge-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'badge-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'badge-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'input-' => "{
            'input-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'input-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'input-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'input-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'input-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'input-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'input-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'input-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'textarea-' => "{
            'textarea-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'textarea-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'textarea-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'textarea-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'textarea-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'textarea-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'textarea-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'textarea-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'checkbox-' => "{
            'checkbox-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'checkbox-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'checkbox-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'checkbox-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'checkbox-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'checkbox-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'checkbox-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'checkbox-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'radio-' => "{
            'radio-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'radio-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'radio-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'radio-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'radio-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'radio-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'radio-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'radio-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'toggle-' => "{
            'toggle-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'toggle-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'toggle-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'toggle-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'toggle-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'toggle-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'toggle-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'toggle-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'range-' => "{
            'range-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'range-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'range-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'range-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'range-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'range-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'range-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'range-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        'progress-' => "{
            'progress-primary': ($expression) === '" . ThemeColorTypes::PRIMARY->value . "',
            'progress-secondary': ($expression) === '" . ThemeColorTypes::SECONDARY->value . "',
            'progress-accent': ($expression) === '" . ThemeColorTypes::ACCENT->value . "',
            'progress-neutral': ($expression) === '" . ThemeColorTypes::NEUTRAL->value . "',
            'progress-info': ($expression) === '" . ThemeColorTypes::INFO->value . "',
            'progress-success': ($expression) === '" . ThemeColorTypes::SUCCESS->value . "',
            'progress-warning': ($expression) === '" . ThemeColorTypes::WARNING->value . "',
            'progress-error': ($expression) === '" . ThemeColorTypes::ERROR->value . "'
        }",
        default => (function () use ($expression, $prefix) {
            $colors = ThemeColorTypes::values();
            $mappings = [];
            foreach ($colors as $color) {
                $mappings["{$prefix}{$color}"] = "({$expression}) === '{$color}'";
            }

            return \json_encode((object) $mappings);
        })(),
    };
}
