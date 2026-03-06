<?php

/**
 * Example: How to use translations in Neutrino
 */

// ============================================
// 1. IN TEMPLATES (.phtml files)
// ============================================

// Simple translation
echo $this->t('Welcome');  // Output: Welcome (en_US) or Добре дошли (bg_BG)

// Translation with variables (use sprintf)
echo sprintf($this->t('Hello, %s!'), $userName);

// Translation in HTML attributes
?>
<button title="<?= $this->t('Click here') ?>">
    <?= $this->t('Submit') ?>
</button>

<a href="#" aria-label="<?= $this->t('Home') ?>">
    <?= $this->t('Home') ?>
</a>
<?php

// ============================================
// 2. IN HANDLERS/CONTROLLERS
// ============================================

use Laminas\I18n\Translator\TranslatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExampleHandler
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Translate a message
        $message = $this->translator->translate('Success');

        // Change locale temporarily
        $this->translator->setLocale('bg_BG');
        $bulgarianMessage = $this->translator->translate('Success'); // "Успех"

        // Get current locale
        $currentLocale = $this->translator->getLocale();

        // Pass translated messages to template
        return new HtmlResponse($this->template->render('template::name', [
            'message' => $message,
            'locale' => $currentLocale,
        ]));
    }
}

// ============================================
// 3. ADDING NEW TRANSLATIONS
// ============================================

// File: data/language/en_US/messages.php
return [
    'Welcome to %s' => 'Welcome to %s',
    'You have %d new messages' => 'You have %d new messages',
    'Settings saved successfully' => 'Settings saved successfully',
];

// File: data/language/bg_BG/messages.php
return [
    'Welcome to %s' => 'Добре дошли в %s',
    'You have %d new messages' => 'Имате %d нови съобщения',
    'Settings saved successfully' => 'Настройките са запазени успешно',
];

// ============================================
// 4. PLURALIZATION
// ============================================

// In template
echo sprintf(
    $this->t('You have %d new messages'),
    $messageCount
);

// For better pluralization, you can use conditions:
if ($count === 1) {
    echo $this->t('1 item');
} else {
    echo sprintf($this->t('%d items'), $count);
}

// ============================================
// 5. LOCALE SWITCHER EXAMPLE
// ============================================
?>

<div class="locale-switcher">
    <select id="locale-selector" class="form-select">
        <option value="en_US" <?= $this->t()->getLocale() === 'en_US' ? 'selected' : '' ?>>
            English
        </option>
        <option value="bg_BG" <?= $this->t()->getLocale() === 'bg_BG' ? 'selected' : '' ?>>
            Български
        </option>
        <option value="de_DE" <?= $this->t()->getLocale() === 'de_DE' ? 'selected' : '' ?>>
            Deutsch
        </option>
    </select>
</div>

<script>
document.getElementById('locale-selector').addEventListener('change', function() {
    // Send AJAX request to change locale or redirect with locale parameter
    window.location.href = '?locale=' + this.value;
});
</script>

<?php

// ============================================
// 6. TEXT DOMAINS (OPTIONAL - for organizing translations)
// ============================================

// You can organize translations by domain (e.g., 'navigation', 'forms', 'errors')

// In template:
echo $this->translate('Home', 'navigation');
echo $this->translate('Required field', 'forms');
echo $this->translate('Page not found', 'errors');

// ============================================
// 7. COMMON PATTERNS
// ============================================

// Page titles
?>
<title><?= $this->t('Dashboard') ?> - Neutrino SaaS</title>

<?php
// Breadcrumbs
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><?= $this->t('Home') ?></a></li>
        <li class="breadcrumb-item"><a href="/settings"><?= $this->t('Settings') ?></a></li>
        <li class="breadcrumb-item active"><?= $this->t('Profile') ?></li>
    </ol>
</nav>

<?php
// Form labels
?>
<form>
    <label for="email"><?= $this->t('Email') ?></label>
    <input type="email" id="email" placeholder="<?= $this->t('Enter your email') ?>">

    <label for="password"><?= $this->t('Password') ?></label>
    <input type="password" id="password" placeholder="<?= $this->t('Enter your password') ?>">

    <button type="submit"><?= $this->t('Login') ?></button>
</form>

<?php
// Alert messages
?>
<div class="alert alert-success">
    <?= $this->t('Settings saved successfully') ?>
</div>

<div class="alert alert-danger">
    <?= $this->t('Error') ?>: <?= $this->t('Please check your input') ?>
</div>

