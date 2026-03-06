<?php

declare(strict_types=1);

use Laminas\I18n\Translator\Loader\Gettext;
use Laminas\I18n\Translator\Loader\PhpArray;
use Laminas\I18n\Translator\TranslatorInterface;

return [
    'dependencies' => [
        'factories' => [],
        'aliases'   => [
            'MvcTranslator' => TranslatorInterface::class,
        ],
    ],
    'translator'   => [
        'locale'                    => 'bg_BG',
        'fallback_locale'           => 'en_US',
        'translation_file_patterns' => [
            [
                'type'        => Gettext::class,
                'base_dir'    => getcwd() . '/data/language',
                'pattern'     => '%s/messages.mo',
                'text_domain' => 'default',
            ],
            [
                'type'     => PhpArray::class,
                'base_dir' => getcwd() . '/data/languages',
                'pattern'  => '%s.php',
            ],
        ],
    ],
];
