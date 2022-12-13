<?php
defined('ABSPATH') or die('No script kiddies please!');
/*
 * eventer_enqueue_scripts function
 * Enqueue the style and js for front end
 * Variables of strings are used to send them in js file using wp_localize_script function, so that they can be fully translatable
 * wp_add_inline_style function used to generate dynamic css of color selected by user through settings page
 */
$stripe_switch = eventer_get_settings('eventer_stripe_payment_switch');
if ($stripe_switch == '1') {
  include('stripe/init.php');
}
if (!function_exists('eventer_enqueue_scripts')) {
  function eventer_enqueue_scripts()
  {
    $eventer_paypal_server = eventer_get_settings('eventer_paypal_payment_type');
    $eventer_dotpay_server = eventer_get_settings('eventer_dotpay_payment_type');
    $mandatory_registrations = eventer_get_settings('eventer_registrants_fields_mandatory');
    $stripe_switch = eventer_get_settings('eventer_stripe_payment_switch');
    $paypal_site = ($eventer_paypal_server == "1") ? "https://www.paypal.com/cgi-bin/webscr" : "https://www.sandbox.paypal.com/cgi-bin/webscr";
    $dotpay_site = ($eventer_dotpay_server == "1") ? "https://ssl.dotpay.pl/t2/" : "https://ssl.dotpay.pl/test_payment/";
    $stripe_site = EVENTER__PLUGIN_URL . "front/stripe.php";
    $paypal_email = eventer_get_settings('eventer_paypal_business_email');
    $dotpay_business_id = eventer_get_settings('eventer_dotpay_business_id');
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $eventer_woo_layout = eventer_get_settings('eventer_woo_layout');
    $register_status = (get_query_var('reg')) ? get_query_var('reg') : '';
    $offline_payment_switch = eventer_get_settings('eventer_offline_payment_switch');
    $all_required_msg = esc_html__("Please fill all required fields.", "eventer");
    $number_msg = esc_html__("Field Should have Numbers Only.", "eventer");
    $email_msg = esc_html__("Field Should have Email Only.", "eventer");
    $blank_payment_options = esc_html__("Please select payment options.", "eventer");
    $blank_tickets = esc_html__("Please select tickets.", "eventer");
    $paypal_proceed = esc_html__("Proceed to paypal", "eventer");
    $dotpay_proceed = esc_html__("Proceed to dotpay", "eventer");
    $stripe_proceed = esc_html__("Pay with Stripe", "eventer");
    $tickets_added = esc_html__("Tickets added.", "eventer");
    $eventer_maximum_tickets_count = eventer_get_settings('eventer_tickets_quantity_set');
    $eventer_maximum_tickets_count = ($eventer_maximum_tickets_count) ? $eventer_maximum_tickets_count : 10;
    $future_date_cal = esc_html__("Select Booking Date", "eventer");
    $proceed_register = ($woocommerce_ticketing == 'on' && $eventer_woo_layout == 'on') ? esc_html__('Add Tickets', 'eventer') : esc_html__("Register", "eventer");
    $stripe_publishable_key = eventer_get_settings('eventer_stripe_publishable_key');
    $contact_manager_text = esc_html__('Details forwarded to event manager successfully.', 'eventer');
    $google_calendar_id = eventer_get_settings('cal_id');
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    $eventer_carousel_switch = eventer_get_settings('eventer_dequeue_carousel_scripts');
    $woo_currency_position = get_option('woocommerce_currency_pos');
    $woo_currency_position = ($woo_currency_position == "left" || $woo_currency_position == "left_space") ? "suffix" : "postfix";
    $eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_currency_symbol(eventer_get_settings('eventer_paypal_currency')) : eventer_get_currency_symbol(get_option('woocommerce_currency'));

    //$woo_currency_position = ($woo_currency_position == "left" || $woo_currency_position == "left_space") ? "suffix" : "postfix";
    $eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_currency_symbol(eventer_get_settings('eventer_paypal_currency')) : eventer_get_currency_symbol(get_option('woocommerce_currency'));
    $eventer_currency_position = ($woocommerce_ticketing != 'on') ? eventer_get_settings('eventer_currency_position') : $woo_currency_position;

    $query_args = array(
      'family' => 'Oswald:400,700|Open+Sans:400,400i,700|Great+Vibes',
      'subset' => '',
    );
    $individual_field_checkbox = esc_html__('Add individual registrant details', 'eventer');
    $individual_registrants_fields = eventer_get_settings('eventer_registrants_fields');
    $individual_registrant = ($individual_registrants_fields == "on") ? 1 : '';
    $registrant_name_label = esc_html__('Registrant name', 'eventer');
    $registrant_email_label = esc_html__('Registrant email', 'eventer');
    $registrant_email = esc_html__('Email', 'eventer');
    $registrant_name = esc_html__('Name', 'eventer');
    $registrant_label = esc_html__('Registrant', 'eventer');
    $site_lang = EVENTER__LANGUAGE_CODE;
    $eventer_stored = get_option($site_lang . '_eventer_stored');
    $eventer_style = EVENTER__PLUGIN_URL . 'css/eventer_style.css';
    $woo_thanks_order = '';
    if (function_exists('is_wc_endpoint_url')) {
      $woo_thanks_order = is_wc_endpoint_url('order-received');
    }
    $registered_now = (get_query_var('reg') || $woo_thanks_order) ? '1' : '';

    //Js
    wp_enqueue_script('jquery-ui-datepicker');
    if ($stripe_switch == '1' && is_singular('eventer')) {
      wp_enqueue_script('eventer-stripe', 'https://js.stripe.com/v3/', array(), $plugin_version, false);
    }

    wp_enqueue_script('fullcalendar-moment', EVENTER__PLUGIN_URL . 'front/fullcalendar/lib/moment.min.js', array(), $plugin_version, false);
    wp_enqueue_script('eventer-plugins', EVENTER__PLUGIN_URL . 'js/plugins.js', array('jquery'), $plugin_version, true);
    wp_localize_script('eventer-plugins', 'upcoming_data', array('c_time' => date_i18n('U')));
    if ($eventer_carousel_switch != 'on') {
      wp_enqueue_script('owl-carousel-min', EVENTER__PLUGIN_URL . 'vendors/owl-carousel/js/owl.carousel.min.js', array('jquery'), $plugin_version, true);
      wp_enqueue_script('eventer-carousel-init', EVENTER__PLUGIN_URL . 'vendors/owl-carousel/js/carousel-init.js', array('jquery'), $plugin_version, true);
    }

    wp_enqueue_script('eventer-qrcode', EVENTER__PLUGIN_URL . 'js/jquery-qrcode-0.14.0.min.js', array('jquery-ui-autocomplete', 'jquery'), $plugin_version, true);
    wp_enqueue_script('eventer-modal', EVENTER__PLUGIN_URL . 'js/jquery.modal.min.js', array('jquery'), $plugin_version, true);
    //if($registered_now=='1')
    //{
    wp_enqueue_script('eventer-qrcode1', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js', array('jquery'), $plugin_version, true);
    //}

    wp_enqueue_script('eventer-init', EVENTER__PLUGIN_URL . 'js/init.js', array('jquery'), $plugin_version, true);
    wp_enqueue_script('eventer-filters', EVENTER__PLUGIN_URL . 'js/filters.js', array('jquery'), $plugin_version, true);
    wp_localize_script('eventer-filters', 'filters', array('ajax_url' => admin_url('admin-ajax.php'), 'root' => esc_url_raw(rest_url()), 'nonce' => wp_create_nonce('wp_rest')));



    $localized_data = array('ajax_url' => admin_url('admin-ajax.php'), 'future_date_cal' => $future_date_cal, 'eventer_style' => $eventer_style, 'register_status' => $register_status, 'email_msg' => $email_msg, 'number_msg' => $number_msg, 'organizer_contact' => wp_create_nonce("eventer_create_nonce_for_corganizer"), 'contact_manager_text' => $contact_manager_text, 'event_tickets_set' => $eventer_maximum_tickets_count, 'mandatory_registrants' => $mandatory_registrations, 'individual_label' => $individual_field_checkbox, 'registrant_label' => $registrant_label, 'registrant_name' => $registrant_name, 'registrant_name_label' => $registrant_name_label, 'registrant_email' => $registrant_email, 'registrant_email_label' => $registrant_email_label, 'individual_reg' => $individual_registrant, 'curr_position' => $eventer_currency_position, 'curr' => $eventer_currency, 'paypal_site' => $paypal_site, 'dotpay_site' => $dotpay_site, 'paypal_proceed' => $paypal_proceed, 'dotpay_proceed' => $dotpay_proceed, 'stripe_proceed' => $stripe_proceed, 'stripe_site' => $stripe_site, 'offline_switch' => $offline_payment_switch, 'woo_payment_switch' => $woocommerce_ticketing, 'proceed_register' => $proceed_register, 'paypal_email' => $paypal_email, 'paypal_curr' => eventer_get_settings('eventer_paypal_currency'), 'paypal_email' => $paypal_email, 'business_id' => $dotpay_business_id, 'all_required' => $all_required_msg, 'blank_payment' => $blank_payment_options, 'blank_tickets' => $blank_tickets, 'wpml_lang' => $site_lang, 'eventers_name' => '', 'eventer_stored' => $eventer_stored, 'reg_now' => $registered_now, 'tickets_added' => $tickets_added, 'stripe_publishable_key' => $stripe_publishable_key, 'stripe_switch' => $stripe_switch);
    if (function_exists('is_checkout') && is_checkout() && !empty(is_wc_endpoint_url('order-received'))) {
      $localized_event_data = array();
    } elseif (is_singular('eventer')) {
      $localized_event_data = array('enabled_date' => get_post_meta(get_the_ID(), 'eventer_all_dates', true), 'min_date' => get_query_var('edate'), 'max_date' => date_i18n('Y,m,d', strtotime(max(get_post_meta(get_the_ID(), 'eventer_all_dates', true)))), 'optional_tickets' => get_post_meta(get_the_ID(), 'eventer_optional_tickets', true), 'dynamic_event' => get_the_ID(), 'minimum_ticket' => eventer_get_settings('eventer_minimum_default_ticket'));
    }
    if (is_singular('eventer') || (function_exists('is_checkout()') && is_checkout() && !empty(is_wc_endpoint_url('order-received')))) {
      wp_enqueue_script('eventer-single-scripts', EVENTER__PLUGIN_URL . 'js/single-scripts.js', array('jquery'), $plugin_version, true);
      wp_localize_script('eventer-single-scripts', 'single', array_merge($localized_data, $localized_event_data));
    }
    //CSS
    $fonts_args = array(
      'family' => 'Merriweather:400,400i,700,700i|Open+Sans:400,400i,700,700i',
      'subset' => '',
    );
    wp_localize_script('eventer-init', 'initval', array('ajax_url' => admin_url('admin-ajax.php'), 'month_events_nonce' => wp_create_nonce("eventer_create_nonce_for_month"), 'ticket_registrant' => wp_create_nonce("eventer_create_nonce_for_registrant"), 'paypal_curr' => eventer_get_settings('eventer_paypal_currency'), 'paypal_email' => $paypal_email, 'all_required' => $all_required_msg, 'blank_payment' => $blank_payment_options, 'blank_tickets' => $blank_tickets, 'wpml_lang' => $site_lang, 'eventers_name' => '', 'eventer_stored' => $eventer_stored, 'reg_now' => $registered_now, 'tickets_added' => $tickets_added));
    //Register for front end form
    wp_enqueue_style('eventer_ui_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css', false, "1.9.0", false);
    wp_register_style('eventer_datetimepicker', EVENTER__PLUGIN_URL . 'css/jquery.simple-dtpicker.css');
    wp_register_script('eventer_datetimepicker', EVENTER__PLUGIN_URL . 'js/jquery.simple-dtpicker.js', array('jquery-ui-core'), '', true);
    wp_register_script('eventer_front_form', EVENTER__PLUGIN_URL . 'js/front_form.js', array('jquery-ui-autocomplete', 'jquery'), '', true);
    wp_localize_script('eventer_front_form', 'frontForm', array('week_start' => get_option('start_of_week'), 'nonce' => wp_create_nonce('wp-rest')));
    wp_register_script('eventer_admin_form', EVENTER__PLUGIN_URL . 'js/admin_form.js', array('jquery', 'jquery-ui-sortable'), '', true);
    wp_register_script('eventer-dashboard-scripts', EVENTER__PLUGIN_URL . 'js/eventer_dashboard.js', array('jquery', 'jquery-ui-sortable'), '', true);
    //CSS
    wp_enqueue_style('eventer-style', EVENTER__PLUGIN_URL . 'css/eventer_style.css', array(), $plugin_version, 'all');
    if ($eventer_carousel_switch != 'on') {
      wp_enqueue_style('owl-carousel', EVENTER__PLUGIN_URL . 'vendors/owl-carousel/css/owl.carousel.css', array(), $plugin_version, 'all');
      wp_enqueue_style('eventer-owl-theme', EVENTER__PLUGIN_URL . 'vendors/owl-carousel/css/owl.theme.css', array(), $plugin_version, 'all');
    }
    wp_enqueue_style('eventer-line-icons', EVENTER__PLUGIN_URL . 'css/eventer-simple-line-icons.css', array(), $plugin_version, 'all');
    wp_enqueue_style('eventer-google-css-oswald', add_query_arg($query_args, "//fonts.googleapis.com/css"), array(), $plugin_version, 'all');
    wp_enqueue_style('eventer-modal', EVENTER__PLUGIN_URL . 'css/jquery.modal.min.css', array(), $plugin_version, 'all');

    $eventer_default_color = eventer_get_settings('event_default_color');
    $eventer_color = ($eventer_default_color) ? $eventer_default_color : '#00B4FC';
    $css = '.eventer .eventer-btn-primary,.eventer .eventer-btn-primary:hover,.eventer input[type="radio"]:checked, .eventer input[type="checkbox"]:checked,.eventer-btn-default:hover,.fc button.fc-state-active{
				border-color: ' . $eventer_color . ';
				}
			.eventer-loader,.eventer input[type="radio"]:checked:before, .eventer input[type="checkbox"]:checked:before,.eventer-ticket-confirmation-left > div:before,.eventer-ticket-price-total,.eventer .eventer-btn-default:hover,.eventer-countdown .eventer-event-date,.eventer .eventer-pagination li.active,.eventer-event-grid-item-dbg .eventer-event-grid-item-inside,.eventer-switcher-current-month-in > span,.eventer-plain-links,.eventer-detailed-list .eventer-dater,.eventer-modern-list .eventer-dater:before,.eventer-quick-ticket-info .eventer-ticket-type-price,.eventer-featured-date,.eventer-slider-type1 .eventer-slider-content{
				background-color: ' . $eventer_color . '
			}
			.eventer .eventer-btn-primary,.eventer .eventer-btn-primary:disabled,.eventer .eventer-btn-primary:disabled:hover,.eventer .eventer-btn-primary:hover,.eventer-q-field input[type="button"]:hover,.eventer-switcher-actions-view a:hover,.eventer .eventer-switcher-actions-view a.active,.fc button.fc-state-active,.eventer-custom-ui-datepicker.ui-datepicker .ui-widget-header, .eventer-custom-ui-datepicker.ui-datepicker .ui-datepicker-header,.eventer-custom-ui-datepicker.ui-datepicker td.ui-datepicker-current-day,.eventer-custom-ui-datepicker.ui-datepicker td .ui-state-hover,.eventer .eventer-switcher-actions-view a.today-btn,.eventer .eventer-switcher-actions-view .trigger-active,.eventer-status-badge,.eventer-dash-nav-right li.eventer-fe-usermenu img, .eventer-fe-usermenu .eventer-fe-dropdown-in,.eventer-fe-ele-icon,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_header,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_inner_container>.eventer-datepicker_calendar>.eventer-datepicker_table>tbody>tr>td.hover,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_inner_container>.eventer-datepicker_calendar>.eventer-datepicker_table>tbody>tr>td.active,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_inner_container>.eventer-datepicker_calendar>.eventer-datepicker_table>tbody>tr>td.wday_sat:not(.day_in_past):hover,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_inner_container>.eventer-datepicker_calendar>.eventer-datepicker_table>tbody>tr>td.wday_sun:not(.day_in_past):hover,.eventer-fe-add-field-value:hover,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_inner_container>.eventer-datepicker_calendar>.eventer-datepicker_table>tbody>tr>td.wday_sat.active,.eventer-fe-datepicker.eventer-datepicker>.eventer-datepicker_inner_container>.eventer-datepicker_calendar>.eventer-datepicker_table>tbody>tr>td.wday_sun.active,.eventer-fe-ticket-count,.eventer-accent-bg,.eventer-schedule-time{
				background: ' . $eventer_color . '
			}
			.eventer .eventer-ticket-confirmation-right a:not(.eventer-btn),.eventer-ticket-printable h3, .eventer a,.eventer-actions a:hover,.eventer-event-save > ul > li a:hover,.eventer-compact-list .eventer-event-venue i,.eventer-fe-dash-nav ul li a:hover,.eventer-fe-dash-nav ul li.active a,.eventer-dash-nav-right li.eventer-fe-usermenu a:hover,.eventer-fe-dash-nav ul li a:hover i,.eventer-fe-dash-nav ul li.active a i,.eventer-fe-infolist li strong,.eventer-fe-ele-settings:hover,.eventer-fe-ele-copy:hover,.eventer-fe-ele-paste:hover,.eventer-nav-all-users li a:hover,.eventer-booking-order, .eventer-accent-color,.eventer-detailed-col .eventer-event-title a:hover,.eventer-grid-clean .eventer-event-title a:hover,.eventer-grid-featured .eventer-event-title a:hover,.eventer-grid-featured .eventer-event-share li a:hover,.eventer-grid-products .eventer-event-title a:hover,.eventer-grid-products .eventer-grid-meta > div > i,.eventer-grid-modern .eventer-event-day,.eventer-grid-modern .eventer-event-title a:hover,.eventer-slider-type1 .eventer-event-title a:hover,.eventer-single-style2 .eventer-single-header .eventer-event-share li a:hover,.eventer-organizer-block .eventer-organizer-info > span,.eventer-organizer-block ul > li i,.eventer-organizer-block .eventer-organizer-info > ul > li a:hover{
				color: ' . $eventer_color . '
			}
			/*.eventer-fe-ele-settings:hover,.eventer-fe-ele-copy:hover,.eventer-fe-ele-paste:hover,.eventer-nav-all-users li a:hover{
				color: ' . $eventer_color . '!important
			}*//* This code was commented out while adding eventer stage2*/
			.eventer-actions a:hover,.eventer-fe-usermenu.eventer-fe-dd:before{
				border-bottom-color: ' . $eventer_color . '
			}
			.eventer-compact-list .eventer-event-date{
				border-top-color: ' . $eventer_color . '
			}
			.eventer-minimal-list .eventer-event-list-item,.eventer-event-grid-item-plain .eventer-event-grid-item-inside,.eventer-event-grid-item-dbg .eventer-event-grid-item-inside{
				border-left-color: ' . $eventer_color . '
			}';
    wp_add_inline_style('eventer-style', $css);
  }
  add_action('wp_enqueue_scripts', 'eventer_enqueue_scripts');
}

/*
 * eventer_calendar_scripts_enqueue function
 * Registers the style and js for front end view of calendar
 * We just register the scripts and styles here and use them in calendar shortcode, so that these files should not load at unnecessary pages
 */
if (!function_exists('eventer_calendar_scripts_enqueue')) {
  function eventer_calendar_scripts_enqueue()
  {
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    wp_register_script('fullcalendar-min', EVENTER__PLUGIN_URL . 'front/fullcalendar/fullcalendar.min.js', array('jquery'), $plugin_version, true);
    wp_register_script('fullcalendar-gcal', EVENTER__PLUGIN_URL . 'front/fullcalendar/gcal.js', array('jquery'), $plugin_version, true);
    wp_register_script('fullcalendar-locale', EVENTER__PLUGIN_URL . 'front/fullcalendar/locale-all.js', array('jquery'), $plugin_version, true);
    wp_register_script('fullcalendar-load', EVENTER__PLUGIN_URL . 'js/eventer_calendar.js', array('jquery'), $plugin_version, true);

    wp_register_style('fullcalendar-min', EVENTER__PLUGIN_URL . 'front/fullcalendar/fullcalendar.min.css', array(), $plugin_version, 'all');
    wp_register_style('fullcalendar-print-min', EVENTER__PLUGIN_URL . 'front/fullcalendar/fullcalendar.print.min.css', array(), $plugin_version, 'print');
  }
  add_action('wp_enqueue_scripts', 'eventer_calendar_scripts_enqueue');
}

/*
 * eventer_set_template function
 * This function is used to load correct file while viewing the archive or single pages for eventer
 * This function will search for eventer.php file in child theme then parent theme otherwise plugin's eventer.php file would load
 * User can just create file with the name of eventer.php and place all code of page.php of the activated theme into that file and can use                 eventer_content function to display events to site properly
 */
if (!function_exists('eventer_set_template')) {
  function eventer_set_template($template)
  {
    global $post;
    $preview_id = get_option("eventer_shortcode_preview");
    if (is_object($post) && $post->ID == $preview_id) {
      return EVENTER__PLUGIN_PATH . 'eventers/preview.php';
    }
    if (file_exists(trailingslashit(get_stylesheet_directory()) . 'single-eventer.php') && get_post_type($post) == 'eventer' && is_singular('eventer')) {
      if (is_singular('eventer')) {
        add_filter('the_content', 'eventer_single_post_content');
      }
      return trailingslashit(get_stylesheet_directory()) . 'single-eventer.php';
    } elseif (file_exists(trailingslashit(get_template_directory()) . 'single-eventer.php') && get_post_type($post) == 'eventer' && is_singular('eventer')) {
      if (is_singular('eventer')) {
        add_filter('the_content', 'eventer_single_post_content');
      }
      return trailingslashit(get_template_directory()) . 'single-eventer.php';
    }
    if (file_exists(trailingslashit(get_stylesheet_directory()) . 'eventer.php') && get_post_type($post) == 'eventer') {
      return trailingslashit(get_stylesheet_directory()) . 'eventer.php';
    } elseif (file_exists(trailingslashit(get_template_directory()) . 'eventer.php') && get_post_type($post) == 'eventer') {
      return trailingslashit(get_template_directory()) . 'eventer.php';
    } elseif (get_post_type($post) == 'eventer' && !is_singular('eventer')) {
      return EVENTER__PLUGIN_PATH . 'eventers/eventer.php';
    } else {
      if (is_singular('eventer')) {
        add_filter('the_content', 'eventer_single_post_content');
      }
      return $template;
    }
  }
  add_filter('template_include', 'eventer_set_template', 99);
}

function eventer_single_post_content($content)
{
  if (!in_the_loop() || !is_main_query() || !is_singular() || has_shortcode($content, 'eventer_metas') || has_shortcode($content, 'eventer_tickets')) {
    return $content;
  }
  $eventer_content = '';
  $eventer_loop_start = apply_filters('eventer_registration_data_collect', 1, get_the_ID());
  $eventer_image_size = (eventer_get_settings('eventer_image_size_single') != '') ? eventer_get_settings('eventer_image_size_single') : 'full';
  $registration_switch = get_post_meta(get_the_ID(), 'eventer_event_registration_swtich', true);
  if ($registration_switch == "1") {
    $eventer_content .= '<div class="eventer-is-tickets-active">';
  }
  $eventer_content .= do_shortcode('[eventer_metas]');
  $eventer_content .= do_shortcode('[eventer_tickets]');
  if ($registration_switch == "1") {
    $eventer_content .= '</div>';
  }
  $eventer_content .= '<div class="eventer-save-share-wrap">';
  $eventer_content .= do_shortcode('[eventer_social_share]');
  $eventer_content .= do_shortcode('[eventer_save_events]');
  $eventer_content .= '</div>';
  $eventer_content .= $content;
  remove_filter('the_content', 'eventer_single_post_content');
  return $eventer_content;
}

