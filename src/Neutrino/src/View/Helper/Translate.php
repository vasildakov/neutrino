<?php

declare(strict_types=1);

namespace Neutrino\View\Helper;

use Laminas\Translator\TranslatorInterface;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Renderer\RendererInterface;

class Translate implements HelperInterface
{
    private ?RendererInterface $view = null;

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Set the View object
     */
    public function setView(RendererInterface $view): HelperInterface
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the View object
     */
    public function getView(): ?RendererInterface
    {
        return $this->view;
    }

    /**
     * Translate a message
     *
     * @param string $message The message to translate
     * @param string|null $textDomain The text domain (optional)
     * @param string|null $locale The locale (optional)
     * @return string The translated message
     */
    public function __invoke(string $message, ?string $textDomain = 'default', ?string $locale = null): string
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }

    /**
     * Get the translator instance
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
