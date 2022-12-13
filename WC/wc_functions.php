<?php
if (!function_exists('wceventer_enqueue_scripts')) {
  function wceventer_enqueue_scripts()
  {
    $theme_info = wp_get_theme();
    wp_enqueue_script('eventer-woocommerce-scripts', EVENTER__PLUGIN_URL . 'WC/wc_scripts.js', array(), $theme_info->get('Version'), false);
  }
  add_action('wp_enqueue_scripts', 'wceventer_enqueue_scripts');
}
function eventer_add_product_to_cart()
{
  global $woocommerce;
  $product_id = (isset($_REQUEST['product'])) ? $_REQUEST['product'] : '';
  if (!has_term('eventer', 'product_cat', $product_id)) return;
  if (get_post_type($product_id) != 'product') wp_die();
  $tickets = (isset($_REQUEST['tickets'])) ? $_REQUEST['tickets'] : '';
  $eventer_id = (isset($_REQUEST['ticket_id'])) ? $_REQUEST['ticket_id'] : '';
  $event_date = (isset($_REQUEST['event_date'])) ? $_REQUEST['event_date'] : '';
  $event_date_multi = (isset($_REQUEST['event_multi'])) ? $_REQUEST['event_multi'] : '';
  $event_time = (isset($_REQUEST['event_time'])) ? $_REQUEST['event_time'] : '';
  $event_time_slot = (isset($_REQUEST['event_slot'])) ? $_REQUEST['event_slot'] : '';
  $event_time = ($event_time_slot != '' && $event_time_slot != '00:00:00') ? date_i18n(get_option('time_format'), strtotime($event_time_slot)) : $event_time;
  $event_time_slot_title = (isset($_REQUEST['event_slot_title'])) ? $_REQUEST['event_slot_title'] : '';
  $event_allday = (isset($_REQUEST['event_allday'])) ? $_REQUEST['event_allday'] : '';
  $event_url = (isset($_REQUEST['event_url'])) ? $_REQUEST['event_url'] : '';
  $ticket_price = (isset($_REQUEST['ticket_price'])) ? $_REQUEST['ticket_price'] : '';
  $cart_item_data = array('wceventer_name' => apply_filters('eventer_raw_event_title', '', $eventer_id), 'wceventer_id' => $eventer_id, 'wceventer_date' => $event_date, 'wceventer_time' => $event_time, 'wceventer_url' => $event_url, 'eventer_custom_price' => $ticket_price, 'wceventer_product' => 'ticket', 'wceventer_allday' => $event_allday, 'wceventer_slot' => $event_time_slot, 'wceventer_slot_title' => $event_time_slot_title, 'wceventer_multi' => $event_date_multi);
  foreach ($woocommerce->cart->get_cart() as $key => $item) {
    $item_id = $item['wceventer_id'];
    $cart_product_id = $item['product_id']; // the product ID
    if ($eventer_id == $item_id && $product_id == $cart_product_id) {
      $woocommerce->cart->remove_cart_item($key);
    }
  }
  //$cart_item_data = array('price' => $_REQUEST['product_with_services_cost']);
  WC()->cart->add_to_cart($product_id, $tickets, '', array(), $cart_item_data);
  wp_die();
}
add_action('wp_ajax_eventer_add_product_to_cart', 'eventer_add_product_to_cart');
add_action('wp_ajax_nopriv_eventer_add_product_to_cart', 'eventer_add_product_to_cart');

add_action('woocommerce_before_calculate_totals', 'eventer_add_custom_price');

function eventer_add_custom_price($cart)
{
  foreach ($cart->cart_contents as $key => $value) {
    if (isset($value['_eventer_custom_title']) && $value['_eventer_custom_title'] != '') {
      $value['data']->set_name($value['_eventer_custom_title']);
    }
    if (!isset($value['eventer_custom_price'])) continue;
    $value['data']->set_price((!isset($value['eventer_custom_price'])) ? $value['price'] : $value['eventer_custom_price']);
  }
}

