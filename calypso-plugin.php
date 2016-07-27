<?php
/**
 * Plugin Name: Calypso Plugin
 * Plugin URI: https://github.com/Automattic/calypso-plugin
 * Description: Template/Boilerplate for a WordPress plugin that uses Calypso components
 * Version: 1.0.0-dev
 * Author: Automattic
 * Author URI: http://automattic.com
 * Requires at least: 4.6
 * Tested up to: 4.6
 *
 * Text Domain: calypso-plugin
 * Domain Path: /i18n/languages/
 *
 * @package Calypso_Plugin
 * @category Test
 * @author Automattic
 */
defined( 'ABSPATH' ) or die( 'No direct access.' );

// If the i18n directory and url aren't defined by config, use the default.
if ( ! defined( 'CALYPSO_PLUGIN_I18N_DIR' ) ) {
	define( 'CALYPSO_PLUGIN_I18N_DIR', WP_CONTENT_DIR . '/calypso-plugin-i18n' );
}
if ( ! defined( 'CALYPSO_PLUGIN_I18N_URL' ) ) {
	define( 'CALYPSO_PLUGIN_I18N_URL', WP_CONTENT_URL . '/calypso-plugin-i18n' );
}

/**
 * Create the i18n js dir on activation.
 */
function calypso_plugin_create_i18n_dir() {
	if ( ! file_exists( CALYPSO_PLUGIN_I18N_DIR ) ) {
		wp_mkdir_p( CALYPSO_PLUGIN_I18N_DIR, 0660 );
	} else if ( ! is_dir( CALYPSO_PLUGIN_I18N_DIR ) ) {
		error_log( 'Expected ' . CALYPSO_PLUGIN_I18N_DIR . ' to be a directory.' );
	}
}

/**
 * Clean out and remove the i18n js dir on deactivation.
 *
 * Note: If files other than i18n js files have been placed
 * in this directory, the removal of the directory will
 * fail silently and will have to be removed manually, if so desired.
 */
function calypso_plugin_remove_i18n_dir() {
	if ( is_dir( CALYPSO_PLUGIN_I18N_DIR ) ) {
		// Delete the i18n files.
		$file_mask = CalypsoPlugin::TEXTDOMAIN . '-*.js';
		$files = glob( trailingslashit( CALYPSO_PLUGIN_I18N_DIR ) . $file_mask );
		foreach ( $files as $file ) {
			unlink( $file );
		}

		// Remove the directory.
		rmdir( CALYPSO_PLUGIN_I18N_DIR );
	}
}

register_activation_hook( __FILE__, 'calypso_plugin_create_i18n_dir' );
register_deactivation_hook( __FILE__, 'calypso_plugin_remove_i18n_dir' );

/**
 * Main class.
 *
 * @since 1.0
 */
class CalypsoPlugin {

	const VERSION        = '1.0.0';
	const WC_MIN_VERSION = '2.5';
	const TEXTDOMAIN     = 'calypso-plugin';

