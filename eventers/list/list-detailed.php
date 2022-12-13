<li class="eventer-event-item eventer-detailed-row eventer-cfloat">
	<div class="eventer-dater" style="background-color: <?php echo esc_attr($params['color']); ?>">
		<?php
		if ($params['allday'] != '') {
			$date_day = '';
			$show_time = $params['allday'];
		} else {
			$date_day = date_i18n('l', strtotime($params['date']));
			$show_time = date_i18n(get_option('time_format'), strtotime($params['start']));
		}
		?>
		<span class="eventer-event-day"><?php echo esc_attr($date_day); ?></span>
		<span class="eventer-event-date"><strong><?php echo esc_attr(date_i18n('d', strtotime($params['date']))); ?></strong><span><?php echo esc_attr(date_i18n('M', strtotime($params['date']))); ?></span></span>
		<span class="eventer-event-time"><?php echo esc_attr($show_time); ?></span>
	</div>
	<div class="eventer-detailed-col">
		<label><?php esc_html_e('Event', 'eventer'); ?></label>
		<div class="eventer-event-title">
			<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a>
		</div>
	</div>
	<?php if (!empty($params['address'])) { ?>
		<div class="eventer-detailed-col">
			<label><?php esc_html_e('Venue', 'eventer'); ?></label>
			<?php echo $params['address']; ?>
		</div>
	<?php }
	if (isset($params['organizer'])) { ?>
		<div class="eventer-detailed-col">
			<label><?php esc_html_e('Organiser', 'eventer'); ?></label>
			<?php echo $params['organizer']; ?>
		</div>
	<?php } ?>
	<?php
	if (!empty($params['tickets'])) {
		?>
		<div class="eventer-detailed-col eventer-col-actions">
			<div class="eventer-fe-dd eventer-fe-dd-right eventer-quick-ticket-info">
				<a href="#"><i class="eventer-icon-info"></i> <?php esc_html_e('Ticket Info', 'eventer'); ?></a>
				<div class="eventer-fe-dropdown eventer-cfloat">
					<div class="eventer-fe-dropdown-in eventer-cfloat">
						<ul class="eventer-tickets-info eventer-cfloat">
							<?php
								$woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
								$eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_settings('eventer_paypal_currency') : get_option('woocommerce_currency');
								foreach ($params['tickets'] as $ticket) {
									$remaining = ($ticket['tickets'] > 0) ? $ticket['tickets'] . ' ' . esc_html__('remaining', 'eventer') : esc_html__('All booked', 'eventer');
									?>
								<li>
									<span class="eventer-ticket-type-price"><?php echo eventer_get_currency_symbol($eventer_currency, $ticket['price']); ?></span>
									<span class="eventer-ticket-type-name"><?php echo $ticket['name']; ?> <i class="eventer-ticket-remaining"><?php echo $remaining; ?></i></span>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<!--<a href="#" class="eventer-btn">Buy Tickets</a>-->
		</div>
	<?php } ?>
</li>