function eventer_custom_pre_get_posts_query($q)
{
  $tax_query = (array) $q->get('tax_query');
  $tax_query[] = array(
    'taxonomy' => 'product_cat',
    'field' => 'slug',
    'terms' => array('eventer', 'eventer_services'), // Don't display products in the eventer category on the shop page.
    'operator' => 'NOT IN'
  );
  if (in_array($q->get('post_type'), array('product'))) {
    $q->set('tax_query', $tax_query);
  }
}
//add_action( 'woocommerce_product_query', 'eventer_custom_pre_get_posts_query' );
//add_action( 'pre_get_posts' ,'eventer_custom_pre_get_posts_query' ); 


function eventer_add_custom_ticket_variation($item_data, $cart_item)
{
  if (!isset($cart_item['wceventer_name'])) {
    return $item_data;
  }
  $show_order_meta_date = (isset($cart_item['wceventer_date'])) ? date_i18n(get_option('date_format'), $cart_item['wceventer_date']) : '';
  $show_order_meta_allday = (isset($cart_item['wceventer_allday'])) ? $cart_item['wceventer_allday'] : '';
  $time = ($show_order_meta_allday != '') ? esc_html__('All day', 'eventer') : $cart_item['wceventer_time'];
  $multi_date = (isset($cart_item['wceventer_multi'])) ? $cart_item['wceventer_multi'] : '';
  $time_slot = (isset($cart_item['wceventer_slot'])) ? $cart_item['wceventer_slot'] : '';
  $time = ($time_slot != '' && $time_slot != '00:00:00') ? date_i18n(get_option('time_format'), strtotime($cart_item['wceventer_slot'])) : $time;
  $item_data[] = array(
    'key'     => esc_html__('Event', 'eventer'),
    'value'   => wc_clean($cart_item['wceventer_name']),
    'display' => '',
  );
  if ($multi_date) {
    $date_all = explode('-', $multi_date);
    $date_start = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $date_all[0]);
    $date_end = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $date_all[1]);
  }
  $cart_date_show = ($multi_date != '') ? $date_start . '-' . $date_end : $show_order_meta_date . ' ' . $time;
  $item_data[] = array(
    'key'     => esc_html__('Event Date', 'eventer'),
    'value'   => $cart_date_show,
    'display' => '',
  );
  if (isset($cart_item['wceventer_services'])) {
    $item_data[] = array(
      'key'     => esc_html__('Services', 'eventer'),
      'value'   => wc_clean($cart_item['wceventer_services']),
      'display' => '',
    );
  }
  return $item_data;
}
add_filter('woocommerce_get_item_data', 'eventer_add_custom_ticket_variation', 10, 2);


