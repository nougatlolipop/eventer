<li class="eventer-modern-row eventer-cfloat">
	<div class="eventer-modern-col">
		<div class="eventer-dater" style="background-image: url(<?php echo get_the_post_thumbnail_url($params['eventer'], 'eventer-thumb-170x170'); ?>)">
			<span class="eventer-event-date"><strong><?php echo esc_attr(date_i18n('d', strtotime($params['date']))); ?></strong><span><?php echo esc_attr(date_i18n('D, Y', strtotime($params['date']))); ?></span></span>
		</div>
	</div>
	<div class="eventer-modern-col">
		<div class="eventer-event-title">
			<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a>
		</div>
		<?php if (!empty($params['address'])) { ?>
		<div class="eventer-classic-meta"><?php echo $params['address']; ?></div>
		<?php } ?>
	</div>
	<div class="eventer-modern-col">
		<a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>" class="eventer-btn eventer-btn-plain"><?php esc_html_e('Buy Tickets', 'eventer'); ?></a>
	</div>
</li>