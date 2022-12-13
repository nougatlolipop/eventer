<?php
global $eventer_allowed_tags;
$eventer_id = ($event_id) ? $event_id : get_the_ID();
$original_event = eventer_wpml_original_post_id($eventer_id);
$user_fields = eventer_get_settings('ticket_booking_fields');
$custom_registration_form = get_post_meta($eventer_id, 'eventer_event_registration_form', true);
$user_fields = ($custom_registration_form != '') ? $custom_registration_form : $user_fields;
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
$eventer_reg_btn = ($woo_payment != 'on' || $woo_checkout_url == '') ? esc_html__('Register', 'eventer') : esc_html__('Proceed to checkout', 'eventer');
$tab_class = '2';
$tab_prev_class = '1';
$booking_type = ($woo_payment == 'on') ? 'woo' : 'eventer';
if ($params['ajax'] == '1') {
  wp_kses($eventer_tickets, $eventer_allowed_tags);
  return;
}
$non_ticket_events = ($eventer_tickets != '') ? '' : 'eventer-direct-register';
$registrants_form = '<h3>' . esc_html__('Book your tickets', 'eventer') . '</h3>
                        <form data-nonce="' . wp_create_nonce("eventer_create_nonce_for_registrant") . '" data-booking="' . esc_attr($booking_type) . '" class="ticket-reg ' . esc_attr($non_ticket_events) . '" action="" method="post" id="ticket-reg">
                            <div id="reg_event_date" style="display: none">' . esc_attr(date_i18n('Y-m-d', $event_cdate)) . '</div>
                            <div id="reg_event_time" style="display: none">' . $time_slot . '</div>
                            <div id="reg_event_slot_name" style="display: none">' . $time_slot_title . '</div>
                            <div id="paypal_itemname" style="display: none">' . apply_filters('eventer_raw_event_title', '', $eventer_id) . '-' . esc_attr(date_i18n('Y-m-d', $event_cdate)) . '</div>
                            <div id="paypal_return" style="display: none">' . esc_url($eventer_url) . '</div>
                            <div class="eventer-toggle-area">';
$registrants_form .= '<div class="eventer-ticket-step1">';
if (strpos($user_fields, 'reg_name') == false && strpos($user_fields, 'reg_email') == false) {
  $registrants_form .= '<div class="eventer-row">
                                        <div class="eventer-col5 eventer-col10-xs">
                                            <label>' . esc_html__('First name', 'eventer') . '*</label>
                                            <input data-required="1" id="reg_name" class="" type="text" name="' . esc_html__('Name', 'eventer') . '">
                                        </div>
                                        <div class="eventer-col5 eventer-col10-xs">
                                            <label>' . esc_html__('Your email', 'eventer') . '*</label>
                                            <input data-required="1" id="reg_email" class="" type="email" name="' . esc_html__('email', 'eventer') . '">
                                        </div>
                                    </div>';
}
$registrants_form .= do_shortcode($user_fields) . '
                                    <div id="eventer_id" style="display: none">' . esc_attr($eventer_id) . '</div>
                                    <!--<label class="eventer-checkbox"><input type="checkbox" id="register_reg" name="register-reg"> ' . esc_html__('Register for website', 'eventer') . '</label>-->';
if ($eventer_tickets != '') {
  $registrants_form .= '<a href="" class="eventer-btn eventer-toggle-area-trigger-custom validate-registrant" data-eventer-toggle-in=".eventer-ticket-step2" data-eventer-toggle-out=".eventer-ticket-step1, .eventer-ticket-step3">' . esc_html__('Choose tickets', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>';
}
$registrants_form .= '</div>';
if ($eventer_tickets != '') {
  $payment_fields_tickets = ($eventer_services == '' && $woo_payment != 'on') ? $payments : '';
  $tab_class = '3';
  $tab_prev_class = '2';
  $registrants_form .= '<div class="eventer-ticket-step2">
                                    <div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
                                    ' . wp_kses($eventer_tickets, $eventer_allowed_tags) .
    $payment_fields_tickets;

  $registrants_form .= '<div class="eventer-spacer-30"></div>
                                    <a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-ticket-step1" data-eventer-toggle-out=".eventer-ticket-step2, .eventer-ticket-step3"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back to details', 'eventer') . '</a>';
  if ($eventer_services != '') {
    $registrants_form .= '<a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger add_services_btn disableClick" data-eventer-toggle-in=".eventer-ticket-step3" data-eventer-toggle-out=".eventer-ticket-step2, .eventer-ticket-step1">' . esc_html__('Add Services', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a>';
  }
  if ($payment_fields_tickets != '' || ($eventer_services == '' && $woo_payment == 'on')) {
    $stripe_switch = eventer_get_settings('eventer_stripe_payment_switch');
    $stripe_default = eventer_get_settings('eventer_stripe_default_switch');
    $stripe_class = ($stripe_switch != '0' && $stripe_default == '1') ? 'eventer-stripe-trigger' : '';
    $registrants_form .= '<input class="eventer-btn save-registrant ' . esc_attr($stripe_class) . '" disabled type="submit" data-payment="" value="' . esc_attr($eventer_reg_btn) . '">';
  }
  if ($woo_payment == 'on') {
    $registrants_form .= '<div class="eventer-ticket-price-total" data-fprice="">' . esc_attr($eventer_currency) . '0</div>';
  }

  $registrants_form .= '</div>';
} else {
  $registrants_form .= '<input type="hidden" value="1" class="num-tickets">';
  $registrants_form .= '<input class="eventer-btn save-registrant" type="submit" data-payment="" value="' . esc_attr($eventer_reg_btn) . '">';
}

if ($eventer_services != '') {
  $payment_fields_tickets = ($woo_payment != 'on') ? $payments : '';
  $registrants_form .= '<div class="eventer-ticket-step' . esc_attr($tab_class) . '">
                                        <div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
							' . $eventer_services .
    $payment_fields_tickets . '
                                        <div class="eventer-spacer-30"></div>
                                        <a href="#" class="eventer-btn eventer-btn-basic eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-ticket-step' . esc_attr($tab_prev_class) . '" data-eventer-toggle-out=".eventer-ticket-step' . esc_attr($tab_prev_class) . ', .eventer-ticket-step' . esc_attr($tab_class) . '"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back', 'eventer') . '</a>
                                        <input class="eventer-btn save-registrant" type="submit" data-payment="" value="' . esc_attr($eventer_reg_btn) . '">
                                    </div>';
}
$registrants_form .= '</div>
                                <div class="message"></div>
                            </form>';
$registration_modal_show = '<div class="eventer-modal-static eventer-front-ticket-area-dynamic" id="' . $dynamic_val . '-eventer-ticket-form"><div class="eventer-modal-body">
						<div class="eventer eventer-event-single">';
$registration_modal_show .= $registrants_form;
$registration_modal_show .= '</div>
					</div></div>';
echo $registration_modal_show;
