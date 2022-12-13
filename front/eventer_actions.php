<?php
function eventer_show_title($title = '', $id = '')
{
  $event_cdate = get_query_var('edate');
  if (!$event_cdate) {
    return $title;
  }
  global $wpdb;
  $table_name = $wpdb->prefix . "eventer_tickets";
  $dynamic_details = $wpdb->get_row("SELECT * FROM $table_name WHERE `event` = $id AND `date` = '$event_cdate 00:00:00'");
  if (!$dynamic_details) {
    return $title;
  }
  $badge = $dynamic_details->label;
  $passed_badge = (date_i18n('Y-m-d', strtotime($event_cdate)) < date_i18n('Y-m-d')) ? '<span class="eventer-status-badge eventer-status-passed">' . esc_html__('Passed', 'eventer') . '</span>' : '';
  $title .= $passed_badge;
  $title .= ($badge != '' && eventer_get_settings('eventer_show_badges') == 'on') ? ' <span class="eventer-status-badge">' . $badge . '</span>' : '';
  return $title;
}
add_filter('the_title', 'eventer_show_title', 10, 3);

function eventer_show_raw_title($raw_title = '', $id = '')
{
  $raw_title = get_the_title($id);
  return $raw_title;
}
add_filter('eventer_raw_event_title', 'eventer_show_raw_title', 10, 2);

function eventer_content_before_title($content = '', $id = '')
{
  return $content;
}
add_filter('eventer_content_before_title', 'eventer_content_before_title', 10, 2);

function eventer_content_after_title($content = '', $id = '')
{
  return $content;
}
add_filter('eventer_content_after_title', 'eventer_content_after_title', 10, 2);

function eventer_show_title_listing($title = '', $id = '', $all_data = array())
{
  $event_cdate = (isset($all_data['event_cdate'])) ? $all_data['event_cdate'] : date_i18n('U');
  $booked_tickets_create = (!empty($all_data) && isset($all_data['booked_tickets'])) ? $all_data['booked_tickets'] : '';
  $booked_tickets_status = (!empty($booked_tickets_create) && isset($booked_tickets_create[0]['label']) && $booked_tickets_create[0]['label'] != '') ? '<span class="eventer-status-badge">' . $booked_tickets_create[0]['label'] . '</span>' : '';
  $recurring_icon_switch = (!empty($all_data) && isset($all_data['recurring'])) ? $all_data['recurring'] : '';
  $badge_switch = (!empty($all_data) && isset($all_data['label'])) ? $all_data['label'] : '';
  $all_dates = (!empty($all_data) && isset($all_data['all_dates'])) ? $all_data['all_dates'] : array();
  $recurring_icon = (($recurring_icon_switch == "on" && count($all_dates) > 1)) ? ' <i class="eventer-icon-refresh"></i>' : '';

  $passed_badge = ($badge_switch == "on" && date_i18n('Y-m-d', $event_cdate) < date_i18n('Y-m-d')) ? '<span class="eventer-status-badge eventer-status-passed">' . esc_html__('Passed', 'eventer') . '</span>' : '';
  $title .= '<span class="eventer-event-title">';
  $title .= apply_filters('eventer_content_before_title', $content = '', $id);
  $title .= apply_filters('eventer_raw_event_title', $title, $id);
  $title .= $recurring_icon . ' ' . $passed_badge;
  $title .= $booked_tickets_status;
  $title .= apply_filters('eventer_content_after_title', $content = '', $id);
  $title .= '</span>';
  return $title;
}
add_filter('eventer_styled_listing_title', 'eventer_show_title_listing', 10, 3);
//add_action ('template_redirect', 'eventer_custom_permalink_redirect', 9999);

function eventer_custom_permalink_redirect()
{
  global $post;
  if (is_singular('eventer')) {
    $external_link =  get_post_meta($post->ID, 'eventer_event_custom_permalink', true);
    if ($external_link) {
      if (!wp_script_is('jquery', 'done')) {
        wp_enqueue_script('jquery');
      }
      wp_add_inline_script('jquery-migrate', 'window.location.href="' . esc_url($external_link) . '"');
    }
  }
}

function eventer_event_permalink_setup($create_link = '', $URL = '', $id = '')
{

  $eventer_url = get_post_meta($id, 'eventer_event_custom_permalink', true);
  $URL = (empty($eventer_url)) ? $URL : $eventer_url;
  $create_link = (empty($create_link)) ? $URL : $create_link;
  return $create_link;
}
add_filter('eventer_permalink_setup', 'eventer_event_permalink_setup', 10, 3);

