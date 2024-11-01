<?php
/**
 * Plugin Name: WP Voice
 * Plugin URI: https://wordpress.org/plugins/wp-voice/
 * Description: Voice Message for WordPress Contact Form 7
 * Author: dna88
 * Version: 1.0.6
 * Author URI: http://dna88.com/
 * Requires PHP: 5.7
 * Requires at least: 4.6
 * Tested up to: 5.9
 **/

namespace QuantumCloud;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/** Include plugin autoloader for additional classes. */
require __DIR__ . '/includes/autoload.php';

use QuantumCloud\QCvoicemssg\VoiceMessageForm;

/**
 * SINGLETON: Core class used to implement a QCvoicemssg plugin.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since 1.0.0
 */
final class wpvoicemessage {

	/**
	 * Plugin version.
	 *
	 * @string version
	 * @since 1.0.0
	 **/
	public static $version;

	/**
	 * Use minified libraries if SCRIPT_DEBUG is turned off.
	 *
	 * @since 1.0.0
	 **/
	public static $suffix ;

	/**
	 * URL (with trailing slash) to plugin folder.
	 *
	 * @var string
	 * @since 1.0.0
	 **/
	public static $url;

	/**
	 * PATH to plugin folder.
	 *
	 * @var string
	 * @since 1.0.0
	 **/
	public static $path;

	/**
	 * Plugin base name.
	 *
	 * @var string
	 * @since 1.0.0
	 **/
	public static $basename;

	/**
	 * Plugin admin menu base.
	 *
	 * @var string
	 * @since 1.0.5
	 **/
	public static $menu_base;

	/**
	 * The one true QCvoicemssg.
	 *
	 * @since 1.0.0
	 **@var wpvoicemessage
	 */
	private static $instance;
	public $upgrade_link = "https://www.dna88.com/product/wpvoice/";
	public $link_color = "#FCB214";
	public $link_text = "Upgrade to Pro";
	public $link_class = "";
    public $link_target = "_blank";

