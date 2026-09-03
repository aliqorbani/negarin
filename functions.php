<?php
/**
 * Negarin Theme bootstrap.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEGARIN_VERSION', '1.0.0' );
define( 'NEGARIN_DIR', get_template_directory() );
define( 'NEGARIN_URI', get_template_directory_uri() );

/**
 * PSR-4-ish autoloader for theme classes.
 * Negarin\Classes\Foo  => inc/classes/Foo.php
 * Negarin\Services\Foo => inc/services/Foo.php
 * Negarin\Helpers\Foo  => inc/helpers/Foo.php
 */
spl_autoload_register(
    function ( $class ) {
        $prefix = 'Negarin\\';

        if ( ! str_starts_with( $class, $prefix ) ) {
            return;
        }

        $relative   = substr( $class, strlen( $prefix ) );
        $parts      = explode( '\\', $relative );
        $class_name = array_pop( $parts );

        // Remaining namespace segments map 1:1 to lowercase nested folders
        // under inc/, e.g. Negarin\Services\Sms\Foo => inc/services/Sms/Foo.php
        $folder_parts = $parts ? $parts : array( 'classes' );
        $folder_parts[0] = strtolower( $folder_parts[0] );

        $path = NEGARIN_DIR . '/inc/' . implode( '/', $folder_parts ) . '/' . $class_name . '.php';

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
);

/**
 * Plain function-based includes (hooks, template tags, woocommerce glue).
 */
$negarin_includes = array(
    '/inc/helpers/template-tags.php',
    '/inc/hooks/setup.php',
    '/inc/hooks/enqueue.php',
    '/inc/hooks/acf.php',
    '/inc/hooks/woocommerce.php',
    '/inc/hooks/nav-menus.php',
    '/inc/hooks/image-sizes.php',
    '/inc/hooks/otp-guards.php',
    '/inc/hooks/turbo.php',
    '/inc/hooks/woocommerce.php',
);
$negarin_includes = array_unique( $negarin_includes );

foreach ( $negarin_includes as $file ) {
    $full = NEGARIN_DIR . $file;
    if ( file_exists( $full ) ) {
        require_once $full;
    }
}

/**
 * Boot service classes.
 */
add_action(
    'after_setup_theme',
    function () {
        new \Negarin\Services\ThemeOptions();
        new \Negarin\Services\FlexibleContent();
        new \Negarin\Services\OtpAuth();
        new \Negarin\Services\QuickSearch();
        new \Negarin\Services\CustomOrder();
        new \Negarin\Services\ProductFields();
        new \Negarin\Services\CheckoutFields();
        new \Negarin\Services\AccountMenu();
        new \Negarin\Services\AddressBook();
        new \Negarin\Services\BlogFields();
        new \Negarin\Services\Seo();
        new \Negarin\Services\FooterMessage();
        new \Negarin\Services\BuildCleaner();
    }
);