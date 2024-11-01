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
final class CROptionsMetaBox {

	/**
	 * The one true CROptionsMetaBox.
	 *
	 * @var CROptionsMetaBox
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
	 * @param $qcvoicemsg_record - Post Object.
	 *
	 * @since 1.0.0
	 **/
	public function render_metabox( $qcvoicemsg_record ) {

		/** Render Nonce field to validate on save. */
		$this->render_nonce();

		?>
		<div class="vmwpmdp-options-box">
			<table class="form-table">
				<tbody>
				<?php

				/** Render Player field. */
				$this->render_player( $qcvoicemsg_record );

				

				?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save all metabox with all fields.
	 *
	 * @param $post_id - Post Object.
	 * @since 1.0.0
	 **/
	public function save_metabox( $post_id ) {

		/** Options fields keys. */
		$k = [
			'vmwpmdp_transcription_txt', // Transcription.
			'vmwpmdp_notes_txt', // Notes Text.
        ];

		/** Save each field. */
		foreach ( $k as $field ) {
			$value = ( isset( $_POST[$field] ) ? wp_kses_post( $_POST[$field] ) : '' );
			update_post_meta( $post_id, $field, $value );
        }

    }

	/**
	 * Render Player field.
	 *
	 * @param $qcvoicemsg_record - Current QCvoicemssg Record Object.
	 *
	 * @since 1.0.0
	 **/
	public function render_player( $qcvoicemsg_record ) {

	    /** Get Audio path value from meta if it's exist. */
		$audio_path = get_post_meta( $qcvoicemsg_record->ID, 'qcld_wpvm_vmwpmdp_voicemssg_audio', true );
		if ( empty( $audio_path ) ) { return; }

		?>
        <tr>
            <th scope="row">
                <label><?php esc_html_e( 'Voice Message:', 'wpvoicemessage' ); ?></label>
            </th>
            <td>
                <div>
                    <?php $audio_url = $this->abs_path_to_url( $audio_path ); ?>
		            <?php echo do_shortcode( '[audio src="' . $audio_url . '"]' ); ?>
                    <div class="vmwpmdp-wpvoicemessage-audio-info">
                        <span class="dashicons dashicons-download" title="<?php esc_html_e( 'Download audio', 'wpvoicemessage' ); ?>"></span>
                        <a href="<?php echo esc_url( $audio_url ); ?>" download=""><?php esc_html_e( 'Download audio', 'wpvoicemessage' ); ?></a>
                    </div>
                </div>
            </td>
        </tr>
		<?php

    }

	/**
	 * Return wav file duration 00:00.
     * @todo: sometimes returns incorrect time.
	 *
	 * @param string $file - Full Path to file.
	 * @since 1.0.0
	 * @return string|null
	 **/
	public function get_wav_duration( $file ) {

		$fp = fopen( $file, 'r' );

		if ( fread( $fp, 4 ) == "RIFF" ) {

			fseek( $fp, 20 );
			$raw_header = fread( $fp, 16 );

			/** @noinspection SpellCheckingInspection */
			$header = unpack( 'vtype/vchannels/Vsamplerate/Vbytespersec/valignment/vbits', $raw_header );
			$pos = ftell( $fp );

			while ( fread( $fp, 4 ) != "data" && ! feof( $fp ) ) {
				$pos ++;
				fseek( $fp, $pos );
			}

			$raw_header = fread( $fp, 4 );

			/** @noinspection SpellCheckingInspection */
			$data = unpack( 'Vdatasize', $raw_header );

			/** @noinspection SpellCheckingInspection */
			$sec = $data[ 'datasize' ] / $header[ 'bytespersec' ];

			$minutes = (int) ( ( $sec / 60 ) % 60 );
			$seconds = (int) ( $sec % 60 );

			return str_pad( $minutes, 2, "0", STR_PAD_LEFT ) . ":" . str_pad( $seconds, 2, "0", STR_PAD_LEFT );

		}

		return null;

	}

	/**
	 * Convert the file path to URL of the same file.
	 *
	 * @param string $path - Full Path to file.
	 * @since 1.0.0
	 * @return string
	 **/
	public function abs_path_to_url( $path = '' ) {

		$upload_dir = wp_get_upload_dir();
		$url = str_replace(
			wp_normalize_path( $upload_dir['basedir'] ),
			$upload_dir['baseurl'],
			wp_normalize_path( $path )
		);

		return esc_url_raw( $url );
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
	 * Main CROptionsMetaBox Instance.
	 *
	 * Insures that only one instance of CROptionsMetaBox exists in memory at any one time.
	 *
	 * @static
	 * @return CROptionsMetaBox
	 * @since 1.0.0
	 **/
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CROptionsMetaBox ) ) {
			self::$instance = new CROptionsMetaBox;
		}

		return self::$instance;
	}
	
} // End Class CROptionsMetaBox.