function eventer_formatting_email_content($content = '', $registrant_details = array(), $email_content = '')
{
  if (empty($registrant_details) || empty($email_content)) return '';
  $elocation = $organizer_name = $organizer_phone = $organizer_website = $registrant_name1 = $registrant_email1 = $registrant_name2 = $registrant_email2 = $registrant_name3 = $registrant_email3 = $registrant_name4 = $registrant_email4 = $registrant_name5 = $registrant_email5 = $registrant_name6 = $registrant_email6 = $registrant_name7 = $registrant_email7 = $registrant_name8 = $registrant_email8 = $registrant_name9 = $registrant_email9 = $registrant_name10 = $registrant_email10 = '';
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
  $eventer_organizer = get_the_terms($eventer_id, 'eventer-organizer');
  $eventer_venue = get_the_terms($eventer_id, 'eventer-venue');
  if (!is_wp_error($eventer_venue) && !empty($eventer_venue)) {
    foreach ($eventer_venue as $venue) {
      $location_address = get_term_meta($venue->term_id, 'venue_address', true);
      $elocation = ($location_address != '') ? $location_address : $venue->name;
    }
  }
  if (!is_wp_error($eventer_organizer) && !empty($eventer_organizer)) {
    foreach ($eventer_organizer as $organizer) {
      $organizer_name = $organizer->name;
      $organizer_email = get_term_meta($organizer->term_id, 'organizer_email', true);
      $organizer_phone = get_term_meta($organizer->term_id, 'organizer_phone', true);
      $organizer_website = get_term_meta($organizer->term_id, 'organizer_website', true);
    }
  }
  $event_start_date = get_post_meta($eventer_id, 'eventer_event_start_dt', true);
  $event_end_date = get_post_meta($eventer_id, 'eventer_event_end_dt', true);
  $start_date_string = strtotime($event_start_date);
  $end_date_string = strtotime($event_end_date);
  if (date_i18n('Y-m-d', $start_date_string) != date_i18n('Y-m-d', $end_date_string)) {
    $eventer_date_formatted = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $start_date_string) . ' - ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $end_date_string);
  }
  $eventer_time_formatted = date_i18n(get_option('time_format'), strtotime($event_start_date));
  $payment_status = $registrant_details->status;
  $user_details = $registrant_details->user_details;
  $paymentmode = $registrant_details->paymentmode;
  $registrant_user_system = $registrant_details->user_system;
  $registrant_user_system = unserialize($registrant_user_system);
  $services_list = '';
  $services = (isset($registrant_user_system['services'])) ? $registrant_user_system['services'] : [];
  $event_services = [];
  if ($services) {
    foreach ($services as $service) {
      $pid = $service['pid'];
      $value = $service['value'];
      $event_services['{' . $pid . '}'] = $value;
      $services_list .= '<p>' . $pid . ': ' . $value . '</p>';
    }
  }
  $registrant_time_slot = (isset($registrant_user_system['slot_title'])) ? $registrant_user_system['slot_title'] : '';
  $registrants = (isset($registrant_user_system['registrants'])) ? $registrant_user_system['registrants'] : array();
  if (!empty($registrants)) {
    $counter_reg = 1;
    foreach ($registrants as $key => $value) {
      foreach ($value as $valnew) {
        ${'registrant_name' . $counter_reg} = $valnew['name'];
        ${'registrant_email' . $counter_reg} = $valnew['email'];
        $counter_reg++;
      }
    }
  }
  $organizer = wp_get_object_terms($eventer_id, 'eventer-organizer');
  $organizer_email = $completed_url_tkt = $pending_url_tkt = $failed_url_tkt = '';
  //Getting all tickets data that registrant selected
  $reg_tickets = unserialize($tickets);
  $tickets = '';
  $user_fields_val = $registrant_tickets_name = $registrant_tickets_vals = array();
  if (!empty($reg_tickets)) {
    $ticket_start = 1;
    foreach ($reg_tickets as $ticket) {
      if (isset($ticket['number']) && $ticket['number'] != '') {
        $tickets .= $ticket['name'] . ' X ' . $ticket['number'] . '<br/>';
      }
      $registrant_tickets_name['{ticket' . $ticket_start . '}'] = $ticket['name'];
      $registrant_tickets_vals['{ticket_nos' . $ticket_start . '}'] = $ticket['number'];
      $ticket_start++;
    }
  }
  $user_info = '';
  if (!empty($user_details)) {
    //Getting all user details that user fills before selecting event tickets
    $user_details = unserialize($user_details);
    foreach ($user_details as $details) {
      if ($details['name'] == 'quantity_tkt' || $details['value'] == 'chosen-payment-option') continue;
      $user_fields_val['{' . $details['name'] . '}'] = $details['value'];
      $user_info .= '<p>' . esc_attr($details['name']) . ': ' . esc_attr($details['value']) . '</p>';
    }
  }
  $message_dynamic = $email_content;
  //This switch checks payment mode and filter shortcodes of email content for respective payment mode
  //This will remoe all shortcodes except the selected mode
  switch ($paymentmode) {
    case 'Free':
      remove_shortcode('eventer_paid');
      remove_shortcode('eventer_offline');
      $content = do_shortcode($message_dynamic);
      $registration_content_new = preg_replace('#\[[^\]]+\]#', '', $content);
      break;
    case 'Offline':
      remove_shortcode('eventer_paid');
      remove_shortcode('eventer_free');
      $content = do_shortcode($message_dynamic);
      $registration_content_new = preg_replace('#\[[^\]]+\]#', '', $content);
      break;
    default:
      remove_shortcode('eventer_offline');
      remove_shortcode('eventer_offline');
      $content = do_shortcode($message_dynamic);
      $registration_content_new = preg_replace('#\[[^\]]+\]#', '', $content);
      break;
  }
  //Generate unique number using registration ID
  //We here using $start =8 and $end = 9, so that user can see tickets when receiving this email
  $registration_unique_number = eventer_encode_security_registration($registrant_id, 8, 9);
  if ($payment_status == "Completed" || $payment_status == "completed") {
    $completed_url_tkt = eventer_generate_endpoint_url('edate', $eventer_date, get_permalink($eventer_id));
    $completed_url_tkt = add_query_arg(array('reg' => $registration_unique_number), $completed_url_tkt);
  }
  if ($payment_status == "Pending" || $payment_status == "pending") {
    $pending_url_tkt = 'pending';
  } elseif ($payment_status == "Failed" || $payment_status == "failed") {
    $failed_url_tkt = 'failed';
  }
  $eventer_currency = eventer_get_currency_symbol(eventer_get_settings('eventer_paypal_currency'));
  //We are here changing provided codes to the dynamic data of registrants
  $vars = array(
    '{venue}'         => $elocation,
    '{services}' => $services_list,
    '{organizer_name}' => $organizer_name,
    '{organizer_email}' => $organizer_email,
    '{registrant_name1}' => $registrant_name1,
    '{registrant_email1}' => $registrant_email1,
    '{registrant_name2}' => $registrant_name2,
    '{registrant_email2}' => $registrant_email2,
    '{registrant_name3}' => $registrant_name3,
    '{registrant_email3}' => $registrant_email3,
    '{registrant_name4}' => $registrant_name4,
    '{registrant_email4}' => $registrant_email4,
    '{registrant_name5}' => $registrant_name5,
    '{registrant_email5}' => $registrant_email5,
    '{registrant_name6}' => $registrant_name6,
    '{registrant_email6}' => $registrant_email6,
    '{registrant_name7}' => $registrant_name7,
    '{registrant_email7}' => $registrant_email7,
    '{registrant_name8}' => $registrant_name8,
    '{registrant_email8}' => $registrant_email8,
    '{registrant_name9}' => $registrant_name9,
    '{registrant_email9}' => $registrant_email9,
    '{registrant_name10}' => $registrant_name10,
    '{registrant_email10}' => $registrant_email10,
    '{organizer_phone}' => $organizer_phone,
    '{organizer_website}' => $organizer_website,
    '{reg_id}'         => esc_attr($registrant_id),
    '{tx_id}'         => esc_attr($transaction_id),
    '{pmt_st}'         => esc_attr($payment_status),
    '{reg_email}'     => $registrant_email,
    '{amt_pd}'         => esc_attr($eventer_currency . $amount),
    '{evt_date}'         => esc_attr($eventer_date_formatted),
    '{evt_time}'      => esc_attr($eventer_time_formatted),
    '{evt_title}'         => apply_filters('eventer_raw_event_title', '', $eventer_id),
    '{evt_url}'       => esc_url(eventer_generate_endpoint_url('edate', $eventer_date, get_permalink($eventer_id))),
    '{event_additional_info}' => get_post_meta($eventer_id, 'eventer_event_email_additional_info', true),
    '{tkt}'           => $tickets,
    '{time_slot_title}'     => $registrant_time_slot,
    '{user_details}'  => $user_info,
    '{completed}'     => $completed_url_tkt,
    '{pending}'       => $pending_url_tkt,
    '{failed}'         => $failed_url_tkt,
  );
  $new_vars = array_merge($vars, $user_fields_val, $registrant_tickets_name, $registrant_tickets_vals, $event_services);
  $message = strtr($registration_content_new, $new_vars);
  $start = '{';
  $end = '}';

  $pattern = sprintf(
    '/%s(.+?)%s/ims',
    preg_quote($start, '/'),
    preg_quote($end, '/')
  );

  if (preg_match_all($pattern, $message, $matches)) {
    $match_found = $matches[0];
  }
  if ($match_found) {
    foreach ($match_found as $match) {
      $message = str_replace($match, '', $message);
    }
  }
  $content = $message;
  return $content;
}
add_filter('eventer_filter_email_content_body', 'eventer_formatting_email_content', 10, 3);

