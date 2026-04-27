<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'storage', 'bootstrap/cache'])
    ->name('*.php');

return (new Config())
    ->setRiskyAllowed(false)

    ->setRules([
        '@PSR12' => true,

        /*
         * THIS is the correct modern fixer
         */
        'fully_qualified_strict_types' => true,

        /*
         * This is the key fixer for your use case
         */
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],

        /*
         * Required cleanup
         */
        'no_unused_imports' => true,

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],

        'single_import_per_statement' => true,

        'no_leading_import_slash' => true,

        'new_with_parentheses' => [
            'anonymous_class' => false,
            'named_class' => true,
        ],

        'single_line_empty_body' => true,

        'braces_position' => [
            'allow_single_line_anonymous_functions' => true,
            'allow_single_line_empty_anonymous_classes' => true,
            'anonymous_classes_opening_brace' => 'same_line',
            'anonymous_functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],

        'class_definition' => [
            'inline_constructor_arguments' => true,
            'multi_line_extends_each_single_line' => false,
            'single_item_single_line' => false,
            'single_line' => false,
            'space_before_parenthesis' => false,
        ],

        'method_chaining_indentation' => true,
    ])

    ->setFinder($finder);
