<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php');

return new PhpCsFixer\Config()
    ->setRiskyAllowed(false)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'blank_line_after_opening_tag' => true,
        'cast_spaces' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'line_ending' => true,
        'no_closing_tag' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
            ],
        ],
        'no_trailing_whitespace' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'single_blank_line_at_eof' => true,
        'single_import_per_statement' => true,
        'types_spaces' => [
            'space' => 'single',
        ],
    ])
    ->setFinder($finder);
