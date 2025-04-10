<?php
// PHP INCLUDE FILES
include_once( get_stylesheet_directory() . '/framework/functions/child-register-scripts.php');

// ENQUEUE STYLES
function homey_child_enqueue_styles() {
    wp_enqueue_style('homey-child', get_stylesheet_directory_uri() . '/style.css', array('homey'));
}
add_action('wp_enqueue_scripts', 'homey_child_enqueue_styles');

// ENQUEUE SCRIPTS
function homey_child_enqueue_scripts() {
    wp_enqueue_script('homey-child-js', get_stylesheet_directory_uri() . '/js/homey-child.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'homey_child_enqueue_scripts');

// TIMES OPTIONS
if(!function_exists('homey_get_times_options')) {
    function homey_get_times_options() {
        $times = array(
            '1' => '1:00 AM',
            '2' => '2:00 AM', 
            '3' => '3:00 AM',
            '4' => '4:00 AM',
            '5' => '5:00 AM',
            '6' => '6:00 AM',
            '7' => '7:00 AM',
            '8' => '8:00 AM',
            '9' => '9:00 AM',
            '10' => '10:00 AM',
            '11' => '11:00 AM',
            '12' => '12:00 AM',
            '13' => '1:00 PM',
            '14' => '2:00 PM',
            '15' => '3:00 PM',
            '16' => '4:00 PM',
            '17' => '5:00 PM',
            '18' => '6:00 PM',
            '19' => '7:00 PM',
            '20' => '8:00 PM',
            '21' => '9:00 PM',
            '22' => '10:00 PM',
            '23' => '11:00 PM',
            '24' => '12:00 PM'
        );
        
        return $times;
    }
}


// parking taxonomy for listings
function homey_child_parking_taxonomy() {
    $labels = array(
        'name' => 'Parking',
        'singular_name' => 'Parking',
        'search_items' => 'Search Parking',
        'all_items' => 'All Parking',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'parking'),
    );

    register_taxonomy('parking', 'listing', $args);
}
add_action('init', 'homey_child_parking_taxonomy');