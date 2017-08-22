<?php

/*
wrapper functions for get_user_meta and update_user_meta
notes are stored as a separate meta key per content piece per user
*/
function get_personal_notes($uid, $cid){
    $notes_meta_key = 'notes_' . $cid;
    return get_user_meta($uid, $notes_meta_key, true);
}

function set_personal_notes($uid, $cid, $notes){
    $notes_meta_key = 'notes_' . $cid;
    update_user_meta($uid, $notes_meta_key, $notes);
}

/*
add javascript to the frontend to save the user's notes when they submit a form
*/
function enqueue_personal_notes(){
    global $post_type;
    
    if ($post_type === 'sfwd-topic'){
        wp_register_script('personal_notes', plugin_dir_url(__FILE__) . '/js/personal_notes.js');
        
        // pass nonce, url, user id, and content id to javascript
        $js_data = array(
            'security' => wp_create_nonce('personal_notes'),
            'user' => get_current_user_id(),
            'cid' => get_the_ID(),
            'ajaxurl' => personal_notes_url()
        );
        
        wp_localize_script('personal_notes', 'unConfig', $js_data);
        wp_enqueue_script('personal_notes',array('jquery'));
    }
}
add_action('wp_enqueue_scripts','enqueue_personal_notes');

function ajax_personal_notes(){
    if (wp_verify_nonce( $_POST['security'], 'personal_notes' )) {
        try {
            $uid = $_POST['user'];
            $cid = $_POST['cid'];
            $notes = $_POST['notes'];
            $error= "";
        
            set_personal_notes($uid, $cid, $notes);
            $outnotes = get_personal_notes($uid, $cid);
        
        
        }
        catch (Exception $e){
            $error = 'Caught exception: ' .  $e->getMessage();
        }
        
        // return the updated notes and the error message
        $output_array = array(
            'notes' => $outnotes,
            'error' => $error
        );
        echo json_encode($output_array);
        
        wp_die();
    } else {
        // reject incorrect nonce with a forbidden status code
        header('HTTP/1.1 Validation Check Failed', true, 403);
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('error' => 'The session token procided in the update request was invalid.')));
    }
}
add_action('wp_ajax_personal_notes', 'ajax_personal_notes');
add_action('wp_ajax_nopriv_personal_notes', 'ajax_personal_notes');

function personal_notes_url(){
    return admin_url( 'admin-ajax.php' ) . '?action=personal_notes';
}