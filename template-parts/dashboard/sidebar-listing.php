<?php
global $current_user, $homey_local, $homey_prefix;
wp_get_current_user();
$userID  =  $current_user->ID;
$homey_author = homey_get_author_by_id('36', '36', 'img-circle', $userID);
$author_pic = $homey_author['photo'];
$price_postfix = '';

$cgl_meta = homey_option('cgl_meta');
$cgl_beds = homey_option('cgl_beds');
$cgl_baths = homey_option('cgl_baths');
$cgl_guests = homey_option('cgl_guests');
$cgl_types = homey_option('cgl_types');
$price_separator = homey_option('currency_separator');

$bedrooms_icon = homey_option('lgc_bedroom_icon'); 
$bathroom_icon = homey_option('lgc_bathroom_icon'); 
$guests_icon = homey_option('lgc_guests_icon');
$price_separator = homey_option('currency_separator');
$size = '';
$size_prefix = '';

if(!empty($bedrooms_icon)) {
    $bedrooms_icon = '<i class="'.esc_attr($bedrooms_icon).'"></i>';
}
if(!empty($bathroom_icon)) {
    $bathroom_icon = '<i class="'.esc_attr($bathroom_icon).'"></i>';
}
if(!empty($guests_icon)) {
    $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
}

$show_size_fields = 'none';

$total_guests = 0;
$listing_id = $title = $address = $image = $listing_address = $night_price = $listing_bedrooms = $baths = $guests = $permalink = '';
if((isset($_GET['edit_listing']) && $_GET['edit_listing'] != '') || (isset($_GET['upgrade_id']) && $_GET['upgrade_id'] != '') || (isset($_GET['listing_id']) && $_GET['listing_id'] != '')) {

    if(isset($_GET['edit_listing']) && $_GET['edit_listing'] != '') {
        $listing_id = $_GET['edit_listing'];

    } elseif(isset($_GET['upgrade_id']) && $_GET['upgrade_id'] != '') {
        $listing_id = $_GET['upgrade_id'];

    } elseif(isset($_GET['listing_id']) && $_GET['listing_id'] != '') {
        $listing_id = $_GET['listing_id'];

    }

    if(!empty($listing_id)) {
        $show_size_fields = 'inline-block';
    } else {
        $show_size_fields = 'none';
    }

    $title = get_the_title($listing_id);

    $listing_address = '';//homey_get_listing_data_by_id('listing_address', $listing_id);
    $night_price = homey_get_listing_data_by_id('night_price', $listing_id);
    $listing_bedrooms = homey_get_listing_data_by_id('listing_bedrooms', $listing_id);
    $baths = homey_get_listing_data_by_id('baths', $listing_id);
    $guests = homey_get_listing_data_by_id('guests', $listing_id);

    $guests         = $guests > 0 ? $guests : 0;

    $allow_num_additional_guests = get_post_meta($listing_id, 'homey_allow_additional_guests', true );

    $num_additional_guests = get_post_meta($listing_id, 'homey_num_additional_guests', true );
    $num_additional_guests = $num_additional_guests > 0 && $allow_num_additional_guests != 'no' ? $num_additional_guests : 0;

    $total_guests   = (int) $num_additional_guests + (int) $guests;

    $permalink = get_permalink($listing_id);

    $listing_price = homey_get_price_by_id($listing_id);

    $price_postfix = homey_get_price_label_by_id($listing_id);

    $size =  get_post_meta($listing_id, 'homey_listing_size', true );
    $size_prefix = get_post_meta($listing_id, 'homey_listing_size_unit', true );
}

if ( isset($_GET['mode']) && $_GET['mode'] != '' ) {
    $price_postfix = homey_get_price_label_by_mode($_GET['mode']);
}