//add_filter('woocommerce_checkout_cart_item_quantity','eventer_add_ticket_custom_option_from_session_into_cart',1,3);  
add_filter('woocommerce_cart_item_name', 'eventer_add_ticket_custom_option_from_session_into_cart', 1, 3);
add_filter('woocommerce_order_item_name', 'eventer_add_ticket_custom_option_from_session_into_cart', 999, 3);
if (!function_exists('eventer_add_ticket_custom_option_from_session_into_cart')) {
  function eventer_add_ticket_custom_option_from_session_into_cart($product_name, $values, $cart_item_key)
  {
    if ((!has_term('eventer', 'product_cat', $values['product_id']) && !has_term('eventer_services', 'product_cat', $values['product_id'])) || is_page(array('checkout'))) return $product_name;
    if (isset($values['_eventer_custom_title']) && $values['_eventer_custom_title'] != '') {
      return $values['_eventer_custom_title'];
    } else {
      return get_the_title($values['product_id']);
    }
  }
}
add_action('woocommerce_new_order_item', 'eventer_add_values_to_order_item_meta', 1, 3);
if (!function_exists('eventer_add_values_to_order_item_meta')) {
  function eventer_add_values_to_order_item_meta($item_id, $item, $order_id)
  {
    $order_item = new WC_Order_Item_Product($item_id);
    $product_id = $order_item->get_product_id();
    if (!has_term('eventer', 'product_cat', $product_id) && !has_term('eventer_services', 'product_cat', $product_id)) return;
    global $woocommerce, $wpdb;
    $eventer_name = (isset($item->legacy_values['wceventer_name'])) ? $item->legacy_values['wceventer_name'] : '';
    $wceventer_id = (isset($item->legacy_values['wceventer_id'])) ? $item->legacy_values['wceventer_id'] : '';
    $wceventer_date = (isset($item->legacy_values['wceventer_date'])) ? $item->legacy_values['wceventer_date'] : '';
    $wceventer_date_multi = (isset($item->legacy_values['wceventer_multi'])) ? $item->legacy_values['wceventer_multi'] : '';
    $wceventer_time = (isset($item->legacy_values['wceventer_time'])) ? $item->legacy_values['wceventer_time'] : '';
    $wceventer_time_slot = (isset($item->legacy_values['wceventer_slot'])) ? $item->legacy_values['wceventer_slot'] : '';
    $wceventer_time_slot_title = (isset($item->legacy_values['wceventer_slot_title'])) ? $item->legacy_values['wceventer_slot_title'] : '';
    $wceventer_allday = (isset($item->legacy_values['wceventer_allday'])) ? $item->legacy_values['wceventer_allday'] : '';
    $wceventer_url = (isset($item->legacy_values['wceventer_url'])) ? $item->legacy_values['wceventer_url'] : '';
    $wceventer_registrants = (isset($item->legacy_values['eventer_registrants'])) ? $item->legacy_values['eventer_registrants'] : '';
    $wceventer_services = (isset($item->legacy_values['wceventer_services'])) ? $item->legacy_values['wceventer_services'] : '';
    $wceventer_product = (isset($item->legacy_values['wceventer_product'])) ? $item->legacy_values['wceventer_product'] : '';
    $wceventer_product_title = (isset($item->legacy_values['_eventer_custom_title'])) ? $item->legacy_values['_eventer_custom_title'] : '';
    if (!empty($eventer_name)) {
      wc_add_order_item_meta($item_id, 'Event name', $eventer_name);
    }
    if (!empty($wceventer_id)) {
      wc_add_order_item_meta($item_id, '_wceventer_id', $wceventer_id);
    }
    if (!empty($wceventer_date)) {
      $set_time = ($wceventer_allday != '') ? '' : $wceventer_time;
      $show_order_meta_date = date_i18n(get_option('date_format'), $wceventer_date);
      $save_order_meta_date = date_i18n('Y-m-d', $wceventer_date);
      wc_add_order_item_meta($item_id, 'Event Date', $show_order_meta_date . ' ' . $set_time);
      wc_add_order_item_meta($item_id, '_wceventer_date', $save_order_meta_date);
    }
    wc_add_order_item_meta($item_id, '_eventer_multi_date', $wceventer_date_multi);
    if (!empty($wceventer_url)) {
      wc_add_order_item_meta($item_id, 'Event URL', $wceventer_url);
    }
    if (!empty($wceventer_time_slot)) {
      wc_add_order_item_meta($item_id, '_wceventer_slot', $wceventer_time_slot);
      wc_add_order_item_meta($item_id, 'wceventer_slot_title', $wceventer_time_slot_title);
    }
    if (!empty($wceventer_allday)) {
      wc_add_order_item_meta($item_id, '_eventer_allday', $wceventer_allday);
    }
    if (!empty($wceventer_registrants)) {
      wc_add_order_item_meta($item_id, '_eventer_registrants', $wceventer_registrants);
    }
    if (!empty($wceventer_services)) {
      wc_add_order_item_meta($item_id, 'Services', $wceventer_services);
    }
    if (!empty($wceventer_product)) {
      wc_add_order_item_meta($item_id, '_eventer_product', $wceventer_product);
    }
    if (!empty($wceventer_product_title)) {
      wc_add_order_item_meta($item_id, '_eventer_custom_title', $wceventer_product_title);
    }
  }
}
add_action('woocommerce_before_cart_item_quantity_zero', 'eventer_remove_user_ticket_data_options_from_cart', 1, 1);







