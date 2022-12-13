<?php
defined('ABSPATH') or die('No script kiddies please!');
add_action('add_meta_boxes_eventer', 'wceventer_add_event_fields');
add_action('save_post', 'wceventer_update_event_tickets_data', 1, 2);
/**
 * Add custom Meta Box to Posts post type
 */
function wceventer_add_event_fields()
{
  add_meta_box('eventer_event_schedule', __('Event Tickets', 'eventer'), 'wceventer_event_tickets_output', 'eventer', 'normal', 'core');
  add_meta_box('eventer_event_schedule_datewise', __('Date Wise Event Booking Record', 'eventer'), 'wceventer_event_tickets_output_datewise', 'eventer', 'normal', 'core');
}
/**
 * Print the Meta Box content
 */
function wceventer_event_tickets_output()
{
  global $post;
  // Add an nonce field so we can check for it later.
  wp_nonce_field('eventer_event_schedule_meta_box', 'eventer_event_tickets_meta_box_nonce');
  $original_event = eventer_wpml_original_post_id($post->ID);
  $tickets = get_post_meta($original_event, 'eventer_tickets', true);
  $total_tickets_count = ($tickets == '' || count($tickets) < 3) ? 3 : count($tickets);
  $reset_fields = '';
  if ($original_event != $post->ID && $original_event != '') {
    echo '<div id="field_group">
    			<div id="field_wrap">';
    echo '<div id="add_field_row">
      		<p>' . esc_attr__('You can not add ticket for this event, as this event is the copy of ', 'eventer') . get_the_title($original_event) . '</p>
    			</div>';
    echo '</div></div>';
  } else {
    ?>
    <div class="eventer-form-table">
      <table class=" eventer-admin-woo-tickets-table eventer-metabox">
        <tr>
          <th><strong><?php esc_html_e('Ticket Type', 'eventer'); ?></strong></th>
          <th><strong><?php esc_html_e('No of Tickets', 'eventer'); ?></strong></th>
          <th><strong><?php esc_html_e('Price', 'eventer'); ?></strong></th>
          <th><strong><?php esc_html_e('Enable on', 'eventer'); ?></strong></th>
          <th><strong><?php esc_html_e('Woocommerce Ticket Actions', 'eventer'); ?></strong></th>
        </tr>
        <?php
            $tickets_ids = $tickets_field = $ticket_identification = $ticket_enabled = '';
            $eventer_term_id = get_term_by('slug', 'eventer', 'product_cat');
            $tickets_field .= '<option value="add">' . esc_html__('Add new', 'eventer') . '</option>';
            if (!is_wp_error($eventer_term_id) && !empty($eventer_term_id)) {
              $tickets_ids = get_objects_in_term($eventer_term_id->term_id, 'product_cat');
              foreach ($tickets_ids as $ids) {
                if (get_post_type($ids) != 'product' || get_post_status($ids) != 'publish') continue;
                $tickets_field .= '<option value="' . $ids . '">' . get_the_title($ids) . '</option>';
              }
            }
            $make_identification_unique = array();
            for ($field = 0; $field < $total_tickets_count; $field++) {
              $ticket_price = $ticket_name = $ticket_number = '';
              $ticket_id = (isset($tickets[$field]['pid']) && get_post_type($tickets[$field]['pid']) == 'product' && get_post_status($tickets[$field]['pid']) == 'publish') ? $tickets[$field]['pid'] : '';
              $ticket_status_text = esc_html__('Add');
              $ticket_status_val = 'add';
              if ($ticket_id != '' && get_post_type($ticket_id) == 'product') {
                if (function_exists('wc_get_product')) {
                  $_product = wc_get_product($ticket_id);
                  $ticket_price = (isset($tickets[$field]['price'])) ? $tickets[$field]['price'] : $_product->get_price();
                }
                $ticket_name = get_the_title($ticket_id);
                $ticket_number = (isset($tickets[$field]['number'])) ? $tickets[$field]['number'] : '';
                $ticket_status_text = esc_html__('Update');
                $ticket_status_val = 'update';
                $ticket_identification = intval($ticket_id) + intval($post->ID);
                $ticket_enabled = (isset($tickets[$field]['enabled'])) ? $tickets[$field]['enabled'] : '';
              }
              ?>
          <tr class="wc_ticket_section" data-wcticket="">
            <input type="hidden" name="wceventer_ticket_id[]" value="<?php echo esc_attr($ticket_id); ?>" />
            <td><input type="text" name="wceventer_ticket_name[]" class="meta_feat_title" value="<?php echo esc_attr($ticket_name); ?>" placeholder="<?php esc_html_e('Name of Ticket', 'eventer'); ?>"></td>
            <td><input type="text" size="5" name="wceventer_ticket_number[]" class="meta_feat_title" value="<?php echo esc_attr($ticket_number); ?>" placeholder="<?php esc_html_e('No of Tickets', 'eventer'); ?>"></td>
            <td><input size="5" class="meta_sch_title" name="wceventer_ticket_price[]" value="<?php echo esc_attr($ticket_price); ?>" type="text" placeholder="<?php esc_html_e('Price', 'eventer'); ?>">
              <input type="hidden" value="<?php echo esc_attr($ticket_identification); ?>" name="eventer_ticket_identification[]"></td>
            <td>
              <input type="text" placeholder="<?php esc_html_e('Select date', 'eventer'); ?>" class="meta_sch_title eventer_activation_date" name="eventer_ticket_activation_date[]" value="<?php echo esc_attr($ticket_enabled); ?>">
            </td>
            <td>
              <?php if ($ticket_status_val != 'add') { ?>
                <input checked="checked" type="radio" name="wceventer_ticket_status<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($ticket_status_val); ?>">
              <?php echo esc_attr($ticket_status_text);
                    } else {
                      echo '<select name="wceventer_ticket_status' . esc_attr($field) . '">';
                      echo $tickets_field;
                      echo '</select>';
                    }
                    if ($ticket_id != '' && get_post_type($ticket_id) == 'product') { ?>
                <input type="radio" name="wceventer_ticket_status<?php echo esc_attr($field); ?>" value="del"> <?php esc_html_e('Remove', 'eventer'); ?>
                <br><small><a href="<?php echo get_edit_post_link($ticket_id); ?>" class="more_options" target="_blank"><?php esc_html_e('View Ticket Woocommerce Product', 'eventer'); ?></a></small></td>

          <?php } ?>
          </tr>
        <?php } ?>
        <tr class="eventer_admin_new_additional_ticket" data-wcticket="" style="display:none;">
          <input type="hidden" name="wceventer_ticket_id[]" value="" />
          <td><input type="text" name="" class="meta_feat_title eventer-admin-dynamic-ticket-name" value="" placeholder="<?php esc_html_e('Name of Ticket', 'eventer'); ?>"></td>
          <td><input size="5" type="text" name="wceventer_ticket_number[]" class="meta_feat_title" value="" placeholder="<?php esc_html_e('No of Tickets', 'eventer'); ?>"></td>
          <td><input size="5" class="meta_sch_title" name="wceventer_ticket_price[]" value="" type="text" placeholder="<?php esc_html_e('Price', 'eventer'); ?>">
            <input type="hidden" value="" name="eventer_ticket_identification[]"></td>
          <td>
            <input type="text" class="eventer_activation_date" name="eventer_ticket_activation_date[]" value="">
          </td>
          <td>
            <?php
                echo '<select class="eventer-admin-dynamic-ticket-action" name="">';
                echo $tickets_field;
                echo '</select>';
                ?>
          </td>
        </tr>
      </table>
      <p>
        <button name="eventer_admin_add_more_ticket" class="eventer_admin_add_more_ticket button eventer-metabox-field"><?php esc_attr_e('Add Ticket', 'eventer'); ?></button>
      </p>
      <div class="eventer-metabox-section">
        <label><?php esc_html_e('Disable the booking before number of days Ex: 2.'); ?></label>
        <input type="text" placeholder="<?php esc_attr_e('Enter the number of days', 'eventer'); ?>" class="eventer-metabox-field" value="<?php echo get_post_meta($post->ID, 'eventer_disable_booking_before', true); ?>" name="eventer_disable_booking_before">
        <label><?php esc_html_e('Use this field to set common ticket count for all tickets.'); ?></label>
        <input type="text" placeholder="<?php esc_attr_e('Ticket count', 'eventer'); ?>" class="eventer-common-ticket-count eventer-metabox-field" value="<?php echo get_post_meta($post->ID, 'eventer_common_ticket_count', true); ?>" name="eventer_common_ticket_count">
        <label><?php esc_attr_e('Tickets settings', 'eventer'); ?></label>
        <select name="eventer_ticket_optional_settings" class="eventer-metabox-field">
          <option value=""><?php esc_html_e('Select options if you are modifying any of ticket info', 'eventer'); ?></option>
          <option value="all"><?php esc_html_e('Reset tickets for all date', 'eventer'); ?></option>
          <!--<option value="limit"><?php esc_html_e('Reset tickets except quantity', 'eventer'); ?></option>-->
        </select>
      </div><br />

      <div id="add_field_row" class="eventer-metabox-section">
        <p class="field-description"><?php echo esc_attr_e('If common ticket count field using then all tickets booking will deduct counting from this field only.', 'eventer'); ?></p>
        <p class="field-description"><?php echo esc_attr_e('Do not add currency in price field, currency should be selected from WooCommerce => Settings => General', 'eventer'); ?></p>
        <p class="field-description"><?php echo esc_attr_e('Ticket quantity is maintained on per date basis by the Eventer plugin only. Quantity/SKU/Inventory of WooCommerce product will not work here. Add quantity here for the ticket type which will be avilable for all events using the same ticket type.', 'eventer'); ?></p>
        <p class="field-description"><?php echo esc_attr_e('To add a new ticket type, enter the name, quantity and price for it and select "Add New" from the dropdown. To use a pre built ticket type select it from the dropdown available.', 'eventer'); ?></p>
      </div>
    </div>
  <?php
    }
  }

  function wceventer_event_tickets_output_datewise()
  {
    global $post;
    // Add an nonce field so we can check for it later.
    wp_nonce_field('eventer_event_schedule_meta_box', 'eventer_event_tickets_meta_box_nonce');
    $original_event = eventer_wpml_original_post_id($post->ID);
    $all_dates = get_post_meta($original_event, 'eventer_all_dates', true);
    $tickets = get_post_meta($original_event, 'wceventer_tickets', true);
    $total_tickets_count = ($tickets == '' || count($tickets) < 3) ? 3 : count($tickets);
    ?>
  <div id="field_group" class="booked_ticket_section eventer-booked-tickets-record">
    <div id="field_wrap" data-date="" data-time="00:00:00">
      <div class="eventer-metabox-section">
        <label><?php esc_attr_e('Select date to load ticket info', 'eventer'); ?></label>
        <select name="event_date_wise_bookings" class="generate_eventer_bookings eventer-metabox-field" data-eventer="<?php echo esc_attr($original_event); ?>">
          <option value="" selected><?php esc_html_e('Select', 'eventer'); ?></option>
          <option value="1"><?php esc_html_e('Enable Reset Button', 'eventer'); ?></option>
          <?php
            $all_dates = array_filter($all_dates, function ($date) {
              $start = date_i18n('Y-m-d G:i', strtotime(date_i18n("Y-m-d", time()) . " - 730 day"));
              $end = date_i18n('Y-m-d G:i', strtotime(date_i18n("Y-m-d", time()) . " + 730 day"));
              return (strtotime($date) >= strtotime($start) and strtotime($date) <= strtotime($end));
            });
            foreach ($all_dates as $date) {
              echo '<option value="' . esc_attr($date) . '">' . esc_attr($date) . '</option>';
            }
            ?>
        </select>
        <?php
          $time_slot = get_post_meta($original_event, 'eventer_time_slot', true);
          if ($time_slot) {
            ?>
          <label><?php esc_attr_e('Select time slot', 'eventer'); ?></label>
          <select name="event_time_wise_bookings" class="generate_eventer_bookings_slot eventer-metabox-field" data-eventer="<?php echo esc_attr($original_event); ?>">
            <option value="" selected><?php esc_html_e('Select Time', 'eventer'); ?></option>
            <?php
                foreach ($time_slot as $slot) {
                  echo '<option value="' . esc_attr($slot['start'] . ':00') . '">' . $slot['title'] . ' ' . $slot['start'] . '-' . $slot['end'] . '</option>';
                }
                ?>
          </select>
        <?php
          }
          ?>
      </div>
      <br />
      <span class="dashicons dashicons-update eventer-loading" style="display: none"></span>
      <div class="clear"></div>
      <table class="eventer-metabox">
        <tr>
          <th><strong><?php esc_html_e('Ticket Type', 'eventer'); ?></strong></td>
          <th><strong><?php esc_html_e('Available Tickets', 'eventer'); ?></strong></td>
          <th><strong><?php esc_html_e('Price', 'eventer'); ?></strong></td>
          <th><strong><?php esc_html_e('Enable On', 'eventer'); ?></strong></td>
        </tr>
        <?php
          for ($field = 0; $field < $total_tickets_count; $field++) {
            $random_ticket_number = $original_event + $field;
            $ticket_backend_generate = $random_ticket_number * 2648;
            $already_booked_ids = get_post_meta($original_event, 'wceventer_tickets', true);
            $product_id = (isset($already_booked_ids[$field]['wceventer_ticket_id'])) ? $already_booked_ids[$field]['wceventer_ticket_id'] : '';
            ?>
          <tr class="booked_eventer_section">
            <td><input disabled type="text" class="meta_feat_title booked_record_event_title booked_fields" value="" placeholder="<?php esc_html_e('Name of Ticket', 'eventer'); ?>" style="width: 100%"></td>
            <td><input size="5" disabled type="text" class="meta_feat_title booked_record_event_number booked_fields" value="" placeholder="<?php esc_html_e('Available Tickets', 'eventer'); ?>" style="width: 100%"></td>
            <td><input size="5" disabled class="meta_sch_title booked_record_event_price booked_fields" value="" type="text" placeholder="<?php esc_html_e('Price', 'eventer'); ?>" style="width: 100%"></td>
            <td>
              <label><input class="meta_sch_title booked_record_event_restrict booked_fields" value="1" type="checkbox" style="width: 100%; display: none;"><input type="hidden" value="<?php echo esc_attr($product_id); ?>" class="booked_fields booked_record_event_pid eventer-admin-ticket-woo-id"></label>
              <input class="meta_sch_title booked_record_event_id booked_fields" type="hidden">
              <input type="text" class="eventer_activation_date meta_sch_title booked_record_event_enabled booked_fields" value="ss">
            </td>
          </tr>
        <?php } ?>
      </table>
      <hr>


      <div class="eventer-metabox-section">
        <label><?php esc_html_e('Add custom label for the selected date event', 'eventer'); ?></label><br>
        <input type="text" class="eventer_admin_badge booked_record_event_badge eventer-metabox-field" placeholder="<?php esc_html_e('Add Label', 'eventer'); ?>">

        <label><?php esc_html_e('Make selected date event to be featured', 'eventer'); ?></label><br>
        <select class="eventer_admin_featured booked_record_event_featured eventer-metabox-field">
          <option value=""><?php esc_attr_e('No', 'eventer'); ?></option>
          <option value="1"><?php esc_attr_e('Yes', 'eventer'); ?></option>
        </select>
        <label><?php esc_html_e('Use this field to set common ticket count for all tickets.'); ?></label>
        <input type="text" placeholder="<?php esc_attr_e('Ticket count', 'eventer'); ?>" class="eventer_admin_common_count eventer-metabox-field" value="">
      </div>
      <p>
        <button disabled data-eventer="<?php echo esc_attr($original_event); ?>" data-position="save" type="button" class="button button-primary update_booked_tickets save_booked_btn eventer-metabox-field"><?php esc_html_e('Save', 'eventer'); ?></button>
      </p>
      <p>
        <button disabled data-eventer="<?php echo esc_attr($original_event); ?>" data-position="reset" type="button" class="button update_booked_tickets reset_booked_btn eventer-metabox-field"><?php esc_html_e('Reset All Dates to Default', 'eventer'); ?></button>
      </p>
      <br />
      <div id="add_field_row" class="eventer-metabox-section">
        <p class="field-description"><?php echo esc_attr_e('Do not add currency in price field, currency should be selected from PayPal Configuration Settings', 'eventer'); ?></p>
        <p class="field-description"><?php echo esc_attr_e('Events dates are listing only between two years before and two years after from current date.', 'eventer'); ?></p>
        <p class="field-description"><?php echo esc_attr_e('Ticket details are showing through original events of this language, like ticket details of default language of this post.', 'eventer'); ?></p>
        <p class="field-description"><?php echo esc_attr_e('Label: A custom label is a small piece of text you can show next to event title. This can be used to show a small message like "Upcoming" next to event title. This field can be used differently for each selected date in this section.', 'eventer'); ?></p>
      </div>
    </div>
  </div>
<?php
}
/**
 * Save post action, process fields
 */