function eventer_formatting_woo_email_content($content = '', $order_id = '', $email_content = '')
{
  if (empty($email_content) && $order_id != '') {
    $event_trs = $venue_image = '';
    $order = wc_get_order($order_id);
    $order_items_start = 1;
    $organizer_email = $elocation = '';
    $registrant_uname = get_post_meta($order_id, '_billing_first_name', true) . ' ' . get_post_meta($order_id, '_billing_last_name', true);
    $registrant_email = get_post_meta($order_id, '_billing_email', true);
    foreach ($order->get_items() as $item_key => $item_values) :
      $item_data = $item_values->get_data();
      $item_id = $item_values->get_id();
      $product_id = $item_data['product_id'];
      if (!has_term('eventer', 'product_cat', $product_id)) continue;
      $event_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
      $event_time = get_post_meta($event_id, 'eventer_event_start_dt', true);
      $event_time = date_i18n(get_option('time_format'), strtotime($event_time));
      $event_date = wc_get_order_item_meta($item_id, '_wceventer_date', true);
      $event_date = strtotime($event_date);
      $product_name = $item_data['name'];
      $quantity = $item_data['quantity'];
      $order_num = 'we' . $order_id . '-' . $order_items_start;
      $eventer_organizer = get_the_terms($event_id, 'eventer-organizer');
      $eventer_venue = get_the_terms($event_id, 'eventer-venue');
      if (!is_wp_error($eventer_venue) && !empty($eventer_venue)) {
        foreach ($eventer_venue as $venue) {
          $location_address = get_term_meta($venue->term_id, 'venue_address', true);
          $elocation = ($location_address != '') ? $location_address : $venue->name;
          $venue_image = get_term_meta($venue->term_id, 'venue_image', true);
          if ($venue_image) {
            $image_src = wp_get_attachment_image_src($venue_image, 'full');
            $venue_image = $image_src[0];
          }
        }
      }
      if (!is_wp_error($eventer_organizer) && !empty($eventer_organizer)) {
        foreach ($eventer_organizer as $organizer) {
          $organizer_email = get_term_meta($organizer->term_id, 'organizer_email', true);
        }
      }
      $event_trs .= '<tr>
				<td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0;
					padding-top: 30px;
					padding-right: 20px;"><img
				border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;
					color: #000000;"
					src="' . get_the_post_thumbnail_url($event_id, 'medium') . '"
					alt="H" title="Highly compatible"
					width="100" height="100"></td>
				<td align="left" valign="top" style="font-size: 17px; font-weight: 400; line-height: 160%; border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;
					padding-top: 25px;
					color: #000000;
					font-family: sans-serif;" class="paragraph">
						<b style="color: #333333;">' . get_the_title($event_id) . '</b><br/>
						' . esc_attr($product_name) . ' X ' . esc_attr($quantity) . '<br/>
						' . esc_html__('Event Date', 'eventer') . ': ' . esc_attr(date_i18n(get_option('date_format'), $event_date)) . '<br/>
						' . (($organizer_email != '') ? esc_html__('Contact Manager', 'eventer') . ': ' . $organizer_email . '<br/>' : '') . '
                  ' . (($elocation != '') ? esc_html__('Location', 'eventer') . ': ' . $elocation . '<br/>' : '') . '
				</td>

			</tr>';
    endforeach;
    $email_content = '<html xmlns="http://www.w3.org/1999/xhtml">
                           <head>
                              <meta name="viewport" content="width=device-width, initial-scale=1.0;">
                              <meta name="format-detection" content="telephone=no"/>
                              <style>
                                 body { margin: 0; padding: 0; min-width: 100%; width: 100% !important; height: 100% !important;}
                                 body, table, td, div, p, a { -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%; }
                                 table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; border-spacing: 0; }
                                 img { border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
                                 #outlook a { padding: 0; }
                                 .ReadMsgBody { width: 100%; } .ExternalClass { width: 100%; }
                                 .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
                                 @media all and (min-width: 560px) {
                                 .container { border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -khtml-border-radius: 8px;}
                                 }

                                 /* Set color for auto links (addresses, dates, etc.) */ 
                                 a, a:hover {
                                    color: #127DB3;
                                 }
                                 .footer a, .footer a:hover {
                                    color: #999999;
                                 }

                              </style>
                           </head>
                           <body topmargin="0" rightmargin="0" bottommargin="0" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
                              background-color: #F0F0F0;
                              color: #000000;"
                              bgcolor="#F0F0F0"
                              text="#000000">
                              <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%;" class="background"><tr><td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;"
                                 bgcolor="#F0F0F0">
                                 <table border="0" cellpadding="0" cellspacing="0" align="center"
                                    bgcolor="#FFFFFF"
                                    width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
                                    max-width: 560px;" class="container">
                                    <tr>
                                       <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 24px; font-weight: bold; line-height: 130%;
                                          padding-top: 25px;
                                          color: #000000;
                                          font-family: sans-serif;" class="header">
                                          ' . esc_html__('Here is your order details', 'eventer') . '
                                       </td>
                                    </tr>
                                    <tr>
                                       <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-bottom: 3px; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 18px; font-weight: 300; line-height: 150%;
                                          padding-top: 5px;
                                          color: #000000;
                                          font-family: sans-serif;" class="subheader">
                                          ' . esc_html__('Welcome to', 'eventer') . ' ' . get_bloginfo('name') . '
                                       </td>
                                    </tr>
									<tr>
										<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;
											padding-top: 20px;" class="hero"><img border="0" vspace="0" hspace="0"
											src="' . esc_url($venue_image) . '"
											alt="" title="Event Image"
											width="560" style="
											width: 100%;
											max-width: 560px;
											color: #000000; font-size: 13px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;"/></td>
									</tr>
									<tr>
										<td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
											padding-top: 25px; 
											color: #000000;
											font-family: sans-serif;" class="paragraph">
											' . esc_html__('Please see order details below and make sure that every details are correct as you filled while registering for event.', 'eventer') . '<br/>
											Registrant: ' . $registrant_uname . '<br/>
											Order ID: we' . $order_id . '<br/>
										 Email: ' . $registrant_email . '<br/>

										</td>
									</tr>
									<tr>	
										<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
											padding-top: 25px;" class="line"><hr
											color="#E0E0E0" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
										</td>
									</tr>

									<!-- LIST -->
									<tr>
										<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%;" class="list-item">
											<table align="left" border="0" cellspacing="0" cellpadding="0" style="width: inherit; margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">

											<!-- LIST ITEM -->
											' . $event_trs . '

										</table></td>
									</tr>
									<tr>
										<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
											padding-top: 25px;" class="line"><hr
											color="#E0E0E0" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
										</td>
									</tr>
									<tr>
										<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
											padding-top: 20px;
											padding-bottom: 25px;
											color: #000000;
											font-family: sans-serif;" class="paragraph">
												Have a&nbsp;question? <a href="mailto:' . get_option('admin_email') . '" target="_blank" style="color: #127DB3; font-family: sans-serif; font-size: 17px; font-weight: 400; line-height: 160%;">' . get_option('admin_email') . '</a>
										</td>
									</tr>

								<!-- End of WRAPPER -->
								</table>
								<table border="0" cellpadding="0" cellspacing="0" align="center"
									width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
									max-width: 560px;" class="wrapper">

									<!-- FOOTER -->
									<!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
									<tr>
										<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 13px; font-weight: 400; line-height: 150%;
											padding-top: 20px;
											padding-bottom: 20px;
											color: #999999;
											font-family: sans-serif;" class="footer">

												' . esc_html__('Thanks for registering event at our website', 'eventer') . ' ' . esc_attr(get_bloginfo('name')) . ', ' . esc_html__('we will be very happy to see you in event.', 'eventer') . '

										</td>
									</tr>

								<!-- End of WRAPPER -->
								</table>

								<!-- End of SECTION / BACKGROUND -->
								</td></tr></table>

								</body>
								</html>';
    return $email_content;
  }
  if (empty($order_id) || empty($email_content)) return '';
  $registrant_uname = get_post_meta($order_id, '_billing_first_name', true) . ' ' . get_post_meta($order_id, '_billing_last_name', true);
  $registrant_email = get_post_meta($order_id, '_billing_email', true);
  $booked_registrant_tickets = array();
  $order = wc_get_order($order_id);
  $generate_ticket_url = '';
  foreach ($order->get_items() as $item_key => $item_values) :
    $item_data = $item_values->get_data();
    $item_id = $item_values->get_id();
    $event_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
    $product_name = $item_data['name'];
    $product_id = $item_values->get_product_id();
    if (!has_term('eventer', 'product_cat', $product_id)) continue;
    //if($event_id!=get_the_ID()) continue;
    $quantity = $item_data['quantity'];
    $booked_registrant_tickets[] = array('name' => $product_name, 'number' => $quantity);
    $generate_dynamic_order_id = eventer_encode_security_registration($order_id, 9, 9);
    $eventer_url = wc_get_order_item_meta($item_id, 'Event URL', true);
    $generate_ticket_url .= esc_url(add_query_arg('reg', $generate_dynamic_order_id, $eventer_url)) . '<br/>';
  endforeach;
  $user_fields_val = $registrant_tickets_name = $registrant_tickets_vals = array();
  $order_num = 'we' . $order_id;
  $tickets = '';
  if (!empty($booked_registrant_tickets)) {
    $ticket_start = 1;
    foreach ($booked_registrant_tickets as $ticket) {
      if (isset($ticket['number']) && $ticket['number'] != '') {
        $tickets .= $ticket['name'] . ' X ' . $ticket['number'] . '<br/>';
      }
      $registrant_tickets_name['{ticket' . $ticket_start . '}'] = $ticket['name'];
      $registrant_tickets_vals['{ticket_nos' . $ticket_start . '}'] = $ticket['number'];
      $ticket_start++;
    }
  }
  $event_date = wc_get_order_item_meta($item_id, '_wceventer_date', true);
  $event_date = strtotime($event_date);
  $eventer_date_formatted = date_i18n(get_option('date_format'), $event_date);
  $message_dynamic = $email_content;
  $eventer_date = date_i18n('Y-m-d', $event_date);
  $registrants_identity = eventer_get_registrant_details('eventer', $order_id);
  //We are here changing provided codes to the dynamic data of registrants
  $vars = array(
    '{reg_id}'         => esc_attr($registrants_identity->id),
    '{tx_id}'         => esc_attr(get_post_meta($order_id, '_order_key', true)),
    '{pmt_st}'         => esc_attr($order->get_status()),
    '{reg_email}'     => $registrant_email,
    '{amt_pd}'         => esc_attr(get_post_meta($order_id, '_order_currency', true) . get_post_meta($order_id, '_order_total', true)),
    '{evt_date}'         => esc_attr($eventer_date_formatted),
    '{evt_title}'         => apply_filters('eventer_raw_event_title', '', $event_id),
    '{evt_url}'       => esc_url(eventer_generate_endpoint_url('edate', $eventer_date, get_permalink($event_id))),
    '{tkt}'           => $tickets,
    '{evt_tkt}'           => $generate_ticket_url,
  );
  $new_vars = array_merge($vars, $registrant_tickets_name, $registrant_tickets_vals);
  $message = strtr($message_dynamic, $new_vars);
  $content = $message;
  return $content;
}
add_filter('eventer_filter_woo_email_content_body', 'eventer_formatting_woo_email_content', 10, 3);

