<?php
/***************************************************************************/
/********   Check Booking Availability On Date Change   *********/
/***************************************************************************/
function check_booking_availability_on_date_change() {

    $local = homey_get_localization();
    $allowded_html = array();
    $booking_proceed = true;

    $listing_id = intval($_POST['listing_id']);
    $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
    $check_out_date = wp_kses($_POST['check_out_date'], $allowded_html);
    $start_hour = wp_kses($_POST['start_hour'], $allowded_html); 
    $end_hour = wp_kses($_POST['end_hour'], $allowded_html);
    $total_hours = 0;

    $min_booking_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);

    $booking_type = homey_booking_type_by_id($listing_id);

    if(empty($check_out_date) && empty($check_in_date)) {
        echo json_encode(
            array(
                'success' => false,
                'message' => 'Please select check in and check out date',
            )
        );
        wp_die();
    }else{
        if ($booking_type == "per_day_date" && strtotime($check_out_date) < strtotime($check_in_date)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['ins_book_proceed']
                )
            );
            wp_die();
        }
    }

    if(empty($start_hour) || empty($end_hour)) {
        echo json_encode(
            array(
                'success' => false,
                'message' => 'Please select start and end time',
            )
        );
        wp_die();
    }

    // Calculate total hours between dates and times
    if(!empty($start_hour) && !empty($end_hour) && !empty($check_in_date) && !empty($check_out_date)) {

        $total_hours = homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour);

        if ($min_booking_hours > $total_hours) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Minimum booking is ' . $min_booking_hours . ' hours'
                )
            );
            wp_die();
        }
    }

    echo json_encode(
        array(
            'success' => true,
            'message' => $local['dates_available'],
            'total_hours' => $total_hours
        )
    );
    wp_die();
}

function homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour){
    // Format datetime strings properly
    $start_date_time = date('Y-m-d H:i:s', strtotime($check_in_date . ' ' . $start_hour));
    $end_date_time = date('Y-m-d H:i:s', strtotime($check_out_date . ' ' . $end_hour));

    $start_datetime = strtotime($start_date_time);
    $end_datetime = strtotime($end_date_time);
    
    // Calculate total hours between start and end datetime
    if ($start_datetime && $end_datetime) {

        $total_hours = ceil(($end_datetime - $start_datetime) / 3600);
        if ($total_hours < 0) {
            $total_hours = 0;
        }
    } else {
        $total_hours = 0;
    }

    return $total_hours;
}

/***************************************************************************/
/********   Calculate Booking Cost   *********/
/***************************************************************************/
add_action('wp_ajax_nopriv_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax');
add_action('wp_ajax_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax');

if (!function_exists('homey_calculate_booking_cost_ajax')) {
    function homey_calculate_booking_cost_ajax()
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id = intval($_POST['listing_id']);
        $check_in_date = wp_kses($_POST['check_in_date'], $allowded_html);
        $check_out_date = wp_kses($_POST['check_out_date'], $allowded_html);
        $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guests = intval($_POST['guests']);
        $start_hour = wp_kses($_POST['start_hour'], $allowded_html); 
        $end_hour = wp_kses($_POST['end_hour'], $allowded_html);

        $booking_type = homey_booking_type_by_id($listing_id);

        if ($booking_type == 'per_week') {
            homey_calculate_booking_cost_ajax_weekly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        } else if ($booking_type == 'per_month') {
            homey_calculate_booking_cost_ajax_monthly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        } else if ($booking_type == 'per_day_date') {
            homey_calculate_booking_cost_ajax_day_date_child($listing_id, $check_in_date, $check_out_date, $start_hour, $end_hour, $guests, $extra_options);
        } else {
            homey_calculate_booking_cost_ajax_nightly($listing_id, $check_in_date, $check_out_date, $guests, $extra_options);
        }

        wp_die();

    }
}

