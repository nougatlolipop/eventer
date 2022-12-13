<li class="eventer-event-grid-item equah-item eventer-cfloat">
    <div class="eventer-modern-r1">
        <?php
        if (has_post_thumbnail($params['eventer'])) {
            ?>
            <div>
                <div class="eventer-grid-fimage">
                    <a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo get_the_post_thumbnail($params['eventer'], 'eventer-thumb-170x170'); ?></a>
                </div>
            </div>
        <?php } ?>
        <?php
        $date_show = $params['show_date'];
        ?>
        <div>
            <div class="eventer-countdown-timer" data-date="<?php echo esc_attr(date_i18n('Y/m/d', strtotime($params['date']))); ?>"></div>
        </div>
    </div>
    <div class="eventer-modern-r2">
        <div>
            <div class="eventer-event-title"><a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a></div>
            <?php if (!empty($params['address'])) { ?>
                <div class="eventer-classic-meta"><?php echo $params['address']; ?></div>
            <?php } ?>
            <ul class="eventer-event-share">
                <li></li>
                <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-facebook"></i></a></li>
                <li><a href="https://twitter.com/intent/tweet?source=<?php echo esc_url($params['raw_url']); ?>&text=Event: <?php echo $params['event_title']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-twitter"></i></a></li>
                <li><a href="https://plus.google.com/share?url=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-google"></i></a></li>
                <li><a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($params['raw_url']); ?>&title=<?php echo $params['event_title']; ?>&source=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-linkedin"></i></a></li>
                <li><a href="mailto:?subject=<?php echo $params['event_title']; ?>&body=<?php echo $params['excerpt']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-envelope-letter"></i></a></li>
            </ul>
        </div>
        <div>
            <div class="eventer-event-day"><?php echo date_i18n('l', strtotime($params['date'])); ?></div>
            <div class="eventer-event-date eventer-classic-meta"><?php echo esc_attr($date_show); ?></div>
            <?php
            if (!empty($params['tickets'])) {
                $price = array();
                $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
                $eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_settings('eventer_paypal_currency') : get_option('woocommerce_currency');
                foreach ($params['tickets'] as $ticket) {
                    $price[] = $ticket['price'];
                }
                $min_price = min($price);
                $max_price = max($price);
                if ($min_price != '' && $max_price != '') {
                    ?>
                    <div class="eventer-price-range"><span><?php esc_html_e('Tickets Starting', 'eventer'); ?></span> <strong><?php echo eventer_get_currency_symbol($eventer_currency, $min_price) . ' - ' . eventer_get_currency_symbol($eventer_currency, $max_price); ?></strong></div>
            <?php }
            }
            ?>
        </div>
    </div>
    <?php
    $custom_btn = get_post_meta($params['eventer'], 'eventer_event_custom_permalink_btn', true);
    if ($custom_btn != '') {
        ?>
        <a class="eventer-btn" href="<?php echo $params['details']; ?>" target="<?php echo esc_url($params['target']); ?>"><?php echo esc_attr($custom_btn); ?></a>
    <?php
    }
    ?>
</li>