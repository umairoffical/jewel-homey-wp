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

        <?php if($hide_fields['cancel_policy'] != 1) { ?>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <!--<div class="form-group">
                        <label for="cancel"><?php echo esc_attr(homey_option('ad_cancel_policy')).homey_req('cancellation_policy'); ?></label>
                        <textarea name="cancellation_policy" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_cancel_policy_plac'), 'homey'); ?>" <?php homey_required('cancellation_policy'); ?>><?php echo $cancellation_policy; ?></textarea>
                    </div>-->

                    <div class="form-group">
                        <label for="cancel"><?php echo esc_attr(homey_option('ad_cancel_policy')).homey_req('cancellation_policy'); ?></label>
                        <select name="cancellation_policy" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr(homey_option('ad_cancel_policy')); ?>">
                            <option <?php if($cancellation_policy < 1){ echo 'selected'; } ?> value=""><?php echo esc_html__("Select Cancellation Policy", "homey"); ?></option>
                            <?php

                $args = array(
                    'post_type' => 'homey_cancel_policy',
                    'posts_per_page' => 100
                );

                $policies_data = '';

                $policies_qry = new WP_Query($args);
                if ($policies_qry->have_posts()):
                    while ($policies_qry->have_posts()): $policies_qry->the_post();
                        $is_selected = $cancellation_policy == get_the_ID() ? "selected='selected'" : '';
                        echo '<option '.$is_selected.' value="'.get_the_ID().'">'.get_the_title().'</option>';
                        endwhile;
                endif;
                ?>
                        </select>
                        <?php  wp_reset_postdata(); ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="row">

            <?php if($homey_booking_type == 'per_hour') { ?>

                <?php if($hide_fields['min_book_days'] != 1) { ?>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="min_book_hours"><?php echo esc_attr(homey_option('ad_min_hours_booking')).homey_req('min_book_days'); ?></label>
                        <input type="text" name="min_book_hours" <?php homey_required('min_book_days'); ?> value="<?php echo esc_attr($min_book_hours); ?>" class="form-control" id="min_book_hours" placeholder="<?php echo esc_attr(homey_option('ad_min_hours_booking_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php } elseif($homey_booking_type == 'per_week') {

                    if($hide_fields['min_book_weeks'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="min_book_weeks"><?php echo esc_attr(homey_option('ad_min_weeks_booking')).homey_req('min_book_weeks'); ?></label>
                            <input type="text" name="min_book_weeks" value="<?php echo esc_attr($min_book_weeks); ?>" class="form-control" <?php homey_required('min_book_weeks'); ?> id="min_book_weeks" placeholder="<?php echo esc_attr(homey_option('ad_min_weeks_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }

                    if($hide_fields['max_book_weeks'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="max_book_weeks"><?php echo esc_attr(homey_option('ad_max_weeks_booking')).homey_req('max_book_weeks'); ?></label>
                            <input type="text" name="max_book_weeks" value="<?php echo esc_attr($max_book_weeks); ?>" class="form-control" <?php homey_required('max_book_weeks'); ?> id="max_book_weeks" placeholder="<?php echo esc_attr(homey_option('ad_max_weeks_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }


            } elseif($homey_booking_type == 'per_month') {

                    if($hide_fields['min_book_months'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="min_book_months"><?php echo esc_attr(homey_option('ad_min_months_booking')).homey_req('min_book_months'); ?></label>
                            <input type="text" name="min_book_months" value="<?php echo esc_attr($min_book_months); ?>" class="form-control" <?php homey_required('min_book_months'); ?> id="min_book_months" placeholder="<?php echo esc_attr(homey_option('ad_min_months_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }

                    if($hide_fields['max_book_months'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="max_book_months"><?php echo esc_attr(homey_option('ad_max_months_booking')).homey_req('max_book_months'); ?></label>
                            <input type="text" name="max_book_months" value="<?php echo esc_attr($max_book_months); ?>" class="form-control" <?php homey_required('max_book_months'); ?> id="max_book_months" placeholder="<?php echo esc_attr(homey_option('ad_max_months_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }


            } else { ?>

                <?php if($hide_fields['min_book_days'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="min_book_days"><?php echo esc_attr(homey_option('ad_min_days_booking')).homey_req('min_book_days'); ?></label>
                        <input type="text" name="min_book_days" <?php homey_required('min_book_days'); ?> value="<?php echo esc_attr($min_book_days); ?>" class="form-control" id="min_book_days" placeholder="<?php echo esc_attr(homey_option('ad_min_days_booking_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['max_book_days'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="max_book_days"><?php echo esc_attr(homey_option('ad_max_days_booking')).homey_req('max_book_days'); ?></label>
                        <input type="text" name="max_book_days" <?php homey_required('max_book_days'); ?> value="<?php echo esc_attr($max_book_days); ?>" class="form-control" id="max_book_days" placeholder="<?php echo esc_attr(homey_option('ad_max_days_booking_plac')); ?>">
                    </div>
                </div>
                <?php } ?>
            <?php } ?>

        </div>

        <div class="row">
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="start_hour"><?php echo esc_html__('Start Business Hour', 'homey').homey_req('start_hour'); ?></label>
                    <select name="start_hour" class="selectpicker" <?php homey_required('start_hour'); ?> id="start_hour" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                            <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                            <?php 
                            for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
                                echo '<option '.selected(date('H:i',$halfhour), $get_start_hour, false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
                            }
                            ?>
                    </select>
                </div>
            </div>
            
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="end_hour"><?php echo esc_html__('End Business Hour', 'homey').homey_req('end_hour'); ?></label>
                    <select name="end_hour" class="selectpicker" <?php homey_required('end_hour'); ?> id="end_hour" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                        <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                        <?php 
                        for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
                            echo '<option '.selected(date('H:i',$halfhour), $get_end_hour, false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
        </div>

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
                if(!empty($homey_rules)):
                    foreach($homey_rules as $id => $rule):
                        $args = array(
                            'id' => $id,
                            'rulesText' => '',
                            'data' => $rule
                        );

                        get_template_part('template-parts/dashboard/submit-listing/single-rule', null, $args);
                    endforeach; 
                endif;
                ?>
            </div>
        </div>
    </div>
</div>