function homey_calculate_booking_cost_ajax_day_date_child($listing_id, $check_in_date, $check_out_date, $start_hour, $end_hour, $guests, $extra_options) {

    $prefix = 'homey_';
    $local = homey_get_localization();
    $allowded_html = array();
    $output = '';

    $total_hours = homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour);

    $prices_array = homey_get_prices_child($check_in_date, $check_out_date, $start_hour, $end_hour, $listing_id, $guests, $extra_options);

    $nights_total_price_li_html = $prices_array['nights_total_price_li_html'];
    $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
    $no_of_days = $prices_array['days_count'];

    $guest_price = $prices_array['guest_price'];

    $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

    $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
    $services_fee = $prices_array['services_fee'];
    $taxes = $prices_array['taxes'];
    $taxes_percent = $prices_array['taxes_percent'];
    $city_fee = homey_formatted_price($prices_array['city_fee']);
    $security_deposit = $prices_array['security_deposit'];
    $additional_guests = $prices_array['additional_guests'];
    $additional_guests_price = $prices_array['additional_guests_price'];
    $additional_guests_total_price = $prices_array['additional_guests_total_price'];

    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];

    $extra_prices_html = $prices_array['extra_prices_html'];
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];

    if ($no_of_days > 1) {
        $night_label = homey_option('glc_day_nights_label');
    } else {
        $night_label = homey_option('glc_day_night_label');
    }

    if ($additional_guests > 1) {
        $add_guest_label = $local['cs_add_guests'];
    } else {
        $add_guest_label = $local['cs_add_guest'];
    }

    $output = '<div class="payment-list-price-detail clearfix">';

    if (isset($prices_array['breakdown_price'])) {
        $output .= '<div style="display:none;">' . $prices_array['breakdown_price'] . '</div>';
    }

    $output .= '<div class="pull-left">';
    $output .= '<div class="payment-list-price-detail-total-price">' . esc_attr($local['cs_total']) . '</div>';
    $output .= '<div class="payment-list-price-detail-note">' . esc_attr($local['cs_tax_fees']) . '</div>';
    $output .= '</div>';

    $output .= '<div class="pull-right text-right">';
    $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
    $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . esc_attr($local['cs_view_details']) . '</a>';
    $output .= '</div>';
    $output .= '</div>';

    $output .= '<div class="collapse collapseExample" id="collapseExample">';
    $output .= '<ul>';

    if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

    } elseif ($booking_has_weekend == 1) {
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $nights_total_price . '</span></li>';

    } elseif ($booking_has_custom_pricing == 1) {
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

    } else {
        // $output .= '<li>' . $price_per_night . ' x ' . $no_of_days . ' ' . $night_label . ' <span>' . $nights_total_price . '</span></li>';
        if($total_hours > 1){
            $night_label = 'Hours';
        }else{
            $night_label = 'Hour';
        }
        $nights_total_price = $prices_array['total_hours_price'];
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. ($price_per_night) . ' x ' . esc_attr($total_hours) . ' ' . esc_attr($night_label) . ' <span>' . homey_formatted_price($nights_total_price) . '</span></li>';
    }

    if (!empty($guest_price)) {
        $output .= '<li>' . esc_attr('Guest Price') . ' <span>' . homey_formatted_price($guest_price) . '</span></li>';
    }

    if (!empty($additional_guests)) {
        $output .= '<li>' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
    }

    if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
        $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . ($cleaning_fee) . '</span></li>';
    }

    if (!empty($extra_prices_html)) {
        $output .= $extra_prices_html;
    }

    $services_fee = $services_fee > 0 ? $services_fee : 0;
    $sub_total_amnt = $total_price - $prices_array['city_fee'] - $security_deposit - $services_fee - $taxes;
    $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

    if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
        $output .= '<li>' . esc_attr($local['cs_city_fee']) . ' <span>' . ($city_fee) . '</span></li>';
    }

    if (!empty($security_deposit) && $security_deposit != 0) {
        $output .= '<li>' . esc_attr($local['cs_sec_deposit']) . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
    }

    if (!empty($services_fee) && $services_fee != 0) {
        $output .= '<li>' . esc_attr($local['cs_services_fee']) . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
    }

    if (!empty($taxes) && $taxes != 0) {
        $output .= '<li>' . esc_attr($local['cs_taxes']) . ' ' . esc_attr($taxes_percent) . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
    }

    $avg_price = homey_formatted_price(0);
    if (!empty($upfront_payment) && $upfront_payment != 0) {
        $curncy = homey_get_currency(1);

        $avg_price = $curncy . ' ' . $upfront_payment / $no_of_days;

        $avg_price .= ' <sub> /';
        $avg_price .= esc_html__('Average Night', 'homey');
        $avg_price .= '</sub>';

        $output .= '<li class="payment-due">' . esc_attr($local['cs_payment_due']) . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
        $output .= '<input data-avg-price="' . $avg_price . '" type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
    }

    $output .= '</ul>';
    $output .= '</div>';

    // This variable has been safely escaped in same file: Line: 1071 - 1128
    $output_escaped = $output;
    print '' . $output_escaped;

    wp_die();

}