//add_filter('woocommerce_order_item_display_meta_key', 'change_order_item_meta_title', 20, 3);
/**
 * Changing a meta title
 * @param  string        $key  The meta key
 * @param  WC_Meta_Data  $meta The meta object
 * @param  WC_Order_Item $item The order item object
 * @return string        The title
 */
function change_order_item_meta_title($key, $meta, $item)
{

  // By using $meta-key we are sure we have the correct one.
  if ('Event Date' === $meta->key) {
    $key = 'SOMETHING';
  }

  return $key;
}
add_filter('woocommerce_order_item_display_meta_value', 'change_order_item_meta_value', 20, 3);
/**
 * Changing a meta value
 * @param  string        $value  The meta value
 * @param  WC_Meta_Data  $meta   The meta object
 * @param  WC_Order_Item $item   The order item object
 * @return string        The title
 */
function change_order_item_meta_value($value, $meta, $item)
{

  // By using $meta-key we are sure we have the correct one.
  if ('Event Date' === $meta->key) {
    $item_id = $item->get_id();
    $multi_date = wc_get_order_item_meta($item_id, '_eventer_multi_date', true);
    if ($multi_date != '') {
      $date_all = explode('-', $multi_date);
      $date_start = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $date_all[0]);
      $date_end = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $date_all[1]);
      $value = $date_start . '-' . $date_end;
    }
  }

  return $value;
}





if (!function_exists('eventer_remove_user_ticket_data_options_from_cart')) {
  function eventer_remove_user_ticket_data_options_from_cart($cart_item_key)
  {
    global $woocommerce;
    // Get cart
    $cart = $woocommerce->cart->get_cart();
    // For each item in cart, if item is upsell of deleted product, delete it
    foreach ($cart as $key => $values) {
      //if ( $values['_wceventer_id'] == $cart_item_key || $values['_wceventer_date'] == $cart_item_key)
      //unset( $woocommerce->cart->cart_contents[ $key ] );
    }
  }
}
add_action('woocommerce_checkout_order_processed', 'eventer_update_tickets', 10, 1);
//add_action('woocommerce_thankyou', 'eventer_update_tickets', 10, 1);
function eventer_update_tickets($order_id)
{
  if (!$order_id) return;
  $ticket_orders = (is_array(get_option('eventer_ticket_orders'))) ? get_option('eventer_ticket_orders') : array();
  if (in_array($order_id, $ticket_orders)) {
    return;
  }
  $order = wc_get_order($order_id);
  $update_new_val = $new_already_booked = $cart_items = array();
  foreach ($order->get_items() as $item_key => $item_values) :
    $new_already_booked = $update_new_val = array();
    $item_data = $item_values->get_data();
    $item_id = $item_values->get_id();
    $product_name = $item_data['name'];
    $product_id = $item_data['product_id'];
    if (!has_term('eventer', 'product_cat', $product_id) && !has_term('eventer_services', 'product_cat', $product_id)) continue;
    $quantity = $item_data['quantity'];
    $order_event_url = wc_get_order_item_meta($item_id, 'Event URL', true);
    $eventer_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
    $original_event = eventer_wpml_original_post_id($eventer_id);
    $eventer_date = wc_get_order_item_meta($item_id, '_wceventer_date', true);
    $eventer_time_slot = wc_get_order_item_meta($item_id, '_wceventer_slot', true);
    $eventer_time = wc_get_order_item_meta($item_id, 'Event Date', true);
    $eventer_time = $eventer_time_slot;
    $send_ticket_data = array('id' => intval($product_id) + intval($original_event), 'number' => $quantity);
    if (get_post_meta($eventer_id, 'eventer_common_ticket_count', true) != '') {
      $booked_tickets = eventer_update_date_wise_bookings_table($eventer_id, $eventer_date . ' ' . $eventer_time, array(), 2);
      if ($booked_tickets) {
        foreach ($booked_tickets as $get_ticket) {
          $all_tickets = (isset($get_ticket['pid'])) ? $get_ticket['pid'] : '';
          if ($all_tickets != '' && $all_tickets != $product_id) {
            $send_ticket_data_new = array('id' => intval($all_tickets) + intval($original_event), 'number' => $quantity);
            eventer_update_date_wise_bookings_table($eventer_id, $eventer_date . ' ' . $eventer_time, array($send_ticket_data_new), 3, 1);
          }
        }
      }
    }
    eventer_update_date_wise_bookings_table($eventer_id, $eventer_date . ' ' . $eventer_time, array($send_ticket_data), 3, 1);
  endforeach;
  $new_order_vals = array_unique(array_merge($ticket_orders, array($order_id)));
  update_option('eventer_ticket_orders', $new_order_vals);
}