function wceventer_update_event_tickets_data($post_id, $post_object)
{
  //echo count($_POST['wceventer_ticket_name']); exit;
  if (!isset($_POST['eventer_event_tickets_meta_box_nonce'])) {
    return;
  }
  $update_meta_box = array();
  // Verify that the nonce is valid.
  if (!wp_verify_nonce($_POST['eventer_event_tickets_meta_box_nonce'], 'eventer_event_schedule_meta_box')) {
    return;
  }
  // Doing revision, exit earlier **can be removed**
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
  // Doing revision, exit earlier
  if ('revision' == $post_object->post_type)
    return;
  // Verify authenticity
  // Check the user's permissions.
  if (isset($_POST['post_type']) && 'eventer' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) {
      return;
    }
  } else {
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }
  }
  $booked_tickets = $update_meta_box = $new_booked_tickets = array();
  if (isset($_POST['wceventer_ticket_name'])) {
    $total_tickets = count($_POST['wceventer_ticket_name']);
    $unique_identification = array();
    if (isset($_POST['eventer_ticket_optional_settings']) && $_POST['eventer_ticket_optional_settings'] == 'all') {
      update_option('abcd', 'entering');
      global $wpdb;
      $table_name_tickets = $wpdb->prefix . "eventer_tickets";
      $wpdb->delete($table_name_tickets, array('event' => $post_id));
    }
    for ($i = 0; $i < $total_tickets; $i++) {
      if (!empty($_POST['wceventer_ticket_name'][$i]) || get_post_type($_POST['wceventer_ticket_status' . $i]) == 'product') {
        //if($i==5) exit;
        $ticket_count = (isset($_POST['eventer_common_ticket_count']) && $_POST['eventer_common_ticket_count'] != '') ? $_POST['eventer_common_ticket_count'] : $_POST['wceventer_ticket_number'][$i];
        $ticket_name = $_POST['wceventer_ticket_name'][$i];
        $ticket_price = $_POST['wceventer_ticket_price'][$i];
        $ticket_enabled = $_POST['eventer_ticket_activation_date'][$i];

        if ($ticket_name != '' && ($post_object->post_type == 'eventer') && $_POST['wceventer_ticket_status' . $i] == 'add') {
          $product_arg = array('post_type' => 'product', 'post_title' => $ticket_name, 'post_status' => 'publish');
          $product_id = wp_insert_post($product_arg);
          if (function_exists('icl_object_id') && class_exists('SitePress') && function_exists('wpml_add_translatable_content')) {
            wpml_add_translatable_content('post_product', $product_id, EVENTER__LANGUAGE_CODE);
          }
          $ticket_backend_generate = intval($product_id) + intval($post_id);
          wp_set_object_terms($product_id, 'eventer', 'product_cat');
          update_post_meta($product_id, '_regular_price', (float) $ticket_price);
          update_post_meta($product_id, '_price', (float) $ticket_price);
          update_post_meta($product_id, '_virtual', 'yes');
          update_post_meta($product_id, '_downloadable', 'yes');
          $update_meta_box[] = array('wceventer_ticket_id' => $product_id, 'wceventer_ticket_number' => $ticket_count);
          $booked_tickets[] = array('name' => $ticket_name, 'number' => $ticket_count, 'price' => $ticket_price, 'pid' => $product_id, 'id' => $ticket_backend_generate, 'restrict' => '', 'enabled' => $ticket_enabled);
          $new_booked_tickets[$ticket_name] = $ticket_count;
        } elseif ($ticket_name != '' && ($post_object->post_type == 'eventer') && $_POST['wceventer_ticket_status' . $i] == 'update') {
          $ticket_id = $_POST['wceventer_ticket_id'][$i];
          $ticket_backend_generate = intval($ticket_id) + intval($post_id);
          $product_arg = array('ID' => $ticket_id, 'post_type' => 'product', 'post_title' => $ticket_name, 'post_status' => 'publish');
          wp_update_post($product_arg);
          update_post_meta($ticket_id, '_regular_price', (float) $ticket_price);
          update_post_meta($ticket_id, '_price', (float) $ticket_price);
          $update_meta_box[] = array('wceventer_ticket_id' => $ticket_id, 'wceventer_ticket_number' => $ticket_count);
          $booked_tickets[] = array('name' => $ticket_name, 'number' => $ticket_count, 'price' => $ticket_price, 'pid' => $ticket_id, 'id' => $ticket_backend_generate, 'restrict' => '', 'enabled' => $ticket_enabled);
          $new_booked_tickets[$ticket_name] = $ticket_count;
        } elseif (($post_object->post_type == 'eventer') && get_post_type($_POST['wceventer_ticket_status' . $i]) == 'product') {
          $ticket_id = $_POST['wceventer_ticket_status' . $i];
          $ticket_backend_generate = intval($ticket_id) + intval($post_id);
          $ticket_name = get_the_title($ticket_id);
          $ticket_price = get_post_meta($ticket_id, '_price', true);
          $update_meta_box[] = array('wceventer_ticket_id' => $ticket_id, 'wceventer_ticket_number' => $ticket_count);
          $booked_tickets[] = array('name' => $ticket_name, 'number' => $_POST['wceventer_ticket_number'][$i], 'price' => $ticket_price, 'pid' => $ticket_id, 'id' => $ticket_backend_generate, 'restrict' => '', 'enabled' => $ticket_enabled);
          $new_booked_tickets[$ticket_name] = $ticket_count;
        } elseif ($ticket_name != '' && ($post_object->post_type == 'eventer') && $_POST['wceventer_ticket_status' . $i] == 'del') {
          $ticket_id = $_POST['wceventer_ticket_id'][$i];
          wp_trash_post($ticket_id);
        }
      }
    }
    update_post_meta($post_id, 'wceventer_tickets', $update_meta_box);
    update_post_meta($post_id, 'eventer_tickets', $booked_tickets);
    update_post_meta($post_id, 'eventer_booked_tickets', $new_booked_tickets);
  }
  update_post_meta($post_id, 'eventer_common_ticket_count', $_POST['eventer_common_ticket_count']);
  update_post_meta($post_id, 'eventer_disable_booking_before', $_POST['eventer_disable_booking_before']);
}