function eventer_clean_string($string)
{
  $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

  return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

/*
 * eventer_regenerate_calender_index function
 * This function is used to add one second to the date if Google events are of same date and time for currently viewing month calendar
 * Used in eventer_fetch_google_events function
 */
if (!function_exists('eventer_regenerate_calender_index')) {
  function eventer_regenerate_calender_index($index, $google_event_array)
  {
    $index = ($index + 1);
    if (array_key_exists($index, $google_event_array)) {
      return eventer_regenerate_calender_index($index, $google_event_array);
    }
    return $index;
  }
}
function eventer_generate_Google_events_list($calender_id = '', $api_key = '')
{
  $GoogleEvents = array();
  $sb = str_replace('+00:00', 'Z', gmdate('c', strtotime(date_i18n('Y-m-d'))));
  $sbe = str_replace('+00:00', 'Z', gmdate('c', strtotime('2021-12-01')));
  $events = wp_remote_get('https://www.googleapis.com/calendar/v3/calendars/' . $calender_id . '/events/?key=' . $api_key . '&timeMin=' . $sb . '&timeMax=' . $sbe);
  if (is_wp_error($events) || $api_key == '' || $calender_id == '') {
    return false;
  }
  $body = wp_remote_retrieve_body($events);
  $data = json_decode($body);
  $gevents = (property_exists($data, 'items')) ? $data->items : array();
  if (!empty($gevents)) {
    foreach ($gevents as $ev) {
      $multi_events = array($ev);
      $recurrence = (property_exists($ev, 'recurrence')) ? '1' : '';
      $id = $ev->id;
      if ($recurrence == '1') {
        $event_instances = wp_remote_get('https://www.googleapis.com/calendar/v3/calendars/' . $calender_id . '/events/' . $id . '/instances?key=' . $api_key . '&timeMin=' . $sb . '&timeMax=' . $sbe);
        $body_instances = wp_remote_retrieve_body($event_instances);
        $data_instances = json_decode($body_instances);
        $instances_event = $data_instances->items;
        $multi_events = $instances_event;
      }
      foreach ($multi_events as $nevent) {
        if ($nevent->status != 'confirmed') {
          continue;
        }

        $googleEvents = array();
        $start_date = (property_exists($nevent->start, 'dateTime')) ? $nevent->start->dateTime : $nevent->start->date;
        $start_date = get_the_date($start_date);
        $start_date = date_i18n('Y-m-d H:i:s', strtotime($start_date));
        $all_day = (property_exists($nevent->start, 'dateTime')) ? '' : esc_html__('All day', 'eventer');
        $end_date = (property_exists($nevent->end, 'dateTime')) ? $nevent->end->dateTime : $nevent->end->date;
        $end_date = get_the_date($end_date);
        $end_date = date_i18n('Y-m-d H:i:s', strtotime($end_date));
        $htnl_link = (property_exists($nevent, 'htmlLink')) ? $nevent->htmlLink : '';
        $title = (property_exists($nevent, 'summary')) ? $nevent->summary : '';
        $description = (property_exists($nevent, 'description')) ? $nevent->description : '';
        $location = (property_exists($nevent, 'location')) ? $nevent->location : '';
        $googleEvents['start_time'] = $start_date;
        $googleEvents['end_time'] = $end_date;
        $googleEvents['url'] = $htnl_link;
        $googleEvents['title'] = $title;
        $googleEvents['event_day'] = '';
        $googleEvents['description'] = $description;
        $googleEvents['location'] = $location;
        $googleEvents['allday'] = $all_day;
        $googleEvents['color'] = '';
        $GoogleEvents[] = $googleEvents;
      }
    }
  }
  return $GoogleEvents;
}
/*
 * eventer_fetch_google_events function
 * This function is fetches the events from provided Google calendar ID
 */
if (!function_exists('eventer_fetch_google_events')) {
  function eventer_fetch_google_events($status = '')
  {
    $google_calendar_id = eventer_get_settings('cal_id');
    $google_calendar_api = eventer_get_settings('google_cal_apikey');
    $google_event_array = array();
    $items = eventer_generate_Google_events_list($google_calendar_id, $google_calendar_api);
    if (!$items) {
      return array();
    }

    foreach ($items as $entry) {
      $title = $entry['title'];
      $link = $entry['url'];
      $event_start_time = $entry['start_time'];
      $google_event_end_time = $entry['end_time'];
      $index = strtotime($event_start_time);

      $description = $entry['description'];
      $location = $entry['location'];
      $allday = $entry['allday'];
      $color = $entry['color'];
      if (!empty($index) && array_key_exists($index, $google_event_array)) {
        $index = eventer_regenerate_calender_index($index, $google_event_array);
      }
      $start_date_format = get_date_from_gmt(date_i18n('Y-m-d H:i:s', $index), 'Y-m-d H:i:s');
      $end_date_format = get_date_from_gmt(date_i18n('Y-m-d H:i:s', strtotime($google_event_end_time)), 'Y-m-d H:i:s');
      $allday_text = ($allday == '1') ? esc_html__('All day', 'eventer') : '';
      $google_event_array[$start_date_format] = array('start_time' => strtotime($start_date_format), 'featured' => '', 'multi' => '', 'title' => $title, 'link' => $link, 'end' => $end_date_format, 'location' => $location, 'desc' => $description, 'allday' => $allday, 'color' => $color, 'type' => 'google', 'featured_set' => '', 'start' => $start_date_format, 'id' => array('start_time' => $start_date_format, 'title' => $title, 'link' => $link, 'end' => $end_date_format, 'location' => $location, 'desc' => $description, 'allday' => $allday, 'color' => $color, 'type' => 'google', 'featured_set' => '', 'start' => $start_date_format, 'multi' => '', 'featured' => ''));
    }
    return $google_event_array;
  }
}

function eventer_generatePaymentResponse($intent)
{
  if (($intent->status == 'requires_action' || $intent->status == 'requires_source_action') && $intent->next_action->type == 'use_stripe_sdk') {
    # Tell the client to handle the action
    return json_encode([
      'requires_action' => true,
      'payment_intent_client_secret' => $intent->client_secret
    ]);
  } else if ($intent->status == 'succeeded') {
    # The payment didn’t need any additional actions and completed!
    # Handle post-payment fulfillment
    return json_encode([
      'success' => true
    ]);
  } else {
    # Invalid status
    http_response_code(500);
    return json_encode(['error' => 'Invalid PaymentIntent status']);
  }
}

function eventer_sort_asc($x, $y)
{
  return $x['start_time'] - $y['start_time'];
}

function eventer_sort_desc($x, $y)
{
  return $y['start_time'] - $x['start_time'];
}

/*
 * eventer_get_events_array function
 * This function is creating the event array with dates, this function used multiple number of parameters
 * $ids variable used to get events by specific event ID
 * $status variable used to get events by future/past/monthly
 * $series is the date variable where user can provide start date and end date to get events from that date range only, format Y-m-d
 * $offset is used to skip number of events from array
 * $count is number of events to show
 * $events_type is used to show events of two types WP/Google
 * $event_until is used to show events to page until start time or end time
 * $page_lang is used to get events of specific language while using WPML
 * $pass is the variable to use direct WP Query instead of saved data
 */
if (!function_exists('eventer_get_events_array')) {
  function eventer_get_events_array($id = array(), $status = "future", $series = "", $offset = 0, $count = '', $events_type = '', $event_until = '1', $page_lang = '', $pass = '', $occurrence = '', $featured = '')
  {
    //$start = microtime(true);
    $all_events_data_wp_only = $all_events_data_google_only = $featured_events_set = $all_events_merged = array();
    $show_event_until = $event_until;
    $event_count = $count;
    $event_occurance = eventer_get_settings('eventer_show_single_occurance');
    if ($count == '') {
      $event_count = eventer_get_settings('event_limit');
      if ($event_count == '') {
        $event_count = get_option('posts_per_page');
      }
    }

    $site_lang = EVENTER__LANGUAGE_CODE;
    $site_lang = ($page_lang == '') ? $site_lang : $page_lang;
    $offset = ($offset == '') ? 0 : ($offset - 1) * $event_count;
    $all_events_data = $default_featured_set = array();
    $eventer_saved_future = get_option($site_lang . '_eventer_future_data_formatted_wp');
    if ((empty($eventer_saved_future)) || ($pass == "1")) {
      $id = ($pass != "1") ? array() : $id;
      $all_events_data_google = eventer_fetch_google_events();
      $all_events_data_new = $default_featured_set = array();
      $event_arg = array('post_type' => 'eventer', 'post__in' => $id, 'posts_per_page' => -1, 'post_status' => 'publish');
      $event_list = new WP_Query($event_arg);
      if ($event_list->have_posts()) : while ($event_list->have_posts()) : $event_list->the_post();
          $event_start_date = get_post_meta(get_the_ID(), 'eventer_event_start_dt', true);
          $event_end_date = get_post_meta(get_the_ID(), 'eventer_event_end_dt', true);
          $default_featured = get_post_meta(get_the_ID(), 'eventer_event_featured', true);
          if ($default_featured == 'on') {
            $default_featured_set[] = get_the_ID();
          }
          $all_day = get_post_meta(get_the_ID(), 'eventer_event_all_day', true);
          $all_day = ($all_day) ? esc_html__('All day', 'eventer') : '';
          $allday_code = ($all_day) ? '1' : '';
          $event_end_date = ($event_end_date != '') ? $event_end_date : $event_start_date;
          $event_start_dt_str = strtotime($event_start_date);
          $event_end_dt_str = strtotime($event_end_date);
          $multi_day = (date_i18n('Y-m-d', $event_end_dt_str) != date_i18n('Y-m-d', $event_start_dt_str)) ? '1' : '';
          $all_dates = get_post_meta(get_the_ID(), 'eventer_all_dates', true);
          $all_dynamic_dates = '';
          if ($occurrence == 'dynamic') {
            $all_dynamic_dates = get_post_meta(get_the_ID(), 'eventer_event_multiple_dt_inc', true);
            $all_dynamic_dates = array_map('trim', explode(',', $all_dynamic_dates));
            $all_dynamic_dates = array_filter($all_dynamic_dates);
            $all_dates = array_diff($all_dates, $all_dynamic_dates);
          }
          $all_dates = array_filter($all_dates);
          if (!empty($all_dynamic_dates) && $occurrence == 'dynamic') {
            $dynamic_set = array();
            foreach ($all_dynamic_dates as $dynamic) {
              $dynamic_set[] = $dynamic;
            }
            $all_dates = array_merge($all_dates, $dynamic_set);
          }
          $diff_seconds = $event_end_dt_str - $event_start_dt_str;
          $start_time_only = date_i18n('H:i', $event_start_dt_str);
          $end_time_only = date_i18n('H:i', $event_end_dt_str);
          $random_id = 1;
          $event_id = get_the_ID();
          $registration_switch = get_post_meta(get_the_ID(), 'eventer_event_registration_swtich', true);
          foreach ($all_dates as $all_date) {
            $ready_end_date = ($diff_seconds > 0) ? strtotime($all_date . ' ' . $start_time_only) + $diff_seconds : strtotime($all_date);
            $end_time_create = strtotime($all_date) + $diff_seconds;
            $end_time_create = date_i18n('Y-m-d', $end_time_create);
            $all_events_data_new[$event_id . '-' . $random_id] = array('start' => $all_date . ' ' . $start_time_only, 'end' => date_i18n('Y-m-d ' . $end_time_only, $ready_end_date), 'id' => $event_id, 'start_time' => strtotime($all_date . ' ' . $start_time_only), 'end_time' => strtotime($end_time_create . ' ' . $start_time_only), 'actual_start' => $event_start_dt_str, 'actual_end' => $event_end_dt_str, 'featured' => '', 'multi' => $multi_day, 'featured_set' => $default_featured, 'registration_switch' => $registration_switch, 'type' => 'wp', 'allday' => $all_day, 'allday_code' => $allday_code);
            $random_id++;
          }
          $all_events_data_wp_only = $all_events_data_new;
          $all_events_data_google_only = $all_events_data_google;
          $all_events_merged = array_merge($all_events_data_wp_only, $all_events_data_google_only);

        endwhile;
      endif;
      wp_reset_postdata();
      if ($all_events_merged) {
        update_option('eventer_extreme_last_event_date', max(array_column($all_events_merged, 'start')));
        update_option('eventer_extreme_first_event_date', min(array_column($all_events_merged, 'start')));
      }


      update_option($site_lang . '_eventer_future_data_formatted_wp', $all_events_data_new);
      update_option('eventer_future_data_google', $all_events_data_google);
      $all_events_data_new = array_merge($all_events_data_new, $all_events_data_google);
    } else {
      switch ($events_type) {
        case "2":
          $all_events_data_new = (!empty(get_option('eventer_future_data_google'))) ? get_option('eventer_future_data_google') : array();
          $all_events_data_wp_only = array();
          $all_events_data_google_only = (!empty(get_option('eventer_future_data_google'))) ? get_option('eventer_future_data_google') : array();
          break;
        case "1":
          $all_events_data_new = $eventer_saved_future;
          $all_events_data_wp_only = $eventer_saved_future;
          $all_events_data_google_only = array();
          break;
        default:
          $all_events_data_wp = $eventer_saved_future;
          $all_events_data_google = (!empty(get_option('eventer_future_data_google'))) ? get_option('eventer_future_data_google') : array();
          $all_events_data_new = array_merge($all_events_data_wp, $all_events_data_google);
          $all_events_data_wp_only = $eventer_saved_future;
          $all_events_data_google_only = (!empty(get_option('eventer_future_data_google'))) ? get_option('eventer_future_data_google') : array();
      }
    }
    if (!empty($id)) {
      $all_events_data_new = array_filter($all_events_data_wp_only, function ($date) use ($id) {
        return (in_array($date['id'], (array) $id));
      });
    }
    if (($status == "future" || $status == "counters") && (empty($series))) {
      $series = array(date_i18n('Y-m-d G:i'), date_i18n('Y-m-d G:i', strtotime(date("Y-m-d", time()) . " + 1825 day")));
    } elseif (($status == "past") && (empty($series))) {
      $series = array(date_i18n('Y-m-d G:i', strtotime(date("Y-m-d", time()) . " - 1825 day")), date_i18n('Y-m-d G:i'));
    } else {
      $series = ($series == '') ? date_i18n('Y-m') : $series;
    }

    $all_events_data_new = array_filter($all_events_data_new, function ($date) use ($series) {
      if (!is_array($series)) {
        $start = date_i18n('Y-m-01 00:01', strtotime($series));
        $end = date_i18n('Y-m-t 23:59', strtotime($series));
      } else {
        $start = date_i18n('Y-m-d H:i', strtotime($series[0]));
        $end = date_i18n('Y-m-d 23:59', strtotime($series[1]));
      }
      return (strtotime($date['end']) >= strtotime($start) and strtotime($date['end']) <= strtotime($end));
    });


    if ($status == "past") {
      uasort($all_events_data_new, 'eventer_sort_desc');
    } else {
      uasort($all_events_data_new, 'eventer_sort_asc');
      $all_events_data_featured = array_filter($all_events_data_new, function ($data) {
        return ($data['featured_set'] == 'on');
      });
      $featured_events = $already_set = array();
      if ($all_events_data_featured) {
        foreach ($all_events_data_featured as $key => $feat_set) {
          if (in_array($feat_set['id'], $already_set) || $feat_set['type'] == 'google') continue;
          $already_set[] = $feat_set['id'];
          $feat_set['featured'] = 'on';
          unset($all_events_data_new[$key]);
          $featured_events[$key] = $feat_set;
        }
        $all_events_data_new = array_merge($featured_events, $all_events_data_new);
      }
    }
    if ($event_occurance == 'on') {
      $duplicate_events = $new_single_array = array();
      foreach ($all_events_data_new as $key => $this_event) {
        if (in_array($this_event['id'], $duplicate_events)) continue;
        $duplicate_events[] = $this_event['id'];
        $new_single_array[$key] = $this_event;
      }
      $all_events_data_new = $new_single_array;
    }
    $output_events = array_slice($all_events_data_new, $offset, $event_count, true);
    $total_result = count($all_events_data_new);
    return array('results' => $total_result, 'events' => $output_events);
  }
}

/*
 * eventer_search_result_data function
 * This function is used for showing result on search page
 */
if (!function_exists('eventer_search_result_data')) {
  function eventer_search_result_data($id = array(), $status = "future", $series = "", $offset = 0, $count = '', $events_type = '', $event_until = '1', $page_lang = '')
  {
    $show_event_until = $event_until;
    $event_count = $count;
    $event_occurance = eventer_get_settings('eventer_show_single_occurance');
    if ($count == '') {
      $event_count = eventer_get_settings('event_limit');
      if ($event_count == '') {
        $event_count = get_option('posts_per_page');
      }
    }

    $site_lang = EVENTER__LANGUAGE_CODE;
    $all_events_data_google = array();
    $site_lang = ($page_lang == '') ? $site_lang : $page_lang;
    $offset = ($offset == '') ? 0 : ($offset - 1) * $event_count;
    $all_events_data = array();
    $eventer_saved_future = get_option($site_lang . '_eventer_future_data_wp');
    $eventer_saved_show_until = get_option('eventer_saved_show_until');
    $eventer_multi_date = (!empty(get_option('eventer_multi_day_event'))) ? get_option('eventer_multi_day_event') : array();
    $all_events_data_new = array();
    if (have_posts()) : while (have_posts()) : the_post();
        $event_start_date = get_post_meta(get_the_ID(), 'eventer_event_start_dt', true);
        $event_end_date = get_post_meta(get_the_ID(), 'eventer_event_end_dt', true);
        $event_end_date = ($event_end_date != '') ? $event_end_date : $event_start_date;
        $event_start_dt_str = strtotime($event_start_date);
        $event_end_dt_str = strtotime($event_end_date);
        $event_time = ($show_event_until == 1) ? date_i18n("H:i", $event_start_dt_str) : date_i18n("H:i", $event_end_dt_str);
        $days_diff = eventer_dateDiff($event_start_date, $event_end_date);
        $all_dates = get_post_meta(get_the_ID(), 'eventer_all_dates', true);
        $all_dates = (is_array($all_dates)) ? array_filter($all_dates) : array($all_dates);
        if (date('Y-m-d', $event_start_dt_str) != date('Y-m-d', $event_end_dt_str) && $show_event_until != 1) {
          $s = strtotime('2025-01-01 ' . date_i18n('H:i', $event_start_dt_str));
          $e = strtotime('2025-01-02 ' . date_i18n('H:i', $event_end_dt_str));
          $diff = ($e - $s) / 3600;
          if ($diff > 0) {
            $diff_minutes = $diff * 60;
            $sn = date("G:i", $event_start_dt_str);
            $all_dates = array_map(function ($date) use ($sn, $diff_minutes) {
              $st = strtotime($date . ' ' . $sn);
              return date("Y-m-d H:i", strtotime('+' . $diff_minutes . ' minutes', $st));
            }, $all_dates);
          } else {
            $st_date = date_i18n("H:i", $event_start_dt_str);
            $all_dates = preg_filter('/$/', ' ' . $st_date, $all_dates);
          }
        } else {
          $all_dates = preg_filter('/$/', ' ' . $event_time, $all_dates);
        }

        $fill_id = array_fill_keys($all_dates, get_the_ID());
        foreach ($fill_id as $key => $value) {
          $check_keys = array($key, $key . ':01', $key . ':02', $key . ':03', $key . ':04', $key . ':05', $key . ':06', $key . ':07', $key . ':08', $key . ':09');
          $count_total_duplicate_keys = count(array_intersect_key(array_flip($check_keys), $all_events_data_new));
          if ($count_total_duplicate_keys > 0) {
            $mod_key = str_pad($count_total_duplicate_keys, 2, "0", STR_PAD_LEFT);
            $all_events_data_new[$key . ':' . $mod_key] = $all_events_data_new[$key];
          }
        }
        $all_events_data_new = array_merge($all_events_data_new, $fill_id);
        $all_events_data_wp_only = $all_events_data_new;
        $all_events_data_google_only = $all_events_data_google;
      endwhile;
    endif;
    if (!empty($id)) {
      $all_events_data_new = array_intersect($all_events_data_new, $id);
    }
    //Below Code is written for event which have different date in start and end date field, so that the event should display only one time for future or event start date if don't have any future date
    if ((!empty($eventer_multi_date) && !empty($all_events_data_wp_only)) || ($event_occurance == "on" && !empty($all_events_data_wp_only))) {
      $all_events_data_new_singleday = array_diff($all_events_data_wp_only, $eventer_multi_date);
      $eventer_multi_date = ($event_occurance == "on") ? array_values($all_events_data_wp_only) : $eventer_multi_date;
      $all_events_data_selected = ($event_occurance == "on") ? $all_events_data_wp_only : array_intersect($all_events_data_wp_only, $eventer_multi_date);
      $all_events_data_new_again = array_filter($all_events_data_selected, function ($date) {
        return (strtotime($date) >= date_i18n('U'));
      }, ARRAY_FILTER_USE_KEY);
      $single_occurrecnce = array_unique($all_events_data_new_again);
      $diff_ids = array_diff($eventer_multi_date, $all_events_data_new_again);
      $diff_ids = array_unique($diff_ids);
      if (!empty($diff_ids)) {
        $each_non_future_events = array();
        foreach ($diff_ids as $multi) {
          $all_events_passed_nont_future = array_intersect($all_events_data_wp_only, array($multi));
          $all_events_passed_event = array_unique($all_events_passed_nont_future);
          $each_non_future_events = array_merge($each_non_future_events, $all_events_passed_event);
        }
        $single_occurrecnce = array_merge($single_occurrecnce, $each_non_future_events);
      }

      $all_events_data_first = ($event_occurance != "on") ? array_merge($all_events_data_new_singleday, $single_occurrecnce) : $single_occurrecnce;
      $all_events_data_new = array_merge($all_events_data_first, (array) $all_events_data_google_only);
    }
    //End code of multiple date event
    if ($status == "future" || $status == "counters") {
      $series = array(date_i18n('Y-m-d G:i'), date_i18n('Y-m-d G:i', strtotime(date("Y-m-d", time()) . " + 1825 day")));
    } elseif ($status == "past") {
      $series = array(date_i18n('Y-m-d G:i', strtotime(date("Y-m-d", time()) . " - 1825 day")), date_i18n('Y-m-d G:i'));
    } else {
      $series = ($series == '') ? date_i18n('Y-m') : $series;
    }

    $all_events_data_new = array_filter($all_events_data_new, function ($date) use ($series) {
      if (!is_array($series)) {
        $start = date_i18n('Y-m-01 00:01', strtotime($series));
        $end = date_i18n('Y-m-t 23:59', strtotime($series));
      } else {
        $start = $series[0];
        $end = $series[1];
      }
      return (strtotime($date) >= strtotime($start) and strtotime($date) <= strtotime($end));
    }, ARRAY_FILTER_USE_KEY);

    if ($status == "past") {
      krsort($all_events_data_new);
    } else {
      ksort($all_events_data_new);
    }
    $output_events = array_slice($all_events_data_new, $offset, $event_count, true);
    $total_result = count($all_events_data_new);
    $output = array('events' => $output_events, 'results' => $total_result);
    //echo microtime(true) - $start;
    return $output;
  }
}

//eventer_dotpay_chk_security_create function to use chk field for payment
/*
#$DotpayId is dotpay account ID
#$DotpayPin is pin of account that can access from settings of dashboard
*/
function eventer_dotpay_chk_security_create($DotpayId, $DotpayPin, $Environment, $RedirectionMethod, $ParametersArray, $MultiMerchantList, $customer_base64)
{
  $ParametersArray['id'] = $DotpayId;
  $ParametersArray['customer'] = $customer_base64;

  $chk =   $DotpayPin . (isset($ParametersArray['api_version']) ? $ParametersArray['api_version'] : null) . (isset($ParametersArray['lang']) ? $ParametersArray['lang'] : null) . (isset($ParametersArray['id']) ? $ParametersArray['id'] : null) . (isset($ParametersArray['pid']) ? $ParametersArray['pid'] : null) . (isset($ParametersArray['amount']) ? $ParametersArray['amount'] : null) . (isset($ParametersArray['currency']) ? $ParametersArray['currency'] : null) . (isset($ParametersArray['description']) ? $ParametersArray['description'] : null) . (isset($ParametersArray['control']) ? $ParametersArray['control'] : null) . (isset($ParametersArray['channel']) ? $ParametersArray['channel'] : null) . (isset($ParametersArray['credit_card_brand']) ? $ParametersArray['credit_card_brand'] : null) . (isset($ParametersArray['ch_lock']) ? $ParametersArray['ch_lock'] : null) . (isset($ParametersArray['channel_groups']) ? $ParametersArray['channel_groups'] : null) . (isset($ParametersArray['onlinetransfer']) ? $ParametersArray['onlinetransfer'] : null) . (isset($ParametersArray['url']) ? $ParametersArray['url'] : null) . (isset($ParametersArray['type']) ? $ParametersArray['type'] : null) . (isset($ParametersArray['buttontext']) ? $ParametersArray['buttontext'] : null) . (isset($ParametersArray['urlc']) ? $ParametersArray['urlc'] : null) . (isset($ParametersArray['firstname']) ? $ParametersArray['firstname'] : null) . (isset($ParametersArray['lastname']) ? $ParametersArray['lastname'] : null) . (isset($ParametersArray['email']) ? $ParametersArray['email'] : null) . (isset($ParametersArray['street']) ? $ParametersArray['street'] : null) . (isset($ParametersArray['street_n1']) ? $ParametersArray['street_n1'] : null) . (isset($ParametersArray['street_n2']) ? $ParametersArray['street_n2'] : null) . (isset($ParametersArray['state']) ? $ParametersArray['state'] : null) . (isset($ParametersArray['addr3']) ? $ParametersArray['addr3'] : null) . (isset($ParametersArray['city']) ? $ParametersArray['city'] : null) . (isset($ParametersArray['postcode']) ? $ParametersArray['postcode'] : null) . (isset($ParametersArray['phone']) ? $ParametersArray['phone'] : null) . (isset($ParametersArray['country']) ? $ParametersArray['country'] : null) . (isset($ParametersArray['code']) ? $ParametersArray['code'] : null) . (isset($ParametersArray['p_info']) ? $ParametersArray['p_info'] : null) . (isset($ParametersArray['p_email']) ? $ParametersArray['p_email'] : null) . (isset($ParametersArray['n_email']) ? $ParametersArray['n_email'] : null) . (isset($ParametersArray['expiration_date']) ? $ParametersArray['expiration_date'] : null) . (isset($ParametersArray['deladdr']) ? $ParametersArray['deladdr'] : null) . (isset($ParametersArray['recipient_account_number']) ? $ParametersArray['recipient_account_number'] : null) . (isset($ParametersArray['recipient_company']) ? $ParametersArray['recipient_company'] : null) . (isset($ParametersArray['recipient_first_name']) ? $ParametersArray['recipient_first_name'] : null) . (isset($ParametersArray['recipient_last_name']) ? $ParametersArray['recipient_last_name'] : null) . (isset($ParametersArray['recipient_address_street']) ? $ParametersArray['recipient_address_street'] : null) . (isset($ParametersArray['recipient_address_building']) ? $ParametersArray['recipient_address_building'] : null) . (isset($ParametersArray['recipient_address_apartment']) ? $ParametersArray['recipient_address_apartment'] : null) . (isset($ParametersArray['recipient_address_postcode']) ? $ParametersArray['recipient_address_postcode'] : null) . (isset($ParametersArray['recipient_address_city']) ? $ParametersArray['recipient_address_city'] : null) . (isset($ParametersArray['application']) ? $ParametersArray['application'] : null) . (isset($ParametersArray['application_version']) ? $ParametersArray['application_version'] : null) . (isset($ParametersArray['warranty']) ? $ParametersArray['warranty'] : null) . (isset($ParametersArray['bylaw']) ? $ParametersArray['bylaw'] : null) . (isset($ParametersArray['personal_data']) ? $ParametersArray['personal_data'] : null) . (isset($ParametersArray['credit_card_number']) ? $ParametersArray['credit_card_number'] : null) . (isset($ParametersArray['credit_card_expiration_date_year']) ? $ParametersArray['credit_card_expiration_date_year'] : null) . (isset($ParametersArray['credit_card_expiration_date_month']) ? $ParametersArray['credit_card_expiration_date_month'] : null) . (isset($ParametersArray['credit_card_security_code']) ? $ParametersArray['credit_card_security_code'] : null) . (isset($ParametersArray['credit_card_store']) ? $ParametersArray['credit_card_store'] : null) . (isset($ParametersArray['credit_card_store_security_code']) ? $ParametersArray['credit_card_store_security_code'] : null) . (isset($ParametersArray['credit_card_customer_id']) ? $ParametersArray['credit_card_customer_id'] : null) . (isset($ParametersArray['credit_card_id']) ? $ParametersArray['credit_card_id'] : null) . (isset($ParametersArray['blik_code']) ? $ParametersArray['blik_code'] : null) . (isset($ParametersArray['credit_card_registration']) ? $ParametersArray['credit_card_registration'] : null) . (isset($ParametersArray['surcharge_amount']) ? $ParametersArray['surcharge_amount'] : null) . (isset($ParametersArray['surcharge']) ? $ParametersArray['surcharge'] : null) . (isset($ParametersArray['surcharge']) ? $ParametersArray['surcharge'] : null) . (isset($ParametersArray['ignore_last_payment_channel']) ? $ParametersArray['ignore_last_payment_channel'] : null) . (isset($ParametersArray['vco_call_id']) ? $ParametersArray['vco_call_id'] : null) . (isset($ParametersArray['vco_update_order_info']) ? $ParametersArray['vco_update_order_info'] : null) . (isset($ParametersArray['vco_subtotal']) ? $ParametersArray['vco_subtotal'] : null) . (isset($ParametersArray['vco_shipping_handling']) ? $ParametersArray['vco_shipping_handling'] : null) . (isset($ParametersArray['vco_tax']) ? $ParametersArray['vco_tax'] : null) . (isset($ParametersArray['vco_discount']) ? $ParametersArray['vco_discount'] : null) . (isset($ParametersArray['vco_gift_wrap']) ? $ParametersArray['vco_gift_wrap'] : null) . (isset($ParametersArray['vco_misc']) ? $ParametersArray['vco_misc'] : null) . (isset($ParametersArray['vco_promo_code']) ? $ParametersArray['vco_promo_code'] : null) . (isset($ParametersArray['credit_card_security_code_required']) ? $ParametersArray['credit_card_security_code_required'] : null) . (isset($ParametersArray['credit_card_operation_type']) ? $ParametersArray['credit_card_operation_type'] : null) . (isset($ParametersArray['credit_card_avs']) ? $ParametersArray['credit_card_avs'] : null) . (isset($ParametersArray['credit_card_threeds']) ? $ParametersArray['credit_card_threeds'] : null) . (isset($ParametersArray['customer']) ? $ParametersArray['customer'] : null) . (isset($ParametersArray['gp_token']) ? $ParametersArray['gp_token'] : null);

  foreach ($MultiMerchantList as $item) {
    foreach ($item as $key => $value) {
      $chk =   $chk . (isset($value) ? $value : null);
    }
  }
  return $chk;
}
/*
 * eventer_display_time function
 * This is the helper function of eventer_explore_event_ids
 * This function is used to set date and time for all events used for this plugin
 */
if (!function_exists('eventer_display_time')) {
  function eventer_display_time($key = '', $id = '', $stime_format = '', $etime_format = '', $time_separator = '-', $counter_until = "1")
  {
    $date_format = esc_attr(eventer_get_settings('eventer_date_format'));
    $date_format = ($date_format == '') ? get_option('date_format') : $date_format;
    $counter_status = $output_time = $exit_rest = '';
    $show_counter = $key;
    if (!$id) {
      return;
    }

    $event_start_date = get_post_meta($id, 'eventer_event_start_dt', true);
    $event_end_date = get_post_meta($id, 'eventer_event_end_dt', true);
    $event_end_date = ($event_end_date != '') ? $event_end_date : $event_start_date;
    $event_start_dt_str = strtotime($event_start_date);
    $event_end_dt_str = strtotime($event_end_date);
    $days_diff = eventer_dateDiff($event_start_date, $event_end_date);
    $hours_diff = (intval($event_end_dt_str) - intval($event_start_dt_str));
    $result_event_multipledays = $multiday_set_start = $multiday_set_end = '';
    $edtstr = $key;
    if ($hours_diff > 86400 && $counter_until != "1") {
      $show_event_until = $counter_until;
      if ($show_event_until != "1") {
        $s = strtotime('2025-01-01 ' . date_i18n('G:i', $event_start_dt_str));
        $e = strtotime('2025-01-02 ' . date_i18n('G:i', $event_end_dt_str));
        $diff = ($e - $s) / 3600;
        if ($diff > 0) {
          $diff_minutes = $diff * 60;
          $lessed_time = date("Y-m-d G:i", strtotime('-' . $diff_minutes . ' minutes', $key));
          $show_counter = strtotime($lessed_time);
        }
      }
    }
    if ($hours_diff > 86400) {
      $multiday_set_start = date_i18n('j M', $event_start_dt_str);
      $multiday_set_end = date_i18n('j M', $event_end_dt_str);
    }
    $sdate = date_i18n('Y-m-d', $show_counter);
    $stime = date_i18n("G:i", $event_start_dt_str);
    $etime = date_i18n("G:i", $event_end_dt_str);
    $sdtstr = strtotime($sdate . ' ' . $stime);
    $edtstr = strtotime($sdate . ' ' . $etime);

    $counter_status = ($sdtstr <= date_i18n('U') && $edtstr >= date_i18n('U')) ? esc_html__('Ongoing', 'eventer') : esc_html__('Upcoming', 'eventer');
    $all_day = get_post_meta($id, 'eventer_event_all_day', true);
    $show_start_time = date_i18n($stime_format, $sdtstr);
    $show_end_time = date_i18n($etime_format, $edtstr);
    if (date_i18n('Y-m-d', $event_start_dt_str) != date_i18n('Y-m-d', $event_end_dt_str) && $key >= date_i18n('U') && $counter_until != "1") {
      $output_time = date_i18n($date_format, $show_counter) . ' ' . $time_separator . ' ' . date_i18n($date_format, $key);
    } elseif ((date_i18n('Y-m-d', $event_start_dt_str) != date_i18n('Y-m-d', $event_end_dt_str)) && (date_i18n('Y', $event_start_dt_str) != date_i18n('Y', $event_end_dt_str))) {
      $multiday_set_start = $multiday_set_end = '';
      $output_time = date_i18n($date_format, $event_start_dt_str) . ' ' . $time_separator . ' ' . date_i18n($date_format, $event_end_dt_str);
    } elseif ($hours_diff > 86400) {
      $output_time = date_i18n($date_format, $event_start_dt_str) . ' ' . $time_separator . ' ' . date_i18n($date_format, $event_end_dt_str);
    } elseif ($all_day) {
      $output_time = esc_html__('All Day', 'eventer');
    } elseif ($stime_format != '' && $etime_format != '') {
      $output_time = $show_start_time . ' ' . $time_separator . ' ' . $show_end_time;
    } elseif ($stime_format != '' && $etime_format == '') {
      $output_time = $show_start_time;
    } elseif ($stime_format == '' && $etime_format != '') {
      $output_time = $show_end_time;
    } else {
      $time_format = get_option('time_format');
      $output_time = date_i18n($time_format, $show_start_time);
    }
    return array($counter_status, $output_time, $show_counter, $multiday_set_start, $multiday_set_end);
  }
}

/*
 * eventer_explore_event_ids function
 * This function is used to set date/time/color/title/URL/location metas for events on site
 */
if (!function_exists('eventer_explore_event_ids')) {
  function eventer_explore_event_ids($key = '', $value = '', $stime_format = '', $etime_format = '', $time_separator = '-', $event_until = "1")
  {
    $easy_eventer = array();
    $elocation = $color = '';
    if (is_array($value)) {
      $title = $value['title'];
      $location = $value['location'];
      $allday = $value['allday'];
      $color = $value['color'];
      $end_time = $value['end_time'];
      $google_url = $value['link'];
      $show_start_time = date_i18n($stime_format, $key);
      $show_end_time = date_i18n($etime_format, strtotime($end_time));
      $counter_status = esc_html__('Upcoming Event', 'eventer');
      if ($allday == 1) {
        $output_time = esc_html__('All Day', 'eventer');
      } elseif ($stime_format != '' && $etime_format != '') {
        $output_time = $show_start_time . ' ' . $time_separator . ' ' . $show_end_time;
      } elseif ($stime_format != '' && $etime_format == '') {
        $output_time = $show_start_time;
      } elseif ($stime_format == '' && $etime_format != '') {
        $output_time = $show_end_time;
      } else {
        $time_format = get_option('time_format');
        //$output_time = '<span class="eventer-event-time">'.date_i18n($time_format, $key).'</span>';
      }
      $easy_eventer = array('eid' => '', 'etitle' => $title, 'elocation' => $location, 'etime' => $output_time, 'status' => $counter_status, 'show_counter' => $key, 'color' => $color, 'google_url' => $google_url);
    } else {
      $google_url = $venue_name = '';
      $time = eventer_display_time($key, $value, $stime_format, $etime_format, $time_separator, $event_until);
      $primary_term = get_post_meta($value, 'eventer_primary_term', true);
      if (has_term(array($primary_term), 'eventer-venue', $value)) {
        $location_address = get_term_meta($primary_term, 'venue_address', true);
        if ($location_address != '') {
          $elocation = $location_address;
        } else {
          $venue_title = get_term_by('id', $primary_term, 'eventer-venue');
          $elocation = $venue_title->name;
        }
        $venue_name = $venue_title->name;
      } else {
        $venues = get_the_terms($value, 'eventer-venue');
        $eventer_category = get_the_terms($value, 'eventer-category');
        if (!is_wp_error($eventer_category) && !empty($eventer_category)) {
          $color = get_term_meta($eventer_category[0]->term_id, 'category_color', true);
          $color = ($color != '') ? $color : '';
        }
        if (!is_wp_error($venues) && !empty($venues)) {
          $location_address = get_term_meta($venues[0]->term_id, 'venue_address', true);
          if ($location_address != '') {
            $elocation = $location_address;
          } else {
            $elocation = $venues[0]->name;
          }
          $venue_name = $venues[0]->name;
        }
      }
      $easy_eventer = array('eid' => $value, 'etitle' => apply_filters('eventer_raw_event_title', '', $value), 'elocation' => $elocation, 'etime' => $time[1], 'status' => $time[0], 'show_counter' => $time[2], 'color' => $color, 'google_url' => $google_url, 'multiday_start' => $time[3], 'multiday_end' => $time[4], 'venue_name' => $venue_name);
    }
    return $easy_eventer;
  }
}

if (!function_exists('eventer_get_currency_symbol')) {
  /*
     * eventer_get_currency_symbol function
     * This function convert currency to HTML entity
     */
  add_action('init', 'eventer_get_currency_symbol');
  function eventer_get_currency_symbol($currency = '', $price = '')
  {
    if (!$currency) {
      $currency = 'USD';
    }
    switch ($currency) {
      case 'AED':
        $currency_symbol = '&#x62f;.&#x625;';
        break;
      case 'AFN':
        $currency_symbol = '&#x60b;';
        break;
      case 'ALL':
        $currency_symbol = 'L';
        break;
      case 'AMD':
        $currency_symbol = 'AMD';
        break;
      case 'ANG':
        $currency_symbol = '&fnof;';
        break;
      case 'AOA':
        $currency_symbol = 'Kz';
        break;
      case 'AWG':
        $currency_symbol = '&fnof;';
        break;
      case 'AZN':
        $currency_symbol = 'AZN';
        break;
      case 'BAM':
        $currency_symbol = 'KM';
        break;
      case 'BDT':
        $currency_symbol = '&#2547;&nbsp;';
        break;
      case 'BGN':
        $currency_symbol = '&#1083;&#1074;.';
        break;
      case 'BHD':
        $currency_symbol = '.&#x62f;.&#x628;';
        break;
      case 'BIF':
        $currency_symbol = 'Fr';
        break;
      case 'BOB':
        $currency_symbol = 'Bs.';
        break;
      case 'BRL':
        $currency_symbol = '&#82;&#36;';
        break;
      case 'BTC':
        $currency_symbol = '&#3647;';
        break;
      case 'BTN':
        $currency_symbol = 'Nu.';
        break;
      case 'BWP':
        $currency_symbol = 'P';
        break;
      case 'BYR':
        $currency_symbol = 'Br';
        break;
      case 'BYN':
        $currency_symbol = 'byn';
        break;
      case 'CDF':
        $currency_symbol = 'Fr';
        break;
      case 'CHF':
        $currency_symbol = '&#67;&#72;&#70;';
        break;
      case 'CNY':
        $currency_symbol = '&yen;';
        break;
      case 'CRC':
        $currency_symbol = '&#x20a1;';
        break;
      case 'CZK':
        $currency_symbol = '&#75;&#269;';
        break;
      case 'DJF':
        $currency_symbol = 'Fr';
        break;
      case 'DKK':
        $currency_symbol = 'DKK';
        break;
      case 'DOP':
        $currency_symbol = 'RD&#36;';
        break;
      case 'DZD':
        $currency_symbol = '&#x62f;.&#x62c;';
        break;
      case 'EGP':
        $currency_symbol = 'EGP';
        break;
      case 'ERN':
        $currency_symbol = 'Nfk';
        break;
      case 'ETB':
        $currency_symbol = 'Br';
        break;
      case 'FJD':
        $currency_symbol = '&#36;';
        break;
      case 'FKP':
        $currency_symbol = 'EGP';
        break;
      case 'GEL':
        $currency_symbol = '&#x10da;';
        break;
      case 'GHS':
        $currency_symbol = '&#x20b5;';
        break;
      case 'GMD':
        $currency_symbol = 'D';
        break;
      case 'GNF':
        $currency_symbol = 'Fr';
        break;
      case 'GTQ':
        $currency_symbol = 'Q';
        break;
      case 'HNL':
        $currency_symbol = 'L';
        break;
      case 'HRK':
        $currency_symbol = 'Kn';
        break;
      case 'HTG':
        $currency_symbol = 'G';
        break;
      case 'HUF':
        $currency_symbol = '&#70;&#116;';
        break;
      case 'IDR':
        $currency_symbol = 'Rp';
        break;
      case 'ILS':
        $currency_symbol = '&#8362;';
        break;
      case 'INR':
        $currency_symbol = '&#8377;';
        break;
      case 'IQD':
        $currency_symbol = '&#x639;.&#x62f;';
        break;
      case 'IRR':
        $currency_symbol = '&#xfdfc;';
        break;
      case 'IRT':
        $currency_symbol = '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;';
        break;
      case 'ISK':
        $currency_symbol = 'Kr.';
        break;
      case 'JOD':
        $currency_symbol = '&#x62f;.&#x627;';
        break;
      case 'KES':
        $currency_symbol = 'KSh';
        break;
      case 'KGS':
        $currency_symbol = '&#x441;&#x43e;&#x43c;';
        break;
      case 'KHR':
        $currency_symbol = '&#x17db;';
        break;
      case 'KMF':
        $currency_symbol = 'Fr';
        break;
      case 'KPW':
        $currency_symbol = '&#x20a9;';
        break;
      case 'KRW':
        $currency_symbol = '&#8361;';
        break;
      case 'KWD':
        $currency_symbol = '&#x62f;.&#x643;';
        break;
      case 'KZT':
        $currency_symbol = 'KZT';
        break;
      case 'LAK':
        $currency_symbol = '&#8365;';
        break;
      case 'LBP':
        $currency_symbol = '&#x644;.&#x644;';
        break;
      case 'LKR':
        $currency_symbol = '&#xdbb;&#xdd4;';
        break;
      case 'LSL':
        $currency_symbol = 'L';
        break;
      case 'LYD':
        $currency_symbol = '&#x644;.&#x62f;';
        break;
      case 'MAD':
        $currency_symbol = '&#x62f;.&#x645;.';
        break;
      case 'MDL':
        $currency_symbol = 'MDL';
        break;
      case 'MGA':
        $currency_symbol = 'Ar';
        break;
      case 'MKD':
        $currency_symbol = '&#x434;&#x435;&#x43d;';
        break;
      case 'MMK':
        $currency_symbol = 'Ks';
        break;
      case 'MNT':
        $currency_symbol = '&#x20ae;';
        break;
      case 'MOP':
        $currency_symbol = 'P';
        break;
      case 'MRO':
        $currency_symbol = 'UM';
        break;
      case 'MUR':
        $currency_symbol = '&#x20a8;';
        break;
      case 'MVR':
        $currency_symbol = '.&#x783;';
        break;
      case 'MWK':
        $currency_symbol = 'MK';
        break;
      case 'MYR':
        $currency_symbol = '&#82;&#77;';
        break;
      case 'MZN':
        $currency_symbol = 'MT';
        break;
      case 'NGN':
        $currency_symbol = '&#8358;';
        break;
      case 'NIO':
        $currency_symbol = 'C&#36;';
        break;
      case 'NOK':
        $currency_symbol = '&#107;&#114;';
        break;
      case 'NPR':
        $currency_symbol = '&#8360;';
        break;
      case 'OMR':
        $currency_symbol = '&#x631;.&#x639;.';
        break;
      case 'PAB':
        $currency_symbol = 'B/.';
        break;
      case 'PEN':
        $currency_symbol = 'S/.';
        break;
      case 'PGK':
        $currency_symbol = 'K';
        break;
      case 'PHP':
        $currency_symbol = '&#8369;';
        break;
      case 'PKR':
        $currency_symbol = '&#8360;';
        break;
      case 'PLN':
        $currency_symbol = '&#122;&#322;';
        break;
      case 'PRB':
        $currency_symbol = '&#x440;.';
        break;
      case 'PYG':
        $currency_symbol = '&#8370;';
        break;
      case 'QAR':
        $currency_symbol = '&#x631;.&#x642;';
        break;
      case 'RON':
        $currency_symbol = 'lei';
        break;
      case 'RSD':
        $currency_symbol = '&#x434;&#x438;&#x43d;.';
        break;
      case 'RUB':
        $currency_symbol = '&#8381;';
        break;
      case 'RWF':
        $currency_symbol = 'Fr';
        break;
      case 'SAR':
        $currency_symbol = '&#x631;.&#x633;';
        break;
      case 'SCR':
        $currency_symbol = '&#x20a8;';
        break;
      case 'SDG':
        $currency_symbol = '&#x62c;.&#x633;.';
        break;
      case 'SEK':
        $currency_symbol = '&#107;&#114;';
        break;
      case 'SLL':
        $currency_symbol = 'Le';
        break;
      case 'SOS':
        $currency_symbol = 'Sh';
        break;
      case 'STD':
        $currency_symbol = 'Db';
        break;
      case 'SYP':
        $currency_symbol = '&#x644;.&#x633;';
        break;
      case 'SZL':
        $currency_symbol = 'L';
        break;
      case 'THB':
        $currency_symbol = '&#3647;';
        break;
      case 'TJS':
        $currency_symbol = '&#x405;&#x41c;';
        break;
      case 'TMT':
        $currency_symbol = 'm';
        break;
      case 'TND':
        $currency_symbol = '&#x62f;.&#x62a;';
        break;
      case 'TOP':
        $currency_symbol = 'T&#36;';
        break;
      case 'TRY':
        $currency_symbol = '&#8378;';
        break;
      case 'TWD':
        $currency_symbol = '&#78;&#84;&#36;';
        break;
      case 'TZS':
        $currency_symbol = 'Sh';
        break;
      case 'UAH':
        $currency_symbol = '&#8372;';
        break;
      case 'UGX':
        $currency_symbol = 'UGX';
        break;
      case 'UZS':
        $currency_symbol = 'UZS';
        break;
      case 'VEF':
        $currency_symbol = 'Bs F';
        break;
      case 'VND':
        $currency_symbol = '&#8363;';
        break;
      case 'VUV':
        $currency_symbol = 'Vt';
        break;
      case 'WST':
        $currency_symbol = 'T';
        break;
      case 'XAF':
        $currency_symbol = 'Fr';
        break;
      case 'XOF':
        $currency_symbol = 'Fr';
        break;
      case 'XPF':
        $currency_symbol = 'Fr';
        break;
      case 'YER':
        $currency_symbol = '&#xfdfc;';
        break;
      case 'ZAR':
        $currency_symbol = '&#82;';
        break;
      case 'ZMW':
        $currency_symbol = 'ZK';
        break;
      case 'EUR':
        $currency_symbol = '&euro;';
        break;
      case 'ARS':
      case 'AUD':
      case 'BBD':
      case 'BMD':
      case 'BND':
      case 'BSD':
      case 'BZD':
      case 'CAD':
      case 'CLP':
      case 'COP':
      case 'CUC':
      case 'CUP':
      case 'CVE':
      case 'GYD':
      case 'HKD':
      case 'JMD':
      case 'KYD':
      case 'LRD':
      case 'MXN':
      case 'NAD':
      case 'NZD':
      case 'SBD':
      case 'SGD':
      case 'SRD':
      case 'TTD':
      case 'USD':
      case 'UYU':
      case 'XCD':
        $currency_symbol = '&#36;';
        break;
      case 'FKP':
      case 'GBP':
      case 'GGP':
      case 'GIP':
      case 'IMP':
      case 'JEP':
      case 'SHP':
      case 'SSP':
        $currency_symbol = '&pound;';
        break;
      case 'JPY':
      case 'RMB':
        $currency_symbol = '&yen;';
        break;
      default:
        $currency_symbol = '';
        break;
    }
    $left_blank_space = $right_blank_space = '';
    $woocommerce_switch = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    if ($woocommerce_switch == 'on' && get_option('woocommerce_currency_pos') == 'left_space') {
      $left_blank_space = '&nbsp;' . $price;
    } elseif ($woocommerce_switch == 'on' && get_option('woocommerce_currency_pos') == 'right_space') {
      $right_blank_space = $price . '&nbsp;';
    } elseif ($woocommerce_switch == 'on' && get_option('woocommerce_currency_pos') == 'right') {
      $right_blank_space = $price;
    } elseif ($woocommerce_switch == 'on' && get_option('woocommerce_currency_pos') == 'left') {
      $left_blank_space = $price;
    } elseif (eventer_get_settings('eventer_currency_position') == 'postfix') {
      $right_blank_space = $price;
    } elseif (eventer_get_settings('eventer_currency_position') == 'suffix') {
      $left_blank_space = $price;
    }
    return $right_blank_space . $currency_symbol . $left_blank_space;
  }
}

function eventer_filtering_values($status = 'monthly', $get_month_act = '', $i = 0)
{
  $filter_data = '';
  $get_month_act = ($get_month_act == '') ? date_i18n('Y-m-d') : $get_month_act;
  $arrow = "1";
  if ($status == 'daily') {
    $proper_formatted_date = date_parse_from_format('Y-m-d', $get_month_act);
    $year = ($proper_formatted_date['year']) ? $proper_formatted_date['year'] : date_i18n('Y');
    $month = ($proper_formatted_date['month']) ? $proper_formatted_date['month'] : date_i18n('m');
    $day = ($proper_formatted_date['day']) ? $proper_formatted_date['day'] : date_i18n('d');
    $get_month_act = $year . '-' . $month . '-' . $day;
    $tabs = 'day';
    $tabs_date = 'l';

    $tab_length = 6;
    $increment_format = 'Y-m-d';
    if ($arrow == "1") {
      $get_months = date_i18n($increment_format, strtotime('+' . $i . ' ' . $tabs, strtotime($get_month_act)));
    } else {
      $get_months = date_i18n($increment_format, strtotime('-' . $i . ' ' . $tabs, strtotime($get_month_act)));
    }
    $date_start = date_i18n('Y-m-d 00:01', strtotime($get_months));
    $date_end = date_i18n('Y-m-d 23:59', strtotime($get_months));
    $label_month = date_i18n('l', strtotime($date_start));
    $label_year = '<span>' . date_i18n('F', strtotime($date_start)) . '</span>';
    $filter_data = array('tabs' => $tabs, 'tabs_format' => $tabs_date, 'tabs_length' => $tab_length, 'start_dt' => $date_start, 'end_dt' => $date_end, 'label_month' => $label_month, 'label_year' => $label_year, 'get_dates' => $get_months, 'inc_format' => $increment_format, 'calview' => $status, 'current_date' => $get_month_act);
  } elseif ($status == 'yearly') {
    $proper_formatted_date = date_parse_from_format('Y-m-d', $get_month_act);
    $year = ($proper_formatted_date['year']) ? $proper_formatted_date['year'] : date_i18n('Y');
    $get_month_act = $year . '-01-01';
    $tabs = 'year';
    $tabs_date = 'Y';
    $tab_length = 10;
    $increment_format = 'Y';
    if ($arrow == "1") {
      $get_months = date_i18n($increment_format . '-01-01', strtotime('+' . $i . ' ' . $tabs, strtotime($get_month_act)));
    } else {
      $get_months = date_i18n($increment_format . '-01-01', strtotime('-' . $i . ' ' . $tabs, strtotime($get_month_act)));
    }
    $date_start = date_i18n('Y-01-01 00:01', strtotime($get_months));
    $date_end = date_i18n('Y-12-31 23:59', strtotime($get_months));
    $label_month = date_i18n('Y', strtotime($date_start));
    $label_year = '';
    $filter_data = array('tabs' => $tabs, 'tabs_format' => $tabs_date, 'tabs_length' => $tab_length, 'start_dt' => $date_start, 'end_dt' => $date_end, 'label_month' => $label_month, 'label_year' => $label_year, 'get_dates' => $get_months, 'inc_format' => $increment_format, 'calview' => $status, 'current_date' => $get_month_act);
  } elseif ($status == 'weekly') {
    $proper_formatted_date = date_parse_from_format('Y-m-d', $get_month_act);
    $year = ($proper_formatted_date['year']) ? $proper_formatted_date['year'] : date_i18n('Y');
    $month = ($proper_formatted_date['month']) ? $proper_formatted_date['month'] : date_i18n('m');
    $day = ($proper_formatted_date['day']) ? $proper_formatted_date['day'] : date_i18n('d');
    $get_month_act = $year . '-' . $month . '-' . $day;
    $date = new DateTime($get_month_act);
    $week = $date->format("W");
    $year = $date->format("Y");
    $date_array = eventer_get_start_end_date_byweek($week - 1, $year);
    $increment_format = 'Y-m-d';
    $date_start = $date_array[0];
    $date_end = $date_array[1];
    $tabs = 'week';
    $tabs_date = 'Y-m-d';
    $label_month = date_i18n(get_option('date_format'), strtotime($date_start)) . ' ' . esc_html__('to', 'eventer') . ' ' . date_i18n(get_option('date_format'), strtotime($date_end));
    $label_year = '';
    $tab_length = 1;
    $next_result = 1;
    if ($arrow == "1") {
      $get_months = date_i18n($increment_format, strtotime('+' . $i . ' ' . $tabs, strtotime($get_month_act)));
    } else {
      $get_months = date_i18n($increment_format, strtotime('-' . $i . ' ' . $tabs, strtotime($get_month_act)));
    }
    $filter_data = array('tabs' => $tabs, 'tabs_format' => $tabs_date, 'tabs_length' => $tab_length, 'start_dt' => $date_start, 'end_dt' => $date_end, 'label_month' => $label_month, 'label_year' => $label_year, 'get_dates' => $get_months, 'inc_format' => $increment_format, 'calview' => $status, 'current_date' => $get_month_act);
  } else {
    $proper_formatted_date = date_parse_from_format('Y-m-d', $get_month_act);
    $year = ($proper_formatted_date['year']) ? $proper_formatted_date['year'] : date_i18n('Y');
    $month = ($proper_formatted_date['month']) ? $proper_formatted_date['month'] : date_i18n('m');
    $day = ($proper_formatted_date['day']) ? $proper_formatted_date['day'] : '01';
    $get_month_act = $year . '-' . $month . '-' . $day;
    $tabs = 'month';
    $tabs_date = 'M';
    $tab_length = 12;
    $increment_format = 'Y-m';
    $arrow = 1;
    if ($arrow == "1") {
      $get_months = date_i18n($increment_format, strtotime('+' . $i . ' ' . $tabs, strtotime($get_month_act)));
    } else {
      $get_months = date_i18n($increment_format, strtotime('-' . $i . ' ' . $tabs, strtotime($get_month_act)));
    }
    $date_start = date_i18n('Y-m-01 00:01', strtotime($get_months));
    $date_end = date_i18n('Y-m-t 23:59', strtotime($get_months));
    $label_month = date_i18n('F', strtotime($date_start));
    $label_year = '<span>' . date_i18n('Y', strtotime($date_start)) . '</span>';
    $filter_data = array('tabs' => $tabs, 'tabs_format' => $tabs_date, 'tabs_length' => $tab_length, 'start_dt' => $date_start, 'end_dt' => $date_end, 'label_month' => $label_month, 'label_year' => $label_year, 'get_dates' => $get_months, 'inc_format' => $increment_format, 'calview' => $status, 'current_date' => $get_month_act);
  }
  return $filter_data;
}
/*
 * eventer_month_wise_events function
 * This function is used to display events on list page while using ajax query, this function sends json data to init.js file where all the html     generated using the values of this function and display them to list page
 */
if (!function_exists('eventer_month_wise_events')) {
  function eventer_month_wise_events()
  {
    if (!wp_verify_nonce($_REQUEST['nonce'], "eventer_create_nonce_for_month")) {
      exit();
    }
    $halfyear = $fullyear = $halfyear_msg = $fullyear_msg = $next_class = $next_result = '';
    $output = $monthsgrid = array();
    $get_month_act = $_REQUEST['get_month'];
    $arrow = $_REQUEST['arrow'];
    $shortcode_attr = (isset($_REQUEST['shortcode_attr'])) ? $_REQUEST['shortcode_attr'] : array();
    $filters = (isset($_REQUEST['filters'])) ? $_REQUEST['filters'] : array();
    $status = (isset($_REQUEST['stat'])) ? $_REQUEST['stat'] : '';
    $event_count = $shortcode_attr['count'];
    if ($status == "month") {
      $event_count = 1000;
    }
    global $wp_locale;
    $date_array = '';
    $list_layout = $shortcode_attr['view'];
    $ids = (isset($shortcode_attr['ids'])) ? $shortcode_attr['ids'] : array();
    $from_date = (isset($shortcode_attr['efrom'])) ? $shortcode_attr['efrom'] : '';
    $to_date = (isset($shortcode_attr['eto'])) ? $shortcode_attr['eto'] : '';
    $terms_cats = (isset($shortcode_attr['terms_cats'])) ? $shortcode_attr['terms_cats'] : array();
    $terms_cats = (isset($filters['terms_cats'])) ? array_merge($terms_cats, (array) $filters['terms_cats']) : $terms_cats;
    $terms_tags = (isset($shortcode_attr['terms_tags'])) ? $shortcode_attr['terms_tags'] : array();
    $terms_tags = (isset($filters['terms_tags'])) ? array_merge($terms_tags, (array) $filters['terms_tags']) : $terms_tags;
    $terms_venue = (isset($shortcode_attr['terms_venue'])) ? $shortcode_attr['terms_venue'] : array();
    $terms_venue = (isset($filters['terms_venue'])) ? array_merge($terms_venue, (array) $filters['terms_venue']) : $terms_venue;
    $terms_organizer = (isset($shortcode_attr['terms_organizer'])) ? $shortcode_attr['terms_organizer'] : array();
    $terms_organizer = (isset($filters['terms_organizer'])) ? array_merge($terms_organizer, (array) $filters['terms_organizer']) : $terms_organizer;
    $event_ids = eventer_merge_all_ids($ids, $terms_cats, $terms_tags, $terms_venue, $terms_organizer);
    $eventer_keyword_id = (isset($shortcode_attr['eventerid'])) ? array_map('trim', explode(',', $shortcode_attr['eventerid'])) : array();
    $eventer_keyword_id = array_unique(array_filter($eventer_keyword_id));
    $eventer_new_ids = array_merge($event_ids, (array) $eventer_keyword_id);
    $jump = (isset($_REQUEST['datajump'])) ? $_REQUEST['datajump'] : '0';
    $pagination = $shortcode_attr['pagination'];
    $pagin = ($pagination) ? get_query_var('pagin') : 1;
    $last_event_date = get_option('eventer_extreme_last_event_date');
    $last_event_date = ($last_event_date == '') ? '2100-01-01' : $last_event_date;
    $first_event_date = get_option('eventer_extreme_first_event_date');

    for ($i = 0; $i <= $jump; $i++) {
      if ($shortcode_attr['month_filter'] != '') {
        $filtering_data = eventer_filtering_values($status, $get_month_act, $i);
        $tabs = $filtering_data['tabs'];
        $tabs_date = $filtering_data['tabs_format'];
        $tab_length = $filtering_data['tabs_length'];
        $date_start = $filtering_data['start_dt'];
        $date_end = $filtering_data['end_dt'];
        $label_month = $filtering_data['label_month'];
        $label_year = $filtering_data['label_year'];
        $get_months = $filtering_data['get_dates'];
        $increment_format = $filtering_data['inc_format'];
        $event_count = 1000;
      }
      $date_array = ($from_date != '' && $to_date != '') ? array($from_date, $to_date) : array($date_start, $date_end);
      if (is_search()) {
        $events = eventer_search_result_data($eventer_new_ids, $status, $date_array, $pagin, $event_count, $shortcode_attr['type'], $shortcode_attr['event_until']);
      } else {
        $events = eventer_get_events_array($eventer_new_ids, $status, $date_array, $pagin, $event_count, $shortcode_attr['type'], $shortcode_attr['event_until'], $shortcode_attr['pass']);
      }
      if ($events['results'] > 0) {
        break;
      }
    }
    $eventer = $events['events'];
    for ($is = 1; $is <= $tab_length; $is++) {
      if (strtotime($last_event_date) < strtotime(date_i18n('Y-m-d 23:59', strtotime('+' . $is . ' ' . $tabs, strtotime($get_months))))) {
        break;
      }

      $next_result = 1;
      $monthsgrid[] = array('lival' => date_i18n($increment_format, strtotime('+' . $is . ' ' . $tabs, strtotime($get_months))), 'lishow' => date_i18n($tabs_date, strtotime('+' . $is . ' ' . $tabs, strtotime($get_months))));
    }
    $previous_result = (strtotime($first_event_date) < strtotime($get_months)) ? 1 : '';
    $prevmonth = date_i18n($increment_format, strtotime('-1 ' . $tabs, strtotime($get_months)));
    $nextmonth = date_i18n($increment_format, strtotime('+1 ' . $tabs, strtotime($get_months)));
    $longjump = '';
    if ($arrow == "1" && $tabs == "month") {
      $halfyear_msg = esc_html__('Search events for next six months.', 'eventer');
      $fullyear_msg = esc_html__('Search events for next twelve months.', 'eventer');
      $next_class = "next-month";
      $longjump = $nextmonth;
    } elseif ($tabs == "month") {
      $halfyear_msg = esc_html__('Search events for previous six months.', 'eventer');
      $fullyear_msg = esc_html__('Search events for previous twelve months.', 'eventer');
      $next_class = "";
      $longjump = $prevmonth;
    }
    if (empty($eventer)) {
      $datacon = "1";
    } else {
      $datacon = '';
    }

    //if(empty(eventer)) exit();
    $stime_format = esc_attr(eventer_get_settings('start_time_format'));
    $etime_format = esc_attr(eventer_get_settings('end_time_format'));
    $time_separator = esc_attr(eventer_get_settings('time_separator'));
    $date_format = esc_attr(eventer_get_settings('eventer_date_format'));
    $stime_format = ($stime_format == '') ? get_option('time_format') : $stime_format;
    $etime_format = ($etime_format == '') ? get_option('time_format') : $etime_format;
    $date_format = ($date_format == '') ? get_option('date_format') : $date_format;
    $time_separator = ($time_separator == '') ? ' - ' : $time_separator;
    $recurring_icon_switch = eventer_get_settings('eventer_recurring_icon_yes');
    $title_data_passed = array();
    $badge_switch = eventer_get_settings('eventer_show_badges');
    $title_data_passed['recurring'] = $recurring_icon_switch;
    $title_data_passed['badges'] = $badge_switch;
    foreach ($eventer as $event_data) {
      $key = $event_data['start'];
      $value = $event_data['id'];
      if (get_post_status($value) != 'publish') {
        continue;
      }

      $event_month = $event_year = $start_list = $event_time = $event_venue = $event_title = $image_url = '';
      $string_date = strtotime($key);
      $eventer_data = eventer_explore_event_ids($string_date, $value, $stime_format, $etime_format, $time_separator, $shortcode_attr['event_until']);
      $event_all_dates = get_post_meta($value, 'eventer_event_frequency_type', true);
      $event_dynamic_dates = get_post_meta($value, 'eventer_event_multiple_dt_inc', true);
      $recurring_icon = (($recurring_icon_switch == "on" && is_numeric($event_all_dates)) || ($recurring_icon_switch == "on" && $event_dynamic_dates != '')) ? '1' : '';
      $event_ymd = date_i18n('Y-m-d', $eventer_data['show_counter']);
      $eventer_url = ($eventer_data['google_url'] == '') ? eventer_generate_endpoint_url('edate', $event_ymd, get_permalink($value)) : $eventer_data['google_url'];
      $event_day = date_i18n('d', $eventer_data['show_counter']);
      $event_month = date_i18n('F', $eventer_data['show_counter']);
      $event_year = esc_attr(date_i18n(' Y', $eventer_data['show_counter']));
      $event_time = $eventer_data['etime'];
      $original_event = eventer_wpml_original_post_id($value);
      $title_data_passed['event_cdate'] = strtotime($key);
      $title_data_passed['all_dates'] = get_post_meta($value, 'eventer_all_dates', true);
      eventer_update_date_wise_bookings_table($value, date_i18n('Y-m-d 00:00:00', strtotime($key)), array());
      $title_data_passed['booked_tickets'] = eventer_update_date_wise_bookings_table($value, date_i18n('Y-m-d 00:00:00', strtotime($key)), array(), 2);
      $event_title = apply_filters('eventer_styled_listing_title', $title = '', $value, $title_data_passed);
      if ($eventer_data['elocation'] != '') {
        $event_venue = $eventer_data['elocation'];
      }
      if (has_post_thumbnail($value)) {
        $image_url = get_the_post_thumbnail_url($value, 'eventer-thumb-170x170');
      }

      if ($list_layout == 'minimal') {
        $single_day_set = (isset($eventer_data['multiday_start']) && $eventer_data['multiday_start'] == '') ? '<span class="eventer-event-day pull-left">' . date_i18n('d', $eventer_data['show_counter']) . '</span>' : '';
        $event_year = ($single_day_set != '') ? $event_year : '';
        $event_month = ($single_day_set != '') ? $event_month : $eventer_data['multiday_start'] . '-' . $eventer_data['multiday_end'];
      } else {
        $multiday_class = (isset($eventer_data['multiday_start']) && $eventer_data['multiday_start'] != '') ? 'eventer-event-multiday ' : 'eventer-event-day ';
        $multiday_start = (isset($eventer_data['multiday_start']) && $eventer_data['multiday_start'] != '') ? '<span class="' . esc_attr($multiday_class) . ' eventer-event-multiday-border">' . $eventer_data['multiday_start'] . '</span>' : '';
        $multiday_end = (isset($eventer_data['multiday_end']) && $eventer_data['multiday_end'] != '') ? '<span class="' . esc_attr($multiday_class) . '">' . $eventer_data['multiday_end'] . '</span>' : '';
        $single_day_set = (isset($eventer_data['multiday_start']) && $eventer_data['multiday_start'] != '') ? $multiday_start . $multiday_end : '<span class="' . esc_attr($multiday_class) . '">' . date_i18n('d', $eventer_data['show_counter']) . '</span>';
      }

      $border_left_color = ($eventer_data['color']) ? ' style="border-left-color:' . $eventer_data['color'] . '"' : '';
      $border_top_color = ($eventer_data['color']) ? ' style="border-top-color:' . $eventer_data['color'] . '"' : '';
      $time_icon = ($shortcode_attr['view'] == "compact") ? '<i class="eventer-icon-clock"></i>' : '';
      $output[] = array('da' => $single_day_set, 'multidays' => $multiday_start, 'mon' => $event_month, 'year' => $event_year, 'time' => $event_time, 'venue' => $event_venue, 'title' => $event_title, 'bordertop' => $border_top_color, 'borderleft' => $border_left_color, 'image_url' => $image_url, 'ticon' => $time_icon, 'color' => $eventer_data['color'], 'eventer_url' => $eventer_url, 'recurring_icon' => $recurring_icon, 'tabs' => $tabs);
      $confirm_data = 1;
    }

    echo wp_send_json(array('layout' => $shortcode_attr['view'], 'lidata' => $output, 'noresult' => $datacon, 'thismonth' => $label_month, 'thisyear' => $label_year, 'prevmonth' => $prevmonth, 'nextmonth' => $nextmonth, 'blank' => esc_html__('Sorry, no more events available for this month.', 'eventer'), 'halfyear' => '<a class="eventer-btn show_month_events ' . $next_class . '" data-jump="5" data-arrow="' . $longjump . '">' . $halfyear_msg . '</a>', 'fullyear' => '<a class="eventer-btn show_month_events ' . $next_class . '" data-jump="11" data-arrow="' . $longjump . '">' . $fullyear_msg . '</a>', 'showmsg' => esc_html__('Sorry, there no more events found for your request.', 'eventer'), 'monthsgrid' => $monthsgrid, 'next_result' => $next_result, 'previous_result' => $previous_result));
    wp_die();
  }
  add_action('wp_ajax_eventer_month_wise_events', 'eventer_month_wise_events');
  add_action('wp_ajax_nopriv_eventer_month_wise_events', 'eventer_month_wise_events');
}

/*
 * eventer_get_start_end_date_byweek function
 * This function is used to set start and end date by week number and year, this used when viewing weekly events on list page
 */
function eventer_get_start_end_date_byweek($week, $year)
{
  $time = strtotime("1 January $year", time());
  $day = date('w', $time);
  $time += ((7 * $week) + 1 - $day) * 24 * 3600;
  $return[0] = date('Y-m-d 00:01', $time);
  $time += 6 * 24 * 3600;
  $return[1] = date('Y-m-d 23:59', $time);
  return $return;
}

/*
 * eventer_pagination function
 * This function is used to display pagination for list/grid view
 */
if (!function_exists('eventer_pagination')) {
  function eventer_pagination($pages = '', $paged = 1, $range = 4, $grid_id = '')
  {
    $default = '';
    if (is_tax()) {
      $permalink = get_term_link(get_query_var('term'), get_query_var('taxonomy'));
    } elseif (is_post_type_archive()) {
      $permalink = get_post_type_archive_link(get_query_var('post_type'));
    } else {
      $permalink = get_the_permalink();
    }
    if (is_search()) {
      $default = 1;
      $permalink = home_url('/');
      $search_term = '%s';
      $permalink = esc_url(add_query_arg('p', ''));
    }
    $pagi = '';
    $showitems = $range + 1;
    if (1 != $pages) {
      $pagi .= '<ul class="eventer-pagination">';
      if ($paged != 1) {
        $pagi .= '<li><a href="' . esc_url(eventer_generate_endpoint_url('pagin', 1, $permalink, $default) . $grid_id) . '" title="' . __('First', 'eventer') . '">' . esc_html__('First', 'eventer') . '</a></li>';
      }
      for ($i = 1; $i <= $pages; $i++) {
        if (1 != $pages && (!($i >= $paged + $range || $i <= $paged - $range) || $pages <= $showitems)) {
          $pagi .= ($paged == $i) ? "<li class=\"active\"><span>" . $i . "</span></li>" : "<li><a href='" . esc_url(eventer_generate_endpoint_url('pagin', $i, $permalink, $default) . $grid_id) . "' class=\"\">" . $i . "</a></li>";
        }
      }
      if ($paged != $pages) {
        $pagi .= '<li><a href="' . esc_url(eventer_generate_endpoint_url('pagin', $pages, $permalink, $default) . $grid_id) . '" title="' . __('Last', 'eventer') . '">' . esc_html__('Last', 'eventer') . '</a></li>';
      }
      $pagi .= '</ul>';
    }
    return $pagi;
  }
}

/*
 * eventer_add_query_var function
 * This function is used to set query string variables so that they could easily accessible while add/remove from URL
 */
if (!function_exists('eventer_add_query_var')) {
  function eventer_add_query_var($vars)
  {
    $vars[] = "booking_status";
    $vars[] = "reg";
    $vars[] = "pagenum";
    return $vars;
  }
  add_filter('query_vars', 'eventer_add_query_var');
}

/*
 * eventer_encode_security_registration function
 * This function is used to set Unique number for registration code, so that it could not be understandable for everyone
 * The start value is the total of first and second digit of generated number and end variable is the total of second last and last digit of             generated number, if total of start and end is not greater than 14 then user can not see tickets using any hack
 */
if (!function_exists('eventer_encode_security_registration')) {
  function eventer_encode_security_registration($reg = '', $start = 8, $end = 9)
  {
    if ($reg == '') {
      return;
    }
    //If blank registration number passed
    $random_number_start = rand(0, $start); //Generate any unique number starting from 0 to $start
    $random_number_end = rand(0, $end); //Generate any unique number starting from 0 to $end
    $random_number_start_second = $start - $random_number_start;
    $random_number_end_second = $end - $random_number_end;
    $total_corners = $start + $end;
    $calculated_position = ($total_corners <= 14) ? $random_number_start : $random_number_end_second;
    $calculate = $total_corners + $calculated_position;
    $create_number = $reg * $calculate;
    return $random_number_start . $random_number_start_second . $create_number . $random_number_end . $random_number_end_second;
  }
}

/*
 * eventer_decode_security_registration function
 * This function is used to decode the registration code to get actual registration ID of registrant
 */
if (!function_exists('eventer_decode_security_registration')) {
  function eventer_decode_security_registration($reg = '')
  {
    if ($reg == '' || strlen($reg < 6)) {
      return;
    }
    //If reg code length is not greater than equal to 6 digits
    $first_one = $reg[0]; //Getting first digit
    $first_two = $reg[1];
    $last_one = $reg[strlen($reg) - 1];
    $second_last = $reg[strlen($reg) - 2];
    $registration_code = substr($reg, 2, -2); //Getting the number between $start and $end
    $start = $first_one + $first_two;
    $end = $last_one + $second_last;
    $total_corners = $start + $end;
    $calculated_position = ($total_corners <= 14) ? $first_one : $last_one;
    $calculate = $total_corners + $calculated_position;
    $actual_reg = (!is_float($registration_code / $calculate)) ? $registration_code / $calculate : '';
    return array('reg_id' => $actual_reg, 'reg_pos' => $total_corners);
  }
}
/*
 * eventer_pass_email_registration function
 * This function is used to email the registration information to event manager and registrant
 */
if (!function_exists('eventer_pass_email_registration')) {
  function eventer_pass_email_registration($reg_id = '', $pre = '1')
  {
    $registrant_details = eventer_get_registrant_details('id', $reg_id); //Getting all registered details by registration ID

    if (empty($registrant_details)) {
      return;
    }

    $eventer_id = $registrant_details->eventer;
    $tickets = $registrant_details->tickets;
    $registrant_id = $registrant_details->id;
    $registrant_email = $registrant_details->email;
    $amount = $registrant_details->amount;
    $eventer_date = $registrant_details->eventer_date;
    $eventer_date_formatted = date_i18n(get_option('date_format'), strtotime($eventer_date));
    $transaction_id = ($registrant_details->transaction_id != '') ? $registrant_details->transaction_id : md5(uniqid($registrant_id, true));
    if ($registrant_details->transaction_id == '') {
      eventer_update_registrant_details(array('transaction_id' => $transaction_id), $registrant_id, array("%s", "%s"));
    }
    $payment_status = $registrant_details->status;
    $user_details = $registrant_details->user_details;
    $paymentmode = $registrant_details->paymentmode;
    $organizer = wp_get_object_terms($eventer_id, 'eventer-organizer');
    $organizer_email = $completed_url_tkt = $pending_url_tkt = $failed_url_tkt = '';
    $headers = array();
    if (!is_wp_error($organizer) && !empty($organizer)) {
      foreach ($organizer as $org) {
        $organizer_id = $org->term_id;
        $organizer_email = get_term_meta($organizer_id, 'organizer_email', true);
        break;
      }
    }
    $sender = ($organizer_email != '') ? $organizer_email : get_option('admin_email');
    //If event do not have any event manager or event manager do not have email address then email will be forwarded to admin

    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $sender . '>';
    $headers[] = "MIME-Version: 1.0" . "\r\n";
    $headers[] = "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
    if ($pre == '1') {
      //This is the condition when user fills registration info and ticket details
      //Only event manager will get this email
      $registration_content = eventer_get_settings('pre_registration_content');
      $registration_content_switch = eventer_get_settings('pre_registration_content_switch');
      if ($registration_content_switch == '0') {
        return 1;
      }
      $subject = esc_html__('Ticket Pre Registration Email', 'eventer');
      $to = '';
    } elseif ($pre == '2') {
      //This is the case if tickets are paid and using PayPal
      $registration_content = eventer_get_settings('payment_confirmation_content');
      $registration_content_switch = eventer_get_settings('payment_confirmation_content_switch');
      if ($registration_content_switch == '0') {
        return 1;
      }
      $subject = esc_html__('Payment verification email', 'eventer');
      $to = $registrant_email;
    } else {
      //This is the case if ticket is booked in free or offline mode
      $registration_content = eventer_get_settings('payment_confirmation_content');
      $registration_content_switch = eventer_get_settings('payment_confirmation_content_switch');
      if ($registration_content_switch == '0') {
        return 1;
      }
      $subject = esc_html__('Ticket Registration email', 'eventer');
      $to = $registrant_email;
    }
    $user_fields_val = $registrant_tickets_name = $registrant_tickets_vals = array();
    if ($registration_content != '') {
      $message = apply_filters('eventer_filter_email_content_body', '', $registrant_details, $registration_content);
    } else {
      $message = "<p>" . esc_html__('New Registrant Details', 'eventer') . "<p>";
      $message .= "<p>" . esc_html__('Registrant ID', 'eventer') . ": " . $registrant_id . "<p>";
      $message .= "<p>" . esc_html__('Registrant Email', 'eventer') . ": " . $registrant_email . "<p>";
      $message .= "<p>" . esc_html__('Amount Paid', 'eventer') . ": " . $amount . "<p>";
    }

    if ($to != '') {
      wp_mail($to, $subject, $message, $headers);
    }
    $email_status = wp_mail($sender, $subject, $message, $headers);
    if ($email_status) {
      return 1;
    } else {
      return 0;
    }
  }
}

function eventer_search_ticket($name, $array, $loop = 1, $pid = '')
{
  $last_key = null;
  $name = ($pid != '') ? $pid : $name;
  $search = ($pid != '') ? 'pid' : 'name';
  foreach ($array as $key => $val) {
    if ($val[$search] == $name) {
      $last_key = $key;
      if ($loop == 1) {
        break;
      }
    }
  }
  return $last_key;
}
/*
 * eventer_registrant_tickets function
 * This function is used to insert registrant all details to the table
 * This function have hook of ajax so that it could work only on ajax call
 */
if (!function_exists('eventer_registrant_tickets')) {
  function eventer_registrant_tickets()
  {
    if (check_ajax_referer('eventer_create_nonce_for_registrant', 'booking_nonce', false)) {
      echo 'there is something went wrong.';
      exit();
    }
    //$email_cookie = (isset($_COOKIE["reg_email"]))?$_COOKIE["reg_email"]:'';
    $new_already_booked = array();
    $reg_email = (isset($_POST['reg_mail'])) ? $_POST['reg_mail'] : '';
    $eventer_id = esc_attr($_POST['eventer_id']);
    $tickets = (isset($_REQUEST['tickets'])) ? $_REQUEST['tickets'] : array();
    $eventer_date = esc_attr($_POST['reg_event_date']);
    $eventer_time = (isset($_POST['reg_event_time']) && $_POST['reg_event_time'] != '') ? esc_attr($_POST['reg_event_time']) : '00:00:00';
    global $wpdb;
    $table_name = $wpdb->prefix . "eventer_registrant";
    $file = 'eventer_booking_csv';
    $export_query = $wpdb->get_results("SELECT * FROM $table_name WHERE `eventer`= $eventer_id AND `email`='$reg_email'", ARRAY_A);

    $table_name_tickets = $wpdb->prefix . "eventer_tickets";

    $registrations = [];
    if ($export_query) {
      $tickets_booked = $export_query[0]['tickets'];

      $tickets_booked = unserialize($tickets_booked);
      if ($tickets_booked) {
        foreach ($tickets_booked as $tbook) {
          $ticket_booked_name = $tbook['name'];
          $saved_ticket = $wpdb->get_results("SELECT * FROM $table_name_tickets WHERE `event` = $eventer_id AND `date` = '$eventer_date $eventer_time' AND `name` = '$ticket_booked_name' AND `restricts` = 1", ARRAY_A);
          if ($saved_ticket) {
            echo json_encode(['reg_invalid' => '1', 'ticket_name' => $ticket_booked_name]);
            wp_die();
          }
        }
      }
    }
    $tickets_ser = (!empty($tickets)) ? serialize($tickets) : array();
    $formdata = (isset($_POST['reg_data'])) ? $_POST['reg_data'] : array();
    $services = (isset($_POST['services'])) ? $_POST['services'] : array();
    $registrants = (isset($_POST['registrants'])) ? $_POST['registrants'] : array();

    $reg_details = serialize($formdata);
    $current_date = date_i18n('Y-m-d G:i');
    $amount = (isset($_POST['amount'])) ? $_POST['amount'] : '';

    $original_event = eventer_wpml_original_post_id($eventer_id);

    $cart_status = (isset($_POST['cart_status'])) ? $_POST['cart_status'] : '';

    $eventer_time_slot = (isset($_POST['reg_event_slot'])) ? $_POST['reg_event_slot'] : '';
    $status = 'Pending';
    $book_type = (isset($_REQUEST['book_type'])) ? $_REQUEST['book_type'] : 'eventer';
    $eventer_start_time = get_post_meta($eventer_id, 'eventer_event_start_dt', true);
    $eventer_allday = get_post_meta($eventer_id, 'eventer_event_all_day', true);
    $event_start_time_str = strtotime($eventer_start_time);
    $eventer_st_time = ($eventer_time != '00:00:00' && $eventer_time != '') ? date_i18n("H:i", strtotime($eventer_time)) : date_i18n("H:i", $event_start_time_str);
    if ($book_type == 'woo') {
      global $woocommerce;
      if ($cart_status != '') {
        foreach ($woocommerce->cart->get_cart() as $key => $item) {
          $cart_product_id = $item['product_id']; // the product ID
          if ($cart_status == $cart_product_id && strtotime($eventer_date . ' ' . $eventer_st_time) == $item['wceventer_date']) {
            $woocommerce->cart->remove_cart_item($key);
          }
        }
      }

      $eventer_url = eventer_generate_endpoint_url('edate', $eventer_date, get_permalink($eventer_id));
      $woo_checkout_name = $woo_checkout_email = '';
      foreach ($tickets as $woo_ticket) {
        if ($woo_ticket['number'] == '') {
          continue;
        }

        $pid = $woo_ticket['pid'];
        $translated_id = (function_exists('icl_object_id')) ? icl_object_id($pid, 'product', false, ICL_LANGUAGE_CODE) : $pid;
        $product_id = $pid;
        $number = $woo_ticket['number'];
        $this_ticket_name = $woo_ticket['name'];
        if ($translated_id == '') {
          $product_id = icl_makes_duplicates($product_id);
        }
        $event_custom_price = $woo_ticket['price'];
        if (!has_term('eventer', 'product_cat', $product_id)) {
          continue;
        }

        if (get_post_type($product_id) != 'product') {
          continue;
        }

        $this_ticket_registrants = (isset($registrants[$this_ticket_name])) ? $registrants[$this_ticket_name] : array();
        if ($woo_checkout_name == '') {
          foreach ($this_ticket_registrants as $woo_checkout_data) {
            $woo_checkout_name = $woo_checkout_data['name'];
            $woo_checkout_email = $woo_checkout_data['email'];
            setcookie('woo_checkout_user_name',  $woo_checkout_name, (time() + 3600), "/");
            setcookie('woo_checkout_user_email',  $woo_checkout_email, (time() + 3600), "/");
            //print_r($_COOKIE);
            break;
          }
        }

        foreach ($woocommerce->cart->get_cart() as $key => $item) {
          $item_id = $item['wceventer_id'];
          $cart_product_id = $item['product_id']; // the product ID
          if ($eventer_id == $item_id && $product_id == $cart_product_id) {
            //$woocommerce->cart->remove_cart_item($key);
          }
        }
        $cart_item_data = array('wceventer_name' => apply_filters('eventer_raw_event_title', '', $eventer_id), 'wceventer_id' => $eventer_id, 'wceventer_date' => strtotime($eventer_date . ' ' . $eventer_st_time), 'wceventer_time' => date_i18n(get_option('time_format'), strtotime($eventer_st_time)), 'wceventer_url' => $eventer_url, 'eventer_custom_price' => $event_custom_price, 'eventer_registrants' => $this_ticket_registrants, 'wceventer_product' => 'ticket', '_eventer_custom_title' => $this_ticket_name, 'wceventer_allday' => $eventer_allday, 'wceventer_slot' => $eventer_time, 'wceventer_slot_title' => $eventer_time_slot);
        WC()->cart->add_to_cart($product_id, $number, '', array(), $cart_item_data);
      }
      if (!empty($services)) {
        foreach ($services as $woo_service) {
          $service_name = $woo_service['name'];
          $service_type = $woo_service['value'];
          if ($service_type == '') {
            continue;
          }

          $service_pid = $woo_service['pid'];
          $service_cost = $woo_service['cost'];
          if (!has_term('eventer_services', 'product_cat', intval($service_pid))) {
            continue;
          }

          if (get_post_type($service_pid) != 'product') {
            continue;
          }

          foreach ($woocommerce->cart->get_cart() as $key => $item) {
            $item_id = $item['wceventer_id'];
            $cart_product_id = $item['product_id']; // the product ID
            if ($eventer_id == $item_id && $service_pid == $cart_product_id) {
              $woocommerce->cart->remove_cart_item($key);
            }
          }
          $cart_item_data = array('wceventer_name' => apply_filters('eventer_raw_event_title', '', $eventer_id), 'wceventer_id' => $eventer_id, 'wceventer_date' => strtotime($eventer_date . ' ' . $eventer_st_time), 'wceventer_time' => date_i18n(get_option('time_format'), strtotime($eventer_st_time)), 'wceventer_services' => $service_type, 'eventer_custom_price' => $service_cost, 'wceventer_url' => $eventer_url, 'wceventer_product' => 'service');
          WC()->cart->add_to_cart($service_pid, 1, '', array(), $cart_item_data);
        }
      }
      $checkout_js_settings = eventer_get_settings('eventer_checkout_js');
      $checkout_js_settings = (empty($checkout_js_settings)) ? array() : $checkout_js_settings;
      $all_gateway = get_option('woocommerce_stripe_settings');
      $stripe_enabled = (isset($all_gateway['enabled']) && $all_gateway['enabled'] == 'yes') ? 1 : 0;
      ob_start();
      echo '<div class="widget_shopping_cart_content">';
      woocommerce_mini_cart();
      echo '</div>';
      $output = ob_get_contents();
      ob_end_clean();
      echo wp_json_encode(array('reg' => '', 'woo' => $output, 'stripe_error' => '1', 'stripe_msg' => '', 'reg_invalid' => '0'));
      wp_die();
    }
    $stripe_status_success = '2';
    $reg_email = (isset($_POST['reg_mail'])) ? $_POST['reg_mail'] : '';
    $reg_name = (isset($_POST['reg_name'])) ? $_POST['reg_name'] : '';
    $secret = '';
    if ($book_type == 'stripe') {
      $card_credentials = (isset($_REQUEST['card_cred'])) ? $_REQUEST['card_cred'] : '';
      $token = $card_credentials['token'];
      $stripe_publishable_key = eventer_get_settings('eventer_stripe_publishable_key');
      $stripe_secret_key = eventer_get_settings('eventer_stripe_secret_key');
      $itemName = get_the_title($eventer_id);
      $itemNumber = $eventer_id;
      $itemPrice = $amount;
      $currency = eventer_get_settings('eventer_paypal_currency');
      \Stripe\Stripe::setApiKey($stripe_secret_key);
      $intent = \Stripe\PaymentIntent::create([
        'payment_method_data' => [
          'type' => 'card',
          'card' => ['token' => $token],
        ],
        'amount' => ($currency != 'JPY') ? ($itemPrice * 100) : $itemPrice,
        'currency' => $currency,
        'description' => $itemName,
        'metadata' => array(
          'order_id' => $itemNumber . '-' . wp_rand(1000, 999999),
        ),
        'confirmation_method' => 'manual',
        'confirm' => true,
      ]);
      $data = eventer_generatePaymentResponse($intent);
      $stripe_response = $intent->jsonSerialize();
      $verified_amount_received = $stripe_response['amount_received'];
      $verified_status = $stripe_response['status'];
      $transaction_id = $stripe_response['charges']['data'][0]['balance_transaction'];
      $deducted_amount = ($currency != 'JPY') ? ($itemPrice * 100) : $itemPrice;
      if ($verified_status == 'succeeded' && $verified_amount_received == $deducted_amount) {
        $stripe_status_success = '1';
      } else {
        $data = json_decode($data, true);
        if (isset($data['payment_intent_client_secret'])) {
          $secret = $data['payment_intent_client_secret'];
        }
      }
    }
    if (empty($registrants) && !empty($tickets)) {
      foreach ($tickets as $set_reg) {
        $registrants[$set_reg['name']] = array(array('name' => $reg_name, 'email' => $reg_email));
      }
    }
    $update_new_val = array();
    if (!empty($tickets)) {
      $all_dynamics = array();
      $total_booked = 0;
      foreach ($tickets as $new_ticket) {
        $ticket_ids = (isset($new_ticket['id'])) ? $new_ticket['id'] : '';
        $ticket_numbers = (isset($new_ticket['number'])) ? $new_ticket['number'] : '';
        if ($ticket_numbers <= 0) {
          continue;
        }

        $all_dynamics[$ticket_ids] = $ticket_numbers;
        $total_booked = intval($total_booked) + intval($ticket_numbers);
      }
      if (get_post_meta($eventer_id, 'eventer_common_ticket_count', true) != '') {
        $get_all_tickets = eventer_update_date_wise_bookings_table($eventer_id, $eventer_date . ' ' . $eventer_time, array(), 2, 1, true);
        if ($get_all_tickets) {
          foreach ($get_all_tickets as $get_ticket) {
            $all_tickets = (isset($get_ticket['dynamic'])) ? $get_ticket['dynamic'] : '';
            eventer_update_date_wise_bookings_table($eventer_id, $eventer_date . ' ' . $eventer_time, array(array('id' => $all_tickets, 'number' => $total_booked)), 1, 1, true);
          }
        }
      } else {
        eventer_update_date_wise_bookings_table($eventer_id, $eventer_date . ' ' . $eventer_time, $tickets, 1, 1, true);
      }
    }
    $ip = eventer_client_ip();
    $user_reg_id = get_current_user_id();
    $user_system_data = serialize(array('ip' => $ip, 'services' => $services, 'email_pre' => "1", 'registrants' => $registrants, 'time_slot' => $eventer_time, 'slot_title' => $eventer_time_slot));
    global $wpdb;
    $table_name = $wpdb->prefix . "eventer_registrant";
    $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO $table_name
				( eventer, eventer_date, username, email, user_details, tickets, ctime, status, amount, user_system, user_id)
				VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d )",
        array($eventer_id, $eventer_date, $reg_name, $reg_email, $reg_details, $tickets_ser, $current_date, $status, $amount, $user_system_data, $user_reg_id)
      )
    );

    $autocomplete_orders = eventer_get_settings('eventer_order_autocomplete');
    $autocomplete_orders = ($status == 'completed') ? '1' : $autocomplete_orders;
    $lastid = $wpdb->insert_id;
    $dont_show_ticket = eventer_encode_security_registration($lastid, 6, 8);
    $show_ticket = eventer_encode_security_registration($lastid, 9, 8);
    $registration_id = ($amount > 0 && $autocomplete_orders != '1') ? $dont_show_ticket : $show_ticket;
    if ($lastid && $book_type == 'stripe' && $stripe_status_success == '1') {
      $update_in = array('transaction_id' => $transaction_id, 'status' => "Success", 'paypal_details' => serialize($stripe_response), 'paymentmode' => 'Stripe', 'amount' => ($verified_amount_received / 100));
      $vals_in = array("%s", "%s", "%s", "%f");
      eventer_update_registrant_details($update_in, $lastid, $vals_in);
      $registration_id = $show_ticket;
    }

    eventer_pass_email_registration($lastid, "1");
    echo wp_json_encode(array('reg' => $registration_id, 'woo' => '', 'stripe_error' => '', 'stripe_msg' => '', 'secret' => $secret, 'reg_id' => $lastid, 'reg_invalid' => '0'));
    wp_die();
  }
  add_action('wp_ajax_eventer_registrant_tickets', 'eventer_registrant_tickets');
  add_action('wp_ajax_nopriv_eventer_registrant_tickets', 'eventer_registrant_tickets');
}