function eventer_set_dynamic_fields($field = '', $type = 0, $section = 'custom_section1', $form = '', $form_data)
{
  $form_options = $form_data;
  $form_options = (empty($form_options)) ? array() : $form_options;
  $current_form_details = (isset($form_options[$form])) ? $form_options[$form] : '';
  $disabled = $mandatory = $disabled_check = $mandatory_check = $section_position = $dynamic_fields = $dynamic = $sections = array();
  if (!empty($current_form_details)) {
    $disabled = (isset($current_form_details['disabled']) && !empty($current_form_details['disabled'])) ? $current_form_details['disabled'] : array();
    $mandatory = (isset($current_form_details['mandatory']) && !empty($current_form_details['mandatory'])) ? $current_form_details['mandatory'] : array();
    $dynamic = (isset($current_form_details['dynamic']) && !empty($current_form_details['dynamic'])) ? $current_form_details['dynamic'] : array();
    $sections = (isset($current_form_details['sections']) && !empty($current_form_details['sections'])) ? $current_form_details['sections'] : array();
  }
  if (!empty($dynamic)) {
    $section_field = (isset($dynamic[$section]['fields'])) ? $dynamic[$section]['fields'] : array();
    if (!empty($section_field)) {
      foreach ($section_field as $fields) {
        $row_status = ($fields['status'] == '') ? 'disable' : $fields['status'];
        $row_switch_class = ($fields['status'] == '' || $fields['status'] == 'enable') ? 'enable' : 'disable';
        $row_disabled_class = ($fields['status'] == '' || $fields['status'] == 'enable') ? '' : ' eventer-fe-disabled-row';
        $row_switch_string = ($row_switch_class == 'enable') ? esc_html__('Disable row', 'eventer') : esc_html__('Enable row', 'eventer');
        if (isset($fields['status']) && $fields['status'] == 'disable' && $type != 0) continue;
        $class = ($type == 0) ? 'eventer-fn-field eventer-dynamic_area-action' : '';
        $field .= '<div class="eventer-row ' . esc_attr($class . $row_disabled_class) . '">';
        foreach ($fields['shorts'] as $set_field) {
          $array = array();
          preg_match('/name="([^"]*)"/i', $set_field['shortcode'], $array);
          $name = (isset($array[1])) ? $array[1] : '';
          $array = array();
          preg_match('/type="([^"]*)"/i', $set_field['shortcode'], $array);
          $field_type = (isset($array[1])) ? $array[1] : '';
          $array = array();
          preg_match('/meta_key="([^"]*)"/i', $set_field['shortcode'], $array);
          $field_meta_key = (isset($array[1])) ? $array[1] : '';
          $field .= '<div class="eventer-col' . esc_attr($set_field['column']) . ' eventer-col10-xs eventer_dynamic_meta_field eventer_fn_field_col" data-column="' . esc_attr($set_field['column']) . '">';
          $field .= ($type == 0) ? '<input type="hidden" placeholder="' . esc_html__('Place field shortcode', 'eventer') . '" data-id="' . esc_attr($set_field['id']) . '" value="' . esc_attr($set_field['shortcode']) . '">' : do_shortcode($set_field['shortcode']);
          $field .= ($type == 0) ? '<div class="eventer eventer-fe-builder-ele"><span class="eventer-fe-ele-icon"></span><span class="eventer-fe-ele-title">' . esc_attr($name) . '</span><span class="eventer-fe-ele-value">' . esc_attr($field_type . '/' . $field_meta_key) . '</span><a title="' . esc_html__('Copy', 'eventer') . '" class="eventer-fe-ele-copy"></a><a title="' . esc_html__('Paste', 'eventer') . '" class="eventer-fe-ele-paste eventer_disabled_link"></a><a title="' . esc_html__('Settings', 'eventer') . '" class="eventer-fe-ele-settings"></a></div>' : '';
          $field .= '</div>';
        }

        $field .= ($type == 0) ? '<div class="eventer-fn-actions"><a href="javascript:void();" class="eventer-row-switch eventer-fn-act-' . esc_attr($row_switch_class) . ' eventer-settings-action" data-key="' . esc_attr($row_status) . '" title="' . esc_attr($row_switch_string) . '"><i class="eventer-icon-eye"></i></a><a href="javascript:void();" class="eventer-fn-act-delete eventer-settings-action" data-key="remove" data-status="0" title="' . esc_html__('Remove Row', 'eventer') . '"><i class="eventer-icon-close"></i></a></div>' : '';
        $field .= '</div>';
      }
    }
  }
  return $field;
}
add_filter('eventer_set_dynamic_field', 'eventer_set_dynamic_fields', 10, 5);

