<?php
global $post, $homey_local;
$amenities   = wp_get_post_terms( get_the_ID(), 'listing_amenity', array("fields" => "all"));
$facilities  = wp_get_post_terms( get_the_ID(), 'listing_facility', array("fields" => "all"));
$parkings  = wp_get_post_terms( get_the_ID(), 'parking', array("fields" => "all"));

if(!empty($amenities) || !empty($facilities) || !empty($parkings)) { ?>
<div id="features-section" class="features-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_features')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <?php if(!empty($amenities)) { ?>
                    <p><strong><?php echo esc_attr(homey_option('sn_amenities')); ?></strong></p>
                    <ul class="detail-list detail-list-2-cols">
                        <?php foreach($amenities as $amenity): ?>
                            <li><i class="<?php echo $amenity->description != '' ? $amenity->description : 'homey-icon homey-icon-arrow-right-1'; ?>" aria-hidden="true"></i> <?php echo esc_attr($amenity->name); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php } ?>

                    <?php if(!empty($facilities)) { ?>
                    <p><strong><?php echo esc_attr(homey_option('sn_facilities')); ?></strong></p>
                    <ul class="detail-list detail-list-2-cols">
                        <?php foreach($facilities as $facility): ?>
                            <li><i class="<?php echo $facility->description != '' ? $facility->description : 'homey-icon homey-icon-arrow-right-1'; ?>" aria-hidden="true"></i> <?php echo esc_attr($facility->name); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php } ?>

                    <?php if(!empty($parkings)) { ?>
                    <p><strong><?php esc_html_e('Parking','homey-child'); ?></strong></p>
                    <ul class="detail-list detail-list-2-cols">
                        <?php foreach($parkings as $parking): ?>
                            <li><i class="<?php echo $parking->description != '' ? $parking->description : 'homey-icon homey-icon-arrow-right-1'; ?>" aria-hidden="true"></i> <?php echo esc_attr($parking->name); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php } ?>

                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
<?php } ?>