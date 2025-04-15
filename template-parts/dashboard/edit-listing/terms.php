<?php
global $homey_prefix, $hide_fields, $homey_local, $listing_data, $listing_meta_data, $homey_booking_type;
$min_book_days = homey_get_field_meta('min_book_days'); 
$min_book_hours = homey_get_field_meta('min_book_hours'); 
$max_book_days = homey_get_field_meta('max_book_days'); 
$min_book_weeks = homey_get_field_meta('min_book_weeks'); 
$max_book_weeks = homey_get_field_meta('max_book_weeks'); 
$min_book_months = homey_get_field_meta('min_book_months'); 
$max_book_months = homey_get_field_meta('max_book_months'); 
$checkin_after = homey_get_field_meta('checkin_after'); 
$checkout_before = homey_get_field_meta('checkout_before'); 
$get_start_hour = homey_get_field_meta('start_hour'); 
$get_end_hour = homey_get_field_meta('end_hour'); 
$smoke = homey_get_field_meta('smoke'); 
$pets = homey_get_field_meta('pets'); 
$party = homey_get_field_meta('party'); 
$children = homey_get_field_meta('children');

$additional_rules = isset($listing_meta_data['homey_additional_rules'][0]) ? $listing_meta_data['homey_additional_rules'][0] : ''; 
$cancellation_policy = isset($listing_meta_data['homey_cancellation_policy'][0]) ? $listing_meta_data['homey_cancellation_policy'][0] : ''; 


$checkin_after_before = homey_option('checkin_after_before');
$checkin_after_before_array = explode( ',', $checkin_after_before );

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');

// NEW FIELDS
$slots = get_post_meta($listing_data->ID, 'homey_parking_spots', true);
$homey_rules = get_post_meta($listing_data->ID, 'homey_rules', true);

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'rules') {
    $class = 'in active';
}
?>
<div id="rules-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
            <h3 class="title"><?php echo esc_attr(homey_option('ad_terms_rules')); ?></h3>
    </div>
    <div class="block-body">
        <div class="row mb-20">
            <div class="col-sm-12 col-xs-12">
                <div class="form-group">
                    <label><?php esc_html_e('How many parking spots are available?','homey-child'); ?></label>
                    <input type="number" name="parking_spots" id="parking_spots" class="form-control" value="<?php echo $slots; ?>" placeholder="<?php esc_html_e('Enter Number of Parking Spots','homey-child'); ?>">
                </div>
            </div>
            <div class="col-sm-12 col-xs-12">
                <div class="listing-form-row">
                    <div class="house-features-list">
                        <label class="label-title" style="margin-bottom: 5px;"><?php esc_html_e('Parking Facilities','homey-child'); ?></label>

                        <?php
                        $amenities = get_terms('parking', array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
                        $selected_amenities = wp_get_post_terms($listing_data->ID, 'parking', array('fields' => 'ids'));

                        if (!empty($amenities)) {
                            foreach ($amenities as $amenity) {
                                $checked = in_array($amenity->term_id, $selected_amenities) ? 'checked' : '';
                                echo '<label class="control control--checkbox">';
                                    echo '<input type="checkbox" name="listing_parking[]" id="amenity-' . esc_attr($amenity->slug) . '" value="' . esc_attr($amenity->term_id) . '" ' . $checked . '>';
                                    echo '<span class="contro-text">' . esc_attr($amenity->name) . '</span>';
                                    echo '<span class="control__indicator"></span>';
                                echo '</label>';
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <label class="label-title" style="margin-bottom: 5px;"><?php esc_html_e('Additoinal Rules!','homey-child');?></label>
                <small>(<?php esc_html_e('Add Mulitple additional rules for your listing.','homey-child');?>)</small>
            </div>
            <div class="col-sm-12 mt-10">
                <div class="row">
                    <div class="col-sm-9 col-xs-12">
                        <input type="text" name="listing_rules_add_rule" id="listing_rules_add_rule" class="form-control" placeholder="<?php esc_html_e('Enter the Rules!','homey-child'); ?>">
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <a href="#" class="btn btn-primary btn-single-rule" style="width:100%;"><i class="homey-icon homey-icon-add"></i> <?php esc_html_e('Add Rule','homey-child'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-xs-12 listing-rules-row mt-10">
                <?php 
                    foreach($homey_rules as $id => $rule):
                        $args = array(
                            'id' => $id,
                            'rulesText' => '',
                            'data' => $rule
                        );

                        get_template_part('template-parts/dashboard/submit-listing/single-rule', null, $args);
                    endforeach; 
                ?>
            </div>
        </div>
    </div>
</div>