	/**
	 * Hook into plugins_loaded, which is when all plugins will be available.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		include_once( 'dist/config.php' );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'calypso_plugin_generate_translation_files', array( $this, 'generate_translation_files' ) );
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'calypso-plugin', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'languages/' );
	}

	/**
	 * Hooks in the plugin if supported, otherwise hooks in admin notices only.
	 *
	 * @since 1.0
	 */
	public function init() {
		if ( $this->check_dependencies() ) {
			// Hooks and filters for the plugin should be added here.
			add_action( 'admin_menu', array( $this, 'attach_menus' ) );
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'admin_init', array( $this, 'maybe_generate_translation_files' ) );
			add_action( 'admin_notices', array( $this, 'before_notices' ), 0 );
			add_action( 'admin_notices', array( $this, 'after_notices' ), PHP_INT_MAX );
		}
	}

	/**
	 * Checks if the current page being viewed is our admin page.
	 */
	public function is_admin_page() {
		$current_screen = get_current_screen();

		return 'toplevel_page_calypso-plugin' === $current_screen->id;
	}

	/**
	 * Adds the open tag for a notices container div.
	 */
	public function before_notices() {
		if ( $this->is_admin_page() ) {
			echo '<div id="admin-notice-list" class="admin-notice-list-hide">';
		}
	}

	/**
	 * Adds the close tag for a notices container div.
	 */
	public function after_notices() {
		if ( $this->is_admin_page() ) {
			echo '</div>';
			echo '<div id="calypso-plugin-notices" class="uses-calypso-plugin-styles">';
			echo '</div>';
		}
	}

	/**
	 * Queues PO to JSON conversion when needed.
	 */
	public function maybe_generate_translation_files() {
		$next_event = wp_next_scheduled( 'calypso_plugin_generate_translation_files', array( get_locale() ) );
		$po_file    = $this->get_po_file_path( get_locale() );

		// We can only do conversion if the PO file exists and we don't want to queue this up twice.
		if ( file_exists( $po_file ) && ( ! $next_event || $next_event < time() ) ) {
			$translation_info = wp_get_pomo_file_data( $po_file );
			$revision         = strtotime( $translation_info['PO-Revision-Date'] );
			$js_file        = $this->get_i18n_js_file_path( get_locale() );

			/**
			 * There are 2 case where we'd want to do a conversion;
			 *  - if the JSON file does not exist
			 *  - if the JSON file is out of date
			 */
			if ( ! file_exists( $js_file ) || $revision > get_option( 'calypso-plugin_revision_' . get_locale(), 0 ) ) {
				wp_schedule_single_event( time() + 10, 'calypso_plugin_generate_translation_files', array( get_locale() ) );
			}
		}
	}

	/**
	 * Generates the translation files for a locale.
	 * @param  string $locale
	 */
	public function generate_translation_files( $locale = '' ) {
		$locale           = $locale ? $locale : get_locale();
		$po_file          = $this->get_po_file_path( $locale );
		$js_file          = $this->get_i18n_js_file_path( $locale );
		$translation_info = wp_get_pomo_file_data( $po_file );
		$revision         = strtotime( $translation_info['PO-Revision-Date'] );

		// Parse PO file
		$po_data   = $this->parse_po_file( $po_file );

		// Convert entries to JSON
		$json      = $this->po2json( $po_data['headers'], $po_data['entries'], CalypsoPlugin::TEXTDOMAIN );

		// Write to file
		$this->create_js_language_file( $json, $js_file );

		// Record the revision and locale
		update_option( 'calypso-plugin_revision_' . $locale, $revision );
		wp_clear_scheduled_hook( 'calypso_plugin_generate_translation_files', array( $locale ) );
	}

	/**
	 * Gets a .po file path.
	 * @param string $locale
	 * @return string
	 */
	protected function get_po_file_path( $locale ) {
		$base_dir = trailingslashit( WP_LANG_DIR ) . 'plugins';
		$file = CalypsoPlugin::TEXTDOMAIN . '-' . $locale . '.po';
		return trailingslashit( $base_dir ) . $file;
	}

	/**
	 * Gets an i18n js file path.
	 * @param  string $locale
	 * @return string
	 */
	protected function get_i18n_js_file_path( $locale ) {
		$base_dir = CALYPSO_PLUGIN_I18N_DIR;
		$file = CalypsoPlugin::TEXTDOMAIN . '-' . $locale . '.js';
		return trailingslashit( $base_dir ) . $file;
	}

	/**
	 * Gets an i18n js url.
	 *
	 * @param string $locale The locale of the i18n js file desired.
	 * @return string
	 */
	public function get_i18n_js_url( $locale ) {
		$base_url = CALYPSO_PLUGIN_I18N_URL;
		$file = CalypsoPlugin::TEXTDOMAIN . '-' . $locale . '.js';
		return trailingslashit( $base_url ) . $file;
	}

	/**
	 * Parse a po file and get headers and entries.
	 * @param  string $po_file Path to po file
	 * @return array
	 */
	protected function parse_po_file( $po_file ) {
		include_once ABSPATH . WPINC . '/pomo/po.php';
		$po = new PO();
		$po->import_from_file( $po_file );
		return array(
			'headers' => $po->headers,
			'entries' => $po->entries,
		);
	}

	/**
	 * Converts PO file entries to Jed compatible JSON.
	 *
	 * Based on https://github.com/neam/php-po2json/blob/develop/Po2Json.php but
	 * adapted to work with entries from WP POMO class.
	 *
	 * @param array  $headers Array of headers from a PO file
	 * @param array  $translations Array of strings from a PO file
	 * @param string $textdomain Textdomain. Default ''
	 * @return string JSON
	 */
	protected function po2json( $headers, $translations, $textdomain = '' ) {
		// Copy the headers into the '' element, and add the localeSlug.
		$data = array(
			'' => $headers
		);

		$language = $data['']['Language'];
		$localeSlug = substr( $language, 0, strpos( $language, '_' ) );
		$data['']['localeSlug'] = $localeSlug;

		// Loop over parsed translations. Each translation will be of type
		// Translation_Entry. $translation_key contains a key, with context.
		foreach ( $translations as $translation_key => $translation ) {
			$entry = array();

			if ( $translation->is_plural ) {
				if ( 2 === sizeof( $translation->translations ) ) {
					$entry[0] = $translation->translations[1];
					$entry[1] = $translation->translations[0];
					$entry[2] = $translation->translations[1];
				} else {
					$entry    = $translation->translations;
				}
			} elseif ( $translation->translations ) {
				$entry[0] = null;
				$entry[1] = $translation->translations[0];
			} else {
				$entry = null;
			}

			$data[ $translation_key ] = $entry;
		}

		return json_encode( $data );
	}

	/**
	 * Create JSON JavaScript Language file.
	 * @param  string $json JSON data to write
	 * @param  string $file File path to write to.
	 */
	protected function create_js_language_file( $json, $file ) {
		if ( $file_handle = @fopen( $file, 'w' ) ) {
			fwrite( $file_handle, 'var i18nLocaleStrings = ' );
			fwrite( $file_handle, $json );
			fwrite( $file_handle, ';' );
			fclose( $file_handle );
		}
	}

	/**
	 * Reads a JS language file.
	 * @param  string $file File path to read.
	 * @return JSON data.
	 */
	protected function read_js_language_file( $file ) {
		if ( file_exists( $file ) && $file_handle = fopen( $file, 'r' ) ) {
			$json = fread( $file_handle, filesize( $file ) );
			return $json;
		}
	}


	/**
	 * Adds Jed-compatible JSON translations to the given script.
	 * @param  string $script_handle The handle of the script.
	 * @param  string $name The name of the JavaScript variable for the translations.
	 * @param  string $local The locale of the translations to add.
	 */
	protected function add_translations( $script_handle, $locale ) {
		$version = get_option( 'calypso-plugin_revision_' . $locale );
		$js_file = $this->get_i18n_js_file_path( $locale );
		if ( $version && file_exists( $js_file ) ) {

			wp_enqueue_script(
				$script_handle,
				$this->get_i18n_js_url( $locale ),
				array(),
				$version,
				true
			);
		}
	}

	/**
	 * Checks if the WP-API plugin is active.
	 * Note: Must be run after the "plugins_loaded" action fires.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_wpapi_active() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'WP-API/plugin.php' );
	}

	/**
	 * Attaches a top-level admin menu.
	 *
	 * @since 1.0
	 */
	public function attach_menus() {
		add_menu_page(
			__( 'Calypso Plugin', 'calypso-plugin' ),
			__( 'Calypso Plugin', 'calypso-plugin' ),
			'activate_plugins',
			'calypso-plugin',
			array( $this, 'output' ),
			null,
			56
		);
	}

	/**
	 * Outputs the main calypso-plugin screen and enqueues scripts.
	 * @since 1.0
	 */
	public function output() {
		wp_enqueue_script(
			'calypso-plugin-js',
			$this->get_assets_url() . 'calypso-plugin_bundle.js',
			array(),
			$this->get_asset_version(),
			true
		);
		wp_enqueue_style(
			'calypso-plugin-css',
			$this->get_assets_url() . 'calypso-plugin.css',
			array(),
			$this->get_asset_version()
		);
		echo '<div id="calypso-plugin" class="uses-calypso-plugin-styles"></div>';
	}

	/**
	 * Gets the base url for assets like .js and .css bundles
	 *
	 * @since 1.0
	 */
	public function get_assets_url() {
		if ( null !== CALYPSO_PLUGIN_ASSETS_URL ) {
			if ( strstr( CALYPSO_PLUGIN_ASSETS_URL, '//' ) ) {
				// It's a full URL, so just return it.
				return CALYPSO_PLUGIN_ASSETS_URL;
			} else {
				// It's a relative url, so use plugin url as a base.
				return plugins_url( CALYPSO_PLUGIN_ASSETS_URL, __FILE__ );
			}
		} else {
			// No assets url provided, so return base plugins url.
			return plugins_url( '/', __FILE__ );
		}
	}

	/**
	 * Gets the asset version for enqueuing purposes.
	 * If the config is set to bust caches, this returns a random hex string.
	 * If not, it returns the version of the plugin.
	 * @since 1.0
	 */
	public function get_asset_version() {
		if ( CALYPSO_PLUGIN_BUST_ASSET_CACHE ) {
			require_once( ABSPATH . 'wp-includes/class-phpass.php' );
			$hasher = new PasswordHash( 8, false );
			return md5( $hasher->get_random_bytes( 16 ) );
		} else {
			return CalypsoPlugin::VERSION;
		}
	}

	/**
	 * Checks that dependencies are loaded before doing anything else.
	 *
	 * @since 1.0
	 * @return bool True if supported
	 */
	private function check_dependencies() {
		$dependencies = array(
			'wc_installed' => array(
				'callback'        => array( $this, 'is_wpapi_active' ),
				'notice_callback' => array( $this, 'wpapi_inactive_notice' ),
			)
		);

		foreach ( $dependencies as $check ) {
			if ( ! call_user_func( $check['callback'] ) ) {
				add_action( 'admin_notices', $check['notice_callback'] );
				return false;
			}
		}

		return true;
	}

	/**
	 * WC inactive notice.
	 * @since  1.0.0
	 * @return string
	 */
	public function wpapi_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="error"><p><strong>' . __( 'Calypso Plugin is inactive.', 'calypso-plugin' ) . '</strong> ' . sprintf( __( 'The WP-API plugin must be active for Calypso Plugin to work. %sPlease install and activate WP-API%s.', 'calypso-plugin' ), '<a href="' .esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' ) . '</p></div>';
		}
	}
}

return new CalypsoPlugin();