function eventer_confirm_payment_stripe()
{
  $secret = (isset($_REQUEST['secret'])) ? $_REQUEST['secret'] : '';
  $lastid = (isset($_REQUEST['reg_id'])) ? $_REQUEST['reg_id'] : '';
  $stripe_secret_key = eventer_get_settings('eventer_stripe_secret_key');
  \Stripe\Stripe::setApiKey($stripe_secret_key);
  $intent = \Stripe\PaymentIntent::retrieve($secret);
  try {
    $intent->confirm();
  } catch (\Stripe\Error\InvalidRequest $err) {
    die(json_error($err->getMessage()));
  } catch (\Stripe\Error\Card $err) {
    die(json_error($err->getMessage()));
  }
  $stripe_response = $intent->jsonSerialize();
  $verified_amount_received = $stripe_response['amount_received'];
  $verified_status = $stripe_response['status'];
  $transaction_id = $stripe_response['charges']['data'][0]['balance_transaction'];
  if ($lastid && $verified_status == 'succeeded') {
    $update_in = array('transaction_id' => $transaction_id, 'status' => "Success", 'paypal_details' => serialize($stripe_response), 'paymentmode' => 'Stripe', 'amount' => ($verified_amount_received / 100));
    $vals_in = array("%s", "%s", "%s", "%f");
    eventer_update_registrant_details($update_in, $lastid, $vals_in);
    $registration_id = eventer_encode_security_registration($lastid, 9, 8);
  }
  if (
    $intent->status == 'requires_action' &&
    $intent->next_action->type == 'use_stripe_sdk'
  ) {
    # Tell the client to handle the action
    echo json_encode([
      'requires_action' => true,
      'payment_intent_client_secret' => $intent->client_secret
    ]);
  } else if ($intent->status == 'succeeded') {
    # The payment didn’t need any additional actions and completed!
    # Handle post-payment fulfillment
    echo wp_json_encode(array('reg' => $registration_id, 'woo' => '', 'stripe_error' => '', 'stripe_msg' => '', 'secret' => '', 'success' => true));
  } else {
    # Invalid status
    http_response_code(500);
    echo json_encode(['error' => 'Invalid PaymentIntent status']);
  }
  wp_die();
}
add_action('wp_ajax_eventer_confirm_payment_stripe', 'eventer_confirm_payment_stripe');
add_action('wp_ajax_nopriv_eventer_confirm_payment_stripe', 'eventer_confirm_payment_stripe');

