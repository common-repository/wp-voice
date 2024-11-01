<?php
/**
 * 
 */
class CF7VoiceMessageCreate
{
	public function __construct(){
		/** Add AJAX callback. */
		add_action( 'wp_ajax_cf7wpvoicemessage_send', [$this, 'cf7wpvoicemessage_send'] );
		add_action( 'wp_ajax_nopriv_cf7wpvoicemessage_send', [$this, 'cf7wpvoicemessage_send'] );
	}
	/**
	 * Process AJAX requests from frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function cf7wpvoicemessage_send() {

		/** Verifies the Ajax request to prevent processing requests external of the blog. */
		check_ajax_referer( 'wpvoicemessage-nonce', 'nonce' );

		/** Exit if no data to process. */
		if ( empty( $_POST ) ) { wp_die(); }
		$response = array();
		$response['status'] = 'fail';

		/** Get QCvoicemssg Form ID. */
		$cForm_id =  filter_input(INPUT_POST, 'cform-id', FILTER_SANITIZE_NUMBER_INT );

		/** Save Audio file. */
		$audio_file_path = $this->save_audio_file( $cForm_id );

		/** Create qcvoicemsg_record record. */
		$post_id = $this->create_record( $cForm_id, $audio_file_path );
		if( $post_id ){
			$response['status'] = 'ok';
			$response['post_id'] = $post_id;
		}

		/** Fire event to send email notification. */
		do_action( 'qcvoicemsg_record_added', $post_id );

		// echo 'ok';
		wp_send_json($response);

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
		$file_tmp_name = $_FILES['vmwpmdp-wpvoicemessage-audio']['tmp_name'];

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
}


new CF7VoiceMessageCreate();