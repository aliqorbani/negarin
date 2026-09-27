<?php
/**
 * "تماس با ما" contact form. Each submission is stored as a
 * `negarin_contact_msg` post — viewable, searchable, and deletable from a
 * normal wp-admin list screen, no custom DB table needed. Front end:
 * templates/page-contact.php + template-parts/components/contact-form.php
 * + assets/js/contact-form.js. Self-hosted CAPTCHA verification via
 * Services/Captcha.php — no reCAPTCHA/hCaptcha dependency.
 *
 * POST /wp-json/negarin/v1/contact/submit
 *   body: { name, phone, email, subject, message, website, captcha_token, captcha_answer }
 *
 * Admin list adds phone/email/subject/message columns, bolds unread rows,
 * and shows an unread count badge on the menu item (same "awaiting-mod"
 * style WordPress core uses for pending comments) — read is marked the
 * moment an admin opens a submission.
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Post;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ContactForm {

    public const POST_TYPE = 'negarin_contact_msg';

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'admin_columns' ) );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_admin_column' ), 10, 2 );
        add_filter( 'post_class', array( $this, 'unread_row_class' ), 10, 3 );

        add_action( 'admin_menu', array( $this, 'add_unread_badge' ), 999 );
        add_action( 'load-post.php', array( $this, 'mark_read_on_open' ) );
    }

    public function register_post_type(): void {
        register_post_type(
            self::POST_TYPE,
            array(
                'labels'              => array(
                    'name'          => __( 'پیام‌های تماس با ما', 'negarin' ),
                    'singular_name' => __( 'پیام تماس', 'negarin' ),
                    'menu_name'     => __( 'تماس با ما', 'negarin' ),
                    'edit_item'     => __( 'مشاهده پیام', 'negarin' ),
                    'view_item'     => __( 'مشاهده پیام', 'negarin' ),
                    'search_items'  => __( 'جستجوی پیام‌ها', 'negarin' ),
                    'not_found'     => __( 'پیامی یافت نشد.', 'negarin' ),
                ),
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_admin_bar'   => false,
                'show_in_rest'        => false, // Classic editor screen — plain title/content is all a submission needs.
                'menu_icon'           => 'dashicons-email-alt',
                'supports'            => array( 'title', 'editor' ),
                'capability_type'     => 'post',
                'map_meta_cap'        => true,
                'capabilities'        => array(
                    'create_posts' => 'do_not_allow', // Submissions only ever come from the front-end form — hides "Add New".
                ),
                'has_archive'         => false,
                'rewrite'             => false,
                'exclude_from_search' => true,
            )
        );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/contact/submit',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_submit' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function handle_submit( WP_REST_Request $request ) {
        // Honeypot: a hidden field a bot fills in, a human never does.
        // Pretend success so a bot never learns it was caught.
        if ( ! empty( $request->get_param( 'website' ) ) ) {
            return new WP_REST_Response( array( 'success' => true ), 200 );
        }

        $name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
        $phone   = sanitize_text_field( (string) $request->get_param( 'phone' ) );
        $email   = sanitize_email( (string) $request->get_param( 'email' ) );
        $subject = sanitize_text_field( (string) $request->get_param( 'subject' ) );
        $message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

        $errors = array();

        if ( '' === trim( $name ) ) {
            $errors['name'] = __( 'نام را وارد کنید.', 'negarin' );
        }
        if ( '' === trim( $phone ) && '' === trim( $email ) ) {
            $errors['phone'] = __( 'شماره تماس یا ایمیل را وارد کنید.', 'negarin' );
        }
        if ( '' !== (string) $request->get_param( 'email' ) && ! is_email( $email ) ) {
            $errors['email'] = __( 'ایمیل معتبر نیست.', 'negarin' );
        }
        if ( '' === trim( $message ) ) {
            $errors['message'] = __( 'پیام را وارد کنید.', 'negarin' );
        }
        if ( ! Captcha::verify( (string) $request->get_param( 'captcha_token' ), (string) $request->get_param( 'captcha_answer' ) ) ) {
            $errors['captcha'] = __( 'کد امنیتی درست نیست.', 'negarin' );
        }

        if ( $errors ) {
            return new WP_Error(
                'negarin_contact_invalid',
                __( 'لطفاً موارد مشخص‌شده را اصلاح کنید.', 'negarin' ),
                array(
                    'status' => 400,
                    'errors' => $errors,
                )
            );
        }

        $post_id = wp_insert_post(
            array(
                'post_type'    => self::POST_TYPE,
                'post_status'  => 'publish',
                'post_title'   => $name,
                'post_content' => $message,
            ),
            true
        );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error( 'negarin_contact_failed', __( 'ثبت پیام با خطا مواجه شد.', 'negarin' ), array( 'status' => 500 ) );
        }

        update_post_meta( $post_id, '_negarin_contact_phone', $phone );
        update_post_meta( $post_id, '_negarin_contact_email', $email );
        update_post_meta( $post_id, '_negarin_contact_subject', $subject );
        update_post_meta( $post_id, '_negarin_contact_read', '' ); // Unread until an admin opens it.

        wp_mail(
            get_option( 'admin_email' ),
            sprintf(
            /* translators: %s: sender name */
                __( 'پیام جدید فرم تماس با ما از طرف %s', 'negarin' ),
                $name
            ),
            sprintf(
                "%1\$s\n\n%2\$s: %3\$s\n%4\$s: %5\$s\n\n%6\$s",
                $message,
                __( 'تلفن', 'negarin' ),
                $phone ? $phone : '-',
                __( 'ایمیل', 'negarin' ),
                $email ? $email : '-',
                admin_url( 'edit.php?post_type=' . self::POST_TYPE )
            )
        );

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    // --- Admin edit screen ---------------------------------------------

    public function add_meta_box(): void {
        add_meta_box(
            'negarin_contact_details',
            __( 'اطلاعات تماس', 'negarin' ),
            array( $this, 'render_meta_box' ),
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function render_meta_box( WP_Post $post ): void {
        $phone   = get_post_meta( $post->ID, '_negarin_contact_phone', true );
        $email   = get_post_meta( $post->ID, '_negarin_contact_email', true );
        $subject = get_post_meta( $post->ID, '_negarin_contact_subject', true );
        ?>
        <p><strong><?php esc_html_e( 'تلفن:', 'negarin' ); ?></strong> <?php echo esc_html( $phone ? $phone : '—' ); ?></p>
        <p><strong><?php esc_html_e( 'ایمیل:', 'negarin' ); ?></strong>
            <?php if ( $email ) : ?>
                <a href="<?php echo esc_attr( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
            <?php else : ?>
                —
            <?php endif; ?>
        </p>
        <p><strong><?php esc_html_e( 'موضوع:', 'negarin' ); ?></strong> <?php echo esc_html( $subject ? $subject : '—' ); ?></p>
        <?php
    }

    public function mark_read_on_open(): void {
        if ( ! isset( $_GET['post'], $_GET['action'] ) || 'edit' !== $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( self::POST_TYPE !== get_post_type( $post_id ) ) {
            return;
        }

        update_post_meta( $post_id, '_negarin_contact_read', '1' );
    }

    // --- Admin list screen -----------------------------------------------

    public function admin_columns( array $columns ): array {
        $new = array();
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = ( 'title' === $key ) ? __( 'نام', 'negarin' ) : $label;
            if ( 'title' === $key ) {
                $new['negarin_phone']   = __( 'تلفن', 'negarin' );
                $new['negarin_email']   = __( 'ایمیل', 'negarin' );
                $new['negarin_subject'] = __( 'موضوع', 'negarin' );
                $new['negarin_message'] = __( 'پیام', 'negarin' );
            }
        }
        return $new;
    }

    public function render_admin_column( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'negarin_phone':
                echo esc_html( get_post_meta( $post_id, '_negarin_contact_phone', true ) ?: '—' );
                break;
            case 'negarin_email':
                $email = get_post_meta( $post_id, '_negarin_contact_email', true );
                if ( $email ) {
                    echo '<a href="' . esc_attr( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';
                } else {
                    echo '—';
                }
                break;
            case 'negarin_subject':
                echo esc_html( get_post_meta( $post_id, '_negarin_contact_subject', true ) ?: '—' );
                break;
            case 'negarin_message':
                echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 12 ) );
                break;
        }
    }

    /**
     * Adds a marker class the admin list row's unread styling
     * (assets/css/admin.css) hooks off of.
     *
     * @param string[] $classes
     * @param string[] $css_class
     */
    public function unread_row_class( array $classes, $css_class, int $post_id ): array {
        if ( is_admin() && self::POST_TYPE === get_post_type( $post_id ) && '' === get_post_meta( $post_id, '_negarin_contact_read', true ) ) {
            $classes[] = 'negarin-contact-unread';
        }
        return $classes;
    }

    public function add_unread_badge(): void {
        global $menu;

        $count = $this->count_unread();
        if ( ! $count || ! is_array( $menu ) ) {
            return;
        }

        foreach ( $menu as $key => $item ) {
            if ( isset( $item[2] ) && 'edit.php?post_type=' . self::POST_TYPE === $item[2] ) {
                $menu[ $key ][0] .= ' <span class="awaiting-mod count-' . absint( $count ) . '"><span class="pending-count">' . absint( $count ) . '</span></span>';
                break;
            }
        }
    }

    private function count_unread(): int {
        $query = new WP_Query(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
                'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    array(
                        'key'     => '_negarin_contact_read',
                        'value'   => '',
                        'compare' => '=',
                    ),
                ),
            )
        );

        return count( $query->posts );
    }
}