function homey_get_prices_child($check_in_date, $check_out_date, $start_hour, $end_hour, $listing_id, $guests, $extra_options = null) {

    $prefix = 'homey_';

    $enable_services_fee = homey_option('enable_services_fee');
    $enable_taxes = homey_option('enable_taxes');
    $offsite_payment = homey_option('off-site-payment');
    $reservation_payment_type = homey_option('reservation_payment');
    $booking_percent = homey_option('booking_percent');
    $tax_type = homey_option('tax_type');
    $apply_taxes_on_service_fee = homey_option('apply_taxes_on_service_fee');
    $taxes_percent_global = homey_option('taxes_percent');
    $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);
    $total_hours = 0;

    $total_hours = homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour);

    $period_price = get_post_meta($listing_id, 'homey_custom_period', true);

    if (empty($period_price)) {
        $period_price = array();
    }

    $total_extra_services = 0;
    $extra_prices_html = "";
    $taxes_final = 0;
    $taxes_percent = 0;
    $total_price = 0;
    $total_guests_price = 0;
    $upfront_payment = 0;
    $nights_total_price = 0;
    $nights_total_price_li_html = '<ul style="display: none;">';
    $booking_has_weekend = 0;
    $booking_has_custom_pricing = 0;
    $balance = 0;
    $taxable_amount = 0;
    $period_days = 0;
    $security_deposit = '';
    $additional_guests = '';
    $additional_guests_total_price = '';
    $services_fee_final = '';
    $taxes_fee_final = '';
    $prices_array = array();

    $listing_guests = floatval(get_post_meta($listing_id, $prefix . 'guests', true));
    $nightly_price = floatval(get_post_meta($listing_id, $prefix . 'night_price', true));
    $price_per_night = $nightly_price;
    $weekends_price = floatval(get_post_meta($listing_id, $prefix . 'weekends_price', true));
    $weekends_days = get_post_meta($listing_id, $prefix . 'weekends_days', true);
    $priceWeek = floatval(get_post_meta($listing_id, $prefix . 'priceWeek', true)); // 7 Nights
    $priceMonthly = floatval(get_post_meta($listing_id, $prefix . 'priceMonthly', true));  // 30 Nights
    $security_deposit = floatval(get_post_meta($listing_id, $prefix . 'security_deposit', true));

    $cleaning_fee = floatval(get_post_meta($listing_id, $prefix . 'cleaning_fee', true));
    $cleaning_fee_type = get_post_meta($listing_id, $prefix . 'cleaning_fee_type', true);

    $city_fee = floatval(get_post_meta($listing_id, $prefix . 'city_fee', true));
    $city_fee_type = get_post_meta($listing_id, $prefix . 'city_fee_type', true);

    $extra_guests_price = floatval(get_post_meta($listing_id, $prefix . 'additional_guests_price', true));
    $additional_guests_price = $extra_guests_price;


    $allow_additional_guests = get_post_meta($listing_id, $prefix . 'allow_additional_guests', true);

    $check_in = new DateTime($check_in_date);
    $check_in_unix = $check_in->getTimestamp();
    $check_in_unix_first_day = $check_in->getTimestamp();
    $check_out = new DateTime($check_out_date);
    $check_out_unix = $check_out->getTimestamp();

    $time_difference = abs(strtotime($check_in_date) - strtotime($check_out_date));
    $days_count = $time_difference / 86400;
    $days_count = intval($days_count);
    $breakdown_price = '';

    if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {
        $price_per_night = $period_price[$check_in_unix]['night_price'];

        $booking_has_custom_pricing = 1;
        $period_days = $period_days + 1;
    }

    if ($days_count >= 7 && $priceWeek != 0) {
        $price_per_night = $priceWeek;
    }

    if ($days_count >= 30 && $priceMonthly != 0) {
        $price_per_night = $priceMonthly;
    }

    // Check additional guests price
    if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
        if ($guests > $listing_guests) {
            $additional_guests = $guests - $listing_guests;

            $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);
            $breakdown_price .= ', total_guests_price prev price=' . $total_guests_price . ' + weekend or reg price=' . $guests_price_return . '<br>';

            $total_guests_price = $total_guests_price + $guests_price_return;
        }
    }

    $breakdown_price .= ' * This first date * ' . date('d-m-Y', $check_in_unix) . '<br>';

    $weekday = date('N', $check_in_unix_first_day);
    if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
        $booking_has_weekend = 1;
    }

    if ($booking_has_weekend != 1 && isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {
        $returnPrice = $period_price[$check_in_unix]['night_price'];
    } else {
        $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);
    }

    // echo  ' first night price= '. $returnPrice.'<br>';
    $nights_total_price = $nights_total_price + $returnPrice;
    $html_date_text = date('Y-m-d', $check_in_unix);
    $nights_total_price_li_html .= '<li class="out-loop">Price '.$returnPrice .' for '.$html_date_text.'</li>';

    $check_in->modify('tomorrow');
    $check_in_unix = $check_in->getTimestamp();

    $total_price = $total_price + $returnPrice;

    $total_price = $total_price * $total_hours;

    $total_hours_price = $total_price;

    // TOTAL GUEST RPICES
    $total_guests_price = $guests;
    $total_price = $guests + $total_price;
    $amount_after_guests = $total_price;

    // $current_index = 0;
    // while ($check_in_unix < $check_out_unix) {
    //     // echo ' * This date * '.date('d-m-Y',$check_in_unix).'<br>';
    //     $current_index++;

    //     $weekday = date('N', $check_in_unix);
    //     if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
    //         $booking_has_weekend = 1;
    //     }

    //     if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {

    //         $price_per_night = $period_price[$check_in_unix]['night_price'];
    //         //echo 'cond> <pre>  if( isset('.$period_price[$check_in_unix].') && isset('. $period_price[$check_in_unix]['night_price'] .') && '. $period_price[$check_in_unix]['night_price'] .'!=0 ){';
    //         //print_r($period_price[$check_in_unix]);
    //         $breakdown_price .= date('d-m-Y', $check_in_unix) . ' its custom pr ' . $price_per_night . ' custom price <br>';

    //         $booking_has_custom_pricing = 1;
    //         $period_days = $period_days + 1;
    //     } else {
    //         if ($days_count >= 7 && $priceWeek != 0) {
    //             //do the logic
    //         } else if ($days_count >= 30 && $priceMonthly != 0) {
    //             //do the logic
    //         } else {
    //             $price_per_night = $nightly_price; // this creates issue for 7+ and 30+ nights issue
    //         }
    //     }

    //     // To make this per night per additional guest, we added a condition > 1 night, because once it is added
    //     // if ($current_index > 0 && $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
    //     if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
    //         if ($guests > $listing_guests) {
    //             $additional_guests = $guests - $listing_guests;

    //             $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

    //             $breakdown_price .= ', prev price=' . $total_guests_price . ' + guest price=' . $guests_price_return . '<br>';

    //             $total_guests_price = $total_guests_price + $guests_price_return;
    //         }
    //     } // end To make this per night per additional guest, we added a condition > 1 night, because once it is added

    //     $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

    //     // echo ' the day => price='. $returnPrice.'<br>';

    //     $nights_total_price = $nights_total_price + $returnPrice;
    //     $html_date_text = date('Y-m-d', $check_in_unix);
    //     $nights_total_price_li_html .= '<li class="in-loop">Price '.$returnPrice .' for '.$html_date_text.'</li>';

    //     $total_price = $total_price + $returnPrice;
    //     $breakdown_price .= date('d-m-Y', $check_in_unix) . ' < date ' . $total_price . ' < total price <br>';

    //     $check_in->modify('tomorrow');
    //     $check_in_unix = $check_in->getTimestamp();

    // }

    if ($cleaning_fee_type == 'daily') {
        $cleaning_fee = $cleaning_fee * $days_count;
        $total_price = $total_price + $cleaning_fee;
    } else {
        $total_price = $total_price + $cleaning_fee;
    }


    //Extra prices =======================================
    if ($extra_options != '' && is_array($extra_options)) {

        $extra_prices_output = '';
        $is_first = 0;
        if (is_array($extra_options) || is_object($extra_options)) {
            foreach ($extra_options as $extra_price) {
                if ($is_first == 0) {
                    $extra_prices_output .= '<li class="sub-total">' . esc_html__('Add Ons', 'homey-child') . '</li>';
                }
                $is_first = 2;

                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = floatval($ex_single_price[1]);
                
                // $ex_type = $ex_single_price[2];
                $ex_type = 'per_night';

                if ($ex_type == 'single_fee') {
                    $ex_price = $ex_price;

                } elseif ($ex_type == 'per_night') {
                    $ex_price = $ex_price * $days_count;
                } elseif ($ex_type == 'per_guest') {
                    $ex_price = $ex_price * $guests;
                } elseif ($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price * $days_count * $guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>' . esc_attr($ex_name) . ' (' . $ex_price/$days_count . ' x ' . $days_count . ')' . '<span>' . homey_formatted_price($ex_price) . '</span></li>';
            }
        }

        $total_price = $total_price + $total_extra_services;
        $extra_prices_html = $extra_prices_output;
    }

    //Calculate taxes based of original price (Excluding city, security deposit etc)
    if ($enable_taxes == 1) {

        if ($tax_type == 'global_tax') {
            $taxes_percent = $taxes_percent_global;
        } else {
            if (!empty($single_listing_tax)) {
                $taxes_percent = $single_listing_tax;
            }
        }

        $taxable_amount = $total_price;
        $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
        $total_price = $total_price + $taxes_final;
    }

    //Calculate sevices fee based of original price ( guests price + extra prices ) (Excluding cleaning, city fee etc)
    if ($enable_services_fee == 1 && $offsite_payment != 1) {
        $services_fee_type = homey_option('services_fee_type');
        $services_fee = homey_option('services_fee');
        $price_for_services_fee = $total_price + $total_guests_price;
        $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
        $total_price = (float) $total_price + (float) $services_fee_final;
    }

    $total_guests_with_additional = (int) $guests + (int) $additional_guests;

    if ($city_fee_type == 'daily') {
        $city_fee = $city_fee * $days_count;
        $total_price = $total_price + $city_fee;
    } elseif ($city_fee_type == 'per_guest') {
        $city_fee = $city_fee * $total_guests_with_additional;
        $total_price = $total_price + $city_fee;
    } elseif ($city_fee_type == 'daily_per_guest') {
        $city_fee = $city_fee * $days_count * $total_guests_with_additional;
        $total_price = $total_price + $city_fee;
    } else {
        $total_price = $total_price + $city_fee;
    }

    if (!empty($security_deposit) && $security_deposit != 0) {
        $total_price = $total_price + $security_deposit;
    }

    // if ($total_guests_price != 0) {
    //     $total_price = $total_price + $total_guests_price;
    // }

    $listing_host_id = get_post_field('post_author', $listing_id);
    $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
    $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

    if ($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

        if ($host_reservation_payment_type == 'percent') {
            if (!empty($host_booking_percent) && $host_booking_percent != 0) {
                $upfront_payment = round($host_booking_percent * $total_price / 100, 2);
            }

        } elseif ($host_reservation_payment_type == 'full') {
            $upfront_payment = $total_price;

        } elseif ($host_reservation_payment_type == 'only_security') {
            $upfront_payment = $security_deposit;

        } elseif ($host_reservation_payment_type == 'only_services') {
            $upfront_payment = $services_fee_final;

        } elseif ($host_reservation_payment_type == 'services_security') {
            $upfront_payment = $security_deposit + $services_fee_final;
        }

    } else {

        if ($reservation_payment_type == 'percent') {
            if (!empty($booking_percent) && $booking_percent != 0) {
                $upfront_payment = round($booking_percent * $total_price / 100, 2);
            }

        } elseif ($reservation_payment_type == 'full') {
            $upfront_payment = $total_price;

        } elseif ($reservation_payment_type == 'only_security') {
            $upfront_payment = $security_deposit;

        } elseif ($reservation_payment_type == 'only_services') {
            $upfront_payment = $services_fee_final;

        } elseif ($reservation_payment_type == 'services_security') {
            $upfront_payment = (int)$security_deposit + (int)$services_fee_final;
        }
    }

    $balance = $total_price - $upfront_payment;
    $nights_total_price_li_html .= '</ul>';

    $prices_array['price_per_hour'] = $price_per_night;
    $prices_array['total_hours'] = $total_hours;
    $prices_array['total_hours_price'] = $total_hours_price;
    $prices_array['guest_price'] = $total_guests_price;
    $prices_array['amount_after_guests'] = $amount_after_guests;

    $prices_array['breakdown_price'] = $breakdown_price;
    $prices_array['price_per_night'] = $price_per_night;
    $prices_array['nights_total_price'] = $nights_total_price;
    $prices_array['nights_total_price_li_html'] = $nights_total_price_li_html;
    $prices_array['total_price'] = $total_price;
    $prices_array['check_in_date'] = $check_in_date;
    $prices_array['check_out_date'] = $check_out_date;
    $prices_array['cleaning_fee'] = $cleaning_fee;
    $prices_array['city_fee'] = $city_fee;
    $prices_array['services_fee'] = $services_fee_final;
    $prices_array['days_count'] = $days_count;
    $prices_array['period_days'] = $period_days;
    $prices_array['taxes'] = $taxes_final;
    $prices_array['taxes_percent'] = $taxes_percent;
    $prices_array['security_deposit'] = $security_deposit;
    $prices_array['additional_guests'] = $additional_guests;
    $prices_array['additional_guests_price'] = $additional_guests_price;
    $prices_array['additional_guests_total_price'] = $total_guests_price;
    $prices_array['booking_has_weekend'] = $booking_has_weekend;
    $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
    $prices_array['extra_prices_html'] = $extra_prices_html;
    $prices_array['balance'] = $balance;
    $prices_array['upfront_payment'] = $upfront_payment;

    return $prices_array;

}