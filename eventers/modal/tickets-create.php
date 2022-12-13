<?php
if($woocommerce_ticketing != 'on' && $registrant)
{
	$back_order_tickets = (isset($_REQUEST['backorder']))?wp_get_referer():'';
	$default = array();
	$new_tickets = apply_filters('eventer_preapare_data_for_tickets', 'id', $registrant, array('event'=>$event_id));
	$new_tickets['data-regpos'] = $reg_position;
	$new_tickets['data-backorder'] = $back_order_tickets;
	$new_tickets['default']['data-eid'] = $event_id;
	$new_tickets['default']['data-regpos'] = $reg_position;
	$new_tickets['default']['data-registrant'] = $registrant;
	do_action('eventer_ticket_raw_design', '', $new_tickets);
	
}