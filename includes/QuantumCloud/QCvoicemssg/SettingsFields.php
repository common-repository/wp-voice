<?php
namespace QuantumCloud\QCvoicemssg;

use QuantumCloud\wpvoicemessage;
use WP_Query;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class used to render plugin settings fields.
 *
 * @since 1.0.0
 **/
final class SettingsFields {

	/**
	 * The one true SettingsFields.
	 *
	 * @var SettingsFields
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Render Download Link Text field.
	 *
	 * @since 1.0.6
	 * @access public
	 **/
	public static function download_link_text() {

		/** Render Show Download Link switcher. */
		UI::get_instance()->render_input(
			Settings::get_instance()->options['download_link_text'],
			esc_html__('Download Link Text', 'wpvoicemessage' ),
			esc_html__( 'Text for download link.', 'wpvoicemessage' ),
			[
				'name' => 'qcld_wpvm_vmwpmdp_voicemssg_settings[download_link_text]',
				'id' => 'vmwpmdp-wpvoicemessage-settings-download-link-text'
			]
		);

	}

	/**
	 * Render Show Download Link field.
	 *
	 * @since 1.0.5
	 * @access public
	 **/
	public static function show_download_link() {

		/** Render Show Download Link switcher. */
		UI::get_instance()->render_switches(
			Settings::get_instance()->options['show_download_link'],
			esc_html__('Show Download Link', 'wpvoicemessage' ),
			esc_html__( 'Show download link to audio on frontend.', 'wpvoicemessage' ),
			[
				'name' => 'qcld_wpvm_vmwpmdp_voicemssg_settings[show_download_link]',
				'id' => 'vmwpmdp-wpvoicemessage-settings-show-download-link'
			]
		);

    }


	/**
	 * Render Accent Color field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function accent_color() {

		/** Render Accent Color colorpicker. */
		UI::get_instance()->render_colorpicker(
			Settings::get_instance()->options['accent_color'],
			esc_html__( 'Accent Color', 'wpvoicemessage' ),
			esc_html__( 'Select accent color', 'wpvoicemessage' ),
			[
				'name' => 'qcld_wpvm_vmwpmdp_voicemssg_settings[accent_color]',
				'id' => 'qcld_wpvm_vmwpmdp_voicemssg_settings_accent_color',
				'readonly' => 'readonly'
			]
		);

	}

	/**
	 * Render Max duration field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function max_duration() {

		/** Max duration slider. */
		UI::get_instance()->render_slider(
			Settings::get_instance()->options['max_duration'],
			0,
			300,
			1,
			esc_html__( 'Border Radius', 'wpvoicemessage' ),
			esc_html__( 'Max recording duration: ', 'wpvoicemessage' ) .
			'<strong>' . Settings::get_instance()->options['max_duration'] . '</strong>' .
			esc_html__( ' seconds. Set 0 to unlimited record time.', 'wpvoicemessage' ),
			[
				'name' => 'qcld_wpvm_vmwpmdp_voicemssg_settings[max_duration]',
				'id' => 'qcld_wpvm_vmwpmdp_voicemssg_settings_max_duration',
				'class' => 'mdc-slider-width'
			]
		);

	}


	

	/**
	 * Render Before text message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_before_txt() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_before_txt'], 'vmwpmdpvoicemssgmessagessettingsmsgbeforetxt', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_before_txt]' ] );

	}

	/**
	 * Render After text message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_after_txt() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_after_txt'], 'vmwpmdpvoicemssgmessagessettingsmsgaftertxt', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_after_txt]' ] );

	}

	/**
	 * Render Speak Now message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_speak_now() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_speak_now'], 'vmwpmdpvoicemssgmessagessettingsmsgspeaknow', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_speak_now]' ] );
		?>
        <div class="mdc-select-helper-text mdc-select-helper-text--persistent">
            <?php esc_html_e( 'You can use special placeholders: {timer}, {max-duration}, {countdown}.', 'wpvoicemessage' ); ?>
        </div>
        <?php

	}

	/**
	 * Render Microphone access error message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_allow_access() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_allow_access'], 'vmwpmdpvoicemssgmessagessettingsmsgallowaccess', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_allow_access]' ] );

	}

	/**
	 * Render Allow Access to microphone message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_mic_access_err() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_mic_access_err'], 'vmwpmdpvoicemssgmessagessettingsmsgmicaccesserr', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_mic_access_err]' ] );

	}

	/**
	 * Render Sending Error message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_sending_error() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_sending_error'], 'vmwpmdpvoicemssgmessagessettingsmsgsendingerror', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_sending_error]' ] );

	}

	/**
	 * Render Reset recording message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_reset_recording() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_reset_recording'], 'vmwpmdpvoicemssgmessagessettingsmsgresetrecording', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_reset_recording]' ] );

	}

	/**
	 * Render Send recording message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_send() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_send'], 'vmwpmdpvoicemssgmessagessettingsmsgsend', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_send]' ] );

	}

	/**
	 * Render "Thank You" message field.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public static function msg_thank_you() {

		/** @noinspection SpellCheckingInspection */
		wp_editor( Settings::get_instance()->options['msg_thank_you'], 'vmwpmdpvoicemssgmessagessettingsmsgthankyou', [ 'textarea_rows' => 7, 'textarea_name' => 'qcld_wpvm_vmwpmdp_voicemssg_messages_settings[msg_thank_you]' ] );

	}

	/**
	 * Render "SettingsFields Saved" nags.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 **/
	public static function render_nags() {

		/** Did we try to save settings? */
		if ( ! isset( $_GET['settings-updated'] ) ) { return; }

		/** Are the settings saved successfully? */
		if (sanitize_text_field($_GET['settings-updated']) === 'true' ) {

			/** Render "SettingsFields Saved" message. */
			UI::get_instance()->render_snackbar( esc_html__( 'Settings saved!', 'wpvoicemessage' ) );
		}

		if ( ! isset( $_GET['tab'] ) ) { return; }

	}

	/**
	 * Main SettingsFields Instance.
	 *
	 * Insures that only one instance of SettingsFields exists in memory at any one time.
	 *
	 * @static
	 * @return SettingsFields
	 * @since 1.0.0
	 **/
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SettingsFields ) ) {
			self::$instance = new SettingsFields;
		}

		return self::$instance;
	}
	
} // End Class SettingsFields.
