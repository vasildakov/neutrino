# Translation Setup for Neutrino

## Overview

Laminas i18n translation has been enabled for the Neutrino application using Mezzio.

## Installation

The following packages have been installed:
- `laminas/laminas-i18n` - Core internationalization component
- `laminas/laminas-mvc-i18n` - MVC integration for i18n
- `laminas/laminas-translator` - Translation component

## Configuration

### Translator Configuration
Located at: `config/autoload/translator.global.php`

```php
return [
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type' => 'phpArray',
                'base_dir' => getcwd() . '/data/language',
                'pattern' => '%s/messages.php',
            ],
        ],
    ],
];
```

### Language Files
Translation files are located in `data/language/`:
- `en_US/messages.php` - English (US)
- `bg_BG/messages.php` - Bulgarian
- `de_DE/messages.php` - German

## Usage in Templates

### Using the Translate View Helper

The translate helper is available in all templates with two aliases:
- `$this->translate('text')` - Full name
- `$this->t('text')` - Short alias (recommended)

### Examples

```php
<!-- Simple translation -->
<h1><?= $this->t('Welcome') ?></h1>

<!-- Translation with default text domain -->
<button><?= $this->t('Login') ?></button>

<!-- Translation in attributes -->
<a href="#" title="<?= $this->t('Click here') ?>">Link</a>
```

## Adding New Translations

### 1. Add to English file (`data/language/en_US/messages.php`):
```php
return [
    'New Key' => 'New Translation',
];
```

### 2. Add corresponding translations to other languages:

**Bulgarian** (`data/language/bg_BG/messages.php`):
```php
return [
    'New Key' => 'Нов превод',
];
```

**German** (`data/language/de_DE/messages.php`):
```php
return [
    'New Key' => 'Neue Übersetzung',
];
```

## Changing Locale

### In Controller/Handler
```php
use Laminas\I18n\Translator\TranslatorInterface;

public function __construct(
    private readonly TranslatorInterface $translator
) {}

public function handle(ServerRequestInterface $request): ResponseInterface
{
    // Change locale
    $this->translator->setLocale('bg_BG');
    
    // Your code...
}
```

### In Template
```php
<?php $this->t()->setLocale('bg_BG'); ?>
```

## Supported Languages

Currently configured languages:
- **English (US)** - `en_US` (default)
- **Bulgarian** - `bg_BG`
- **German** - `de_DE`

## Translation Keys Available

### General
- Welcome, Home, About, Contact
- Login, Logout, Register, Dashboard

### Pricing
- Our Pricing, Monthly, Yearly, Save 30%, Choose Plan

### Plans
- Basic Plan, Premium Plan, Corporate Plan, Community Plan

### Features
- Project, Projects, API Access, Storage
- Weekly Reports, 7/24 Support

### User
- Profile, Settings, Account
- User Activity, User Roles

### Actions
- Edit, Delete, Save, Cancel, Submit, Search

### Messages
- Success, Error, Warning, Info

## Files Modified

1. `config/autoload/translator.global.php` - Translator configuration
2. `config/autoload/dependencies.global.php` - Added translator aliases
3. `src/Neutrino/src/ConfigProvider.php` - Added translate view helper
4. `src/Neutrino/src/View/Helper/Translate.php` - Translate helper class
5. `src/Neutrino/src/View/Helper/TranslateFactory.php` - Helper factory
6. `src/Neutrino/templates/sandbox/home.phtml` - Added translations to home page

## Best Practices

1. **Use descriptive keys**: Use the English text as the key
2. **Keep translations consistent**: Use the same translation for the same meaning
3. **Use short alias**: Use `$this->t()` instead of `$this->translate()`
4. **Group related translations**: Organize translation keys by feature/module
5. **Always provide fallback**: English should always be complete

## Testing Translations

To test translations in different languages, you can temporarily change the default locale in `config/autoload/translator.global.php`:

```php
return [
    'translator' => [
        'locale' => 'bg_BG', // Change to Bulgarian
        // ...
    ],
];
```

## Future Enhancements

- Add locale detection from browser/user preferences
- Add locale switcher in UI
- Add more languages (French, Spanish, Italian, etc.)
- Add database-backed translations for dynamic content
- Add translation management interface

