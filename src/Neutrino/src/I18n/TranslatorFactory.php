<?php

declare(strict_types=1);

namespace Neutrino\I18n;

use Laminas\I18n\Translator\Translator;
use Psr\Container\ContainerInterface;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container): Translator
    {
        $config           = $container->get('config');
        $translatorConfig = $config['translator'] ?? [];

        $translator = new Translator();

        // Set locale
        if (isset($translatorConfig['locale'])) {
            $translator->setLocale($translatorConfig['locale']);
        }

        // Set fallback locale
        if (isset($translatorConfig['fallback_locale'])) {
            $translator->setFallbackLocale($translatorConfig['fallback_locale']);
        }

        // Add translation files
        if (isset($translatorConfig['translation_file_patterns'])) {
            foreach ($translatorConfig['translation_file_patterns'] as $pattern) {
                $translator->addTranslationFilePattern(
                    $pattern['type'],
                    $pattern['base_dir'],
                    $pattern['pattern'],
                    $pattern['text_domain'] ?? 'default'
                );
            }
        }

        return $translator;
    }
}
