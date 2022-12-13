<?php
defined('ABSPATH') or die('No script kiddies please!');
include('bookings-details.php');
if (!function_exists('load_custom_wp_admin_scripts')) {
  /*
	* load_custom_wp_admin_scripts function
	* Enqueue the style and js for back end
	* Variables of strings are used to send them in js file using wp_localize_script function, so that they can be fully translatable
	*/
  function load_custom_wp_admin_scripts($hook)
  {
    $screen = get_current_screen();
    if ('edit.php' != $hook && $screen->post_type != 'eventer' && $hook != 'admin_page_eventer-booking-info') {
      return;
    }
    $category_eventer = '';
    $google_map_api = eventer_get_settings('google_apikey');
    $multiple_days_message = esc_html__('Recurring options will not work if you select event of more than one day/24 hours.', 'eventer');
    $primary_button = esc_html__('Primary', 'eventer');
    $saving_btn = esc_html__('Saving', 'eventer');
    $save_btn = esc_html__('Save', 'eventer');
    $remove_btn = esc_html__('Remove', 'eventer');
    $time_format = eventer_get_settings('eventer_datepicker_format');
    $eventer_id = (isset($_REQUEST['post'])) ? get_post_meta($_REQUEST['post'], 'eventer_primary_term', true) : '';
    $shortcode_copy = esc_html__('shortcode copied to clipboard', 'eventer');
    wp_enqueue_script('jquery-ui-datepicker');
    if (isset($_REQUEST['taxonomy']) == 'eventer-venue') {
      wp_enqueue_media();
    }
    $coupons_load = '';
    if (isset($_REQUEST['taxonomy']) == 'eventer-category' || isset($_REQUEST['page']) == 'eventer_settings_options') {
      wp_enqueue_script('wp-color-picker');
      wp_enqueue_style('wp-color-picker');
      $category_eventer = 1;
      $coupons_load = '1';
    }
    $screen = get_current_screen();
    $screen_taxonomy = (property_exists($screen, 'taxonomy')) ? $screen->taxonomy : '';
    wp_enqueue_style('eventer_ui_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css', false, "1.9.0", false);
    wp_enqueue_style('eventer_datetimepicker', EVENTER__PLUGIN_URL . 'css/jquery.simple-dtpicker.css');
    wp_enqueue_script('eventer_datetimepicker', EVENTER__PLUGIN_URL . 'js/jquery.simple-dtpicker.js', array('jquery-ui-core'), '', true);
    wp_enqueue_script('eventer_ui_plugins', EVENTER__PLUGIN_URL . 'js/jquery-ui.multidatespicker.js', array('jquery-ui-core'), '', true);
    wp_enqueue_script('eventer_admin_gmap_autocomplete', '//maps.googleapis.com/maps/api/js?libraries=places&key=' . $google_map_api);
    wp_enqueue_script('eventer_admin_scripts', EVENTER__PLUGIN_URL . 'js/admin_scripts.js', array('jquery'), '', true);
    wp_enqueue_script('eventer_admin_checkin', EVENTER__PLUGIN_URL . 'js/admin_checkin.js', array('jquery'), '', true);
    wp_localize_script('eventer_admin_checkin', 'checkin', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_localize_script('eventer_admin_scripts', 'dynamicval', array('multiplemsg' => $multiple_days_message, 'ajax_url' => admin_url('admin-ajax.php'), 'venue_nonce' => wp_create_nonce("eventer_make_event_primary_venue_nonce"), 'eventer' => $eventer_id, 'event_cat' => $category_eventer, 'registrant_remove_nonce' => wp_create_nonce("eventer_remove_registrant_nonce"), 'shortcode_copied' => $shortcode_copy, 'primary_btn' => $primary_button, 'gmap_api' => $google_map_api, 'screen_tax' => $screen_taxonomy, 'saving_btn' => $saving_btn, 'save_btn' => $save_btn, 'remove_btn' => $remove_btn, 'time_format' => $time_format, 'load_coupons' => $coupons_load, 'week_start' => get_option('start_of_week')));
    wp_enqueue_style('eventer_admin_style', EVENTER__PLUGIN_URL . 'css/admin_style.css');
  }
  add_action('admin_enqueue_scripts', 'load_custom_wp_admin_scripts');
}

function eventer_store_default_form_settings()
{
  $existing_form_settings = get_option('eventer_forms_data');
  if (empty($existing_form_settings)) {
    $form_settings_val = unserialize('a:1:{s:29:"eventer-default-form-settings";a:5:{s:7:"dynamic";a:5:{s:8:"section5";a:3:{s:4:"type";s:0:"";s:3:"btn";s:7:"enabled";s:6:"fields";a:4:{i:0;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:3:{i:0;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:27:"eventer-0.33826693254757756";s:9:"shortcode";s:171:"[eventer_fields type="text" required="yes" featured_type="" text_row="4" textarea_type="" class="" id="" name="Event title" label="Event title" meta_key="title" param="|"]";}i:1;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:25:"eventer-0.640654605593687";s:9:"shortcode";s:198:"[eventer_fields type="date" required="yes" featured_type="" text_row="4" textarea_type="" class="" id="" name="Event start date" label="Event start date" meta_key="eventer_event_start_dt" param="|"]";}i:2;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.4938553863997305";s:9:"shortcode";s:192:"[eventer_fields type="date" required="yes" featured_type="" text_row="4" textarea_type="" class="" id="" name="Event end date" label="Event end date" meta_key="eventer_event_end_dt" param="|"]";}}}i:1;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:1:{i:0;a:3:{s:6:"column";s:2:"10";s:2:"id";s:25:"eventer-0.505141878395051";s:9:"shortcode";s:208:"[eventer_fields type="textarea" required="yes" featured_type="" text_row="5" textarea_type="wp" class="" id="eventer-wp-editor" name="Event description" label="Event description" meta_key="content" param="|"]";}}}i:2;a:2:{s:6:"status";s:7:"disable";s:6:"shorts";a:1:{i:0;a:3:{s:6:"column";s:2:"10";s:2:"id";s:25:"eventer-0.761745691922583";s:9:"shortcode";s:171:"[eventer_fields type="featured" required="no" featured_type="wp" text_row="4" textarea_type="" class="" id="" name="Event image" label="Event image" meta_key="" param="|"]";}}}i:3;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:1:{i:0;a:3:{s:6:"column";s:2:"10";s:2:"id";s:27:"eventer-0.44703476545334286";s:9:"shortcode";s:169:"[eventer_fields type="featured" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Event image" label="Event image" meta_key="" param="|"]";}}}}}s:8:"section2";a:3:{s:4:"type";s:0:"";s:3:"btn";s:7:"enabled";s:6:"fields";a:1:{i:0;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:3:{i:0;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.5236805438494746";s:9:"shortcode";s:213:"[eventer_fields type="select" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Recurring event" label="Recurring event" meta_key="eventer_event_frequency_type" param="no|No,1|Yes"]";}i:1;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.7611748863799075";s:9:"shortcode";s:328:"[eventer_fields type="select" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Event recurring" label="Event recurring" meta_key="eventer_event_frequency" param="1|Every day,2|Every second day,3|Every third day,4|Every fourth day,5|Every fifth day,6|Every sixth day,7|Every week,30|Every month"]";}i:2;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:27:"eventer-0.03241813253593584";s:9:"shortcode";s:212:"[eventer_fields type="number" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Recurring frequency" label="Recurring frequency" meta_key="eventer_event_frequency_count" param="|"]";}}}}}s:8:"section1";a:3:{s:4:"type";s:17:"eventer-organizer";s:3:"btn";s:7:"enabled";s:6:"fields";a:2:{i:0;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:2:{i:0;a:3:{s:6:"column";s:1:"5";s:2:"id";s:26:"eventer-0.5579738415141968";s:9:"shortcode";s:210:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="eventer_get_saved_data" id="" name="Organizer name" label="Organizer name" meta_key="eventer-organizer" param="|"]";}i:1;a:3:{s:6:"column";s:1:"5";s:2:"id";s:26:"eventer-0.1088470188812577";s:9:"shortcode";s:188:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Organizer email" label="Organizer email" meta_key="organizer_email" param="|"]";}}}i:1;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:2:{i:0;a:3:{s:6:"column";s:1:"5";s:2:"id";s:27:"eventer-0.49224397744948345";s:9:"shortcode";s:192:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Oganizer website" label="Oganizer website" meta_key="organizer_website" param="|"]";}i:1;a:3:{s:6:"column";s:1:"5";s:2:"id";s:27:"eventer-0.41708263450628547";s:9:"shortcode";s:217:"[eventer_fields type="number" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Organizer phone" label="Organizer phone" meta_key="organizer_phone" meta_key="organizer_phone" param="|"]";}}}}}s:8:"section4";a:3:{s:4:"type";s:13:"eventer-venue";s:3:"btn";s:7:"enabled";s:6:"fields";a:1:{i:0;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:2:{i:0;a:3:{s:6:"column";s:1:"5";s:2:"id";s:26:"eventer-0.6507914953184427";s:9:"shortcode";s:176:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Venue name" label="Venue name" meta_key="eventer-venue" param="|"]";}i:1;a:3:{s:6:"column";s:1:"5";s:2:"id";s:26:"eventer-0.3573525663784346";s:9:"shortcode";s:182:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Venue address" label="Venue address" meta_key="venue_address" param="|"]";}}}}}s:8:"section3";a:3:{s:4:"type";s:7:"tickets";s:3:"btn";s:7:"enabled";s:6:"fields";a:4:{i:0;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:3:{i:0;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:28:"eventer-0.024479173211175653";s:9:"shortcode";s:257:"[eventer_fields type="select" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Registration" label="Registration" meta_key="eventer_event_registration_swtich" meta_key="eventer_event_registration_swtich" param="no|No,1|Yes"]";}i:1;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.6542816918697981";s:9:"shortcode";s:200:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Custom URL" label="Custom URL" meta_key="eventer_event_custom_registration_url" param="|"]";}i:2;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.6991851641940596";s:9:"shortcode";s:234:"[eventer_fields type="select" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Custom URL Target" label="Custom URL Target" meta_key="eventer_event_registration_target" param="_self|Self,_blank|Blank"]";}}}i:1;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:3:{i:0;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.4266792564731865";s:9:"shortcode";s:207:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="eventer_get_saved_data" id="" name="Ticket name" label="Ticket name" meta_key="eventer_ticket_name0" param="|"]";}i:1;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.4884638474764328";s:9:"shortcode";s:197:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket Quantity" label="Ticket Quantity" meta_key="eventer_ticket_quantity0" param="|"]";}i:2;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:27:"eventer-0.08380307243794716";s:9:"shortcode";s:198:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket price in $" label="Ticket price in $" meta_key="eventer_ticket_price0" param="|"]";}}}i:2;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:3:{i:0;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.7664874155298499";s:9:"shortcode";s:185:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket name" label="Ticket name" meta_key="eventer_ticket_name1" param="|"]";}i:1;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:27:"eventer-0.05912964997083725";s:9:"shortcode";s:197:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket Quantity" label="Ticket Quantity" meta_key="eventer_ticket_quantity1" param="|"]";}i:2;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.8276879316332272";s:9:"shortcode";s:198:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket price in $" label="Ticket price in $" meta_key="eventer_ticket_price1" param="|"]";}}}i:3;a:2:{s:6:"status";s:6:"enable";s:6:"shorts";a:3:{i:0;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.2155069921141367";s:9:"shortcode";s:185:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket name" label="Ticket name" meta_key="eventer_ticket_name2" param="|"]";}i:1;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:27:"eventer-0.20954417053933816";s:9:"shortcode";s:197:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket Quantity" label="Ticket Quantity" meta_key="eventer_ticket_quantity2" param="|"]";}i:2;a:3:{s:6:"column";s:4:"1by3";s:2:"id";s:26:"eventer-0.6886590932765758";s:9:"shortcode";s:198:"[eventer_fields type="text" required="no" featured_type="" text_row="4" textarea_type="" class="" id="" name="Ticket price in $" label="Ticket price in $" meta_key="eventer_ticket_price2" param="|"]";}}}}}}s:8:"sections";a:6:{i:0;s:15:"section-message";i:1;s:8:"section5";i:2;s:8:"section2";i:3;s:8:"section1";i:4;s:8:"section4";i:5;s:8:"section3";}s:6:"number";s:1:"5";s:6:"status";s:5:"draft";s:4:"name";s:0:"";}}');
    update_option('eventer_forms_data', $form_settings_val);
  }
}
add_action('plugins_loaded', 'eventer_store_default_form_settings');

if (!function_exists('eventer_get_post_language')) {
  /*
	* eventer_get_post_language function
	* Get language of post while using WPML plugin
	*/
  function eventer_get_post_language($post_id)
  {
    if (class_exists('SitePress')) {
      global $wpdb;
      $query = $wpdb->prepare('SELECT language_code FROM ' . $wpdb->prefix . 'icl_translations WHERE element_id="%d"', $post_id);
      $query_exec = $wpdb->get_row($query);
      if (!empty($query_exec)) {
        return $query_exec->language_code;
      }
    }
  }
  //add_action('init', 'eventer_get_post_language');
}

if (!function_exists('eventer_add_form_fields_venue')) {
  /*
	* eventer_add_form_fields_venue function
	* Make term description field to WYSIWYG, if yoast seo plugin activated then this function will not work
	*/
  function eventer_add_form_fields_venue($term, $taxonomy)
  {
    ?>
    <table class="form-table">
      <tbody>
        <tr valign="top" class="form-field">
          <th scope="row">
            <label><?php esc_html_e('Description', 'eventer'); ?></label>
          </th>
          <td>
            <?php wp_editor(html_entity_decode($term->description), 'description', array('media_buttons' => true)); ?>
            <script>
              jQuery(window).ready(function() {
                jQuery('label[for=description]').parent().parent().remove();
              });
            </script>
          </td>
        </tr>
      </tbody>
    </table>
    <?php
      }
      if (is_admin() && function_exists('is_plugin_active') && !is_plugin_active('wordpress-seo/wp-seo.php')) {
        $taxonomies = array('eventer-venue', 'eventer-category', 'eventer-organizer', 'eventer-tag');
        foreach ($taxonomies as $taxonomy) {
          add_action("{$taxonomy}_edit_form_fields", 'eventer_add_form_fields_venue', 10, 2);
        }
        remove_filter('pre_term_description', 'wp_filter_kses');
        remove_filter('term_description', 'wp_kses_data');
      }
    }

    function eventer_date_sort($a, $b)
    {
      return strtotime($a) - strtotime($b);
    }

    if (!function_exists('eventer_find_non_recurring_events')) {
      /*
	* eventer_find_non_recurring_events function
	* This function create array of all dates of recurring events and save them in database, so that we can easily get the all date of event by ID 		only.
	* This is the case if user do not select any recurring option for event
	*/
      function eventer_find_non_recurring_events($ID)
      {
        $ID = eventer_wpml_original_post_id($ID);
        $update_date = array();
        $show_event_until = eventer_get_settings('countdown_timer');
        $eventer_start_dt = get_post_meta($ID, 'eventer_event_start_dt', true);
        $eventer_end_dt = get_post_meta($ID, 'eventer_event_end_dt', true);
        $eventer_start_dt_str = strtotime($eventer_start_dt);
        $eventer_end_dt_str = strtotime($eventer_end_dt);
        $hour_diff = (intval($eventer_end_dt_str) - intval($eventer_end_dt_str));
        $eventer_dynamic_include = get_post_meta($ID, 'eventer_event_multiple_dt_inc', true);
        $eventer_dynamic_include_get = array_map('trim', explode(',', $eventer_dynamic_include));
        $eventer_dynamic_include_get = array_filter($eventer_dynamic_include_get);

        $count_date_diff = eventer_dateDiff(date('Y-m-d', $eventer_start_dt_str), date('Y-m-d', $eventer_end_dt_str));
        $eventer_multi_date = (!empty(get_option('eventer_multi_day_event'))) ? get_option('eventer_multi_day_event') : array();
        if (date_i18n('Y-m-d', $eventer_start_dt_str) == date_i18n('Y-m-d', $eventer_end_dt_str)) {
          $actual_date = date_i18n('Y-m-d', $eventer_start_dt_str);
          $update_date = (!empty($eventer_dynamic_include_get)) ? array_merge($eventer_dynamic_include_get, array($actual_date)) : array($actual_date);
          if (in_array($ID, $eventer_multi_date)) {
            $updated_multi = array_diff($eventer_multi_date, array($ID));
            update_option('eventer_multi_day_event', $updated_multi);
          }
        } else {
          $update_date[] = date_i18n('Y-m-d', $eventer_start_dt_str);
          if (!empty($eventer_dynamic_include_get)) {
            $update_date = array_merge($update_date, $eventer_dynamic_include_get);
          }
          $updating_multi_ids = array_merge($eventer_multi_date, array($ID));
          $updating_multi_ids = array_unique($updating_multi_ids);
          update_option('eventer_multi_day_event', $updating_multi_ids);
        }
        return $update_date;
      }
    }

    if (!function_exists('eventer_find_first_recurring_events')) {
      /*
	* eventer_find_first_recurring_events function
	* This function create array of all dates of recurring events and save them in database, so that we can easily get the all date of event by ID 		only.
	* This is the case if user select fixed date option for event frequency
	*/
      function eventer_find_first_recurring_events($ID)
      {
        $ID = eventer_wpml_original_post_id($ID);
        $show_event_until = eventer_get_settings('countdown_timer');
        $eventer_start_dt = get_post_meta($ID, 'eventer_event_start_dt', true);
        $eventer_end_dt = get_post_meta($ID, 'eventer_event_end_dt', true);
        $eventer_start_dt_str = strtotime($eventer_start_dt);
        $eventer_end_dt_str = strtotime($eventer_end_dt);
        $eventer_start_time = date('G:i', $eventer_start_dt_str);
        $eventer_end_time = date('G:i', $eventer_end_dt_str);
        $eventer_time_set = ($show_event_until == 1) ? $eventer_start_time : $eventer_end_time;
        $eventer_default_set = ($show_event_until == 1) ? $eventer_start_dt_str : $eventer_end_dt_str;

        //First Selection Fixed Date EveryDay, EveryWeek, EveryMonth
        $eventer_frequency = get_post_meta($ID, 'eventer_event_frequency', true);

        $eventer_dynamic_include = get_post_meta($ID, 'eventer_event_multiple_dt_inc', true) . ',';
        $eventer_dynamic_include_get = array_map('trim', explode(',', $eventer_dynamic_include));
        $eventer_dynamic_include_get = array_filter($eventer_dynamic_include_get);

        $eventer_frequency_count = get_post_meta($ID, 'eventer_event_frequency_count', true);
        $eventer_dynamic_exclude = get_post_meta($ID, 'eventer_event_multiple_dt_exc', true) . ',';
        $eventer_dynamic_exclude_get = array_map('trim', explode(',', $eventer_dynamic_exclude));
        $eventer_dynamic_exclude_get = array_filter($eventer_dynamic_exclude_get);

        $count_date_diff = eventer_dateDiff(date('Y-m-d', $eventer_start_dt_str), date('Y-m-d', $eventer_end_dt_str));
        $update_date = array();
        $update_date[] = date('Y-m-d', $eventer_start_dt_str);
        //if($count_date_diff<=0)
        //{
        if ($eventer_frequency_count > 0) {
          $eventer_frequency_val = ($eventer_frequency == 30) ? 'month' : 'days';
          $eventer_frequency = ($eventer_frequency == 30) ? 1 : $eventer_frequency;
          for ($i = 0; $i < $eventer_frequency_count; $i++) {
            $event_new_date = strtotime("+" . $eventer_frequency . " " . $eventer_frequency_val, $eventer_start_dt_str);
            $event_new_date = date('Y-m-d', $event_new_date);
            $update_date[] = $event_new_date;
            $eventer_start_dt_str = strtotime($event_new_date);
          }
        }
        //}
        $update_date_new = array_merge($update_date, $eventer_dynamic_include_get);
        $update_date_unique = array_unique($update_date_new);
        $updated_date_get = array_diff($update_date_unique, $eventer_dynamic_exclude_get);
        usort($updated_date_get, "eventer_date_sort");
        $eventer_multi_date = (!empty(get_option('eventer_multi_day_event'))) ? get_option('eventer_multi_day_event') : array();
        if (in_array($ID, $eventer_multi_date)) {
          $updated_multi = array_diff($eventer_multi_date, array($ID));
          update_option('eventer_multi_day_event', $updated_multi);
        }
        return $updated_date_get;
      }
    }

    if (!function_exists('eventer_find_second_recurring_events')) {
      /*
	* eventer_find_second_recurring_events function
	* This function create array of all dates of recurring events and save them in database, so that we can easily get the all date of event by ID 		only.
	* This is the case if user select week day option for event frequency
	*/
      function eventer_find_second_recurring_events($ID)
      {
        $ID = eventer_wpml_original_post_id($ID);
        $all_events = array();
        $show_event_until = eventer_get_settings('countdown_timer');
        $eventer_start_dt = get_post_meta($ID, 'eventer_event_start_dt', true);
        $eventer_end_dt = get_post_meta($ID, 'eventer_event_end_dt', true);
        $eventer_start_dt_str = strtotime($eventer_start_dt);
        $eventer_end_dt_str = strtotime($eventer_end_dt);
        $eventer_start_time = date_i18n('G:i', $eventer_start_dt_str);
        $eventer_end_time = date_i18n('G:i', $eventer_end_dt_str);
        $eventer_time_set = ($show_event_until == 1) ? $eventer_start_time : $eventer_end_time;
        $eventer_default_set = ($show_event_until == 1) ? $eventer_start_dt_str : $eventer_end_dt_str;
        $event_weekly = get_post_meta($ID, 'eventer_event_weekly_repeat', true);

        //Second Selection Week Day First Sunday of First Week ETC
        $eventer_week_number = get_post_meta($ID, 'eventer_event_day_month', true);
        $eventer_week_day = get_post_meta($ID, 'eventer_event_week_day', true);

        $eventer_dynamic_include = get_post_meta($ID, 'eventer_event_multiple_dt_inc', true) . ',';
        $eventer_dynamic_include_get = array_map('trim', explode(',', $eventer_dynamic_include));
        $eventer_dynamic_include_get = array_filter($eventer_dynamic_include_get);

        $eventer_frequency_count = get_post_meta($ID, 'eventer_event_frequency_count', true);
        $eventer_dynamic_exclude = get_post_meta($ID, 'eventer_event_multiple_dt_exc', true) . ',';
        $eventer_dynamic_exclude_get = array_map('trim', explode(',', $eventer_dynamic_exclude));
        $eventer_dynamic_exclude_get = array_filter($eventer_dynamic_exclude_get);

        $count_date_diff = eventer_dateDiff(date('Y-m-d', $eventer_start_dt_str), date('Y-m-d', $eventer_end_dt_str));


        $get_updated_date = $eventer_start_dt_str;
        if ($event_weekly === 'on') {
          foreach ($eventer_week_number as $week_number) {
            switch ($week_number) {
              case 'first':
                $daysbyweek = 7;
                break;
              case 'second':
                $daysbyweek = 14;
                break;
              case 'third':
                $daysbyweek = 21;
                break;
              case 'fourth':
                $daysbyweek = 28;
                break;
              default:
                $daysbyweek = 35;
                break;
            }
            $eventer_frequency_val = 'days';
            $event_new_date_original = date_i18n('Y-m-d', $eventer_start_dt_str);
            for ($i = 1; $i <= $eventer_frequency_count; $i++) {
              $extent_for = $daysbyweek * $i;
              $event_new_date_get = date_i18n('Y-m-d', strtotime($event_new_date_original . ' + ' . $extent_for . ' ' . $eventer_frequency_val));
              foreach ($eventer_week_day as $week_day) {
                $day_num = date('N', strtotime($week_day));
                $dayofweek = date('w', strtotime($event_new_date_get));
                $event_new_date    = date('Y-m-d', strtotime(($day_num - $dayofweek) . ' day', strtotime($event_new_date_get)));
                $all_events[] = $event_new_date;
              }
            }
          }
        } else {
          //$all_events[] = date_i18n('Y-m-d', $eventer_start_dt_str);
          for ($i = 0; $i < $eventer_frequency_count; $i++) {
            $eventDate = strtotime(date_i18n('Y-m-01', $get_updated_date));
            $event_start_date_next = strtotime("+" . $i . " month", $eventDate);
            $next_month = date_i18n('F', $event_start_date_next);
            $next_event_year = date_i18n('Y', $event_start_date_next);
            $start_date_time = date_i18n('G:i', $get_updated_date);
            foreach ($eventer_week_number as $week_number) {
              foreach ($eventer_week_day as $week_day) {
                $all_events_add = date('Y-m-d', strtotime($week_number . ' ' . $week_day . ' of ' . $next_month . ' ' . $next_event_year));
                $all_events[] = $all_events_add;
              }
            }
          }
        }

        $update_date_new = array_merge($all_events, $eventer_dynamic_include_get);
        $update_date_unique = array_unique($update_date_new);
        $updated_date_get = array_diff($update_date_unique, $eventer_dynamic_exclude_get);
        usort($updated_date_get, "eventer_date_sort");
        $eventer_multi_date = (!empty(get_option('eventer_multi_day_event'))) ? get_option('eventer_multi_day_event') : array();
        if (in_array($ID, $eventer_multi_date)) {
          $updated_multi = array_diff($eventer_multi_date, array($ID));
          update_option('eventer_multi_day_event', $updated_multi);
        }
        return $updated_date_get;
      }
    }

    if (!function_exists('eventer_date_updated')) {
      /*
	* eventer_find_second_recurring_events function
	* This function create array of all dates of recurring events and save them in database, so that we can easily get the all date of event by ID 		only.
	* This is the case if user select week day option for event frequency
	*/
      function eventer_date_updated($post_id)
      {
        if (get_post_type($post_id) != 'eventer') return;
        $eventer_frequency_type = get_post_meta($post_id, 'eventer_event_frequency_type', true);
        $all_day = get_post_meta($post_id, 'eventer_event_all_day', true);
        $st_date = get_post_meta($post_id, 'eventer_event_start_dt', true);
        $en_date = get_post_meta($post_id, 'eventer_event_end_dt', true);
        if ($all_day && $en_date < $st_date) {
          update_post_meta($post_id, 'eventer_event_end_dt', $st_date);
        }
        update_option('eventer_saved_show_until', '');
        $update_all_date = '';
        $site_lang = EVENTER__LANGUAGE_CODE;
        $polylang_lang = (function_exists('pll_get_post_language')) ? pll_get_post_language($post_id) : '';
        $site_lang = ($polylang_lang) ? $polylang_lang : $site_lang;
        update_option($site_lang . '_eventer_future_data_wp', array());
        update_option($site_lang . '_eventer_future_data_formatted_wp', array());
        $eventer_stored = get_option($site_lang . '_eventer_stored');
        $new_eventer_save[$post_id] = get_the_title($post_id);
        $merge_eventer_stored = array_merge($eventer_stored, $new_eventer_save);
        update_option($site_lang . '_eventer_stored', $merge_eventer_stored);
        switch ($eventer_frequency_type) {
          case 'no':
            $update_all_date = eventer_find_non_recurring_events($post_id);
            update_post_meta($post_id, 'eventer_all_dates', $update_all_date);
            break;
          case '1':
            $update_all_date = eventer_find_first_recurring_events($post_id);
            update_post_meta($post_id, 'eventer_all_dates', $update_all_date);
            break;
          case '2':
            $update_all_date = eventer_find_second_recurring_events($post_id);
            update_post_meta($post_id, 'eventer_all_dates', $update_all_date);
            break;
          default:
            $update_all_date = eventer_find_non_recurring_events($post_id);
            update_post_meta($post_id, 'eventer_all_dates', $update_all_date);
        }
      }
    }

    if (!function_exists('eventer_actioned_post')) {
      /*
	* eventer_actioned_post function
	* This function remove all recurring date from database if trash action trigerred
	*/
      function eventer_actioned_post($post_id)
      {
        if (get_post_type($post_id) != 'eventer') return;
        update_post_meta($post_id, 'eventer_all_dates', '');
        $eventer_multi_date = (!empty(get_option('eventer_multi_day_event'))) ? get_option('eventer_multi_day_event') : array();
        if (in_array($post_id, $eventer_multi_date)) {
          $eventer_multi_date = array_diff($eventer_multi_date, array($post_id));
          $updating_multi_ids = array_unique($eventer_multi_date);
          update_option('eventer_multi_day_event', $updating_multi_ids);
        }
      }
      add_action('save_post', 'eventer_date_updated', 99, 2);
      add_action('edit_post', 'eventer_date_updated', 99, 2);
      add_action('publish_eventer', 'eventer_date_updated', 999, 2);
      add_action('trashed_post', 'eventer_actioned_post', 10, 2);
      add_action('untrash_post', 'eventer_date_updated', 10, 2);
      add_action('eventer_create_action_rest_api', 'eventer_date_updated', 10, 1);
    }

    function eventer_get_eventer_list()
    {
      $event_list = array('' => esc_html__('Next Future Event', 'eventer'));
      $args = array('post_type' => 'eventer', 'posts_per_page' => -1);
      $eventer_list = new WP_Query($args);
      if ($eventer_list->have_posts()) : while ($eventer_list->have_posts()) : $eventer_list->the_post();
          $event_list[get_the_ID()] = get_the_title();
        endwhile;
      endif;
      wp_reset_postdata();
      return $event_list;
    }

    if (!function_exists('eventer_get_terms')) {
      /*
	* eventer_get_terms function
	* This function returns all terms of given taxonomy
	*/
      function eventer_get_terms($taxonomy = '')
      {
        if ($taxonomy == '') return;
        $event_terms = array('' => esc_html__('All', 'eventer'));
        $term_args = get_terms($taxonomy);
        if (!is_wp_error($term_args) && !empty($term_args)) {
          foreach ($term_args as $term) {
            $event_terms[$term->term_id] = $term->name;
          }
        }
        return $event_terms;
      }
    }

    if (!function_exists('eventer_make_event_primary_venue')) {
      /*
	* eventer_make_event_primary_venue function
	* This function make venue primary if selected multiple for event
	*/
      function eventer_make_event_primary_venue()
      {
        $status = '';
        if (!wp_verify_nonce($_REQUEST['nonce'], "eventer_make_event_primary_venue_nonce")) {
          exit();
        }
        $eventer_id = $_REQUEST['post_id'];
        $term_id = $_REQUEST['term'];
        $uncheck = $_REQUEST['uncheck'];
        $terms = explode("-", $term_id);

        if (defined('DOING_AJAX') && DOING_AJAX) {
          $old_status = get_post_meta($eventer_id, 'eventer_primary_term', true);
          if ($old_status == $terms[2] && $uncheck == '1') {
            $status = update_post_meta($eventer_id, 'eventer_primary_term', '');
            echo 0;
          } elseif ($uncheck != '1') {
            $status = update_post_meta($eventer_id, 'eventer_primary_term', $terms[2]);
            echo 1;
          }
          echo 'eventer-venue-' . get_post_meta($eventer_id, 'eventer_primary_term', true);

          die();
        } else {
          exit();
        }
      }
      add_action('wp_ajax_eventer_make_event_primary_venue', 'eventer_make_event_primary_venue');
    }

    if (!function_exists('eventer_update_registrant_status')) {
      /*
	* eventer_update_registrant_status function
	* This function update the status of registrant payment
	*/
      function eventer_update_registrant_status()
      {
        $nonce = (isset($_REQUEST['nonce'])) ? $_REQUEST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'eventer_update_registrant_status')) {
          wp_die();
        }
        $registrant_id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
        $registrants = eventer_get_registrant_details('id', $registrant_id);
        $status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : 'woo';
        if ($status == "woo") {

          echo apply_filters('eventer_status_changed_completed', $registrants, 'admin');
          wp_die();
        }

        $user_system = unserialize($registrants->user_system);
        $user_system['email_post'] = "1";

        eventer_update_registrant_details(array('status' => $status), $registrant_id, array("%s", "%s"));
        if ($status == "completed") {
          $new_user_system_restore = (isset($user_system['restore'])) ? $user_system['restore'] : '';
          //if ($new_user_system_restore == 1) {
          $tickets = $registrants->tickets;
          $tickets = unserialize($tickets);
          $time_slot = (isset($user_system['time_slot']) && $user_system['time_slot'] != '') ? $user_system['time_slot'] : '00:00:00';
          $ticket_date = $registrants->eventer_date . ' ' . $time_slot;
          if (!empty($tickets)) {
            $new_tickets = [];
            foreach ($tickets as $ticket_set) {
              $this_ticket = $ticket_set;
              $this_ticket['restored'] = '0';
              $new_tickets[] = $this_ticket;
              $tickets_id = $ticket_set['id'];
              $total_tickets = $ticket_set['number'];
              if (isset($ticket_set['restored']) && $ticket_set['restored'] == '1') {
                eventer_update_date_wise_bookings_table($registrants->eventer, $ticket_date, array(array('id' => $tickets_id, 'number' => $total_tickets)), 3, 1);
              }
            }
            global $wpdb;
            $registration_table = $wpdb->prefix . "eventer_registrant";
            $wpdb->update($registration_table, array('tickets' => serialize($new_tickets)), ['id' => $registrant_id], array('%s'), array('%d'));
          }
          //}
          $user_system['restore'] = 0;
          //$new_user_system = serialize($user_system);
          eventer_pass_email_registration($registrant_id, 4);
          //eventer_update_registrant_details(array('user_system' => $new_user_system), $registrant_id, array("%s", "%s"));
          echo apply_filters('eventer_status_changed_completed', $registrants, 'admin');
        } else {

          $tickets = $registrants->tickets;
          $tickets = unserialize($tickets);
          $restore_status = (isset($user_system['restore'])) ? $user_system['restore'] : '';
          $time_slot = (isset($user_system['time_slot']) && $user_system['time_slot'] != '') ? $user_system['time_slot'] : '00:00:00';
          $ticket_date = $registrants->eventer_date . ' ' . $time_slot;
          if (!empty($tickets)) {
            $new_tickets = [];
            foreach ($tickets as $ticket_set) {
              $this_ticket = $ticket_set;
              $this_ticket['restored'] = '1';
              $new_tickets[] = $this_ticket;
              $tickets_id = $ticket_set['id'];
              $user_system['restore'] = 1;
              if (isset($ticket_set['restored']) && $ticket_set['restored'] == '1') continue;
              //$restore_tickets = serialize($user_system);
              //eventer_update_registrant_details(array('user_system' => $restore_tickets), $registrant_id, array("%s", "%s"));
              $total_tickets = $ticket_set['number'];
              if ($total_tickets <= 0 || $restore_status == 1) continue;
              eventer_update_date_wise_bookings_table($registrants->eventer, $ticket_date, array(array('id' => $tickets_id, 'number' => $total_tickets)), 3, 2);
            }
            global $wpdb;
            $registration_table = $wpdb->prefix . "eventer_registrant";
            $wpdb->update($registration_table, array('tickets' => serialize($new_tickets)), ['id' => $registrant_id], array('%s'), array('%d'));
          }
        }
        echo 'completed';
        wp_die();
      }
      add_action('wp_ajax_eventer_update_registrant_status', 'eventer_update_registrant_status');
    }

    if (!function_exists('eventer_send_tickets_again')) {
      /*
	* eventer_update_registrant_status function
	* This function update the status of registrant payment
	*/
      function eventer_send_tickets_again()
      {
        $nonce = (isset($_REQUEST['nonce'])) ? $_REQUEST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'eventer_send_tickets_again')) {
          wp_die();
        }
        $registrant_id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
        $registrants = eventer_get_registrant_details('id', $registrant_id);
        echo apply_filters('eventer_status_changed_completed', $registrants, 'admin');
        wp_die();
      }
      add_action('wp_ajax_eventer_send_tickets_again', 'eventer_send_tickets_again');
    }



    if (!function_exists('eventer_remove_registrant')) {
      /*
	* eventer_remove_registrant function
	* This function removes entry of registrant from database, whenever user click remove button from dashboard bookings section
	*/
      function eventer_remove_registrant()
      {
        if (!wp_verify_nonce($_REQUEST['nonce'], "eventer_remove_registrant_nonce")) {
          exit();
        }
        $reg_id = $_REQUEST['reg_id'];
        $reg_email = $_REQUEST['reg_email'];
        $new_already_booked = array();
        if ($reg_id != '' && $reg_email != '') {
          global $wpdb;
          $table_name = $wpdb->prefix . "eventer_registrant";
          $reg_details = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $reg_id");
          if ($reg_details) {
            $user_system = unserialize($reg_details->user_system);
            $tickets = $reg_details->tickets;
            $tickets = unserialize($tickets);
            $restore_status = (isset($user_system['restore'])) ? $user_system['restore'] : '';
            $time_slot = (isset($user_system['time_slot']) && $user_system['time_slot'] != '') ? $user_system['time_slot'] : '00:00:00';
            $ticket_date = $reg_details->eventer_date . ' ' . $time_slot;
            if (!empty($tickets)) {
              foreach ($tickets as $ticket_set) {
                $tickets_id = $ticket_set['id'];
                $total_tickets = $ticket_set['number'];
                if (($total_tickets > 0 && isset($ticket_set['restored']) && $ticket_set['restored'] == '0') || ($total_tickets > 0 && !isset($ticket_set['restored']))) {
                  eventer_update_date_wise_bookings_table($reg_details->eventer, $ticket_date, array(array('id' => $tickets_id, 'number' => $total_tickets)), 3, 2);
                }
              }
            }
          }
          $wpdb->delete($table_name, array('id' => $reg_id, 'email' => $reg_email), array('%d', '%s'));
          echo "deleted";
        }
        die();
      }
      add_action('wp_ajax_eventer_remove_registrant', 'eventer_remove_registrant');
    }

    if (!function_exists('eventer_export_bookings_csv')) {
      /*
	* eventer_export_bookings_csv function
	* This function export registrant details to csv file
	*/
      add_action('wp_ajax_eventer_export_bookings_csv', 'eventer_export_bookings_csv');
      function eventer_export_bookings_csv()
      {
        global $wpdb;
        $booking_status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : '';
        $specific_event = (isset($_REQUEST['eventer'])) ? $_REQUEST['eventer'] : '';
        $where = "";
        if ($booking_status != '' && $specific_event != '') {
          $where = "WHERE status = '$booking_status' AND eventer = $specific_event";
        } elseif ($booking_status != '' && $specific_event == '') {
          $where = "WHERE status = '$booking_status'";
        } elseif ($booking_status == '' && $specific_event != '') {
          $where = "WHERE eventer = '$specific_event'";
        }
        $wpdb->show_errors();
        $table_name = $wpdb->prefix . "eventer_registrant";
        $file = 'eventer_booking_csv';
        $export_query = $wpdb->get_results("SELECT * FROM $table_name $where", ARRAY_A);
        if (!$export_query) {
          $Error = $wpdb->print_error();
          die("The following error was found: $Error");
        } else {
          $csv_fields = array();
          $csv_fields[] = 'Registrant ID';
          $csv_fields[] = 'Eventer ID';
          $csv_fields[] = 'Registration Time';
          $csv_fields[] = 'Eventer Date';
          $csv_fields[] = 'Transaction ID';
          $csv_fields[] = 'Registrant User Name';
          $csv_fields[] = 'Registrant Email';
          $csv_fields[] = 'Payment Mode';
          $csv_fields[] = 'Payment Status';
          $csv_fields[] = 'Amount';
          $csv_fields[] = 'User Details';
          $csv_fields[] = 'Tickets';
          $csv_fields[] = 'User ID';
          $csv_fields[] = 'Payment Details';
          $csv_fields[] = 'User Details';
          $output_filename = $file . "_" . date_i18n("Y-m-d_H-i-s") . ".csv";
          $output_handle = @fopen('php://output', 'w');

          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Content-Description: File Transfer');
          header('Content-type: text/csv');
          header('Content-Disposition: attachment; filename=' . $output_filename);
          header('Expires: 0');
          header('Pragma: public');

          fputcsv($output_handle, $csv_fields);
          foreach ($export_query as $Result) {
            $sb = array();
            foreach ($Result as $key => $value) {
              $new_data = @unserialize($value);
              $show_data = ($new_data !== false) ? unserialize($value) : $value;
              if (is_array($show_data) && $key == 'user_details') {
                $user_details = '';
                foreach ($show_data as $data) {
                  $field_val = (isset($data['value'])) ? $data['value'] : '';
                  $field_name = (isset($data['name'])) ? $data['name'] : '';
                  $user_details .= ($field_val != '') ? '{' . $field_name . ': ' . $field_val . '}' : '';
                }
                $show_data = $user_details;
              } elseif (is_array($show_data) && $key == 'user_system') {
                $user_tickets = '';
                foreach ($show_data as $key => $value) {
                  if ($key == 'tickets') {
                    foreach ($value as $nvalue) {
                      foreach ($nvalue as $new_key => $new_val) {
                        $new_val = ($new_key == 'date') ? date_i18n('Y-m-d', $new_val) : $new_val;
                        $new_val = ($new_key == 'event') ? get_the_title($new_val) : $new_val;
                        $user_tickets .= '{' . $new_key . ': ' . $new_val . '}';
                      }
                    }
                  }
                  if ($key == 'services') {
                    foreach ($value as $nvalue) {
                      $user_tickets .= '{' . $nvalue['name'] . '=>' . $nvalue['value'] . '}';
                    }
                  }
                }
                $show_data = $user_tickets;
              } elseif (is_array($show_data) && $key == 'tickets') {
                $user_tickets = '';
                foreach ($show_data as $data) {
                  $tickets_count = (isset($data['number']) && $data['number'] != '') ? $data['number'] : 0;
                  $tickets_name = (isset($data['name']) && $data['name'] != '') ? $data['name'] : 0;
                  $user_tickets .= ($tickets_count != 0) ? '{' . $tickets_name . ': ' . $tickets_count . '}' : '';
                }
                $show_data = $user_tickets;
              } elseif (is_array($show_data) && $key == 'paypal_details') {
                $payment_details = '';
                foreach ($show_data as $key => $value) {
                  $payment_details .= '{' . $key . ': ' . $value . '}';
                }
                $show_data = $payment_details;
              }
              $sb[$key] = $show_data;
            }
            $leadArray = $sb;
            fputcsv($output_handle, $leadArray);
          }
          fclose($output_handle);
        }
        die();
      }
    }

    if (!function_exists('manage_eventer_posts_columns')) {
      /*
	* manage_eventer_posts_columns function
	* Create column in eventer post type for event start and end date
	*/
      add_filter('manage_eventer_posts_columns', 'eventer_set_eventer_column_label');
      function eventer_set_eventer_column_label($columns)
      {
        $columns['edate'] = __('EDate', 'eventer');
        return $columns;
      }
    }

    if (!function_exists('eventer_show_eventer_column_data')) {
      /*
	* eventer_show_eventer_column_data function
	* Update values in event date admin column
	*/
      // Add the data to the custom columns for the eventer post type:
      add_action('manage_eventer_posts_custom_column', 'eventer_show_eventer_column_data', 10, 2);
      function eventer_show_eventer_column_data($column, $post_id)
      {
        switch ($column) {
          case 'edate':
            $event_start_date     = get_post_meta($post_id, 'eventer_event_start_dt', true);
            $event_end_date       = get_post_meta($post_id, 'eventer_event_end_dt', true);
            $event_end_date       = ($event_end_date != '') ? $event_end_date : $event_start_date;
            $event_start_dt_str   = strtotime($event_start_date);
            $event_end_dt_str     = strtotime($event_end_date);
            echo date_i18n('Y-m-d H:i', $event_start_dt_str) . '<br><abbr title="">' . date_i18n('Y-m-d H:i', $event_end_dt_str) . '</abbr>';
            break;
        }
      }
    }

    if (!function_exists('eventer_get_booked_tickets')) {
      /*
	* eventer_get_booked_tickets function
	* Show status of event tickets booked status
	*/
      function eventer_get_booked_tickets()
      {
        $eventer_id = ($_REQUEST['eventer_id']) ? $_REQUEST['eventer_id'] : '';
        $booked_date = ($_REQUEST['booked_date']) ? $_REQUEST['booked_date'] : '';
        $booked_time = (isset($_REQUEST['booked_time']) && $_REQUEST['booked_time']) ? $_REQUEST['booked_time'] : '';
        $original_event = eventer_wpml_original_post_id($eventer_id);
        $default_featured = get_post_meta($original_event, 'eventer_event_featured', true);
        $tickets = get_post_meta($original_event, 'eventer_tickets', true);
        $updated_tickets_new = eventer_update_date_wise_bookings_table($eventer_id, $booked_date . ' ' . $booked_time, array());
        $updated_tickets_new = eventer_update_date_wise_bookings_table($eventer_id, $booked_date . ' ' . $booked_time, array(), 2);
        $featured_events = get_option('eventer_all_featured_events');
        $featured_events = (!empty($featured_events)) ? $featured_events : array();
        $saved_data = (isset($featured_events[$booked_date])) ? $featured_events[$booked_date] : array();
        $saved_data = array_unique($saved_data);
        if ($updated_tickets_new[0]['featured'] == 1) {
          $saved_data[] = $eventer_id;
        } else {
          $saved_data = array_diff($saved_data, (is_array($eventer_id) ? $eventer_id : array($eventer_id)));
        }
        $featured_events[$booked_date] = $saved_data;
        $title = [];
        update_option('eventer_all_featured_events', $featured_events);
        if ($updated_tickets_new) {
          foreach ($updated_tickets_new as $new) {
            $locale_title = isset($new['cust_val1']) ? $new['cust_val1'] : [];
            $locale_title = json_decode($locale_title, true);
            $title[$new['ticket_id']] = $locale_title[EVENTER__LANGUAGE_CODE];
          }
        }

        echo json_encode(array('tickets' => $updated_tickets_new, 'title' => $title, 'featured' => $default_featured));
        die();
      }
      add_action('wp_ajax_eventer_get_booked_tickets', 'eventer_get_booked_tickets');
    }

    function eventer_update_date_wise_bookings_table($event, $date, $tickets = array(), $insert = 1, $update = 1, $front = false)
    {
      if (is_array($event) || !is_numeric($event) || $date == '') return;
      $original_event = eventer_wpml_original_post_id($event);
      $booked_eventer_tickets = (!empty(get_post_meta($original_event, 'specific_eventer_tickets', true))) ? get_post_meta($original_event, 'specific_eventer_tickets', true) : get_post_meta($original_event, 'eventer_tickets', true);
      $only_date = date_i18n('Y-m-d', strtotime($date));
      if (!empty($tickets) && $insert == 2) {
        $tickets_created = $tickets;
      } elseif (is_array($booked_eventer_tickets) && array_key_exists($only_date, $booked_eventer_tickets)) {
        $tickets_created = $booked_eventer_tickets[$only_date];
      } else {
        $tickets_created = get_post_meta($original_event, 'eventer_tickets', true);
      }
      $booked_date = $date;
      $locale = EVENTER__LANGUAGE_CODE;
      $woo_payment = eventer_get_settings('eventer_enable_woocommerce_ticketing');
      $ticket_type = ($woo_payment == 'on') ? 'woo-ticket' : 'ticket';
      global $wpdb;
      $table_name_tickets = $wpdb->prefix . "eventer_tickets";
      $saved_ticket = $wpdb->get_results("SELECT * FROM $table_name_tickets WHERE event = $original_event AND date = '$booked_date' AND type = '$ticket_type'", ARRAY_A);
      $saved_ticket = (!empty($tickets) && $insert == 2) ? array() : $saved_ticket;
      if (empty($saved_ticket) && $insert <= 2 && $tickets_created) {
        foreach ($tickets_created as $ticket_new) {
          $ticket_pid = (isset($ticket_new['pid'])) ? $ticket_new['pid'] : wp_rand(100, 999999999999);
          $woo_payment = eventer_get_settings('eventer_enable_woocommerce_ticketing');
          if ($woo_payment == 'on') {
            $dynamic_ids = intval($ticket_pid) + intval($original_event);
          } else {
            $dynamic_ids = (isset($ticket_new['id']) && $ticket_new['id'] != '') ? $ticket_new['id'] : intval($ticket_pid) + intval($original_event);
          }

          $ticket_name = (isset($ticket_new['name'])) ? $ticket_new['name'] : '';
          if ($ticket_name == '') continue;
          $ticket_number = (isset($ticket_new['number'])) ? $ticket_new['number'] : '';

          $ticket_price = (isset($ticket_new['price'])) ? $ticket_new['price'] : 0;
          $ticket_restrict = (isset($ticket_new['restrict'])) ? $ticket_new['restrict'] : '';
          $ticket_featured = (isset($ticket_new['featured'])) ? $ticket_new['featured'] : '';
          $ticket_badge = (isset($ticket_new['badge'])) ? $ticket_new['badge'] : '';
          $ticket_enabled = (isset($ticket_new['enabled'])) ? $ticket_new['enabled'] : '';
          $escaped_title = esc_sql($ticket_name);
          $saved_ticket_verify = $wpdb->get_results("SELECT * FROM $table_name_tickets WHERE event = $original_event AND date = '$booked_date' AND type = '$ticket_type' AND dynamic = '$dynamic_ids' AND name = '$escaped_title'", ARRAY_A);
          if (empty($saved_ticket_verify)) {
            $wpdb->query($wpdb->prepare("INSERT INTO $table_name_tickets ( dynamic, pid, event, name, date, type, tickets, price, restricts, featured, label, enabled, `cust_val1` ) VALUES ( %d, %d, %d, %s, %s, %s, %s, %f, %s, %s, %s, %s, %s ) ", $dynamic_ids, $ticket_pid, $original_event, stripslashes($ticket_name), $booked_date, $ticket_type, $ticket_number, $ticket_price, $ticket_restrict, $ticket_featured, $ticket_badge, $ticket_enabled, json_encode([$locale => stripslashes($ticket_name)])));
          }
        }
      } else if ($insert == 2) {
        $saved_ticket = $wpdb->get_results("SELECT * FROM $table_name_tickets WHERE event = $original_event AND date = '$booked_date' AND type = '$ticket_type' ORDER BY ticket_id ASC", ARRAY_A);
        return $saved_ticket;
      } else if (!empty($tickets)) {
        foreach ($tickets as $save_ticket) {
          $dynamic_ids = (isset($save_ticket['id']) && $save_ticket['id'] != '') ? $save_ticket['id'] : '';
          $validate_ticket_name = (isset($save_ticket['name'])) ? $save_ticket['name'] : '';
          if ($dynamic_ids != '') {
            $saved_ticket = $wpdb->get_row("SELECT * FROM $table_name_tickets WHERE event = $original_event AND date = '$booked_date' AND dynamic = $dynamic_ids AND type = '$ticket_type'");
          } else {
            $saved_ticket = $wpdb->get_row("SELECT * FROM $table_name_tickets WHERE event = $original_event AND date = '$booked_date' AND type = '$ticket_type'");
          }

          if ($saved_ticket) {

            $ticket_name = (isset($save_ticket['name'])) ? $save_ticket['name'] : $saved_ticket->name;
            //if($ticket_name=='') continue;
            $check_unique_ids[] = $dynamic_ids;
            $remaining_tickets = $saved_ticket->tickets;
            if ($update == 1) {
              $ticket_number = (isset($save_ticket['number']) && $remaining_tickets >= $save_ticket['number']) ? intval($remaining_tickets) - intval($save_ticket['number']) : $saved_ticket->tickets;
            } elseif ($update == 3) {
              $ticket_number = (isset($save_ticket['number'])) ? $save_ticket['number'] : $saved_ticket->tickets;
            } else {
              $ticket_number = (isset($save_ticket['number'])) ? intval($remaining_tickets) + intval($save_ticket['number']) : $saved_ticket->tickets;
            }

            $ticket_pid = (isset($save_ticket['pid'])) ? $save_ticket['pid'] : $saved_ticket->pid;
            $ticket_price = (isset($save_ticket['price'])) ? $save_ticket['price'] : $saved_ticket->price;
            $ticket_restrict = (isset($save_ticket['restrict'])) ? $save_ticket['restrict'] : $saved_ticket->restricts;
            $ticket_restrict = (empty($ticket_restrict)) ? 0 : $ticket_restrict;
            $ticket_featured = (isset($save_ticket['featured'])) ? $save_ticket['featured'] : $saved_ticket->featured;
            $ticket_featured = (empty($ticket_featured)) ? 0 : $ticket_featured;
            $ticket_badge = (isset($save_ticket['badge'])) ? $save_ticket['badge'] : $saved_ticket->label;
            $ticket_enabled = (isset($save_ticket['enabled'])) ? $save_ticket['enabled'] : $saved_ticket->enabled;
            $locale_title = $saved_ticket->cust_val1 ? json_decode($saved_ticket->cust_val1, true) : [];

            $locale_title[$locale] = stripslashes($ticket_name);
            $locale_title = json_encode($locale_title);
            if ($front) {
              $wpdb->update($table_name_tickets, array('tickets' => $ticket_number), array('event' => $original_event, 'date' => $booked_date, 'dynamic' => $dynamic_ids, 'type' => $ticket_type), array('%s'), array('%d', '%s', '%d', '%s'));
            } else {
              $wpdb->update($table_name_tickets, array('type' => $ticket_type, 'tickets' => $ticket_number, 'price' => $ticket_price, 'restricts' => $ticket_restrict, 'featured' => $ticket_featured, 'label' => $ticket_badge, 'enabled' => $ticket_enabled, 'cust_val1' => $locale_title), array('event' => $original_event, 'date' => $booked_date, 'dynamic' => $dynamic_ids, 'type' => $ticket_type), array('%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s'), array('%d', '%s', '%d', '%s'));
            }
          }
        }
      }
    }
    if (!function_exists('eventer_update_booked_tickets')) {
      /*
	* eventer_update_booked_tickets function
	* Show booked ticket information in event post type section while editing any event
	*/
      function eventer_update_booked_tickets()
      {
        $eventer_id = (isset($_REQUEST['eventer_id'])) ? $_REQUEST['eventer_id'] : '';
        $booked_date = (isset($_REQUEST['booked_date'])) ? $_REQUEST['booked_date'] : '';
        $eventer_position = (isset($_REQUEST['position'])) ? $_REQUEST['position'] : '';
        if ($eventer_position == 'reset') {
          global $wpdb;
          $table_name_tickets = $wpdb->prefix . "eventer_tickets";
          $wpdb->delete($table_name_tickets, array('event' => $eventer_id));
          wp_die();
        }
        $time_slot = (isset($_REQUEST['time']) && $_REQUEST['time'] != 'undefined' && $_REQUEST['time'] != '') ? $_REQUEST['time'] : '00:00:00';
        $booked_date = $booked_date . ' ' . $time_slot;
        $original_event = eventer_wpml_original_post_id($eventer_id);
        $tickets_new = (isset($_REQUEST['updated_detail'])) ? $_REQUEST['updated_detail'] : '';
        $woo_payment = eventer_get_settings('eventer_enable_woocommerce_ticketing');
        $setup_tickets_new = ($woo_payment == 'on') ? array() : $tickets_new;
        if (!empty($tickets_new) && empty($setup_tickets_new)) {
          foreach ($tickets_new as $ticket_get) {
            if (isset($ticket_get['pid']) && $ticket_get['pid'] == '' && isset($ticket_get['name']) &&  $ticket_get['name'] != '') {
              $product_arg = array('post_type' => 'product', 'post_title' => $ticket_get['name'], 'post_status' => 'publish');
              $product_id = wp_insert_post($product_arg);
              if (function_exists('icl_object_id') && class_exists('SitePress') && function_exists('wpml_add_translatable_content')) {
                wpml_add_translatable_content('post_product', $product_id, EVENTER__LANGUAGE_CODE);
              }
              $ticket_backend_generate = intval($product_id) + intval($eventer_id);
              wp_set_object_terms($product_id, 'eventer', 'product_cat');
              update_post_meta($product_id, '_regular_price', (float) $ticket_get['price']);
              update_post_meta($product_id, '_price', (float) $ticket_get['price']);
              update_post_meta($product_id, '_virtual', 'yes');
              $ticket_get['id'] = $ticket_backend_generate;
              $ticket_get['pid'] = $product_id;
              eventer_update_date_wise_bookings_table($eventer_id, $booked_date . ' ' . $time_slot, array($ticket_get), 2);
            }
            $setup_tickets_new[] = $ticket_get;
          }
        }
        eventer_update_date_wise_bookings_table($eventer_id, $booked_date, $setup_tickets_new, 1, 3);
        wp_die();
      }
      add_action('wp_ajax_eventer_update_booked_tickets', 'eventer_update_booked_tickets');
    }

    function eventer_get_term_details()
    {
      $term_id = (isset($_REQUEST['term_id'])) ? $_REQUEST['term_id'] : '';
      $taxonomy = (isset($_REQUEST['taxonomy'])) ? $_REQUEST['taxonomy'] : '';
      if ($taxonomy == 'list:eventer-venue') {
        $location = get_term_meta($term_id, 'venue_address', true);
        echo '<div id="misc-publishing-actions" class="eventer-admin-term-metas-show"><div class="">' . esc_html__('Location', 'eventer') . ': <span id="post-status-display">' . esc_attr($location) . '</span></div>';
      } else {
        $organizer_email = get_term_meta($term_id, 'organizer_email', true);
        $organizer_phone = get_term_meta($term_id, 'organizer_phone', true);
        $organizer_website = get_term_meta($term_id, 'organizer_website', true);
        echo '<div id="misc-publishing-actions" class="eventer-admin-term-metas-show"><div class="">' . esc_html__('Email', 'eventer') . ': <span id="post-status-display">' . esc_attr($organizer_email) . '</span></div><div class="">' . esc_html__('Phone', 'eventer') . ': <span id="post-status-display">' . esc_attr($organizer_phone) . '</span></div><div class="">' . esc_html__('Website', 'eventer') . ': <span id="post-status-display">' . esc_url($organizer_website) . '</span></div></div>';
      }
      wp_die();
    }
    add_action('wp_ajax_eventer_get_term_details', 'eventer_get_term_details');

    function eventer_woo_get_return_url($order = null)
    {
      if ($order) {
        $return_url = $order->get_checkout_order_received_url();
      } else {
        $return_url = wc_get_endpoint_url('order-received', '', wc_get_page_permalink('checkout'));
      }
      if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
        $return_url = str_replace('http:', 'https:', $return_url);
      }
      return apply_filters('woocommerce_get_return_url', $return_url);
    }
    function eventer_status_transitions($ID, $post)
    {
      $mail_status = get_post_meta($ID, 'eventer_rest_email_status', true);
      if ($mail_status == '' || $mail_status == 'publish') return;
      wp_set_current_user(1);
      $request = new WP_REST_Request('POST', '/imithemes/email');
      $request->set_param('event', $ID);
      $response = rest_do_request($request);
    }
    add_action('publish_eventer',  'eventer_status_transitions', 999, 2);

    add_action('wp_ajax_nopriv_eventer_dynamic_ticket_area', 'eventer_dynamic_ticket_area');
    add_action('wp_ajax_eventer_export_registrants', 'eventer_export_registrants');
    function eventer_export_registrants()
    {
      global $wpdb;
      $booking_date = (isset($_REQUEST['date'])) ? $_REQUEST['date'] : '';
      $booking_status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : '';
      $specific_event = (isset($_REQUEST['eventer'])) ? $_REQUEST['eventer'] : '';
      $event_all = (isset($_REQUEST['eventer_all'])) ? $_REQUEST['eventer_all'] : '';
      $woocommerce_events = eventer_get_settings('eventer_enable_woocommerce_ticketing');
      $where = '';
      if ($woocommerce_events != 'on' && $event_all == '' && $specific_event != '') {
        $where = "WHERE eventer = '$specific_event' AND eventer_date = '$booking_date'";
      }

      $wpdb->show_errors();
      $table_name = $wpdb->prefix . "eventer_registrant";
      $file = 'eventer-registrant-csv';
      $export_query = $wpdb->get_results("SELECT * FROM $table_name $where", ARRAY_A);
      if (!$export_query) {
        $Error = $wpdb->print_error();
        die("The following error was found: $Error");
      } else {
        $csv_fields = array();
        $csv_fields[] = 'Status';
        $output_filename = $file . "_" . date_i18n("Y-m-d_H-i-s") . ".csv";
        $output_handle = @fopen('php://output', 'w');

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=' . $output_filename);
        header('Expires: 0');
        header('Pragma: public');
        $leadArray = $new_rows = $user_data_set = array();
        $counter = 1;
        if ($woocommerce_events != 'on') {
          $csv_fields[] = 'Event Title';
          foreach ($export_query as $Result) {
            if ($booking_status != '') {
              if ($booking_status != $Result['status']) continue;
            }
            if ($specific_event != '') {
              if ($specific_event != $Result['eventer']) continue;
            }
            $user_info = $Result['user_details'];
            $user_data = unserialize($user_info);
            $user_system = $Result['user_system'];
            $user_system = unserialize($user_system);
            $registrants = (isset($user_system['registrants'])) ? $user_system['registrants'] : array();
            $time_slot = (isset($user_system['slot_title'])) ? $user_system['slot_title'] : '';
            $tickets_booked = $Result['tickets'];
            $tickets_booked = unserialize($tickets_booked);
            $new_booked = [];
            if ($tickets_booked) {
              foreach ($tickets_booked as $tbook) {
                if (isset($tbook['number']) && $tbook['number'] > 0) {
                  $new_booked[$tbook['name']] = $tbook['number'];
                }
              }
            }
            $tickets_booked = $new_booked;
            $data_set = array();

            $ticket_showing = '';
            if (!empty($registrants)) {
              foreach ($registrants as $key => $value) {
                if (!array_key_exists($key, $tickets_booked)) {
                  continue;
                }
                if (!empty($value)) {
                  $ticket_setting = '';
                  $ticket_setting = $key . '(';
                  foreach ($value as $regs) {
                    $name = $regs['name'];
                    $email = $regs['email'];
                    $set_arr = array('status' => $Result['status'], 'title' => get_the_title($Result['eventer']), 'id' => $Result['id'], 'event_date' => $Result['eventer_date'], 'event_slot' => $time_slot, 'registration_date' => $Result['ctime'], 'username' => $Result['username'], 'email' => $Result['email'], 'amount' => $Result['amount'], 'registrant_ticket' => $key, 'count' => $tickets_booked[$key], 'registrant_name' => $name, 'registrant_email' => $email);
                    if (!empty($user_data)) {

                      foreach ($user_data as $data) {
                        if (strpos('quantity_tkt', $data['name']) !== false) continue;
                        $set_arr[$data['name']] = $data['value'];
                      }
                      //$user_data_set[] = $data_set;
                    }
                    $leadArray[] = $set_arr;
                  }
                }
              }
            } else {
              $set_arr = array('status' => $Result['status'], 'title' => get_the_title($Result['eventer']), 'id' => $Result['id'], 'event_date' => $Result['eventer_date'], 'event_slot' => $time_slot, 'registration_date' => $Result['ctime'], 'username' => $Result['username'], 'email' => $Result['email'], 'amount' => $Result['amount'], 'registrant_ticket' => $key, 'registrant_name' => $name, 'registrant_email' => $email);
              $leadArray[] = $set_arr;
            }
          }
          foreach ($leadArray as $rows) {
            $new_rows[] = $rows;
          }
          $set = 0;
          foreach ($new_rows as $row_set) {
            if ($set == 0 && $counter == 1) {
              $counter++;
              $label = [];
              foreach ($row_set as $key => $value) {
                $label[] = $key;
              }
              fputcsv($output_handle, $label);
            }
            fputcsv($output_handle, $row_set);
            $set++;
          }
        } else {
          $csv_fields[] = 'order id';
          $csv_fields[] = 'Event name';
          $csv_fields[] = 'Event Date';
          $csv_fields[] = 'Mode';
          $csv_fields[] = 'Name';
          $csv_fields[] = 'Email';
          $csv_fields[] = 'Phone';
          $csv_fields[] = 'Product type';
          $csv_fields[] = 'Ticket name';
          $csv_fields[] = 'Ticket Quantity';
          $csv_fields[] = 'Registrants';
          fputcsv($output_handle, $csv_fields);
          foreach ($export_query as $Result) {
            if ($booking_status != '') {
              if ($booking_status != $Result['status']) continue;
            }
            //if(get_post_type($Result['eventer'])=='eventer') continue;
            $woo_order = $Result['eventer'];
            $order = wc_get_order($woo_order);
            if (!$order) continue;
            $user_system = $Result['user_system'];
            $user_system = unserialize($user_system);
            $registrants = (isset($user_system['tickets'])) ? $user_system['tickets'] : array();


            foreach ($order->get_items() as $item_key => $item_values) :
              $data_set = array();
              $item_data = $item_values->get_data();
              $item_id = $item_values->get_id();
              $product_name = $item_data['name'];
              $product_id = $item_data['product_id'];
              $quantity = $item_data['quantity'];
              $order_event_url = wc_get_order_item_meta($item_id, 'Event URL', true);
              $eventer_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
              $eventer_date = wc_get_order_item_meta($item_id, '_wceventer_date', true);
              if ($specific_event != '' && $eventer_id != $specific_event) continue;
              $ticket_showing = '';
              if ($registrants) {

                foreach ($registrants as $regs) {
                  if ($regs['ticket'] != $product_name) continue;
                  $get_registrant = (isset($regs['registrants'])) ? $regs['registrants'] : array();
                  $event_id = $regs['event'];
                  $get_event_title = get_the_title($event_id) . '-' . $regs['ticket'];
                  if ($get_registrant) {
                    $ticket_setting = '';
                    $ticket_setting .= $get_event_title . '(';
                    foreach ($get_registrant as $set_registrant) {
                      $reg_name = $set_registrant['name'];
                      $reg_email = $set_registrant['email'];
                      $ticket_setting .= '[' . $reg_name . '=>' . $reg_email . ']';
                    }
                    $ticket_setting .= ')';
                  }
                  $ticket_showing .= $ticket_setting;
                }
              }
              $data_set['status'] = $order->get_status();
              $data_set['order id'] = $woo_order;
              $data_set['Event name'] = get_the_title($eventer_id);
              $data_set['Event Date'] = date_i18n(get_option('date_format'), strtotime($eventer_date));
              $data_set['Mode'] = $order->get_payment_method_title();
              $data_set['Name'] = get_post_meta($woo_order, '_billing_first_name', true) . ' ' . get_post_meta($woo_order, '_billing_last_name', true);
              $data_set['Email'] = get_post_meta($woo_order, '_billing_email', true);
              $data_set['Phone'] = get_post_meta($woo_order, '_billing_phone', true);
              $data_set['Product Type'] = wc_get_order_item_meta($item_id, '_eventer_product', true);
              $data_set['Ticket name'] = $product_name;
              $data_set['Ticket Quantity'] = $quantity;
              $data_set[] = $ticket_showing;
              fputcsv($output_handle, $data_set);
            endforeach;
          }
        }

        fclose($output_handle);
      }
      die();
    }

    function eventer_checkin_process_ticket()
    {
      $event_id = (isset($_REQUEST['event'])) ? $_REQUEST['event'] : '';
      $msg = $name = $email = $ticket_info = '';
      $event_date = (isset($_REQUEST['date'])) ? $_REQUEST['date'] : '';
      $ticket_id = (isset($_REQUEST['ticket'])) ? $_REQUEST['ticket'] : '';
      $woocommerce_events = eventer_get_settings('eventer_enable_woocommerce_ticketing');
      $registrants = eventer_get_registrant_details('id', $ticket_id);
      $name = $registrants->username;
      $email = $registrants->email;
      if ($woocommerce_events == 'on') {
        $tickets_updated = array();
        $ticket_exist = $date_verify = $proceed_further = '';
        $user_system = unserialize($registrants->user_system);
        $tickets = (isset($user_system['tickets'])) ? $user_system['tickets'] : array();
        if (!empty($tickets)) {
          foreach ($tickets as $ticket) {
            $check_checkin_status = (isset($ticket['checkin'])) ? $ticket['checkin'] : '';
            $ticket['checkin'] = $ticket['checkin_date'] = '';
            if ($event_id == $ticket['event']) {
              $ticket_exist = '1';
            }
            if ($event_date == date_i18n('Y-m-d', $ticket['date'])) {
              $date_verify = '1';
            }
            if ($ticket_exist != '' && $date_verify != '') {
              $proceed_further = '1';
              $ticket['checkin'] = '1';
              $ticket['checkin_date'] = date_i18n('Y-m-d H:i:s');
              $tickets_updated[] = $ticket;
            }
          }
          if ($proceed_further != '' && $check_checkin_status == '') {
            $user_system['tickets'] = $tickets_updated;
            eventer_update_registrant_details(array('user_system' => serialize($user_system)), $ticket_id, array("%s", "%s"));
            $msg = "Successfully check-in";
          } elseif ($check_checkin_status != '') {
            $msg = "This ticket is already checked in";
          }
          $ticket_info = '<div class="form-style-2">
                        <div class="form-style-2-heading">Ticket Information</div>
                        <label><span>Name </span>' . $name . '</label>
                        <label><span>Email </span>' . $email . '</label>
                    </div>';
        } else {
          $msg = "It seems the ticket is not related to the details you submiited above.";
        }
      } else {
        if ($event_id == $registrants->eventer && $event_date == $registrants->eventer_date) {
          $user_system = unserialize($registrants->user_system);
          if (isset($user_system['checkin']) && $user_system['checkin'] == '1') {
            $msg = "This ticket is already checked in";
          } else {
            $user_system['checkin'] = "1";
            $user_system['checkin_date'] = date_i18n('Y-m-d H:i:s');
            eventer_update_registrant_details(array('user_system' => serialize($user_system)), $ticket_id, array("%s", "%s"));
            $msg = "Successfully check-in";
          }
          $ticket_info = '<div class="form-style-2">
                        <div class="form-style-2-heading">Ticket Information</div>
                        <label><span>Name </span>' . $name . '</label>
                        <label><span>Email </span>' . $email . '</label>
                    </div>';
        } else {
          $msg = "It seems the ticket is not related to the details you submiited above.";
        }
      }

      wp_send_json(array('msg' => $msg, 'ticket' => $ticket_info));
      wp_die();
    }
    add_action('wp_ajax_eventer_checkin_process_ticket', 'eventer_checkin_process_ticket');
    function eventer_create_page_for_shortcode_preview()
    {
      $preview_id = get_option("eventer_shortcode_preview");
      $preview_page_switch = eventer_get_settings('eventer_shortcode_preview_page');
      if (get_post_type($preview_id) == 'page' && get_post_status($preview_id) == 'publish') return;
      $shortcode_preview = array(
        'post_title'    => wp_strip_all_tags('Eventer Shortcode Preview Page'),
        'post_content'  => 'This page is created to show preview of shortcode that you generated from settings.',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type' => 'page'
      );
      if ($preview_page_switch != 'on') {
        // Insert the post into the database
        $preview_id = wp_insert_post($shortcode_preview);
        update_option("eventer_shortcode_preview", $preview_id);
      }
    }
    add_action('admin_init', 'eventer_create_page_for_shortcode_preview');

    function eventer_coupon_refresh()
    {
      $coupons = (isset($_REQUEST['coupons'])) ? $_REQUEST['coupons'] : array();
      if ($coupons) {
        global $wpdb;
        $eventer_coupon_table = $wpdb->prefix . "eventer_coupons";
        foreach ($coupons as $coupon) {
          $coupon_id = (isset($coupon['id'])) ? $coupon['id'] : '';
          $coupon_title = (isset($coupon['title'])) ? $coupon['title'] : '';
          $coupon_code = (isset($coupon['code'])) ? $coupon['code'] : '';
          $coupon_amount = (isset($coupon['amount'])) ? $coupon['amount'] : '';
          $coupon_validity = (isset($coupon['validity'])) ? $coupon['validity'] : '';
          $coupon_status = (isset($coupon['status'])) ? $coupon['status'] : '';
          $coupon_remove = (isset($coupon['remove'])) ? $coupon['remove'] : '';
          if ($coupon_id == '' && $coupon_title != '' && $coupon_amount != '') {
            $wpdb->query($wpdb->prepare("INSERT INTO $eventer_coupon_table ( coupon_name, coupon_code, discounted, valid_till, coupon_status) VALUES ( %s, %s, %s, %s, %d) ", $coupon_title, $coupon_code, $coupon_amount, $coupon_validity, $coupon_status));
          } elseif ($coupon_remove == 1 && $coupon_id != '') {
            $wpdb->delete($eventer_coupon_table, array('id' => $coupon_id));
          } elseif ($coupon_id != '' && $coupon_title != '' && $coupon_amount != '') {
            $wpdb->update($eventer_coupon_table, array('coupon_name' => $coupon_title, 'coupon_code' => $coupon_code, 'discounted' => $coupon_amount, 'valid_till' => $coupon_validity, 'coupon_status' => $coupon_status), array('id' => $coupon_id), array('%s', '%s', '%s', '%s', '%d'), array('%d'));
          }
        }
      }
      echo json_encode($wpdb->get_results("SELECT * FROM $eventer_coupon_table"));
      wp_die();
    }
    add_action('wp_ajax_eventer_coupon_refresh', 'eventer_coupon_refresh');
    function eventer_set_android_app_key()
    {
      $get_key = get_option('eventer-android-app-api-key');
      if ($get_key == '') {
        $dyn_val = wp_rand(100, 1000000);
        $divis = $dyn_val * 2648;
        update_option('eventer-android-app-api-key', $divis);
      }
    }
    add_action('admin_init', 'eventer_set_android_app_key');

    function eventer_delete_bookings()
    {
      $bookings = $_REQUEST['bookings'];
      global $wpdb;
      $table_name = $wpdb->prefix . "eventer_registrant";
      if ($bookings) {
        foreach ($bookings as $id) {
          $wpdb->delete($table_name, array('id' => $id), array('%d'));
        }
      }
      echo "success";
      wp_die();
    }
    add_action('wp_ajax_eventer_delete_bookings', 'eventer_delete_bookings');

    function eventer_restore_unsuccessful_tickets()
    {
      global $wpdb;
      $prev_date = date_i18n('Y-m-d H:i:s', strtotime('-1 hour', strtotime(date_i18n('Y-m-d H:i:s'))));
      $next_date = date_i18n('Y-m-d H:i:s', strtotime('-1 day', strtotime(date_i18n('Y-m-d H:i:s'))));
      $registration_table = $wpdb->prefix . "eventer_registrant";
      $saved_ticket = $wpdb->get_results("SELECT * FROM $registration_table WHERE `status` = 'Pending' AND `ctime` <= '$prev_date' AND `ctime` >= '$next_date'", ARRAY_A);
      if ($saved_ticket) {

        foreach ($saved_ticket as $reg) {
          $event = $reg['eventer'];
          $tickets = unserialize($reg['tickets']);
          if ($tickets) {
            $new_tickets = [];
            $user_details = unserialize($reg['user_details']);
            $time_slot = (isset($user_details['time_slot'])) ? $user_details['time_slot'] : '00:00:00';
            $event_date_booked = $reg['eventer_date'];
            foreach ($tickets as $ticket) {
              $set_ticket = $ticket;
              $set_ticket['restored'] = '1';
              $new_tickets[] = $set_ticket;
              if ($ticket['number'] <= 0 || isset($ticket['restored'])) continue;
              $name = $ticket['name'];
              $dynamic = $ticket['id'];
              $table_name_tickets = $wpdb->prefix . "eventer_tickets";
              $get_tickets = $wpdb->get_row("SELECT `tickets` from $table_name_tickets WHERE `name` = '$name' AND `dynamic` = $dynamic");
              $total_tickets = $get_tickets->tickets + $ticket['number'];
              $wpdb->update($table_name_tickets, array('tickets' => $total_tickets), ['date' => $event_date_booked . ' ' . $time_slot, 'dynamic' => $dynamic], array('%s'), array('%s', '%d'));
            }
            $wpdb->update($registration_table, array('tickets' => serialize($new_tickets)), ['id' => $reg['id']], array('%s'), array('%d'));
          }
        }
      }
    }
    eventer_restore_unsuccessful_tickets();