function eventer_set_dynami_fields_btn($buttons = '', $type = 0, $section = '', $form = '', $form_data)
{
  if ($type == 1) return $buttons;
  $form_options = $form_data;
  $form_options = (empty($form_options)) ? array() : $form_options;
  $current_form_details = (isset($form_options[$form])) ? $form_options[$form] : '';
  $dynamic = array();
  if (!empty($current_form_details)) {
    $dynamic = (isset($current_form_details['dynamic']) && isset($current_form_details['dynamic'][$section]) && !empty($current_form_details['dynamic'])) ? $current_form_details['dynamic'][$section] : array();
  }
  $type = (!empty($dynamic) && isset($dynamic['type'])) ? $dynamic['type'] : '';
  $btn = (!empty($dynamic) && isset($dynamic['btn'])) ? $dynamic['btn'] : '';
  $buttons .= '<div class="eventer-row eventer_form_action_btns">';
  $buttons .= '<div class="eventer-col2">';
  $buttons .= '<div class="eventer-settings-action">';
  $buttons .= '<select class="eventer_add_new_row">';
  $buttons .= '<option value="">' . esc_html__('Add section', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array(10))) . '" data-rows="1" value="">' . esc_html__('Full Column', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array(5, 5))) . '" data-rows="2" value="1">' . esc_html__('1/2 - 1/2', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array(4, 3, 3))) . '" data-rows="3" value="2">' . esc_html__('4/10+3/10+3/10', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array(4, 6))) . '" data-rows="2" value="3">' . esc_html__('4/10+6/10', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array(4, 4, 2))) . '" data-rows="3" value="4">' . esc_html__('4/10+4/10+2/10', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array('1by3', '1by3', '1by3'))) . '" data-rows="3" value="5">' . esc_html__('1/3+1/3+1/3', 'eventer') . '</option>';
  $buttons .= '<option data-cols="' . esc_attr(json_encode(array('1by4', '1by4', '1by4'))) . '" data-rows="4" value="6">' . esc_html__('1/4+1/4+1/4+1/4', 'eventer') . '</option>';
  $buttons .= '</select>';
  $buttons .= '</div>';
  $buttons .= '</div>';

  $buttons .= '<div class="eventer-col2">';
  $buttons .= '<select name="eventer-section-type" class="eventer-section-type eventer-settings-action">';
  $buttons .= '<option value="">Select</option>';
  $buttons .= '<option ' . (($type == 'eventer-organizer') ? 'selected' : '') . ' value="eventer-organizer">Organizer</option>';
  $buttons .= '<option ' . (($type == 'eventer-venue') ? 'selected' : '') . ' value="eventer-venue">Venue</option>';
  $buttons .= '<option ' . (($type == 'tickets') ? 'selected' : '') . ' value="tickets">Tickets</option>';
  $buttons .= '</select>';
  $buttons .= '</div>';

  $buttons .= '<div class="eventer-col2">';
  $buttons .= '<select name="eventer-section-type" class="eventer-section-switch eventer-settings-action">';
  $buttons .= '<option ' . (($btn == 'enabled') ? 'selected' : '') . ' value="enabled">Enabled</option>';
  $buttons .= '<option ' . (($btn == 'disabled') ? 'selected' : '') . ' value="disabled">Disabled</option>';
  $buttons .= '</select>';
  $buttons .= '</div>';
  $buttons .= '<div class="eventer-col2">';
  $buttons .= '</div>';
  $buttons .= '</div>';
  return $buttons;
}
add_filter('eventer_set_dynamic_fields_button', 'eventer_set_dynami_fields_btn', 10, 5);

