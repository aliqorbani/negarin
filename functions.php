<?php
/**
 * Negarin Theme bootstrap.
 *
 * @package Negarin
 */

use Negarin\Services\{
    AccountMenu,
    AddressBook,
    BlogFields,
    BuildCleaner,
    Captcha,
    CartAjax,
    CheckoutFields,
    ContactForm,
    FlexibleContent,
    FooterMessage,
    IconButtonBlock,
    OtpAuth,
    ProductFields,
    ProductSizing,
    QuickSearch,
    Seo,
    SmsNewsletter,
    SmsNewsletterBlock,
    ThemeOptions
};

if (!defined('ABSPATH')) {
    exit;
}

define('NEGARIN_VERSION', '1.0.1');
define('NEGARIN_DIR', get_template_directory());
define('NEGARIN_URI', get_template_directory_uri());

/**
 * PSR-4-ish autoloader for theme classes.
 * Negarin\Classes\Foo  => inc/classes/Foo.php
 * Negarin\Services\Foo => inc/services/Foo.php
 * Negarin\Helpers\Foo  => inc/helpers/Foo.php
 */
spl_autoload_register(
    function ($class) {
        $prefix = 'Negarin\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $parts = explode('\\', $relative);
        $class_name = array_pop($parts);

        // Remaining namespace segments map 1:1 to lowercase nested folders
        // under inc/, e.g. Negarin\Services\Sms\Foo => inc/services/Sms/Foo.php
        $folder_parts = $parts ? $parts : array('classes');
        $folder_parts[0] = strtolower($folder_parts[0]);

        $path = NEGARIN_DIR . '/inc/' . implode('/', $folder_parts) . '/' . $class_name . '.php';

        if (file_exists($path)) {
            require_once $path;
        }
    }
);

/**
 * Plain function-based includes (hooks, template tags, woocommerce glue).
 */
$negarin_hooks = [
    'setup.php',
    'enqueue.php',
    'acf.php',
    'woocommerce.php',
    'nav-menus.php',
    'image-sizes.php',
    'otp-guards.php',
    'turbo.php',
    'notices.php',
    'woocommerce.php',
    'ai-seo-box.php',
    'bale-notifier.php',
    'sms-notifier.php',
];
$negarin_hooks = array_unique($negarin_hooks);
foreach ($negarin_hooks as $hook_file) {
    $full = NEGARIN_DIR . '/inc/hooks/' . $hook_file;
    if (file_exists($full)) {
        require_once $full;
    }
}
$negarin_includes = array(
    '/inc/helpers/template-tags.php',
);
$negarin_includes = array_unique($negarin_includes);

foreach ($negarin_includes as $file) {
    $full = NEGARIN_DIR . $file;
    if (file_exists($full)) {
        require_once $full;
    }
}

/**
 * Boot service classes.
 */
add_action(
    'after_setup_theme',
    function () {
        new ThemeOptions();
        new FlexibleContent();
        new OtpAuth();
        new QuickSearch();
        new ProductSizing();
        new CartAjax();
        new ProductFields();
        new CheckoutFields();
        new AccountMenu();
        new AddressBook();
        new BlogFields();
        new Seo();
        new FooterMessage();
        new Captcha();
        new ContactForm();
        new BuildCleaner();
        new IconButtonBlock();
        new SmsNewsletter();
        new SmsNewsletterBlock();
    }
);