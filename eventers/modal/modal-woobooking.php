<?php
global $eventer_allowed_tags;
$eventer_id = ($event_id) ? $event_id : get_the_ID();
$original_event = eventer_wpml_original_post_id($eventer_id);
$user_fields = eventer_get_settings('ticket_booking_fields');
$custom_checkout = eventer_get_settings('eventer_woo_layout');
$offline_payment_switch = eventer_get_settings('eventer_offline_payment_switch');
$offline_payment_msg = eventer_get_settings('eventer_offline_payment_desc');
$params['offline_switch'] = $offline_payment_switch;
$params['offline_msg'] = $offline_payment_msg;
$eventer_tickets = apply_filters('eventer_get_tickets', $params);
$eventer_services = apply_filters('eventer_get_services', $params);
$payments = apply_filters('eventer_tickets_payment_fields', $params);
$eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
$woo_checkout_url = (function_exists('wc_get_page_id')) ? get_permalink(wc_get_page_id('checkout')) : '';
$eventer_url = ($woo_payment != 'on' || $woo_checkout_url == '') ? eventer_generate_endpoint_url('edate', $eventer_formatted_date, get_permalink($eventer_id)) : esc_url($woo_checkout_url);
$eventer_reg_btn = ($woo_payment != 'on' || $woo_checkout_url == '') ? esc_html__('Register', 'eventer') : esc_html__('Add Tickets', 'eventer');
$tab_class = '2';
$tab_prev_class = '1';
$booking_type = ($woo_payment == 'on') ? 'woo' : 'eventer';
$tab_counts = ($eventer_services != '') ? 0 : 1;
$non_ticket_events = ($eventer_tickets != '') ? '' : 'eventer-direct-register';
echo '<div class="eventer-modal-static eventer-front-ticket-area-dynamic" id="' . $dynamic_val . '-eventer-ticket-form">
        <div class="eventer-modal-body">
		<div class="eventer eventer-event-single">';
echo            '<h3 class="eventer-section-wise-heading">' . esc_html__('Book your tickets', 'eventer') . '</h3>';

echo            '<form data-nonce="' . wp_create_nonce("eventer_create_nonce_for_registrant") . '" data-booking="' . esc_attr($booking_type) . '" class="ticket-reg ' . esc_attr($non_ticket_events) . '" action="" method="post" id="ticket-reg">
                    <div id="reg_event_date" style="display: none">' . esc_attr(date_i18n('Y-m-d', $event_cdate)) . '</div>
                    <div id="reg_event_time" style="display: none">' . $time_slot . '</div>
                    <div id="reg_event_slot_name" style="display: none">' . $time_slot_title . '</div>
                        <div id="paypal_itemname" style="display: none">' . apply_filters('eventer_raw_event_title', '', get_the_ID()) . '-' . esc_attr(date_i18n('Y-m-d', $event_cdate)) . '</div>
                        <div id="paypal_return" style="display: none">' . esc_url($eventer_url) . '</div>
                        <div id="eventer_id" style="display: none">' . esc_attr($eventer_id) . '</div>
                        <div class="eventer-toggle-area">';