/*
 * eventer_get_registrant_details function
 * This function is used to get all details of registrant
 * $field required table row name
 * $val required value related to that field
 */
if (!function_exists('eventer_get_registrant_details')) {
  function eventer_get_registrant_details($field, $val)
  {
    if ($field != '' && $val != '') {
      $field = esc_attr($field);
      $val = esc_attr($val);
      global $wpdb;
      $table_name = $wpdb->prefix . "eventer_registrant";
      $reg_details = $wpdb->get_row("SELECT * FROM $table_name WHERE $field = $val");
      if ($reg_details) {
        return $reg_details;
      } else {
        return false;
      }
    }
  }
}
function eventer_get_terms_front($taxonomy = '', $event = '', $metas = array())
{
  $eventer_terms = get_the_terms($event, $taxonomy);
  $result = $meta_vals = array();
  if (!is_wp_error($eventer_terms) && !empty($eventer_terms)) {
    foreach ($eventer_terms as $term) {
      if (!empty($metas)) {
        $meta_vals = array();
        foreach ($metas as $meta) {
          $meta_vals[$meta] = get_term_meta($term->term_id, $meta, true);
        }
      }
      $result[] = array('name' => $term->name, 'slug' => $term->slug, 'id' => $term->term_id, 'metas' => $meta_vals);
    }
  }
  return $result;
}
/*
 * eventer_update_registrant_details function
 * This function is used to update details of registrants
 */