function eventer_remove_editing_quantity($product_quantity, $cart_item_key)
{
  $cart_item = WC()->cart->cart_contents[$cart_item_key];
  $product_id = $cart_item['product_id'];
  if (!has_term('eventer', 'product_cat', $product_id) && !has_term('eventer_services', 'product_cat', $product_id)) return $product_quantity;
  $quantity = $cart_item['quantity'];
  return $quantity;
}
add_filter('woocommerce_cart_item_quantity', 'eventer_remove_editing_quantity', 10, 3);

function eventer_remove_permalink_thumb($image, $cart_item, $cart_item_key)
{
  $product_id = $cart_item['product_id'];
  $event_id = (isset($cart_item['wceventer_id'])) ? $cart_item['wceventer_id'] : '';
  if (!has_term('eventer', 'product_cat', $product_id) && !has_term('eventer_services', 'product_cat', $product_id)) return $image;
  return get_the_post_thumbnail($event_id);
}
add_filter('woocommerce_cart_item_thumbnail', 'eventer_remove_permalink_thumb', 10, 3);
function eventer_alter_ticket_thumbnail($product_get_permalink_cart_item, $cart_item, $cart_item_key)
{
  $product_id = $cart_item['product_id'];
  $event_url = (isset($cart_item['wceventer_url'])) ? $cart_item['wceventer_url'] : '';
  if (!has_term('eventer', 'product_cat', $product_id) && !has_term('eventer_services', 'product_cat', $product_id)) return $product_get_permalink_cart_item;
  return $event_url;
}
add_filter('woocommerce_cart_item_permalink', 'eventer_alter_ticket_thumbnail', 10, 3);
function eventer_change_item_key($cart_item_data, $product_id)
{
  if (!has_term('eventer', 'product_cat', $product_id) && has_term('eventer_services', 'product_cat', $product_id)) return $cart_item_data;
  $unique_cart_item_key = md5(microtime() . rand());
  $cart_item_data['unique_key'] = $unique_cart_item_key;
  return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'eventer_change_item_key', 10, 2);
add_filter('woocommerce_order_item_name', 'eventer_remove_hyperlink_from_order', 10, 2);
function eventer_remove_hyperlink_from_order($item_name, $item)
{
  if (!has_term('eventer', 'product_cat', $item['product_id']) && !has_term('eventer_services', 'product_cat', $item['product_id'])) return $item_name;
  $item_name = get_the_title($item['product_id']);
  return $item_name;
}
function eventer_add_meta_on_success($array)
{
  $order = $array['order'];
  if ($order->get_status() == "completed") {
    wp_add_inline_script('eventer-init', 'jQuery(".eventer-show-download-tickets-form").show();');
    $order_id = $order->get_id();
    $has_eventer = 0;

    foreach ($order->get_items() as $item_key => $item_values) :
      $item_data = $item_values->get_data();
      $product_id = $item_data['product_id'];
      if (!has_term('eventer', 'product_cat', $product_id)) continue;
      $has_eventer = 1;
    endforeach;
    if ($has_eventer == 0) return $array;

    wp_schedule_single_event(time() + 5, 'eventer_woocommerce_generate_ticket', array($order_id));
  }
  return $array;
}
add_filter('woocommerce_email_order_items_args', 'eventer_add_meta_on_success', 12, 1);