if ($eventer_tickets != '') {
   $payment_fields_tickets = ($eventer_services == '' && $woo_payment != 'on') ? $payments : '';
   $tab_class = '2';
   $tab_prev_class = '1';
   echo                    '<div class="eventer-ticket-step1">
                                <div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
                                ' . wp_kses($eventer_tickets, $eventer_allowed_tags) .
      $payment_fields_tickets;

   echo                        '<div class="eventer-spacer-30"></div>';
   if ($payment_fields_tickets != '' || ($eventer_services == '' && $woo_payment == 'on')) {
      echo                    '<input class="eventer-btn save-registrant" disabled type="submit" data-payment="" value="' . esc_attr($eventer_reg_btn) . '">';
   }
   if ($eventer_services != '') {
      echo                       '<a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger add_services_btn disableClick" data-eventer-toggle-in=".eventer-ticket-step' . esc_attr(intval(2) - intval($tab_counts)) . '" data-eventer-toggle-out=".eventer-ticket-step1, .eventer-ticket-step' . esc_attr(intval(3) - intval($tab_counts)) . ', .eventer-ticket-step' . esc_attr(intval(4) - intval($tab_counts)) . '" data-eventer-dialog-heading="' . esc_html__('Add services', 'eventer') . '">' . esc_html__('Add Services', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>';
   } else {
      echo                       '<a style="display:none;" href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger eventer-show-order-summary" data-eventer-toggle-in=".eventer-ticket-step' . esc_attr(intval(3) - intval($tab_counts)) . '" data-eventer-toggle-out=".eventer-ticket-step' . esc_attr(intval(2) - intval($tab_counts)) . ', .eventer-ticket-step1, .eventer-ticket-step' . esc_attr(intval(4) - intval($tab_counts)) . '" data-eventer-dialog-heading="' . esc_html__('Order Summary', 'eventer') . '">' . esc_html__('Order Summary', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>';
   }

   echo                    '</div>';
} else {
   echo                    '<input type="hidden" value="1" class="num-tickets">';
   echo                    '<input class="eventer-btn save-registrant" type="submit" data-payment="" value="' . esc_attr($eventer_reg_btn) . '">';
}

if ($eventer_services != '') {
   $payment_fields_tickets = ($woo_payment != 'on') ? $payments : '';
   echo                 '<div class="eventer-ticket-step' . esc_attr($tab_class) . '">
                              <div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
                              ' . $eventer_services .
      $payment_fields_tickets . '
                              <div class="eventer-spacer-30"></div>
                              <a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-ticket-step1" data-eventer-toggle-out=".eventer-ticket-step2, .eventer-ticket-step3, .eventer-ticket-step4"  data-eventer-dialog-heading="' . esc_html__('Book your tickets', 'eventer') . '"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back', 'eventer') . '</a>';
   echo                    '<input class="eventer-btn save-registrant" type="submit" data-payment="" value="' . esc_attr($eventer_reg_btn) . '">';
   echo                    '<a style="display:none;" href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger eventer-show-order-summary" data-eventer-toggle-in=".eventer-ticket-step3" data-eventer-toggle-out=".eventer-ticket-step2, .eventer-ticket-step1, .eventer-ticket-step4" data-eventer-dialog-heading="' . esc_html__('Order Summary', 'eventer') . '">' . esc_html__('Order Summary', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>
                           </div>';
}
echo                    '<div class="message"></div>';
$payment_fields_tickets = ($woo_payment != 'on') ? $payments : '';
echo                    '<div class="eventer-ticket-step' . esc_attr(intval(3) - intval($tab_counts)) . '">
                              <div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>';
echo                       '<a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger add_services_btn disableClick eventer-woo-checkout-section" data-eventer-toggle-in=".eventer-ticket-step' . esc_attr(intval(2) - intval($tab_counts)) . '" data-eventer-toggle-out=".eventer-ticket-step' . esc_attr(intval(3) - intval($tab_counts)) . ', .eventer-ticket-step' . esc_attr(intval(4) - intval($tab_counts)) . ', .eventer-ticket-step1"  data-eventer-dialog-heading="' . esc_html__('Add Services', 'eventer') . '"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back', 'eventer') . ' </a>';
if ($custom_checkout == 'checkout') {
   global $woocommerce;
   $checkout_url = function_exists('wc_get_cart_url') ? wc_get_checkout_url() : $woocommerce->cart->get_checkout_url();
   echo                       '<a href="' . esc_url($checkout_url) . '" class="eventer-btn eventer-btn-basic" data-eventer-dialog-heading="' . esc_html__('Proceed to Checkout', 'eventer') . '">' . esc_html__('Checkout', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>
                           </div></form>';
} else {
   echo                       '<a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger add_services_btn disableClick" data-eventer-toggle-in=".eventer-ticket-step' . esc_attr(intval(4) - intval($tab_counts)) . '" data-eventer-toggle-out=".eventer-ticket-step' . esc_attr(intval(3) - intval($tab_counts)) . ', .eventer-ticket-step' . esc_attr(intval(2) - intval($tab_counts)) . ', .eventer-ticket-step1" data-eventer-dialog-heading="' . esc_html__('Proceed to Checkout', 'eventer') . '">' . esc_html__('Checkout', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>
   </div></form>';
}


echo                    '<div class="eventer-ticket-step' . esc_attr(intval(4) - intval($tab_counts)) . '" style="display:none;">';

echo '<div class="eventer_checkout_payment_options">';

echo do_shortcode('[woocommerce_checkout]');
echo '</div>';
echo                    '<a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger add_services_btn disableClick" data-eventer-toggle-in=".eventer-ticket-step' . esc_attr(intval(3) - intval($tab_counts)) . '" data-eventer-toggle-out=".eventer-ticket-step1, .eventer-ticket-step' . esc_attr(intval(2) - intval($tab_counts)) . ', .eventer-ticket-step' . esc_attr(intval(4) - intval($tab_counts)) . '"  data-eventer-dialog-heading="' . esc_html__('Order Summary', 'eventer') . '"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back', 'eventer') . ' </a>';

echo         '</div>';
echo        '</div>
			</div>
            </div>
		</div>';
