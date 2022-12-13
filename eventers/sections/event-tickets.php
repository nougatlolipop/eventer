<?php
if ($registration_switch == "1") {
  $eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
  $eventer_start_time = get_post_meta($event_id, 'eventer_event_start_dt', true);
  $event_start_time_str = strtotime($eventer_start_time);
  $eventer_st_time = date_i18n("H:i", $event_start_time_str);
  $eventer_formatted_proper_time = date_i18n('Y-m-d ' . $eventer_st_time, $event_cdate);
  $original_event = eventer_wpml_original_post_id($event_id);
  $tickets = get_post_meta($original_event, 'eventer_tickets', true);
  $tickets_translated = get_post_meta(get_the_ID(), 'eventer_tickets', true);
  $remaining_for_reg = '';
  $booking_url = '';
  $booking_url_target = get_post_meta($event_id, 'eventer_event_registration_target', true);
  $registration_url = get_post_meta($event_id, 'eventer_event_custom_registration_url', true);
  //if (filter_var($registration_url, FILTER_VALIDATE_URL)) {
  $booking_url = $registration_url;
  //}
  $booking_calendar = eventer_get_settings('eventer_booking_calendar');
  ?>
  <div class="eventer eventer-event-single eventer-ticket-details-wrap">

    <?php
      //if($booking_url=='')
      //{
      $ticket_remaining_modal = $remaining_for_reg = '';
      $show_tickets_info = (!empty($booked_tickets)) ? $booked_tickets : $tickets;
      if (!empty($show_tickets_info)) {
        echo '<div class="eventer-ticket-details" data-date="' . esc_attr(date_i18n('Y-m-d', $event_cdate)) . '" data-time="' . esc_attr($time_slot) . '" data-slottitle="' . $time_slot_title . '">
                        <h3>' . esc_html__('Tickets details', 'eventer') . '</h3>';
        echo (count($all_dates) > 1 && $days_diff <= 0 && get_post_type($event_id) == 'eventer' && $booking_calendar == 'on') ? '<input type="input" id="eventer-future-bookings" data-time="asdasdf" class="datepicker" style="display:none;"/>' : '';

        echo $time_slot_values;
        echo '<ul class="eventer-tickets-info">
                <form class="eventer-loader-form" style="display:none;"><div class="eventer-loader-wrap"><div class="eventer-loader"></div></div></form>';
        $counting = 0;
        foreach ($show_tickets_info as $ticket) {

          $ticket_name = (isset($ticket['name'])) ? $ticket['name'] : '';
          $ticket_locale_name = (isset($ticket['cust_val1'])) ? json_decode($ticket['cust_val1'], true) : [];
          $ticket_name = ($ticket_locale_name && isset($ticket_locale_name[EVENTER__LANGUAGE_CODE]) && $ticket_locale_name[EVENTER__LANGUAGE_CODE] != '') ? $ticket_locale_name[EVENTER__LANGUAGE_CODE] : $ticket_name;
          $ticket_pid = (isset($ticket['pid'])) ? $ticket['pid'] : '';
          $ticket_existing = (get_post_type($ticket_pid) == 'product' && get_post_status($ticket_pid) == 'publish') ? '' : esc_html__('Ticket missing', 'eventer');
          $ticket_existing = ($woo_ticketing == 'on') ? $ticket_existing : '';
          if (isset($tickets_translated[$counting]) && isset($tickets_translated[$counting]['pid']) && $tickets_translated[$counting]['pid'] == $ticket_pid && $ticket_name != $tickets_translated[$counting]['name']) {
            $ticket_name = $tickets_translated[$counting]['name'];
          }
          if ($ticket_name == '') continue;
          $ticket_number = (isset($ticket['tickets'])) ? $ticket['tickets'] : '';
          $ticket_price = (isset($ticket['price'])) ? number_format($ticket['price'], 2) : '';
          $ticket_restrict = (isset($ticket['restricts'])) ? $ticket['restricts'] : '';
          $ticket_enabled = (isset($ticket['enabled'])) ? $ticket['enabled'] : '';
          $ticket_currency = $eventer_currency;
          if (is_numeric($ticket_price) && $ticket_price != '') {
            $ticket_price = ($currency_position != 'postfix') ? $ticket_currency . $ticket_price : $ticket_price . $ticket_currency;
            $discounted_price = '';
          } elseif (strpos($ticket_price, "-") !== false && $ticket_price != '') {
            $new_ticket_price = explode('-', $ticket_price);
            $calculate_discounted_price = $new_ticket_price[0] - $new_ticket_price[1];
            $discounted_price = $ticket_currency . $calculate_discounted_price;
            $show_price = ($currency_position != 'postfix') ? $ticket_currency . $new_ticket_price[0] : $new_ticket_price[0] . $ticket_currency;
            $ticket_price = '<del class="eventer-price-currency">' . $show_price . '</del>';
          } else {
            $ticket_price = $ticket_price;
            $discounted_price = '';
            $ticket_currency = '';
          }
          $remaining_tickets = ($ticket_number <= 0) ? '<i class="eventer-ticket-remaining eventer-ticket-full">' . esc_html__('All Booked', 'eventer') . '</i>' : '<i class="eventer-ticket-remaining">' . $ticket_number . ' ' . esc_html__('remaining', 'eventer') . '</i>';
          $ticket_enabled_date = (strtotime($ticket_enabled) <= date_i18n('U')) ? '' : '<i class="eventer-ticket-remaining eventer-ticket-full">' . esc_html__('Ticket selling opens on', 'eventer') . ' ' . date_i18n(get_option('date_format'), strtotime($ticket_enabled)) . '</i>';
          $difference = 1000;
          $booking_closes = get_post_meta($event_id, 'eventer_disable_booking_before', true);
          if ($booking_closes != '') {
            $close_date = date('Y-m-d', strtotime($eventer_formatted_date . ' - ' . $booking_closes . ' days'));
            $difference = eventer_dateDiff(date_i18n('Y-m-d'), $close_date);
            if ($difference <= 4 && $difference > 0) {
              $ticket_enabled_date = '<i class="eventer-ticket-remaining eventer-ticket-full">' . $difference . esc_html__(' days left for booking', 'eventer') . '</i>';
            } elseif ($difference <= 0) {
              $ticket_enabled_date = '<i class="eventer-ticket-remaining eventer-ticket-full">' . esc_html__('Booking closed', 'eventer') . '</i>';
            }
          }


          if ($ticket_number > 0) {
            $remaining_for_reg = 1;
          }
          if ($ticket_existing == '') {
            echo '<li data-restrict="' . esc_attr($ticket_restrict) . '">
						<span class="eventer-ticket-type-price">' . $ticket_price . ' ' . $discounted_price . '</span>
						<span class="eventer-ticket-type-name">' . $ticket_name . ' ' . $remaining_tickets . $ticket_enabled_date . '</span>
						</li>';
          } else {
            echo '<li title="' . esc_html__('It seems the ticket you added for this event is no more exists.', 'eventer') . '">
					<span class="eventer-ticket-type-name">' . $ticket_existing . '</span>
					</li>';
          }

          $counting++;
        }
        echo '</ul></div>';
      }
      if ($remaining_for_reg == 1 && date_i18n('U') < strtotime($eventer_formatted_proper_time) && in_array($eventer_formatted_date, $all_dates) && $booking_url == '' && get_post_type($event_id) == 'eventer' && $difference > 0) { ?>
      <a class="eventer-btn eventer-btn-primary" rel="emodal:open" href="#<?php echo $dynamic_val; ?>-eventer-ticket-form"><?php esc_html_e('Book tickets', 'eventer'); ?></a>
    <?php
      } elseif (date_i18n('U') > strtotime($eventer_formatted_proper_time) && in_array($eventer_formatted_date, $all_dates) && $booking_url == '' && get_post_type(get_the_ID()) == 'eventer') {
        ?>
      <a href="javascript:void(0)" class="eventer-btn eventer-btn-primary"><?php esc_html_e('Sorry, Event Passed', 'eventer'); ?></a>
    <?php } elseif (empty($show_tickets_info) && $booking_url == '' && get_post_type($event_id) == 'eventer' && $difference > 0) { ?>
      <a class="eventer-btn eventer-btn-primary" rel="emodal:open" href="#<?php echo $dynamic_val; ?>-eventer-ticket-form"><?php esc_html_e('Book tickets', 'eventer'); ?></a>
    <?php }

      ?>
    <?php
      if ($booking_url != '') { ?>
      <a href="<?php echo esc_url($booking_url); ?>" target="<?php echo esc_attr($booking_url_target); ?>" class="eventer-btn eventer-btn-primary"><?php esc_html_e('Register', 'eventer'); ?></a>
    <?php } ?>
  </div>
<?php
}
?>