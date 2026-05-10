<?php

$header = <<<'EOF'
This file is part of the Discogs API Bundle.

(c) 2026 Tamas Gere
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->notName('*Test.php')  // keep test formatting more flexible
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => ['statements' => ['return']],
        'cast_spaces' => true,
        'class_attributes_separation' => true,
        'compact_nullable_typehint' => false,  # PHP 8.2 style
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'single'],
        'elseif' => true,
        'no_blank_lines_after_class_opening' => false,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'no_trailing_comma_in_singleline' => false,
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => true,
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'phpdoc_indent' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_types' => true,
        'single_import_per_statement' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder)
;