?>
<div class="item-grid-view">
    <div class="add-new-item item-wrap">
        <div class="upload-view-media item-media-thumb">
            <div class="media-image">
                <?php
                echo '<a class="hover-effect" href="'.esc_url($permalink).'">';
                if(!empty($listing_id)) {
                    if( has_post_thumbnail( $listing_id ) ) {
                        $post_thumbnail_id = get_post_thumbnail_id( $listing_id );
                        $listing_thumb = wp_get_attachment_image_src( $post_thumbnail_id, 'homey-listing-thumb' );?>
                        <img src="<?php echo esc_url($listing_thumb[0]); ?>">
                    <?php    
                    }else{
                        homey_image_placeholder( 'homey-listing-thumb' );
                    }   
                } else {
                ?>
                <img src="http://place-hold.it/370x250" alt="<?php esc_attr_e('Image', 'homey'); ?>">
                <?php } ?>
                <?php echo '</a>'; ?>
            </div>
            <!-- <div class="item-media-price">
                <span class="item-price">
                    <sup><?php //echo homey_get_currency(false); ?></sup>
                    <span class="price-count" id="price-place">
                        <?php 
                        // if(!empty($listing_price)) {
                        //     echo esc_html($listing_price);
                        // } else {
                        //     echo '0'; 
                        // }?> 
                    </span>
                    <sub><?php //echo esc_attr($price_separator); ?><span class="price-postfix" id="price-postfix"><?php //echo $price_postfix;?></span></sub>
                </span>
            </div> -->

            <?php if(!empty($author_pic)) { ?>
            <div class="item-user-image">
                <?php echo ''.$author_pic; ?>
            </div>
            <?php } ?>
        </div>
        <div class="upload-view-body item-body">
            <div class="item-title-head">
                <h3 class="title">
                    <span id="title-place">
                        <a href="<?php echo esc_url($permalink);?>">
                        <?php 
                        if(!empty($title)) {
                             echo esc_html($title);
                        } else {
                            esc_html_e('Title', 'homey');
                        }?>
                        </a>        
                    </span>
                </h3>
                <?php get_template_part('single-listing/item-address', null, array('address_tag_class' => 'item-address', 'prefix_address' => '<span id="address-place">', 'postfix_address' => '</span>')); ?>

                <!--<address class="item-address">
                    <span id="address-place"> 
                        <?php 
                        // if(!empty($listing_address)) {
                        //     echo esc_html($listing_address);
                        // } else {
                        //     esc_html_e('Address', 'homey'); 
                        // }?> 
                    </span>
                </address>-->
            </div>

            <?php if($cgl_meta != 0) { ?>
            <ul class="item-amenities">

                <?php if(empty($size) || $size != 0) { ?>
                <li class="sidebar-item-size" style="display: <?php echo $show_size_fields; ?>;">
                    <i class="fa fa-arrows-h"></i>
                    <span id="total-size"><?php echo esc_html($size); ?></span> 
                    <span id="size-prefix"><?php echo $size_prefix;?></span>
                </li>
                <?php } ?>

                <?php //if($cgl_beds != 0) { ?>
                <!-- <li>
                    <?php //echo ''.$bedrooms_icon; ?>
                    <span id="total-beds"><?php //echo esc_html($listing_bedrooms); ?></span> 
                    <?php //echo homey_option('glc_bedrooms_label');?>
                </li> -->
                <?php //} ?>

                <?php //if($cgl_baths != 0) { ?>
                <!-- <li>
                    <?php //echo ''.$bathroom_icon; ?>
                    <span id="total-baths"><?php //echo esc_html($baths); ?></span> 
                    <?php //echo homey_option('glc_baths_label');?>
                </li> -->
                <?php //} ?>

                <?php if($cgl_guests!= 0) { ?>
                <li>
                    <?php echo ''.$guests_icon; ?>
                    <span id="total-guests"><?php echo esc_html($total_guests); ?></span>
                    <?php esc_html_e('Max Guests', 'homey');?>
                </li>
                <?php } ?>

                <?php if($cgl_types != 0) { ?>
                <li class="item-type">
                    <span id="listing-type-view">
                        <?php 
                        if(!empty($listing_id)) {
                            $listing_types = wp_get_object_terms($listing_id, 'listing_type');
                            $type_names = array();
                            foreach($listing_types as $type) {
                                $type_names[] = $type->name;
                            }
                            if(!empty($listing_id)) {
                                echo 'Type: ' . implode(', ', $type_names);
                            } else {
                                echo implode(', ', $type_names);
                            }
                        } else {
                            if(!empty($listing_id)) {
                                echo 'Type: ' . homey_option('sn_type_label');
                            } else {
                                echo homey_option('sn_type_label');
                            }
                        }
                        ?> 
                    </span>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>

        </div>
    </div>
</div>