function eventer_login_form($login = '')
{
  $login = '';
  $login .= '<form class="eventer eventer_login_user eventer-fe-rforms" action="" method="POST">
			<div class="eventer-loader-wrap" style="display: none"><div class="eventer-loader"></div></div>
			<h4>Login</h4>
			<label>' . esc_html__('Username', 'eventer') . '</label>
			<input required type="text" name="eventer-login-username" class="eventer_login_username">
			<label>' . esc_html__('Password', 'eventer') . '</label>
			<input required type="password" name="eventer-login-pass" class="eventer_login_pass"><br/>
			<input type="submit" value="' . esc_html__('Login', 'eventer') . '">
			<p class="eventer-form-info-links pull-right"><a href="#" class="eventer-toggle-area-trigger" data-eventer-toggle-in=".eventer-fe-toggle-form3" data-eventer-toggle-out=".eventer-fe-toggle-form1">' . esc_html__('Forgot Password?', 'eventer') . '</a></p>
		</form>';
  return $login;
}
add_filter('eventer_create_login_form', 'eventer_login_form', 10, 1);

function eventer_populate_manager_data($data = array(), $current_user = '')
{
  wp_set_current_user(get_current_user_id());
  $request = new WP_REST_Request('GET', '/wp/v2/eventer');
  $request->set_param('per_page', -1);
  $response = rest_do_request($request);
  if (!$response->is_error()) { }
}
add_filter('eventer_get_populate_manager_data', 'eventer_populate_manager_data', 10, 2);

