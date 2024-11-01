<?php



/** Exit if accessed directly. */

if ( ! defined( 'ABSPATH' ) ) {

	header( 'Status: 403 Forbidden' );

	header( 'HTTP/1.1 403 Forbidden' );

	exit;

}



/**

 * SINGLETON: Class used to implement shortcodes.

 *

 * @since 1.0.0

 **/

final class QcCF7VoiceMessageShortcodes {



	/**

	 * The one true Shortcodes.

	 *

	 * @var Shortcodes

	 * @since 1.0.0

	 **/

	private static $instance;



	/**

	 * Sets up a new Shortcodes instance.

	 *

	 * @since 1.0.0

	 * @access public

	 **/

	private function __construct() {



		/** Initializes plugin shortcodes. */

		add_action( 'init', [$this, 'shortcodes_init'] );



	}



	/**

	 * Initializes shortcodes.

	 *

	 * @since 1.0.0

	 * @access public

	 * @return void

	 **/

	public function shortcodes_init() {



		/** Add plugin shortcode [wpvoicemessage id=""]. Works everywhere on site. */

		add_shortcode( 'cf7wpvoicemessage', [ $this, 'voicemssg_shortcode' ] );



	}



	/**

	 * Add QCvoicemssg by shortcode [wpvoicemessage].

	 *

	 * @param array $atts - Shortcodes attributes.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return string

	 **/

	public function voicemssg_shortcode( $atts = [] ) {



		/** Filter shortcode attributes. */

		$atts = shortcode_atts( [

			'id' => '',

			'title' => '',

			'name'	=>	''

		], $atts );



		/** Nothing to show without any parameters. */

		if ( '' === $atts['id'] && '' === $atts['title'] ) { return ''; }



		/** Unique id for current shortcode. */

		$id = $this->get_shortcode_id( $atts );



		/** Get QCvoicemssg form data. */

		$c_form = $this->get_cform( $atts );



		/** QCvoicemssg form not found. */

		if ( ! $c_form ) { return ''; }



		/** QCvoicemssg form not published. */

        if ( 'publish' !== $c_form->post_status) { return ''; }



		/** Enqueue styles and scripts only if shortcode used on this page. */

		$this->enqueue( $id, $c_form );



		/** Get align. */

		$vmwpmdp_align = get_post_meta( $c_form->ID, 'vmwpmdp_align', true );

		/** Default value. */

		if ( $vmwpmdp_align === '' ) {

			$vmwpmdp_align = 'center';

		}



		ob_start();



		?>



		<!-- Start QCvoicemssg WordPress Plugin -->

		<div id="<?php esc_attr_e( $id ); ?>"

             class="vmwpmdp-wpvoicemessage-form-box vmwpmdp-step-start vmwpmdp-align-<?php esc_attr_e( $vmwpmdp_align ); ?>"

             cform-name="<?php esc_attr_e( $c_form->post_title ); ?>"

             cform-id="<?php esc_attr_e( $c_form->ID ); ?>"

             max-duration="<?php esc_attr_e( \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['max_duration'] ); ?>"

        >



            <?php $this->render_start_box( $c_form ); // Start Recording Step. ?>



			<?php $this->render_allow_access_box(); // Allow access to microphone step. ?>



			<?php $this->render_mic_access_err_box( $c_form ); // Microphone access error step. ?>



			<?php $this->render_recording_box( $c_form ); // Speak Now Step. ?>



			<?php $this->render_reset_box( $c_form ); // Reset Step. ?>



			<?php $this->render_error_box(); // Error Step. ?>



			<?php $this->render_send_box( $c_form ); // Send Step. ?>



			<?php $this->render_thanks_box( $c_form ); // Thank You Step. ?>


			<!-- <input type="hidden" name="<?php echo $atts['name']; ?>[type]" value="qccf7wpvoicemessage"> -->

			<input type="hidden" class="cf7-wpvoicemessage-field" name="qcwpvoicemessage">


		</div>



		<!-- End QCvoicemssg WordPress Plugin -->

		<?php



		return ob_get_clean();



	}



