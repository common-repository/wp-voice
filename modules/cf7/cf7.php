<?php

class QcCF7WpVoiceMessage{

    public function __construct(){

        add_action( 'wpcf7_init', [$this, 'qcwpvoicemessage_add_form_tag_voicemessage'] );

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Tag Generator Button

        add_action('admin_init', [$this, 'qcvoicemessage_tag_generator']);

        //add_filter( 'wpcf7_before_send_mail', [$this, 'wpcf7_before_send_mail_function'], 10, 3 );
        add_filter( 'wpcf7_posted_data', [$this, 'wpcf7_before_send_mail_function'], 10, 1 );
      //  add_filter('get_edit_post_link' , [$this, 'wpvo_edit_url'], 10, 1 );

    }

   
    function wpcf7_before_send_mail_function( $posted_data ) {
        global $post;
        // $submission = WPCF7_Submission::get_instance();
        // $posted_data = $submission->get_posted_data();
        $html = '';
                
        foreach ($posted_data as $post_key => $data) {

            if( $post_key == 'qcwpvoicemessage' ){
                $voice_data = json_decode($data, true);
                $record_id = $voice_data['record_id'][0];
               
                $post_link = admin_url(). 'post.php?post='.$record_id.'&action=edit'; 
               

            	if( isset($voice_data['record_id']) && !empty($voice_data['record_id']) ){
                    $records = $voice_data['record_id'];
                    $count=0;
                    foreach( $records as $record ){
                        $count++;
                        if( $count > 1 ){ $html .= ' \r\n'; }
                        $audio_path = get_post_meta($record, 'qcld_wpvm_vmwpmdp_voicemssg_audio', true);
                        if( $audio_path ){
                            $audio_url = $this->abs_path_to_url( $audio_path );
                            $html .= "Voice Message Link" . "\n";
                            $html .= $audio_url . "\n \n";
                            $html .= "Edit or View Voice message". "\n";
                            $html .=  $post_link;
                        }
                    }
                    // echo $html;
                }
            }
        }
        $posted_data['qcwpvoicemessage'] = $html;
        
        return $posted_data;
    }

    public function abs_path_to_url( $path = '' ) {

        $upload_dir = wp_get_upload_dir();
		$url = str_replace(
			wp_normalize_path( $upload_dir['basedir'] ),
			$upload_dir['baseurl'],
			wp_normalize_path( $path )
		);
       
        return esc_url_raw( $url );
    }



    public function enqueue_scripts(){

        wp_enqueue_script('cf7_wpvoicemessage', plugin_dir_url(__FILE__) . 'js/cf7.js', array('jquery'), false,  true);

    }



    public function qcwpvoicemessage_add_form_tag_voicemessage() {

      wpcf7_add_form_tag( 'qcwpvoicemessage', [$this,'qcwpvoicemessage_form_tag_handler'] ); // "clock" is the type of the form-tag

    }

    

    public function qcwpvoicemessage_form_tag_handler( $tag ) {

        $tag = new WPCF7_FormTag($tag);

        $form_id = $tag->get_option('form_id', '', true);

        $name = '';

        if( isset($tag['options'][0]) && !empty($tag['options'][0]) ){

            $name = $tag['options'][0];

        }

        // echo $form_id;

        if( isset($form_id) && $form_id > 0 ){

            // return do_shortcode('[wpvoicemessage id="'.$tag->raw_form_id[0].'"]');

            return do_shortcode('[cf7wpvoicemessage name="'.$name.'" id="'.$form_id.'"]');

        }else{

            return false;

        }

    }



    public function qcvoicemessage_tag_generator(){

        // wpcf7_add_tag_generator( $name, $title, $elm_id, $callback, $options = array() )

        if (! function_exists( 'wpcf7_add_tag_generator'))

            return;



        wpcf7_add_tag_generator(

            'qcwpvoicemessage',

            __('WPVoice Message', 'wpcf7cf'),

            'qcwpvoicemessage',

            [$this, 'qcwpvoicemessage_tab_generator_cb']

        );

    }

    

    public function qcwpvoicemessage_tab_generator_cb($args){

        $args = wp_parse_args( $args, array() );

        $type = 'qcwpvoicemessage';



        $description = __( "Generate a voicemessage tag to display the voicemessage recorder on the form.", 'cf7cf' );



        ?>

            <div class="control-box">

                <fieldset>

                    <legend><?php echo sprintf( esc_html( $description ) ); ?></legend>



                    <table class="form-table">

                        <tbody>


                        <tr>

                            <th scope="row">

                                <label for="form_id">

                                    <?php echo esc_html( __( 'Select Record Field', 'contact-form-7' ) ); ?>

                                </label>

                            </th>

                            <td>

                                <?php

                                    $args = array(

                                        'post_type' =>  'voicemssg_form_qcwp',

                                        'posts_per_page'    =>  -1,

                                    );

                                    $query = new WP_Query($args);

                                    if( $query->have_posts() ){

                                ?>

                                    <!-- <select id="form_id" name="form_id"> -->

                                        <!-- <option value="">Select a Form</option> -->

                                        <?php while( $query->have_posts() ){ $query->the_post(); ?>

                                            <!-- <option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option> -->

                                            <label>

                                                <input type="radio" name="form_id" class="option" value="<?php echo get_the_ID(); ?>">

                                                <?php the_title(); ?>

                                            </label>

                                        <?php } ?>

                                    <!-- </select> -->

                                <?php } ?>

                            </td>

                        </tr>



                        </tbody>

                    </table>

                </fieldset>

            </div>



            <div class="insert-box">

                <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />



                <div class="submitbox">

                    <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />

                </div>



                <br class="clear" />

            </div>

        <?php

    }

}



new QcCF7WpVoiceMessage();



 

 