add_action('eventer_woocommerce_generate_ticket', 'eventer_woo_tickets_attachment', 10, 1);
function eventer_woo_tickets_attachment($order_id)
{
  $registrants = eventer_get_registrant_details('eventer', $order_id);
  echo apply_filters('eventer_status_changed_completed', $registrants);
}

function eventer_change_product_meta_key($display_key)
{
  if ($display_key == "Event Ticket") {
    $display_key = esc_html__('Event Ticket', 'eventer');
  } elseif ($display_key == "Event name") {
    $display_key = esc_html__('Event name', 'eventer');
  } elseif ($display_key == "Event Date") {
    $display_key = esc_html__('Event Date', 'eventer');
  } elseif ($display_key == "Event URL") {
    $display_key = esc_html__('Event URL', 'eventer');
  } elseif ($display_key == "Services") {
    $display_key = esc_html__('Services', 'eventer');
  }
  return $display_key;
};
add_filter('woocommerce_order_item_display_meta_key', 'eventer_change_product_meta_key', 10, 1);

add_action('eventer_dashboard_bookings_tickets', 'eventer_create_booking_woocommerce', 20, 1);
add_action('woocommerce_thankyou', 'eventer_show_thanks_page_download_button', 1);

function eventer_show_thanks_page_download_button($order_id)
{
  $order = wc_get_order($order_id);
  $download = '';
  foreach ($order->get_items() as $item_key => $item_values) :
    $item_id = $item_values->get_id();
    $eventer_product_type = wc_get_order_item_meta($item_id, '_eventer_product', true);
    if ($eventer_product_type == '') continue;
    $download = 1;
  endforeach;
  if ($download == 1) {
    $back_order_tickets = (isset($_REQUEST['backorder'])) ? wp_get_referer() : '';
    $registrant_uname = get_post_meta($order_id, '_billing_first_name', true) . ' ' . get_post_meta($order_id, '_billing_last_name', true);
    $registrant_email = get_post_meta($order_id, '_billing_email', true);
    $default = array();
    $new_tickets = apply_filters('eventer_preapare_data_for_tickets', 'eventer', $order_id, array());
    $registrants = eventer_get_registrant_details('eventer', $order_id);
    $new_tickets['data-regpos'] = 14;
    $new_tickets['data-backorder'] = $back_order_tickets;
    $new_tickets['default']['data-eid'] = '';
    $new_tickets['default']['data-regpos'] = 14;
    $new_tickets['default']['data-registrant'] = $registrants->id;
    do_action('eventer_ticket_raw_design', '', $new_tickets);
    echo '<form action="' . esc_url(admin_url('admin-ajax.php')) . '" method="post" class="eventer-show-download-tickets-form" style="display:none;">';
    echo '<input type="hidden" name="action" value="eventer_woo_download_tickets">';
    echo '<input type="hidden" class="eventer-woo-tickets" name="tickets" value="">';
    echo '<input type="hidden" name="captcha" value="' . wp_create_nonce('eventer-tickets-download') . '">';
    echo '<input type="submit" value="' . esc_html__('Download Tickets', 'eventer') . '" class="button"></form><br/>';
  }
}
add_action('woocommerce_checkout_order_processed', 'eventer_create_booking_woocommerce', 999);

