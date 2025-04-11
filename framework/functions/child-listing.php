<?php

add_action('wp_ajax_generate_listing_rules', 'generate_listing_rules');
add_action('wp_ajax_nopriv_generate_listing_rules', 'generate_listing_rules');
function generate_listing_rules(){
    $rulesText = $_POST['rulesText'];

    $args = array(
        'id' => uniqid(),
        'rulesText' => $rulesText,
    );
    
    ob_start();
    get_template_part('template-parts/dashboard/submit-listing/single-rule', null, $args);
    $html = ob_get_contents();
    ob_end_clean();

    $response_data = array(
        'success' => true,
        'message' => 'Rule added successfully',
        'rule_html' => $html,
    );

    echo json_encode($response_data);
    wp_die();
    
}

// HOMEY LISTING GALLERY UPLOAD
function homey_listing_gallery_upload() {

    // Check security Nonce
    $verify_nonce = $_REQUEST['verify_nonce'];
    if (!wp_verify_nonce($verify_nonce, 'verify_gallery_nonce')) {
        echo json_encode(array('success' => false, 'reason' => 'Invalid nonce!'));
        die;
    }

    $submitted_file = $_FILES['listing_upload_file'];
    // $is_dimension_valid = homey_listing_image_dimension($submitted_file);
    $uploaded_image = wp_handle_upload($submitted_file, array('test_form' => false));

    if (isset($uploaded_image['file'])) {
        $file_name = basename($submitted_file['name']);
        $file_type = wp_check_filetype($uploaded_image['file']);

        // Prepare an array of post data for the attachment.
        $attachment_details = array(
            'guid' => $uploaded_image['url'],
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_name)),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment_details, $uploaded_image['file']);
        $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_image['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        $thumbnail_url = wp_get_attachment_image_src($attach_id, 'thumbnail');
        $listing_thumb = wp_get_attachment_image_src($attach_id, 'homey-listing-thumb');
        $feat_image_url = wp_get_attachment_url($attach_id);

        $ajax_response = array(
            'success' => true,
            'url' => $thumbnail_url[0],
            'attachment_id' => $attach_id,
            'full_image' => $feat_image_url,
            'thumb' => $listing_thumb[0],
        );

        echo json_encode($ajax_response);
        die;

    }

}