if (!function_exists('eventer_update_registrant_details')) {
  function eventer_update_registrant_details($fieldval, $where, $param)
  {
    if ($where != '') {
      global $wpdb;
      $table_name = $wpdb->prefix . "eventer_registrant";
      $wpdb->update(
        $table_name,
        $fieldval,
        array('id' => $where),
        $param,
        array('%d')
      );
    }
  }
}

/*
 * eventer_contact_organizer function
 * This function is used to contact event manager using button provided at event details page
 * runs only on ajax call
 */
if (!function_exists('eventer_contact_organizer')) {
  function eventer_contact_organizer()
  {
    $organizer_fields = (isset($_POST['org_data'])) ? $_POST['org_data'] : array();
    $eventer_id = (isset($_POST['eventer_id'])) ? $_POST['eventer_id'] : '';
    $eventer_date = (isset($_POST['eventer_date'])) ? $_POST['eventer_date'] : '';
    if (!empty($organizer_fields) && !empty($eventer_id)) {
      $organizer = get_the_terms($eventer_id, 'eventer-organizer');
      $organizer_email = '';
      $headers = array();
      if (!is_wp_error($organizer) && !empty($organizer)) {
        $organizer_id = $organizer[0]->term_id;
        $organizer_email = get_term_meta($organizer_id, 'organizer_email', true);
      }
      $sender = ($organizer_email != '') ? $organizer_email : get_option('admin_email');
      $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $sender . '>';
      $headers[] = "MIME-Version: 1.0" . "\r\n";
      $headers[] = "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
      $message = "<p>" . esc_html__('Someone contacted for below event', 'eventer') . "<p>";
      $message .= "<p>" . esc_url(eventer_generate_endpoint_url('edate', $eventer_date, get_permalink($eventer_id))) . "</p>";
      foreach ($organizer_fields as $field) {
        $message .= '<p>' . $field['name'] . ': ' . $field['value'] . '</p>';
        if (filter_var($field['value'], FILTER_VALIDATE_EMAIL)) {
          $person_email = $field['value'];
        }
      }
      $subject = esc_html__('Query for event', 'eventer') . ' ' . apply_filters('eventer_raw_event_title', '', $eventer_id);
      wp_mail($sender, $subject, $message, $headers);
    }
    wp_die();
  }
  add_action('wp_ajax_eventer_contact_organizer', 'eventer_contact_organizer');
  add_action('wp_ajax_nopriv_eventer_contact_organizer', 'eventer_contact_organizer');
}

/*
 * eventer_url_endpoint function
 * This function is used to create pretty permalink
 */
if (!function_exists('eventer_url_endpoint')) {
  function eventer_url_endpoint()
  {
    add_rewrite_endpoint('edate', EP_PERMALINK);
    add_rewrite_endpoint('pagin', EP_ALL);
  }
  add_action('init', 'eventer_url_endpoint');
}

/*
 * eventer_setup_seo_endpoint function
 * This function is used to set permalink settings on different permalink settings
 */
if (!function_exists('eventer_setup_seo_endpoint')) {
  function eventer_setup_seo_endpoint()
  {
    $event_permalinks = eventer_get_settings('eventer_event_permalink');
    $event_permalink = empty($event_permalinks) ? 'eventer' : $event_permalinks;
    // Ensures the $query_vars['item'] is available
    add_rewrite_tag('%pagin%', '([^&]+)');

    // Requires flushing endpoints whenever the
    // front page is switched to a different page
    $page_on_front = get_option('page_on_front');

    // Match the front page and pass item value as a query var.
    add_rewrite_rule('^pagin/([^/]*)/?', 'index.php?page_id=' . $page_on_front . '&pagin=$matches[1]', 'top');
    // Match non-front page pages.
    add_rewrite_rule('^(.*)/pagin/([^/]*)/?', 'index.php?pagename=$matches[1]&pagin=$matches[2]', 'top');
    // Match eventer archive page.
    //add_rewrite_rule('post-type/(.+)/(.+?)/?$', 'index.php?post-type=$matches[1]&pagin=$matches[2]','top');
    add_rewrite_rule($event_permalink . '/pagin/?([0-9]{1,})?$', 'index.php?post_type=eventer&pagin=$matches[1]', 'top');
  }
  add_action('init', 'eventer_setup_seo_endpoint', 1);
}

/*
 * eventer_disable_canonical_redirect_for_front_page function
 */
if (!function_exists('eventer_disable_canonical_redirect_for_front_page')) {
  // http://wordpress.stackexchange.com/a/220484/52463
  // In order to keep WordPress from forcing a redirect to the canonical
  // home page, the redirect needs to be disabled.
  function eventer_disable_canonical_redirect_for_front_page($redirect)
  {
    if (is_page() && $front_page = get_option('page_on_front')) {
      if (is_page($front_page)) {
        $redirect = false;
      }
    }

    return $redirect;
  }
  add_filter('redirect_canonical', 'eventer_disable_canonical_redirect_for_front_page');
}

/*
 * eventer_get_template_part function
 * This function is used to set appropriate template for event pages
 */
if (!function_exists('eventer_get_template_part')) {
  function eventer_get_template_part($slug, $name = null, $load = true)
  {
    do_action('get_template_part_' . $slug, $slug, $name);

    // Setup possible parts
    $templates = array();
    if (isset($name)) {
      $templates[] = $slug . '-' . $name . '.php';
    }

    $templates[] = $slug . '.php';

    // Allow template parts to be filtered
    $templates = apply_filters('eventer_get_template_part', $templates, $slug, $name);

    // Return the part that is found
    return eventer_get_template_path($templates, $load, false);
  }
}

if (!function_exists('eventer_get_template_path')) {
  /* Extend locate_template from WP Core
     * Define a location of your plugin file dir to a constant in this case = PLUGIN_DIR_PATH
     * Note: PLUGIN_DIR_PATH - can be any folder/subdirectory within your plugin files
     */
  function eventer_get_template_path($template_names, $load = false, $require_once = true)
  {
    $located = false;

    // Try to find a template file
    foreach ((array) $template_names as $template_name) {

      // Continue if template is empty
      if (empty($template_name)) {
        continue;
      }

      // Trim off any slashes from the template name
      $template_name = ltrim($template_name, '/');
      // Check child theme first
      if (file_exists(trailingslashit(get_stylesheet_directory()) . 'eventers/' . $template_name)) {
        $located = trailingslashit(get_stylesheet_directory()) . 'eventers/' . $template_name;
        break;

        // Check parent theme next
      } elseif (file_exists(trailingslashit(get_template_directory()) . 'eventers/' . $template_name)) {
        $located = trailingslashit(get_template_directory()) . 'eventers/' . $template_name;
        break;

        // Check theme compatibility last
      } elseif (file_exists(trailingslashit(EVENTER__PLUGIN_PATH) . $template_name)) {
        $located = trailingslashit(EVENTER__PLUGIN_PATH) . $template_name;
        break;
      }
    }

    if ((true == $load) && !empty($located)) {
      load_template($located, $require_once);
    }

    return $located;
  }
}

function eventer_append_template_with_arguments($slug = null, $name = null, array $params = array())
{
  global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
  $located = '';
  do_action("get_template_part_{$slug}", $slug, $name);
  $templates = array();
  if (isset($name)) {
    $template_name = "{$slug}-{$name}.php";
  }

  if (file_exists(trailingslashit(get_stylesheet_directory()) . '' . $template_name)) {
    $located = trailingslashit(get_stylesheet_directory()) . '' . $template_name;
    //break;

    // Check parent theme next
  } elseif (file_exists(trailingslashit(get_template_directory()) . '' . $template_name)) {
    $located = trailingslashit(get_template_directory()) . '' . $template_name;
    //break;

    // Check theme compatibility last
  } elseif (file_exists(trailingslashit(EVENTER__PLUGIN_PATH) . $template_name)) {
    $located = trailingslashit(EVENTER__PLUGIN_PATH) . $template_name;
    //break;
  }
  if (is_array($wp_query->query_vars)) {
    extract($wp_query->query_vars, EXTR_SKIP);
  }
  if (!$located) {
    return;
  }

  extract($params, EXTR_SKIP);
  require $located;
}

/*
 * eventer_execute_registrant_event function
 * This function is used to generate tickets information on details page
 * This function also send required values to event details page
 */