	/**

	 * Render Thank You Step.

	 *

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_thanks_box( $c_form ) {



		$thanks_txt = get_post_meta( $c_form->ID, 'vmwpmdp_thanks_txt', true );
		$vmwpmdp_start_new_message_caption = get_post_meta( $c_form->ID, 'vmwpmdp_start_new_message_caption', true );
		/** Default value. */

		if ( empty( $thanks_txt ) ) {

			// $thanks_txt = \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_thank_you'];
			$thanks_txt = esc_html__( 'Message Saved', 'wpvoicemessage' );

		}
		if ( $vmwpmdp_start_new_message_caption === '' ) {

			$vmwpmdp_start_new_message_caption = esc_html__( 'Start a new message', 'wpvoicemessage' );

		}
		?>

		<div class="vmwpmdp-wpvoicemessage-thanks-box">

			<?php echo wp_kses_post( $thanks_txt ); ?>

            <a class="vmwpmdp-restart"><span><?php esc_html_e( $vmwpmdp_start_new_message_caption, 'wpvoicemessage' ); ?></span></a>

        </div>

		<?php

	}



	/**

	 * Render Send Step.

	 *

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_send_box( $c_form ) {



	    /** Get Additional fields. */

        $send_txt = get_post_meta( $c_form->ID, 'vmwpmdp_send_txt', true );

		$additional_fields = get_post_meta( $c_form->ID, 'vmwpmdp_additional_fields', true );

		$fields_res = get_post_meta( $c_form->ID, 'vmwpmdp_additional_fields_res', true );
		$vmwpmdp_btn_reset = get_post_meta( $c_form->ID, 'vmwpmdp_btn_reset', true );
		$vmwpmdp_btn_save = get_post_meta( $c_form->ID, 'vmwpmdp_btn_save', true );

		if ( $vmwpmdp_btn_reset === '' ) {
			$vmwpmdp_btn_reset = esc_html__( 'Reset', 'wpvoicemessage' );
		}
		if ( $vmwpmdp_btn_save === '' ) {
			$vmwpmdp_btn_save = esc_html__( 'Save', 'wpvoicemessage' );
		}
		/** Default value. */

		if ( empty( $send_txt ) ) {

			$send_txt =\QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_send'];

		}



		/** Default value. Additional Fields switcher. */

		if ( '' === $additional_fields ) {

			$additional_fields = 'off';

		}



		/** Default value. Form in HTML. */

		if ( '' === $fields_res ) {

			$fields_res = '<div class="rendered-form"><div class="fb-text form-group field-first-name"><label for="first-name" class="fb-text-label">First Name</label></div><div class="fb-text form-group field-e-mail"><label for="e-mail" class="fb-text-label">E-mail<span class="fb-required">*</span></label></div></div>';

		}



		?>

		<div class="vmwpmdp-wpvoicemessage-send-box">

            <?php if ( 'on' === $additional_fields ) : ?><form novalidate=""><?php endif; ?>



                <?php echo wp_kses_post( $send_txt ); ?>

                <div class="vmwpmdp-wpvoicemessage-player-box"></div>



                <?php if ( 'on' === \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['show_download_link'] ) : ?>

                    <p class="vmwpmdp-wpvoicemessage-download-link-box">

                        <a href="#"><?php esc_html_e( \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['download_link_text'] ); ?></a>

                    </p>

                <?php endif; ?>



		        <?php if ( 'on' === $additional_fields ) : ?>

                    <div class="vmwpmdp-wpvoicemessage-additional-fields"

                         additional-fields="<?php esc_attr_e( $fields_res ); ?>"

                         user-login="<?php esc_attr_e( wp_get_current_user()->user_login ); ?>"

                         user-ip="<?php esc_attr_e( $this->get_real_ip() ); ?>"

                    ></div>

                <?php endif; ?>



                <div class="vmwpmdp-send-btns vmwpmdp-hover-<?php esc_attr_e( get_post_meta( $c_form->ID, 'vmwpmdp_btn_hover_animation', true ) ) ?>">

                    <a class="vmwpmdp-send-rec-btn" style="display:none"><span><?php esc_html_e( $vmwpmdp_btn_save ); ?></span></a>

                    <div class="vmwpmdp-speak-now-btns vmwpmdp-speak-now-btns">

						<a class="vmwpmdp-reset-rec-yes"><span><?php esc_html_e( $vmwpmdp_btn_reset ); ?></span></a>

					</div>

                </div>



			<?php if ( 'on' === $additional_fields ) : ?></form><?php endif; ?>

		</div>

		<?php

	}



	/**

	 * Return real user IP.

	 *

	 * @since  1.0.5

	 * @access public

	 * @return string

	 **/

	private function get_real_ip() {



		/** Check ip from share internet. */

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {



			$ip = $_SERVER['HTTP_CLIENT_IP'];



        /** To check ip is pass from proxy. */

		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {



			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];



		} else {



			$ip = $_SERVER['REMOTE_ADDR'];



		}



		return $ip;



	}



	/**

	 * Render Error Step.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_error_box() {

		?>

		<div class="vmwpmdp-wpvoicemessage-error-box">

			<?php echo wp_kses_post( \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_sending_error'] ); ?>

		</div>

		<?php

	}



	/**

	 * Render Reset Step.

	 *

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_reset_box( $c_form ) {
		$vmwpmdp_btn_reset = get_post_meta( $c_form->ID, 'vmwpmdp_btn_reset', true );
		$vmwpmdp_btn_resume = get_post_meta( $c_form->ID, 'vmwpmdp_btn_resume', true );
		if ( $vmwpmdp_btn_reset === '' ) {
			$vmwpmdp_btn_reset = esc_html__( 'Reset', 'wpvoicemessage' );
		}
		if ( $vmwpmdp_btn_resume === '' ) {
			$vmwpmdp_btn_resume = esc_html__( 'Resume', 'wpvoicemessage' );
		}
		?>

		<div class="vmwpmdp-wpvoicemessage-reset-box vmwpmdp-hover-<?php esc_attr_e( get_post_meta( $c_form->ID, 'vmwpmdp_btn_hover_animation', true ) ) ?>">

			<?php echo wp_kses_post( \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_reset_recording'] ); ?>

			<div class="vmwpmdp-speak-now-btns">

				<a class="vmwpmdp-reset-rec-yes"><span><?php esc_html_e( $vmwpmdp_btn_reset ); ?></span></a>

				<a class="vmwpmdp-reset-rec-no"><span><?php esc_html_e($vmwpmdp_btn_resume); ?></span></a>

			</div>

		</div>

		<?php

	}



	/**

	 * Render Speak Now Step.

	 *

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_recording_box( $c_form ) {

		?>

		<div class="vmwpmdp-wpvoicemessage-recording-box">

			<?php

			/** Get Speak Now content. */

			$vmwpmdp_speak_now_msg = get_post_meta( $c_form->ID, 'vmwpmdp_speak_now_txt', true );

			$vmwpmdp_btn_pause = get_post_meta( $c_form->ID, 'vmwpmdp_btn_pause', true );
			$vmwpmdp_btn_stop = get_post_meta( $c_form->ID, 'vmwpmdp_btn_stop', true );
			if( $vmwpmdp_btn_pause === '' ) {
				$vmwpmdp_btn_pause = esc_html__( 'Pause', 'wpvoicemessage' );
			}
			if($vmwpmdp_btn_stop === '' ) {
				$vmwpmdp_btn_stop = esc_html__( 'Stop', 'wpvoicemessage' );
			}
			/** Default value. */

			if ( empty( $speak_now_txt ) ) {

				$speak_now_txt =\QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_speak_now'];

			}



			/** Replace {timer} {max-duration} {countdown} placeholders. */

			$vmwpmdp_speak_now_msg = $this->replace_placeholders( $vmwpmdp_speak_now_msg );



			/** Show Speak Now content. */

			echo wp_kses_post( $vmwpmdp_speak_now_msg );

			?>

            <div class="vmwpmdp-wpvoicemessage-recording-animation">

                <canvas width="384" height="60">

                    <div><?php esc_html_e( 'Canvas not available.', 'wpvoicemessage' ); ?></div>

                </canvas>

            </div>

			<div class="vmwpmdp-speak-now-btns vmwpmdp-hover-<?php esc_attr_e( get_post_meta( $c_form->ID, 'vmwpmdp_btn_hover_animation', true ) ) ?>">

				<a class="vmwpmdp-stop-rec-btn"><span><?php esc_html_e( $vmwpmdp_btn_stop); ?></span></a>

				<a class="vmwpmdp-reset-rec-btn"><span><?php esc_html_e( $vmwpmdp_btn_pause); ?></span></a>

			</div>

		</div>

		<?php

	}



	/**

	 * Render Microphone access error Step.

	 *

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_mic_access_err_box( $c_form ) {

		?>

        <div class="vmwpmdp-wpvoicemessage-mic-access-err-box">

			<?php echo wp_kses_post( \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_mic_access_err'] ); ?>

            <div class="vmwpmdp-speak-now-btns vmwpmdp-hover-<?php esc_attr_e( get_post_meta( $c_form->ID, 'vmwpmdp_btn_hover_animation', true ) ) ?>">

                <a class="vmwpmdp-mic-access-err-reload-btn"><span><?php esc_html_e( 'Try again', 'wpvoicemessage' ); ?></span></a>

            </div>

        </div>

		<?php

	}



	/**

	 * Render Allow access to microphone Step.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_allow_access_box() {

        ?>

        <div class="vmwpmdp-wpvoicemessage-allow-access-box">

            <?php echo wp_kses_post( \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_allow_access'] ); ?>

        </div>

        <?php

    }



    private function render_custom_content_start_box( $c_form, $content ) {



	    /** Get Start Box Settings. */

	    $vmwpmdp_before_txt = get_post_meta( $c_form->ID, 'vmwpmdp_before_txt', true );

	    $vmwpmdp_after_txt = get_post_meta( $c_form->ID, 'vmwpmdp_after_txt', true );



	    /** Default value. */

		if ( empty( $vmwpmdp_before_txt ) ) {

			$vmwpmdp_before_txt =\QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_before_txt'];

		}



		/** Empty field. */

		if ( ' ' === $vmwpmdp_before_txt ) {

			$vmwpmdp_before_txt = '';

		}



		/** Default value. */

		if ( empty( $vmwpmdp_after_txt ) ) {

			$vmwpmdp_after_txt =\QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_after_txt'];

		}



		/** Empty field. */

		if ( ' ' === $vmwpmdp_after_txt ) {

			$vmwpmdp_after_txt = '';

		}



	    ?>

        <div class="vmwpmdp-wpvoicemessage-start-box">

		    <?php if ( $vmwpmdp_before_txt ) : ?>

                <div class="vmwpmdp-wpvoicemessage-before-txt"><?php echo wp_kses_post( $vmwpmdp_before_txt ); ?></div>

		    <?php endif; ?>



            <div class="vmwpmdp-wpvoicemessage-start-btn-box">

                <div class="vmwpmdp-wpvoicemessage-start-btn vmwpmdp-wpvoicemessage-custom">

                    <?php echo wp_kses_post( $content ); ?>

                </div>

            </div>



		    <?php if ( $vmwpmdp_after_txt ) : ?>

                <div class="vmwpmdp-wpvoicemessage-after-txt"><?php echo wp_kses_post( $vmwpmdp_after_txt ); ?></div>

		    <?php endif; ?>

        </div>

	    <?php



    }



	/**

	 * Render Start Recording Step.

	 *

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function render_start_box( $c_form ) {



		/** Get Start Box Settings. */

		$vmwpmdp_before_txt = get_post_meta( $c_form->ID, 'vmwpmdp_before_txt', true );

		$vmwpmdp_after_txt = get_post_meta( $c_form->ID, 'vmwpmdp_after_txt', true );

		$vmwpmdp_btn_caption = get_post_meta( $c_form->ID, 'vmwpmdp_btn_caption', true );
		$vmwpmdp_btn_pause = get_post_meta( $c_form->ID, 'vmwpmdp_btn_pause', true );
		$vmwpmdp_btn_stop = get_post_meta( $c_form->ID, 'vmwpmdp_btn_stop', true );
		$vmwpmdp_btn_reset = get_post_meta( $c_form->ID, 'vmwpmdp_btn_reset', true );
		$vmwpmdp_btn_save = get_post_meta( $c_form->ID, 'vmwpmdp_btn_save', true );

		$vmwpmdp_start_new_message_caption = get_post_meta( $c_form->ID, 'vmwpmdp_start_new_message_caption', true );
		
		$vmwpmdp_btn_icon = get_post_meta( $c_form->ID, 'vmwpmdp_btn_icon', true );

		$vmwpmdp_btn_icon_position = get_post_meta( $c_form->ID, 'vmwpmdp_btn_icon_position', true );

		$vmwpmdp_btn_hover_animation = get_post_meta( $c_form->ID, 'vmwpmdp_btn_hover_animation', true );



		/** Default value. */

		if ( empty( $vmwpmdp_before_txt ) ) {

			$vmwpmdp_before_txt =\QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_before_txt'];

		}



		/** Empty field. */

		if ( ' ' === $vmwpmdp_before_txt ) {

			$vmwpmdp_before_txt = '';

		}



		/** Default value. */

		if ( empty( $vmwpmdp_after_txt ) ) {

			$vmwpmdp_after_txt =\QuantumCloud\QCvoicemssg\Settings::get_instance()->options['msg_after_txt'];

		}



		/** Empty field. */

		if ( ' ' === $vmwpmdp_after_txt ) {

			$vmwpmdp_after_txt = '';

		}



		/** Default value. */

		if ( $vmwpmdp_btn_caption === '' ) {

			$vmwpmdp_btn_caption = esc_html__( 'Record', 'wpvoicemessage' );

		}
		if ( $vmwpmdp_btn_save === '' ) {
			$vmwpmdp_btn_save = esc_html__( 'Save', 'wpvoicemessage' );
		}
		if ( $vmwpmdp_btn_pause === '' ) {

			$vmwpmdp_btn_pause = esc_html__( 'Pause', 'wpvoicemessage' );

		}
		if ( $vmwpmdp_btn_reset === '' ) {

			$vmwpmdp_btn_reset = esc_html__( 'Reset', 'wpvoicemessage' );

		}
		if ( $vmwpmdp_btn_stop === '' ) {

			$vmwpmdp_btn_stop = esc_html__( 'Stop', 'wpvoicemessage' );

		}
		

		

		/** Default value. */

		if ( '' === $vmwpmdp_btn_icon ) {

			$vmwpmdp_btn_icon = '_voicemssg/microphone.svg';

		}



		/** We use this to detect empty icon and first time loading. */

		if ( ' ' === $vmwpmdp_btn_icon ) {

			$vmwpmdp_btn_icon = '';

		}



		/** Default value. */

		if ( $vmwpmdp_btn_icon_position === '' ) {

			$vmwpmdp_btn_icon_position = 'before';

		}

		// echo QuantumCloud\wpvoicemessage::url;

		?>

		<div class="vmwpmdp-wpvoicemessage-start-box">

			<?php if ( $vmwpmdp_before_txt ) : ?>

				<div class="vmwpmdp-wpvoicemessage-before-txt"><?php echo wp_kses_post( $vmwpmdp_before_txt ); ?></div>

			<?php endif; ?>



			<div class="vmwpmdp-wpvoicemessage-start-btn-box vmwpmdp-hover-<?php esc_attr_e( $vmwpmdp_btn_hover_animation ); ?>">

				<a class="vmwpmdp-wpvoicemessage-start-btn vmwpmdp-icon-position-<?php esc_attr_e( $vmwpmdp_btn_icon_position ); ?>">



					<?php if ( $vmwpmdp_btn_icon_position !== 'none' ) : ?>

						<span class="vmwpmdp-wpvoicemessage-start-btn-icon"><?php \QuantumCloud\QCvoicemssg\Helper::get_instance()->inline_svg_e( $vmwpmdp_btn_icon ); ?></span>

					<?php endif; ?>



					<?php if ( $vmwpmdp_btn_caption ) : ?>

						<span class="vmwpmdp-wpvoicemessage-start-btn-caption"><?php echo wp_kses_post( $vmwpmdp_btn_caption ); ?></span>

					<?php endif; ?>



				</a>

			</div>



			<?php if ( $vmwpmdp_after_txt ) : ?>

				<div class="vmwpmdp-wpvoicemessage-after-txt"><?php echo wp_kses_post( $vmwpmdp_after_txt ); ?></div>

			<?php endif; ?>

		</div>

		<?php



	}



	/**

	 * Enqueue styles and scripts only if shortcode used on this page.

	 *

	 * @param string $id - current shortcode id.

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return void

	 **/

	private function enqueue( $id, $c_form ) {



		/** Enqueue styles only if shortcode used on this page. */

		wp_enqueue_style( 'vmwpmdp-wpvoicemessage' );



		/** Enqueue JavaScript only if shortcode used on this page. */

		wp_enqueue_script( 'vmwpmdp-wpvoicemessage-recorder' );

		wp_enqueue_script( 'vmwpmdp-wpvoicemessage' );



		/** Get inline CSS for current shortcode. */

		$css = $this->get_shortcode_inline_css( $id, $c_form );



		/** Add inline styles.. */

		wp_add_inline_style( 'vmwpmdp-wpvoicemessage', $css );



	}



	/**

	 * Return inline css styles for current shortcode.

	 *

	 * @param string $id - current shortcode id.

	 * @param object $c_form - current QCvoicemssg Form.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return string - inline css styles.

	 */

	private function get_shortcode_inline_css( $id, $c_form ) {
		/** Get QCvoicemssg Form params. */
		$btn_caption = get_post_meta( $c_form->ID, 'vmwpmdp_btn_caption', true );
		$btn_margin = get_post_meta( $c_form->ID, 'vmwpmdp_btn_margin', true );
		$btn_padding = get_post_meta( $c_form->ID, 'vmwpmdp_btn_padding', true );
		$btn_radius = get_post_meta( $c_form->ID, 'vmwpmdp_btn_radius', true );
		$btn_color = get_post_meta( $c_form->ID, 'vmwpmdp_btn_color', true );
		$btn_color_hover = get_post_meta( $c_form->ID, 'vmwpmdp_btn_color_hover', true );
		$btn_bg_color = get_post_meta( $c_form->ID, 'vmwpmdp_btn_bg_color', true );
		$btn_bg_color_hover = get_post_meta( $c_form->ID, 'vmwpmdp_btn_bg_color_hover', true );
		$btn_size = get_post_meta( $c_form->ID, 'vmwpmdp_btn_size', true );

		/** Default value. */
		if ( $btn_caption === '' ) {
			$btn_caption = esc_html__( 'Record', 'wpvoicemessage' );
		}

		/** Default value. */
		if ( $btn_margin === '' ) {
			$btn_margin = '10';
		}

		/** Default value. */
		if ( $btn_radius === '' ) {
			$btn_radius = '50';
		}

		/** Default value. */
		if ( $btn_padding === '' ) {
			$btn_padding = '20';
		}

		/** Default value. */
		if ( $btn_color === '' ) {
			$btn_color = '#fff';
		}

		/** Default value. */
		if ( $btn_color_hover === '' ) {
			$btn_color_hover = '#0274e6';
		}

		/** Default value. */
		if ( $btn_bg_color === '' ) {
			$btn_bg_color = '#0274e6';
		}

		/** Default value. */
		if ( $btn_bg_color_hover === '' ) {
			$btn_bg_color_hover = '#fff';
		}

		/** Default value. */
		if ( $btn_size === '' ) {
			$btn_size = '18';
		}

		/** If a have caption make it rectangle. */
		if ( $btn_caption ) {
			$btn_padding = "calc({$btn_padding}px / 2) {$btn_padding}px";
		/** Else make it square. */
		} else {
			$btn_padding = "{$btn_padding}px";
		}

		// language=CSS
		/** @noinspection CssUnusedSymbol */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$css = "

		    #{$id} .vmwpmdp-wpvoicemessage-start-btn:not(.vmwpmdp-wpvoicemessage-custom),

		    #{$id} .vmwpmdp-speak-now-btns a,

		    #{$id} .vmwpmdp-wpvoicemessage-thanks-box a,

		    #{$id} .vmwpmdp-send-btns a {

		        margin: {$btn_margin}px;

		        padding: {$btn_padding};

                border-radius: {$btn_radius}px;

                color: {$btn_color};

                background: {$btn_bg_color}; 

		    }

		    

		    #{$id} .vmwpmdp-wpvoicemessage-start-btn:not(.vmwpmdp-wpvoicemessage-custom):hover,

		    #{$id} .vmwpmdp-wpvoicemessage-start-btn:not(.vmwpmdp-wpvoicemessage-custom):hover svg,

		    #{$id} .vmwpmdp-speak-now-btns a:hover,

		    #{$id} .vmwpmdp-wpvoicemessage-thanks-box a:hover,

		    #{$id} .vmwpmdp-send-btns a:hover {

                fill: {$btn_color_hover};

                color: {$btn_color_hover};

                background: {$btn_bg_color_hover}; 

		    }

		    

		    #{$id} .vmwpmdp-wpvoicemessage-start-btn.vmwpmdp-wpvoicemessage-custom { cursor: pointer; }

		    

		    #{$id} .vmwpmdp-wpvoicemessage-start-btn-icon svg {

		        width: {$btn_size}px;

                height: {$btn_size}px;

                fill: {$btn_color};

		    }

		    

		    #{$id} .vmwpmdp-wpvoicemessage-start-btn .vmwpmdp-wpvoicemessage-start-btn-caption {

		        font-size: {$btn_size}px;

		        line-height: 1.3;

		    }



		";



		return $css;

	}



	/**

	 * Return QCvoicemssg Form object.

	 *

	 * @param array $atts - Shortcodes attributes.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return object - QCvoicemssg Form

	 **/

	private function get_cform( $atts ) {



		/** Get QCvoicemssg form data. */

		if ( ! empty( $atts['id'] ) ) {

			$c_form = get_post( $atts['id'] );

		} else {

			$c_form = get_page_by_title( $atts['title'], 'OBJECT', \QuantumCloud\QCvoicemssg\VoiceMessageForm::POST_TYPE );

		}



		return $c_form;

	}



	/**

	 * Return unique id for current shortcode.

	 *

	 * @param array $atts - Shortcodes attributes.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return string

	 **/

	private function get_shortcode_id( $atts ) {



		/** $call_count will be initialized on the first time call. */

		static $call_count = 0;



		/** call_count will be incremented each time the method gets called. */

		$call_count ++;



		return 'vmwpmdp-wpvoicemessage-' . md5( json_encode( $atts ) ) . '-' . $call_count;



	}



	/**

	 * Replace {timer} {max-duration} {countdown} placeholders.

	 *

	 * @param string $message - Content for Speak Now step.

	 *

	 * @since  1.0.0

	 * @access public

	 * @return string

	 **/

	private function replace_placeholders( $message ) {



		/** Replace {timer}. */

		$timer   = '<p class="vmwpmdp-wpvoicemessage-recording-timer">00:00</p>';

		$message = str_replace( '{timer}', $timer, $message );



		/** Replace {countdown}. */

		$countdown   = '<p class="vmwpmdp-wpvoicemessage-recording-countdown">00:00</p>';

		$message = str_replace( '{countdown}', $countdown, $message );



		/** Replace {max-duration}. */

		$max_duration = \QuantumCloud\QCvoicemssg\Settings::get_instance()->options['max_duration'];

        if ( '0' === $max_duration ) { $max_duration = '∞'; }

		$max_duration_msg =

			'<p class="vmwpmdp-wpvoicemessage-recording-max-duration">' .

                esc_html__( 'Max duration', 'wpvoicemessage' ) .

				' <strong>' . $max_duration . '</strong> '.

				esc_html__( 'seconds', 'wpvoicemessage' ) .

            '</p>';

		$message          = str_replace( '{max-duration}', $max_duration_msg, $message );



		return $message;

	}



	/**

	 * Main Shortcodes Instance.

	 *

	 * Insures that only one instance of Shortcodes exists in memory at any one time.

	 *

	 * @static

	 * @return Shortcodes

	 * @since 1.0.0

	 **/

	public static function get_instance() {



		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {



			self::$instance = new self;



		}



		return self::$instance;



	}



} // End Class Shortcodes.



QcCF7VoiceMessageShortcodes::get_instance();