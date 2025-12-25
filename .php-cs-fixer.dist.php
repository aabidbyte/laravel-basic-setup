<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules')
    ->exclude('storage')
    ->exclude('bootstrap/cache')
    ->name('*.php');

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Laravel' => true,
        'method_chaining_indentation' => true,
        // These rules help with line breaking but don't force it
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => true,
    ])
    ->setLineEnding("\n")
    ->setIndent("    ")
    ->setFinder($finder)
    ->setLineLength(100); // This is a guideline, not enforced automatically

