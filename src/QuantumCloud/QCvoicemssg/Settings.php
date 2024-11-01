<?php
namespace QuantumCloud\QCvoicemssg;

use Google\Cloud\Core\Testing\Snippet\Container;
use QuantumCloud\wpvoicemessage;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class used to implement plugin settings.
 *
 * @since 1.0.0
 **/
final class Settings {

	/**
	 * QCvoicemssg Plugin settings.
	 *
	 * @var array()
	 * @since 1.0.0
	 **/
	public $options = [];

	/**
	 * The one true Settings.
	 *
	 * @var Settings
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new Settings instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Get plugin settings. */
		$this->get_options();

	}

	/**
	 * Render Tabs Headers.
	 *
	 * @param string $current - Selected tab key.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_tabs( $current = 'general' ) {

		/** Get available tabs. */
		$tabs = $this->get_tabs();

		/** Render Tabs. */
		?>
        <aside class="mdc-drawer">
            <div class="mdc-drawer__content">
                <nav class="mdc-list">
					<?php

					/** Render logo in plugin settings. */
					$this->render_logo();

					/** Render settings tabs. */
					$this->render_tabs( $tabs, $current );

					?>
                </nav>
            </div>
        </aside>
		<?php
	}

	/**
	 * Render logo and Save changes button in plugin settings.
	 *
	 * @access private
	 * @since 1.0.5
	 *
	 * @return void
	 **/
	private function render_logo() {

		?>
        <div class="mdc-drawer__header mdc-plugin-fixed">
            <!--suppress HtmlUnknownAnchorTarget -->
            <a class="mdc-list-item vmwpmdp-plugin-title" href="#wpwrap">
                <i class="mdc-list-item__graphic" aria-hidden="true">
                    <img src="<?php echo esc_attr( wpvoicemessage::$url . 'images/logo-color.svg' ); ?>" alt="<?php echo esc_html__( 'Voice Message for WPBot', 'wpvoicemessage' ) ?>">
                </i>
                <span class="mdc-list-item__text">
                    <?php echo esc_html__( 'Voice Message for WPBot', 'wpvoicemessage' ) ?>
                </span>
            </a>
            <button type="submit" name="submit" id="submit" class="mdc-button mdc-button--dense mdc-button--raised">
                <span class="mdc-button__label"><?php echo esc_html__( 'Save changes', 'wpvoicemessage' ) ?></span>
            </button>
        </div>
		<?php

	}

	/**
	 * Render settings tabs.
	 *
	 * @param array $tabs       - Array of available tabs.
	 * @param string $current   - Slug of active tab.
	 *
	 * @access private
	 * @since  1.0.5
	 *
	 * @return void
	 **/
	private function render_tabs( $tabs, $current ) {

		?>
        <hr class="mdc-plugin-menu">
        <!-- <hr class="mdc-list-divider"> -->
        <h6 class="mdc-list-group__subheader"><?php echo esc_html__( 'Plugin settings', 'wpvoicemessage' ) ?></h6>
		<?php

		/** Plugin settings tabs. */
		foreach ( $tabs as $tab => $value ) {

			/** Prepare CSS classes. */
			$classes = [];
			$classes[] = 'mdc-list-item';

			/** Mark Active Tab. */
			if ( $tab === $current ) {
				$classes[] = 'mdc-list-item--activated';
			}

			/** Hide Developer tab before multiple clicks on logo. */
			if ( 'developer' === $tab ) {
				$classes[] = 'vmwpmdp-developer';
				$classes[] = 'mdc-hidden';
				$classes[] = 'mdc-list-item--activated';
			}

			/** Prepare link. */
			$link = '?post_type=qcvoicemsg_record&page=qcld_wpvm_vmwpmdp_voicemssg_settings&tab=' . $tab;

			?>
            <a class="<?php esc_attr_e( implode( ' ', $classes ) ); ?>" href="<?php esc_attr_e( $link ); ?>">
                <i class='material-icons mdc-list-item__graphic' aria-hidden='true'><?php esc_html_e( $value['icon'] ); ?></i>
                <span class='mdc-list-item__text'><?php esc_html_e( $value['name'] ); ?></span>
            </a>
			<?php
		}

	}

	/**
	 * Return an array of available tabs in plugin settings.
	 *
	 * @access private
	 * @since 1.0.5
	 *
	 * @return array
	 **/
	private function get_tabs() {

		/** Tabs array. */
		$tabs = [];
		$tabs['general'] = [
			'icon' => 'tune',
			'name' => esc_html__( 'General', 'wpvoicemessage' )
		];

		$tabs['messages'] = [
			'icon' => 'spellcheck',
			'name' => esc_html__( 'Default Messages', 'wpvoicemessage' )
		];


		/** Adds a developer tab. */
		$tabs = $this->add_developer_tab( $tabs );

		return $tabs;

	}

	/**
	 * Adds a developer tab if all the necessary conditions are met.
	 *
	 * @param array $tabs - Array of tabs to show in plugin settings.
	 *
	 * @access private
	 * @since  1.0.5
	 *
	 * @return array - Array of tabs to show in plugin settings.
	 **/
	private function add_developer_tab( $tabs ) {

		/** Output Developer tab only if DEBUG mode enabled. */
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			$tabs['developer'] = [
				'icon' => 'developer_board',
				'name' => esc_html__( 'Developer', 'wpvoicemessage' )
			];

		}

		return $tabs;

	}


	/**
	 * Add plugin settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_settings_page() {

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'settings_init' ] );

	}

	/**
	 * Create General Tab.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
    public function tab_general() {

	    /** General Tab. */
	    $group_name = 'ContacterOptionsGroup';
	    $section_id = 'qcld_wpvm_vmwpmdp_voicemssg_settings_page_general_section';
	    $option_name = 'qcld_wpvm_vmwpmdp_voicemssg_settings';

	    /** Create settings section. */
	    register_setting( $group_name, $option_name );
	    add_settings_section( $section_id, '', null, $group_name );

	    /** Render Settings fields. */
	    add_settings_field( 'max_duration', esc_html__( 'Max Duration:', 'wpvoicemessage' ),             [ SettingsFields::class, 'max_duration' ], $group_name, $section_id );
	    add_settings_field( 'accent_color', esc_html__( 'Accent Color:', 'wpvoicemessage' ),             [ SettingsFields::class, 'accent_color' ], $group_name, $section_id );
	    add_settings_field( 'show_download_link', esc_html__( 'Show Download Link:', 'wpvoicemessage' ), [ SettingsFields::class, 'show_download_link'], $group_name, $section_id );
	    add_settings_field( 'download_link_text', esc_html__( 'Download Link Text:', 'wpvoicemessage' ), [ SettingsFields::class, 'download_link_text'], $group_name, $section_id );

    }


	/**
	 * Create Messages Tab.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function tab_messages() {

		/** Messages Tab. */
		$group_name = 'ContacterMessagesOptionsGroup';
		$section_id = 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings_page_general_section';
		$option_name = 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings';

		/** Create settings section. */
		register_setting( $group_name, $option_name );
		add_settings_section( $section_id, '', null, $group_name );

		/** Render Settings fields. */
        add_settings_field( 'msg_before_txt', esc_html__( 'Before Text:', 'wpvoicemessage' ),          [ SettingsFields::class, 'msg_before_txt'], $group_name, $section_id );
		add_settings_field( 'msg_after_txt', esc_html__( 'After Text:', 'wpvoicemessage' ),            [ SettingsFields::class, 'msg_after_txt'], $group_name, $section_id );
		add_settings_field( 'msg_speak_now', esc_html__( 'Speak Now:', 'wpvoicemessage' ),             [ SettingsFields::class, 'msg_speak_now'], $group_name, $section_id );
		add_settings_field( 'msg_allow_access', esc_html__( 'Allow Access:', 'wpvoicemessage' ),       [ SettingsFields::class, 'msg_allow_access'], $group_name, $section_id );
		add_settings_field( 'msg_mic_access_err', esc_html__( 'Access error:', 'wpvoicemessage' ),     [ SettingsFields::class, 'msg_mic_access_err'], $group_name, $section_id );
		add_settings_field( 'msg_reset_recording', esc_html__( 'Reset recording:', 'wpvoicemessage' ), [ SettingsFields::class, 'msg_reset_recording'], $group_name, $section_id );
		add_settings_field( 'msg_send', esc_html__( 'Send recording:', 'wpvoicemessage' ),             [ SettingsFields::class, 'msg_send'], $group_name, $section_id );
		add_settings_field( 'msg_sending_error', esc_html__( 'Sending error:', 'wpvoicemessage' ),     [ SettingsFields::class, 'msg_sending_error'], $group_name, $section_id );
        add_settings_field( 'msg_thank_you', esc_html__( '"Thank you" message:', 'wpvoicemessage' ),   [ SettingsFields::class, 'msg_thank_you'], $group_name, $section_id );

    }

	/**
	 * Generate Settings Page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function settings_init() {

		/** General Tab. */
	    $this->tab_general();

		/** Messages. */
		$this->tab_messages();

	}

	/**
	 * Add admin menu for plugin settings.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_admin_menu() {

		add_submenu_page(
			'edit.php?post_type=' . ContacterRecord::POST_TYPE,
			esc_html__( 'QCvoicemssg Settings', 'wpvoicemessage' ),
			esc_html__( 'Settings', 'wpvoicemessage' ),
			'manage_options',
			'qcld_wpvm_vmwpmdp_voicemssg_settings',
			[$this, 'options_page']
		);
		add_submenu_page(
			'edit.php?post_type=qcvoicemsg_record',
			__( 'Help', 'textdomain' ),
			__( 'Help', 'textdomain' ),
			'manage_options',
			'wpvoicemessage_help',
			array($this,'wpvoicemessage_help_callback')
		);
		

	}

	/**
	 * Plugin Settings Page.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function wpvoicemessage_help_callback(){
		ob_start();
		//wp_enqueue_script('qcld-voice-license-js',  'js/license.js?tes', array('jquery'), true);
		wp_enqueue_style('qcld-wp-voice-help-page-css',  plugins_url( 'wp-voice/css/help-page.css'));
		require_once('include/help.php');
	}

	public function qcld_wpvm_pricing_callback(){
		ob_start();
		$site_url = wpvoicemessage::$url;
		wp_enqueue_script('owl-caro-js',  plugin_dir_url('wp-voice/js'). 'js/owl-caro.min.js', array('jquery'), true);
		wp_enqueue_script('magnific',  plugin_dir_url('wp-voice/js'). 'js/magnific.min.js', array('jquery'), true);
		wp_enqueue_script('upgrade-to-voice-pro',  plugin_dir_url('wp-voice/js'). 'js/upgradevoicepro.js', array('jquery'), true);
		wp_enqueue_style('qcld-wp-voice-pricingpage-css',  plugin_dir_url('wp-voice/css/pricing.css'). 'pricing-page.css');
		wp_enqueue_style('owl-caro-css',  plugin_dir_url('wp-voice/css/pricing.css'). 'owl-caro.min.css');
		wp_enqueue_style('qcld-wp-voice-font-awesome',  plugins_url( 'wp-voice/css/font-awesome.css'));
		wp_enqueue_style('qcld-wp-voice-magnific',  plugins_url( 'wp-voice/css/magnific.css'));
		require_once('include/pricing.php');
	}

	public function options_page() {

		/** User rights check. */
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		} ?>
        <!--suppress HtmlUnknownTarget -->
        <form action='options.php' method='post'>
            <div class="wrap">

				<?php
				$tab = 'general';
				if ( isset ( $_GET['tab'] ) ) { $tab = sanitize_text_field($_GET['tab']); }

				/** Render "QCvoicemssg settings saved!" message. */
				SettingsFields::get_instance()->render_nags();

				/** Render Tabs Headers. */
				?><section class="vmwpmdp-aside"><?php $this->print_tabs( $tab ); ?></section><?php

				/** Render Tabs Body. */
				?><section class="vmwpmdp-tab-content vmwpmdp-tab-<?php echo esc_attr( $tab ) ?>"><?php

					/** General Tab. */
					if ( 'general' === $tab ) {
						echo '<h3>' . esc_html__( 'General Settings', 'wpvoicemessage' ) . '</h3>';
						settings_fields( 'ContacterOptionsGroup' );
						do_settings_sections( 'ContacterOptionsGroup' );

                    /** Messages Tab. */
					} elseif ( 'messages' === $tab ) {
						echo '<h3>' . esc_html__( 'Messages', 'wpvoicemessage' ) . '</h3>';
						echo '<p>' . esc_html__( 'These are default messages that will be used to create new forms. Changing the settings on this page will not change the settings for existing forms.', 'wpvoicemessage' ) . '</p>';
						settings_fields( 'ContacterMessagesOptionsGroup' );
						do_settings_sections( 'ContacterMessagesOptionsGroup' );

                    /** Developer Tab. */
                    }

					?>
                </section>
            </div>
        </form>

		<?php
	}

	/**
	 * Get plugin settings with default values.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 **/
	public function get_options() {

		/** General Tab Options. */
		$options = get_option( 'qcld_wpvm_vmwpmdp_voicemssg_settings' );

		/** Default values. */
		$defaults = [
			// General Tab
			// TODO: Remove 'api_key' in 1.0.6
			'api_key'                   => '',
            'max_duration'              => '60',
			'accent_color'              => '#0274e6',
            'show_download_link'        => isset( $options[ 'show_download_link' ] ) ? $options[ 'show_download_link' ] : 'off', // Show Download Link
			'download_link_text'        => esc_html__( 'Download record', 'wpvoicemessage' ), // Download Link Text

			// Float Button Tab
			'show_fbutton'              => isset( $options[ 'show_fbutton' ] ) ? $options[ 'show_fbutton' ] : 'off', // Show Float Button.

			// Messages Tab
			'msg_before_txt'            => '<h4>' . esc_html__( 'We would love to hear from you!', 'wpvoicemessage' ) . '</h4><p>' . esc_html__( 'Please record your message.', 'wpvoicemessage' ) . '</p>',
			'msg_after_txt'             => '<p>' . esc_html__( 'Record, Listen, Send', 'wpvoicemessage' ) . '</p>',
			'msg_speak_now'             => '<h4>' . esc_html__( 'Speak now', 'wpvoicemessage' ) . '</h4><div>{countdown}</div>',
			'msg_allow_access'          => '<h4>' . esc_html__( 'Allow access to your microphone', 'wpvoicemessage' ) . '</h4><p>' . esc_html__( 'Click "Allow" in the permission dialog. It usually appears under the address bar in the upper left side of the window. We respect your privacy.', 'wpvoicemessage' ) . '</p>',
            'msg_mic_access_err'        => '<h4>' . esc_html__( 'Microphone access error', 'wpvoicemessage' ) . '</h4><p>' . esc_html__( 'It seems your microphone is disabled in the browser settings. Please go to your browser settings and enable access to your microphone.', 'wpvoicemessage' ) . '</p>',
			'msg_reset_recording'       => '<h4>' . esc_html__( 'Reset recording', 'wpvoicemessage' ) . '</h4><p>' . esc_html__( 'Are you sure you want to start a new recording? Your current recording will be deleted.', 'wpvoicemessage' ) . '</p>',
			'msg_send'                  => '<h4>' . esc_html__( 'Send your recording', 'wpvoicemessage' ) . '</h4>',
			'msg_sending_error'         => '<h4>' . esc_html__( 'Oops, something went wrong', 'wpvoicemessage' ) . '</h4><p>' . esc_html__( 'Error occurred during uploading your audio. Please click the Retry button to try again.', 'wpvoicemessage' ) . '</p>',
            'msg_thank_you'             => '<h4>' . esc_html__( 'Thank you', 'wpvoicemessage' ) . '</h4>',

			// Custom CSS Tab
            'custom_css'                => '',
        ];

		/** Transcription work only with PHP 7+. */
		if ( defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION < 7 ) {
			$options['dnd-api-key'] = '';
        }

		$results = wp_parse_args( $options, $defaults );

		/** Float Button Tab Options. */
		$float_button = get_option( 'qcld_wpvm_vmwpmdp_voicemssg_floatbutton_settings' );
		$results = wp_parse_args( $float_button, $results );

		/** Messages Tab Options. */
		$messages_tab = get_option( 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings' );
		$results = wp_parse_args( $messages_tab, $results );

		/** Custom CSS tab Options. */
		$qcld_wpvm_vmwpmdp_voicemssg_css_settings = get_option( 'qcld_wpvm_vmwpmdp_voicemssg_css_settings' );
		$results = wp_parse_args( $qcld_wpvm_vmwpmdp_voicemssg_css_settings, $results );

		$this->options = $results;
	}

	/**
	 * Main Settings Instance.
	 *
	 * Insures that only one instance of Settings exists in memory at any one time.
	 *
	 * @static
	 * @return Settings
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Settings.