	public $show_with_action_links = true;
	/**
	 * Sets up a new plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Initialize main variables. */
		$this->initialization();

	}

	/**
	 * Setup the plugin.
	 *
	 * @since 1.0.5
	 * @access public
	 *
	 * @return void
	 **/
	public function setup() {

		
		/** Send install Action to our host. */
		self::send_install_action();

		/** Define hooks that runs on both the front-end as well as the dashboard. */
		$this->both_hooks();

		/** Define public hooks. */
		$this->public_hooks();

		/** Define admin hooks. */
		$this->admin_hooks();

	}

	/**
	 * Return plugin version.
	 *
	 * @return string
	 * @since 1.0.0
	 * @access public
	 **/
	public function get_version() {

		return self::$version;

	}

	/**
	 * Define hooks that runs on both the front-end as well as the dashboard.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 **/
	private function both_hooks() {

		/** Initialize plugin settings. */
		\QuantumCloud\QCvoicemssg\Settings::get_instance();

		/** Load translation. */
		add_action( 'plugins_loaded', [$this, 'load_textdomain'] );

		/** Register voicemssg_form_qcwp and qcvoicemsg_record post types. */
		\QuantumCloud\QCvoicemssg\CustomPost::get_instance();

		/** Allow svg uploading. */
		\QuantumCloud\QCvoicemssg\SvgHelper::get_instance();

		/** Add AJAX callback. */
		add_action( 'wp_ajax_wpvoicemessage_send', [$this, 'wpvoicemessage_send'] );
		add_action( 'wp_ajax_nopriv_wpvoicemessage_send', [$this, 'wpvoicemessage_send'] );
		$dir = plugin_basename(__FILE__);
		add_filter( 'plugin_action_links_' . $dir, [$this, 'func_show_upgrade_link_with_action_links'], 95 );
	

	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 **/
	private function admin_hooks() {

		/** Work only in admin area. */
		if ( ! is_admin() ) { return; }

		/** Add plugin settings page. */
		\QuantumCloud\QCvoicemssg\Settings::get_instance()->add_settings_page();

		/** Load JS and CSS for Backend Area. */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ], 100 ); // CSS.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 100 ); // JS.

		/** Remove "Thank you for creating with WordPress" and WP version only from plugin settings page. */
		add_action( 'admin_enqueue_scripts', [$this, 'remove_wp_copyrights'] );

		/** Remove all "third-party" notices from plugin settings page. */
		add_action( 'in_admin_header', [$this, 'remove_all_notices'], 1000 );

		add_filter( 'custom_menu_order', [$this, 'wpvoicemessage_order_index_catalog_menu_page'] );

	}


	/**
	 * Sets the Notification plugin in white label mode.
	 *
	 * Args you can use:
	 * - 'page_hook' => 'edit.php?post_type=page' // to move the Notifications under specific admin page
	 *
	 * @since 1.0.5
	 * @param array $args white label args.
	 * @return void
	 **/
	private function notification_whitelabel( $args = [] ) {

		add_filter( 'notification/whitelabel', '__return_true' );

		/** Change Notification CPT page. */
		if ( isset( $args['page_hook'] ) && ! empty( $args['page_hook'] ) ) {

			add_filter( 'notification/whitelabel/cpt/parent', static function( $hook ) use ( $args ) {

				return $args['page_hook'];

			} );

		}

		/** Remove extensions. */
		if ( isset( $args['extensions'] ) && false === $args['extensions'] ) {

			add_filter( 'notification/whitelabel/extensions', '__return_false' );

		}

		/** Remove settings. */
		if ( isset( $args['settings'] ) && false === $args['settings'] ) {

			add_filter( 'notification/whitelabel/settings', '__return_false' );

		}

		/** Settings access. */
		if ( isset( $args['settings_access'] ) ) {

			add_filter( 'notification/whitelabel/settings/access', static function( $access ) use ( $args ) {

				return (array) $args['settings_access'];

			} );

		}

	}

	/**
	 * ucfirst() function for multibyte character encodings
	 *
	 * @param string $string - String to uppercase first letter.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return string
	 **/
	private function mb_ucfirst( $string ) {

        return mb_strtoupper( mb_substr( $string, 0, 1 ) ).mb_strtolower( mb_substr( $string, 1 ) );

	}

	/**
	 * Show admin warning, if we need API Key.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 **/


	/**
	 * Remove all other notices.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function remove_all_notices() {

		/** Work only on plugin settings page. */
		$screen = get_current_screen();
		if ( null === $screen ) { return; }

		if ( $screen->base !== self::$menu_base ) { return; }

		/** Remove other notices. */
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

	}

	/**
	 * Remove "Thank you for creating with WordPress" and WP version only from plugin settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 **/
	public function remove_wp_copyrights() {

		/** Remove "Thank you for creating with WordPress" and WP version from plugin settings page. */
		$screen = get_current_screen(); // Get current screen.
        if ( null === $screen ) { return; }

		/** QCvoicemssg Settings Page. */
		if ( $screen->base === self::$menu_base ) {
			add_filter( 'admin_footer_text', '__return_empty_string', 11 );
			add_filter( 'update_footer', '__return_empty_string', 11 );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 **/
	private function public_hooks() {

		/** Work only on frontend area. */
		if ( is_admin() ) { return; }

		/** Load CSS for Frontend Area. */
		add_action( 'wp_enqueue_scripts', [$this, 'styles'] ); // CSS.

		/** Load JavaScript for Frontend Area. */
		add_action( 'wp_enqueue_scripts', [$this, 'scripts'] ); // JS.

	}

	/**
	 * Process AJAX requests from frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function wpvoicemessage_send() {

		/** Verifies the Ajax request to prevent processing requests external of the blog. */
		// check_ajax_referer( 'wpvoicemessage-nonce', 'nonce' );

		/** Exit if no data to process. */
		if ( empty( $_POST ) ) { wp_die(); }

		/** Get QCvoicemssg Form ID. */
		$cForm_id =  filter_input(INPUT_POST, 'cform-id', FILTER_SANITIZE_NUMBER_INT );

		/** Save Audio file. */
		$audio_file_path = $this->save_audio_file( $cForm_id );

		/** Create qcvoicemsg_record record. */
		$post_id = $this->create_record( $cForm_id, $audio_file_path );

		/** Fire event to send email notification. */
		do_action( 'qcvoicemsg_record_added', $post_id );

		echo 'ok';

		wp_die();
	}

	/**
	 * Create qcvoicemsg_record record.
	 *
	 * @param int $cForm_id - ID of QCvoicemssg Form.
	 * @param string $audio_file_path - Full path to audio file.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return int - post_id
	 **/
	private function create_record( $cForm_id, $audio_file_path ) {

		/** QCvoicemssg Form. */
		$cForm = get_post( $cForm_id );

		/** Create record. */
		$post_id = wp_insert_post( [
			'post_type'     => 'qcvoicemsg_record',
			'post_title'    => 'Record: ' . $cForm->post_title,
			'post_status'   => 'pending',
		] );

		/** Fill meta fields. */
		if ( $post_id ) {

			/** Save audio file. */
			update_post_meta( $post_id, 'qcld_wpvm_vmwpmdp_voicemssg_audio', wp_slash( $audio_file_path ) );

			/** Save audio sample rate. */
			$sample_rate = filter_input(INPUT_POST, 'vmwpmdp-wpvoicemessage-audio-sample-rate', FILTER_SANITIZE_STRING );
			update_post_meta( $post_id, 'qcld_wpvm_vmwpmdp_voicemssg_audio_sample_rate', $sample_rate );

			/** Save QCvoicemssg Form ID. */
			update_post_meta( $post_id, 'vmwpmdp_cform_id', $cForm_id );

			/** Prepare Additional fields. */
			$fields_fb = get_post_meta( $cForm_id, 'vmwpmdp_additional_fields_fb', true );
			$fields_fb = json_decode( $fields_fb, true ); // Array with fields params.

		}

		return $post_id;

	}

	/**
	 * Save recorded audio file.
	 *
	 * @param int $cForm_id - ID of QCvoicemssg Form.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 **/
	private function save_audio_file( $cForm_id ) {

		if ( empty( $_FILES['vmwpmdp-wpvoicemessage-audio'] ) ) { return false; }

		/** Create file name for audio file. */
		$file_path = $this->prepare_audio_name( $cForm_id );

		/** Check file mime type. */
		$mime = mime_content_type( $_FILES['vmwpmdp-wpvoicemessage-audio']['tmp_name'] );
		$file_tmp_name = sanitize_text_field($_FILES['vmwpmdp-wpvoicemessage-audio']['tmp_name']);

		/** Looks like uploading some shit. */
		if ( ! in_array( $mime, [ 'audio/wav', 'audio/x-wav' ] ) ) {

			/** Remove temporary audio file. */
			wp_delete_file( $file_tmp_name );

			wp_die(); // Emergency exit.
		}

		/** Save audio file. */
		file_put_contents( $file_path, file_get_contents( $file_tmp_name ), FILE_APPEND );

		/** Remove temporary audio file. */
		wp_delete_file( $file_tmp_name );

		return $file_path;

	}

	/**
	 * Prepare unique file name for wav audio file.
	 *
	 * @param int $cForm_id - ID of QCvoicemssg Form.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 **/
	private function prepare_audio_name( $cForm_id ) {

		/** Prepare File name. */
		$upload_dir     = wp_get_upload_dir();
		$upload_basedir = $upload_dir['basedir'] . '/wpvoicemessage/'; // Path to upload folder.

		$unique_counter = 0;
		$file_name = $this->build_file_name( $cForm_id, $unique_counter );

		/** We do not need collisions. */
		$f_path = $upload_basedir . $file_name;
		if ( file_exists( $f_path ) ) {

			do {
				$unique_counter++;
				$file_name = $this->build_file_name( $cForm_id, $unique_counter );
				$f_path = $upload_basedir . $file_name;
			} while ( file_exists( $f_path ) );

		}

		$f_path = wp_normalize_path( $f_path );
		$f_path = str_replace( ['/', '\\'], DIRECTORY_SEPARATOR, $f_path );

		return $f_path;

	}

	/**
	 * Build file name.
	 *
	 * @param int $cForm_id - ID of QCvoicemssg Form.
	 * @param int $unique_counter - Unique identificator
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 **/
	private function build_file_name( $cForm_id, $unique_counter ) {

		return 'wpvoicemessage-' . $cForm_id . '-' . gmdate( 'Y-m-d\TH:i:s\Z' ) . '-' . $unique_counter . '.wav';

	}

	/**
	 * Initialize main variables.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function initialization() {

		/** Get Plugin version. */
		$plugin_data = $this->get_plugin_data();
		self::$version = $plugin_data['Version'];

		/** Gets the plugin URL (with trailing slash). */
		self::$url = plugin_dir_url( __FILE__ );

		/** Gets the plugin PATH. */
		self::$path = plugin_dir_path( __FILE__ );

		/** Use minified libraries if SCRIPT_DEBUG is turned off. */
		self::$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		self::$suffix = '';

		/** Set plugin basename. */
		self::$basename = plugin_basename( __FILE__ );

		/** Plugin settings page base. */
		self::$menu_base = 'qcvoicemsg_record_page_qcld_wpvm_vmwpmdp_voicemssg_settings';

		/** Create /wp-content/uploads/wpvoicemessage/ folder for audio files. */
		wp_mkdir_p( trailingslashit( wp_upload_dir()['basedir'] ) . 'wpvoicemessage/' );
		file_put_contents( trailingslashit( wp_upload_dir()['basedir'] ) . 'wpvoicemessage/index.php', 'No Access' );

	}

	/**
	 * Return current plugin metadata.
	 *
	 * @since 1.0.5
	 * @access public
	 *
	 * @return array {
	 *     Plugin data. Values will be empty if not supplied by the plugin.
	 *
	 *     @type string $Name        Name of the plugin. Should be unique.
	 *     @type string $Title       Title of the plugin and link to the plugin's site (if set).
	 *     @type string $Description Plugin description.
	 *     @type string $Author      Author's name.
	 *     @type string $AuthorURI   Author's website address (if set).
	 *     @type string $Version     Plugin version.
	 *     @type string $TextDomain  Plugin textdomain.
	 *     @type string $DomainPath  Plugins relative directory path to .mo files.
	 *     @type bool   $Network     Whether the plugin can only be activated network-wide.
	 *     @type string $RequiresWP  Minimum required version of WordPress.
	 *     @type string $RequiresPHP Minimum required version of PHP.
	 * }
	 **/
	public function get_plugin_data() {

		if ( ! function_exists('get_plugin_data') ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return get_plugin_data( __FILE__ );

	}

	/**
	 * Add CSS for the public-facing side of the site.
	 *
	 * @return void
	 * @since 1.0.0
	 **/
	public function styles() {

		/** Frontend CSS for shortcodes. */
		wp_register_style( 'vmwpmdp-wpvoicemessage', self::$url . 'css/wpvoicemessage' . self::$suffix . '.css', [], self::$version );

		$inline_css = $this->get_voicemssg_inline_css();

		/** Add custom CSS. */
		wp_add_inline_style( 'vmwpmdp-wpvoicemessage', $inline_css . \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['custom_css'] );

	}

	/**
	 * Return inline CSS for wpvoicemessage.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 **/
	private function get_voicemssg_inline_css() {

	    /** Get Plugin Settings. */
	    $options = \QuantumCloud\QCvoicemssg\Settings::get_instance()->options;

		/** Accent Color. */
		$accent_color = $options['accent_color'];

		// language=CSS
		/** @noinspection CssUnusedSymbol */
		return "
            .vmwpmdp-wpvoicemessage-player-box.green-audio-player .slider .gap-progress,
            .vmwpmdp-wpvoicemessage-player-box.green-audio-player .slider .gap-progress .pin {
                background-color: {$accent_color};
            }
            
            .vmwpmdp-wpvoicemessage-player-box.green-audio-player .volume .volume__button.open path {
                fill: {$accent_color};
            } 
        ";

    }

	/**
	 * Add JavaScript for the public-facing side of the site.
	 *
	 * @return void
	 * @since 1.0.0
	 **/
	public function scripts() {

		/** Frontend JS for shortcodes. */
		wp_register_script( 'vmwpmdp-wpvoicemessage-recorder', self::$url . 'js/recorder' . self::$suffix . '.js', [], self::$version, true );
		wp_register_script( 'green-audio-player', self::$url . 'js/green-audio-player' . self::$suffix . '.js', [], self::$version, true );
		wp_register_script( 'vmwpmdp-wpvoicemessage', self::$url . 'js/wpvoicemessage' . self::$suffix . '.js', ['vmwpmdp-wpvoicemessage-recorder', 'green-audio-player'], self::$version, true );

		/** Pass variables to frontend. */
		wp_localize_script( 'vmwpmdp-wpvoicemessage', 'vmwpmdpContacterWP', [
			'nonce' => wp_create_nonce( 'wpvoicemessage-nonce' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'accentColor' => \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['accent_color']
		] );

	}

	/**
	 * Loads the QCvoicemssg translated strings.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function load_textdomain() {

		load_plugin_textdomain( 'wpvoicemessage', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Add CSS for admin area.
	 *
	 * @return void
	 * @since 1.0.0
	 **/
	public function admin_styles() {

		/** Get current screen to add styles on specific pages. */
		$screen = get_current_screen();
		if($screen->post_type == 'qcvoicemsg_record'){
			wp_enqueue_style( 'vmwpmdp-wpvoicemessage-admin', self::$url . 'css/admin' . self::$suffix . '.css', [], self::$version );
		}
		if ( null === $screen ) { return; }

		/** QCvoicemssg Settings Page. */
		if ( self::$menu_base === $screen->base ) {
			wp_enqueue_style( 'merkulov-ui', self::$url . 'css/merkulov-ui' . self::$suffix . '.css', [], self::$version );
		//	wp_enqueue_style( 'vmwpmdp-wpvoicemessage-admin', self::$url . 'css/admin' . self::$suffix . '.css', [], self::$version );

		/** QCvoicemssg popup on update. */
		}elseif ( 'post' === $screen->base && 'voicemssg_form_qcwp' === $screen->post_type ) {

			/** Add class .mdc-disable to body. So we can use UI without overrides WP CSS, only for this page.  */
			add_action( 'admin_body_class', [$this, 'add_admin_class'] );

			wp_enqueue_style( 'merkulov-ui', self::$url . 'css/merkulov-ui' . self::$suffix . '.css', [], self::$version );

		/** QCvoicemssg Record edit screen. */
		}

	}

	/**
	 * Add class to body in admin area.
	 *
	 * @param string $classes - Space-separated list of CSS classes.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function add_admin_class( $classes ) {

		return $classes . ' mdc-disable ';

	}

	/**
	 * Add JS for admin area.
	 *
	 * @return void
	 * @since 1.0.0
	 **/
	public function admin_scripts() {

		/** Get current screen to add scripts on specific pages. */
		$screen = get_current_screen();
		if ( null === $screen ) { return; }

		/** QCvoicemssg Settings Page. */
		if ( $screen->base === self::$menu_base ) {
			wp_enqueue_script( 'merkulov-ui', self::$url . 'js/merkulov-ui' . self::$suffix . '.js', [], self::$version, true );
			wp_enqueue_media(); // WordPress Image library for API Key File.
			wp_enqueue_script( 'vmwpmdp-wpvoicemessage-admin', self::$url . 'js/admin' . self::$suffix . '.js', ['jquery'], self::$version, true );

			wp_localize_script('vmwpmdp-wpvoicemessage-admin', 'vmwpmdpContacter', [
				'ajaxURL' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'reset_settings' ),
				'voicemssg_nonce' => wp_create_nonce( 'wpvoicemessage-nonce' ), // Nonce for security.
			] );

		/** QCvoicemssg Form edit screen. */
		} elseif ( 'post' === $screen->base && 'voicemssg_form_qcwp' === $screen->post_type ) {

			/** Add class .mdc-disable to body. So we can use UI without overrides WP CSS, only for this page. */
			add_action( 'admin_body_class', [$this, 'add_admin_class'] );

			wp_enqueue_script( 'merkulov-ui', self::$url . 'js/merkulov-ui' . self::$suffix . '.js', [], self::$version, true );

		/** QCvoicemssg Record edit screen. */
		} elseif ( 'post' === $screen->base && 'qcvoicemsg_record' === $screen->post_type ) {

        /** QCvoicemssg Forms list. */
		} elseif ( 'edit' === $screen->base && 'voicemssg_form_qcwp' === $screen->post_type ) {

		    wp_enqueue_script( 'vmwpmdp-admin-wpvoicemessage-forms', self::$url . 'js/admin-wpvoicemessage-forms' . self::$suffix . '.js', ['jquery'], self::$version, true );

        }
		wp_enqueue_script( 'vmwpmdp-wpvoicemessage-common', self::$url . 'js/common-wpvoice' . self::$suffix . '.js', ['jquery'], self::$version, true );
	}

	/**
	 * Return locale for Form Builder.
	 *
	 * @since 1.0.0
	 * @return string
	 **/
	private function get_fb_locale() {

		/** Get current user Locale. */
		$locale = get_user_locale();

		/** Convert "en_US" to "en-US". */
		$locale = str_replace( '_', '-', $locale );

		/** Do we have translations file for this locale? */
		if ( file_exists( self::$path . 'js/form-builder-languages/' . $locale . '.lang' ) ) {
			return $locale;
		}

		return 'en-US';

	}

	/**
	 * Run when the plugin is activated.
	 *
	 * @static
	 * @since 1.0.0
	 **/
	public static function on_activation() {

		/** Security checks. */
		if ( ! current_user_can( 'activate_plugins' ) ) { return; }

		/** We need to know plugin to activate it. */
		if ( ! isset( $_REQUEST['plugin'] ) ) { return; }

		/** Get plugin. */
		$plugin = filter_var( $_REQUEST['plugin'], FILTER_SANITIZE_STRING );

		/** Checks that a user was referred from admin page with the correct security nonce. */
		check_admin_referer( "activate-plugin_{$plugin}" );

		/** Send install Action to our host. */
		self::send_install_action();

		/** Create Default Form. */
		\QuantumCloud\QCvoicemssg\VoiceMessageForm::create_default_form();

	}

	/**
	 * Send install Action to our host.
	 *
	 * @static
	 * @since 1.0.5
	 **/
	private static function send_install_action() {

		/** Plugin version. */
		$ver = self::get_instance()->get_version();

		/** Have we already sent 'install' for this version? */
		$opt_name = 'qcld_wpvm_vmwpmdp_wpvoicemessage_send_action_install';
		$ver_installed = get_option( $opt_name );

		/** Send install Action to our host. */
		if ( ! $ver_installed || $ver !== $ver_installed ) {

			/** Send install Action to our host. */
			update_option( $opt_name, $ver );

		}

	}

	/**
	 * Main QCvoicemssg Instance.
	 *
	 * Insures that only one instance of QCvoicemssg exists in memory at any one time.
	 *
	 * @static
	 * @since 1.0.0
	 **@return wpvoicemessage
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

	function wpvoicemessage_order_index_catalog_menu_page( $menu_ord ){
		global $submenu;
    	
		$submenu['edit.php?post_type=qcvoicemsg_record'][15] = array( 'Upgrade to Pro', 'manage_options' , 'https://www.dna88.com/product/wpvoice' ); 
	
		  // Enable the next line to see a specific menu and it's order positions
		  // echo '<pre>'; print_r( $submenu['edit.php?post_type=qcvoicemsg_record'] ); echo '</pre>'; exit();

		  // Sort the menu according to your preferences
		  //Original order was 5,11,12,13,14,15

		//  echo "<pre>", print_r($submenu,1), "</pre>n";
		  
		  
		//   $arr = array();
		//     $arr[] = $submenu['edit.php?post_type=qcvoicemsg_record'][5];
		//     $arr[] = $submenu['edit.php?post_type=qcvoicemsg_record'][11];
		//     $arr[] = $submenu['edit.php?post_type=qcvoicemsg_record'][12];
		//     $arr[] = $submenu['edit.php?post_type=qcvoicemsg_record'][13];
		//     $arr[] = $submenu['https://www.dna88.com/wpvoice/'][15];
		//   $submenu['edit.php?post_type=qcvoicemsg_record'] = $arr;
		
		  return $submenu;
	}
	function func_show_upgrade_link_with_action_links( $links )
        {
            $links = array_merge( $links, array(
                '<a  style="font-weight: bold; color: '.$this->link_color.';" href="' . esc_url( $this->upgrade_link ) . '" target="'.$this->link_target.'">' . __( $this->link_text, 'quantumcloud' ) . '</a>'
            ) );

            return $links;
        }
	

} // End Class QCvoicemssg.

add_filter(
	'plugin_row_meta',
	function ( $meta, $plugin_file ) {
		if($plugin_file == 'wp-voice/wpvoicemessage.php'){
			return array_merge($meta, get_plugin_row_meta());
		}
		return $meta;
	},
	10,
	2
);
function get_plugin_row_meta() {
	return array(
		'<a href="https://www.dna88.com/wpvoice/" style="color:#fa983a;font-weight: bold;">' . __( 'Upgrade To Pro', 'google-site-kit' ) . '</a>',
		
	);
}
 

/** Run when the plugin is activated. */
register_activation_hook( __FILE__, [wpvoicemessage::class, 'on_activation'] );

/** Run Speaker class once after activated plugins have loaded. */
add_action( 'plugins_loaded', [wpvoicemessage::get_instance(), 'setup'] );


require_once( plugin_dir_path(__FILE__) . 'modules/cf7/cf7-voicemessage-shortcode.php' );
require_once( plugin_dir_path(__FILE__) . 'modules/cf7/cf7-voicemessage-create.php' );
require_once( plugin_dir_path(__FILE__) . 'modules/cf7/cf7.php' );

