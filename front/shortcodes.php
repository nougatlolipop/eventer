<?php
defined('ABSPATH') or die('No script kiddies please!');
/*
	* Shortcode function file
	* These shortcode can be overwrite by using remove_shortcode function in init action
	* To alter shortcode in parent or child theme functions.php file
	* Please create a function and hook that in init action
	* create another function with any name and place all code of that shortcode which you would like to alter
*/
function eventer_register_shortcodes()
{
  if (!function_exists('eventer_counter_shortcode')) {
    /*
			* eventer_counter_shortcode function
			* Used to generate output of counter shortcode
		*/
    function eventer_counter_shortcode($atts)
    {
      $atts = shortcode_atts(array(
        'ids' => array(), //comma separated ids of event to show only those specific events
        'terms_cats' => array(), //comma separated category terms ids to show events from that categories only
        'terms_tags' => array(), //comma separated tag terms ids to show events from those tags only
        'terms_venue' => array(), //comma separated venues terms ids to show events from that venues only
        'terms_organizer' => array(), //comma separated organizers terms ids to show events from that organiers only
        'venue' => '1', //Whether to show venue or not
        'type' => '', //WP or Google
        'event_until' => '1', //Show event until start time or end time
        'pass' => '',
      ), $atts, 'eventer_counter');
      $countdown_output = '';
      $event_ids = eventer_merge_all_ids($atts['ids'], $atts['terms_cats'], $atts['terms_tags'], $atts['terms_venue'], $atts['terms_organizer']);
      $get_eventer_counter = eventer_get_events_array($event_ids, 'counters', '', '', '1', $atts['type'], $atts['event_until'], EVENTER__LANGUAGE_CODE, $atts['pass']);
      $no_result = '<div class="eventer eventer-countdown eventer-counter-no-events">';
      $no_result .= '<div class="eventer-event-details">';
      $no_result .= '<h3>' . esc_html__('Sorry, No more event to show', 'eventer') . '</h3>';
      $no_result .= '</div>';
      $no_result .= '</div>';
      if ($get_eventer_counter['results'] <= 0) return $no_result;
      $eventer = $get_eventer_counter['events'];
      $stime_format = esc_attr(eventer_get_settings('start_time_format'));
      $etime_format = esc_attr(eventer_get_settings('end_time_format'));
      $time_separator = esc_attr(eventer_get_settings('time_separator'));
      $date_format = esc_attr(eventer_get_settings('eventer_date_format'));
      $stime_format = ($stime_format == '') ? get_option('time_format') : $stime_format;
      $etime_format = ($etime_format == '') ? get_option('time_format') : $etime_format;
      $date_format = ($date_format == '') ? get_option('date_format') : $date_format;
      $time_separator = ($time_separator == '') ? ' - ' : $time_separator;
      $recurring_icon_switch = eventer_get_settings('eventer_recurring_icon_yes');
      foreach ($eventer as $event_data) {
        $key = $event_data['start'];
        $value = $event_data['id'];
        if (get_post_status($value) != 'publish' && !is_array($value)) continue;
        $string_date = strtotime($key);
        $eventer_data = eventer_explore_event_ids($string_date, $value, $stime_format, $etime_format, $time_separator, $atts['event_until']);
        $event_all_dates = get_post_meta($value, 'eventer_event_frequency_type', true);
        $event_dynamic_dates = get_post_meta($value, 'eventer_event_multiple_dt_inc', true);
        $recurring_icon = (($recurring_icon_switch == "on" && is_numeric($event_all_dates)) || ($recurring_icon_switch == "on" && $event_dynamic_dates != '')) ? ' <i class="eventer-icon-refresh"></i>' : '';
        if (!empty($eventer_data)) {
          $event_ymd = date_i18n('Y-m-d', $eventer_data['show_counter']);
          $eventer_url = ($eventer_data['google_url'] == '') ? eventer_generate_endpoint_url('edate', $event_ymd, get_permalink($value)) : $eventer_data['google_url'];
          $eventer_url = apply_filters('eventer_permalink_setup', '', $eventer_url, $value);
          $eventer_category = get_the_terms($value, 'eventer-category');
          if (!is_wp_error($eventer_category) && !empty($eventer_category)) {
            $color = get_term_meta($eventer_category[0]->term_id, 'category_color', true);
            $color = ($color != '') ? $color : '';
          }
          $counter_id = wp_rand();
          $event_permalink_target = get_post_meta($value, 'eventer_event_custom_permalink_target', true);
          $countdown_output .=
            '<div class="eventer eventer-countdown">
						<div class="eventer-event-date" style="background-color: ' . $eventer_data['color'] . '">
							<span class="eventer-event-day">' . esc_attr(date_i18n('d', $eventer_data['show_counter'])) . '</span>
							<span class="eventer-event-month">' . esc_attr(date_i18n('F', $eventer_data['show_counter'])) . '</span>
						</div>
						<div class="eventer-event-details">
							<label>' . esc_html__('Next', 'eventer') . '</label>
							<span class="eventer-labeler">' . esc_attr($eventer_data['status']) . ' ' . esc_html__('Event', 'eventer') . '</span>
							<h3><a href="' . esc_url($eventer_url) . '" target="' . esc_attr($event_permalink_target) . '">' . esc_attr($eventer_data['etitle']) . $recurring_icon . '</a></h3>';
          if ($eventer_data['elocation'] != '') {
            $countdown_output .= '<span class="eventer-event-venue"><i class="eventer-icon-location-pin"></i> ' . esc_attr($eventer_data['elocation']) . '</span>';
          }
          $countdown_output .= '</div>
						<div id="counter-' . $counter_id . '" class="eventer-countdown-timer" data-date="' . esc_attr($string_date) . '">
						<div class="eventer-timer-col">
							<div>
								<div>
									<span id="days"></span>
									<strong>' . esc_html__('days', 'eventer') . '</strong>
								</div>
							</div>
						</div>
						<div class="eventer-timer-col">
							<div>
								<div>
									<span id="hours"></span> 
									<strong>' . esc_html__('hr', 'eventer') . '</strong>
								</div>
							</div>
						</div>
						<div class="eventer-timer-col">
							<div>
								<div>
									<span id="minutes"></span> 
									<strong>' . esc_html__('min', 'eventer') . '</strong>
								</div>
							</div>
						</div>
						<div class="eventer-timer-col">
							<div>
								<div>
									<span id="seconds"></span> 
									<strong>' . esc_html__('sec', 'eventer') . '</strong>
								</div>
							</div>
						</div>
						</div>
					</div>';
        }
        break;
      }
      return $countdown_output;
    }
    add_shortcode('eventer_counter', 'eventer_counter_shortcode');
  }

  if (!function_exists('eventer_calendar_shortcode')) {
    /*
			* eventer_calendar_shortcode function
			* Used to generate output of calendar shortcode
	*/
    function eventer_calendar_shortcode($atts)
    {
      $atts = shortcode_atts(array(
        'ids' => '',
        'terms_cats' => '',
        'terms_tags' => '',
        'terms_venue' => '',
        'terms_organizer' => '',
        'type' => '',
        'preview' => '',
        'defaultDate' => date_i18n('Y-m-d')
      ), $atts, 'eventer_calendar');
      $calendar_output = '';
      $google_calendar_id = '';
      if ($atts['type'] != "wp") {
        $google_calendar_id = eventer_get_settings('cal_id');
      }

      $site_lang = substr(get_locale(), 0, 2);
      if (function_exists('icl_object_id') && class_exists('SitePress')) {
        $site_lang = ICL_LANGUAGE_CODE;
      }
      $calendar_view = (eventer_get_settings('eventer_calendar_view') != '') ? eventer_get_settings('eventer_calendar_view') : 'month';
      $calendar_header_left = eventer_get_settings('eventer_calendar_header_left_view');
      $calendar_end_time = eventer_get_settings('eventer_calendar_end_time');
      $calendar_header_center = eventer_get_settings('eventer_calendar_header_center_view');
      $calendar_header_right = eventer_get_settings('eventer_calendar_header_right_view');
      $calendar_week_start = get_option('start_of_week');
      $calendar_rtl = (eventer_get_settings('eventer_calendar_rtl') == '1') ? true : false;
      $google_calendar_api = eventer_get_settings('google_cal_apikey');
      $google_calendar_weeks = eventer_get_settings('eventer_calendar_weeks');
      $time_format = eventer_convert_timeformat_tojs(get_option('time_format'));
      $time_format = eventer_convert_timeformat_tojs(eventer_get_settings('start_time_format'));
      wp_enqueue_script('fullcalendar-min');
      wp_enqueue_script('fullcalendar-gcal');
      wp_enqueue_script('fullcalendar-locale');
      wp_enqueue_script('fullcalendar-load');
      //These are the values that we are sending to eventer_calenadar.js file
      wp_localize_script('fullcalendar-load', 'fcal', array('sitelan' => $site_lang, 'homeurl' => EVENTER__PLUGIN_URL, 'calendar_api' => $google_calendar_api, 'time_format' => $time_format, 'cal_view' => $calendar_view, 'header_left' => $calendar_header_left, 'header_center' => $calendar_header_center, 'header_right' => $calendar_header_right, 'week_str' => $calendar_week_start, 'cal_rtl' => $calendar_rtl, 'event_end_time' => $calendar_end_time, 'weeks' => $google_calendar_weeks, 'defaultDate' => $atts['defaultDate']));
      wp_enqueue_style('fullcalendar-min');
      wp_enqueue_style('fullcalendar-print-min');

      $calendar_output .= '<div class="eventer-calendar-render"><div data-calendar="' . $google_calendar_id . '" class="shortcode-vals" style="display:none;">' . esc_attr(json_encode($atts)) . '</div><div class="calendar"></div></div>';
      return $calendar_output;
    }
    add_shortcode('eventer_calendar', 'eventer_calendar_shortcode');
  }

  if (!function_exists('eventer_generate_field')) {
    /*
			* eventer_generate_field function
			* Used to generate output of fields that are using in book ticket and contact manager forms
	*/
    function eventer_generate_field($atts)
    {
      $atts = shortcode_atts(array(
        'type' => 'text',
        'class' => '',
        'id' => '',
        'placeholder' => '',
        'param' => array(),
        'name' => '',
        'label' => '',
        'text_row' => '4',
        'textarea_type' => '',
        'featured_type' => '',
        'required' => 'no',
        'meta_key' => '',
        'meta_key_custom' => '',
      ), $atts, 'eventer_fields');
      $ticket_class = preg_replace('/[0-9]+/', '', $atts['meta_key']);
      $additional_class = ($atts['type'] == 'date') ? ' eventer_front_date_field ' : ' ';
      $required = ($atts['required'] == 'yes') ? '*' : '';
      $additional_class .= ($atts['type'] == 'featured') ? ' eventer_set_featured_image ' : ' ';
      $checkbox_class = ($atts['type'] == "checkbox") ? '' : '';
      $required_data = ($atts['required'] == "yes") ? 'data-required="1"' : '';
      $class = 'class="eventer_add_event_field ' . esc_attr($atts['class']) . $additional_class . ' ' . esc_attr($ticket_class) . '"';
      $id = ($atts['id'] != '') ? 'id="' . esc_attr($atts['id']) . '"' : '';
      $name = ($atts['name'] != '') ? 'name="' . esc_attr($atts['name']) . '"' : '';
      switch ($atts['type']) {
        case 'textarea':
          $id = ($atts['id'] != '') ? $atts['id'] : 'eventer_textarea' . wp_rand();
          if ($atts['textarea_type'] != 'wp') {
            $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <textarea data-meta="' . esc_attr($atts['meta_key']) . '" ' . $required_data . ' rows="' . esc_attr($atts['text_row']) . '" ' . $name . ' ' . $class . ' ' . $id . '></textarea>';
          } else {
            ob_start();
            echo '<label class="wp-editor-label">' . esc_attr($atts['label']) . $required . '</label>';
            wp_editor('', $id, array('editor_class' => $atts['class'] . ' eventer_wp_editor eventer_add_event_field eventer_data_required data-meta|' . $atts['meta_key'] . ' ', 'textarea_name' => $atts['name'], 'textarea_rows' => $atts['text_row'], 'media_buttons' => false, 'quicktags' => false));
            $field = ob_get_clean();
          }

          break;
        case 'select':
          $field = '';
          $options = '';
          $options .= '<option value="">' . esc_html__('Select', 'eventer') . '</option>';
          if (!empty($atts['param'])) {
            $var = str_replace(', ', ',', $atts['param']);
            $rows = explode(',', $var); //print_r($rows);
            $array = [];
            foreach ($rows as $row) {
              $matches = explode("|", $row);
              if (!empty($matches)) {
                if (isset($matches[0]) && isset($matches[1])) {
                  $array[$matches[0]] = $matches[1];
                }
              }
            }
            $params = (array_keys($array) !== range(0, count($array) - 1) && !empty($array)) ? $array : explode(',', $atts['param']);
            if (array_keys($array) !== range(0, count($array) - 1) && !empty($array)) {
              foreach ($params as $key => $value) {
                $selected = '';
                if (strpos($value, "{") !== false) {
                  $param = preg_replace(array('/^\{/', '/\}$/'), '', $value);
                  $selected = 'selected';
                }
                $options .= '<option ' . $selected . ' value="' . esc_attr($key) . '">' . esc_attr($value) . '</option>';
              }
            } else {
              foreach ($params as $param) {
                $selected = '';
                if (strpos($param, "{") !== false) {
                  $param = preg_replace(array('/^\{/', '/\}$/'), '', $param);
                  $selected = 'selected';
                }
                $options .= '<option ' . $selected . ' value="' . esc_attr($param) . '">' . esc_attr($param) . '</option>';
              }
            }

            $field .= '<label>' . esc_attr($atts['label']) . $required . '</label> <select data-meta="' . esc_attr($atts['meta_key']) . '" ' . $required_data . ' ' . $name . ' ' . $class . ' ' . $id . '>';
            $field .= $options;
            $field .= '</select>';
          }
          break;
        case 'checkbox':
          $field = '';
          $checkboxes = '';
          if (!empty($atts['param'])) {
            $var = str_replace(', ', ',', $atts['param']);
            $rows = explode(',', $var); //print_r($rows);
            $array = [];
            foreach ($rows as $row) {
              $matches = explode("|", $row);
              if (!empty($matches)) {
                if (isset($matches[0]) && isset($matches[1])) {
                  $array[$matches[0]] = $matches[1];
                }
              }
            }
            $params = (array_keys($array) !== range(0, count($array) - 1) && !empty($array)) ? $array : explode(',', $atts['param']);
            if (array_keys($array) !== range(0, count($array) - 1) && !empty($array)) {
              $field .= '<div class="check-radio-wrap"><label class="' . $checkbox_class . '">' . esc_attr($atts['label']) . $required . '</label>';
              foreach ($params as $key => $value) {
                $checked = '';
                if (strpos($value, "{") !== false) {
                  $param = preg_replace(array('/^\{/', '/\}$/'), '', $value);
                  $selected = 'checked';
                }
                $checkboxes .= ' <input data-meta="' . esc_attr($atts['meta_key']) . '" type="checkbox" ' . $required_data . ' ' . $name . ' ' . $checked . ' class="eventer_add_event_field eventer_set_class_for_add ' . esc_attr($atts['class']) . '" value="' . esc_attr($key) . '"> ' . esc_attr($value);
              }
              $field .= $checkboxes;
              $field .= '</div>';
            } else {
              $field .= '<div class="check-radio-wrap"><label class="' . $checkbox_class . '">' . esc_attr($atts['label']) . $required . '</label>';
              $params = explode(',', $atts['param']);
              foreach ($params as $param) {
                $checked = '';
                if (strpos($param, "{") !== false) {
                  $param = preg_replace(array('/^\{/', '/\}$/'), '', $param);
                  $checked = 'checked';
                }
                $checkboxes .= ' <input data-meta="' . esc_attr($atts['meta_key']) . '" type="checkbox" ' . $required_data . ' ' . $name . ' ' . $checked . ' class="eventer_add_event_field eventer_set_class_for_add ' . esc_attr($atts['class']) . '" value="' . esc_attr($param) . '"> ' . $param;
              }
              $field .= $checkboxes;
              $field .= '</div>';
            }
          }
          break;
        case 'eventer-tag':
          $eventer_terms = get_terms(array('taxonomy' => 'eventer-tag', 'hide_empty' => false));
          $field = '';
          if (!is_wp_error($eventer_terms) && !empty($eventer_terms)) {
            $field .= '<label class="">' . esc_attr($atts['label']) . $required . '</label>';
            $field .= '<select data-meta="eventer-tag" ' . $required_data . ' ' . $name . ' ' . $class . ' ' . $id . '>';
            $field .= '<option value="">' . esc_html__('Select', 'eventer') . '</option>';
            foreach ($eventer_terms as $term) {
              $field .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
            }
            $field .= '</select>';
          }
          break;
        case 'eventer-category':
          $eventer_terms = get_terms(array('taxonomy' => 'eventer-category', 'hide_empty' => false));
          $field = '';
          if (!is_wp_error($eventer_terms) && !empty($eventer_terms)) {
            $field .= '<label class="">' . esc_attr($atts['label']) . $required . '</label>';
            $field .= '<select data-meta="eventer-category" ' . $required_data . ' ' . $name . ' ' . $class . ' ' . $id . '>';
            $field .= '<option value="">' . esc_html__('Select', 'eventer') . '</option>';
            foreach ($eventer_terms as $term) {
              $field .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
            }
            $field .= '</select>';
          }
          break;
        case 'radio':
          $field = '';
          $radio = '';
          if (!empty($atts['param'])) {
            $var = str_replace(', ', ',', $atts['param']);
            $rows = explode(',', $var); //print_r($rows);
            $array = [];
            foreach ($rows as $row) {
              $matches = explode("|", $row);
              if (!empty($matches)) {
                if (isset($matches[0]) && isset($matches[1])) {
                  $array[$matches[0]] = $matches[1];
                }
              }
            }
            $params = (array_keys($array) !== range(0, count($array) - 1) && !empty($array)) ? $array : explode(',', $atts['param']);
            if (array_keys($array) !== range(0, count($array) - 1) && !empty($array)) {
              $field .= '<div class="check-radio-wrap"><label class="' . $checkbox_class . '">' . esc_attr($atts['label']) . $required . '</label>';
              foreach ($params as $key => $value) {
                $checked = '';
                if (strpos($value, "{") !== false) {
                  $param = preg_replace(array('/^\{/', '/\}$/'), '', $value);
                  $selected = 'checked';
                }
                $radio .= ' <input data-meta="' . esc_attr($atts['meta_key']) . '" type="radio" ' . $required_data . ' ' . $name . ' ' . $checked . ' class="eventer_add_event_field eventer_set_class_for_add ' . esc_attr($atts['class']) . '" value="' . esc_attr($key) . '"> ' . esc_attr($value);
              }
              $field .= $radio;
              $field .= '</div>';
            } else {
              $field .= '<div class="check-radio-wrap"><label class="' . $checkbox_class . '">' . esc_attr($atts['label']) . $required . '</label>';
              $params = explode(',', $atts['param']);
              foreach ($params as $param) {
                $checked = '';
                if (strpos($param, "{") !== false) {
                  $param = preg_replace(array('/^\{/', '/\}$/'), '', $param);
                  $checked = 'checked';
                }
                $radio .= ' <input data-meta="' . esc_attr($atts['meta_key']) . '" type="radio" ' . $required_data . ' ' . $name . ' ' . $checked . ' ' . $class . ' value="' . esc_attr($param) . '"> ' . esc_attr($param);
              }
              $field .= $radio;
              $field .= '</div>';
            }
          }
          break;
        case 'number':
          $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <input data-meta="' . esc_attr($atts['meta_key']) . '" type="number" ' . $required_data . ' value="" ' . $name . ' ' . $class . ' ' . $id . '>';
          break;
        case 'div':
          $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <div ' . $class . ' ' . $id . '></div>';
          break;
        case 'email':
          $value = '';
          if ($atts['name'] == 'reg_email') {
            $current_user = wp_get_current_user();
            $value = $current_user->user_email;
          }
          $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <input data-meta="' . esc_attr($atts['meta_key']) . '" type="email" ' . $required_data . ' value="' . $value . '" ' . $name . ' ' . $class . ' ' . $id . '>';
          break;
        case 'date':
          $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <input data-meta="' . esc_attr($atts['meta_key']) . '" type="text" ' . $required_data . ' value="" ' . $name . ' ' . $class . ' ' . $id . '>';
          break;
        case 'featured':
          if ($atts['featured_type'] != 'wp') {
            $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <input data-meta="' . esc_attr($atts['meta_key']) . '" type="file" ' . $required_data . ' value="" ' . $name . ' ' . $class . ' ' . $id . '>';
            $field .= '<div class="eventer_set_featured_image"><div class="eventer_featured_image_preview eventer_add_event_field" data-meta="eventer_featured_image_url"></div></div>';
          } else {
            $field = '<div class="eventer-row eventer-wp-featured-media">';
            $field .= '<div class="eventer-col10">';
            $field .= '<div class="eventer">		
							<label>' . esc_attr($atts['label']) . $required . '</label>
							<button class="eventer_featured_image_wp_add eventer-btn eventer-btn-default"><i class="eventer-icon-plus"></i> ' . esc_html('Open Media Library', 'eventer') . '</button>
							<button class="eventer_featured_image_wp_remove hidden"><i class="eventer-icon-close"></i></button>
						</div></div>';
            $field .= '<div class="eventer-col10">';
            $field .= '<div class="eventer eventer_featured_image_wp_preview eventer_featured_image_preview eventer_add_event_field" data-meta="eventer_featured_image_url">
							<input type="hidden" class="eventer_featured_image_id eventer_add_event_field" value="" data-meta="eventer_thumbnail_id">
							<input type="hidden" class="eventer_featured_image_URL eventer_add_event_field" value="" data-meta="eventer_thumbnail_URL">
						</div>';
            $field .= '</div>';
            $field .= '</div>';
          }

          break;
        default:
          $value = '';
          if ($atts['name'] == 'reg_name') {
            $current_user = wp_get_current_user();
            $value = $current_user->display_name;
          }
          $field = '<label>' . esc_attr($atts['label']) . $required . '</label> <input data-meta="' . esc_attr($atts['meta_key']) . '" type="text" ' . $required_data . ' value="' . $value . '" ' . $name . ' ' . $class . ' ' . $id . '>';
          break;
      }
      return $field;
    }
    add_shortcode('eventer_fields', 'eventer_generate_field');
  }

  if (!function_exists('eventer_display_field')) {
    /*
			* eventer_display_field function
			* Used to display output of fields that are using in book ticket and contact manager forms
	*/
    function eventer_display_field($atts)
    {
      $atts = shortcode_atts(array(
        'type' => 'text',
        'class' => '',
        'id' => '',
        'placeholder' => '',
        'param' => array(),
        'name' => '',
        'label' => '',
        'text_row' => '4',
        'required' => 'no',
        'meta_key' => '',
        'meta_key_custom' => '',
        'featured_type' => '',
        'textarea_type' => '',
      ), $atts, 'eventer_fields_display');
      return json_encode($atts);
    }
    add_shortcode('eventer_fields_display', 'eventer_display_field');
  }

  function eventer_paid_shortcode($atts, $content = null)
  {
    extract(shortcode_atts(array(
      "data"    => "",
    ), $atts));
    return $data;
  }
  add_shortcode('eventer_paid', 'eventer_paid_shortcode');

  function eventer_offline_shortcode($atts, $content = null)
  {
    extract(shortcode_atts(array(
      "data"    => "",
    ), $atts));
    return $data;
  }
  add_shortcode('eventer_offline', 'eventer_offline_shortcode');

  function eventer_free_shortcode($atts, $content = null)
  {
    extract(shortcode_atts(array(
      "data"    => "",
    ), $atts));
    return $data;
  }
  add_shortcode('eventer_free', 'eventer_free_shortcode');

  function eventer_field_row($atts, $content = null)
  {
    return '<div class="eventer-row">' . do_shortcode($content) . '</div>';
  }
  add_shortcode('eventer_field_row', 'eventer_field_row');

  function eventer_field_halfcol($atts, $content = null)
  {
    return '<div class="eventer-col5 eventer-col10-xs">' . do_shortcode($content) . '</div>';
  }
  add_shortcode('eventer_field_halfcol', 'eventer_field_halfcol');

  function eventer_ticket_url_in_email($atts, $content = null)
  {
    extract(shortcode_atts(array(
      "completed"    => "",
      "pending"    => "",
      "failed"    => "",
    ), $atts));
    $completed = (isset($atts['completed']) && $atts['completed'] == "1") ? "1" : '';
    $pending = (isset($atts['pending']) && $atts['pending'] == "1") ? "1" : '';
    $failed = (isset($atts['failed']) && $atts['failed'] == "1") ? "1" : '';
    $print_tkt = '';
    if ($completed == "1") {
      $print_tkt .= '{completed}';
    } elseif ($pending == "1") {
      $print_tkt .= '{pending}';
    } elseif ($failed == "1") {
      $print_tkt .= '{failed}';
    }
    return $print_tkt;
  }
  add_shortcode('eventer_tkturl', 'eventer_ticket_url_in_email');

  function eventer_add_event_from_frontend($atts)
  {
    $atts = shortcode_atts(array(
      'id' => wp_rand(1001, 99999999),
      'sections' => '1',
      'name' => '',
      'status' => '0',
      'default' => '0',
      'eventer' => '',
      'load' => '',
    ), $atts, 'eventer_add_new');
    $current_user = get_current_user_id();
    $set_section = $atts['sections'];
    $eventer_edit = (isset($_REQUEST['fevent']) && is_numeric($_REQUEST['fevent'])) ? $_REQUEST['fevent'] : $atts['eventer'];
    $eventer_author = get_post_field('post_author', $eventer_edit);
    $validate_author = ($current_user == $eventer_author) ? $eventer_edit : '';
    $submit_btn = ($validate_author !== '') ? esc_html__('Update', 'eventer') : esc_html__('Add Event', 'eventer');
    $settings = '';
    $form_options = get_option('eventer_forms_data');
    if ($atts['default'] == 'yes' && !isset($form_options[$atts['id']])) {
      $set_section = 5;
      $default_form_details = (isset($form_options['eventer-default-form-settings'])) ? $form_options['eventer-default-form-settings'] : '';
      $form_options[$atts['id']] = $default_form_details;
      update_option('eventer_forms_data', $form_options);
    }
    $form_options = (empty($form_options)) ? array() : $form_options;
    $current_form_details = (isset($form_options[$atts['id']])) ? $form_options[$atts['id']] : '';
    $disabled = $mandatory = $disabled_check = $mandatory_check = $section_position = $dynamic_fields = $dynamic = $field_position = array();
    $field_position = $sections_position = array();
    if (!empty($current_form_details)) {
      $dynamic = (isset($current_form_details['dynamic']) && !empty($current_form_details['dynamic'])) ? $current_form_details['dynamic'] : array();
      $sections_position = (isset($current_form_details['sections']) && !empty($current_form_details['sections'])) ? $current_form_details['sections'] : $sections;
    }
    $action_link = '';
    $admin = 1;
    $set_section = ($set_section < count($sections_position)) ? count($sections_position) : $set_section;
    if (isset($_REQUEST['settings']) && ((current_user_can('editor')) || (current_user_can('administrator')))) {

      wp_enqueue_script('eventer_admin_form');
      wp_localize_script('eventer_admin_form', 'adminval', array(
        'ajax_url' => admin_url('admin-ajax.php'), 'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'add_shortcode' => esc_html__('Add Shortcode', 'eventer'),
        'place_shortcode' => esc_html__('Place field shortcode', 'eventer'),
        'delete_row' => esc_html__('Delete Row', 'eventer'),
        'disable_row' => esc_html__('Disable row', 'eventer'),
        'enable_row' => esc_html__('Enable row', 'eventer'),
        'failure' => esc_html__('There is some issue in processing.', 'eventer'),
      ));
      $admin = 0;
      $action_link = '<div class="eventer eventer-fe-form-head"><div class="eventer-row"><div class="eventer-col10"><a href="' . esc_url(get_permalink()) . '" class="eventer-btn eventer-btn-default pull-right">' . esc_html__('Preview', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a></div></div></div>';
    } else {
      if (!isset($_REQUEST['settings']) && ((current_user_can('editor')) || (current_user_can('administrator')))) {
        $action_link = '<div class="eventer eventer-fe-form-head"><div class="eventer-row"><div class="eventer-col10"><a href="' . esc_url(add_query_arg('settings', '1', get_permalink())) . '" class="eventer-btn eventer-btn-default pull-right">' . esc_html__('Settings', 'eventer') . ' <i class="eventer-icon-arrow-right"></i></a></div></div></div>';
      }
      if ($atts['load'] == '') {
        wp_enqueue_media();
        wp_enqueue_style('eventer_ui_css');
        wp_enqueue_style('eventer_datetimepicker');
        wp_enqueue_script('eventer_datetimepicker');
        wp_enqueue_script('eventer_front_form');
        wp_localize_script('eventer_front_form', 'ajaxval', array(
          'ajax_url' => admin_url('admin-ajax.php'), 'root' => esc_url_raw(rest_url()),
          'nonce' => wp_create_nonce('wp_rest'),
          'enter_email' => esc_html__('Please enter valid email address', 'eventer'),
          'enter_pass' => esc_html__('Please enter password', 'eventer'),
          'repeat_pass' => esc_html__('Please enter same password as above', 'eventer'),
          'enter_verify' => esc_html__('Please enter verification code', 'eventer'),
          'enter_name' => esc_html__('Please enter username', 'eventer'),
          'success' => esc_html__('Thank you for your submission!', 'eventer'),
          'failure' => esc_html__('Your submission could not be processed at this time. Please try again later.', 'eventer'),
          'current_user_id' => get_current_user_id(),
          'load_scripts' => '1',
          'event_status' => esc_attr($atts['status']),
          'update' => $validate_author
        ));
      }
    }
    $autocomplete_values = array('eventer-organizer', 'eventer-venue', 'tickets');
    $eventer_autocomplete_values = array();
    foreach ($autocomplete_values as $autocomplete) {
      $already_added_data = get_user_meta(get_current_user_id(), $autocomplete, true);
      if (!empty($already_added_data)) {
        $field_values = array();
        foreach ($already_added_data as $key => $value) {

          if ($autocomplete == 'eventer-organizer' || $autocomplete == 'eventer-venue') {
            $term_key = ($autocomplete == 'eventer-organizer') ? 'organizer' : 'venue';
            $field_values[] = array('label' => $value[$autocomplete], 'value' => $value);
          } else {
            $term_key = $autocomplete;
            $field_values[] = array('label' => $value['name'], 'value' => $value);
          }
        }
        $eventer_autocomplete_values[$term_key] = $field_values;
      }
    }

    $front_end = ($atts['load'] == '') ? $action_link : '';
    $front_end .= '<div id="eventer-form-area">
		<form class="eventer eventer_add_new_event" id="eventer_add_new_event" data-name="' . esc_attr($atts['name']) . '" action="" data-sections="' . esc_attr($atts['sections']) . '" data-status="' . esc_attr($atts['status']) . '" method="POST" data-id="' . esc_attr($atts['id']) . '">
		<div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
		<input type="hidden" value="' . esc_attr($validate_author) . '" class="eventer-set-form-fields">
		<input type="hidden" value="" class="eventer_copied_content">
		<input type="hidden" value="' . esc_attr(json_encode($eventer_autocomplete_values)) . '" class="eventer_autocomplete_values">
		<!-- Event Details -->';
    $front_end .=     '<div class="eventer-fn-form-status" id="section-message" style="display:none;">';
    $front_end .=    '<div class="eventer-row">';
    $front_end .=    '<div class="eventer-col10 eventer-fn-form-status-error">';
    $front_end .=    '</div>';
    $front_end .=    '</div>';
    $front_end .=    '</div>';
    $form_start = $front_end;
    $section1 = $section2 = $section3 = $section4 = $section5 = $section6 = $section7 = $section8 = $section9 = $section10 = '';
    for ($sections = 1; $sections <= $set_section; $sections++) {
      $type = (!empty($dynamic) && isset($dynamic['section' . $sections])) ? $dynamic['section' . $sections]['type'] : '';
      $btn = (!empty($dynamic) && isset($dynamic['section' . $sections])) ? $dynamic['section' . $sections]['btn'] : '';
      if ($btn == "disabled" && $admin != 0) continue;
      $front_end =     '<div class="eventer-fn-form-block eventer-fn-edetails eventer_dynamic_section_area" id="section' . esc_attr($sections) . '" data-section="' . esc_attr($type) . '">';
      $front_end .=     apply_filters('eventer_set_dynamic_field', '', $admin, 'section' . $sections, $atts['id'], $form_options);
      $front_end .=     '<div class="eventer_add_field_before"></div>';
      $front_end .=     apply_filters('eventer_set_dynamic_fields_button', '', $admin, 'section' . $sections, $atts['id'], $form_options);
      $front_end .=    '</div>';
      ${'section' . $sections} = $front_end;
      $field_position[] = 'section' . $sections;
    }
    $all_sections = $field_position;
    $field_position = (!empty($sections_position)) ? $sections_position : $field_position;
    $diff_sections = array_diff($all_sections, $field_position);
    $field_position = array_merge($field_position, $diff_sections);
    $front_end = '<div class="eventer-row">
								<div class="eventer-col10 eventer-col10-xs">';
    if ($admin != 0) {
      $front_end .= '<input class="eventer-submit-form-btn eventer-btn eventer-btn-primary" type="submit" value="' . esc_attr($submit_btn) . '">';
    } else {
      $front_end .= '<input type="button" class="eventer-settings-action-form" style="display:none;">';
    }
    $front_end .= '</div></div></form></div>';
    $form_close = $front_end;
    $form_show = $form_start;
    foreach ($field_position as $position) {
      if ($position == 'section-message') continue;
      $form_show .= ${$position};
    }
    $form_show .= $form_close;
    return $form_show;
  }
  add_shortcode('eventer_add_new', 'eventer_add_event_from_frontend');
  function eventer_login_forms()
  {
    $atts = shortcode_atts(array(
      'required' => '',
    ), $atts, 'eventer_login');
    $registration_section = '
		<div class="eventer-fn-form-block eventer-users-section eventer-toggle-area" id="eventer_user_registration_section">
			<div class="eventer-forms-info">
			' . esc_html__('To view this page you must login to your account. Login below or register for a new account.', 'eventer') . '
			</div>
			<div class="message eventer-fn-form-status-blank"></div>
			
			
			<div class="eventer-fe-toggle-forms eventer-fe-toggle-form1">
				<div class="eventer-row">
					<div class="eventer-col1by3"></div>
					<div class="eventer-col1by3">';
    $registration_section .= apply_filters('eventer_create_login_form', '');
    $registration_section .=
      '
						<p class="eventer-form-info-links"><a href="#" class="eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-fe-toggle-form2" data-eventer-toggle-out=".eventer-fe-toggle-form1">' . esc_html__('Not Registered Yet? Register Here', 'eventer') . '</a></p>
						</div>
					<div class="eventer-col1by3"></div>
				</div>
			</div>
			<div class="eventer-fe-toggle-forms eventer-fe-toggle-form2">
				<div class="eventer-row">
					<div class="eventer-col1by3"></div>
					<div class="eventer-col1by3">
						<form class="eventer eventer_register_user eventer-fe-rforms" action="" method="POST">
						<div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
							<h4>Register</h4>
							<label>' . esc_html__('Username', 'eventer') . '</label>
							<input required type="text" name="eventer-register-username" class="eventer_register_username">
							<label>' . esc_html__('Email', 'eventer') . '</label>
							<input required type="email" name="eventer-register-email" class="eventer_register_email">
							<label>' . esc_html__('Password', 'eventer') . '</label>
							<input required type="password" name="eventer-register-pass" class="eventer_register_pass1">
							<label>' . esc_html__('Repeat Password', 'eventer') . '</label>
							<input required type="password" name="eventer-register-pass" class="eventer_register_pass2"><br/>
							<input type="submit" value="' . esc_html__('Register', 'eventer') . '">
							<p class="eventer-form-info-links pull-right"><a href="#" class="eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-fe-toggle-form1" data-eventer-toggle-out=".eventer-fe-toggle-form2"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back to Login', 'eventer') . '</a></p>
						</form>
					</div>
					<div class="eventer-col1by3"></div>
				</div>
			</div>
			<div class="eventer-fe-toggle-forms eventer-fe-toggle-form3">
				<div class="eventer-row">
					<div class="eventer-col1by3"></div>
					<div class="eventer-col1by3">
							<form class="eventer eventer_reset_password eventer-fe-rforms" action="" method="POST">
								<div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
								<h4>Recover Password</h4>
								<div class="eventer-form-fields-area">
									<label>' . esc_html__('Email', 'eventer') . '</label>
									<input required type="email" name="eventer-reset-username" class="eventer_reset_username">
									<div class="eventer_reset_fields_area">

									</div>
								</div>
								<div class="eventer-spacer-20"></div>
									<input type="submit" value="' . esc_html__('Validate', 'eventer') . '">
									<p class="eventer-form-info-links pull-right"><a href="#" class="eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-fe-toggle-form1" data-eventer-toggle-out=".eventer-fe-toggle-form3"><i class="eventer-icon-arrow-left"></i> ' . esc_html__('Back to Login', 'eventer') . '</a></p>
							</form>
						</div>
					<div class="eventer-col1by3"></div>
				</div>
			</div>
		</div>';
    return $registration_section;
  }
  add_shortcode('eventer_login', 'eventer_login_forms');
  function eventer_dashboard($atts)
  {
    $atts = shortcode_atts(array(
      'default' => '',
      'add_new' => '',
    ), $atts, 'eventer_dashboard');
    $default_shortcode = '';
    if ($atts['default'] == 'submissions') {
      $set_default = 'eventer_submissions';
      //$default_shortcode = do_shortcode('[eventer_submissions]');
      //$default_shortcode .= do_shortcode('[eventer_dash_terms]');
    } elseif ($atts['default'] == 'bookings') {
      $set_default = 'eventer_bookings';
      //$default_shortcode = do_shortcode('[eventer_bookings]');
    } else {
      $set_default = 'eventer_add_new';
      //$default_shortcode = do_shortcode('[eventer_add_new]');
    }
    $current_user = get_current_user_id();
    $user_details = wp_get_current_user();
    $eventer_edit = (isset($_REQUEST['fevent']) && is_numeric($_REQUEST['fevent'])) ? $_REQUEST['fevent'] : '';
    $eventer_author = get_post_field('post_author', $eventer_edit);
    $validate_author = ($current_user == $eventer_author) ? $eventer_edit : '';
    $user_populate_data = '';
    $form_options = get_option('eventer_forms_data');
    $form_options = (empty($form_options)) ? array() : $form_options;
    $current_form_details = (isset($form_options[esc_attr($atts['add_new'])])) ? $form_options[esc_attr($atts['add_new'])] : '';
    $current_form_status = (!empty($current_form_details)) ? $current_form_details['status'] : 'draft';
    echo '<div style="display:none;">';
    wp_editor('', 'eventer-load-wp_editor');
    echo '</div>';
    wp_enqueue_media();
    wp_enqueue_style('eventer_ui_css');
    wp_enqueue_style('eventer_datetimepicker');
    wp_enqueue_script('eventer_datetimepicker');
    wp_enqueue_script('eventer_front_form');
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    wp_localize_script('eventer_front_form', 'ajaxval', array(
      'ajax_url' => admin_url('admin-ajax.php'), 'root' => esc_url_raw(rest_url()),
      'auto' => json_encode($user_populate_data),
      'nonce' => wp_create_nonce('wp_rest'),
      'enter_email' => esc_html__('Please enter valid email address', 'eventer'),
      'enter_pass' => esc_html__('Please enter password', 'eventer'),
      'repeat_pass' => esc_html__('Please enter same password', 'eventer'),
      'enter_verify' => esc_html__('Please enter verification code', 'eventer'),
      'enter_name' => esc_html__('Please enter username', 'eventer'),
      'success' => esc_html__('Thanks for your submission!', 'eventer'),
      'failure' => esc_html__('Your submission could not be processed.', 'eventer'),
      'current_user_id' => get_current_user_id(),
      'event_status' => esc_attr($current_form_status),
      'load_scripts' => '',
      'woo_tickets' => $woocommerce_ticketing,
      'add_event_msg' => '<div class="eventer-fn-form-status-success">' . esc_html__('Event is successfully added', 'eventer') . '</div>',
      'update_event_msg' => '<div class="eventer-fn-form-status-success">' . esc_html__('Event is successfully updated', 'eventer') . '</div>',
      'update' => $validate_author
    ));
    wp_enqueue_script('eventer-dashboard-scripts');
    wp_localize_script('eventer-dashboard-scripts', 'dashboard', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'root' => esc_url_raw(rest_url()),
      'current_user_id' => get_current_user_id(),
      'nonce' => wp_create_nonce('wp_rest'),
      'default_shortcode' => $set_default,
      'order_string' => esc_html__('order', 'eventer') . '# '
    ));
    $dashboard =
      '<div class="eventer eventer-fe-dashboard">
	<div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
		<div class="eventer-fe-dash-nav">
			<ul class="eventer-nav-all-users">
				<li class="eventer-fe-booking-linkss" style="display:none;"></li>
				<li class="" style="display:none;">
					<a data-tab="eventer_login" class="eventer_dashboard_tabs">
					</a>
				</li>';
    if ($atts['add_new'] != '') {
      $dashboard .=
        '<li class="">
					<a data-temp="" data-tab="eventer_add_new" data-shortcode="' . esc_attr($atts['add_new']) . '" class="eventer_dashboard_tabs" title="' . esc_html__('Add new event', 'eventer') . '">
						<i class="eventer-icon-cloud-upload"></i>
						' . esc_html__('Add new event', 'eventer') . '
					</a>
				</li>';
    }
    $dashboard .=
      '<li class="">
					<a data-tab="eventer_submissions" data-shortcode="" class="eventer_dashboard_tabs" title="' . esc_html__('Submissions', 'eventer') . '">
						<i class="eventer-icon-briefcase"></i>
						' . esc_html__('Submissions', 'eventer') . '
					</a>
				</li>
				<li class="">
					<a data-tab="eventer_bookings" data-shortcode="" class="eventer_dashboard_tabs" title="' . esc_html__('Bookings', 'eventer') . '">
						<i class="eventer-icon-event"></i>
						' . esc_html__('Bookings', 'eventer') . '
					</a>
				</li>
				
			</ul>
			<div class="eventer-dashboard-download-tickets" style="display:none;">
					<form action="' . esc_url(admin_url('admin-ajax.php')) . '" method="post" class="eventer-show-download-tickets-form" style="">
						<input type="hidden" name="action" value="eventer_woo_download_tickets">
						<input type="hidden" class="eventer-woo-tickets" name="tickets" value="">
						<input type="hidden" name="captcha" value="' . wp_create_nonce('eventer-tickets-download') . '">
						<a class="eventer-btn eventer-btn-default eventer-admin-woo-download-tickets-action" href="" title="' . esc_html__('Download Tickets', 'eventer') . '">
							' . esc_html__('Download Tickets', 'eventer') . '
						</a>
					</form>

				</div>
			<ul class="eventer-dash-nav-right">
				<li class="eventer-fe-usermenu eventer-fe-dd">
					<a>
						<!--<img src="images/user-avatar.png" alt="' . esc_attr($user_details->user_nicename) . '">-->
						' . esc_attr($user_details->user_nicename) . '
					</a>
					<div class="eventer-fe-dropdown">
						<div class="eventer-fe-dropdown-in">
							<ul>
								<li>' . esc_html__('Hi', 'eventer') . ', ' . esc_attr($user_details->user_nicename) . '!</li>
								<!--<li><a>' . esc_html__('My Profile', 'eventer') . '</a></li>
								<li><a>' . esc_html__('Change Password', 'eventer') . '</a></li>-->
								<li><a href="' . esc_url(wp_logout_url(get_permalink())) . '">' . esc_html__('Logout', 'eventer') . '</a></li>
							</ul>
						</div>
					</div>
				</li>
				<!--<li>
					<a href="#" class="eventer-modal-trigger" data-eventer-modal="#eventer-help-form">' . esc_html__('Help', 'eventer') . '</a>
					<div class="eventer-modal eventer-modal-md" id="eventer-help-form">
						<div class="eventer-modal-wrap">
							<a href="#" class="eventer-modal-close"><i class="eventer-icon-close"></i></a>
							<div class="eventer-modal-body">
								<h3>' . esc_html__('Contact event manager', 'eventer') . '</h3>
								<form>
									<label>' . esc_html__('Your name', 'eventer') . '</label>
									<input type="text" name="name">
									<label>' . esc_html__('Your Email', 'eventer') . '</label>
									<input type="email" name="email">
									<label>' . esc_html__('Your Phone', 'eventer') . '</label>
									<input type="text" name="phone">
									<label>' . esc_html__('Your message', 'eventer') . '</label>
									<textarea name="message" rows="5"></textarea>
									<button type="submit" class="eventer-btn">' . esc_html__('Submit', 'eventer') . '</button>
								</form>
							</div>
						</div>
					</div>
				</li>-->
			</ul>
		</div>
		<div class="eventer-fe-dash-content" data-posts="" data-termcount="" data-postscount="100" data-type="' . esc_attr($atts['default']) . '">
			<div id="eventer-dashboard-content-area" class="eventer-fe-content-col eventer-fe-content-part eventer-dashboard-main">' .
      '' . '</div>
		</div>
	</div>';

    return $dashboard;
  }

  add_shortcode('eventer_dashboard', 'eventer_dashboard');
  function eventer_dashboard_bookings($atts)
  {
    $atts = shortcode_atts(array(
      'required' => '',
    ), $atts, 'eventer_bookings');
    $bookings = '
		<div class="eventer-dt-headers eventer-section-bookings">
			<ul class="eventer-fe-table-legends">
				<li class="eventer-legend-pending">' . esc_html__('Order Pending', 'eventer') . '</li>
				<li class="eventer-legend-active">' . esc_html__('Tickets Available', 'eventer') . '</li>
			</ul>
			<ul class="eventer-multi-nav">
				<li class="active eventer-get-user-bookings" data-book=""><a>All</a></li>
				<li class=" eventer-get-user-bookings" data-book="upcoming"><a>Upcoming</a></li>
				<li class=" eventer-get-user-bookings" data-book="passed"><a>Passed</a></li>
			</ul>
		</div>';
    return $bookings;
  }

  add_shortcode('eventer_bookings', 'eventer_dashboard_bookings');
  function eventer_dashboard_submissions($atts)
  {
    $atts = shortcode_atts(array(
      'required' => '',
    ), $atts, 'eventer_submissions');
    $submissions = '
		<div class="eventer-dt-headers">
			<ul class="eventer-fe-table-legends">
				<li class="eventer-legend-pending">' . esc_html__('Under Review', 'eventer') . '</li>
				<li class="eventer-legend-active">' . esc_html__('Active', 'eventer') . '</li>
				<li class="eventer-legend-disabled">' . esc_html__('Disabled', 'eventer') . '</li>
			</ul>
			<ul class="eventer-multi-nav">
				<li class="active eventer_submission_status_wise"><a data-status="draft,pending,publish">' . esc_html__('All', 'eventer') . '</a></li>
				<li class="eventer_submission_status_wise"><a data-status="pending">' . esc_html__('Under Review', 'eventer') . '</a></li>
				<li class="eventer_submission_status_wise"><a data-status="draft">' . esc_html__('Disabled', 'eventer') . '</a></li>
			</ul>
		</div>

		<ul class="eventer-fe-dash-list eventer-fe-submissions-list">

			<li class="eventer_dashboard_submissions_list" style="display:none;" id="">
				<div class="eventer-while-delete">
					<label>' . esc_html__('Date', 'eventer') . '</label>
					<strong class="eventer-submission-date"></strong>
				</div>
				<div>
					<span class="eventer-fe-event-title eventer-submission-title"></span>
				</div>
				<div class="eventer-while-delete">
					<label>' . esc_html__('Venue', 'eventer') . '</label>
					<span class="eventer-fe-event-meta eventer-submission-venue"></span>
				</div>
				<div class="eventer-while-delete">
					<label>' . esc_html__('Organizer', 'eventer') . '</label>
					<span class="eventer-fe-event-meta eventer-submission-organizer"></span>
				</div>
				<div class="eventer-fe-dd eventer-fe-list-actions eventer-while-delete">
					<a href="#"><i class="eventer-icon-options"></i></a>
					<div class="eventer-fe-dropdown">
						<div class="eventer-fe-dropdown-in eventer-submission-actions">
							<ul>
								<li data-action="edit"><a>' . esc_html__('Edit', 'eventer') . '</a></li>
								<li class="eventer-submission-act eventer-submission-status-act" data-publish="' . esc_html__('Enable', 'eventer') . '" data-draft="' . esc_html__('Disable', 'eventer') . '" data-action="draft"><a>' . esc_html__('Disable', 'eventer') . '</a></li>
								<li class="eventer-submission-act" data-action="delete"><a>' . esc_html__('Delete', 'eventer') . '</a></li>
							</ul>
						</div>
					</div>
				</div>
			</li>
		</ul>';
    return $submissions;
  }

  add_shortcode('eventer_submissions', 'eventer_dashboard_submissions');
  function eventer_dashboard_terms($atts)
  {
    $atts = shortcode_atts(array(
      'required' => '',
    ), $atts, 'eventer_dash_terms');
    $terms = '
	<div class="eventer-fe-content-col eventer-fe-sidebar">

			</div>';
    return $terms;
  }
  add_shortcode('eventer_dash_terms', 'eventer_dashboard_terms');
  add_shortcode('eventer_metas', 'eventer_meta_section');
  add_shortcode('eventer_tickets', 'eventer_ticket_section');
  add_shortcode('eventer_ajax_tickets', 'eventer_ajax_ticket_section');
  add_shortcode('eventer_ajax_tickets_meta', 'eventer_ajax_ticket_meta_section');
  add_shortcode('eventer_social_share', 'eventer_share_section');
  add_shortcode('eventer_save_events', 'eventer_save');
  function eventer_divi_builder_support()
  {
    $post_types[] = 'eventer';
    return $post_types;
  }
  function eventer_meta_section($atts, $content = "")
  {
    $atts = shortcode_atts(array(
      'id' => '',
      'date' => ''
    ), $atts, 'eventer_metas');
    ob_start();
    $event_id = (isset($atts['id'])) ? $atts['id'] : get_the_ID();
    $params = apply_filters('eventer_registration_data_collect', 1, $event_id, $atts['date']);
    eventer_append_template_with_arguments('eventers/sections/event', 'metas', $params);
    eventer_append_template_with_arguments('eventers/modal/modal', 'contact', $params);
    return ob_get_clean();
  }
  function eventer_ticket_section($atts, $content = "")
  {
    $atts = shortcode_atts(array(
      'id' => '',
      'date' => '',
      'time' => '00:00:00',
      'ajax' => ''
    ), $atts, 'eventer_tickets');
    ob_start();
    echo '<div class="eventer-front-ticket-area-dynamic">';
    $event_id = (isset($atts['id']) && $atts['id'] != '') ? intval($atts['id']) : get_the_ID();
    $params = apply_filters('eventer_registration_data_collect', 1, $event_id, $atts['date'], $atts['time']);
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $eventer_woo_layout = eventer_get_settings('eventer_woo_layout');
    $params['woo_payment'] = $woocommerce_ticketing;
    $params['woocommerce_ticketing'] = $woocommerce_ticketing;
    $params['dynamic_val'] = $event_id;
    $params['ajax'] = $atts['ajax'];
    if ($woocommerce_ticketing == "on" && $eventer_woo_layout != "on" && $eventer_woo_layout != "checkout") {
      eventer_append_template_with_arguments('eventers/sections/event', 'wootickets', $params);
    } else {
      $registration_switch = get_post_meta($event_id, 'eventer_event_registration_swtich', true);
      $params['registration_switch'] = $registration_switch;
      eventer_append_template_with_arguments('eventers/sections/event', 'tickets', $params);
      if ($woocommerce_ticketing != "on") {
        eventer_append_template_with_arguments('eventers/modal/modal', 'booking', $params);
      } else {
        eventer_append_template_with_arguments('eventers/modal/modal', 'woobooking', $params);
      }
    }
    eventer_append_template_with_arguments('eventers/modal/modal', 'thanks', $params);
    eventer_append_template_with_arguments('eventers/modal/tickets', 'create', $params);
    eventer_append_template_with_arguments('eventers/modal/modal', 'tickets', $params);
    echo '</div>';
    return ob_get_clean();
  }
  function eventer_ajax_ticket_section($atts, $content = "")
  {
    $atts = shortcode_atts(array(
      'id' => '',
      'date' => '',
      'time' => '00:00:00',
      'ajax' => ''
    ), $atts, 'eventer_tickets');
    ob_start();
    echo '<div class="eventer-front-ticket-area-dynamic">';
    $event_id = (isset($atts['id']) && $atts['id'] != '') ? intval($atts['id']) : get_the_ID();
    $params = apply_filters('eventer_registration_data_collect', 1, $event_id, $atts['date'], $atts['time']);
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $eventer_woo_layout = eventer_get_settings('eventer_woo_layout');
    $params['woo_payment'] = $woocommerce_ticketing;
    $params['woocommerce_ticketing'] = $woocommerce_ticketing;
    $params['dynamic_val'] = $event_id;
    $params['ajax'] = $atts['ajax'];
    $registration_switch = get_post_meta($event_id, 'eventer_event_registration_swtich', true);
    $params['registration_switch'] = $registration_switch;
    eventer_append_template_with_arguments('eventers/modal/modal', 'bookingajax', $params);
    return ob_get_clean();
  }
  function eventer_ajax_ticket_meta_section($atts, $content = "")
  {
    $atts = shortcode_atts(array(
      'id' => '',
      'date' => '',
      'time' => '00:00:00',
      'ajax' => ''
    ), $atts, 'eventer_tickets');
    ob_start();
    echo '<div class="eventer-front-ticket-area-dynamic">';
    $event_id = (isset($atts['id']) && $atts['id'] != '') ? intval($atts['id']) : get_the_ID();
    $params = apply_filters('eventer_registration_data_collect', 1, $event_id, $atts['date'], $atts['time']);
    $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $eventer_woo_layout = eventer_get_settings('eventer_woo_layout');
    $params['woo_payment'] = $woocommerce_ticketing;
    $params['woocommerce_ticketing'] = $woocommerce_ticketing;
    $params['dynamic_val'] = $event_id;
    $params['ajax'] = $atts['ajax'];
    $registration_switch = get_post_meta($event_id, 'eventer_event_registration_swtich', true);
    $params['registration_switch'] = $registration_switch;
    if ($woocommerce_ticketing != 'on' || ($woocommerce_ticketing == 'on' & $eventer_woo_layout != 'off')) {
      eventer_append_template_with_arguments('eventers/sections/event', 'tickets', $params);
    } else {
      eventer_append_template_with_arguments('eventers/sections/event', 'wootickets', $params);
    }
    return ob_get_clean();
  }
  function eventer_share_section($atts, $content = "")
  {
    ob_start();
    $event_id = (isset($atts['id'])) ? $atts['id'] : get_the_ID();
    $params['event_id'] = $event_id;
    eventer_append_template_with_arguments('eventers/sections/event', 'social', $params);
    return ob_get_clean();
  }
  function eventer_save($atts, $content = "")
  {
    ob_start();
    $event_id = (isset($atts['id'])) ? $atts['id'] : get_the_ID();
    $params = apply_filters('eventer_registration_data_collect', 1, $event_id);
    eventer_append_template_with_arguments('eventers/sections/event', 'save', $params);
    return ob_get_clean();
  }
}
add_action('init', 'eventer_register_shortcodes');
