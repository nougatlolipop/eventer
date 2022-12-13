<?php
$elocation = $organizer_name = $organizer_email = $organizer_details = $organizer_phone = $organizer_website = $organizer_events = '';

if(get_post_type($registrant)=='shop_order' && $woocommerce_ticketing=='on')
{
   $registrant_uname = get_post_meta($registrant, '_shipping_first_name', true).' '.get_post_meta($registrant, '_shipping_last_name', true);
   $registrant_uname = ($registrant_uname!='')?$registrant_uname:get_post_meta($registrant, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);
	$registrant_email = get_post_meta($registrant, '_billing_email', true);
	$booked_registrant_tickets = array();
	$order = wc_get_order( $registrant );
   $order_status = $order->get_status();
	foreach ($order->get_items() as $item_key => $item_values):
		$item_data = $item_values->get_data();
		$item_id = $item_values->get_id();
		$event_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
		$product_name = $item_data['name'];
		if($event_id!=get_the_ID()) continue;
		$quantity = $item_data['quantity'];
		$booked_registrant_tickets[] = array('name'=>$product_name, 'number'=>$quantity);
	endforeach;
	$order_num = 'we'.$registrant;
	$mode = ($order_status=='completed')?'Free':'';
	$registrant_position = $reg_position;
}
else
{
    $registrant_uname = ($username)?$username:'';
	$registrant_email = $registrant_email;
	$booked_registrant_tickets = ($booked_registrant_tickets)?$booked_registrant_tickets:array();
	$order_num = sprintf('%06d', $registrant);
	$mode = $mode;
	$registrant_position = $reg_position;
}
$eventer_organizer = get_the_terms(get_the_ID(), 'eventer-organizer');
$eventer_venue = get_the_terms(get_the_ID(), 'eventer-venue');
$usersystem = unserialize($usersystem);
$time_slot_set = (isset($usersystem['time_slot']))?$usersystem['time_slot']:'00:00:00';
$event_time_show = ($time_slot_set=='00:00:00')?$event_time_show:date_i18n(get_option('time_format'), strtotime($time_slot_set));
if(!is_wp_error($eventer_venue)&&!empty($eventer_venue))
{
	foreach($eventer_venue as $venue)
	{
		$location_address = get_term_meta($venue->term_id, 'venue_address', true);
		$elocation = ($location_address!='')?$location_address:$venue->name;
	}
}
if(!is_wp_error($eventer_organizer)&&!empty($eventer_organizer))
{
	foreach($eventer_organizer as $organizer)
	{
		$organizer_details = "1";
		$organizer_name = $organizer->name;
		$organizer_email = get_term_meta($organizer->term_id, 'organizer_email', true);
		$organizer_phone = get_term_meta($organizer->term_id, 'organizer_phone', true);
		$organizer_website = get_term_meta($organizer->term_id, 'organizer_website', true);
		$organizer_events = get_term_link($organizer->term_id, 'eventer-organizer');
	}
}
$event_time_show = ($event_time_show)?$event_time_show:'';
$thanks_content = ' <div class="eventer-ticket-confirmation-info">
                           <label>'.esc_html__('Event', 'eventer').'</label>
                           <h3>'.apply_filters('eventer_raw_event_title', '', get_the_ID()).'</h3>
                           <div class="eventer-row">';
if($elocation!='') 
{
	$thanks_content .=         '<div class="eventer-col5">
                                 <label>'.esc_html__('Venue Location', 'eventer').'</label>
                                 <p>'.esc_attr($elocation).'</p>
                                 <!--<a href="#">Get directions</a>-->
                              </div>';
}
$thanks_content .=         '<div class="eventer-col5">
                                 <label>'.esc_html__('Date', 'eventer').' &amp; '.esc_html__('Time', 'eventer').'</label>
                                 <p>'.esc_attr($event_time_show).'<br>'.esc_attr(date_i18n(get_option('date_format'), $event_cdate)).'</p>
                                 <!--<a href="#">Add to calendar</a>-->
                              </div>
                           </div>
                           <div class="eventer-spacer-30"></div>
                           <div class="eventer-row">
                              <div class="eventer-col5">
                                 <label>'.esc_html__('Order', 'eventer').' #</label>
                                 <p>'.esc_attr($order_num).'</p>
                              </div>';
if($organizer_details=="1") 
{
	$thanks_content .=      '<div class="eventer-col5">
                                 <label>'.esc_html__('Organizer', 'eventer').'</label>
                                 <p>'.esc_attr($organizer_name).'</p>
                                 <p>'.esc_attr($organizer_phone).'</p>
                                 <p>'.esc_attr($organizer_email).'</p>
                                 <p>'.esc_attr($organizer_website).'</p>
                                 <a href="'.esc_url($organizer_events).'">'.esc_html__("Organizer's other events", "eventer").'</a>
                              </div>';
}
$thanks_content .=        '</div>
                           <div class="eventer-spacer-30"></div>';
                           
if(!empty($booked_registrant_tickets))
{
   $thanks_content .= '<label>'.esc_html__('Booked Tickets', 'eventer').'</label>';
	foreach($booked_registrant_tickets as $reg_ticket)
	{
      if($reg_ticket['name']=='') continue;
		$thanks_content .= '<p>'.esc_attr($reg_ticket['name']).' x <strong>'.esc_attr($reg_ticket['number']).'</strong></p>';
	}
}
$thanks_content .=       '<div class="eventer-ticket-confirmation-footer">';
if(($mode=="Free" || $registrant_position>=15) && (!empty($booked_registrant_tickets)))
{
   $thanks_content .=      '<button class="eventer-get-ticket eventer-btn eventer-btn-default pull-right eventer-get-ticket-modal"  type="submit">'.esc_html__('View ticket', 'eventer').'</button>';
   $thanks_content .=         '<label>'.esc_html__('Tickets sent to', 'eventer').':</label>
                              <p>'.esc_attr($registrant_email).'</p>';
}
elseif(!empty($booked_registrant_tickets))
{
   $thanks_content .= '<p>'.esc_html__('An email with link to download ticket will be sent to you once we acknowledge successful payment.', 'eventer').'</p>';
}

$thanks_content .=      '</div>
                        </div>';
$thanks_body = '<div class="eventer-row equah">
                     <div class="eventer-ticket-confirmation-left eventer-col4 eventer-col10-xs equah-item">
                        <div style="">
                           <div>
                              <div class="equah-item">
                                 <span>'.esc_html__('Thank', 'eventer').' <em>'.esc_html__('you', 'eventer').'</em> '.esc_html__('Kindly', 'eventer').'</span>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="eventer-ticket-confirmation-right eventer-col6 eventer-col10-xs equah-item">
                        <div class="eventer-toggle-area">';
$thanks_body .=         $thanks_content;
$thanks_body .=      '</div>
                     </div>
                  </div>';
$thanks_modal_show = '<div class="eventer eventer-event-single eventer-modal-static" id="eventer-ticket-confirmation">
			<div class="eventer-modal-body">';
$thanks_modal_show .= $thanks_body;
$thanks_modal_show .= '</div>
                </div>';
echo $thanks_modal_show;