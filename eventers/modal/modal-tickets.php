<?php
if (get_post_type($registrant) == 'shop_order' && $woocommerce_ticketing == 'on') {
  $order = wc_get_order($registrant);
  $order_status = $order->get_status();
  $order_num = $registrant;
  $registrant_uname = get_post_meta($registrant, '_billing_first_name', true) . ' ' . get_post_meta($registrant, '_billing_last_name', true);
} else {
  $order_status = 0;
  $order_num = $registrant;
  $user_system = unserialize($usersystem);
  $registrant_uname = (isset($username)) ? $username : '';
  $registrant_firstname = (isset($user_details['Name'])) ? $user_details['Name'] : '';
  $registrant_lastname = (isset($user_details['LAST NAME'])) ? $user_details['LAST NAME'] : '';
  $registrant_uname = $registrant_firstname . ' ' . $registrant_lastname;
  if (isset($user_system['slot_title']) && $user_system['time_slot'] != '00:00:00') {

    $slot_time = date_i18n(get_option('time_format'), strtotime($user_system['time_slot']));
    //$slot_time = '';
    $event_time_show = $slot_time . $user_system['slot_title'];
  }
}
if ($order_status != 'completed' && $reg_position <= 14) return;
$elocation = '';
$eventer_venue = get_the_terms($event_id, 'eventer-venue');
if (!is_wp_error($eventer_venue) && !empty($eventer_venue)) {
  foreach ($eventer_venue as $venue) {
    $location_address = get_term_meta($venue->term_id, 'venue_address', true);
    $elocation = ($location_address != '') ? $location_address : $venue->name;
  }
}
$ticket_top_area = '<div class="eventer-on-ticket-qr" data-qr-content="' . esc_attr($ticket_id_db) . '" data-qr-size="200"></div>
				<label class="eventer-ticket-reg-code">' . esc_attr($ticket_id_db) . '</label>';
$ticket_bottom_area = '<label>' . esc_html__('Attendee', 'eventer') . '</label>
				<h3>' . esc_attr($registrant_uname) . '</h3>
				<label>' . esc_html__('Event', 'eventer') . '</label>
				<p>' . apply_filters('eventer_raw_event_title', '', get_the_ID()) . '</p>
				<div class="eventer-spacer-10"></div>
				<label>' . esc_html__('Ticket', 'eventer') . '</label>';
if (!empty($booked_registrant_tickets)) {
  foreach ($booked_registrant_tickets as $reg_ticket) {
    if ($reg_ticket['name'] == '' || $reg_ticket['number'] <= 0) continue;
    $ticket_bottom_area .= '<p class="eventer-tickets-booked-info">' . esc_attr($reg_ticket['name']) . ' x <strong>' . esc_attr($reg_ticket['number']) . '</strong></p>';
  }
}
$ticket_bottom_area .= '<div class="eventer-spacer-10"></div>
								<div class="eventer-row">';
if ($elocation) {
  $ticket_bottom_area .= '<div class="eventer-col5">
										<label>' . esc_html__('Venue Location', 'eventer') . '</label>
										<p>' . esc_attr($elocation) . '</p>
								</div>';
}
$ticket_bottom_area .= '<div class="eventer-col5">
										<label>' . esc_html__('Date', 'eventer') . ' &amp; ' . esc_html__('Time', 'eventer') . '</label>
										<p>' . $event_time_show . '<br>' . esc_attr(date_i18n(get_option('date_format'), $event_cdate)) . '</p>
									</div>
								</div>
								<div class="eventer-spacer-10"></div>
								<a class="eventer-print-ticket" href="javascript:void(0)">' . esc_html__('PRINT', 'eventer') . '</a>';
$ticket_modal_show = '<div class="eventer eventer-event-single eventer-modal-static" id="eventer-ticket-show-now">
			<div class="eventer-modal-body">
					<div class="eventer-ticket-final-tickets">
						<div class="eventer-ticket-printable">
							<div class="eventer-ticket-printable-top">';
$ticket_modal_show .=                $ticket_top_area;
$ticket_modal_show .=           '</div>
							<div class="eventer-ticket-printable-bottom">';
$ticket_modal_show .=                $ticket_bottom_area;
$ticket_modal_show .=       '</div>
					</div>
				</div>
			</div>
	</div>';

echo $ticket_modal_show;
/*Ticket modal popup End*/
