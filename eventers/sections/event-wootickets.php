<?php
$registration_switch = get_post_meta($event_id, 'eventer_event_registration_swtich', true);
if ($registration_switch != "1") return;
$original_event = eventer_wpml_original_post_id($event_id);
$tickets = get_post_meta($original_event, 'wceventer_tickets', true);
$eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
$event_start_time_str = $start_str;
$event_end_time_str = $end_str;
$multi_state = (date_i18n('Y-m-d', $event_start_time_str) != date_i18n('Y-m-d', $event_end_time_str)) ? $event_start_time_str . '-' . $event_end_time_str : '';
$eventer_st_time = date_i18n("H:i", $event_start_time_str);
$booking_url = (function_exists('wc_get_cart_url')) ? wc_get_cart_url() : '';
$booking_url_target = get_post_meta($event_id, 'eventer_event_registration_target', true);
$registration_url = get_post_meta($event_id, 'eventer_event_custom_registration_url', true);
$time_slot = (date_i18n('H:i:s', strtotime($time_slot)) != '00:00:00') ? $time_slot : date_i18n('H:i:00', $event_start_time_str);
if (filter_var($registration_url, FILTER_VALIDATE_URL)) {
  $booking_url = $registration_url;
}
$new_updated_bookings = $all_tickets_name = array();
$eventer_maximum_tickets_count = eventer_get_settings('eventer_tickets_quantity_set');
$eventer_maximum_tickets_count = ($eventer_maximum_tickets_count) ? $eventer_maximum_tickets_count : 10;
$booking_calendar = eventer_get_settings('eventer_booking_calendar');
?>
<div class="eventer eventer-event-single">
  <div class="eventer-ticket-details-wrap">
    <?php
    $ticket_remaining_modal = $remaining_for_reg = $check_duplicate = '';
    $show_tickets_info = $tickets;
    if (!empty($show_tickets_info)) {
      echo '<div class="eventer-ticket-details" data-date="' . esc_attr(date_i18n('Y-m-d', $event_cdate)) . '" data-time="' . esc_attr($time_slot) . '" data-slottitle="' . $time_slot_title . '">
										<h3>' . esc_html__('Tickets details', 'eventer') . '</h3>';
      echo ($days_diff <= 0 && get_post_type($event_id) == 'eventer' && $booking_calendar == 'on') ? '<input type="input" id="eventer-future-bookings" data-time="asdasdf" class="datepicker" style="display:none;" />' : '';
      echo $time_slot_values;
      echo '<ul class="eventer-tickets-info">';
      foreach ($show_tickets_info as $ticket) {
        $remaining_for_reg = $remaining_booked = '';
        $wceventer_ticket_id = (isset($ticket['wceventer_ticket_id'])) ? $ticket['wceventer_ticket_id'] : '';
        if (get_post_type($wceventer_ticket_id) != 'product' || get_post_status($wceventer_ticket_id) != 'publish') continue;
        $ticket_name = get_the_title($wceventer_ticket_id);
        $sold_individually = get_post_meta($wceventer_ticket_id, '_sold_individually', true);
        $eventer_maximum_tickets_count = ($sold_individually == 'yes') ? 1 : $eventer_maximum_tickets_count;
        $product = wc_get_product($wceventer_ticket_id);
        $woocommerce_currency = get_woocommerce_currency_symbol();
        $raw_price = number_format($product->get_price(), 2);
        $ticket_regular_price = $product->get_regular_price();
        $show_old_price = ($currency_position != 'postfix') ? $woocommerce_currency . $ticket_regular_price : $ticket_regular_price . $woocommerce_currency;
        $old_price = ($raw_price != $ticket_regular_price) ? '<del class="eventer-price-currency">' . $show_old_price . '</del> ' : '';
        $new_booked_tickets_key = eventer_search_ticket($ticket_name, $booked_tickets, 0, $wceventer_ticket_id);
        $exact_ticket_find = (isset($booked_tickets[$new_booked_tickets_key])) ? $booked_tickets[$new_booked_tickets_key] : array();
        if (!empty($exact_ticket_find)) {
          $pid = (isset($exact_ticket_find['pid'])) ? $exact_ticket_find['pid'] : '';
          if ($pid == $wceventer_ticket_id || $exact_ticket_find['name'] == $ticket_name) {
            $ticket_name = esc_attr($exact_ticket_find['name']);
            $remaining_booked = esc_attr($exact_ticket_find['tickets']);
            $raw_price = ($exact_ticket_find['price'] != '') ? number_format($exact_ticket_find['price'], 2) : number_format($raw_price, 2);
          }
        }
        if (in_array($ticket_name, $all_tickets_name)) {
          $check_duplicate = "1";
          continue;
        }
        $all_tickets_name[] = $ticket_name;
        $ticket_number = (isset($ticket['wceventer_ticket_number']) && $remaining_booked == '') ? $ticket['wceventer_ticket_number'] : $remaining_booked;

        $ticket_price = ($currency_position != 'postfix') ? $old_price . $woocommerce_currency . $raw_price : $old_price . $raw_price . $woocommerce_currency;
        $remaining_tickets = ($ticket_number <= 0) ? '<i class="eventer-ticket-remaining eventer-ticket-full">' . esc_html__('All Booked', 'eventer') . '</i>' : '<i class="eventer-ticket-remaining">' . $ticket_number . ' ' . esc_html__('remaining', 'eventer') . '</i>';
        if ($ticket_number > 0) {
          $remaining_for_reg = 1;
        }
        $remain_for_booking = ($ticket_number > $eventer_maximum_tickets_count) ? $eventer_maximum_tickets_count : $ticket_number;
        $btn_txt = esc_html__('Add to cart', 'eventer');
        $added_cart = esc_html__('Added to cart', 'eventer');
        echo '<li data-btntxt="' . esc_attr($btn_txt) . '" data-addedcart="' . esc_attr($added_cart) . '" data-currency="' . $woocommerce_currency . '" data-price="' . esc_attr($raw_price) . '" data-product="' . esc_attr($wceventer_ticket_id) . '" data-eventer="' . $event_id . '" data-multi="' . esc_attr($multi_state) . '"  data-edate="' . $event_cdate . '" data-eventerurl="' . esc_url(eventer_generate_endpoint_url('edate', $eventer_formatted_date, get_permalink($event_id))) . '" data-etime="' . date_i18n(get_option('time_format'), strtotime($time_slot)) . '" data-allday="' . esc_attr($allday) . '">';
        if (date_i18n('Y-m-d') <= $eventer_formatted_date) {
          echo '<div class="eventer-wc-ticket-table"><div>
								<div class="eventer-wc-ticket-col"><span class="eventer-ticket-type-name">' . $ticket_name . ' ' . $remaining_tickets . '</span></div>
								<div class="eventer-wc-ticket-col"><span class="eventer-wc-ticket-price eventer-ticket-type-price">' . $ticket_price . '</span></div>';

          if ($remaining_for_reg == 1) {
            echo '<div class="eventer-wc-ticket-col"><select class="eventer-wc-ticket-quantity">';
            for ($start = 1; $start <= $remain_for_booking; $start++) {
              echo '<option value="' . $start . '">' . $start . '</option>';
            }
            echo '</select></div>';
            echo '<div class="eventer-wc-ticket-col">
								<div class="eventer-wc-addtocart"><input type="button" class="eventer-btn add-ticket-wc" value="' . esc_attr($btn_txt) . '">
											<!-- <span class="eventer-wc-ticket-total"></span> -->
											<span class="eventer-wc-ticket-added"></span></div></div>';
          }

          echo '</div></div>';
        } else {
          esc_html_e('Sorry, Event passed', 'eventer');
        }
        echo '</li>';
      }
      echo '</ul></div>';
    }
    if (get_post_type($event_id) == 'eventer') {
      ?>
      <a href="<?php echo esc_url($booking_url); ?>" target="<?php echo esc_attr($booking_url_target); ?>" class="eventer-btn eventer-btn-primary"><?php esc_html_e('View cart', 'eventer'); ?></a>
    <?php } ?>
  </div>
</div>