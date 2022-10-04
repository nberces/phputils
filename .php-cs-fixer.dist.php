<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->exclude('vendor')
    ->in(__DIR__);

$config = new Config();

$config
    ->setFinder($finder)
    ->setRules(
        [
            '@PSR12' => true,
            'array_syntax' => [
                'syntax' => 'short'
            ],
            'class_attributes_separation' => [
                'elements' => [
                    'method' => 'one'
                ]
            ],
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'method_public_abstract_static',
                    'method_protected_abstract_static',
                    'method_abstract',
                    'method_public_abstract',
                    'method_protected_abstract',
                    'method_static',
                    'method_public_static',
                    'method_protected_static',
                    'method_private_static',
                    'phpunit',
                    'method_public',
                    'method_protected',
                    'method_private'
                ],
                'sort_algorithm' => 'alpha'
            ],
            'ordered_imports' => [
                'sort_algorithm' => 'alpha'
            ],
            'phpdoc_order' => true,
            'phpdoc_trim' => true
        ]
    );

return $config;
