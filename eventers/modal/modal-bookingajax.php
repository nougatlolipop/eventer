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
echo wp_kses($eventer_tickets, $eventer_allowed_tags);
