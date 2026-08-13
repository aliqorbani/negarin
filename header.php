<?php
/**
 * Document <head> and opening layout markup.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	$favicon = negarin_option( 'favicon' );
	if ( $favicon ) {
		echo '<link rel="icon" href="' . esc_url( wp_get_attachment_image_url( $favicon, 'thumbnail' ) ) . '">';
	}
	$head_scripts = negarin_option( 'head_scripts' );
	if ( $head_scripts ) {
		echo $head_scripts; // phpcs:ignore -- editor-controlled tracking snippet, intentional raw output.
	}
	wp_head();
	?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'رفتن به محتوای اصلی', 'negarin' ); ?></a>

<?php
$body_scripts = negarin_option( 'body_scripts' );
if ( $body_scripts ) {
	echo $body_scripts; // phpcs:ignore
}
?>

<?php
$is_bare_login_screen = function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in();
if ( ! $is_bare_login_screen ) :
	get_template_part( 'template-parts/header/announcement-bar' );
	get_template_part( 'template-parts/header/site-header' );
endif;
?>
<?php if ( ! $is_bare_login_screen ) : ?>
    <?php do_action( 'negarin_content_top' ); ?>
<?php endif; ?>