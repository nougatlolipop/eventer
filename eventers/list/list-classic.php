<li class="eventer-event-item eventer-p2-event-list-item eventer-cfloat equah">
	<div class="eventer-p2-event-image equah-item">
		<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>" class="eventer-media-hover">
			<?php echo get_the_post_thumbnail($params['eventer'], 'eventer-thumb-600x400'); ?>
		</a>
		<div class="eventer-quick-share closed">
			<a href="#"><i class="eventer-icon-share"></i></a>
			<ul>
				<li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-facebook"></i></a></li>
				<li><a href="https://twitter.com/intent/tweet?source=<?php echo esc_url($params['raw_url']); ?>&text=Event: <?php echo $params['event_title']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-twitter"></i></a></li>
				<li><a href="https://plus.google.com/share?url=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-google"></i></a></li>
				<li><a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($params['raw_url']); ?>&title=<?php echo $params['event_title']; ?>&source=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-linkedin"></i></a></li>
				<li><a href="mailto:?subject=<?php echo $params['event_title']; ?>&body=<?php echo $params['excerpt']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-envelope-letter"></i></a></li>
			</ul>
		</div>
	</div>
	<div class="eventer-p2-list-content equah-item">
		<h4 class="eventer-event-title">
			<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a>
		</h4>
		<?php
			$date_show = $params['show_date'];
		?>
		<div class="eventer-classic-meta"><i class="eventer-icon-calendar"></i> <?php echo esc_attr($date_show); ?> <span class="eventer-meta-sub"><?php echo $params['show_time']; ?></span></div>
		<?php if (!empty($params['address'])) { ?>
			<div class="eventer-classic-meta"><i class="eventer-icon-location-pin"></i> <?php echo $params['address']; ?></div>
		<?php } ?>
		<div class="eventer-classic-content">
			<p><?php echo esc_attr($params['excerpt']); ?></p>
		</div>
		<?php
		if (!empty($params['tickets'])) {
			?>
			<div class="eventer-classic-ticket-info">
				<a href="<?php echo esc_attr($params['details']); ?>" target="<?php echo esc_attr($params['target']); ?>" class="eventer-btn"><?php esc_html_e('Buy Tickets', 'eventer'); ?></a>
				<div class="eventer-fe-dd eventer-quick-ticket-info">
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
			</div>
		<?php } ?>
	</div>
</li>