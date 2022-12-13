<li class="eventer-event-slide">
	<div class="eventer-grid-fimage">
		<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>" style="background-image: url(<?php echo get_the_post_thumbnail_url($params['eventer'], 'full'); ?>)"><?php echo $params['event']; ?></a>
	</div>
	<div class="eventer-slider-content">
		<div class="eventer-slider-content-in">
			<div class="eventer-slider-content-inside">
				<div class="eventer-event-title"><a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a></div>
				<div class="eventer-grid-meta">
					<div><i class="eventer-icon-calendar"></i> <?php echo $params['show_date']; ?></div>
					<div><i class="eventer-icon-clock"></i> <?php echo $params['show_time']; ?></div>
					<?php if (!empty($params['address'])) { ?>
						<div><i class="eventer-icon-location-pin"></i> <?php echo $params['address']; ?></div>
					<?php } ?>
				</div>
				<ul class="eventer-event-share">
					<li></li>
					<li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-facebook"></i></a></li>
					<li><a href="https://twitter.com/intent/tweet?source=<?php echo esc_url($params['raw_url']); ?>&text=Event: <?php echo $params['event_title']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-twitter"></i></a></li>
					<li><a href="https://plus.google.com/share?url=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-google"></i></a></li>
					<li><a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($params['raw_url']); ?>&title=<?php echo $params['event_title']; ?>&source=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-linkedin"></i></a></li>
					<li><a href="mailto:?subject=<?php echo $params['event_title']; ?>&body=<?php echo $params['excerpt']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-envelope-letter"></i></a></li>
				</ul>
			</div>
			<?php
			if (!empty($params['tickets'])) {
				$price = array();
				$woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
				$eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_settings('eventer_paypal_currency') : get_option('woocommerce_currency');
				foreach ($params['tickets'] as $ticket) {
					$price[] = $ticket['price'];
				}
				$min_price = min($price);
				if ($min_price != '') {
					?>
					<div class="eventer-slider-content-bottom">
						<!--<a href="#">Buy Tickets</a>-->
						<span><?php esc_html_e('Tickets starting from', 'eventer');
								echo ' ' . eventer_get_currency_symbol($eventer_currency, $min_price); ?></span>
					</div>
				<?php }
			} ?>
		</div>
	</div>
</li>