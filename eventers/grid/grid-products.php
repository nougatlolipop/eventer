<li class="eventer-event-grid-item equah-item eventer-cfloat">
    <div class="eventer-product-r1">
        <div class="eventer-event-title"><a href="<?php echo $params['details']; ?>"><?php echo $params['event']; ?></a>
        </div>
    </div>
    <div class="eventer-grid-fimage">
        <a href="<?php echo esc_url($params['details']); ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo get_the_post_thumbnail($params['eventer'], 'eventer-thumb-600x400'); ?></a>
    </div>
    <?php
    $date_show = $params['show_date'];
    ?>
    <div class="eventer-product-r2">
        <div class="eventer-grid-meta">
            <div><i class="eventer-icon-calendar"></i> <?php echo esc_attr($date_show); ?></div>
            <div><i class="eventer-icon-clock"></i> <?php echo $params['show_time']; ?></div>
            <?php if (!empty($params['address'])) { ?>
                <div><i class="eventer-icon-location-pin"></i> <?php echo $params['address']; ?></div>
            <?php } ?>
        </div>
        <?php
        if ($params['registration'] == '1') {
            echo '<div class="eventer-product-r2-inner">';
        }
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

                <div class="eventer-product-pricing">
                    <span><?php esc_html_e('Tickets', 'eventer'); ?><em><?php esc_html_e('Starting from', 'eventer'); ?></em></span>
                    <strong><?php echo eventer_get_currency_symbol($eventer_currency, $min_price); ?></strong>
                </div>

            <?php
                }
            }
            if ($params['registration'] == '1') {
                ?>
            <div>
                <a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>" class="eventer-btn eventer-btn-primary"><?php esc_html_e('Book Now', 'eventer'); ?></a>
            </div>
        <?php
            echo '</div>';
        }
        ?>
        <?php
        $custom_btn = get_post_meta($params['eventer'], 'eventer_event_custom_permalink_btn', true);
        if ($custom_btn != '') {
            ?>
            <a class="eventer-btn" href="<?php echo $params['details']; ?>" target="<?php echo esc_url($params['target']); ?>"><?php echo esc_attr($custom_btn); ?></a>
        <?php
        }
        ?>
    </div>
</li>