<?php
global $homey_local, $hide_fields, $homey_booking_type;
$checkin_after_before = homey_option('checkin_after_before');
$checkin_after_before_array = explode( ',', $checkin_after_before );

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');
$start_and_end_hours = '';
for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
    $start_and_end_hours .= '<option value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
}

$checkinout_hours = '';
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php esc_html_e('Parking & Accessibility','homey-child'); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            
            <div class="row mb-20">
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label><?php esc_html_e('How many parking spots are available?','homey-child'); ?></label>
                        <input type="number" name="parking_spots" id="parking_spots" class="form-control" placeholder="<?php esc_html_e('Enter Number of Parking Spots','homey-child'); ?>">
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12">
                    <div class="listing-form-row">
                        <div class="house-features-list">
                            <label class="label-title" style="margin-bottom: 5px;"><?php esc_html_e('Parking Facilities','homey-child'); ?></label>

                            <?php
                            $amenities = get_terms( 'parking', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                            if (!empty($amenities)) {
                                $count = 1;
                                foreach ($amenities as $amenity) {
                                    echo '<label class="control control--checkbox">';
                                        echo '<input type="checkbox" name="listing_parking[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '">';
                                        echo '<span class="contro-text">'.esc_attr( $amenity->name ).'</span>';
                                        echo '<span class="control__indicator"></span>';
                                    echo '</label>';
                                    $count++;
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
                </div>
                <div class="col-sm-12 col-xs-12 listing-rules-row mb-10">
                    <div class="single-rules-row">
                        <label class="label-condition">Is Smoking Allowed?</label>
                        <div class="rules-fileds-sections">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input name="smoke" value="1" type="radio">
                                    <span class="control-text"><?php esc_html_e('Yes','homey-child'); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input name="smoke" value="0" checked="checked" type="radio">
                                    <span class="control-text"><?php esc_html_e('No','homey-child'); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                            <a href="#" class="btn-single-rule"><i class="fa fa-trash"></i></a>
                        </div>
                    </div>
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
            </div>
        </div>
    </div>
</div>
