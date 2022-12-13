<li class="eventer-event-item eventer-native-row eventer-cfloat">
	<div class="eventer-native-col">
		<div class="eventer-dater">
			<span class="eventer-event-day"><?php echo esc_attr(date_i18n('d', strtotime($params['date']))); ?></span>
			<span class="eventer-event-month"><?php echo esc_attr(date_i18n('M', strtotime($params['date']))); ?></span>
		</div>
	</div>
	<div class="eventer-native-col">
		<div class="eventer-event-title">
			<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a>
		</div>
		<?php
		$date_show = $params['show_date'];
		?>
		<div class="eventer-classic-meta"><?php echo esc_attr($date_show); ?> | <strong><?php echo $params['show_time']; ?></strong></div>
		<?php
		if (!empty($params['tickets'])) {
			?>
			<div class="eventer-meta-ticket">
				<?php
					$woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
					$eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_settings('eventer_paypal_currency') : get_option('woocommerce_currency');
					foreach ($params['tickets'] as $ticket) {
						$remaining = ($ticket['tickets'] > 0) ? $ticket['tickets'] . ' ' . esc_html__('remaining', 'eventer') : esc_html__('All booked', 'eventer');
						?>
					<span><strong><?php echo eventer_get_currency_symbol($eventer_currency, $ticket['price']); ?></strong><span><?php echo $ticket['name']; ?></span><em><?php echo $remaining; ?></em></span>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
	<?php if (!empty($params['address'])) { ?>
		<div class="eventer-native-col">
			<a href="https://www.google.com/maps/dir//<?php echo $params['address']; ?>" target="_blank" title="<?php esc_html_e('Get Directions', 'eventer'); ?>" class="eventer-plain-links"><i class="eventer-icon-map"></i></a>
			<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>" class="eventer-btn eventer-btn-plain"><?php esc_html_e('Buy Tickets', 'eventer'); ?></a>
		</div>
	<?php } ?>
</li>