function eventer_create_booking_woocommerce($order_id)
{
  if (empty(get_post_meta($order_id, 'eventer_order_recorded', true))) {
    $registrant_uname = get_post_meta($order_id, '_billing_first_name', true) . ' ' . get_post_meta($order_id, '_billing_last_name', true);
    $registrant_email = get_post_meta($order_id, '_billing_email', true);
    $order = wc_get_order($order_id);
    $event_ids = $registrants = $all_tickets = $all_services = $reg_details = $ticket_wise_registrants = array();
    foreach ($order->get_items() as $item_key => $item_values) :
      $item_data = $item_values->get_data();
      $item_id = $item_values->get_id();
      $product_id = $item_data['product_id'];
      $eventer_product_type = wc_get_order_item_meta($item_id, '_eventer_product', true);
      if ($eventer_product_type == '') continue;
      $event_date = wc_get_order_item_meta($item_id, '_wceventer_date', true);
      $event_time_slot = wc_get_order_item_meta($item_id, '_wceventer_slot', true);
      $event_time_slot_title = wc_get_order_item_meta($item_id, 'wceventer_slot_title', true);
      $event_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
      $event_allday = wc_get_order_item_meta($item_id, '_eventer_allday', true);
      $event_ids[$event_id . '-' . mt_rand()] = $event_date;
      $event_time = get_post_meta($event_id, 'eventer_event_start_dt', true);
      $event_time = date_i18n(get_option('time_format'), strtotime($event_time));
      $event_time = ($event_allday != '') ? esc_html__('All day', 'eventer') : $event_time;
      $event_registrants = wc_get_order_item_meta($item_id, '_eventer_registrants', true);
      $registrants[$event_id . '-' . mt_rand()] = $event_registrants;
      $event_date = strtotime($event_date);
      $product_name = $item_data['name'];
      $quantity = $item_data['quantity'];
      $all_tickets[] = array('name' => $product_name, 'quantity' => $quantity, 'number' => $quantity);
      $ticket_wise_registrants[] = array('event' => $event_id, 'date' => $event_date, 'type' => $eventer_product_type, 'ticket' => $product_name, 'quantity' => $quantity, 'registrants' => $event_registrants, 'time_slot' => $event_time_slot, 'slot_title' => $event_time_slot_title, 'id' => intval($product_id) + intval($event_id));
      if ($eventer_product_type != 'ticket') {
        $all_services[] = array('name' => $product_name, 'quantity' => $quantity);
      }

    endforeach;
    $current_date = date_i18n('Y-m-d G:i');
    $transID = $order->get_transaction_id();
    $payment_method = $order->get_payment_method();
    $ip = eventer_client_ip();
    $status = $order->get_status();
    $amount = $order->get_total();
    $user_reg_id = get_current_user_id();
    $user_system_data = serialize(array('ip' => $ip, 'services' => $all_services, 'tickets' => $ticket_wise_registrants, 'registrants' => $registrants, 'events' => $event_ids, 'time_slot' => $event_time_slot, 'slot_title' => $event_time_slot_title));
    $eventer_date = $event_date;
    global $wpdb;
    $table_name = $wpdb->prefix . "eventer_registrant";
    $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO $table_name
          ( eventer, transaction_id , username, email, paymentmode, user_details, tickets, ctime, status, amount, user_system, user_id)
          VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %d )",
        array($order_id, $transID, $registrant_uname, $registrant_email, $payment_method, serialize($reg_details), serialize($all_tickets), $current_date, $status, $amount, $user_system_data, $user_reg_id)
      )
    );
    update_post_meta($order_id, 'eventer_order_recorded', $wpdb->insert_id);
    wp_schedule_single_event(time() + 5, 'eventer_woocommerce_ticket_restore_auto', array($order_id));
  }
}

if (eventer_get_settings('eventer_woo_layout') != 'off') {
  add_action('woocommerce_widget_shopping_cart_buttons', function () {
    // Removing Buttons
    remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
    remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20);

    // Adding customized Buttons
    add_action('woocommerce_widget_shopping_cart_buttons', 'eventer_custom_widget_shopping_cart_button_view_cart', 10);
    add_action('woocommerce_widget_shopping_cart_buttons', 'eventer_custom_widget_shopping_cart_button_view_cart', 20);
  }, 1);
}