if (!function_exists('eventer_execute_registrant_event')) {
  function eventer_execute_registrant_event($first, $eventer_id = '', $date_dynamic = '', $time_dynamic = '00:00:00')
  {
    $eventer_id = ($eventer_id == '') ? get_the_ID() : $eventer_id;
    $original_event = eventer_wpml_original_post_id($eventer_id);
    //if ( is_singular('eventer') && get_post_type()=='eventer') {
    $event_start_date = get_post_meta($original_event, 'eventer_event_start_dt', true);
    $event_end_date = get_post_meta($original_event, 'eventer_event_end_dt', true);
    $event_end_date = ($event_end_date != '') ? $event_end_date : $event_start_date;
    $event_start_dt_str = strtotime($event_start_date);
    $event_end_dt_str = strtotime($event_end_date);
    $stime_format = eventer_get_settings('start_time_format');
    $etime_format = eventer_get_settings('end_time_format');
    $time_separator = eventer_get_settings('time_separator');
    $date_format = eventer_get_settings('eventer_date_format');
    $stime_format = ($stime_format == '') ? get_option('time_format') : $stime_format;
    $etime_format = ($etime_format == '') ? get_option('time_format') : $etime_format;
    $date_format = ($date_format == '') ? get_option('date_format') : $date_format;
    $time_separator = ($time_separator == '') ? ' - ' : $time_separator;
    $all_day = get_post_meta($original_event, 'eventer_event_all_day', true);
    $days_diff = eventer_dateDiff($event_start_date, $event_end_date);
    $usersystem = '';
    $all_dates = get_post_meta($original_event, 'eventer_all_dates', true);
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $event_current_date = get_query_var('edate');
    $event_current_date = ($event_current_date) ? $event_current_date : $date_dynamic;
    if (!$event_current_date) {

      $event_time = date_i18n("H:i", $event_start_dt_str);
      $all_dates_with_time = preg_filter('/$/', ' ' . $event_time, $all_dates);
      $all_dates_with_time = array_filter($all_dates_with_time, function ($date) {
        return (strtotime($date) >= date_i18n('U'));
      });
      sort($all_dates_with_time);
      if (!empty($all_dates_with_time)) {
        $event_new_key = key($all_dates_with_time);
        $event_cdate = strtotime($all_dates_with_time[$event_new_key]);
      } else {
        $event_cdate = $event_start_dt_str;
      }
    } else {
      $event_cdate = strtotime($event_current_date);
    }

    //$booked_eventer_tickets = get_post_meta($original_event, 'specific_eventer_tickets', true);
    $eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
    $booked_tickets = eventer_update_date_wise_bookings_table($original_event, date_i18n('Y-m-d ' . $time_dynamic, $event_cdate), array()); //This functions is calling for existing users to store ticket info in new table
    $booked_tickets = eventer_update_date_wise_bookings_table($original_event, date_i18n('Y-m-d ' . $time_dynamic, $event_cdate), array(), 2);
    $registrant = $registrant_position = $user_system = $user_system_old = '';
    $registrant_id = get_query_var('reg');
    $registrant_vals = eventer_decode_security_registration($registrant_id);
    $registrant_vals = (is_array($registrant_vals) && isset($registrant_vals['reg_id'])) ? $registrant_vals : array('reg_id' => '', 'reg_pos' => '');
    $booked_registrant_tickets = $user_details = array();
    $transaction_id = (isset($_REQUEST['tx'])) ? $_REQUEST['tx'] : '';
    $ticket_id = $registrant_email = $amount = $username = $mode = $ticket_id_db = '';
    if ($first == 1 || $registrant_vals['reg_pos'] <= 14) {
      if ($registrant_vals) {
        $registrant = $registrant_vals['reg_id'];
        $registrant_position = (isset($registrant_vals['reg_pos'])) ? $registrant_vals['reg_pos'] : '';
        $registrants = eventer_get_registrant_details('id', $registrant);
        $registrants = ($woocommerce_ticketing == 'on') ? eventer_get_registrant_details('eventer', $registrant) : $registrants;
        //$registrants = (!(array) $registrants)?$registrants:(!empty($registrants_woo) && get_post_type($registrants_woo)=='shop_order' && $woocommerce_ticketing=='on')?$registrants_woo:'';
        if ($registrants) {
          $booked_registrant_tickets = unserialize($registrants->tickets);
          $registrant_email = $registrants->email;
          $ticket_id = $registrants->id . '-' . $registrants->eventer;
          $ticket_id_db = $registrants->id;
          $amount = $registrants->amount;
          $username = $registrants->username;
          $user_details = unserialize($registrants->user_details);
          $user_details = ($user_details) ? array_column($user_details, 'value', 'name') : array();
          $user_system = unserialize($registrants->user_system);
          $user_system_old = $user_system;
          $usersystem = $registrants->user_system;
          $payment_details = $registrants->paypal_details;
          $pos = strpos($payment_details, 'stripe');
          $payment_mode = 'Offline';
          if ($pos) {
            $payment_mode = 'Stripe';
          }
          if ($registrant_position <= 14) {
            $user_system['email_pre'] = "1";
          }
          if ($registrant_position > 14) {
            $user_system['email_post'] = "1";
          }
        }
        if ($transaction_id) {
          $update_reg_data = (empty($user_system_old)) ? array('transaction_id' => $transaction_id, 'paymentmode' => 'paypal') : array('transaction_id' => $transaction_id, 'paymentmode' => 'paypal', 'user_system' => serialize($user_system));
          $vals_in = (!empty($user_system_old)) ? array("%s", "%s", "%s") : array("%s", "%s");
          if ((!isset($user_system_old['email_pre']) && !isset($user_system_old['email_post'])) || (!isset($user_system_old['email_post']) && $registrant_position > 14)) {
            eventer_update_registrant_details($update_reg_data, $registrant, $vals_in);
          }
        } elseif ((!isset($user_system_old['email_pre']) && !isset($user_system_old['email_post'])) || (!isset($user_system_old['email_post']) && $registrant_position > 14)) {
          $transaction_id = md5(uniqid($registrant, true));
          $status = "completed";
          $mode = "Free";
          if ($amount > 0) {
            $mode = $payment_mode;
          }
          $update_in = (empty($user_system_old)) ? array('transaction_id' => $transaction_id, 'status' => $status, 'paymentmode' => $mode) : array('transaction_id' => $transaction_id, 'status' => $status, 'paymentmode' => $mode, 'user_system' => serialize($user_system));
          $vals_in = (!empty($user_system_old)) ? array("%s", "%s", "%s", "%s") : array("%s", "%s", "%s");
          eventer_update_registrant_details($update_in, $registrant, $vals_in);
        }
        if ((!isset($user_system_old['email_pre']) && !isset($user_system_old['email_post'])) || (!isset($user_system_old['email_post']) && $registrant_position > 14)) {
          $email_sent_status = eventer_pass_email_registration($registrant, "4");
        }
      }
    } elseif ($first == 2) {
      if ($registrant_vals) {
        $registrant = $registrant_vals['reg_id'];
        $registrant_position = $registrant_vals['reg_pos'];
        $registrants = eventer_get_registrant_details('id', $registrant);
        if ($registrants) {
          $booked_registrant_tickets = unserialize($registrants->tickets);
          $registrant_email = $registrants->email;
          $ticket_id = $registrants->id . '-' . $registrants->eventer;
          $amount = $registrants->amount;
          $ticket_id_db = $registrants->id;
          $username = $registrants->username;
          $usersystem = $registrants->user_system;
          $user_details = unserialize($registrants->user_details);
          $user_details = ($user_details) ? array_column($user_details, 'value', 'name') : array();
        }
      }
    }
    if ($days_diff > 0) {
      $event_time_show = date_i18n($date_format, $event_start_dt_str) . ' ' . $time_separator . ' ' . date_i18n($date_format, $event_end_dt_str);
    } elseif ($all_day) {
      $event_time_show = esc_html__('All Day', 'eventer');
    } else {
      $st_time = date_i18n('H:i', $event_start_dt_str);
      $en_time = date_i18n('H:i', $event_end_dt_str);
      $new_st_time = strtotime($st_time . ' ' . date_i18n('Y-m-d', $event_cdate));
      $new_en_time = strtotime($en_time . ' ' . date_i18n('Y-m-d', $event_cdate));
      $event_time_show = date_i18n($stime_format, $new_st_time) . ' ' . $time_separator . ' ' . date_i18n($etime_format, $new_en_time);
    }
    $time_slot_values = $time_slot_title = '';
    $time_slot_setup = get_post_meta($original_event, 'eventer_time_slot', true);
    if ($time_slot_setup) {
      $time_slot_values .= '<select class="eventer-time-slot">';
      $time_slot_values .= '<option value="00:00:00">' . esc_html__('Select Time', 'eventer') . '</option>';
      foreach ($time_slot_setup as $slot) {
        $time_slot_title = ($time_slot_title == '' && $time_dynamic == $slot['start'] . ':00') ? $slot['title'] . ' ' . $slot['start'] . '-' . $slot['end'] : $time_slot_title;
        $time_slot_values .= '<option ' . (($time_dynamic == $slot['start'] . ':00') ? 'selected' : '') . ' value="' . esc_attr(date_i18n('H:i:s', strtotime($slot['start']))) . '">' . $slot['title'] . ' ' . $slot['start'] . '-' . $slot['end'] . '</option>';
      }
      $time_slot_values .= '</select>';
    }
    $eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_currency_symbol(eventer_get_settings('eventer_paypal_currency')) : eventer_get_currency_symbol(get_option('woocommerce_currency'));
    $woo_currency_position = get_option('woocommerce_currency_pos');
    $woo_currency_position = ($woo_currency_position == "left" || $woo_currency_position == "left_space") ? "suffix" : "postfix";

    $eventer_currency_position = ($woocommerce_ticketing != 'on') ? eventer_get_settings('eventer_currency_position') : $woo_currency_position;
    return array('event_id' => $eventer_id, 'event_cdate' => $event_cdate, 'event_time_show' => $event_time_show, 'all_dates' => $all_dates, 'booked_tickets' => $booked_tickets, 'booked_registrant_tickets' => $booked_registrant_tickets, 'ticket_id' => $ticket_id, 'registrant_email' => $registrant_email, 'registrant' => $registrant, 'username' => $username, 'mode' => $mode, 'reg_position' => $registrant_position, 'eventer_currency' => $eventer_currency, 'currency_position' => $eventer_currency_position, 'usersystem' => $usersystem, 'allday' => $all_day, 'days_diff' => $days_diff, 'time_slot' => $time_dynamic, 'time_slot_values' => $time_slot_values, 'time_slot_title' => $time_slot_title, 'start_str' => $event_start_dt_str, 'end_str' => $event_end_dt_str, 'ticket_id_db' => $ticket_id_db, 'woo_ticketing' => $woocommerce_ticketing, 'user_details' => $user_details);
    //}
  }
}
add_filter('eventer_registration_data_collect', 'eventer_execute_registrant_event', 10, 4);
/*
 * eventer_generate_endpoint_url function
 * This function is used to create permalinks for event according to value set in settings page of dashboard
 */
if (!function_exists('eventer_generate_endpoint_url')) {
  add_action('init', 'eventer_generate_endpoint_url');
  function eventer_generate_endpoint_url($qarg = '', $qval = '', $qurl = '', $default = '')
  {
    if ($qarg != '' && $qval != '') {
      $raw_url = $qurl;
      $query = array();
      $parts = parse_url($qurl);
      if (isset($parts['query'])) {
        parse_str($parts['query'], $query);
        $raw_url = strtok($qurl, '?');
      }
      $arg = esc_attr($qarg);
      $val = esc_attr($qval);
      $qurl = ($qurl == '') ? get_permalink() : $raw_url;
      $url = rtrim($qurl, "/");
      $permalink_status = get_option('permalink_structure');
      if ($permalink_status != '' && $default != 1) {
        $st_url = esc_url($url) . '/' . $arg . '/' . $val;
        return esc_url(add_query_arg($query, $st_url));
      } else {
        $query[$qarg] = $qval;
        return esc_url(add_query_arg($query, $url));
      }
    }
  }
}

/*
 * eventer_save_events function
 * This function is used to save event in Google, icalendar etc.
 * This function has been modified as one of plugin's user noticed some issue and rectified according to his best knowledge https://support.imithemes.com/help/ticket/108012
 */
