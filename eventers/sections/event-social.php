<?php
$eventer_show_share_icons = eventer_get_settings('eventer_sharing_icons');
if ($eventer_show_share_icons == "on") {
  ?>
  <div class="eventer-single-event-footer eventer clearfix">
    <ul class="pull-left eventer-event-share">
      <li><?php esc_html_e('Share event', 'eventer'); ?></li>
      <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url(get_permalink($event_id)); ?>"><i class="eventer-icon-social-facebook"></i></a></li>
      <li><a href="https://twitter.com/intent/tweet?source=<?php echo esc_url(get_permalink($event_id)); ?>&text=Event: <?php echo esc_attr(apply_filters('eventer_raw_event_title', '', $event_id)); ?>:<?php echo esc_url(get_permalink($event_id)); ?>"><i class="eventer-icon-social-twitter"></i></a></li>
      <li><a href="https://plus.google.com/share?url=<?php echo esc_url(get_permalink($event_id)); ?>"><i class="eventer-icon-social-google"></i></a></li>
      <li><a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url(get_permalink($event_id)); ?>&title=<?php echo esc_attr(apply_filters('eventer_raw_event_title', '', $event_id)); ?>&source=<?php echo esc_url(get_permalink($event_id)); ?>"><i class="eventer-icon-social-linkedin"></i></a></li>
      <li><a href="mailto:?subject=<?php echo esc_attr(apply_filters('eventer_raw_event_title', '', $event_id)); ?>&body=:<?php echo esc_url(get_permalink($event_id)); ?>"><i class="eventer-icon-envelope-letter"></i></a></li>
    </ul>
  </div>
<?php } ?>