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

/***************************************************************************/
/********   Calculate Booking Cost   *********/
/***************************************************************************/
function homey_calculate_booking_cost($reservation_id, $collapse = false) {

    $prefix = 'homey_';
    $local = homey_get_localization();
    $allowded_html = array();
    $output = '';

    if (empty($reservation_id)) {
        return;
    }
    $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
    $extra_options = get_post_meta($reservation_id, 'extra_options', true);

    $listing_id = intval($reservation_meta['listing_id']);
    $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
    $check_out_date = wp_kses($reservation_meta['check_out_date'], $allowded_html);
    $guests = intval($reservation_meta['guests']);

    $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);

    $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
    $no_of_days = $prices_array['days_count'];

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

    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];

    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];

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

    $start_div = '<div class="payment-list">';

    if ($collapse) {
        $output = '<div class="payment-list-price-detail clearfix">';
        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">' . $local['cs_total'] . '</div>';
        $output .= '<div class="payment-list-price-detail-note">' . $local['cs_tax_fees'] . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . $local['cs_view_details'] . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $start_div = '<div class="collapse collapseExample" id="collapseExample">';
    }


    $output .= $start_div;
    $output .= '<ul>';

    if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
        $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $local['with_custom_period_and_weekend_label'] . ') <span>' . $nights_total_price . '</span></li>';

    } elseif ($booking_has_weekend == 1) {
        $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $with_weekend_label . ') <span>' . $nights_total_price . '</span></li>';

    } elseif ($booking_has_custom_pricing == 1) {
        $output .= '<li>' . $no_of_days . ' ' . $night_label . ' (' . $local['with_custom_period_label'] . ') <span>' . $nights_total_price . '</span></li>';

    } else {
        $output .= '<li>' . $price_per_night . ' x ' . $no_of_days . ' ' . $night_label . ' <span>' . $nights_total_price . '</span></li>';
    }

    if (!empty($additional_guests)) {
        $output .= '<li>' . $additional_guests . ' ' . $add_guest_label . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
    }

    if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
        $output .= '<li>' . $local['cs_cleaning_fee'] . ' <span>' . $cleaning_fee . '</span></li>';
    }

    if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
        $output .= '<li>' . $local['cs_city_fee'] . ' <span>' . $city_fee . '</span></li>';
    }

    if (!empty($security_deposit) && $security_deposit != 0) {
        $output .= '<li>' . $local['cs_sec_deposit'] . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
    }

    if (!empty($services_fee) && $services_fee != 0) {
        $output .= '<li>' . $local['cs_services_fee'] . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
    }

    if (!empty($taxes) && $taxes != 0) {
        $output .= '<li>' . $local['cs_taxes'] . ' ' . $taxes_percent . '% <span>' . homey_formatted_price($taxes) . '</span></li>';
    }

    if (!empty($upfront_payment) && $upfront_payment != 0) {
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
        $paid_or_due = $reservation_status == 'booked' ? $local['paid'] : $local['cs_payment_due'];
        $output .= '<li class="payment-due">' . $paid_or_due . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
        $output .= '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
    }

    if (!empty($balance) && $balance != 0) {
        $output .= '<li><i class="homey-icon homey-icon-information-circle"></i> ' . $local['cs_pay_rest_1'] . ' ' . homey_formatted_price($balance) . ' ' . $local['cs_pay_rest_2'] . '</li>';
    }

    $output .= '</ul>';
    $output .= '</div>';

    return $output;
}