<?php
/**
 * SMS "notify me about new products" subscription. This is NOT tied to
 * WooCommerce's product-publish hook — a customer leaves their mobile
 * number here, and an admin decides when to actually send an SMS blast
 * (manually, from whichever SMS panel is set up). Nothing here fires an
 * SMS automatically when a product is published.
 *
 * Storage: a dedicated `{$wpdb->prefix}negarin_sms_subscribers` table
 * (not a custom post type) — a phone number plus a status is all there
 * is per subscriber. `status` is soft-delete only ('active' / 'removed')
 * — the admin "حذف" action never runs a real DELETE, matching how the
 * rest of the project handles removal. That also matters for the UNIQUE
 * key on `phone`: resubscribing after a soft-delete reactivates the same
 * row instead of colliding with it (see handle_subscribe()).
 *
 * The theme is already active in production, so table creation can't
 * rely on `after_switch_theme` alone (it won't fire again until the
 * theme is deactivated/reactivated) — maybe_upgrade_db() runs a cheap
 * version check on every load instead and creates/upgrades the table
 * if needed.
 *
 * Frontend: template-parts/components/sms-newsletter-form.php is the
 * one form markup, rendered both by the [negarin_sms_newsletter]
 * shortcode (registered here) and by the negarin/sms-newsletter block
 * (inc/services/SmsNewsletterBlock.php) — same as icon-button, so the
 * two can never drift apart.
 *
 * POST /wp-json/negarin/v1/sms-newsletter/subscribe  body: { phone, website }
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SmsNewsletter {

    const DB_VERSION        = '1.0';
    const DB_VERSION_OPTION = 'negarin_sms_subscribers_db_version';
    const ADMIN_PAGE_SLUG   = 'negarin-sms-newsletter';
    const ADMIN_NONCE       = 'negarin_sms_newsletter_admin';

    public function __construct() {
        add_action( 'init', array( $this, 'maybe_upgrade_db' ) );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        add_action( 'init', array( $this, 'register_shortcode' ) );
        add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
        add_action( 'admin_init', array( $this, 'maybe_handle_admin_actions' ) );
    }

    public static function table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'negarin_sms_subscribers';
    }

    /**
     * Creates/upgrades the subscribers table. Cheap after the first run
     * on a given site — get_option() is a single cached lookup, so this
     * is effectively one string comparison per request.
     */
    public function maybe_upgrade_db(): void {
        if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
            return;
        }

        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name      = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            phone VARCHAR(20) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY phone (phone)
        ) {$charset_collate};";

        dbDelta( $sql );

        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
    }

    public function register_routes(): void {
        register_rest_route(
            'negarin/v1',
            '/sms-newsletter/subscribe',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_subscribe' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function handle_subscribe( WP_REST_Request $request ) {
        // Honeypot: a hidden field a bot fills in, a human never does.
        // Pretend success so a bot never learns it was caught.
        if ( ! empty( $request->get_param( 'website' ) ) ) {
            return new WP_REST_Response( array( 'success' => true ), 200 );
        }

        $phone = sanitize_text_field( (string) $request->get_param( 'phone' ) );

        if ( '' === trim( $phone ) || ! validate_phone( $phone ) ) {
            return new WP_Error(
                'negarin_sms_newsletter_invalid',
                __( 'لطفاً موارد مشخص‌شده را اصلاح کنید.', 'negarin' ),
                array(
                    'status' => 400,
                    'errors' => array( 'phone' => __( 'شماره موبایل را به صورت صحیح وارد کنید.', 'negarin' ) ),
                )
            );
        }

        $normalized = normalize_phone( $phone );

        global $wpdb;
        $table_name = self::table_name();

        $existing = $wpdb->get_row(
            $wpdb->prepare( "SELECT id, status FROM {$table_name} WHERE phone = %s", $normalized ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );

        if ( $existing && 'active' === $existing->status ) {
            return new WP_REST_Response( array( 'success' => true, 'already' => true ), 200 );
        }

        if ( $existing ) {
            // Previously removed (soft-delete) — reactivate the same row
            // instead of inserting, which would collide with UNIQUE(phone).
            $updated = $wpdb->update(
                $table_name,
                array(
                    'status'     => 'active',
                    'created_at' => current_time( 'mysql' ),
                ),
                array( 'id' => $existing->id ),
                array( '%s', '%s' ),
                array( '%d' )
            );

            if ( false === $updated ) {
                return new WP_Error( 'negarin_sms_newsletter_failed', __( 'ثبت شماره با خطا مواجه شد.', 'negarin' ), array( 'status' => 500 ) );
            }

            return new WP_REST_Response( array( 'success' => true, 'already' => false ), 200 );
        }

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'phone'      => $normalized,
                'status'     => 'active',
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s' )
        );

        if ( false === $inserted ) {
            return new WP_Error( 'negarin_sms_newsletter_failed', __( 'ثبت شماره با خطا مواجه شد.', 'negarin' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'success' => true, 'already' => false ), 200 );
    }

    /**
     * [negarin_sms_newsletter title="..." description="..." button="..."]
     */
    public function register_shortcode(): void {
        add_shortcode( 'negarin_sms_newsletter', array( $this, 'render_shortcode' ) );
    }

    public function render_shortcode( $atts ): string {
        $atts = shortcode_atts(
            array(
                'title'       => __( 'عضویت در خبرنامه پیامکی', 'negarin' ),
                'description' => __( 'شماره موبایلت رو بزن تا به محض انتشار محصولات جدید بهت پیامک بدیم.', 'negarin' ),
                'button'      => __( 'عضویت', 'negarin' ),
            ),
            $atts,
            'negarin_sms_newsletter'
        );

        $title       = $atts['title'];
        $description = $atts['description'];
        $button_text = $atts['button'];

        ob_start();
        include NEGARIN_DIR . '/template-parts/components/sms-newsletter-form.php';
        return (string) ob_get_clean();
    }

    /**
     * A plain list of numbers is otherwise invisible in wp-admin — this
     * page is read + soft-delete + CSV export only. Actually sending the
     * SMS blast stays a manual step done elsewhere, outside this page.
     */
    public function register_admin_page(): void {
        add_menu_page(
            __( 'خبرنامه پیامکی', 'negarin' ),
            __( 'خبرنامه پیامکی', 'negarin' ),
            'manage_options',
            self::ADMIN_PAGE_SLUG,
            array( $this, 'render_admin_page' ),
            'dashicons-email-alt',
            58
        );
    }

    /**
     * Runs on admin_init — before admin-header.php sends any HTML — so
     * both the CSV download (needs to set headers) and the post-action
     * redirect (needs no output sent yet) are possible. render_admin_page()
     * itself only ever displays.
     */
    public function maybe_handle_admin_actions(): void {
        if ( ! isset( $_GET['page'] ) || self::ADMIN_PAGE_SLUG !== $_GET['page'] ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['export'] ) && 'csv' === $_GET['export'] ) {
            check_admin_referer( self::ADMIN_NONCE );
            $this->export_csv(); // Exits.
        }

        if ( isset( $_GET['remove'] ) ) {
            check_admin_referer( self::ADMIN_NONCE );
            $this->soft_delete( (int) $_GET['remove'] );
            wp_safe_redirect( remove_query_arg( array( 'remove', '_wpnonce' ) ) );
            exit;
        }
    }

    private function soft_delete( int $id ): void {
        global $wpdb;
        $wpdb->update(
            self::table_name(),
            array( 'status' => 'removed' ),
            array( 'id' => $id ),
            array( '%s' ),
            array( '%d' )
        );
    }

    private function export_csv(): void {
        global $wpdb;
        $table_name = self::table_name();

        $rows = $wpdb->get_results(
            $wpdb->prepare( "SELECT phone, created_at FROM {$table_name} WHERE status = %s ORDER BY created_at DESC", 'active' ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=negarin-sms-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );

        $out = fopen( 'php://output', 'w' );
        fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM so Excel opens it without mangling Persian dates/labels.
        fputcsv( $out, array( 'phone', 'subscribed_at' ) );
        foreach ( $rows as $row ) {
            fputcsv( $out, array( $row->phone, $row->created_at ) );
        }
        fclose( $out );
        exit;
    }

    public function render_admin_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;
        $table_name = self::table_name();

        $subscribers = $wpdb->get_results(
            $wpdb->prepare( "SELECT id, phone, created_at FROM {$table_name} WHERE status = %s ORDER BY created_at DESC", 'active' ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );
        $count = count( $subscribers );

        $export_url = wp_nonce_url( admin_url( 'admin.php?page=' . self::ADMIN_PAGE_SLUG . '&export=csv' ), self::ADMIN_NONCE );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'خبرنامه پیامکی', 'negarin' ); ?></h1>
            <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'خروجی CSV', 'negarin' ); ?></a>
            <hr class="wp-header-end">
            <p>
                <?php
                printf(
                    /* translators: %d: active subscriber count */
                    esc_html( _n( '%d شماره فعال.', '%d شماره فعال.', $count, 'negarin' ) ),
                    (int) $count
                );
                ?>
            </p>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'شماره موبایل', 'negarin' ); ?></th>
                        <th><?php esc_html_e( 'تاریخ ثبت‌نام', 'negarin' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! $subscribers ) : ?>
                        <tr><td colspan="3"><?php esc_html_e( 'هنوز کسی عضو نشده است.', 'negarin' ); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ( $subscribers as $row ) : ?>
                        <?php $remove_url = wp_nonce_url( admin_url( 'admin.php?page=' . self::ADMIN_PAGE_SLUG . '&remove=' . (int) $row->id ), self::ADMIN_NONCE ); ?>
                        <tr>
                            <td dir="ltr" style="text-align:right"><?php echo esc_html( $row->phone ); ?></td>
                            <td><?php echo esc_html( mysql2date( 'Y/m/d H:i', $row->created_at ) ); ?></td>
                            <td>
                                <a
                                    href="<?php echo esc_url( $remove_url ); ?>"
                                    onclick="return confirm('<?php echo esc_js( __( 'این شماره از لیست حذف شود؟', 'negarin' ) ); ?>');"
                                    class="submitdelete"
                                ><?php esc_html_e( 'حذف', 'negarin' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
