<li class="eventer-event-grid-item equah-item">
  <div class="eventer-grid-fimage">
    <a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo get_the_post_thumbnail($params['eventer'], 'full'); ?></a>

    <?php
    if ($params['multi'] == '1') {
      ?>
      <div class="eventer-featured-date"><strong><?php echo date_i18n('d', strtotime($params['raw_start'])); ?></strong><span><?php echo date_i18n('M, Y', strtotime($params['raw_start'])); ?></span></div>
      <div class="eventer-featured-date eventer-featured-date-multi"><strong><?php echo date_i18n('d', strtotime($params['raw_end'])); ?></strong><span><?php echo date_i18n('M, Y', strtotime($params['raw_end'])); ?></span></div>
    <?php } else { ?>
      <div class="eventer-featured-date"><strong><?php echo date_i18n('d', strtotime($params['raw_start'])); ?></strong><span><?php echo date_i18n('M, Y', strtotime($params['raw_start'])); ?></span></div>
    <?php } ?>
  </div>
  <div class="eventer-grid-content">
    <span class="eventer-status-badge eventer-status-upcoming"><?php echo esc_attr($params['badge']); ?></span>
    <div class="eventer-event-title"><a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo $params['event']; ?></a></div>

    <?php if (!empty($params['addresses'])) { ?>
      <div class="eventer-grid-meta">
        <?php foreach ($params['addresses'] as $add) {
            echo '<div><i class="eventer-icon-location-pin"></i> ' . $add . '</div>';
          }
          ?>
      </div>
    <?php } elseif (!empty($params['address'])) { ?>
      <div class="eventer-grid-meta">
        <div><i class="eventer-icon-location-pin"></i> <?php echo $params['address']; ?></div>
      </div>
    <?php }
    ?>

    <div class="eventer-hidden-content">
      <a href="#" class="eventer-hidden-content-trigger eventer-hidden-ctriggero">More</a>
      <a href="#" class="eventer-hidden-content-trigger eventer-hidden-ctriggerc">Less</a>
      <p><?php echo esc_attr($params['excerpt']); ?></p>
      <ul class="eventer-event-share">
        <li></li>
        <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-facebook"></i></a></li>
        <li><a href="https://twitter.com/intent/tweet?source=<?php echo esc_url($params['raw_url']); ?>&text=Event: <?php echo $params['event_title']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-twitter"></i></a></li>
        <li><a href="https://plus.google.com/share?url=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-google"></i></a></li>
        <li><a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url($params['raw_url']); ?>&title=<?php echo $params['event_title']; ?>&source=<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-social-linkedin"></i></a></li>
        <li><a href="mailto:?subject=<?php echo $params['event_title']; ?>&body=<?php echo $params['excerpt']; ?>:<?php echo esc_url($params['raw_url']); ?>"><i class="eventer-icon-envelope-letter"></i></a></li>
      </ul>
      <?php
      if (!empty($params['tickets'])) {
        ?>
        <div class="eventer-featured-grid-title"><?php esc_html_e('Tickets Information', 'eventer'); ?></div>
        <ul class="eventer-tickets-info eventer-cfloat">
          <?php
            $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
            $eventer_currency = ($woocommerce_ticketing != 'on' || !function_exists('get_woocommerce_currency_symbol')) ? eventer_get_settings('eventer_paypal_currency') : get_option('woocommerce_currency');
            foreach ($params['tickets'] as $ticket) {
              $remaining = ($ticket['tickets'] > 0) ? $ticket['tickets'] . ' ' . esc_html__('remaining', 'eventer') : esc_html__('All booked', 'eventer');
              echo '<li>
                                <span class="eventer-ticket-type-price">' . eventer_get_currency_symbol($eventer_currency, $ticket['price']) . '</span>
                                <span class="eventer-ticket-type-name">' . $ticket['name'] . ' <i class="eventer-ticket-remaining">' . $remaining . '</i></span>
                                </li>';
            }
            ?>
        </ul>
    </div>
  <?php
  }
  $custom_btn = get_post_meta($params['eventer'], 'eventer_event_custom_permalink_btn', true);
  if ($custom_btn != '') {
    ?>
    <a class="eventer-btn" href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>"><?php echo esc_attr($custom_btn); ?></a>
  </div>
<?php
}
?>
</li>