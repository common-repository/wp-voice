<?php
namespace QuantumCloud\QCvoicemssg;

use QuantumCloud\wpvoicemessage;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class used to add voicemssg_form post type.
 *
 * @since 1.0.0
 **/
final class CFOptionsMetaBox {

	/**
	 * The one true CFOptionsMetaBox.
	 *
	 * @var CFOptionsMetaBox
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

	}

	/**
	 * Render "Options" metabox with all fields.
	 *
	 * @param $voicemssg_form - Post Object.
	 *
	 * @since 1.0.0
	 **/
	public function render_metabox( $voicemssg_form ) {

		/** Render Nonce field to validate on save. */
		$this->render_nonce();

		?>
		<div class="vmwpmdp-options-box">
			<table class="form-table">
				<tbody>
				<?php

				/** Render Before text field. */
				$this->before_text( $voicemssg_form );

				/** Render "Start recording" Button. */
				// $this->start_recording( $voicemssg_form );
				/** Button Caption. */
                $this->btn_caption( $voicemssg_form );
			//	$this->btn_save( $voicemssg_form );
				$this->btn_pause($voicemssg_form);
				$this->btn_reset($voicemssg_form);
			//	$this->btn_resume($voicemssg_form);
				$this->btn_stop($voicemssg_form);
				/** Render After text field. */
				$this->after_text( $voicemssg_form );

				/** Render Speak Now text field. */
				$this->speak_now_text( $voicemssg_form );

				/** Render Send recording text field. */
				$this->send_text( $voicemssg_form );

				/** Render Thank you text field. */
				$this->thanks_text( $voicemssg_form );

				/** Render Additional Fields. */
				//$this->additional_fields( $voicemssg_form );


				?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save "Options" metabox with all fields.
	 *
	 * @param $post_id - Post Object.
	 * @since 1.0.0
	 **/
	public function save_metabox( $post_id ) {

		/** Options fields keys. */
		$k = [
			'vmwpmdp_before_txt', // Before Text.
			'vmwpmdp_align', // Align.
			'vmwpmdp_btn_margin', // Margin.
			'vmwpmdp_btn_padding', // Padding.
            'vmwpmdp_btn_radius', // Radius.
			'vmwpmdp_btn_icon', // Icon.
			'vmwpmdp_btn_icon_position', // Icon Position.
            'vmwpmdp_btn_caption', // Caption
			'vmwpmdp_btn_resume', // resume
			'vmwpmdp_btn_pause',
			'vmwpmdp_btn_reset',
			'vmwpmdp_btn_stop',
			'vmwpmdp_btn_save',
			'vmwpmdp_start_new_message_caption',
            'vmwpmdp_btn_size', // Size
            'vmwpmdp_btn_color', // Text/Icon color.
            'vmwpmdp_btn_color_hover', // Text/Icon Hover color.
            'vmwpmdp_btn_bg_color', // Background color.
            'vmwpmdp_btn_bg_color_hover', // Background Hover color.
			'vmwpmdp_btn_hover_animation', // Hover Animations.
            'vmwpmdp_after_txt', // After Text.
			'vmwpmdp_speak_now_txt', // Speak Now message.
            'vmwpmdp_send_txt', // Send Recording message.
            'vmwpmdp_thanks_txt', // Thank You message.
            'vmwpmdp_additional_fields', // Additional Fields.
			'vmwpmdp_additional_fields_fb', // Form Builder fields.
			'vmwpmdp_additional_fields_res',
        ];

		/** Save each field. */
		foreach ( $k as $field ) {
			$value = ( isset( $_POST[$field] ) ? wp_kses_post( $_POST[$field] ) : '' );

			if ( in_array( $field, ['vmwpmdp_before_txt', 'vmwpmdp_after_txt', 'vmwpmdp_speak_now_txt', 'vmwpmdp_send_txt', 'vmwpmdp_thanks_txt'] ) AND $value === '' ) {
				$value = ' ';
			}

			update_post_meta( $post_id, $field, $value );
        }

    }

	/**
	 * Render Before text field.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function before_text( $voicemssg_form ) {

		/** Get Before Text field value from meta if it's already been entered. */
		$before_txt = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_before_txt', true );

		/** Default value. */
		if ( empty( $before_txt ) ) {
			$before_txt = Settings::get_instance()->options['msg_before_txt'];
		}

		/** Empty field. */
		if ( ' ' === $before_txt ) {
			$before_txt = '';
		}

		?>
		<tr>
			<th scope="row">
				<label for="vmwpmdpbeforetxt"><?php esc_html_e( 'Before Text:', 'wpvoicemessage' ); ?></label>
			</th>
			<td>
				<?php wp_editor( $before_txt, 'vmwpmdpbeforetxt', ['textarea_rows' => 5, 'textarea_name' => 'vmwpmdp_before_txt'] ); ?>
				<p class="description"><?php esc_html_e( 'Enter text before "Start recording" button or leave blank to do not use the field.', 'wpvoicemessage' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render Start Recording button fieldset.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function start_recording( $voicemssg_form ) {
		?>
        <tr>
            <th scope="row">
                <label for="vmwpmdpaftertxt"><?php esc_html_e( 'Start recording button:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
               <fieldset class="vmwpmdp-wpvoicemessage-start-btn-box">
                    <?php

                    /** Render Align. */
                    $this->align( $voicemssg_form );

                    /** Render Margin. */
                    $this->btn_margin( $voicemssg_form );

                    /** Render Padding. */
                    $this->btn_padding( $voicemssg_form );

                    /** Border Radius Padding. */
                    $this->btn_radius( $voicemssg_form );

                    /** Button Icon. */
                    $this->btn_icon( $voicemssg_form );

                    /** Button Icon Position. */
                    $this->btn_icon_position( $voicemssg_form );

                    /** Button Caption. */
                    $this->btn_caption( $voicemssg_form );

					$this->btn_save( $voicemssg_form );
					$this->btn_stop($voicemssg_form);
					$this->btn_reset($voicemssg_form);
					$this->btn_pause($voicemssg_form);
                    /** Icon/Font size. */
                    $this->btn_size( $voicemssg_form );

                    /** Button Text Color. */
                    $this->btn_color( $voicemssg_form );

                    /** Button Background Color. */
                    $this->btn_bg_color( $voicemssg_form );

                    /** Button Text Hover Color. */
                    $this->btn_color_hover( $voicemssg_form );

                    /** Button Background Color. */
                    $this->btn_bg_color_hover( $voicemssg_form );

                    /** Button Hover Animations. */
                    $this->btn_hover_animation( $voicemssg_form );

                    ?>
               </fieldset>
                <p class="description"><?php esc_html_e( 'Customize the Look & Feel of the "Start recording" button.', 'wpvoicemessage' ); ?></p>
            </td>
        </tr>
		<?php
    }

	/**
	 * Render Margin slider for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_margin( $voicemssg_form ) {

		/** Get Margin value from meta if it's already been entered. */
		$vmwpmdp_btn_margin = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_margin', true );

		/** Default value. */
		if ( $vmwpmdp_btn_margin === '' ) {
			$vmwpmdp_btn_margin = '10';
		}

        ?>
        <div class="vmwpmdp-control-field">
            <?php
            /** Margin slider. */
            UI::get_instance()->render_slider(
	            $vmwpmdp_btn_margin,
	            0,
	            100,
	            1,
	            esc_html__( 'Button Margin', 'wpvoicemessage' ),
	            esc_html__( 'Button margin: ', 'wpvoicemessage' ) .
	            '<strong>' . $vmwpmdp_btn_margin . '</strong>' .
	            esc_html__( ' px', 'wpvoicemessage' ),
	            [
		            'name' => 'vmwpmdp_btn_margin',
		            'id' => 'vmwpmdp_btn_margin',
	            ]
            );
            ?>
        </div>
        <?php
    }

	/**
	 * Render Icon/Caption size for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_size( $voicemssg_form ) {

		/** Get size value from meta if it's already been entered. */
		$vmwpmdp_btn_size = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_size', true );

		/** Default value. */
		if ( $vmwpmdp_btn_size === '' ) {
			$vmwpmdp_btn_size = '18';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Icon/Caption size slider. */
			UI::get_instance()->render_slider(
				$vmwpmdp_btn_size,
				10,
				100,
				1,
				esc_html__( 'Size', 'wpvoicemessage' ),
				esc_html__( 'Icon/Caption size: ', 'wpvoicemessage' ) .
				'<strong>' . $vmwpmdp_btn_size . '</strong>' .
				esc_html__( ' px', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_size',
					'id' => 'vmwpmdp_btn_size',
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Padding slider for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_padding( $voicemssg_form ) {

		/** Get Padding value from meta if it's already been entered. */
		$vmwpmdp_btn_padding = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_padding', true );

		/** Default value. */
		if ( $vmwpmdp_btn_padding === '' ) {
			$vmwpmdp_btn_padding = '20';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Padding slider. */
			UI::get_instance()->render_slider(
				$vmwpmdp_btn_padding,
				0,
				100,
				1,
				esc_html__( 'Button Padding', 'wpvoicemessage' ),
				esc_html__( 'Button padding: ', 'wpvoicemessage' ) .
				'<strong>' . $vmwpmdp_btn_padding . '</strong>' .
				esc_html__( ' px', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_padding',
					'id' => 'vmwpmdp_btn_padding',
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Border Radius slider for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_radius( $voicemssg_form ) {

		/** Get Radius value from meta if it's already been entered. */
		$vmwpmdp_btn_radius = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_radius', true );

		/** Default value. */
		if ( $vmwpmdp_btn_radius === '' ) {
			$vmwpmdp_btn_radius = '50';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Radius slider. */
			UI::get_instance()->render_slider(
				$vmwpmdp_btn_radius,
				0,
				100,
				1,
				esc_html__( 'Button Radius', 'wpvoicemessage' ),
				esc_html__( 'Button radius: ', 'wpvoicemessage' ) .
				'<strong>' . $vmwpmdp_btn_radius . '</strong>' .
				esc_html__( ' px', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_radius',
					'id' => 'vmwpmdp_btn_radius',
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Button Icon for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_icon( $voicemssg_form ) {

		/** Get icon value from meta if it's already been entered. */
		$vmwpmdp_btn_icon = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_icon', true );

		/** Default value. */
		if ( '' === $vmwpmdp_btn_icon ) {
			$vmwpmdp_btn_icon = '_voicemssg/waves.svg';
		}

		/** We use this to detect empty icon and first time loading. */
		if ( ' ' === $vmwpmdp_btn_icon ) {
			$vmwpmdp_btn_icon = '';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Button icon icon. */
			UI::get_instance()->render_icon(
				$vmwpmdp_btn_icon,
				'',
				esc_html__( 'Select icon for button', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_icon',
					'id' => 'vmwpmdp_btn_icon'
				],
				[
					'_voicemssg.json',
					'font-awesome.json',
					'material.json',
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Align for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function align( $voicemssg_form ) {

		/** Get align value from meta if it's already been entered. */
		$vmwpmdp_align = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_align', true );

		/** Default value. */
		if ( $vmwpmdp_align === '' ) {
			$vmwpmdp_align = 'left';
		}

		/** Align options. */
		$options = [
			'none'      => esc_html__( 'None', 'wpvoicemessage' ),
			'left'      => esc_html__( 'Left', 'wpvoicemessage' ),
			'center'    => esc_html__( 'Center', 'wpvoicemessage' ),
			'right'     => esc_html__( 'Right', 'wpvoicemessage' ),
		];

		?><div class="vmwpmdp-control-field"><?php

		/** Render Align dropdown. */
		UI::get_instance()->render_select(
			$options,
			$vmwpmdp_align, // Selected option.
			esc_html__( 'Align', 'wpvoicemessage' ),
			esc_html__( 'Choose how to align the button and other form elements.', 'wpvoicemessage' ),
			[
				'name' => 'vmwpmdp_align',
				'id' => 'vmwpmdp_align'
			]
		);

		?></div><?php

	}

	/**
	 * Render Button Icon Position for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_icon_position( $voicemssg_form ) {

		/** Get icon position value from meta if it's already been entered. */
		$vmwpmdp_btn_icon_position = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_icon_position', true );

		/** Default value. */
		if ( $vmwpmdp_btn_icon_position === '' ) {
			$vmwpmdp_btn_icon_position = 'before';
		}

		/** Icon Position options. */
		$options = [
			'none'   => esc_html__( 'Hide', 'wpvoicemessage' ),
			'before' => esc_html__( 'Before', 'wpvoicemessage' ),
			'after'  => esc_html__( 'After', 'wpvoicemessage' ),
			'above'  => esc_html__( 'Above', 'wpvoicemessage' ),
			'bellow' => esc_html__( 'Bellow', 'wpvoicemessage' ),
		];

		?><div class="vmwpmdp-control-field"><?php

		/** Render Icon Position dropdown. */
		UI::get_instance()->render_select(
			$options,
			$vmwpmdp_btn_icon_position, // Selected option.
			esc_html__( 'Icon Position', 'wpvoicemessage' ),
			esc_html__( 'Position of the icon inside the button', 'wpvoicemessage' ),
			[
				'name' => 'vmwpmdp_btn_icon_position',
				'id' => 'vmwpmdp_btn_icon_position'
			]
		);

        ?></div><?php

    }

	/**
	 * Render Hover Animations for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_hover_animation( $voicemssg_form ) {

		/** Get hover animation value from meta if it's already been entered. */
		$vmwpmdp_btn_hover_animation = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_hover_animation', true );

		/** Default value. */
		if ( $vmwpmdp_btn_hover_animation === '' ) {
			$vmwpmdp_btn_hover_animation = 'none';
		}

		/** Hover Animations options. */
		$options = [
			'none'                  => esc_html__( 'None', 'wpvoicemessage' ),
			'fade'                  => esc_html__( 'Fade', 'wpvoicemessage' ),
			'bounce'                => esc_html__( 'Bounce', 'wpvoicemessage' ),
			'flip-x'                => esc_html__( 'Flip X', 'wpvoicemessage' ),
			'flip-y'                => esc_html__( 'Flip Y', 'wpvoicemessage' ),
			'scale'                 => esc_html__( 'Scale', 'wpvoicemessage' ),
			'wobble'                => esc_html__( 'Wobble', 'wpvoicemessage' ),
			'rotate'                => esc_html__( 'Rotate', 'wpvoicemessage' )
		];

		?><div class="vmwpmdp-control-field"><?php

		/** Render Hover Animations dropdown. */
		UI::get_instance()->render_select(
			$options,
			$vmwpmdp_btn_hover_animation, // Selected option.
			esc_html__( 'Hover animation', 'wpvoicemessage' ),
			esc_html__( 'Button hover animation', 'wpvoicemessage' ),
			[
				'name' => 'vmwpmdp_btn_hover_animation',
				'id' => 'vmwpmdp_btn_hover_animation'
			]
		);

		?></div><?php

	}

	
	private function btn_save( $voicemssg_form){
		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_save = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_save', true );

		/** Default value. */
		if ( $vmwpmdp_btn_save === '' ) {
			$vmwpmdp_btn_save = esc_html__( 'Save', 'wpvoicemessage' );
		}

		?>
		<tr>
			<th>Button caption Save</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_btn_save,
						esc_html__( 'Save', 'wpvoicemessage' ),
						esc_html__( 'Start save button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_btn_save',
							'id' => 'vmwpmdp_btn_save'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<?php
	}

	private function btn_stop($voicemssg_form){
		
		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_stop = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_stop', true );

		/** Default value. */
		if ( $vmwpmdp_btn_stop === '' ) {
			$vmwpmdp_btn_stop = esc_html__( 'Stop', 'wpvoicemessage' );
		}

		?>
		<tr>
			<th>Button caption Stop</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_btn_stop,
						esc_html__( 'Stop', 'wpvoicemessage' ),
						esc_html__( 'Start stop button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_btn_stop',
							'id' => 'vmwpmdp_btn_stop'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<?php

	}
	private function btn_reset( $voicemssg_form){
		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_reset = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_reset', true );

		/** Default value. */
		if ( $vmwpmdp_btn_reset === '' ) {
			$vmwpmdp_btn_reset = esc_html__( 'Reset', 'wpvoicemessage' );
		}

		?>
		<tr>
			<th>Button caption Reset</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_btn_reset,
						esc_html__( 'Reset', 'wpvoicemessage' ),
						esc_html__( 'Start reset button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_btn_reset',
							'id' => 'vmwpmdp_btn_reset'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<?php
	}
	private function btn_pause( $voicemssg_form){
		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_pause = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_pause', true );

		/** Default value. */
		if ( $vmwpmdp_btn_pause === '' ) {
			$vmwpmdp_btn_pause = esc_html__( 'Pause', 'wpvoicemessage' );
		}

		?>
		<tr>
			<th>Button caption Pause</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_btn_pause,
						esc_html__( 'Pause', 'wpvoicemessage' ),
						esc_html__( 'Start pause button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_btn_pause',
							'id' => 'vmwpmdp_btn_pause'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<?php
	}
	private function btn_resume( $voicemssg_form){
		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_resume = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_resume', true );

		/** Default value. */
		if ( $vmwpmdp_btn_resume === '' ) {
			$vmwpmdp_btn_resume = esc_html__( 'Resume', 'wpvoicemessage' );
		}

		?>
		<tr>
			<th>Button caption Resume</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_btn_resume,
						esc_html__( 'Resume', 'wpvoicemessage' ),
						esc_html__( 'Start resume button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_btn_resume',
							'id' => 'vmwpmdp_btn_resume'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<?php
	}
	/**
	 * Render Caption filed for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_caption( $voicemssg_form ) {

		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_caption = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_caption', true );
		$vmwpmdp_start_new_message_caption = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_start_new_message_caption', true );
		/** Default value. */
		if ( $vmwpmdp_btn_caption === '' ) {
			$vmwpmdp_btn_caption = esc_html__( 'Record', 'wpvoicemessage' );
		}
		if ( $vmwpmdp_start_new_message_caption === '' ) {
			$vmwpmdp_start_new_message_caption = esc_html__( 'Start a new Message', 'wpvoicemessage' );
		}
		?>
		<tr>
			<th>Record</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_btn_caption,
						esc_html__( 'Caption', 'wpvoicemessage' ),
						esc_html__( 'Start record button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_btn_caption',
							'id' => 'vmwpmdp_btn_caption'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<tr>
			<th>Start a new Message</th>
			<td>
		        <div class="vmwpmdp-control-field">
					<?php
					/** Caption input. */
					UI::get_instance()->render_input(
						$vmwpmdp_start_new_message_caption,
						esc_html__( 'Caption', 'wpvoicemessage' ),
						esc_html__( 'Start a new Message button caption', 'wpvoicemessage' ),
						[
							'name' => 'vmwpmdp_start_new_message_caption',
							'id' => 'vmwpmdp_start_new_message_caption'
						]
					);
					?>
		        </div>
		    </td>
    	</tr>
		<?php
	}

	/**
	 * Render Text/Icon color for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_color( $voicemssg_form ) {

		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_color = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_color', true );

		/** Default value. */
		if ( $vmwpmdp_btn_color === '' ) {
			$vmwpmdp_btn_color = '#fff';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Text/Icon Color colorpicker. */
			UI::get_instance()->render_colorpicker(
				$vmwpmdp_btn_color,
				esc_html__( 'Text Color', 'wpvoicemessage' ),
				esc_html__( 'Select icon and text color', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_color',
					'id' => 'vmwpmdp_btn_color',
					'readonly' => 'readonly'
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Text/Icon Hover color for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_color_hover( $voicemssg_form ) {

		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_color_hover = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_color_hover', true );

		/** Default value. */
		if ( $vmwpmdp_btn_color_hover === '' ) {
			$vmwpmdp_btn_color_hover = '#0274e6';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Text/Icon Color colorpicker. */
			UI::get_instance()->render_colorpicker(
				$vmwpmdp_btn_color_hover,
				esc_html__( 'Text Hover Color', 'wpvoicemessage' ),
				esc_html__( 'Select icon and text hover color', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_color_hover',
					'id' => 'vmwpmdp_btn_color_hover',
					'readonly' => 'readonly'
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Background color for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_bg_color( $voicemssg_form ) {

		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_bg_color = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_bg_color', true );

		/** Default value. */
		if ( $vmwpmdp_btn_bg_color === '' ) {
			$vmwpmdp_btn_bg_color = '#0274e6';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Text/Icon Color colorpicker. */
			UI::get_instance()->render_colorpicker(
				$vmwpmdp_btn_bg_color,
				esc_html__( 'Background Color', 'wpvoicemessage' ),
				esc_html__( 'Select button background color', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_bg_color',
					'id' => 'vmwpmdp_btn_bg_color',
					'readonly' => 'readonly'
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render Background Hover color for Start button.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function btn_bg_color_hover( $voicemssg_form ) {

		/** Get Caption value from meta if it's already been entered. */
		$vmwpmdp_btn_bg_color_hover = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_btn_bg_color_hover', true );

		/** Default value. */
		if ( $vmwpmdp_btn_bg_color_hover === '' ) {
			$vmwpmdp_btn_bg_color_hover = '#fff';
		}

		?>
        <div class="vmwpmdp-control-field">
			<?php
			/** Text/Icon Color colorpicker. */
			UI::get_instance()->render_colorpicker(
				$vmwpmdp_btn_bg_color_hover,
				esc_html__( 'Background Hover Color', 'wpvoicemessage' ),
				esc_html__( 'Select button background hover color', 'wpvoicemessage' ),
				[
					'name' => 'vmwpmdp_btn_bg_color_hover',
					'id' => 'vmwpmdp_btn_bg_color_hover',
					'readonly' => 'readonly'
				]
			);
			?>
        </div>
		<?php
	}

	/**
	 * Render After text field.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function after_text( $voicemssg_form ) {

		/** Get After Text field value from meta if it's already been entered. */
		$after_txt = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_after_txt', true );

		/** Default value. */
		if ( empty( $after_txt ) ) {
			$after_txt = Settings::get_instance()->options['msg_after_txt'];
		}

		/** Empty field. */
		if ( ' ' === $after_txt ) {
			$after_txt = '';
		}
		?>
        <tr>
            <th scope="row">
                <label for="vmwpmdpaftertxt"><?php esc_html_e( 'After Text:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
				<?php wp_editor( $after_txt, 'vmwpmdpaftertxt', ['textarea_rows' => 5, 'textarea_name' => 'vmwpmdp_after_txt'] ); ?>
                <p class="description"><?php esc_html_e( 'Enter text after "Start recording" button or leave blank to do not use the field.', 'wpvoicemessage' ); ?></p>
            </td>
        </tr>
		<?php
	}

	/**
	 * Render Thank You message field.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function thanks_text( $voicemssg_form ) {

		/** Get Thank you Text field value from meta if it's already been entered. */
		$thanks_txt = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_thanks_txt', true );

		/** Default value. */
		if ( empty( $thanks_txt ) ) {
			$thanks_txt = Settings::get_instance()->options['msg_thank_you'];
		}
		?>
        <tr>
            <th scope="row">
                <label><?php esc_html_e( 'Thank You Text:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
				<?php wp_editor( $thanks_txt, 'vmwpmdpthankstxt', ['textarea_rows' => 5, 'textarea_name' => 'vmwpmdp_thanks_txt'] ); ?>
            </td>
        </tr>
		<?php
	}

	/**
	 * Render Send recording field.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function send_text( $voicemssg_form ) {

		/** Get Send Text field value from meta if it's already been entered. */
		$send_txt = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_send_txt', true );

		/** Default value. */
		if ( empty( $send_txt ) ) {
			$send_txt = Settings::get_instance()->options['msg_send'];
		}
		?>
        <tr>
            <th scope="row">
                <label><?php esc_html_e( 'Send Recording Text:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
				<?php wp_editor( $send_txt, 'vmwpmdpsendtxt', ['textarea_rows' => 5, 'textarea_name' => 'vmwpmdp_send_txt'] ); ?>
            </td>
        </tr>
		<?php
	}

	/**
	 * Render Speak Now field.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function speak_now_text( $voicemssg_form ) {

		/** Get Speak Now Text field value from meta if it's already been entered. */
		$speak_now_txt = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_speak_now_txt', true );

		/** Default value. */
		if ( empty( $speak_now_txt ) ) {
			$speak_now_txt = Settings::get_instance()->options['msg_speak_now'];
		}
		?>
        <tr>
            <th scope="row">
                <label for="vmwpmdpaftertxt"><?php esc_html_e( 'Speak Now Text:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
				<?php wp_editor( $speak_now_txt, 'vmwpmdpspeaknowtxt', ['textarea_rows' => 5, 'textarea_name' => 'vmwpmdp_speak_now_txt'] ); ?>
                <p class="description"><?php esc_html_e( 'You can use special placeholders: {timer}, {max-duration}, {countdown}.', 'wpvoicemessage' ); ?></p>
            </td>
        </tr>
		<?php
	}

	/**
	 * Render Additional fields switcher.
	 *
	 * @param $voicemssg_form - Current QCvoicemssg Form Object.
	 *
	 * @since 1.0.0
	 **/
	private function additional_fields( $voicemssg_form ) {

		/** Get Additional fields switcher value from meta if it's already been entered. */
		$vmwpmdp_additional_fields = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_additional_fields', true );
		$vmwpmdp_additional_fields_fb = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_additional_fields_fb', true );
		$vmwpmdp_additional_fields_res = get_post_meta( $voicemssg_form->ID, 'vmwpmdp_additional_fields_res', true );

		/** Default value. Additional Fields switcher. */
		if ( '' === $vmwpmdp_additional_fields ) {
			$vmwpmdp_additional_fields = 'off';
		}

		/** Default value. Form in JSON. */
		if ( '' === $vmwpmdp_additional_fields_fb ) {
			$vmwpmdp_additional_fields_fb = '[{"type":"text","label":"First Name","placeholder":"Enter your first name","className":"vmwpmdp-form-control vmwpmdp-form-control-name","name":"vmwpmdp-wpvoicemessage-first-name","subtype":"text"},{"type":"text","subtype":"email","required":true,"label":"E-mail","placeholder":"Enter your e-mail","className":"vmwpmdp-form-control vmwpmdp-form-control-email","name":"vmwpmdp-wpvoicemessage-e-mail"}]';
		}

		/** Default value. Form in HTML. */
		if ( '' === $vmwpmdp_additional_fields_res ) {
			$vmwpmdp_additional_fields_res = '<div class="rendered-form"><div class="fb-text form-group field-first-name"><label for="first-name" class="fb-text-label">First Name</label></div><div class="fb-text form-group field-e-mail"><label for="e-mail" class="fb-text-label">E-mail<span class="fb-required">*</span></label></div></div>';
		}

		?>
        <tr>
            <th scope="row">
                <label for="vmwpmdpaftertxt"><?php esc_html_e( 'Additional fields:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
                <?php
                /** Render Additional fields switcher. */
                UI::get_instance()->render_switches(
	                $vmwpmdp_additional_fields,
	                esc_html__('Additional Fields', 'wpvoicemessage' ),
	                '',
	                [
		                'name' => 'vmwpmdp_additional_fields',
		                'id' => 'vmwpmdp_additional_fields'
	                ]
                );
                ?>
                <p class="description">
                	<?php esc_html_e( 'Show to user a small form after recording a voice message.', 'wpvoicemessage' ); ?>
                	<br />
                	<span style="color: red"><?php esc_html_e( 'Additional Fields will not work when voice message is used with Contact Form 7.', 'wpvoicemessage' ); ?></span>
                </p>

                <div class="vmwpmdp-form-builder-box">

                    <!--suppress HtmlFormInputWithoutLabel -->
                    <input name="vmwpmdp_additional_fields_fb"
                           type="hidden"
                           id="vmwpmdp_additional_fields_fb"
	                       value="<?php echo esc_attr_e( $vmwpmdp_additional_fields_fb ); ?>"
                    >
                    <!--suppress HtmlFormInputWithoutLabel -->
	                <input name="vmwpmdp_additional_fields_res"
	                       type="hidden"
	                       id="vmwpmdp_additional_fields_res"
	                       value="<?php esc_attr_e( $vmwpmdp_additional_fields_res ); ?>"
	                >
                    <div id="vmwpmdp-form-builder-editor"></div>

                </div>

            </td>
        </tr>
		<?php
	}

	/**
	 * Render Nonce field to validate form request came from current site.
	 *
	 * @since 1.0.0
	 **/
	private function render_nonce() {

		wp_nonce_field( wpvoicemessage::$basename, 'options_metabox_fields_nonce' );

	}

	/**
	 * Main CFOptionsMetaBox Instance.
	 *
	 * Insures that only one instance of CFOptionsMetaBox exists in memory at any one time.
	 *
	 * @static
	 * @return CFOptionsMetaBox
	 * @since 1.0.0
	 **/
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CFOptionsMetaBox ) ) {
			self::$instance = new CFOptionsMetaBox;
		}

		return self::$instance;
	}
	
} // End Class CFOptionsMetaBox.