function eventer_email_content_format($content = '', $id = '', $user = '')
{
  if ($id == '') return $content;
  $vars = array(
    '{manager_email}'     => $user->user_email,
    '{manager_name}'      => $user->display_name,
    '{event_url}'         => get_permalink($id),
    '{event_title}'       => get_the_title($id),
    '{event_status}'      => get_post_status($id),
  );
  $content = strtr($content, $vars);
  return $content;
}
add_filter('eventer_set_email_content_format', 'eventer_email_content_format', 10, 3);
add_filter('ajax_query_attachments_args', 'eventer_filter_media');

function eventer_filter_media($query)
{
  // admins get to see everything
  if (!current_user_can('manage_options') && !is_admin())
    $query['author'] = get_current_user_id();
  return $query;
}

function eventer_prepare_tickets_data($field, $registrant, $atts)
{
  $reg = eventer_get_registrant_details($field, $registrant);
  $all_data = $individual = $default_payment_reg = $all_events = array();
  $default_all = '';
  if ($reg) {
    $usersystem = $reg->user_system;
    $usersystem = unserialize($usersystem);
    $time_slot = (isset($usersystem['time_slot'])) ? $usersystem['time_slot'] : '';
    $slot_title = (isset($usersystem['slot_title'])) ? $usersystem['slot_title'] : '';
    $user_registrants_list = (!empty($usersystem) && isset($usersystem['registrants'])) ? $usersystem['registrants'] : array();
    $woo_user_registrants_list = (!empty($usersystem) && isset($usersystem['tickets'])) ? $usersystem['tickets'] : array();
    $user_registrants_list = ($field == 'id') ? $user_registrants_list : $woo_user_registrants_list;
    $usertickets = ($field == 'id') ? unserialize($reg->tickets) : $user_registrants_list;
    $normal_registration = (!empty($usertickets)) ? $usertickets : array();
    if (!empty($normal_registration)) {
      foreach ($normal_registration as $normal) {
        if ((isset($normal['number']) && $normal['number'] > 0) || (isset($normal['quantity']) && $normal['quantity'] > 0)) {
          $quantity = ($field == 'id') ? $normal['number'] : $normal['quantity'];
          $ticket = ($field == 'id') ? $normal['name'] : $normal['ticket'];
          $default_payment_reg[$ticket] = $quantity;
          $default_all .= '<p>' . $ticket . ' X ' . $quantity . '</p>';
        }
      }
    }
    if (!empty($user_registrants_list)) {
      foreach ($user_registrants_list as $key => $value) {
        if ((isset($value['type']) && $value['type'] != 'ticket') || ($key == 'main' && $key != 0)) continue;
        $valindex = ($field == 'id') ? $key : $value['ticket'];
        $set_quantity = (isset($value['quantity'])) ? $value['quantity'] : '';
        $get_quantity = (array_key_exists($valindex, $default_payment_reg)) ? $default_payment_reg[$valindex] : $set_quantity;
        $valvalue = ($field == 'id') ? $value : $value['registrants'];
        $event_id = ($field == 'id' && isset($atts['event'])) ? $atts['event'] : $value['event'];
        $event_id = (empty($event_id)) ? $reg->eventer : $event_id;
        $event_time = get_post_meta($event_id, 'eventer_event_start_dt', true);
        $event_end_time = get_post_meta($event_id, 'eventer_event_end_dt', true);
        $date_each_day = get_post_meta($event_id, 'eventer_event_each_day_time', true);
        $days_diff = eventer_dateDiff($event_time, $event_end_time);
        $registered_ticket = $elocation = $organizer_email = '';
        $allday = get_post_meta($event_id, 'eventer_event_all_day', true);
        $eventer_organizer = eventer_get_terms_front('eventer-organizer', $event_id, array('organizer_email'));
        $eventer_venue = eventer_get_terms_front('eventer-venue', $event_id, array('venue_address'));
        $eventer_location = (!empty($eventer_venue) && isset($eventer_venue[0]['metas']) && $eventer_venue[0]['metas']['venue_address'] != '') ? $eventer_venue[0]['metas']['venue_address'] : '';
        $default_reg = array(array('name' => $reg->username, 'email' => $reg->email, 'quantity' => $get_quantity));
        $valvalue = (!empty($valvalue) && $valindex != 'main') ? array_merge($valvalue, $default_reg) : $default_reg;

        if (!empty($valvalue) && $valindex != 'main') {
          foreach ($valvalue as $val) {
            $new_valindex = $valindex;
            $quantity = (isset($val['quantity'])) ? '1' : '';
            if ($quantity == '' && $val['email'] == $reg->email) continue;
            if ($quantity != '') {
              if (in_array($event_id, $all_events)) continue;
              $all_events[] = $event_id;
              $quantity = '';
              $new_valindex = $default_all;
            }
            $time = ($time_slot != '00:00:00' && $time_slot != '') ? $time_slot : date_i18n(get_option('time_format'), strtotime($event_time)) . ' - ' . date_i18n(get_option('time_format'), strtotime($event_end_time));
            if ($time_slot != '00:00:00' && $time_slot != '') { } else {
              $time = ($allday == 'on') ? esc_html__('All day', 'eventer') : $time;
            }

            if ($days_diff > 0) {
              $dynamic_start_date = date_i18n('Y-m-d', $value['date']);
              $set_end_date = date('Y-m-d', strtotime($dynamic_start_date . ' + ' . $days_diff . ' days'));
              $date = ($field == 'id') ? $reg->eventer_date : date_i18n(get_option('date_format'), $value['date']) . ' - ' . date_i18n(get_option('date_format'), strtotime($set_end_date));
            } else {
              $date = ($field == 'id') ? $reg->eventer_date : date_i18n(get_option('date_format'), $value['date']);
            }

            //$date = date_i18n(get_option('date_format'), strtotime($date));
            $individual[] = array('data-ticket' => $new_valindex, 'data-elocation' => $eventer_location, 'data-datetime' => $time . '<p>' . $date . '</p>', 'data-eventid' => get_the_title($event_id), 'data-email' => $val['email'], 'data-name' => $val['name'], 'data-qrcode' => $reg->id, 'data-img' => '');
          }
        }
      }
    }
    $all_data = array('data-nonce' => wp_create_nonce('eventer-qrcode-nonce'), 'default' => array('data-uname' => $reg->username, 'data-uemail' => $reg->email, 'data-registrant' => $reg->id), 'data-mainreg' => $reg->email, 'data-registrant' => $registrant, 'data-eid' => '', 'data-organizer' => (isset($eventer_organizer['metas'])) ? $eventer_organizer['metas']['organizer_email'] : '', 'individual' => $individual);
  }
  return $all_data;
}
add_filter('eventer_preapare_data_for_tickets', 'eventer_prepare_tickets_data', 10, 3);
function eventer_ticket_raw_html($ticket_show = '', $params = array())
{
  $main_atts = $span = $default = '';
  foreach ($params as $key => $value) {
    if (is_array($value) && $key == 'individual') {
      foreach ($value as $new_value) {
        $span .= '<span class="eventer-registrant-show"';
        foreach ($new_value as $ticket_key => $ticket_value) {
          $span .= ' ' . $ticket_key . '="' . $ticket_value . '"';
        }
        $span .= '></span>';
      }
    } elseif (is_array($value) && $key == 'default') {
      foreach ($value as $default_key => $default_value) {
        $default .= ' ' . $default_key . '="' . $default_value . '"';
      }
    } else {
      $main_atts .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
  }
  $ticket_show .= '<div class="eventer-all-registrants" style="display:none;"' . $main_atts . '>';
  $ticket_show .= $span;
  $ticket_show .= '</div>';
  $ticket_show .= '<div class="clearfix"></div>';
  $ticket_show .= '<div style="opacity:0;" class="eventer eventer-temporary-tickets eventer-woo-tickets-create eventer_woo_tickets_to_hide">
   <div style="width:600px;" id="eventer-ticket-printable" class="eventer-ticket-final-tickets eventer-ticket-image_create" data-nonce="' . esc_attr($params['data-nonce']) . '"' . $default . '>
			<div class="eventer-ticket-printable">
				<div class="eventer-ticket-printable-top">
					<div class="eventer-qrcode eventer-on-ticket-qr" data-qr-content=""></div>
					<label class="eventer-ticket-reg-code"></label>
				</div>
				<div class="eventer-ticket-printable-bottom">
					<label>' . esc_html__('Attendee', 'eventer') . '</label>
					<h3>' . esc_attr($params['default']['data-uname']) . '</h3>
					<label>' . esc_html__('Event', 'eventer') . '</label>
					<p class="eventer-woo-title">title</p>
					<div class="eventer-spacer-10"></div>
					<label>' . esc_html__('Ticket', 'eventer') . '</label>
					<div class="registrant-ticket">' . esc_attr(get_the_title($params['default']['data-eid'])) . '</div>
					<div class="eventer-spacer-10"></div>
					<div class="eventer-row">
						<div class="eventer-col5">
							<label>' . esc_html__('Venue Location', 'eventer') . '</label>
							<p class="eventer-woo-location"></p>
						</div>
						<div class="eventer-col5">
							<label>' . esc_html__('Date &amp; Time', 'eventer') . '</label>
							<p class="eventer-woo-datetime">time</p>
						</div>
					</div>
					<div class="eventer-spacer-10"></div>
				</div>
			</div>
		</div>
		</div>';
  echo $ticket_show;
}
add_action('eventer_ticket_raw_design', 'eventer_ticket_raw_html', 10, 2);

add_filter('post_class', 'eventer_remove_post_class', 10, 3);
function eventer_remove_post_class($classes, $class, $post_id)
{
  $index = array_search('eventer', $classes);
  if ($index !== false) {
    unset($classes[$index]);
  }
  return $classes;
}

function eventer_archive_to_custom_archive()
{
  $archive_settings = eventer_get_settings('eventer_archive_switch');
  $archive_template = eventer_get_settings('eventer_archive_template');
  if (is_post_type_archive('eventer') && $archive_settings == 'template' && $archive_template != '') {
    wp_redirect($archive_template, 301);
    exit();
  }
}
add_action('template_redirect', 'eventer_archive_to_custom_archive');

if (function_exists('icl_object_id') && class_exists('SitePress')) {
  add_filter('rest_url', 'wpml_permalink_filter');
}

function eventer_dequeue_scripts()
{
  global $wp_scripts;
  //$wp_scripts->queue = array();
  if (is_singular('eventer')) {
    wp_dequeue_script('divi-custom-script');
    wp_dequeue_script('capital-ui-plugins');
    wp_dequeue_script('auxin-scripts');
  }
}
add_action('wp_print_scripts', 'eventer_dequeue_scripts', 100);
