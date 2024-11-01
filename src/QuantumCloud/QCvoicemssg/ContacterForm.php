<?php
namespace QuantumCloud\QCvoicemssg;

use Exception;
use QuantumCloud\wpvoicemessage;
use WP_Post;
use WP_Query;

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
final class ContacterForm {

	/**
	 * voicemssg_form Post Type name.
	 *
	 * @const POST_TYPE
	 * @since 1.0.0
	 **/
	const POST_TYPE = 'voicemssg_form_qcwp';

	/**
	 * The one true ContacterForm.
	 *
	 * @var ContacterForm
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

		/** Fire meta box setup on the post editor screen. */
		add_action( 'load-post.php', [ $this, 'meta_boxes_setup' ] );
		add_action( 'load-post-new.php', [ $this, 'meta_boxes_setup' ] );

		/** Add Shortcode and Messages columns to QCvoicemssg Form list. */

		/** Add mark for form that is used for the float button. */
		add_filter( 'display_post_states', [$this, 'fbutton_form_mark'], 10, 2 );

		/** You cannot delete the form used for the Floating Button. */
		add_action( 'wp_trash_post', [$this, 'before_delete_post'] );
		add_action( 'before_delete_post', [$this, 'before_delete_post'] );

		/** Add message "You can't delete the form used for the Floating Button." */
		add_action( 'admin_notices', [$this, 'delete_form_warning'] );

	}

	/**
	 * Add message "You can't delete the form used for the Floating Button."
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 **/
	public function delete_form_warning() {

	    if ( isset( $_GET['vmwpmdp-delete-form-warning'] ) AND sanitize_text_field($_GET['vmwpmdp-delete-form-warning']) === '1' ) {
            ?>
            <div id="vmwpmdp-delete-form-warning" class="notice notice-warning is-dismissible">
                <p><?php esc_html_e( 'You can\'t delete the form used for the Floating Button.', 'wpvoicemessage' ); ?></p>
            </div>
            <?php
        }

	}

	/**
	 * You cannot delete the form used for the Floating Button.
	 *
	 * @param $post_id - The post id that is being deleted.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function before_delete_post( $post_id ) {

		/** Process only voicemssg_form posts. */
		if ( ContacterForm::POST_TYPE !== get_post_type( $post_id ) ) { return; }

		if ( $post_id == Settings::get_instance()->options['fbutton_c_form'] ) {

			wp_redirect( admin_url( '/edit.php?post_type=voicemssg_form_qcwp&vmwpmdp-delete-form-warning=1' ) );
			exit();
        }

	}

	/**
	 * Add mark for form that is used for the float button.
	 *
	 * @param $post_states
	 * @param $post
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array
	 **/
	public function fbutton_form_mark( $post_states, $post ) {

		if ( ContacterForm::POST_TYPE !== $post->post_type ) { return $post_states; }

		/** Mark form that is used for floating button. */
		if ( $post->ID == Settings::get_instance()->options['fbutton_c_form'] ) {
			$post_states[] = esc_html__( 'Floating Button', 'wpvoicemessage' );
		}

		return $post_states;

	}

	/**
	 * Add content for Shortcode and Messages columns.
	 *
	 * @param $column_name
	 * @param $form_id
	 *
	 * @throws Exception
	 * @since  1.0.0
	 * @access public
	 **/
	public function columns_body( $column_name, $form_id ) {

	    /** Shortcode column. */
		if ( 'qcld_wpvm_vmwpmdp_voicemssg_form_shortcode' === $column_name ) {
			?>
            <code>[wpvoicemessage id="<?php esc_attr_e( $form_id ); ?>"]</code>
            <a
                class="vmwpmdp-wpvoicemessage-form-shortcode"
                data-clipboard-text='[wpvoicemessage id="<?php esc_attr_e( $form_id ); ?>"]'
                href="#"
                title="<?php esc_html_e( 'Copy to Clipboard', 'wpvoicemessage' ); ?>"
                data-copy-text='<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="clone" class="svg-inline--fa fa-clone fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z"></path></svg>'
                data-copied-text='<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="check-square" class="svg-inline--fa fa-check-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zm0 400H48V80h352v352zm-35.864-241.724L191.547 361.48c-4.705 4.667-12.303 4.637-16.97-.068l-90.781-91.516c-4.667-4.705-4.637-12.303.069-16.971l22.719-22.536c4.705-4.667 12.303-4.637 16.97.069l59.792 60.277 141.352-140.216c4.705-4.667 12.303-4.637 16.97.068l22.536 22.718c4.667 4.706 4.637 12.304-.068 16.971z"></path></svg>'>
                <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="clone" class="svg-inline--fa fa-clone fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z"></path></svg>
            </a>
            <br>
            <br>
            <code>[wpvoicemessage-click id="<?php esc_attr_e( $form_id ); ?>"]Your Content[/wpvoicemessage-click]</code>
            <a
                    class="vmwpmdp-wpvoicemessage-form-shortcode"
                    data-clipboard-text='[wpvoicemessage-click id="<?php esc_attr_e( $form_id ); ?>"]Your Content[/wpvoicemessage-click]'
                    href="#"
                    title="<?php esc_html_e( 'Copy to Clipboard', 'wpvoicemessage' ); ?>"
                    data-copy-text='<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="clone" class="svg-inline--fa fa-clone fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z"></path></svg>'
                    data-copied-text='<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="check-square" class="svg-inline--fa fa-check-square fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zm0 400H48V80h352v352zm-35.864-241.724L191.547 361.48c-4.705 4.667-12.303 4.637-16.97-.068l-90.781-91.516c-4.667-4.705-4.637-12.303.069-16.971l22.719-22.536c4.705-4.667 12.303-4.637 16.97.069l59.792 60.277 141.352-140.216c4.705-4.667 12.303-4.637 16.97.068l22.536 22.718c4.667 4.706 4.637 12.304-.068 16.971z"></path></svg>'>
                <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="clone" class="svg-inline--fa fa-clone fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z"></path></svg>
            </a>
            <?php
		}

		/** Messages column. */
		if ( 'qcld_wpvm_vmwpmdp_voicemssg_form_messages' === $column_name ) :
            ?>
            <div class="vmwpmdp-wpvoicemessage-form-messages-count">
                <?php $this->new_messages_link( $form_id ); ?>&nbsp;<?php $this->total_messages_link( $form_id ); ?>
            </div>
            <?php
		endif;

	}

	private function new_messages_link( $form_id, $show_zeros = false ) {

		/** Select only 'pending' records. */
		$query = new WP_Query( [
			'post_type' => ContacterRecord::POST_TYPE,
			'post_status' => ['pending'],
			'meta_key' => 'vmwpmdp_cform_id',
			'meta_value' => $form_id
		] );

		if ( $query->found_posts > 0 ) {
			?><a class="vmwpmdp-wpvoicemessage-form-messages-count-new" title="<?php esc_html_e( 'New', 'wpvoicemessage' ); ?>" href="<?php echo esc_url( admin_url( 'edit.php?s&post_status=pending&post_type=qcvoicemsg_record&action=-1&m=0&vmwpmdp-wpvoicemessage-filter-by-form=' . $form_id . '&filter_action=Filter&paged=1&action2=-1' ) ); ?>"><?php esc_html_e( $query->found_posts ); ?></a><?php
		} else {
		    if ( $show_zeros ) {
			    ?><span class="vmwpmdp-wpvoicemessage-form-messages-count-new" title="<?php esc_html_e( 'New', 'wpvoicemessage' ); ?>"><?php esc_html_e( $query->found_posts ); ?></span><?php
            }
        }

    }

	private function total_messages_link( $form_id, $show_zeros = false ) {

		$query = new WP_Query( [
			'post_type' => ContacterRecord::POST_TYPE,
			'post_status' => ['publish', 'pending', 'draft'],
			'meta_key' => 'vmwpmdp_cform_id',
			'meta_value' => $form_id
		] );

		if ( $query->found_posts > 0 ) {
			?><a class="vmwpmdp-wpvoicemessage-form-messages-count-total" title="<?php esc_html_e( 'Total', 'wpvoicemessage' ); ?>" href="<?php echo esc_url( admin_url( 'edit.php?s&post_status=all&post_type=qcvoicemsg_record&action=-1&m=0&vmwpmdp-wpvoicemessage-filter-by-form=' . $form_id . '&filter_action=Filter&paged=1&action2=-1' ) ); ?>"><?php esc_html_e( $query->found_posts ); ?></a><?php
        } else {
		    if ( $show_zeros ) {
			    ?><span class="vmwpmdp-wpvoicemessage-form-messages-count-total" title="<?php esc_html_e( 'Total', 'wpvoicemessage' ); ?>"><?php esc_html_e( $query->found_posts ); ?></span><?php
            }
        }

    }

	/**
	 * Add title for Shortcode and Messages columns.
	 *
	 * @param array $columns
	 *
	 * @return array
	 * @since 1.0.0
	 * @access public
	 **/
	public function columns_head( $columns ) {

		/** Add new column key to the existing columns. */
		$columns['qcld_wpvm_vmwpmdp_voicemssg_form_shortcode'] = esc_html__( 'Shortcode', 'wpvoicemessage' );
		$columns['qcld_wpvm_vmwpmdp_voicemssg_form_messages'] = esc_html__( 'Messages', 'wpvoicemessage' );

		/** Define a new order. (•̀o•́)ง */
		$new_order = ['cb', 'title', 'qcld_wpvm_vmwpmdp_voicemssg_form_shortcode', 'qcld_wpvm_vmwpmdp_voicemssg_form_messages', 'date'];

		/** Order columns like set in $new_order. */
		$new = [];
		foreach ( $new_order as $col_name ) {
			$new[ $col_name ] = $columns[ $col_name ];
		}

		/** Return a new column array to WordPress. */
		return $new;
	}

	/**
	 * Meta box setup function.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function meta_boxes_setup() {

		/** Add meta boxes on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		/** Save meta box values on the 'save_post' hook. */
		add_action( 'save_post', [ $this, 'save_meta_boxes' ], 1, 2 );

	}

	/**
	 * Create Options, Shortcode and Messages meta boxes to be displayed on QCvoicemssg Form editor screen.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function add_meta_boxes() {

		/** Options metabox. */
		add_meta_box( 'vmwpmdp-options-mbox', esc_html__( ' Form Options', 'wpvoicemessage' ), [$this, 'options_metabox'], self::POST_TYPE, 'normal', 'default' );

		/** Messages metabox. */
		add_meta_box( 'vmwpmdp-messages-mbox', esc_html__( 'Voice Messages', 'wpvoicemessage' ), [$this, 'messages_metabox'], self::POST_TYPE, 'side', 'default' );

	}

	/**
	 * Display Messages meta box.
	 *
	 * @param WP_Post $voicemssg_form
	 *
	 * @throws Exception
	 * @since  1.0.0
	 * @access public
	 **/
	public function messages_metabox( $voicemssg_form ) {
		?>
        <div class="vmwpmdp-wpvoicemessage-form-messages-count">
	        <?php esc_html_e( 'Messages: ', 'wpvoicemessage' ); ?>
            <?php $this->new_messages_link( $voicemssg_form->ID, true ); ?>&nbsp;<?php $this->total_messages_link( $voicemssg_form->ID, true ); ?>
        </div>
        <?php
	}


	/**
	 * Display Options meta box.
	 *
	 * @param WP_Post $voicemssg_form
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 **/
	public function options_metabox( $voicemssg_form ) {

		/** Render "Options" metabox with all fields. */
		CFOptionsMetaBox::get_instance()->render_metabox( $voicemssg_form );

	}

	/**
	 * Save meta box values.
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return void
	 * @since 1.0.0
	 * @access public
	 **/
	public function save_meta_boxes( $post_id, $post ) {

		/** Work only with voicemssg_form post type. */
	    if ( 'voicemssg_form_qcwp' !== $post->post_type ) { return; }

		/** Verify the nonce before proceeding. */
		if (
             ! isset( $_POST['options_metabox_fields_nonce'] ) ||
             ! wp_verify_nonce( $_POST['options_metabox_fields_nonce'], wpvoicemessage::$basename )
        ) {
			return;
		}

		/** Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/** Check if the current user has permission to edit the post. */
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return;
		}

		/** Save "Options" metabox with all fields. */
		CFOptionsMetaBox::get_instance()->save_metabox( $post_id );

	}

	/**
	 * Register post type.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	public function register_post_type() {

		register_post_type( self::POST_TYPE, [
			'public'              => false,
			'labels'              => [
				'name'                  => esc_html__( 'QCvoicemssg Forms', 'wpvoicemessage' ),
				'singular_name'         => esc_html__( 'QCvoicemssg Form', 'wpvoicemessage' ),
				'add_new'               => esc_html__( 'Add New', 'wpvoicemessage' ),
				'add_new_item'          => esc_html__( 'Add New', 'wpvoicemessage' ),
				'new_item'              => esc_html__( 'New QCvoicemssg Form', 'wpvoicemessage' ),
				'edit_item'             => esc_html__( 'Edit QCvoicemssg Form', 'wpvoicemessage' ),
				'view_item'             => esc_html__( 'View QCvoicemssg Form', 'wpvoicemessage' ),
				'view_items'            => esc_html__( 'View QCvoicemssg Forms', 'wpvoicemessage' ),
				'search_items'          => esc_html__( 'Search QCvoicemssg Forms', 'wpvoicemessage' ),
				'not_found'             => esc_html__( 'No QCvoicemssg Forms found', 'wpvoicemessage' ),
				'not_found_in_trash'    => esc_html__( 'No QCvoicemssg Forms found in Trash', 'wpvoicemessage' ),
				'all_items'             => esc_html__( 'Voice Forms', 'wpvoicemessage' ),
				'archives'              => esc_html__( 'QCvoicemssg Forms Archives', 'wpvoicemessage' ),
				'attributes'            => esc_html__( 'QCvoicemssg Form Attributes', 'wpvoicemessage' ),
				'insert_into_item'      => esc_html__( 'Insert to QCvoicemssg Form', 'wpvoicemessage' ),
				'uploaded_to_this_item' => esc_html__( 'Uploaded to this QCvoicemssg Form', 'wpvoicemessage' ),
				'menu_name'             => esc_html__( 'QCvoicemssg', 'wpvoicemessage' )
			],
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'menu_position'       => false,
			'show_in_rest'        => false,
			'show_in_menu'        => 'edit.php?post_type=' . ContacterRecord::POST_TYPE,
			'rest_base'           => self::POST_TYPE,
			'supports'            => [ 'title' ],
			'show_ui'               => true,
		] );

	}

	/**
	 * Create default wpvoicemessage form.
	 *
	 * @static
	 * @since 1.0.0
     * @return bool
	 **/
	public static function create_default_form() {

		/** Check do we have some forms. */
		$query = new WP_Query( [
			'post_type'     => self::POST_TYPE,
			'post_status'   => 'publish',
		] );

		/** Yes. We have forms. */
		if ( $query->found_posts > 0 ) {

            while ( $query->have_posts() ) {

	            $query->the_post();

	            /** Make first caught form as default. */
	            update_option( 'qcld_wpvm_vmwpmdp_voicemssg_default_form_id', get_the_ID() );
	            break;

            }
			wp_reset_query();

			return true;

        }

		/**
		 * Oops, it looks like there are no forms at all.
         * So create new one.
		 **/
		$form_id = wp_insert_post( [
			'post_type'     => self::POST_TYPE,
			'post_title'    => 'Default Form',
			'post_status'   => 'publish',
		] );

		/** Fill meta fields. */
		if ( ! $form_id ) { return false; }

		/** Get Plugin Settings. */
		$options = Settings::get_instance()->options;

        /** Before Text field value. */
        add_post_meta( $form_id, 'vmwpmdp_before_txt', $options['msg_before_txt'] );

		/** Align. */
		add_post_meta( $form_id, 'vmwpmdp_align', 'center' );

		/** Margin. */
		add_post_meta( $form_id, 'vmwpmdp_btn_margin', '10' );

		/** Padding. */
		add_post_meta( $form_id, 'vmwpmdp_btn_padding', '20' );

		/** Radius. */
		add_post_meta( $form_id, 'vmwpmdp_btn_radius', '50' );

		/** Icon. */
		add_post_meta( $form_id, 'vmwpmdp_btn_icon', 'material/record-voice-microphone-button.svg' );

		/** Position. */
		add_post_meta( $form_id, 'vmwpmdp_btn_icon_position', 'before' );

		/** Button Caption. */
		add_post_meta( $form_id, 'vmwpmdp_btn_caption', esc_html__( 'Record', 'wpvoicemessage' ) );

		/** Button size. */
		add_post_meta( $form_id, 'vmwpmdp_btn_size', '18' );

		/** Button color. */
		add_post_meta( $form_id, 'vmwpmdp_btn_color', '#fff' );

		/** BG color. */
		add_post_meta( $form_id, 'vmwpmdp_btn_bg_color', '#0274e6' );

		/** Hover animation. */
		add_post_meta( $form_id, 'vmwpmdp_btn_hover_animation', 'none' );

		/** After Text field value. */
		add_post_meta( $form_id, 'vmwpmdp_after_txt', $options['msg_after_txt'] );

		/** Speak now Text field value. */
		add_post_meta( $form_id, 'vmwpmdp_speak_now_txt', $options['msg_speak_now'] );

		/** Send Text field value. */
		add_post_meta( $form_id, 'vmwpmdp_send_txt', $options['msg_send'] );

		/** Thanks Text field value. */
		add_post_meta( $form_id, 'vmwpmdp_thanks_txt', $options['msg_thank_you'] );

		/** Additional fields switcher. */
		add_post_meta( $form_id, 'vmwpmdp_additional_fields', 'off' );

        /** Remember ID of created form, to use as default. */
        update_option( 'qcld_wpvm_vmwpmdp_voicemssg_default_form_id', $form_id );

        return true;

	}

	/**
	 * Main ContacterForm Instance.
	 *
	 * Insures that only one instance of ContacterForm exists in memory at any one time.
	 *
	 * @static
	 * @return ContacterForm
	 * @since 1.0.0
	 **/
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ContacterForm ) ) {
			self::$instance = new ContacterForm;
		}

		return self::$instance;
	}
	
} // End Class ContacterForm.
