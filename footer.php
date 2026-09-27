<?php
/**
 * Closing layout markup.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

//$is_bare_login_screen = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
//if ( $show_site_footer ) :
	get_template_part( 'template-parts/footer/site-footer' );
//endif;
?>

<?php get_template_part( 'template-parts/components/toast-container' ); ?>
<?php get_template_part( 'template-parts/components/cart-added-modal' ); ?>

<?php wp_footer(); ?>
</body>
</html>