if (!function_exists('eventer_save_events')) {
  function eventer_save_events()
  {
    $query_string_str = base64_decode($_SERVER['QUERY_STRING']);
    parse_str($query_string_str, $query_string);
    if (isset($query_string['action']) && isset($query_string['id']) && isset($query_string['key']) && $query_string['key'] == 'imic_save_event') {
      $action = $query_string['action'];
      $id = $query_string['id'];
      $key = $query_string['key'];
      $edate = $query_string['edate'];
      $custom_post = get_post($id);
      $title = $custom_post->post_title;
      $content = $custom_post->post_content;
      $excerpt = $custom_post->post_excerpt;
      if (!empty($excerpt)) {
        $content = $excerpt;
      }
      $eventer_venue = get_the_terms($id, 'eventer-venue');
      $elocation = '';
      if (!is_wp_error($eventer_venue) && !empty($eventer_venue)) {
        foreach ($eventer_venue as $venue) {
          $location_address = get_term_meta($venue->term_id, 'venue_address', true);
          $location_coordinates = get_term_meta($venue->term_id, 'venue_coordinates', true);
          $venue_name = $venue->name;
          if ($location_address != '') {
            $elocation = $location_address;
          } else {
            $elocation = $venue->name;
          }
        }
      }
      $imic_event_address = $elocation;
      $eventStartDate = get_post_meta($id, 'eventer_event_start_dt', true);
      $eventEndDate = get_post_meta($id, 'eventer_event_end_dt', true);
      $all_day = get_post_meta($id, 'eventer_event_all_day', true);
      $eventer_timezone = eventer_get_settings('eventer_save_event');
      $random_name = substr(rand() . rand() . rand() . rand(), 0, 20);
      $start_dt_str = strtotime($eventStartDate);
      $end_dt_str = strtotime($eventEndDate);
      $triggerOn_start = date_i18n('Y-m-d H:i:s', $start_dt_str);
      $triggerOn_end = date_i18n('Y-m-d H:i:s', $end_dt_str);
      $event_url = get_permalink($id);
      switch ($action) {
        case 'gcalendar':
          $google_save_url = 'https://www.google.com/calendar/render?action=TEMPLATE';
          $content .= '<p>' . $event_url . '</p>';
          if ($all_day) {
            $google_save_url .= '&dates=' . date_i18n("Ymd", strtotime("$triggerOn_start"));
            $google_save_url .= '/' . date_i18n("Ymd", strtotime("$triggerOn_end"));
          } else {
            if ($eventer_timezone == "on") {
              $google_save_url .= '&dates=' . date_i18n("Ymd\THis", strtotime("$triggerOn_start"));
              $google_save_url .= '/' . date_i18n("Ymd\THis", strtotime("$triggerOn_end"));
            } else {
              $google_save_url .= '&dates=' . date_i18n("Ymd\THis\Z", strtotime("$triggerOn_start"));
              $google_save_url .= '/' . date_i18n("Ymd\THis\Z", strtotime("$triggerOn_end"));
            }
          }
          $google_save_url .= '&location=' . urlencode($imic_event_address);
          $google_save_url .= '&text=' . urlencode($title);
          $google_save_url .= '&details=' . urlencode($content);
          wp_redirect($google_save_url);
          exit;
          break;
        case 'icalendar':
          ob_start();
          header("Content-Type: text/calendar; charset=utf-8");
          header("Content-Disposition: inline; filename=addto_calendar_" . $random_name . ".ics");
          $paragraph_breaks = array('</p>', '<br />', '<br>', '<hr />', '<hr>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '<ul>');
          $content = str_replace($paragraph_breaks, "\n\n", $content);
          $list_starts = array('<li>');
          $content = str_replace($list_starts, "- ", $content);
          $list_ends = array('</li>', '</ul>');
          $content = str_replace($list_ends, "\n\n", $content);
          $content = wp_strip_all_tags($content);
          $content = preg_replace('`\[[^\]]*\]`', '', $content);
          $content = addslashes($content);
          //$content = str_replace(array(",", ":", ";"), array("\,", "\:", "\;"), $content);
          $content = preg_replace('/\s+/', ' ', $content);
          $content = html_entity_decode($content);
          $imic_event_address = addslashes($imic_event_address);
          $imic_event_address = str_replace(array(",", ":", ";"), array("\,", "\:", "\;"), $imic_event_address);
          echo "BEGIN:VCALENDAR\n";
          echo "VERSION:2.0\n";
          echo "PRODID:Imitheme.com \n";
          echo "BEGIN:VEVENT\n";
          echo "UID:" . date_i18n('Ymd') . 'T' . date_i18n('His') . rand() . "\n";
          echo "DTSTAMP;TZID=UTC:" . current_time('Y-m-d H:i:s') . "\n";
          if ($eventer_timezone == "on") {
            //echo "DTSTART:" . get_gmt_from_date($triggerOn_start, "Ymd\THis") . "\n";
            //echo "DTEND:" . get_gmt_from_date($triggerOn_end, "Ymd\THis") . "\n";
          } else {
            //echo "DTSTART;TZID=UTC:" . date_i18n("Ymd\THis\Z", strtotime("$triggerOn_start")) . "\n";
            //echo "DTEND;TZID=UTC:" . date_i18n("Ymd\THis\Z", strtotime("$triggerOn_end")) . "\n";
          }
          echo "DTSTART;TZID=America/Los_Angeles:" . date_i18n("Ymd\THis", strtotime("$triggerOn_start")) . "\n";
          echo "DTEND;TZID=America/Los_Angeles:" . date_i18n("Ymd\THis", strtotime("$triggerOn_end")) . "\n";
          echo "SUMMARY:$title\n";
          echo "LOCATION:$imic_event_address\n";
          echo "DESCRIPTION:$content\n";
          echo "URL:$event_url\n";
          echo "END:VEVENT\n";
          echo "END:VCALENDAR\n";
          ob_flush();
          exit;
          break;
        case 'outlook':
          ob_start();
          header("Content-Type: text/calendar; charset=utf-8");
          header("Content-Disposition: inline; filename=addto_calendar_" . $random_name . ".ics");
          $content .= '<p>' . $event_url . '</p>';
          $paragraph_breaks = array('</p>', '<br />', '<br>', '<hr />', '<hr>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '<ul>');
          $content = str_replace($paragraph_breaks, "\n\n", $content);
          $list_starts = array('<li>');
          $content = str_replace($list_starts, "- ", $content);
          $list_ends = array('</li>', '</ul>');
          $content = str_replace($list_ends, "\n\n", $content);
          $content = wp_strip_all_tags($content);
          $content = preg_replace('`\[[^\]]*\]`', '', $content);
          $content = addslashes($content);
          //$content = str_replace(array(",", ":", ";"), array("\,", "\:", "\;"), $content);
          $content = preg_replace('/\s+/', ' ', $content);
          $content = html_entity_decode($content);
          echo "BEGIN:VCALENDAR\n";
          echo "VERSION:2.0\n";
          echo "PRODID:Imitheme.com\n";
          echo "BEGIN:VEVENT\n";
          echo "UID:" . date_i18n('Ymd') . 'T' . date_i18n('His') . "-" . rand() . "\n";
          echo "DTSTAMP;TZID=UTC:" . current_time('timestamp') . "\n";
          echo "DTSTART;TZID=America/Los_Angeles:" . date_i18n("Ymd\THis", strtotime("$triggerOn_start")) . "\n";
          echo "DTEND;TZID=America/Los_Angeles:" . date_i18n("Ymd\THis", strtotime("$triggerOn_end")) . "\n";
          echo "SUMMARY:$title\n";
          echo "LOCATION:$imic_event_address\n";
          echo "DESCRIPTION: $content\n";
          echo "URL:$event_url\n";
          echo "END:VEVENT\n";
          echo "END:VCALENDAR\n";
          ob_flush();
          exit;
          break;
        case 'outlooklive':
          $ical = "BEGIN:VCALENDAR
                        VERSION:2.0
                        PRODID:-//hacksw/handcal//NONSGML v1.0//EN
                        BEGIN:VEVENT
                        UID:" . md5(uniqid(mt_rand(), true)) . site_url() . "
                        DTSTAMP:" . date_i18n('Ymd') . 'T' . date_i18n('His') . "
                        DTSTART:TZID=" . date_i18n("Ymd\THis", strtotime("$eventStartDate")) . "
                        DTEND:TZID=" . date_i18n("Ymd\THis", strtotime("$eventEndDate")) . "
                        SUMMARY:" . $title . "
                        DESCRIPTION:" . $content . "
                        END:VEVENT
                        END:VCALENDAR";
          header('Content-type: text/calendar; charset=utf-8');
          header('Content-Disposition: inline; filename=calendar.ics');
          echo $ical;
          exit;
        case 'yahoo':
          $yahoo_url = 'https://calendar.yahoo.com/?view=d&v=60&type=20';
          $yahoo_url .= '&title=' . urlencode($title);
          $yahoo_url .= '&in_loc=' . urlencode($imic_event_address);
          $yahoo_url .= '&desc=' . urlencode($content);
          if ($eventer_timezone == "on") {
            $yahoo_url .= '&st=' . date_i18n("Ymd\THis", strtotime("$eventStartDate"));
            $yahoo_url .= '&et=' . date_i18n("Ymd\THis", strtotime("$eventEndDate"));
          } else {
            $yahoo_url .= '&st=' . date_i18n("Ymd\THis\Z", strtotime("$eventStartDate"));
            $yahoo_url .= '&et=' . date_i18n("Ymd\THis\Z", strtotime("$eventEndDate"));
          }
          wp_redirect($yahoo_url);
          exit;
          break;
      }
    }
  }
}
/* add action on init*/
add_action('init', 'eventer_save_events');

if (!function_exists('eventer_merge_all_ids')) {
  function eventer_merge_all_ids($ids = array(), $cats = array(), $tags = array(), $venues = array(), $organizer = array())
  {
    if (empty($ids) && empty($cats) && empty($tags) && empty($venues) && empty($organizer)) {
      return array();
    }
    $all_ids = $all_cats = $all_tags = $all_venues = $all_organizer = array();
    $operator = eventer_get_settings('eventer_taxonomy_search_operator');
    if ($operator == 'or') {
      if (!empty($ids)) {
        $all_ids = explode(',', $ids);
      }
      if (!empty($cats)) {
        $all_cats = get_objects_in_term(explode(',', $cats), 'eventer-category');
      }
      if (!empty($tags)) {
        $all_tags = get_objects_in_term(explode(',', $tags), 'eventer-tag');
      }
      if (!empty($venues)) {
        $all_venues = get_objects_in_term(explode(',', $venues), 'eventer-venue');
      }
      if (!empty($organizer)) {

        $all_organizer = get_objects_in_term(explode(',', $organizer), 'eventer-organizer');
      }
    } else {
      if (!empty($ids)) {
        $all_ids = explode(',', $ids);
      }
      if (!empty($cats)) {
        $all_cats_new = (array) $cats;
        foreach ((array) $cats as $cat) {
          $all_cats_terms = get_term_children($cat, 'eventer-category');
          $all_cats_new = array_merge($all_cats_new, $all_cats_terms);
        }
        $all_cats = get_objects_in_term($all_cats_new, 'eventer-category');
      }
      if (!empty($tags)) {
        $all_tags_new = (array) $tags;
        foreach ((array) $tags as $tag) {
          $all_tags_terms = get_term_children($tag, 'eventer-tag');
          $all_tags_new = array_merge($all_tags_new, $all_tags_terms);
        }
        $all_tags = get_objects_in_term($all_tags_new, 'eventer-tag');
      }
      if (!empty($venues)) {
        $all_venue_new = (array) $venues;
        foreach ((array) $venues as $venue) {
          $all_venue_terms = get_term_children($venue, 'eventer-venue');
          $all_venue_new = array_merge($all_venue_new, $all_venue_terms);
        }
        $all_venues = get_objects_in_term($all_venue_new, 'eventer-venue');
      }
      if (!empty($organizer)) {
        $all_organizer_new = (array) $organizer;
        foreach ((array) $organizer as $org) {
          $all_venue_terms = get_term_children($org, 'eventer-organizer');
          $all_organizer_new = array_merge($all_organizer_new, $all_venue_terms);
        }
        $all_organizer = get_objects_in_term($all_organizer_new, 'eventer-organizer');
      }
    }
    $all_terms_objects = array($all_ids, $all_cats, $all_tags, $all_venues, $all_organizer);
    $array = array_filter($all_terms_objects);
    $all_merged_ids = array_merge($all_ids, $all_cats, $all_tags, $all_venues, $all_organizer);
    $output_ids = (count($array) > 1) ? call_user_func_array('array_intersect', $array) : $array;
    $single_term_objects = (empty($output_ids)) ? array(23452345234523452345) : $output_ids;
    $output_ids = (count($array) == 1) ? $all_merged_ids : $single_term_objects;
    $output_ids = array_unique($output_ids);
    return $output_ids;
  }
  //add_action('init', 'eventer_merge_all_ids');
}

if (!function_exists('eventer_merge_all_ids_or')) {
  function eventer_merge_all_ids_or($ids = array(), $cats = array(), $tags = array(), $venues = array(), $organizer = array())
  {
    if (empty($ids) && empty($cats) && empty($tags) && empty($venues) && empty($organizer)) {
      return array();
    }

    $all_ids = $all_cats = $all_tags = $all_venues = $all_organizer = array();
    if (!empty($ids)) {
      $all_ids = explode(',', $ids);
    }
    if (!empty($cats)) {
      $all_cats = get_objects_in_term(explode(',', $cats), 'eventer-category');
    }
    if (!empty($tags)) {
      $all_tags = get_objects_in_term(explode(',', $tags), 'eventer-tag');
    }
    if (!empty($venues)) {
      $all_venues = get_objects_in_term(explode(',', $venues), 'eventer-venue');
    }
    if (!empty($organizer)) {
      $all_organizer = get_objects_in_term(explode(',', $organizer), 'eventer-organizer');
    }
    $all_terms_objects = array($all_ids, $all_cats, $all_tags, $all_venues, $all_organizer);
    $array = array_filter($all_terms_objects);
    $all_merged_ids = array_merge($all_ids, $all_cats, $all_tags, $all_venues, $all_organizer);
    $output_ids = (count($array) > 1) ? call_user_func_array('array_intersect', $array) : $array;
    $single_term_objects = (empty($output_ids)) ? array(23452345234523452345) : $output_ids;
    $output_ids = (count($array) == 1) ? $all_merged_ids : $single_term_objects;
    $output_ids = array_unique($output_ids);
    return $output_ids;
  }
  //add_action('init', 'eventer_merge_all_ids');
}

if (!function_exists('eventer_convert_timeformat_tojs')) {
  /*
     * eventer_convert_timeformat_tojs function
     * This function is used to change time format of fullcalendar based on set in General Settings of dashboard
     */
  function eventer_convert_timeformat_tojs($format)
  {
    $format_rules = array(
      'B' => '',
      'c' => 'u',
      'd' => 'dd',
      'D' => 'ddd',
      'F' => 'MMMM',
      'g' => 'h',
      'G' => 'H',
      'h' => 'hh',
      'H' => 'HH',
      'i' => 'mm',
      'I' => '',
      'j' => 'd',
      'l' => 'dddd',
      'L' => '',
      'm' => 'MM',
      'M' => 'MMM',
      'n' => 'M',
      'O' => '',
      'r' => '',
      's' => 'ss',
      'S' => 'S',
      't' => '',
      'T' => '',
      'U' => '',
      'w' => '',
      'W' => '',
      'y' => 'yy',
      'Y' => 'yyyy',
      'z' => '',
      'Z' => '',
    );
    $ret = '';
    for ($i = 0; $i < strlen($format); $i++) {
      if (isset($format_rules[$format[$i]])) {
        $ret .= $format_rules[$format[$i]];
      } else {
        $ret .= $format[$i];
      }
    }
    return $ret;
  }
  add_action('init', 'eventer_convert_timeformat_tojs');
}

if (!function_exists('eventer_wpml_original_post_id')) {
  /*
     * eventer_wpml_original_post_id function
     * This function is used to get original post from which the translation was made in WPML plugin
     */
  function eventer_wpml_original_post_id($ID = '')
  {
    $eventer_id = ($ID != '') ? $ID : get_the_ID();
    if (function_exists('icl_object_id') && class_exists('SitePress')) {
      global $sitepress;
      $eventer_id = icl_object_id($eventer_id, 'eventer', false, $sitepress->get_default_language());
    } elseif (function_exists('pll_get_post')) {
      $eventer_id = pll_get_post($eventer_id, pll_default_language());
      $eventer_id = (empty($eventer_id)) ? $ID : $eventer_id;
    }
    return ($eventer_id) ? $eventer_id : $ID;
  }
  add_action('init', 'eventer_wpml_original_post_id');
}
// Add image size
add_image_size('eventer-thumb-170x170', 170, 170, true);
add_image_size('eventer-thumb-600x400', 600, 400, true);
add_image_size('eventer-thumb-400x400', 400, 400, true);

$default_attribs = array('name' => array(), 'class' => array(), 'maxlength' => array(), 'multiple' => array(), 'id' => array(), 'data-tprice' => array(), 'type' => array(), 'name' => array(), 'value' => array(), 'class' => array(), 'data-mprice' => array(), 'style' => array(), 'data-booked' => array(), 'data-registrations' => array(), 'data-ticketid' => array(), 'data-tooltip' => array(), 'data-identify' => array(), 'data-cart' => array(), 'data-pid' => array(), 'data-limit' => array(), 'autocomplete' => array());

$eventer_allowed_tags = array(
  'select' => $default_attribs,
  'p' => $default_attribs,
  'strong' => $default_attribs,
  'div' => $default_attribs,
  'label' => $default_attribs,
  'input' => $default_attribs,
  'del' => $default_attribs,
  'span' => $default_attribs,
);
$eventer_btn_allowed_tags = array(
  'span' => $default_attribs,
  'u' => $default_attribs,
  'i' => $default_attribs,
  'b' => $default_attribs,
  'br' => $default_attribs,
  'strong' => $default_attribs,
  'del' => $default_attribs,
  'strike' => $default_attribs,
  'em' => $default_attribs,
  'img' => $default_attribs,
);

if (!function_exists('eventer_content')) {
  /*
     * eventer_content function
     * This function is used to load content on eveter.php file
     * This would work if user placed eventer.php file in child theme or parent theme folder
     * User just need to content of page.php file of activated theme to eventer.php file and use below function instead of the_content()
     * User also need to remove if and while loop from that file
     */
  function eventer_content()
  {
    if (is_singular('eventer')) {
      eventer_get_template_part('eventers/single', 'loop');
      eventer_get_template_part('eventers/single', 'options');
      return;
    }
    $terms_arg = $ids = '';

    if (is_tax('eventer-category')) {
      $template_design = eventer_get_settings('eventer_category_view');
      $terms_arg = 'terms_cats="' . get_queried_object()->term_id . '"';
    } elseif (is_tax('eventer-tag')) {
      $template_design = eventer_get_settings('eventer_tag_view');
      $terms_arg = 'terms_tags="' . get_queried_object()->term_id . '"';
    } elseif (is_tax('eventer-venue')) {
      $template_design = eventer_get_settings('eventer_venues_view');
      $terms_arg = 'terms_venue="' . get_queried_object()->term_id . '"';
    } elseif (is_tax('eventer-organizer')) {
      $template_design = eventer_get_settings('eventer_organizer_view');
      $terms_arg = 'terms_organizer="' . get_queried_object()->term_id . '"';
    } elseif (is_archive('eventer')) {
      $template_design = eventer_get_settings('eventer_archive_view');
      $ids = (isset($_REQUEST['eid'])) ? $_REQUEST['eid'] : '';
      $ids = 'ids="' . $ids . '"';
    } elseif (is_search('eventer')) {
      $template_design = eventer_get_settings('eventer_search_view');
    }
    $template_design = ($template_design != '') ? $template_design : "1";
    switch ($template_design) {
      case "1":
        $content_output = '[eventer_list ' . $ids . ' ' . $terms_arg . ' type="1" count="10" pagination="yes"]';
        break;
      case "2":
        $content_output = '[eventer_list ' . $ids . ' ' . $terms_arg . ' type="1" status="month" month_filter="1"]';
        break;
      case "3":
        $content_output = '[eventer_list ' . $ids . ' ' . $terms_arg . ' type="1" month_filter="1" view="minimal" count="10" pagination="yes"]';
        break;
      case "4":
        $content_output = '[eventer_list ' . $ids . ' ' . $terms_arg . ' type="1" view="minimal" status="month" month_filter="1"]';
        break;
      case "5":
        $content_output = '[eventer_grid ' . $ids . ' ' . $terms_arg . ' type="1" background="" column="3" pagination="yes" count="10"]';
        break;
    }
    return do_shortcode($content_output);
  }
}

function eventer_store_events_ids()
{
  $site_lang = EVENTER__LANGUAGE_CODE;
  $eventer_stored = get_option($site_lang . '_eventer_stored');
  if (!empty($eventer_stored)) {
    return;
  }

  $eventer_args = array('post_type' => 'eventer', 'posts_per_page' => -1);
  $eventer_lists = new WP_Query($eventer_args);
  $store_events = array();
  if ($eventer_lists->have_posts()) : while ($eventer_lists->have_posts()) : $eventer_lists->the_post();
      $store_events[get_the_ID()] = apply_filters('eventer_raw_event_title', '', get_the_ID());
    endwhile;
  endif;
  wp_reset_postdata();
  update_option($site_lang . '_eventer_stored', $store_events);
}
add_action('init', 'eventer_store_events_ids');
add_action('admin_init', 'eventer_store_events_ids');
function eventer_list_date_filters($atts, $filtering_data)
{
  $date_array = $time_icon = $calview = $today_btn = $date_range = $eventkeys = $search_range = '';
  $tabs = $filtering_data['tabs'];
  $tabs_date = $filtering_data['tabs_format'];
  $tab_length = $filtering_data['tabs_length'];
  $date_start = $filtering_data['start_dt'];
  $date_end = $filtering_data['end_dt'];
  $label_month = $filtering_data['label_month'];
  $label_year = $filtering_data['label_year'];
  $get_months = $filtering_data['get_dates'];
  $increment_format = $filtering_data['inc_format'];
  $cal_view = $filtering_data['calview'];
  $current_date = $filtering_data['current_date'];
  $event_count = 1000;
  $prev_btn = date_i18n($increment_format, strtotime('-1 ' . $tabs, strtotime($get_months)));
  $next_btn = date_i18n($increment_format, strtotime('+1 ' . $tabs, strtotime($get_months)));
  $last_event_date = get_option('eventer_extreme_last_event_date');
  $last_event_date = ($last_event_date == '') ? '2100-01-01' : $last_event_date;
  $date_filters = '';
  $date_filters .= '<div class="eventer-month-switcher">
											<div class="eventer-switcher-current-month">
												<div class="eventer-switcher-current-month-in">' . $label_month . $label_year . '</div>';
  $date_filters .= '<div class="eventer-switcher-actions">
													<a class="show_month_events prev-month single-run" title="Previous" data-jump="0" data-arrow="' . $prev_btn . '" href="javascript:void(0);"><i class="eventer-icon-arrow-left"></i></a>
													<a class="show_month_events next-month single-run" title="Next" data-jump="0" data-arrow="' . $next_btn . '" href="javascript:void(0);"><i class="eventer-icon-arrow-right"></i></a>
												</div>
											</div>';
  if ($atts['calview'] != '') {
    $date_filters .= '<div class="eventer-switcher-actions eventer-switcher-actions-view">';
    $calview = explode(',', $atts['calview']);
    foreach ($calview as $view) {
      switch ($view) {
        case 'yearly':
          $cal_label = esc_html__('Yearly', 'eventer');
          break;
        case 'weekly':
          $cal_label = esc_html__('Weekly', 'eventer');
          break;
        case 'daily':
          $cal_label = esc_html__('Daily', 'eventer');
          break;
        case 'today':
          $today_btn = 1;
          break;
        case 'date_range':
          $date_range = 1;
          break;
        case 'eventkeys':
          $eventkeys = 1;
          $search_range = 0;
          break;
        case 'seventkeys':
          $eventkeys = 1;
          $search_range = 1;
          break;
        default:
          $cal_label = esc_html__('Monthly', 'eventer');
          break;
      }
      if ($today_btn == 1 || $eventkeys == 1 || $date_range == 1) {
        continue;
      }

      $active_class = (($cal_view == $view) || ($atts['status'] == $view && $cal_view == '')) ? 'active' : 'show_month_events';
      $date_filters .= '<a class="list_calendar_view ' . $active_class . '" data-arrow="' . $current_date . '" data-arrowclass="2" data-calview="' . $view . '">' . $cal_label . '</a>';
    }
    if ($today_btn == 1) {
      $date_filters .= '<a class="list_calendar_view show_month_events today-btn" title="Return to current date" data-arrow="' . date_i18n('Y-m-d') . '" data-arrowclass="2">' . esc_html__('Today', 'eventer') . '</a>';
    }
    if ($date_range == 1) {
      $date_filters .= '<a class="eventer-datewise-filter-trigger" title="Date range selector"><i class="eventer-icon-calendar"></i></a>';
    }
    $date_filters .= '</div>';
  }
  if ($date_range == 1) {
    // Load jQuery UI Datepicker script
    //wp_enqueue_script('jquery-ui-datepicker');
    $date_filters .= '<div class="eventer-filter-datewise-wrap"><div class="eventer-fdww-inner"><span>' . esc_html__('Show events from ', 'eventer') . '</span><input type="text" class="eventer-filter-datewise" id="eventer_from" name="from" value="' . $atts['from_date'] . '"></div><div class="eventer-fdww-inner"><span>
			' . esc_html__('to ', 'eventer') . '</span><input type="text" class="eventer-filter-datewise" id="eventer_to" name="to" value="' . $atts['to_date'] . '"></div></div>';
  }
  $date_filters .= ($tab_length > 0) ? '<ul class="eventer-twelve-months">' : '';
  $tab_length = ($atts['status'] == "weekly") ? 0 : $tab_length;
  for ($i = 1; $i <= $tab_length; $i++) {
    if (strtotime($last_event_date) < strtotime(date_i18n('Y-m-d 23:59', strtotime('+' . $i . ' ' . $tabs, strtotime($get_months))))) {
      break;
    }

    $date_filters .= '<li data-jump="0" data-arrow="' . date_i18n('Y-m', strtotime('+' . $i . ' ' . $tabs, strtotime($get_months))) . '" class="show_month_events next-month">' . date_i18n($tabs_date, strtotime('+' . $i . ' ' . $tabs, strtotime($get_months))) . '</li>';
  }
  $date_filters .= ($tab_length > 0) ? '</ul>' : '';
  $date_filters .= '</div>';
  if ($eventkeys == 1) {
    //$date_filters .= '<input data-from="'.date_i18n('Y-m-d').'" data-to="'.date_i18n('Y-m-d',strtotime(date("Y-m-d", time()) . " + 1825 day")).'" data-range="'.$search_range.'" data-search="" data-arrow="'.$get_months.'" type="text" class="keyword-search-eventer" value="" name="keyword-search-eventer">';
  }
  return $date_filters;
}
add_filter('eventer_list_date_filter', 'eventer_list_date_filters', 10, 2);
function eventer_list_category_filters($atts, $all_cats)
{
  if ($atts['filters'] == '') {
    return;
  }

  $category_filters = '';
  $filters = explode(',', $atts['filters']);
  if (!empty($filters)) {
    $category_filters .= '<div class="eventer-filter-wrap">';
    $category_filters .= '<label>' . esc_html__('Sort Events', 'eventer') . '</label>';
    foreach ($filters as $filter) {
      $att_term = $filter;
      if ($filter == 'category') {
        $att_term = 'cats';
      }
      if ($filter == 'tag') {
        $att_term = 'tags';
      }
      $eventer_taxonomy = get_terms('eventer-' . $filter);
      switch ($filter) {
        case 'category':
          $taxonomy_name = esc_html__('Category', 'eventer');
          break;
        case 'tag':
          $taxonomy_name = esc_html__('Tags', 'eventer');
          break;
        case 'venue':
          $taxonomy_name = esc_html__('Venue', 'eventer');
          break;
        case 'organizer':
          $taxonomy_name = esc_html__('Organizer', 'eventer');
          break;
      }
      if (!is_wp_error($eventer_taxonomy) && !empty($eventer_taxonomy)) {
        $category_filters .= '<div class="eventer-filter-col">
														<a class="eventer-filter-trigger eventer-btn eventer-btn-basic" href="javascript:void(0)">' . esc_html__('By Event', 'eventer') . ' ' . esc_attr($taxonomy_name) . ' <i class="eventer-icon-arrow-down"></i></a>';
        $category_filters .= '<ul class="eventer-filter-select eventer-category-filter" data-taxonomy="terms_' . $att_term . '">';
        foreach ($eventer_taxonomy as $cat) {
          $selected = (in_array($cat->term_id, $all_cats)) ? 'checked' : '';
          $category_filters .= '<li class=""><label><input data-arrow="' . $atts['current_date'] . '" data-arrowclass="2" data-term="' . $cat->term_id . '" type="checkbox" ' . $selected . ' class="eventers-filter-check show_month_events" value="' . $cat->term_id . '"> ' . $cat->name . '</label></li>';
        }
        $category_filters .= '</ul></div>';
      }
    }
    $category_filters .= '</div>';
  }
  return $category_filters;
}
add_filter('eventer_list_category_filter', 'eventer_list_category_filters', 10, 2);
function eventer_client_ip()
{
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    //to check ip is pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}
function eventer_tickets_payment_options($eventer_data, $payment_fields = '')
{
  if (empty($eventer_data)) {
    return;
  }
  $stripe_switch = eventer_get_settings('eventer_stripe_payment_switch');
  $stripe_default = eventer_get_settings('eventer_stripe_default_switch');
  $paypal_switch = eventer_get_settings('eventer_paypal_payment_switch');
  $paypal_default = eventer_get_settings('eventer_paypal_default_switch');
  $dotpay_switch = eventer_get_settings('eventer_dotpay_payment_switch');
  $dotpay_default = eventer_get_settings('eventer_dotpay_default_switch');
  $offline_switch = eventer_get_settings('eventer_offline_payment_switch');
  $offline_default = eventer_get_settings('eventer_offline_default_switch');
  $coupons_switch = eventer_get_settings('eventer_coupon_field');
  $offline_default = ($stripe_default != '1' && $paypal_default != '1') ? '1' : $offline_default;
  $offline_switch = ($stripe_switch == '' && $paypal_switch == '') ? '1' : $offline_switch;
  $default_set = '';
  $offline_payment_msg = $eventer_data['offline_msg'];
  $eventer_currency = $eventer_data['eventer_currency'];
  $payment_fields = '<div class="payment-options-area">
								<div class="eventer-row">';
  if ($coupons_switch == 'on') {
    $payment_fields .= '
    <div class="eventer-col10">
      <div class="eventer-coupon-module">
        <div class="eventer-have-cc">
          <label><input type="checkbox" class="eventer-coupon-opener"> ' . esc_attr('Have coupon?', 'eventer') . '</label>
        </div>
      <div class="eventer-coupon-field">
    <div class="eventer-input-wbtn">
      <input type="text" class="eventer-apply-coupon" placeholder="' . esc_attr('Enter Coupon Code', 'eventer') . '">
      <div class="eventer-gbtn">
        <button class="eventer-btn eventer-btn-basic eventer-coupon-validate">' . esc_attr('Apply', 'eventer') . '</button>
      </div>
    </div>
  </div>
</div>
</div>';
  }
  $payment_fields .= '</div>
                                <div class="eventer-row">
									<div class="eventer-col8 eventer-col6-xs">
										<ul class="eventer-payment-options clearfix">
										';
  if ($stripe_switch == '1') {
    if ($stripe_default == '1') {
      $default_set = 'checked';
    }
    $payment_fields .= '<li>
										<label class="eventer-radio"><input ' . esc_attr($default_set) . '  class="chosen-payment-option" value="2" type="radio" name="chosen-payment-option"> ' . esc_html__('Stripe', 'eventer') . '</label>
											</li>';
  }
  if ($paypal_switch != '0') {
    $default_set = ($paypal_default == '1' && $default_set == '') ? 'checked' : '';
    $payment_fields .= '<li>
											<label class="eventer-radio"><input ' . esc_attr($default_set) . ' class="chosen-payment-option" value="1" type="radio" name="chosen-payment-option"> <img src="' . esc_url(EVENTER__PLUGIN_URL . '/images/paypal_logo.png') . '" alt="PayPal"></label>
										</li>';
  }
  if ($dotpay_switch != '100') {
    /*$default_set = ($paypal_default == '1' && $default_set == '') ? 'checked' : '';
		$payment_fields .= '<li>
											<label class="eventer-radio"><input ' . esc_attr($default_set) . ' class="chosen-payment-option" value="3" type="radio" name="chosen-payment-option"> dotpay</label>
										</li>';*/ }
  if ($offline_switch != "0") {
    $default_set = ($offline_default == '1' && $default_set == '') ? 'checked' : '';
    $payment_fields .= '<li>
												<label class="eventer-radio"><input  ' . esc_attr($default_set) . '  class="chosen-payment-option" value="0" type="radio" name="chosen-payment-option"> ' . esc_html__('Offline payment', 'eventer') . '</label>
											</li>';
  }
  $payment_fields .= '</ul>
									</div>
									<div class="eventer-col2 eventer-col4-xs">
										<div class="eventer-ticket-price-total" data-fprice="">' . esc_attr($eventer_currency) . '0</div>
									</div>
								</div>
								<div class="eventer_stripe_field" ' . (($stripe_default != '1' || $stripe_switch != '1') ? 'style="display:none;"' : '') . '>
								<div class="eventer-row">
									<div class="eventer-col10">
										<div class="form-row">
											<label for="card-element">
              									Credit or debit card
            								</label>
											<div id="card-element"></div> 
										</div>
            							<div id="card-errors" role="alert"></div>
									</div>
								</div>
								</div>
								<div class="offline_message" ' . (($offline_default == '1') ? '' : 'style="display:none;"') . '>
									' . $offline_payment_msg . '
								</div>
							</div>';
  return $payment_fields;
}
add_filter('eventer_tickets_payment_fields', 'eventer_tickets_payment_options', 10, 1);
function eventer_generate_tickets_row($eventer_data, $ticket_remaining_modal = '')
{
  $eventer_id = (isset($eventer_data['event_id']) && $eventer_data['event_id'] != '') ? $eventer_data['event_id'] : get_the_ID();
  $original_event = eventer_wpml_original_post_id($eventer_id);
  $tickets = get_post_meta($original_event, 'eventer_tickets', true);
  $tickets_with_ids = get_post_meta($original_event, 'wceventer_tickets', true);
  $tickets_translated = get_post_meta($eventer_id, 'eventer_tickets', true);
  $booked_tickets = $eventer_data['booked_tickets'];
  $show_tickets_info = (!empty($booked_tickets)) ? $booked_tickets : $tickets;
  $eventer_currency = $eventer_data['eventer_currency'];
  $event_cdate = $eventer_data['event_cdate'];
  $eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
  $eventer_url = eventer_generate_endpoint_url('edate', $eventer_formatted_date, get_permalink(get_the_ID()));
  $currency_position = $eventer_data['currency_position'];
  if (!empty($show_tickets_info)) {
    $start_ticket = 0;
    foreach ($show_tickets_info as $ticket) {
      $remaining_for_reg = '';
      $ticket_name = (isset($ticket['name'])) ? $ticket['name'] : '';
      $ticket_locale_name = (isset($ticket['cust_val1'])) ? json_decode($ticket['cust_val1'], true) : [];
      $ticket_name = ($ticket_locale_name && isset($ticket_locale_name[EVENTER__LANGUAGE_CODE]) && $ticket_locale_name[EVENTER__LANGUAGE_CODE] != '') ? $ticket_locale_name[EVENTER__LANGUAGE_CODE] : $ticket_name;
      $ticket_pid = (isset($ticket['pid'])) ? $ticket['pid'] : '';
      $ticket_existing = (get_post_type($ticket_pid) == 'product' && get_post_status($ticket_pid) == 'publish') ? '' : esc_html__('It seems the ticket you added for this event is no more exists.', 'eventer');
      $woo_ticket = (isset($eventer_data['woo_ticketing'])) ? $eventer_data['woo_ticketing'] : '';
      /*if ($ticket_existing == '' && eventer_get_settings('eventer_enable_woocommerce_ticketing') == 'on') {
				global $woocommerce;
				if (WC()->cart->cart_contents_count == 0) {
					WC()->cart->add_to_cart($ticket_pid, 1);
					echo '<div id="reg_cart_status" style="display: none">' . $ticket_pid . '</div>';
				}
			}*/
      $ticket_existing = ($woo_ticket == 'on') ? $ticket_existing : '';
      if (isset($tickets_translated[$start_ticket]) && isset($tickets_translated[$start_ticket]['pid']) && $tickets_translated[$start_ticket]['pid'] == $ticket_pid && $ticket_name != $tickets_translated[$start_ticket]['name']) {
        $ticket_name = $tickets_translated[$start_ticket]['name'];
      }
      $ticket_number = (isset($ticket['tickets'])) ? $ticket['tickets'] : '';
      $ticket_price = (isset($ticket['price'])) ? number_format($ticket['price'], 2) : '';
      $ticket_generation_id = (isset($ticket['dynamic'])) ? $ticket['dynamic'] : '';
      $ticket_enabled_date = (isset($ticket['enabled'])) ? $ticket['enabled'] : '';
      if ($ticket_enabled_date !== '' && strtotime($ticket_enabled_date) > date_i18n('U')) {
        continue;
      }
      if (isset($ticket['pid'])) {
        $ticket_product_id = $ticket['pid'];
      } elseif (isset($tickets_with_ids[$start_ticket])) {
        $ticket_product_id = $tickets_with_ids[$start_ticket]['wceventer_ticket_id'];
      } else {
        $ticket_product_id = '';
      }
      $ticket_product_identify = (isset($ticket['id'])) ? $ticket['id'] : '';
      $sold_individually = get_post_meta($ticket_product_id, '_sold_individually', true);
      $ticket_restrict = (isset($ticket['restricts'])) ? $ticket['restricts'] : '';
      $restricted_class = ($ticket_restrict == "1") ? 'restricted-row' : '';
      $ticket_currency = $eventer_currency;
      if (is_numeric($ticket_price) && $ticket_price != '') {
        $ticket_price_r = $ticket_price;
        $ticket_price = ($currency_position != 'postfix') ? $ticket_currency . $ticket_price : $ticket_price . $ticket_currency;
        $discounted_price = '';
        $raw_price = $ticket_price_r;
      } elseif (strpos($ticket_price, "-") !== false && $ticket_price != '') {
        $new_ticket_price = explode('-', $ticket_price);
        $calculate_discounted_price = intval($new_ticket_price[0]) - intval($new_ticket_price[1]);
        $discounted_price = $ticket_currency . $calculate_discounted_price;
        $show_price = ($currency_position != 'postfix') ? $ticket_currency . $new_ticket_price[0] : $new_ticket_price[0] . $ticket_currency;
        $ticket_price = '<del class="eventer-price-currency">' . $show_price . '</del>';
        $raw_price = $calculate_discounted_price;
      } else {
        $ticket_price = $ticket_price;
        $discounted_price = '';
        $ticket_currency = '';
        $raw_price = $ticket_price;
      }
      $ticket_remaining = (!isset($booked_tickets[$ticket_name])) ? $ticket_number : $booked_tickets[$ticket_name];
      if ($ticket_remaining > 0) {
        $default_price = (isset($eventer_data['currency_position']) && $eventer_data['currency_position'] == "postfix") ? '0' . esc_attr($ticket_currency) : esc_attr($ticket_currency) . '0';
        $ticket_cookie = (isset($_COOKIE[$eventer_formatted_date . '_' . $ticket_generation_id])) ? $_COOKIE[$eventer_formatted_date . '_' . $ticket_generation_id] : '';
        $remaining_for_reg = 1;
        if ($ticket_existing == '') {
          $ticket_remaining_modal .= '<div class="eventer-ticket-type-row" data-limit="' . esc_attr($sold_individually) . '">
						<div class="eventer-row ' . esc_attr($restricted_class) . '" data-identify="' . esc_attr($ticket_product_identify) . '" data-cart="' . esc_attr($ticket_pid) . '" data-pid="' . esc_attr($ticket_product_id) . '" data-booked="' . $ticket_cookie . '" data-ticketid="' . $ticket_generation_id . '">
							<div class="eventer-col2 eventer-col3-xs">
								<label>' . esc_html__('Type', 'eventer') . '</label>
								<div class="name-ticket">' . $ticket_name . '</div>
							</div>
							<div class="eventer-col2 eventer-col2-xs">
							<label>' . esc_html__('Available', 'eventer') . '</label>
							<div class="remaining-ticket">' . $ticket_remaining . '</div>
						</div>
						<div class="eventer-col2 eventer-col2-xs">
							<label>' . esc_html__('Price', 'eventer') . '</label>
							<div class="price-ticket" data-tprice="' . $raw_price . '">' . $ticket_price . ' ' . $discounted_price . '</div>
						</div>
						<div class="eventer-col2 eventer-col3-xs eventer-tq-wrap">
							<span class="eventer-restricted-msg">' . esc_html__('One time registration allowed for this ticket', 'eventer') . '</span>
							<label>' . esc_html__('Quantity', 'eventer') . '</label>
							<div class="eventer-q-field">
								<input type="text" name="quantity_tkt" value="0" maxlength="99" class="num-tickets" autocomplete="off">
								<input type="button" value="+" class="eventer-qtyplus">
								<input type="button" value="-" class="eventer-qtyminus">
							</div>
						</div>
						<div class="eventer-col2 eventer-hidden-xs">
							<label>' . esc_html__('Total', 'eventer') . '</label>
							<div class="total-price" data-mprice="">' . $default_price . '</div>
						</div>
					</div>
					</div>';
        } else {
          $ticket_remaining_modal .= '<div class="eventer-ticket-type-row">
						<div class="eventer-row">
							<div class="eventer-col10 eventer-col10-xs">'
            . $ticket_existing . '
							</div>
						</div>
					</div>';
        }
      }
      $start_ticket++;
    }
  }
  return '<div class="eventer_ticket-filter">' . $ticket_remaining_modal . '</div>';
}
add_filter('eventer_get_tickets', 'eventer_generate_tickets_row', 10, 2);
function eventer_generate_services_row($eventer_data, $ticket_additional_services = '')
{
  $eventer_id = (isset($eventer_data['event_id']) && $eventer_data['event_id'] != '') ? $eventer_data['event_id'] : get_the_ID();
  $original_event = eventer_wpml_original_post_id($eventer_id);
  $eventer_additional_services = get_post_meta($original_event, 'eventer_additional_services', true);
  $eventer_currency = $eventer_data['eventer_currency'];
  $currency_position = $eventer_data['currency_position'];
  if (!empty($eventer_additional_services)) {
    add_action('eventer_ticket_services_tab', 'eventer_additional_services_details', 10, 3);
    foreach ($eventer_additional_services as $services) {
      $multiple = (isset($services['multiple']) && $services['multiple'] == '1') ? 'checkbox' : 'radio';
      $services_mandatory = (isset($services['tickets_mandatory']) && $services['tickets_mandatory'] == '1') ? 'restrict-service' : '';
      $services_field_mandatory = (isset($services['tickets_mandatory']) && $services['tickets_mandatory'] == '1') ? 'restrict-service-field' : '';
      $stype1 = (isset($services['type1'])) ? $services['type1'] : '';
      $sprice1 = (isset($services['price1'])) ? $services['price1'] : '';
      $stype2 = (isset($services['type2'])) ? $services['type2'] : '';
      $sprice2 = (isset($services['price2'])) ? $services['price2'] : '';
      $stype3 = (isset($services['type3'])) ? $services['type3'] : '';
      $sprice3 = (isset($services['price3'])) ? $services['price3'] : '';
      $stype4 = (isset($services['type4'])) ? $services['type4'] : '';
      $sprice4 = (isset($services['price4'])) ? $services['price4'] : '';
      $service_type = array_filter(array($stype1, $stype2, $stype3, $stype4));
      $show_price = (isset($services['price']) && is_numeric($services['price'])) ? ($currency_position == "postfix") ? ' ' . esc_attr($services['price']) . esc_attr($eventer_currency) : ' ' . esc_attr($eventer_currency) . esc_attr($services['price']) : '';
      $service_title = (is_numeric($services['service'])) ? get_the_title($services['service']) : $services['service'];
      $ticket_additional_services .= '<div class="eventer-ticket-type-row" data-pid="' . esc_attr($services['service']) . '">
      <div class="eventer-row ">
      <div class="eventer-col4 eventer-col3-xs">
      <label>' . esc_html__('Choose', 'eventer') . '</label>
      <div class="name-ticket">' . esc_attr($service_title) . $show_price . '</div>
      </div>
      <div class="eventer-col4 eventer-col5-xs">';
      if ($service_type) {

        $ticket_additional_services .= '<div class="eventer-filter-col">
        <a class="eventer-filter-trigger eventer-btn eventer-btn-basic eventer-services-options-trigger" href="#">' . esc_html__('Select Options', 'eventer') . ' <i class="eventer-icon-arrow-down"></i></a>
        <div class="remaining-ticket">
        <ul class="eventer-filter-select services-section ' . esc_attr($services_mandatory) . '">';
        $start = 1;
        foreach ($service_type as $type) {
          $set_price = (is_numeric(${"sprice" . $start})) ? ($currency_position == "postfix") ? ${"sprice" . $start} . $eventer_currency : $eventer_currency . ${"sprice" . $start} : ${"sprice" . $start};
          $raw_price = (is_numeric(${"sprice" . $start})) ? ${"sprice" . $start} : 0;
          $service_price = $eventer_currency . ${"sprice" . $start};
          $ticket_additional_services .= '<li class="price-ticket" data-mprice="" data-tprice="' . esc_attr($raw_price) . '"><label><input type="' . esc_attr($multiple) . '" value="' . esc_attr($type) . '" name="' . esc_attr($services['service']) . '" class="eventers-filter-check add_services ' . esc_attr($services_field_mandatory) . '"> ' . esc_attr($type) . ' ' . esc_attr($set_price) . '</label></li>';
          $start++;
        }
        $ticket_additional_services .= '</ul>
        </div>
        </div>';
      } elseif (isset($services['price']) && is_numeric($services['price'])) {
        $ticket_additional_services .= '<ul class="' . esc_attr($services_mandatory) . ' services-section"><li class="remaining-ticket price-ticket" data-mprice="" data-tprice="' . esc_attr($services['price']) . '"><label class="eventer_add_services_single_check"><input type="checkbox" class="add_services ' . esc_attr($services_field_mandatory) . '" value="' . esc_attr($services['service']) . '"> ' . esc_html__('Select', 'eventer') . '</label></li></ul>';
      }
      $ticket_additional_services .= '</div>
      <div class="eventer-col2">
      <label>' . esc_html__('Total', 'eventer') . '</label>
      <div class="total-price" data-mprice="">0</div>
      </div>
      </div>
      </div>';
    }
  }
  return $ticket_additional_services;
}
add_filter('eventer_get_services', 'eventer_generate_services_row', 10, 2);

function eventer_remove_directory_cron($directory)
{
  if (strpos($directory, 'eventer') === false) {
    return "couldn't delete";
  } elseif (is_file($directory)) {
    if (empty($wp_filesystem)) {
      require_once ABSPATH . '/wp-admin/includes/file.php';
      WP_Filesystem();
    }
    global $wp_filesystem;
    $upload = wp_upload_dir();
    $upload_dir_base = $upload['basedir'];
    if ($wp_filesystem) {
      $wp_filesystem->delete($directory, true, 'f');
    }
  }
}
add_action('eventer_initiate_cron_remove_directory', 'eventer_remove_directory_cron', 10, 1);

add_action('generate_ticket_for_registrants', 'eventer_cron_once_for_ticket', 10, 7);
function eventer_cron_once_for_ticket($email, $registrant_id, $registrants, $event_id, $qrcode_name, $organizer_email)
{
  $sender = ($organizer_email) ? $organizer_email : get_option('admin_email');
  //$headers[] = 'From: '.get_bloginfo('name').' <'.$sender.'>';
  //$headers[] = "MIME-Version: 1.0" . "\r\n";
  $headers[] = "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
  $subject = esc_html__('Your tickets', 'eventer');
  if (empty($qrcode_name)) {
    return;
  }

  global $wp_filesystem;
  if (empty($wp_filesystem)) {
    require_once ABSPATH . '/wp-admin/includes/file.php';
    WP_Filesystem();
  }
  $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
  $message = '';
  $upload = wp_upload_dir();
  $upload_dir_base = $upload['basedir'];
  $has_attachment = 0;
  $mail_attachment = array();
  foreach ($qrcode_name as $ticket_print) {
    if (!file_exists($upload_dir_base . '/eventer/' . $ticket_print)) continue;
    $mail_attachment[] = $upload_dir_base . '/eventer/' . $ticket_print;
    $size = filesize($upload_dir_base . '/eventer/' . $ticket_print);
    if ($size > 12000) {
      $has_attachment = 1;
    }
  }
  $attachment_content = apply_filters('the_content', eventer_get_settings('email_tickets_attachment'));
  $attachment_content_switch = eventer_get_settings('email_tickets_attachment_switch');
  if ($has_attachment == 0 || $attachment_content_switch == '0') {
    return;
  }
  if (get_post_type($registrant_id) == 'shop_order' && $woocommerce_ticketing == 'on') {

    $order = wc_get_order($registrant_id);
    if ($order) {
      $message = apply_filters('eventer_filter_woo_email_content_body', '', $registrant_id, $attachment_content);
    }
  } else {
    $message = apply_filters('eventer_filter_email_content_body', '', $registrants, $attachment_content);
  }

  $message = ($message != '') ? $message : esc_html__('Please find tickets in attachment', 'eventer');


  $mail_status = wp_mail($email, $subject, $message, $headers, $mail_attachment);
}

function eventer_check_base64_image($base64)
{
  $img = @imagecreatefrompng($base64);
  if (!$img) {
    return false;
  }

  imagepng($img, 'tmp.png');
  $info = getimagesize('tmp.png');
  $size = filesize('tmp.png');
  unlink('tmp.png');

  if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
    return $size;
  }

  return false;
}

function eventer_generate_ticket_qrcode()
{
  $nonce = $_REQUEST['nonce'];
  if (!wp_verify_nonce($nonce, 'eventer-qrcode-nonce')) {
    wp_die();
  }

  $qrdata = (isset($_REQUEST['qrdata'])) ? $_REQUEST['qrdata'] : '';
  $event_id = (isset($_REQUEST['eid'])) ? $_REQUEST['eid'] : '';
  $event_title = apply_filters('eventer_raw_event_title', '', $event_id);
  $folder_name = eventer_clean_string($event_title);
  $folder_name = ($folder_name != '') ? $folder_name : $event_id;
  $registrant_id = (isset($_REQUEST['reg'])) ? $_REQUEST['reg'] : '';
  $ticket_front = (isset($_REQUEST['front'])) ? $_REQUEST['front'] : '';
  $ticket_reverse = (isset($_REQUEST['reverse'])) ? $_REQUEST['reverse'] : '';
  $main_reg = (isset($_REQUEST['mainreg'])) ? $_REQUEST['mainreg'] : '';
  $source = (isset($_REQUEST['source'])) ? $_REQUEST['source'] : '';
  $reg_pos = (isset($_REQUEST['regpos'])) ? $_REQUEST['regpos'] : '';
  $organizer_email = (isset($_REQUEST['organizer'])) ? $_REQUEST['organizer'] : get_option('admin_email');
  global $wp_filesystem;
  if (empty($wp_filesystem)) {
    require_once ABSPATH . '/wp-admin/includes/file.php';
    WP_Filesystem();
  }
  $upload = wp_upload_dir();
  $upload_blog_url = $upload['baseurl'];
  $upload_dir_base = $upload['basedir'];
  $upload_dir = $upload_dir_base . '/eventer';
  if (!$wp_filesystem->is_dir($upload_dir)) {
    /* directory didn't exist, so let's create it */
    wp_mkdir_p($upload_dir);
  }
  $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');

  $updated_registrants = $tickets_created = $user_system = array();
  $registrants = eventer_get_registrant_details('id', $registrant_id);

  if ($wp_filesystem) {
    $key = $file_name_back = '';
    $start_time = 2;
    $sub_tickets = $sub_reg_val = array();

    if (!empty($qrdata)) {
      $start_key = 1;
      foreach ($qrdata as $data) {
        $random_name = date_i18n('Y-m-d-H-i-s');
        if (!isset($data['src'])) {
          continue;
        }

        $image_validate = eventer_check_base64_image($data['src']);
        $ticket_name_clean = eventer_clean_string($data['ticket']);
        $qrcode_name = eventer_clean_string($data['email']);
        $filename_first = $start_key . '-' . $qrcode_name . '-' . $ticket_name_clean . '-' . $event_id . '-' . $random_name . '.png';
        $filename = $upload_dir . '/' . $filename_first;
        if (!$image_validate || $image_validate < 10000) {
          continue;
        }

        $sub_tickets[] = $filename_first;
        $wp_filesystem->put_contents(
          $filename,
          file_get_contents($data['src']),
          FS_CHMOD_FILE // predefined mode settings for WP files
        );
        $email = (isset($data['email']) && $data['email'] != '') ? $data['email'] : wp_rand(10, 1000000000000000000);
        $tickets_created[$email] = $filename_first;
        if ($reg_pos > 14 && isset($data['email']) && $data['email'] != '' && $data['email'] != $main_reg && $source == '') {
          wp_schedule_single_event(time() + ($start_time), 'generate_ticket_for_registrants', array($data['email'], $registrant_id, $registrants, $event_id, array($filename_first), $organizer_email));
          $start_time = $start_time + 5;
        }
        $start_key++;
      }
    }
    if ($reg_pos > 14 && $source == '') {
      wp_schedule_single_event(time(), 'generate_ticket_for_registrants', array($main_reg, $registrant_id, $registrants, $event_id, $sub_tickets, $organizer_email));
    }
    $qrcode_name_new_ticket = '';
    $send_tickets = array();
    if (!empty($sub_tickets)) {
      $start = 18600;
      foreach ($sub_tickets as $ticket) {
        $qrcode_name_new_ticket .= $ticket . ',';
        $send_tickets[] = $ticket;
        wp_schedule_single_event(time() + ($start + 5), 'eventer_initiate_cron_remove_directory', array($upload_dir . '/' . $ticket));
      }
    }

    if (get_post_type($registrant_id) == 'shop_order' && $woocommerce_ticketing == 'on') {
      $registrants = eventer_get_registrant_details('eventer', $registrant_id);
      $back_order_tickets = (isset($_REQUEST['backorder'])) ? $_REQUEST['backorder'] : '';
      $back_order_tickets = ($back_order_tickets != '') ? add_query_arg('allow', $registrants->id, $back_order_tickets) : '';
      $order = wc_get_order($registrant_id);
      if ($order) {
        $order_email = get_post_meta($registrant_id, '_billing_email', true);
        $generate_dynamic_order_id = eventer_encode_security_registration($registrant_id, 8, 6);
        $generate_dynamic_order_id_completed = eventer_encode_security_registration($registrant_id, 9, 9);
        $woocommerce_thanks_redirect = eventer_get_settings('eventer_thanks_redirect');
        $order = wc_get_order($registrant_id);
        $order_event_url = '';
        foreach ($order->get_items() as $item_key => $item_values) :
          $new_already_booked = $update_new_val = array();
          $item_data = $item_values->get_data();
          $item_id = $item_values->get_id();
          $product_id = $item_data['product_id'];
          if (!has_term('eventer', 'product_cat', $product_id) && !has_term('eventer_services', 'product_cat', $product_id)) {
            continue;
          }

          $order_event_url = wc_get_order_item_meta($item_id, 'Event URL', true);
          break;
        endforeach;
        $order_event_url = $order_event_url;
        /*update_post_meta($registrant_id, 'eventer_woo_tickets_generated', 1);
                update_post_meta($registrant_id, 'eventer_woo_registrants', $updated_registrants);
                update_post_meta($registrant_id, 'eventer_woo_all_tickets_path', $qrcode_name_new_ticket);*/
        $order_status = $order->get_status();
        if (($order_status == 'completed' && $woocommerce_thanks_redirect == 'on' && $back_order_tickets == '')) {
          $order_event_url = esc_url(add_query_arg('reg', $generate_dynamic_order_id_completed, $order_event_url));
        } elseif ($woocommerce_thanks_redirect == 'on' && $back_order_tickets == '') {
          $order_event_url = esc_url(add_query_arg('reg', $generate_dynamic_order_id, $order_event_url));
        } else {
          $order_event_url = '';
        }
        $order_event_url = ($back_order_tickets != '') ? $back_order_tickets : $order_event_url;
        if ($order_status == 'completed' && $back_order_tickets == '' && $source == '') {
          wp_schedule_single_event(time(), 'generate_ticket_for_registrants', array($order_email, $registrant_id, $order, $event_id, $send_tickets, $organizer_email));
        }
      }
    } else {
      $order_event_url = (isset($_REQUEST['backorder'])) ? $_REQUEST['backorder'] : '';
      $order_event_url = ($order_event_url != '') ? add_query_arg('allow', $registrants->id, $order_event_url) : '';
    }
    if ($registrants) {
      $user_system = unserialize($registrants->user_system);
      if ($user_system) {
        $user_system['tickets_created'] = $tickets_created;
        eventer_update_registrant_details(array('user_system' => serialize($user_system)), $registrants->id, array("%s", "%s"));
      }
    }
    echo wp_json_encode(array('tickets' => $qrcode_name_new_ticket, 'event_url' => $order_event_url, 'ticket_arr' => $send_tickets, 'url' => $upload_blog_url . '/eventer', 'allow' => wp_create_nonce('eventer-tickets-download')));
  }
  wp_die();
}
add_action('wp_ajax_eventer_generate_ticket_qrcode', 'eventer_generate_ticket_qrcode');
add_action('wp_ajax_nopriv_eventer_generate_ticket_qrcode', 'eventer_generate_ticket_qrcode');

function eventer_status_completed($registrant, $called = '')
{
  if ($called == '') return;
  $usersystem = unserialize($registrant->user_system);
  $user_registrants_list = (!empty($usersystem) && isset($usersystem['tickets_created'])) ? $usersystem['tickets_created'] : array();
  //$tickets_generated =  (!empty($usersystem) && isset($usersystem['tickets_generated']))?$usersystem['tickets_generated']:'';
  //global $wp_filesystem;
  $upload = wp_upload_dir();
  $upload_dir_base = $upload['basedir'];
  $upload_dir = $upload_dir_base . '/eventer';
  if (!empty($user_registrants_list)) {
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $registrant_id_set = ($woocommerce_ticketing == 'on') ? $registrant->eventer : $registrant;
    $all_tickets = array();
    foreach ($user_registrants_list as $key => $value) {
      $all_tickets[] = $value;
      if (is_numeric($key) || $key == $registrant->email) {
        continue;
      }

      wp_schedule_single_event(time(), 'generate_ticket_for_registrants', array($key, $registrant->id, $registrant_id_set, $registrant->eventer, array($value), ''));
    }
    wp_schedule_single_event(time(), 'generate_ticket_for_registrants', array($registrant->email, $registrant->id, $registrant_id_set, $registrant->eventer, $all_tickets, ''));
    return 1;
  }
}
add_filter('eventer_status_changed_completed', 'eventer_status_completed', 10, 2);

function eventer_woo_download_tickets()
{
  $nonce = $_REQUEST['captcha'];
  if (!wp_verify_nonce($nonce, 'eventer-tickets-download')) {
    wp_die('Security check failed');
  } else {
    $tickets = explode(',', $_REQUEST['tickets']);
    $folder = date_i18n('Y-m');
    $archive_file_name = "eventer_tickets.zip";
    $upload = wp_upload_dir();
    $upload_dir_base = $upload['basedir'];
    $file_path = $upload_dir_base . '/eventer/';
    $zip = new ZipArchive();
    //create the file and throw the error if unsuccessful
    if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE) !== true) {
      exit("cannot open <$archive_file_name>\n");
    }

    foreach ($tickets as $files) {
      if (empty($files)) {
        continue;
      }

      $zip->addFile($file_path . $files, $files);
    }
    $zip->close();

    //then send the headers to foce download the zip file
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$archive_file_name");
    header("Pragma: no-cache");
    header("Expires: 0");
    ob_end_clean();
    readfile("$archive_file_name");
    if (file_exists($archive_file_name)) {
      unlink($archive_file_name);
    }
    wp_die();
  }
}
add_action('wp_ajax_eventer_woo_download_tickets', 'eventer_woo_download_tickets');

