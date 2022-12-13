<?php
wp_localize_script('eventer-single-scripts', 'single_meta', array('future_date_cal' => esc_attr(date_i18n(get_option('date_format'), $event_cdate))));
?>
<div class="eventer eventer-event-single eventer-single-event-details">
  <div class="eventer-cat-header">
    <?php $eventer_cats = get_the_terms($event_id, 'eventer-category');
    $set_each_day = get_post_meta($event_id, 'eventer_event_each_day_time', true);
    if (!is_wp_error($eventer_cats) && !empty($eventer_cats)) {
      foreach ($eventer_cats as $cat) {
        $color = get_term_meta($cat->term_id, 'category_color', true);
        echo '<a href="' . get_term_link($cat->term_id) . '" class="pull-right" style="background-color: ' . $color . '">' . $cat->name . '</a>';
      }
    }
    ?>

    <h3><?php esc_html_e('Event details', 'eventer'); ?></h3>
  </div>
  <ul class="eventer-single-event-info">
    <?php $eventer_venue = get_the_terms($event_id, 'eventer-venue');
    $elocation = '';
    $specific_address = get_post_meta(get_the_ID(), 'eventer_event_specific_venue', true);
    if (!is_wp_error($eventer_venue) && !empty($eventer_venue)) {
      foreach ($eventer_venue as $venue) {
        $location_address = get_term_meta($venue->term_id, 'venue_address', true);
        $location_coordinates = get_term_meta($venue->term_id, 'venue_coordinates', true);
        $venue_name = $venue->name;
        if ($location_coordinates != '') {
          $elocation = $location_coordinates;
        } elseif ($location_address != '') {
          $elocation = $location_address;
        } else {
          $elocation = $venue->name;
        }
        $venue_name = ($specific_address != '') ? $specific_address : $venue_name;
        echo '<li><span class="eventer-event-venue"><i class="eventer-icon-location-pin"></i> ' . $venue_name . '</span></li>';
      }
    } elseif ($specific_address != '') {
      echo '<li><span class="eventer-event-venue"><i class="eventer-icon-location-pin"></i> ' . $specific_address . '</span></li>';
    }
    if ($days_diff <= 0) {
      echo '<li><span class="eventer-event-date"><i class="eventer-icon-calendar"></i> ' . esc_attr(date_i18n(get_option('date_format'), $event_cdate)) . '</span></li>';
    } elseif ($set_each_day == 'on') {
      $dynamic_start_date = date_i18n('Y-m-d', $event_cdate);
      $set_end_date = date('Y-m-d', strtotime($dynamic_start_date . ' + ' . $days_diff . ' days'));
      echo '<li><span class="eventer-event-date"><i class="eventer-icon-calendar"></i> ' . esc_attr(date_i18n(get_option('date_format'), $event_cdate)) . ' - ' . esc_attr(date_i18n(get_option('date_format'), strtotime($set_end_date))) . '</span></li>';
    }
    ?>

    <?php
    if ($set_each_day == 'on') {
      $start_date_format = eventer_get_settings('start_time_format');
      $end_date_format = eventer_get_settings('end_time_format');
      $separator = eventer_get_settings('time_separator');
      echo '<li><span class="eventer-event-time"><i class="eventer-icon-clock"></i> ' . date_i18n($start_date_format, $start_str) . $separator . date_i18n($end_date_format, $end_str) . esc_attr__(' (Everyday)', 'eventer') . '</span></li>';
    } else {
      echo '<li><span class="eventer-event-time"><i class="eventer-icon-clock"></i> ' . $event_time_show . '</span></li>';
    }
    $additional_info = get_post_meta($event_id, 'eventer_event_additional_info', true);
    if ($additional_info != '') {
      echo '<li><span class="eventer-event-time"><i class="eventer-icon-info"></i> ' . $additional_info . '</span></li>';
    }
    $event_organizer = get_the_terms($event_id, 'eventer-organizer');
    if (!is_wp_error($event_organizer) && !empty($event_organizer)) {
      $organizer_phone = get_term_meta($event_organizer[0]->term_id, 'organizer_phone', true);
      $organizer_website = get_term_meta($event_organizer[0]->term_id, 'organizer_website', true);
      if ($organizer_phone) {
        echo '<li><span class="eventer-event-time"><i class="eventer-icon-phone"></i> ' . $organizer_phone . '</span></li>';
      }
      if ($organizer_website) {
        $link_target = eventer_get_settings('eventer_organizer_link_target');
        echo '<li><div class="eventer-event-time"><i class="eventer-icon-globe"></i> <a href="' . esc_url($organizer_website) . '" target="' . esc_attr($link_target) . '">' . $organizer_website . '</a></div></li>';
      }
    }
    ?>

  </ul>
  <?php if (get_post_type($event_id) == 'eventer') {
    $links = eventer_get_settings('eventer_details_links');
    $options = get_option('eventer_options');
    $default = (isset($options['eventer_details_links'])) ? 1 : '';
    if (empty($default) || !empty($links)) {
      ?>
      <ul class="eventer-actions">
        <?php
            if (empty($default) || in_array('print', $links)) { ?><li><a href="javascript:" onclick="window.print();" title="Print"><?php esc_html_e('Print', 'eventer'); ?></a></li>
        <?php
            }
            if (empty($default) || in_array('contact', $links)) { ?>
          <li><a rel=emodal:open title="Contact" class="" href="#eventer-contact-form"><?php esc_html_e('Contact', 'eventer'); ?></a></li>
        <?php
            }
            ?>
        <?php if ((empty($default) && $elocation) || ($elocation && in_array('direction', $links))) {
              echo '<li><a href="' . esc_url("https://www.google.co.in/maps/dir//" . $elocation) . '" title="Directions" target="_blank">' . esc_html__('Get Directions', 'eventer') . '</a></li>';
            }
            $search_future = array_filter($all_dates, function ($date) {
              return $date > date_i18n('Y-m-d');
            });
            if ($search_future && (empty($default) || in_array('future', $links))) {
              echo '<li><a href="' . esc_url(add_query_arg('eid', $event_id, get_post_type_archive_link('eventer'))) . '" title="Future events">' . esc_html__('Future Events', 'eventer') . '</a></li>';
            }

            ?>
      </ul>
  <?php
    }
  } ?>
</div>