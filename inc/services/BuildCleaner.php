<?php
/**
 * Admin tool that cleans up stale build output: files inside assets/build/
 * that the CURRENT Vite manifest (assets/build/.vite/manifest.json) no
 * longer references.
 *
 * Needed because builds are uploaded via FTP: `npm run build` empties and
 * regenerates the local build folder with new hashed filenames, but FTP only
 * adds/overwrites files on the host — it never removes files that were
 * deleted locally. Old hashed bundles pile up on the host forever unless
 * something explicitly cleans them.
 *
 * Usage: wp-admin → Tools → پاکسازی Build نگارین. Click "بررسی" to see what
 * would be removed, then "حذف" to actually delete it.
 *
 * @package Negarin
 */

namespace Negarin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BuildCleaner {

	private const CAPABILITY = 'manage_options';
	private const REST_BASE  = 'build-cleanup';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'negarin/v1',
			'/' . self::REST_BASE,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_scan' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'handle_clean' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	public function check_permission(): bool {
		return current_user_can( self::CAPABILITY );
	}

	private function build_dir(): string {
		return NEGARIN_DIR . '/assets/build';
	}

	private function manifest_path(): string {
		return $this->build_dir() . '/.vite/manifest.json';
	}

	/**
	 * Every file physically present inside assets/build/, as paths relative
	 * to that folder (using '/' regardless of OS).
	 */
	private function scan_all_files(): array {
		$dir = $this->build_dir();

		if ( ! is_dir( $dir ) ) {
			return array();
		}

		$files    = array();
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}
			$rel     = ltrim( str_replace( wp_normalize_path( $dir ), '', wp_normalize_path( $file->getPathname() ) ), '/' );
			$files[] = $rel;
		}

		return $files;
	}

	/**
	 * Every output file the current manifest actually references (entry
	 * files, css, copied static assets), following imports/dynamicImports
	 * recursively so nothing genuinely in-use gets flagged as stale.
	 *
	 * Returns null when there's no manifest yet (nothing has been built and
	 * uploaded, so there's nothing safe to compare against).
	 */
	private function referenced_files(): ?array {
		$path = $this->manifest_path();

		if ( ! file_exists( $path ) ) {
			return null;
		}

		$manifest = json_decode( (string) file_get_contents( $path ), true );

		if ( ! is_array( $manifest ) ) {
			return null;
		}

		// همیشه خود پوشه .vite (شامل manifest.json) رو نگه می‌داریم.
		$referenced = array( '.vite/manifest.json' => true );
		$visited    = array();

		$collect = function ( string $key ) use ( &$collect, &$referenced, &$visited, $manifest ) {
			if ( isset( $visited[ $key ] ) || ! isset( $manifest[ $key ] ) ) {
				return;
			}
			$visited[ $key ] = true;
			$entry           = $manifest[ $key ];

			if ( ! empty( $entry['file'] ) ) {
				$referenced[ $entry['file'] ] = true;
			}

			foreach ( array( 'css', 'assets' ) as $group ) {
				if ( empty( $entry[ $group ] ) || ! is_array( $entry[ $group ] ) ) {
					continue;
				}
				foreach ( $entry[ $group ] as $asset ) {
					$referenced[ $asset ] = true;
				}
			}

			foreach ( array( 'imports', 'dynamicImports' ) as $group ) {
				if ( empty( $entry[ $group ] ) || ! is_array( $entry[ $group ] ) ) {
					continue;
				}
				foreach ( $entry[ $group ] as $imported_key ) {
					$collect( $imported_key );
				}
			}
		};

		foreach ( array_keys( $manifest ) as $key ) {
			$collect( $key );
		}

		return array_keys( $referenced );
	}

	/**
	 * Files that physically exist in assets/build/ but the manifest doesn't
	 * reference — leftovers from a previous build.
	 *
	 * @return array|WP_Error
	 */
	private function stale_files() {
		$referenced = $this->referenced_files();

		if ( null === $referenced ) {
			return new WP_Error(
				'negarin_no_manifest',
				__( 'فایل manifest.json پیدا نشد. مطمئن شو حداقل یه build کامل آپلود شده.', 'negarin' )
			);
		}

		return array_values( array_diff( $this->scan_all_files(), $referenced ) );
	}

	public function handle_scan( WP_REST_Request $request ): WP_REST_Response {
		$stale = $this->stale_files();

		if ( is_wp_error( $stale ) ) {
			return new WP_REST_Response( array( 'message' => $stale->get_error_message() ), 400 );
		}

		return new WP_REST_Response( array( 'files' => $stale ), 200 );
	}

	public function handle_clean( WP_REST_Request $request ): WP_REST_Response {
		$stale = $this->stale_files();

		if ( is_wp_error( $stale ) ) {
			return new WP_REST_Response( array( 'message' => $stale->get_error_message() ), 400 );
		}

		$build_dir_real = realpath( $this->build_dir() );
		$build_dir_real = $build_dir_real ? wp_normalize_path( $build_dir_real ) : '';

		$deleted = array();
		$failed  = array();

		foreach ( $stale as $rel ) {
			$full = $this->build_dir() . '/' . $rel;
			$real = realpath( $full );

			// لایه محافظتی: هیچ‌وقت چیزی بیرون از پوشه build حذف نشه.
			if ( ! $build_dir_real || ! $real || 0 !== strpos( wp_normalize_path( $real ), $build_dir_real ) ) {
				$failed[] = $rel;
				continue;
			}

			wp_delete_file( $full );

			if ( file_exists( $full ) ) {
				$failed[] = $rel;
			} else {
				$deleted[] = $rel;
			}
		}

		$this->remove_empty_dirs( $this->build_dir() );

		return new WP_REST_Response(
			array(
				'deleted' => $deleted,
				'failed'  => $failed,
			),
			200
		);
	}

	/**
	 * Recursively removes now-empty directories left behind after deleting
	 * stale files (e.g. an old chunk's dedicated folder).
	 */
	private function remove_empty_dirs( string $dir ): void {
		$subdirs = glob( $dir . '/*', GLOB_ONLYDIR );

		if ( ! $subdirs ) {
			return;
		}

		foreach ( $subdirs as $sub ) {
			// هیچ‌وقت پوشه .vite حذف نشه، حتی اگه خالی به نظر برسه.
			if ( '.vite' === basename( $sub ) ) {
				continue;
			}

			$this->remove_empty_dirs( $sub );

			$contents = array_diff( scandir( $sub ) ?: array(), array( '.', '..' ) );

			if ( empty( $contents ) ) {
				@rmdir( $sub ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
	}

	public function register_admin_page(): void {
		add_management_page(
			__( 'پاکسازی Build نگارین', 'negarin' ),
			__( 'پاکسازی Build نگارین', 'negarin' ),
			self::CAPABILITY,
			'negarin-build-cleanup',
			array( $this, 'render_admin_page' )
		);
	}

	public function render_admin_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$rest_url = esc_url_raw( rest_url( 'negarin/v1/' . self::REST_BASE ) );
		$nonce    = wp_create_nonce( 'wp_rest' );
		?>
		<div class="wrap" dir="rtl" style="max-width:720px;">
			<h1><?php esc_html_e( 'پاکسازی فایل‌های قدیمی Build', 'negarin' ); ?></h1>
			<p>
				<?php esc_html_e( 'بعد از هر بار آپلود build جدید با FTP، این صفحه رو باز کن. اول «بررسی» رو بزن تا فایل‌هایی که دیگه در manifest.json فعلی نیستن (مال build قبلی‌ان) لیست بشن، بعد «حذف» رو بزن.', 'negarin' ); ?>
			</p>
			<p>
				<button type="button" class="button button-secondary" id="negarin-bc-scan"><?php esc_html_e( 'بررسی فایل‌های اضافی', 'negarin' ); ?></button>
				<button type="button" class="button button-primary" id="negarin-bc-clean" disabled><?php esc_html_e( 'حذف فایل‌های پیدا شده', 'negarin' ); ?></button>
			</p>
			<div id="negarin-bc-result"></div>
		</div>
		<script>
		( function () {
			var restUrl   = <?php echo wp_json_encode( $rest_url ); ?>;
			var nonce     = <?php echo wp_json_encode( $nonce ); ?>;
			var scanBtn   = document.getElementById( 'negarin-bc-scan' );
			var cleanBtn  = document.getElementById( 'negarin-bc-clean' );
			var result    = document.getElementById( 'negarin-bc-result' );
			var lastFiles = [];

			function esc( str ) {
				var div = document.createElement( 'div' );
				div.textContent = str;
				return div.innerHTML;
			}

			scanBtn.addEventListener( 'click', function () {
				result.textContent = '…';
				cleanBtn.disabled = true;
				fetch( restUrl, { headers: { 'X-WP-Nonce': nonce } } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( data ) {
						if ( data.message ) {
							result.innerHTML = '<p>خطا: ' + esc( data.message ) + '</p>';
							return;
						}
						lastFiles = data.files || [];
						if ( lastFiles.length === 0 ) {
							result.innerHTML = '<p>✅ چیزی برای حذف نیست، هاست با build فعلی سینکه.</p>';
							return;
						}
						result.innerHTML = '<p>' + lastFiles.length + ' فایل قدیمی پیدا شد:</p><ul>' +
							lastFiles.map( function ( f ) { return '<li><code>' + esc( f ) + '</code></li>'; } ).join( '' ) +
							'</ul>';
						cleanBtn.disabled = false;
					} )
					.catch( function () {
						result.textContent = 'خطا در ارتباط با سرور.';
					} );
			} );

			cleanBtn.addEventListener( 'click', function () {
				if ( ! window.confirm( 'مطمئنی می‌خوای این ' + lastFiles.length + ' فایل از روی هاست حذف بشه؟' ) ) {
					return;
				}
				cleanBtn.disabled = true;
				result.textContent = 'در حال حذف...';
				fetch( restUrl, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( data ) {
						var msg = '✅ ' + ( data.deleted ? data.deleted.length : 0 ) + ' فایل حذف شد.';
						if ( data.failed && data.failed.length ) {
							msg += ' (' + data.failed.length + ' فایل حذف نشد: ' + data.failed.map( esc ).join( '، ' ) + ')';
						}
						result.innerHTML = '<p>' + msg + '</p>';
						lastFiles = [];
					} )
					.catch( function () {
						result.textContent = 'خطا در ارتباط با سرور.';
					} );
			} );
		} )();
		</script>
		<?php
	}
}
