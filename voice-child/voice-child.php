<?php

/*

Plugin Name: Voice Child

Version: 1.0

Description: Additional functions for voice comments

Author: João Pedro Costa

Domain Path: /languages

*/



defined('ABSPATH') || exit;

add_action('comment_form_top', 'add_voice');

function add_voice(){

    if ( file_exists(plugin_dir_path(__FILE__) . 'templates/voice.php')) { 
        $located = plugin_dir_path(__FILE__) . 'templates/voice.php';
        include($located);
    }
}

add_action('wp_enqueue_scripts', function(){

    wp_enqueue_script('recorder', plugin_dir_url(__FILE__) . 'assets/js/recorder.js');

    wp_localize_script( 'recorder', 'my_ajax_object',
            array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

});


add_action( 'wp_ajax_nopriv_save_audio', 'save_audio');
add_action( 'wp_ajax_save_audio', 'save_audio');

function save_audio(){
    if(isset($_FILES) && isset($_FILES['audio']) && isset($_FILES['audio']['tmp_name'])){
        $_FILES['audio']['name'] = $_POST['filename'];

        $upload_overrides = array( 'test_form' => false);
   
        $movefile = wp_handle_upload($_FILES['audio'], $upload_overrides);

        // $attachment = array(
        //     "post_mime_type" => $_FILES['audio']['type'],
        //     "post_title" => preg_replace('/\.[^.]+$/', '', $_FILES['audio']['name']),
        //     "post_content" => "",
        //     "post_status" => "inherit"
        // );
        
        // $attachment_id = wp_insert_attachment( $attachment, $_FILES['audio'] );

        // if (!is_wp_error($attachment_id)) {
        //     require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        //     $attachment_data = wp_generate_attachment_metadata( $attachment_id, $_FILES['audio'] );
        //     wp_update_attachment_metadata( $attachment_id,  $attachment_data );
        // }

        // echo "attacment id is $id";

        // var_dump($attachment);
        
        
        if ($movefile) {
            $wp_upload_dir = wp_upload_dir();
            $filepath = $wp_upload_dir['url'].'/'.basename($movefile['file']);
            $attachment = array(
            'guid' => $filepath,
            'post_mime_type' => $movefile['type'],
            'post_title' => preg_replace('/\.[^.]+$/','', basename($movefile['file'])),
            'post_content' =>'',
            'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            // if($comment_id){
                
            //     $comment = get_comment($comment_id);
                
            //     // update_field('voice', $filepath, $comment);
            // }
            echo $filepath;
        }
    }

    wp_die();

}

// add_action('comment_form_before', 'show_voices');

// function show_voices($post_id){

//     // $comment_id = get_comment_ID();

//     // $comment = get_comment($comment_id);

//     $comment = get_comment();

//     if($comment){

        
//         $voice = get_field('voice', $comment);
//         if($voice){
//             echo '
//             <audio controls>
//             <source src="'.$voice.'" type="audio/wav">
//             Your browser does not support the audio element.
//             </audio>';
//         }
        
//     }
// }
