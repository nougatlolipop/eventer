<?php
//This file is used to create events for fullcalendar
// - standalone json feed -
header('Content-Type:application/json');
// - grab wp load, wherever it's hiding -
$parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
require_once($parse_uri[0] . 'wp-load.php');
$visible_start_date = $_POST['start'];
$visible_end_date = $_POST['end'];
$site_lang = (isset($_POST['site_lang'])) ? $_POST['site_lang'] : '';
if (function_exists('icl_object_id') && class_exists('SitePress')) {
  global $sitepress;
  $sitepress->switch_lang($site_lang);
}
$shortcode_atts = (isset($_REQUEST['shortcode_atts'])) ? $_REQUEST['shortcode_atts'] : array();
$event_preview = (isset($shortcode_atts['preview'])) ? $shortcode_atts['preview'] : '';
if ($shortcode_atts['type'] == 'google') return;
$event_cats = (isset($shortcode_atts['terms_cats']) && !empty($shortcode_atts['terms_cats'])) ? explode(',', $shortcode_atts['terms_cats']) : array();
$event_ids = (isset($shortcode_atts['ids']) && !empty($shortcode_atts['ids'])) ? explode(',', $shortcode_atts['ids']) : array();
$event_tags = (isset($shortcode_atts['terms_tags']) && !empty($shortcode_atts['terms_tags'])) ? explode(',', $shortcode_atts['terms_tags']) : array();
$event_venue = (isset($shortcode_atts['terms_venue']) && !empty($shortcode_atts['terms_venue'])) ? explode(',', $shortcode_atts['terms_venue']) : array();
$event_organizer = (isset($shortcode_atts['terms_organizer']) && !empty($shortcode_atts['terms_organizer'])) ? explode(',', $shortcode_atts['terms_organizer']) : array();
$event_ids = eventer_merge_all_ids($event_ids, $event_cats, $event_tags, $event_venue, $event_organizer);
$jsonevents = array();
$events = eventer_get_events_array($event_ids, "month", array($visible_start_date, $visible_end_date), '', '1000', '1', 1, $site_lang);
$multiple_events = (get_option('eventer_multi_day_event')) ? get_option('eventer_multi_day_event') : array();
$event_show = $events['events'];
$excluded_events = array_diff($event_show, $multiple_events);
$inclusive_events = array_merge($excluded_events, $multiple_events);
if (empty($inclusive_events)) return;
if (!empty($inclusive_events)) {
  foreach ($inclusive_events as $event_data) {
    $key = $event_data['start'];
    $value = $event_data['id'];
    if (get_post_status($value) != 'publish') continue;
    $event_start_date = get_post_meta($value, 'eventer_event_start_dt', true);
    $event_end_date = get_post_meta($value, 'eventer_event_end_dt', true);
    $event_start_dt_str = strtotime($event_start_date);
    $event_end_dt_str = strtotime($event_end_date);
    $hours_diff = (intval($event_end_dt_str) - intval($event_start_dt_str));
    $days_diff = ($event_end_date != '') ? eventer_dateDiff(date_i18n('Y-m-d', $event_start_dt_str), date_i18n('Y-m-d', $event_end_dt_str)) : 0;
    $find_ids = array_column($jsonevents, 'id');
    if ($hours_diff > 86400 && in_array($value, $find_ids)) continue;
    $color = '';
    $eventer_category = get_the_terms($value, 'eventer-category');
    if (!is_wp_error($eventer_category) && !empty($eventer_category)) {
      $color = get_term_meta($eventer_category[0]->term_id, 'category_color', true);
      $color = ($color != '') ? $color : '';
    }
    if ($hours_diff > 86400) {
      $all_dates = get_post_meta($value, 'eventer_all_dates', true);
      $event_time = date_i18n("G:i", $event_start_dt_str);
      $all_dates_with_time = preg_filter('/$/', ' ' . $event_time, $all_dates);
      $all_dates_with_time = array_filter($all_dates_with_time, function ($date) {
        return (strtotime($date) >= date_i18n('U'));
      });
      ksort($all_dates_with_time);
      if (!empty($all_dates_with_time)) {
        $event_new_key = key($all_dates_with_time);
        $event_cdate = strtotime($all_dates_with_time[$event_new_key]);
      } else {
        $event_cdate = $event_start_dt_str;
      }
    }
    $event_all_day = get_post_meta($value, 'eventer_event_all_day', true);
    $event_func_date = strtotime($key);
    $event_this_sdate = date_i18n('Y-m-d', $event_func_date);
    $event_this_stime = date_i18n("G:i", $event_start_dt_str);
    $event_this_etime = date_i18n("G:i", $event_end_dt_str);

    if ($hours_diff > 86400) {
      $stime = date_i18n('c', $event_start_dt_str);
      $etime = date_i18n('c', $event_end_dt_str);
      $event_ymd = date_i18n('Y-m-d', $event_cdate);
      $eventer_url = eventer_generate_endpoint_url('edate', $event_ymd, get_permalink($value));
    } else {
      $stime = date_i18n('c', strtotime($event_this_sdate . ' ' . $event_this_stime));
      $etime = (!$event_all_day && $event_end_date != '') ? date_i18n('c', strtotime($event_this_sdate . ' ' . $event_this_etime)) : '';
      $event_ymd = date_i18n('Y-m-d', strtotime($event_this_sdate . ' ' . $event_this_etime));
      $eventer_url = eventer_generate_endpoint_url('edate', $event_ymd, get_permalink($value));
    }
    $custom_permalink = get_post_meta($value, 'eventer_event_custom_permalink', true);
    if (filter_var($custom_permalink, FILTER_VALIDATE_URL)) {
      $eventer_url = $custom_permalink;
    }
    $venues = get_the_terms($value, 'eventer-venue');
    $elocation = $tickets_list = $ticket_exists = '';
    if (!is_wp_error($venues) && !empty($venues)) {
      $elocation = $venues[0]->name;
    }
    $original_event = eventer_wpml_original_post_id($value);
    $eventer_formatted_date = date_i18n('Y-m-d', strtotime($key));
    $show_tickets_info = (!is_array($value)) ? eventer_update_date_wise_bookings_table($value, $eventer_formatted_date . ' 00:00:00', array(), 2) : array();
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $eventer_currency = ($woocommerce_ticketing != 'on') ? eventer_get_currency_symbol(eventer_get_settings('eventer_paypal_currency')) : get_woocommerce_currency_symbol();
    $woo_currency_position = get_option('woocommerce_currency_pos');
    $woo_currency_position = ($woo_currency_position == "left") ? "suffix" : "postfix";
    $eventer_currency_position = ($woocommerce_ticketing != 'on') ? eventer_get_settings('eventer_currency_position') : $woo_currency_position;
    if (!empty($show_tickets_info)) { //print_r($show_tickets_info);
      foreach ($show_tickets_info as $ticket) {
        if (is_array($value)) continue;
        $ticket_exists = 1;
        $ticket_name = (isset($ticket['name'])) ? $ticket['name'] : '';
        $ticket_number = (isset($ticket['tickets'])) ? $ticket['tickets'] : '';
        $ticket_price = (isset($ticket['price'])) ? $ticket['price'] : '';
        $ticket_currency = $eventer_currency;
        if (is_numeric($ticket_price) && $ticket_price != '') {
          $ticket_price = ($eventer_currency_position != 'postfix') ? $ticket_currency . $ticket_price : $ticket_price . $ticket_currency;
          $discounted_price = '';
        } elseif (strpos($ticket_price, "-") !== false && $ticket_price != '') {
          $new_ticket_price = explode('-', $ticket_price);
          $calculate_discounted_price = $new_ticket_price[0] - $new_ticket_price[1];
          $discounted_price = ($eventer_currency_position != 'postfix') ? $ticket_currency . $calculate_discounted_price : $calculate_discounted_price . $ticket_currency;
          $show_price = ($eventer_currency_position != 'postfix') ? $ticket_currency . $new_ticket_price[0] : $new_ticket_price[0] . $ticket_currency;
          $ticket_price = '<del class="eventer-price-currency">' . $show_price . '</del>';
        } else {
          $ticket_price = $ticket_price;
          $discounted_price = '';
          $ticket_currency = '';
        }
        $remaining_tickets = ($ticket_number <= 0) ? '<i class="eventer-ticket-remaining eventer-ticket-full">' . esc_html__('All Booked', 'eventer') . '</i>' : '<i class="eventer-ticket-remaining">' . $ticket_number . ' ' . esc_html__('remaining', 'eventer') . '</i>';
        if ($ticket_number > 0) {
          $remaining_for_reg = 1;
        }
        $tickets_list .= '<li>
					<span class="eventer-ticket-type-price">' . $ticket_price . ' ' . $discounted_price . '</span>
					<span class="eventer-ticket-type-name">' . $ticket_name . ' ' . $remaining_tickets . '</span>
					</li>';
      }
    }

    $event_target = get_post_meta($value, 'eventer_event_custom_permalink_target', true);
    $event_target = ($event_target) ? $event_target : '_self';
    $preview = '<div class="tooltipevent eventer-calendar-event-preview" id="eventer-calendar-popup">
								<div class="eventer-ce-preview-body">
									<div class="eventer">
										<div class="eventer-ce-preview-header">';
    if (has_post_thumbnail($value)) {
      $preview .= get_the_post_thumbnail($value, 'thumbnail');
    }
    $preview .= '<h4 class="accent-color">' . get_the_title($value) . '</h4>';
    if ($elocation != '') {
      $preview .= '<span class="eventer-event-venue">' . $elocation . '</span>';
    }
    $preview .= '</div>';
    if ($ticket_exists != '') {
      $preview .= '<div class="eventer-ticket-details-wrap">
													<div class="eventer-ticket-details">
														<h3>' . esc_html__('Tickets details', 'eventer') . '</h3>
														<ul class="eventer-tickets-info">'
        . $tickets_list . '
														</ul>
													</div>
												</div>';
    } else {
      $preview .= '<div class="eventer-ticket-details-wrap">
      <div class="eventer-ticket-details">
      ' . get_the_excerpt($value) . '
      </div>
          </div>';
    }

    $preview .= '</div>
									</div>
								</div>
							</div>';
    $preview = $preview;
    // - json items -
    if (!is_numeric($value)) continue;
    $jsonevents[] = array(
      'id' => $value,
      'title' => apply_filters('eventer_raw_event_title', '', $value),
      'allDay' => ($event_all_day || $event_end_date == '') ? true : false,
      'start' => $stime,
      'end' => $etime,
      'url' => esc_url($eventer_url),
      'targ' => $event_target,
      'backgroundColor' => $color,
      'borderColor' => $color,
      'metas' => $preview,
    );
  }
}
// - fire away -
echo json_encode($jsonevents);