function eventer_switch_dashboard_tab()
{
  $tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : '';
  $shortcode = (isset($_REQUEST['shortcode'])) ? $_REQUEST['shortcode'] : '';
  $order = (isset($_REQUEST['order'])) ? $_REQUEST['order'] : '';
  if ($tab != '' && $tab != 'undefined') {
    if ($tab == 'eventer_add_new') {
      $form_options = get_option('eventer_forms_data');
      $form_options = (empty($form_options)) ? array() : $form_options;
      $current_form_details = (isset($form_options[$shortcode])) ? $form_options[$shortcode] : '';
      $add_new = '[eventer_add_new';
      if (!empty($current_form_details)) {
        $form_status = $current_form_details['status'];
        $form_sections = $current_form_details['number'];
        $add_new .= ' status="' . $form_status . '"';
        $add_new .= ' sections="' . $form_sections . '"';
        $add_new .= ' id="' . $shortcode . '"';
        $add_new .= ' load="1"';
      }
      $add_new .= ' ]';
      echo do_shortcode($add_new);
    } else {

      if ($tab != 'eventer_submissions') {
        echo do_shortcode('[' . $tab . ']');
      } else {
        echo '<div id="eventer-dashboard-content-area" class="eventer-fe-content-col eventer-fe-content-part eventer-dashboard-main">';
        echo do_shortcode('[' . $tab . ']');
        echo '</div>';
        echo do_shortcode('[eventer_dash_terms]');
      }
    }
  } elseif ($order != '') {
    $field = (get_post_type($order) == 'shop_order' && eventer_get_settings('eventer_enable_woocommerce_ticketing') == 'on') ? 'eventer' : 'id';
    $event = (get_post_type($order) == 'shop_order') ? array() : array();
    $default = array();
    $new_tickets = apply_filters('eventer_preapare_data_for_tickets', $field, $order, $event);
    $new_tickets['data-regpos'] = 15;
    $default['data-eid'] = '';
    $default['data-regpos'] = 15;
    $new_tickets['default'] = $default;
    do_action('eventer_ticket_raw_design', '', $new_tickets);
  }

  wp_die();
}
add_action('wp_ajax_eventer_switch_dashboard_tab', 'eventer_switch_dashboard_tab');
add_action('wp_ajax_nopriv_eventer_switch_dashboard_tab', 'eventer_switch_dashboard_tab');

function eventer_dynamic_ticket_area()
{
  $event = (isset($_REQUEST['event'])) ? $_REQUEST['event'] : '';
  $date = (isset($_REQUEST['date'])) ? date_i18n('Y-m-d', strtotime($_REQUEST['date'])) : '';
  $date_show = (isset($_REQUEST['date'])) ? date_i18n(get_option('date_format'), strtotime($_REQUEST['date'])) : '';
  $time = (isset($_REQUEST['time'])) ? $_REQUEST['time'] : '00:00:00';
  $time = date_i18n('H:i:s', strtotime($time));
  $formatted = date_i18n(get_option('date_format'), strtotime($_REQUEST['date']));
  $offline_payment_switch = eventer_get_settings('eventer_offline_payment_switch');
  $offline_payment_msg = eventer_get_settings('eventer_offline_payment_desc');
  $params['offline_switch'] = $offline_payment_switch;
  $params['offline_msg'] = $offline_payment_msg;

  echo json_encode(array("tickets_modal" => do_shortcode('[eventer_ajax_tickets id="' . $event . '" date="' . $date . '" time="' . $time . '" ajax="1"]'), "tickets" => do_shortcode('[eventer_ajax_tickets_meta id="' . $event . '" date="' . $date . '" time="' . $time . '" ajax="1"]'), "metas" => do_shortcode('[eventer_metas id="' . $event . '" date="' . $date . '" time="' . $time . '"]'), "date_show" => $date_show, "date" => $date, "time" => $time, "formatted" => $formatted, 'event_url' => eventer_generate_endpoint_url('edate', $date, get_permalink($event))));
  wp_die();
}
add_action('wp_ajax_eventer_dynamic_ticket_area', 'eventer_dynamic_ticket_area');
add_action('wp_ajax_nopriv_eventer_dynamic_ticket_area', 'eventer_dynamic_ticket_area');

if (!function_exists('eventer_query_to_array')) {
  function eventer_query_to_array($qry)
  {
    $result = array();
    //string must contain at least one = and cannot be in first position
    if (strpos($qry, '=')) {

      if (strpos($qry, '?') !== false) {
        $q = parse_url($qry);
        $qry = $q['query'];
      }
    } else {
      return false;
    }

    foreach (explode('&', $qry) as $couple) {
      if (strpos($couple, "=")) {
        list($key, $val) = explode('=', $couple);
        $result[$key] = $val;
      }
    }

    return empty($result) ? false : $result;
  }
}
function eventer_get_filters_data($status, $get, $date)
{
  switch ($status) {
    case 'monthly':
      $set_cal = 'month';
      break;
    case 'yearly':
      $set_cal = 'year';
      break;
    case 'weekly':
      $set_cal = 'week';
      break;
    case 'daily':
      $set_cal = 'day';
      break;
  }
  if ($get == 'current') {
    if ($status == 'monthly' && $get = "current") {
      return date_i18n('Y-m');
    } elseif ($status == 'yearly' && $get = "current") {
      return date_i18n('Y');
    } elseif ($status == 'daily' && $get = "current") {
      return date_i18n('Y-m-d');
    } elseif ($status == 'weekly' && $get = "current") {
      return date_i18n('Y-m-d', strtotime('last Sunday', date_i18n('U')));
    }
  } elseif ($get == 'prev') {
    if ($set_cal == 'week') {
      $set_cal = 'sunday';
    }

    return date_i18n('Y-m-d', strtotime('last ' . $set_cal, strtotime($date)));
  } elseif ($get == 'next') {
    if ($set_cal == 'week') {
      $set_cal = 'saturday';
    }
    return date_i18n('Y-m-d', strtotime('next ' . $set_cal, strtotime($date)));
  } elseif ($get == 'format') {
    if ($status == 'monthly') {
      return date_i18n('Y-m-01', strtotime($date));
    } elseif ($status == 'yearly') {
      return date_i18n('Y-01-01', strtotime($date));
    } elseif ($status == 'daily') {
      return date_i18n('Y-m-d', strtotime($date));
    } elseif ($status == 'weekly') {
      return date_i18n('Y-m-d', strtotime($date));
    }
  }
}
function pm_remove_all_scripts()
{
  global $wp_scripts;
  //$wp_scripts->queue = array();
  wp_enqueue_script('jquery-ui-datepicker');
  wp_enqueue_script('eventer-plugins');
  wp_enqueue_script('eventer-qrcode');
  wp_enqueue_script('eventer-modal');
  wp_enqueue_script('eventer-init');
  wp_enqueue_script('eventer-single-scripts');
  wp_dequeue_script('divi-custom-script');
}
//add_action('wp_print_scripts', 'pm_remove_all_scripts', 100);
function eventer_validate_coupon()
{
  $coupon = (isset($_REQUEST['coupon'])) ? $_REQUEST['coupon'] : '';
  $amount = (isset($_REQUEST['amount'])) ? $_REQUEST['amount'] : '';
  $validate = $msg = $discounted = '';
  if ($coupon || $amount > 0) {
    global $wpdb;
    $eventer_coupon_table = $wpdb->prefix . "eventer_coupons";
    $coupon_row = $wpdb->get_row("SELECT * FROM $eventer_coupon_table where `coupon_code` = '$coupon'", ARRAY_A);
    if ($coupon_row) {
      $coupon_discount = (isset($coupon_row['discounted'])) ? $coupon_row['discounted'] : '';
      $coupon_validity = (isset($coupon_row['valid_till'])) ? $coupon_row['valid_till'] : '';
      $coupon_status = (isset($coupon_row['coupon_status'])) ? $coupon_row['coupon_status'] : '';
      if (strtotime($coupon_validity) > date_i18n('U') && $coupon_status != '1') {
        $validate = '1';
        $msg = esc_html__('Coupon validated successfully.', 'eventer');
        $discounted = $coupon_discount;
        if (is_numeric($coupon_discount)) {
          $amount = intval($amount) - intval($coupon_discount);
        } elseif (strpos($coupon_discount, '%') !== false) {
          $discounted_amount = ($coupon_discount / 100) * $amount;
          $amount = $amount - $discounted_amount;
        } else {
          $amount = $amount;
        }
      } elseif (strtotime($coupon_validity) < date_i18n('U')) {
        $validate = '0';
        $msg = esc_html__('Coupon validity period expired', 'eventer');
      } elseif ($coupon_status == '1') {
        $validate = '0';
        $msg = esc_html__('Coupon is disabled', 'eventer');
      }
    } else {
      $validate = '0';
      $msg = esc_html__('Coupon doesn\'t exist', 'eventer');
    }
  } elseif ($coupon == '') {
    $validate = '0';
    $msg = esc_html__('Coupon can not be empty', 'eventer');
  } elseif ($amount <= 0) {
    $validate = '0';
    $msg = esc_html('Total amount is not valid', 'eventer');
  }
  echo json_encode(array('validate' => $validate, 'msg' => $msg, 'discount' => $discounted, 'amount' => $amount));
  wp_die();
}
add_action('wp_ajax_eventer_validate_coupon', 'eventer_validate_coupon');
add_action('wp_ajax_nopriv_eventer_validate_coupon', 'eventer_validate_coupon');
