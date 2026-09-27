<?php
/**
 * Custom search form markup — matches `.negarin-field-input` styling used
 * everywhere else, instead of WordPress's unstyled default. Currently only
 * shown on 404.php.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form role="search" method="get" class="flex gap-2" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="sr-only" for="negarin-search-field"><?php esc_html_e( 'جستجو', 'negarin' ); ?></label>
    <input
        type="search"
        id="negarin-search-field"
        name="s"
        class="negarin-field-input flex-1"
        placeholder="<?php esc_attr_e( 'جستجو در محصولات...', 'negarin' ); ?>"
        value="<?php echo esc_attr( get_search_query() ); ?>"
    >
    <button type="submit" class="btn btn--solid shrink-0"><?php esc_html_e( 'جستجو', 'negarin' ); ?></button>
</form>