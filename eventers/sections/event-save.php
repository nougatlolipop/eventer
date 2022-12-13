<?php
$eventer_show_save_event_options = eventer_get_settings('eventer_save_options');
if($eventer_show_save_event_options=="on") {
$_url = site_url().'?';
$eventer_formatted_date = date_i18n('Y-m-d', $event_cdate);
?>
<div class="eventer-single-event-footer eventer clearfix">
<div class="eventer-event-save">
			<span><?php esc_html_e('Save event', 'eventer'); ?> <i class="eventer-icon-arrow-down"></i></span>
			<ul>
				<li><a href="<?php echo esc_url($_url.base64_encode('action=icalendar&edate='.$eventer_formatted_date.'&key=imic_save_event&id='.$event_id)); ?>"><?php esc_html_e('Save to iCalendar', 'eventer'); ?></a></li>
				<li><a href="<?php echo esc_url($_url.base64_encode('action=gcalendar&edate='.$eventer_formatted_date.'&key=imic_save_event&id='.$event_id)); ?>"><?php esc_html_e('Save to Google calendar', 'eventer'); ?></a></li>
				<li><a href="<?php echo esc_url($_url.base64_encode('action=outlook&edate='.$eventer_formatted_date.'&key=imic_save_event&id='.$event_id)); ?>"><?php esc_html_e('Save to Outlook', 'eventer'); ?></a></li>
				<li><a href="<?php echo esc_url($_url.base64_encode('action=outlooklive&edate='.$eventer_formatted_date.'&key=imic_save_event&id='.$event_id)); ?>"><?php esc_html_e('Save to Outlook online', 'eventer'); ?></a></li>
				<li><a href="<?php echo esc_url($_url.base64_encode('action=yahoo&edate='.$eventer_formatted_date.'&key=imic_save_event&id='.$event_id)); ?>"><?php esc_html_e('Save to Yahoo calendar', 'eventer'); ?></a></li>
			</ul>
		</div>
</div>
<?php } ?>