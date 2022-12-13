<?php
$eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
$contact_organizer_fields = eventer_get_settings('contact_organizer_fields');
$contact_form = '<h3>'.esc_html__('Contact event manager', 'eventer').'</h3>
				<form action="" method="post" class="organizer-contact">
					<div class="organizer-details" style="display: none">'.esc_attr(get_the_ID()).'</div>
					<div class="eventer-date" style="display: none">'.esc_attr($eventer_formatted_date).'</div>';
	if($contact_organizer_fields!='') 
	{ 
		$contact_form .= do_shortcode($contact_organizer_fields);
	}
	else
	{
				$contact_form .= '<label>'.esc_html__('Your name', 'eventer').'</label>
                              <input class="eventer-required" type="text" name="'.esc_html__('Name', 'eventer').'">
                              <label>'.esc_html__('Your Email', 'eventer').'</label>
                              <input class="eventer-required" type="email" name="'.esc_html__('Email', 'eventer').'">
                              <label>'.esc_html__('Your Phone', 'eventer').'</label>
                              <input class="eventer-required" type="text" name="'.esc_html__('Phone', 'eventer').'">
                              <label>'.esc_html__('Your message', 'eventer').'</label>
                              <textarea class="eventer-required" name="'.esc_html__('Message', 'eventer').'" rows="5"></textarea>';
	}
				$contact_form .= '<div class="message"></div>
					<input type="submit" class="eventer-btn" value="'.esc_html__('Submit', 'eventer').'">
				</form>';
$contact_modal_show = '<div class="eventer-modal-static" id="eventer-contact-form"><div class="eventer-modal-body">
						<div class="eventer eventer-event-single">';
$contact_modal_show .= 				$contact_form;
$contact_modal_show .= '</div>
					</div></div>';
echo $contact_modal_show;