// Custom cart button
function eventer_custom_widget_shopping_cart_button_view_cart()
{
  echo '';
}
add_action('woocommerce_order_status_changed', 'eventer_update_booking_status');
function eventer_update_booking_status($order_id)
{
  //if (is_admin()) {
  $order = wc_get_order($order_id);
  $status = $order->get_status();
  $registrant_id = get_post_meta($order_id, 'eventer_order_recorded', true);
  eventer_update_registrant_details(array('status' => $status), $registrant_id, array("%s", "%s"));
  //}
}
add_action('eventer_woocommerce_ticket_restore_auto', 'eventer_update_tickets_woocommerce', 10, 1);

function eventer_update_tickets_woocommerce($order_id)
{
  $order = wc_get_order($order_id);
  $status = $order->get_status();
  if ($status != 'completed') {
    wp_schedule_single_event(time(), 'eventer_woocommerce_order_status_changed', array($order_id, 'pendings', 'failed'));
  }
}
//add_action('eventer_woocommerce_order_status_changed', 'eventer_status_changed_restore', 10, 3);
add_action('woocommerce_order_status_changed', 'eventer_status_changed_restore', 10, 3);
function eventer_status_changed_restore($order_id, $old_status, $new_status)
{

  $registrants = eventer_get_registrant_details('eventer', $order_id);
  $user_system = unserialize($registrants->user_system);
  $tickets = (isset($user_system['tickets'])) ? $user_system['tickets'] : array();
  $tickets_restore = (isset($user_system['restore'])) ? $user_system['restore'] : '';
  if ($old_status == 'pending' && $tickets_restore == '') {
    $user_system['restore'] = 0;
    eventer_update_registrant_details(array('user_system' => serialize($user_system)), $registrants->id, array("%s", "%s"));
    return;
  }
  if (!empty($tickets) && $tickets_restore == 1 && $new_status == 'completed') {
    foreach ($tickets as $ticket) {
      if (!isset($ticket['id'])) break;
      $ticket_date = date_i18n('Y-m-d', $ticket['date']);
      $ticket_time = (isset($ticket['time_slot']) && $ticket['time_slot'] != '') ? $ticket['time_slot'] : '00:00:00';
      eventer_update_date_wise_bookings_table($ticket['event'], $ticket_date . ' ' . $ticket_time, array(array('id' => $ticket['id'], 'number' => $ticket['quantity'])), 3, 1);
    }
    $user_system['restore'] = 0;
    eventer_update_registrant_details(array('user_system' => serialize($user_system)), $registrants->id, array("%s", "%s"));
  } elseif ($tickets_restore != 1 && $new_status != 'completed') {
    foreach ($tickets as $ticket) {
      if (!isset($ticket['id'])) break;
      $ticket_date = date_i18n('Y-m-d', $ticket['date']);
      $ticket_time = (isset($ticket['time_slot']) && $ticket['time_slot'] != '') ? $ticket['time_slot'] : '00:00:00';
      eventer_update_date_wise_bookings_table($ticket['event'], $ticket_date . ' ' . $ticket_time, array(array('id' => $ticket['id'], 'number' => $ticket['quantity'])), 3, 2);
    }
    $user_system['restore'] = 1;
    eventer_update_registrant_details(array('user_system' => serialize($user_system)), $registrants->id, array("%s", "%s"));
  }
}

add_filter('woocommerce_checkout_get_value', 'eventer_modify_checkout_fields', 10, 2);
function eventer_modify_checkout_fields($value, $input)
{

  $token = (!empty($_GET['token'])) ? $_GET['token'] : '';

  //if( 'testtoken' == $token ) {
  // Define your checkout fields  values below in this array (keep the ones you need)
  $checkout_fields = array(
    'billing_first_name'    => ((isset($_COOKIE['woo_checkout_user_name'])) ? $_COOKIE['woo_checkout_user_name'] : ''),
    'billing_email'         => ((isset($_COOKIE['woo_checkout_user_email'])) ? $_COOKIE['woo_checkout_user_email'] : ''),
  );
  foreach ($checkout_fields as $key_field => $field_value) {
    if ($input == $key_field && !empty($field_value)) {
      $value = $field_value;
    }
  }
  //}
  return $value;
}
