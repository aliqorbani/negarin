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
$is_bare_login_screen = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
if ( ! $is_bare_login_screen ) :
	get_template_part( 'template-parts/footer/site-footer' );
endif;
?>

<?php wp_footer(); ?>
</body>
</html>
