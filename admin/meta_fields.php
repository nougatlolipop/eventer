<?php
defined('ABSPATH') or die('No script kiddies please!');
$woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
// Callback function to show fields for event custom post type meta box

function eventer_show_meta_box()
{
  $meta_box = apply_filters('eventer_get_details_metafields', array());
  global $post, $eventer_allowed_tags;

  // Use nonce for verification
  echo '<input type="hidden" name="eventer_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

  echo '<table class="form-table eventer-form-table">';

  foreach ($meta_box['fields'] as $field) {
    // get current post meta data
    $meta = get_post_meta($post->ID, $field['id'], true);

    echo '<tr class="' . $field['class'] . '">',
      '<td valign="top" style="width:40%"><label for="',
      $field['id'],
      '">',
      $field['name'],
      '</label></td>',
      '<td>';
    switch ($field['type']) {
      case 'text':
        echo '<input maxlength="' . $field['limit'] . '" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />', '<span class="field-description">' . $field['desc'] . '</span>';
        break;
      case 'textarea':
        echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', '<span class="field-description">' . $field['desc'] . '</span>';
        break;
      case 'select':
        $multiple = (isset($field['multiple'])) ? $field['multiple'] : '';
        $select_multi = ($multiple) ? 'multiple' : '';
        $select_start = ($multiple) ? '<select ' . $select_multi . ' name="' . $field['id'] . '[]" id="' . $field['id'] . '">' : '<select ' . $select_multi . ' name="' . $field['id'] . '" id="' . $field['id'] . '">';
        echo wp_kses($select_start, $eventer_allowed_tags);
        foreach ($field['options'] as $key => $value) {
          if (is_array($meta)) {
            echo '<option ', in_array($key, $meta) ? ' selected="selected"' : '', ' value="' . $key . '">', $value, '</option>';
          } else {
            echo '<option ', $meta == $key ? ' selected="selected"' : '', ' value="' . $key . '">', $value, '</option>';
          }
        }
        echo '</select>', '<span class="field-description">' . $field['desc'] . '</span>';
        break;
      case 'radio':
        foreach ($field['options'] as $option) {
          echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
        };
        echo '<span class="field-description">' . $field['desc'] . '</span>';
        break;
      case 'checkbox': //print_r($meta);
        echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />', '<span class="field-description">' . $field['desc'] . '</span>';
        break;
    }
    echo     '</td><td>',
      '</td></tr>';
  }

  echo '</table>';
}




function eventer_create_details_metabox($meta_box = array())
{
  $prefix = 'eventer_';

  $meta_box = array(
    'id' => 'eventer_details_meta',
    'title' => esc_html__('Event Details', 'eventer'),
    'page' => 'eventer',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
      array(
        'name' => esc_html__('Event Start Date & Time', 'eventer'),
        'desc' => esc_html__('Insert start date and time of event.', 'eventer'),
        'id' => $prefix . 'event_start_dt',
        'class' => '',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Event End Date & Time', 'eventer'),
        'desc' => esc_html__('Insert end date and time of event.', 'eventer'),
        'id' => $prefix . 'event_end_dt',
        'class' => '',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Set time', 'eventer'),
        'desc' => esc_html__('Set start and end time for each day, Ex: 1 Jan 2021 - 5 Jan 2021 for date and 14:00 - 15:00 Everyday for time', 'eventer'),
        'id' => $prefix . 'event_each_day_time',
        'class' => '',
        'type' => 'checkbox'
      ),
      array(
        'name' => esc_html__('All Day Event', 'eventer'),
        'desc' => esc_html__('Check this if you want to make this event as a full day event. Event start and end time will not be visible on the website.', 'eventer'),
        'id' => $prefix . 'event_all_day',
        'class' => '',
        'type' => 'checkbox'
      ),
      array(
        'name' => esc_html__('Featured Event', 'eventer'),
        'desc' => esc_html__('Check this if you want to make this event as featured, this will make next future event as featured , which you can modify anytime from date wise tickets section. You need to uncheck this box if using date wise ticket area for featured event.', 'eventer'),
        'id' => $prefix . 'event_featured',
        'class' => 'bordered',
        'type' => 'checkbox'
      ),
      array(
        'name' => esc_html__('Specific venue', 'eventer'),
        'desc' => esc_html__('Insert specific venue here to show on detail page. This field is useful while using venue filter for city and want to add event with in same city but with different location.', 'eventer'),
        'id' => $prefix . 'event_specific_venue',
        'class' => '',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Addition info for email', 'eventer'),
        'desc' => esc_html__('Enter the addition info to show in email. Use {event_additional_info} shortcode in the email template fields.', 'eventer'),
        'id' => $prefix . 'event_email_additional_info',
        'class' => '',
        'limit' => '',
        'type' => 'textarea',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Addition info', 'eventer'),
        'desc' => esc_html__('Enter the addition infor for event, this will show in meta section of event details page.', 'eventer'),
        'id' => $prefix . 'event_additional_info',
        'class' => 'bordered',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Event Frequency Type', 'eventer'),
        'desc' => esc_html__('If you want to repeat this event for a selected period then select the type of recurrence. Select Fixed date if you want the event to repeat every day or second day etc. or every week/month. Select weekday if you want to repeat the event on a weekday like sunday, monday etc.', 'eventer'),
        'id' => $prefix . 'event_frequency_type',
        'class' => '',
        'type' => 'select',
        'options' => array('no' => esc_html__('No', 'eventer'), '1' => esc_html__('Fixed Date', 'eventer'), '2' => esc_html__('Week Day', 'eventer'))
      ),
      array(
        'name' => esc_html__('Weekly Repeat', 'eventer'),
        'desc' => esc_html__('Select which week of month you want to repeat this event or use as week span by checking below checkbox.', 'eventer'),
        'id' => $prefix . 'event_day_month',
        'class' => '',
        'multiple' => true,
        'type' => 'select',
        'options' => array(
          'first' => esc_html__('First', 'eventer'),
          'second' => esc_html__('Second', 'eventer'),
          'third' => esc_html__('Third', 'eventer'),
          'fourth' => esc_html__('Fourth', 'eventer'),
          'last' => esc_html__('Last', 'eventer')
        )
      ),
      array(
        'name' => esc_html__('Repeat Weekly', 'eventer'),
        'desc' => esc_html__('Check to enable week span.', 'eventer'),
        'id' => $prefix . 'event_weekly_repeat',
        'class' => '',
        'type' => 'checkbox'
      ),
      array(
        'name' => esc_html__('Event Week Day', 'eventer'),
        'desc' => esc_html__('Select which day of week you want this event to repeat.', 'eventer'),
        'id' => $prefix . 'event_week_day',
        'class' => '',
        'multiple' => true,
        'type' => 'select',
        'options' => array(
          'sunday' => esc_html__('Sunday', 'eventer'),
          'monday' => esc_html__('Monday', 'eventer'),
          'tuesday' => esc_html__('Tuesday', 'eventer'),
          'wednesday' => esc_html__('Wednesday', 'eventer'),
          'thursday' => esc_html__('Thursday', 'eventer'),
          'friday' => esc_html__('Friday', 'eventer'),
          'saturday' => esc_html__('Saturday', 'eventer')
        )
      ),
      array(
        'name' => esc_html__('Event Frequency', 'eventer'),
        'desc' => esc_html__('Select the event repeat frequency for a fixed day repeatation.', 'eventer'),
        'id' => $prefix . 'event_frequency',
        'class' => '',
        'type' => 'select',
        'options' => array(
          '35' => esc_html__('Select', 'eventer'),
          '1' => esc_html__('Every Day', 'eventer'),
          '2' => esc_html__('Every Second Day', 'eventer'),
          '3' => esc_html__('Every Third Day', 'eventer'),
          '4' => esc_html__('Every Fourth Day', 'eventer'),
          '5' => esc_html__('Every Fifth Day', 'eventer'),
          '6' => esc_html__('Every Sixth Day', 'eventer'),
          '7' => esc_html__('Every Week', 'eventer'),
          '30' => esc_html__('Every Month', 'eventer')
        )
      ),
      array(
        'name' => esc_html__('Number of times to repeat event', 'eventer'),
        'desc' => esc_html__('Enter the number of times this recurring event should repeat. Take care of number you put here as more repeating times can slow down the loading speed of events on website.', 'eventer'),
        'id' => $prefix . 'event_frequency_count',
        'class' => '',
        'limit' => '3',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Exclude Dynamic Event Dates', 'eventer'),
        'desc' => esc_html__('Insert random dates here which you wish to exclude from this repeating event', 'eventer'),
        'id' => $prefix . 'event_multiple_dt_exc',
        'class' => '',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Include Dynamic Event Dates', 'eventer'),
        'desc' => esc_html__('Insert random dates here which you wish to include for this repeating event', 'eventer'),
        'id' => $prefix . 'event_multiple_dt_inc',
        'class' => 'bordered',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Custom Permalink', 'eventer'),
        'desc' => esc_html__('Insert URL here if you want the event to redirect some another link.', 'eventer'),
        'id' => $prefix . 'event_custom_permalink',
        'class' => '',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Custom Permalink Target', 'eventer'),
        'desc' => esc_html__('Select custom Permalink taget. If you want to redierct users to custom URL in a new window/tab then select Blank. To open it on same page select Self.', 'eventer'),
        'id' => $prefix . 'event_custom_permalink_target',
        'class' => '',
        'type' => 'select',
        'options' => array('_self' => esc_html__('Self', 'eventer'), '_blank' => esc_html__('Blank', 'eventer'))
      ),
      array(
        'name' => esc_html__('Custom Permalink Button', 'eventer'),
        'desc' => esc_html__('Insert text here to add button to list and grid view for event URL.', 'eventer'),
        'id' => $prefix . 'event_custom_permalink_btn',
        'class' => 'bordered',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Enable Registration', 'eventer'),
        'desc' => esc_html__('Select Yes if you want to enable registration for this event. It can be paid or free. Paid tickets info can be set below.', 'eventer'),
        'id' => $prefix . 'event_registration_swtich',
        'class' => '',
        'type' => 'select',
        'options' => array('no' => 'No', '1' => 'Yes')
      ),
      array(
        'name' => esc_html__('Registration Form', 'eventer'),
        'desc' => esc_html__('Insert shortcodes of registration form fields here if you would like to have different forms for each event, default form will work from settings field only. This area would not work for the Woocommerce payment system.', 'eventer'),
        'id' => $prefix . 'event_registration_form',
        'class' => '',
        'limit' => '',
        'type' => 'textarea',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Custom Registration Button', 'eventer'),
        'desc' => esc_html__('Insert URL here if you want the register button for event to redirect to some oher URl outside your website. Like if you want to redirect users to your event on Eventbrite website.', 'eventer'),
        'id' => $prefix . 'event_custom_registration_url',
        'class' => '',
        'limit' => '',
        'type' => 'text',
        'std' => ''
      ),
      array(
        'name' => esc_html__('Custom Registration URL Target', 'eventer'),
        'desc' => esc_html__('Select custom URL taget. If you want to redierct users to custom URL in a new window/tab then select Blank. To open it on same page select Self.', 'eventer'),
        'id' => $prefix . 'event_registration_target',
        'class' => '',
        'type' => 'select',
        'options' => array('_self' => esc_html__('Self', 'eventer'), '_blank' => esc_html__('Blank', 'eventer'))
      ),

    )
  );
  return $meta_box;
}
add_filter('eventer_get_details_metafields', 'eventer_create_details_metabox', 10, 1);
add_action('add_meta_boxes_eventer', 'eventer_add_meta_box');

// Add meta box
function eventer_add_meta_box()
{
  $meta_box = apply_filters('eventer_get_details_metafields', array());
  add_meta_box($meta_box['id'], $meta_box['title'], 'eventer_show_meta_box', $meta_box['page'], $meta_box['context'], $meta_box['priority']);
}

add_action('save_post', 'eventer_save_meta_data');

// Save data from meta box
function eventer_save_meta_data($post_id)
{
  $meta_box = apply_filters('eventer_get_details_metafields', array());

  // verify nonce
  if (!isset($_POST['eventer_meta_box_nonce']) || !wp_verify_nonce($_POST['eventer_meta_box_nonce'], basename(__FILE__))) {
    return $post_id;
  }

  // check autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $post_id;
  }

  // check permissions
  if ('page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) {
      return $post_id;
    }
  } elseif (!current_user_can('edit_post', $post_id)) {
    return $post_id;
  }
  foreach ($meta_box['fields'] as $field) {
    $old = get_post_meta($post_id, $field['id'], true);
    $new = (isset($_POST[$field['id']])) ? $_POST[$field['id']] : '';

    if ($new && $new != $old) {
      update_post_meta($post_id, $field['id'], $new);
    } elseif ('' == $new && $old) {
      delete_post_meta($post_id, $field['id'], $old);
    }
  }
}

add_action('eventer-organizer_add_form_fields', 'eventer_organizer_add_fields', 10, 1);
add_action('eventer-organizer_edit_form_fields', 'eventer_organizer_edit_fields', 10, 1);

function eventer_organizer_add_fields($tag)
{ ?>
    <!--<h3>Eventer Venue Meta Boxes</h3>-->
    <div class="form-field">
      <label for="organizer_phone"><?php esc_html_e('Organizer Phone', 'eventer') ?></label>
      <input name="organizer_phone" id="organizer_phone" type="text" value="" size="40" aria-required="true" />
      <p class="description"><?php esc_html_e('Enter phone number of this organizer where event visitors can contact the organizer.', 'eventer') ?>
    </div>
    <div class="form-field">
      <label for="organizer_email"><?php esc_html_e('Organizer Email', 'eventer') ?></label>
      <input name="organizer_email" id="organizer_email" type="text" value="" size="40" aria-required="true" />
      <p class="description"><?php esc_html_e('Enter email address of this organizer where event visitors can contact the organizer. This will also be used for the contact form available on the single event page. If you leave it blank then the contact form info will be sent to the website administrator email.', 'eventer') ?>
    </div>
    <div class="form-field">
      <label for="organizer_website"><?php esc_html_e('Organizer Website', 'eventer') ?></label>
      <input name="organizer_website" id="organizer_website" type="text" value="" size="40" aria-required="true" />
      <p class="description"><?php esc_html_e('Enter website address of this organizer which event visitors can visit for organizer information. This will be visible on single event details page.', 'eventer') ?>
    </div>
  <?php }
  function eventer_organizer_edit_fields($tag)
  { ?>
    <!--<h3>Eventer Venue Meta Boxes</h3>-->
    <table class="form-table">
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="organizer_phone"><?php esc_html_e('Organizer Phone', 'eventer'); ?></label>
        </th>
        <td>
          <input name="organizer_phone" id="organizer_phone" type="text" value="<?php echo get_term_meta($tag->term_id, 'organizer_phone', true); ?>" size="40" aria-required="true" />
          <p class="description"><?php esc_html_e('Enter phone number of this organizer where event visitors can contact the organizer.', 'eventer') ?>
        </td>
      </tr>
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="organizer_email"><?php esc_html_e('Organizer Email', 'eventer'); ?></label>
        </th>
        <td>
          <input name="organizer_email" id="organizer_email" type="text" value="<?php echo get_term_meta($tag->term_id, 'organizer_email', true); ?>" size="40" aria-required="true" />
          <p class="description"><?php esc_html_e('Enter email address of this organizer where event visitors can contact the organizer. This will also be used for the contact form available on the single event page. If you leave it blank then the contact form info will be sent to the website administrator email.', 'eventer') ?>
        </td>
      </tr>
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="organizer_website"><?php esc_html_e('Organizer Website', 'eventer'); ?></label>
        </th>
        <td>
          <input name="organizer_website" id="organizer_website" type="text" value="<?php echo get_term_meta($tag->term_id, 'organizer_website', true); ?>" size="40" aria-required="true" />
          <p class="description"><?php esc_html_e('Enter website address of this organizer which event visitors can visit for organizer information. This will be visible on single event details page.', 'eventer') ?>
        </td>
      </tr>
    </table>
    <?php }

    add_action('created_eventer-organizer', 'eventer_organizer_save_fields', 10, 1);
    add_action('edited_eventer-organizer', 'eventer_organizer_save_fields', 10, 1);

    function eventer_organizer_save_fields($term_id)
    {

      if (isset($_POST['organizer_phone'])) {
        update_term_meta($term_id, 'organizer_phone', $_POST['organizer_phone']);
      }
      if (isset($_POST['organizer_email'])) {
        update_term_meta($term_id, 'organizer_email', $_POST['organizer_email']);
      }
      if (isset($_POST['organizer_website'])) {
        update_term_meta($term_id, 'organizer_website', $_POST['organizer_website']);
      }
    }

    if (isset($_REQUEST['taxonomy'])) :
      $taxonomy = 'eventer-venue';
      if (!function_exists('eventer_venue_category_image_field')) :
        add_action($taxonomy . '_add_form_fields', 'eventer_venue_category_image_field', 10, 2);
        add_action($taxonomy . '_edit_form_fields', 'eventer_venue_category_image_field', 10, 2);
        function eventer_venue_category_image_field($tag)
        {
          if (is_object($tag)) {
            $venue_id = $tag->term_id; // Get the ID of the term we're editing
            $term_meta = get_term_meta($venue_id, 'venue_image', true);
            $image_src = wp_get_attachment_image_src($term_meta);
            $image = $image_src[0];
          } else {
            $term_meta = '';
            $image = '';
          }
          ?>
        <table class="form-table">
          <tbody>
            <tr class="form-field form-required">
              <th scope="row"><label for="image"><?php esc_html_e('Venue Image', 'eventer') ?></label></th>
              <td><?php
                        echo '<div><img id="eventer_venue_image" src ="' . esc_url($image) . '" width ="150px" height ="150px"/></div>';
                        echo '<input id="eventer_upload_venue_image" type="button" class="button button-primary" value="' . esc_html__('Upload Image', 'eventer') . '" /> ';
                        if (isset($tag->term_id)) {
                          echo '<input id="eventer_venue_image_remove" type="button" class="button button-primary" value="' . esc_html__('Remove Image', 'eventer') . '" />';
                        }
                        ?>
                <p class="description"><?php esc_html_e('Upload venue image.', 'eventer'); ?></p>
              </td>
            </tr><input type="hidden" id="venue_image_id" name="eventer_venue_id_save" value="<?php echo $term_meta; ?>" />
          </tbody>
        </table>
    <?php
        }
      endif;
      if (!function_exists('eventer_venue_category_save_image_custom_fields')) :
        add_action('created_' . $taxonomy, 'eventer_venue_category_save_image_custom_fields');
        add_action('edited_' . $taxonomy, 'eventer_venue_category_save_image_custom_fields', 10, 1);
        function eventer_venue_category_save_image_custom_fields($term_id)
        {
          if (isset($_POST['eventer_venue_id_save'])) {
            $venue_image = $_POST['eventer_venue_id_save'];
            update_term_meta($term_id, 'venue_image', $venue_image);
          }
        }
      endif;
    endif;


    add_action('eventer-venue_add_form_fields', 'eventer_venue_add_fields', 10, 1);
    add_action('eventer-venue_edit_form_fields', 'eventer_venue_edit_fields', 10, 1);

    function eventer_venue_add_fields($tag)
    { ?>
    <!--<h3>Eventer Venue Meta Boxes</h3>-->
    <div class="form-field">
      <label for="venue_address"><?php esc_html_e('Venue Location', 'eventer') ?></label>
      <input name="venue_address" id="venue_address" type="text" value="" size="40" aria-required="true" />
      <p class="description"><?php esc_html_e('Start typing the address of this venue to get the results.', 'eventer') ?>
    </div>
    <div class="form-field">
      <label for="venue_coordinates"><?php esc_html_e('Venue Map Co-ordinates', 'eventer') ?></label>
      <input name="venue_coordinates" id="venue_coordinates" type="text" value="" size="40" aria-required="true" />
      <p class="description"><?php esc_html_e('Enter Map coordinates here if address is not sufficient to generate Google Map.', 'eventer') ?>
    </div>
  <?php }
  function eventer_venue_edit_fields($tag)
  { ?>
    <!--<h3>Eventer Venue Meta Boxes</h3>-->
    <table class="form-table">
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="venue_address"><?php esc_html_e('Venue Location Address', 'eventer'); ?></label>
        </th>
        <td>
          <input name="venue_address" id="venue_address" type="text" value="<?php echo get_term_meta($tag->term_id, 'venue_address', true); ?>" size="40" aria-required="true" />
          <p class="description"><?php esc_html_e('Start typing the address of this venue to get the results.', 'eventer') ?>
        </td>
      </tr>
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="venue_coordinates"><?php esc_html_e('Venue Map Co-ordinates', 'eventer'); ?></label>
        </th>
        <td>
          <input name="venue_coordinates" id="venue_coordinates" type="text" value="<?php echo get_term_meta($tag->term_id, 'venue_coordinates', true); ?>" size="40" aria-required="true" />
          <p class="description"><?php esc_html_e('Enter Map coordinates here if address is not sufficient to generate Google Map.', 'eventer') ?>
        </td>
      </tr>
    </table>
  <?php }

  add_action('created_eventer-venue', 'eventer_venue_save_fields', 10, 1);
  add_action('edited_eventer-venue', 'eventer_venue_save_fields', 10, 1);

  function eventer_venue_save_fields($term_id)
  {

    if (isset($_POST['venue_address'])) {
      update_term_meta($term_id, 'venue_address', $_POST['venue_address']);
    }
    if (isset($_POST['venue_coordinates'])) {
      update_term_meta($term_id, 'venue_coordinates', $_POST['venue_coordinates']);
    }
  }

  add_action('eventer-category_add_form_fields', 'eventer_category_add_fields', 10, 1);
  add_action('eventer-category_edit_form_fields', 'eventer_category_edit_fields', 10, 1);

  function eventer_category_add_fields($tag)
  { ?>
    <!--<h3>Eventer Category Color Meta Boxes</h3>-->
    <div class="form-field">
      <label for="category_color"><?php esc_html_e('Event Category Color', 'eventer') ?></label>
      <input name="category_color" id="category_color" type="text" value="" size="40" aria-required="true" />
      <p class="description"><?php esc_html_e('Select a color for this event category. This will be used for design purpose in events list, grid and calendar.', 'eventer') ?>
    </div>
  <?php }
  function eventer_category_edit_fields($tag)
  { ?>
    <!--<h3>Eventer Category Color Meta Boxes</h3>-->
    <table class="form-table">
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="category_color"><?php esc_html_e('Event Category Color', 'eventer'); ?></label>
        </th>
        <td>
          <input name="category_color" id="category_color" type="text" value="<?php echo get_term_meta($tag->term_id, 'category_color', true); ?>" size="40" aria-required="true" />
          <p class="description"><?php esc_html_e('Select a color for this event category. This will be used for design purpose in events list, grid and calendar.', 'eventer') ?>
        </td>
      </tr>
    </table>
    <?php }

    add_action('created_eventer-category', 'eventer_category_save_fields', 10, 1);
    add_action('edited_eventer-category', 'eventer_category_save_fields', 10, 1);

    function eventer_category_save_fields($term_id)
    {

      if (isset($_POST['category_color'])) {
        update_term_meta($term_id, 'category_color', $_POST['category_color']);
      }
    }
    //Additional services meta box
    add_action('add_meta_boxes_eventer', 'eventer_additional_services', 10);
    function eventer_additional_services()
    {
      add_meta_box('additional-services', esc_html__('Additional Services', 'eventer'), 'eventer_additional_services_metabox', 'eventer', 'normal', 'default');
    }
    function eventer_additional_services_metabox()
    {
      global $post;
      echo '<table id="eventer_additional_services_fieldset" width="100%">
	<thead>
	</thead>
	<tbody>';
      $repeatable_fields = get_post_meta($post->ID, 'eventer_additional_services', true);
      $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
      wp_nonce_field('eventer_additional_services_nonce', 'eventer_additional_services_nonce');
      $field_start = 0;
      $tickets_ids = $tickets_fields = '';
      $eventer_term_id = get_term_by('slug', 'eventer_services', 'product_cat');
      $tickets_fields .= '<option value="ewoo">' . esc_html__('Add new', 'eventer') . '</option>';
      if (!is_wp_error($eventer_term_id) && !empty($eventer_term_id)) {
        $tickets_ids = get_objects_in_term($eventer_term_id->term_id, 'product_cat');
        foreach ($tickets_ids as $ids) {
          if (get_post_type($ids) != 'product' || get_post_status($ids) != 'publish') continue;
          $tickets_fields .= '<option value="' . $ids . '">' . get_the_title($ids) . '</option>';
        }
      }
      if ($woocommerce_ticketing == 'on') {
        $service_fields = '<select name="service_label[]" class="widefat">' . $tickets_fields . '</select>';
        $price_field_disabled = 'disabled';
        $woo_product_title = esc_html__('Enter Service', 'eventer');
      } else {
        $service_fields = '<input type="text" class="widefat" name="service_label[]" />';
        $price_field_disabled = '';
        $woo_product_title = esc_html__('Price', 'eventer');
      }
      if ($repeatable_fields) :

        foreach ($repeatable_fields as $field) {
          $service_label = (isset($field['service'])) ? $field['service'] : '';
          $service_price = (isset($field['price'])) ? $field['price'] : '';
          $service_multiple = (isset($field['multiple'])) ? $field['multiple'] : '';
          $multiple_checked = ($service_multiple == "1") ? 'checked' : '';
          $tickets_mandatory = (isset($field['tickets_mandatory'])) ? $field['tickets_mandatory'] : '';
          $services_checked = ($tickets_mandatory == "1") ? 'checked' : '';

          $service_type1 = (isset($field['type1'])) ? $field['type1'] : '';
          $type1_price1 = (isset($field['price1'])) ? $field['price1'] : '';

          $service_type2 = (isset($field['type2'])) ? $field['type2'] : '';
          $type1_price2 = (isset($field['price2'])) ? $field['price2'] : '';

          $service_type3 = (isset($field['type3'])) ? $field['type3'] : '';
          $type1_price3 = (isset($field['price3'])) ? $field['price3'] : '';

          $service_type4 = (isset($field['type4'])) ? $field['type4'] : '';
          $type1_price4 = (isset($field['price4'])) ? $field['price4'] : '';
          $tickets_ids = $tickets_field = '';
          $eventer_term_id = get_term_by('slug', 'eventer_services', 'product_cat');
          $tickets_field .= '<option value="ewoo">' . esc_html__('Add new', 'eventer') . '</option>';
          if (!is_wp_error($eventer_term_id) && !empty($eventer_term_id)) {
            $tickets_ids = get_objects_in_term($eventer_term_id->term_id, 'product_cat');
            foreach ($tickets_ids as $ids) {
              if (get_post_type($ids) != 'product' || get_post_status($ids) != 'publish') continue;
              $selected = ($service_label == $ids) ? 'selected' : '';
              $tickets_field .= '<option ' . esc_attr($selected) . ' value="' . $ids . '">' . get_the_title($ids) . '</option>';
            }
          }
          if ($woocommerce_ticketing == 'on') {
            $service_field = '<select name="service_label[]" class="widefat">' . $tickets_field . '</select>';
            $price_field_disabled = 'hidden';
            $woo_product_title_saved = esc_html__('Remove Product', 'eventer');
            $remove_product_field = '<label><input type="checkbox" value="1" class="widefat" name="service_tickets_remove' . esc_attr($field_start) . '" />' . esc_html__('Yes', 'eventer') . '</label><input type="hidden" value="" class="widefat" name="service_price[]" />';
          } else {
            $service_field = '<input type="text" class="widefat" value="' . esc_attr($service_label) . '" name="service_label[]" />';
            $price_field_disabled = 'text';
            $woo_product_title_saved = esc_html__('Price', 'eventer');
            $remove_product_field = '<input type="' . esc_attr($price_field_disabled) . '" value="' . esc_attr($service_price) . '" class="widefat" name="service_price[]" />';
          }
          ?>
        <tr class="eventer-add-services saved_services">
          <td>
            <table width="100%">
              <tr>
                <th colspan="4" align="left">
                  <h3><?php esc_html_e('Add a Service', 'eventer'); ?></h3>
                </th>
              </tr>
              <tr>
                <td width="25%"><strong><?php esc_html_e('Name', 'eventer'); ?></strong></td>
                <td width="25%"><strong><?php echo esc_attr($woo_product_title_saved); ?></strong></td>
                <td width="25%"><strong><?php esc_html_e('Multiple choices?', 'eventer'); ?></strong></td>
                <td width="25%"><strong><?php esc_html_e('Mandatory?', 'eventer'); ?></strong></td>
              </tr>
              <tr>
                <td>
                  <?php echo $service_field; ?>
                </td>
                <td><?php echo $remove_product_field; ?></td>
                <td><label><input type="checkbox" <?php echo esc_attr($multiple_checked); ?> value="1" class="widefat" name="service_multiple<?php echo esc_attr($field_start); ?>" /><?php esc_html_e('Yes', 'eventer'); ?></label></td>
                <td><label><input type="checkbox" <?php echo esc_attr($services_checked); ?> value="1" class="widefat" name="service_tickets_mandatory<?php echo esc_attr($field_start); ?>" /><?php esc_html_e('Yes', 'eventer'); ?></label></td>
              </tr>
              <tr>
                <th colspan="4" align="left">
                  <h4><?php esc_html_e('Service Variants', 'eventer'); ?></h4>
                </th>
              </tr>
              <tr>
                <td><strong><?php esc_html_e('Variant1 name', 'eventer'); ?></strong></td>
                <td><strong><?php esc_html_e('Variant1 price', 'eventer'); ?></strong></td>
                <td><strong><?php esc_html_e('Variant2 name', 'eventer'); ?></strong></td>
                <td><strong><?php esc_html_e('Variant2 price', 'eventer'); ?></strong></td>
              </tr>
              <tr>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($service_type1); ?>" name="sub_service1[]" /></td>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($type1_price1); ?>" name="sub_price1[]" /></td>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($service_type2); ?>" name="sub_service2[]" /></td>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($type1_price2); ?>" name="sub_price2[]" /></td>
              </tr>
              <tr>
                <td><strong><?php esc_html_e('Variant3 name', 'eventer'); ?></strong></td>
                <td><strong><?php esc_html_e('Variant3 price', 'eventer'); ?></strong></td>
                <td><strong><?php esc_html_e('Variant4 name', 'eventer'); ?></strong></td>
                <td><strong><?php esc_html_e('Variant4 price', 'eventer'); ?></strong></td>
              </tr>
              <tr>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($service_type3); ?>" name="sub_service3[]" /></td>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($type1_price3); ?>" name="sub_price3[]" /></td>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($service_type4); ?>" name="sub_service4[]" /></td>
                <td><input type="text" class="widefat" value="<?php echo esc_attr($type1_price4); ?>" name="sub_price4[]" /></td>
              </tr>
              <tr>
                <td colspan="4"><input class="remove-row button" type="button" value="<?php esc_html_e('Remove', 'eventer'); ?>"></td>
              </tr>
            </table>
          </td>
        </tr>
      <?php
            $field_start++;
          } else :
          // show a blank one
          ?>
      <tr class="eventer-add-services saved_services">
        <td>
          <table width="100%">
            <tr>
              <th colspan="4" align="left">
                <h3><?php esc_html_e('Add a Service', 'eventer'); ?></h3>
              </th>
            </tr>
            <tr>
              <td width="25%"><strong><?php esc_html_e('Name', 'eventer'); ?></strong></td>
              <td width="25%"><strong><?php echo esc_attr($woo_product_title); ?></strong></td>
              <td width="25%"><strong><?php esc_html_e('Multiple choices?', 'eventer'); ?></strong></td>
              <td width="25%"><strong><?php esc_html_e('Mandatory?', 'eventer'); ?></strong></td>
            </tr>
            <tr>
              <td><?php echo $service_fields; ?>
              </td>
              <td><input type="text" class="widefat" name="service_price[]" /></td>
              <td><label><input type="checkbox" value="1" class="widefat" name="service_multiple<?php echo esc_attr($field_start); ?>" /><?php esc_html_e('Yes', 'eventer'); ?></label></td>
              <td><label><input type="checkbox" value="1" class="widefat" name="service_tickets_mandatory<?php echo esc_attr($field_start); ?>" /><?php esc_html_e('Yes', 'eventer'); ?></label></td>
            </tr>
            <tr>
              <th colspan="4" align="left">
                <h4><?php esc_html_e('Service Variants', 'eventer'); ?></h4>
              </th>
            </tr>
            <tr>
              <td><strong><?php esc_html_e('Variant1 name', 'eventer'); ?></strong></td>
              <td><strong><?php esc_html_e('Variant1 price', 'eventer'); ?></strong></td>
              <td><strong><?php esc_html_e('Variant2 name', 'eventer'); ?></strong></td>
              <td><strong><?php esc_html_e('Variant2 price', 'eventer'); ?></strong></td>
            </tr>
            <tr>
              <td><input type="text" class="widefat" name="sub_service1[]" /></td>
              <td><input type="text" class="widefat" name="sub_price1[]" /></td>
              <td><input type="text" class="widefat" name="sub_service2[]" /></td>
              <td><input type="text" class="widefat" name="sub_price2[]" /></td>
            </tr>
            <tr>
              <td><strong><?php esc_html_e('Variant3 name', 'eventer'); ?></strong></td>
              <td><strong><?php esc_html_e('Variant3 price', 'eventer'); ?></strong></td>
              <td><strong><?php esc_html_e('Variant4 name', 'eventer'); ?></strong></td>
              <td><strong><?php esc_html_e('Variant4 price', 'eventer'); ?></strong></td>
            </tr>
            <tr>
              <td><input type="text" class="widefat" name="sub_service3[]" /></td>
              <td><input type="text" class="widefat" name="sub_price3[]" /></td>
              <td><input type="text" class="widefat" name="sub_service4[]" /></td>
              <td><input type="text" class="widefat" name="sub_price4[]" /></td>
            </tr>
            <tr>
              <td colspan="4"><input class="remove-row button" type="button" value="<?php esc_html_e('Remove', 'eventer'); ?>"></td>
            </tr>
          </table>
        </td>
      </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text eventer-add-services">
      <td>
        <table width="100%">
          <tr>
            <th colspan="4" align="left">
              <h3><?php esc_html_e('Add a Service', 'eventer'); ?></h3>
            </th>
          </tr>
          <tr>
            <td width="25%"><strong><?php esc_html_e('Name', 'eventer'); ?></strong></td>
            <td width="25%"><strong><?php echo esc_attr($woo_product_title); ?></strong></td>
            <td width="25%"><strong><?php esc_html_e('Multiple choices?', 'eventer'); ?></strong></td>
            <td width="25%"><strong><?php esc_html_e('Mandatory?', 'eventer'); ?></strong></td>
          </tr>
          <tr>
            <td><?php echo $service_fields; ?>
            </td>
            <td><input type="text" class="widefat" name="service_price[]" /></td>
            <td><label><input type="checkbox" value="1" class="widefat" name="service_multiple<?php echo esc_attr($field_start); ?>" /><?php esc_html_e('Yes', 'eventer'); ?></label></td>
            <td><label><input type="checkbox" value="1" class="widefat" name="service_tickets_mandatory<?php echo esc_attr($field_start); ?>" /><?php esc_html_e('Yes', 'eventer'); ?></label></td>
          </tr>
          <tr>
            <th colspan="4" align="left">
              <h4><?php esc_html_e('Service Variants', 'eventer'); ?></h4>
            </th>
          </tr>
          <tr>
            <td><strong><?php esc_html_e('Variant1 name', 'eventer'); ?></strong></td>
            <td><strong><?php esc_html_e('Variant1 price', 'eventer'); ?></strong></td>
            <td><strong><?php esc_html_e('Variant2 name', 'eventer'); ?></strong></td>
            <td><strong><?php esc_html_e('Variant2 price', 'eventer'); ?></strong></td>
          </tr>
          <tr>
            <td><input type="text" class="widefat" name="sub_service1[]" /></td>
            <td><input type="text" class="widefat" name="sub_price1[]" /></td>
            <td><input type="text" class="widefat" name="sub_service2[]" /></td>
            <td><input type="text" class="widefat" name="sub_price2[]" /></td>
          </tr>
          <tr>
            <td><strong><?php esc_html_e('Variant3 name', 'eventer'); ?></strong></td>
            <td><strong><?php esc_html_e('Variant3 price', 'eventer'); ?></strong></td>
            <td><strong><?php esc_html_e('Variant4 name', 'eventer'); ?></strong></td>
            <td><strong><?php esc_html_e('Variant4 price', 'eventer'); ?></strong></td>
          </tr>
          <tr>
            <td><input type="text" class="widefat" name="sub_service3[]" /></td>
            <td><input type="text" class="widefat" name="sub_price3[]" /></td>
            <td><input type="text" class="widefat" name="sub_service4[]" /></td>
            <td><input type="text" class="widefat" name="sub_price4[]" /></td>
          </tr>
          <tr>
            <td colspan="4"><input class="remove-row button" type="button" value="<?php esc_html_e('Remove', 'eventer'); ?>"></td>
          </tr>
        </table>
      </td>
    </tr>
    </tbody>
    </table>

    <p><a id="add-row" class="button button-primary" href="#"><?php esc_html_e('Add new service', 'eventer'); ?></a></p>
    <div id="add_field_row">
      <p class="field-description"><?php echo esc_attr_e('To offer additional services to the ticket registrants you must have atleast one kind of ticket made available for this event.', 'eventer'); ?></p>
      <p class="field-description"><?php echo esc_attr_e('Do not add currency in price field, currency should be selected from PayPal Configuration Settings', 'eventer'); ?></p>
      <p class="field-description"><?php echo esc_attr_e('To use additional services with Woocommerce, you need to change woocommerce layout to plugin from eventer settings page.', 'eventer'); ?></p>
    </div>
  <?php
  }

  add_action('save_post', 'eventer_additional_services_metabox_save', 1, 2);
  function eventer_additional_services_metabox_save($post_id, $post_object)
  {
    if (
      !isset($_POST['eventer_additional_services_nonce']) ||
      !wp_verify_nonce($_POST['eventer_additional_services_nonce'], 'eventer_additional_services_nonce')
    )
      return;
    if ('revision' == $post_object->post_type)
      return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return;
    if (!current_user_can('edit_post', $post_id))
      return;
    $new_services = array();
    $names = $_POST['service_label'];

    $count_services = count($names);
    if ($count_services <= 0) return;
    for ($i = 0; $i < $count_services; $i++) {
      $service_label = (isset($_POST['service_label'][$i])) ? $_POST['service_label'][$i] : '';
      $service_price = (isset($_POST['service_price'][$i])) ? $_POST['service_price'][$i] : '';
      $service_product_remove = (isset($_POST['service_tickets_remove' . $i])) ? $_POST['service_tickets_remove' . $i] : '';
      if ($service_label == '' || ($service_label == 'ewoo' && $service_price == '')) continue;
      if ($service_label == 'ewoo' && $service_price != '' && ($post_object->post_type == 'eventer')) {
        $service_label = $service_price;
        $service_price = '';
        $product_arg = array('post_type' => 'product', 'post_title' => $service_label, 'post_status' => 'publish');
        $product_id = wp_insert_post($product_arg);
        if (function_exists('icl_object_id') && class_exists('SitePress') && function_exists('wpml_add_translatable_content')) {
          wpml_add_translatable_content('post_product', $product_id, EVENTER__LANGUAGE_CODE);
        }
        wp_set_object_terms($product_id, 'eventer_services', 'product_cat');
        update_post_meta($product_id, '_regular_price', intval(1));
        update_post_meta($product_id, '_price', intval(1));
        update_post_meta($product_id, '_virtual', 'yes');
        $service_label = $product_id;
      }
      if ($service_product_remove == "1" && is_numeric($service_label)) {
        wp_trash_post($service_label);
        continue;
      }
      $service_multiple = (isset($_POST['service_multiple' . $i])) ? $_POST['service_multiple' . $i] : '';
      $service_tickets_mandatory = (isset($_POST['service_tickets_mandatory' . $i])) ? $_POST['service_tickets_mandatory' . $i] : '';
      $service_type1 = (isset($_POST['sub_service1'][$i])) ? $_POST['sub_service1'][$i] : '';
      $type1_price1 = (isset($_POST['sub_price1'][$i])) ? $_POST['sub_price1'][$i] : '';

      $service_type2 = (isset($_POST['sub_service2'][$i])) ? $_POST['sub_service2'][$i] : '';
      $type1_price2 = (isset($_POST['sub_price2'][$i])) ? $_POST['sub_price2'][$i] : '';

      $service_type3 = (isset($_POST['sub_service3'][$i])) ? $_POST['sub_service3'][$i] : '';
      $type1_price3 = (isset($_POST['sub_price3'][$i])) ? $_POST['sub_price3'][$i] : '';

      $service_type4 = (isset($_POST['sub_service4'][$i])) ? $_POST['sub_service4'][$i] : '';
      $type1_price4 = (isset($_POST['sub_price4'][$i])) ? $_POST['sub_price4'][$i] : '';
      $new_services[] = array('service' => $service_label, 'price' => $service_price, 'multiple' => $service_multiple, 'tickets_mandatory' => $service_tickets_mandatory, 'type1' => $service_type1, 'price1' => $type1_price1, 'type2' => $service_type2, 'price2' => $type1_price2, 'type3' => $service_type3, 'price3' => $type1_price3, 'type4' => $service_type4, 'price4' => $type1_price4);
    }
    update_post_meta($post_id, 'eventer_additional_services', $new_services);
  }

  function eventer_time_loop()
  {
    $time_format = eventer_get_settings('eventer_datepicker_format');
    $time_field = array();
    $start = "00:00";
    $end = "23:45";
    $tStart = strtotime($start);
    $tEnd = strtotime($end);
    $tNow = $tStart;
    while ($tNow <= $tEnd) {
      $time_set = ($time_format == '12') ? 'h:i A' : 'H:i';
      $time_field[] = date_i18n($time_set, $tNow);
      $tNow = strtotime('+15 minutes', $tNow);
    }
    return $time_field;
  }

  add_action('add_meta_boxes_eventer', 'eventer_add_time_slots');
  add_action('save_post', 'eventer_update_time_slot', 10, 2);
  /**
   * Add custom Meta Box to Posts post type
   */
  function eventer_add_time_slots()
  {
    add_meta_box('eventer-time-slot', esc_html__('Time Slot', 'vestige'), 'eventer_time_slots', 'eventer', 'normal', 'core');
  }
  /**
   * Print the Meta Box content
   */
  function eventer_time_slots()
  {
    global $post;
    wp_nonce_field('eventer_time_slot_section', 'eventer_time_slot_section_nonce');
    $time_slot = get_post_meta($post->ID, 'eventer_time_slot', true);
    ?>
    <div class="eventer-time-slot-sections">
      <table class="eventer-metabox">
        <tr>
          <th><?php esc_html_e('Title', 'eventer'); ?></td>
          <th><?php esc_html_e('Start Time', 'eventer'); ?></td>
          <th><?php esc_html_e('End Time', 'eventer'); ?></td>
          <th><?php esc_html_e('Description', 'eventer'); ?></td>
          <th><?php esc_html_e('Actions', 'eventer'); ?></td>
        </tr>
        <?php
          $slot_title = $slot_desc = $time_start = $time_end = '';
          if (!empty($time_slot)) {
            foreach ($time_slot as $slot) {
              $slot_title = (isset($slot['title'])) ? $slot['title'] : '';
              $slot_desc = (isset($slot['desc'])) ? $slot['desc'] : '';
              $time_start = (isset($slot['start'])) ? $slot['start'] : '';
              $time_end = (isset($slot['end'])) ? $slot['end'] : '';
              ?>
            <tr class="eventer-time-slot-section">
              <td>
                <input type="text" class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-title][]" value="<?php echo $slot_title; ?>">
              </td>
              <td>
                <select class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-start][]">
                  <?php
                        foreach (eventer_time_loop() as $time) {
                          $selected = ($time_start == $time) ? 'selected' : '';
                          echo '<option ' . $selected . ' value="' . $time . '">' . $time . '</option>';
                        }
                        ?>
                </select>
              </td>
              <td>
                <select class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-end][]">
                  <?php
                        foreach (eventer_time_loop() as $time) {
                          $selected = ($time_end == $time) ? 'selected' : '';
                          echo '<option ' . $selected . ' value="' . $time . '">' . $time . '</option>';
                        }
                        ?>
                </select>
              </td>
              <td>
                <input type="text" class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-desc][]" value="<?php echo $slot_desc; ?>">
              </td>
              <td>
                <input type="button" class="button eventer-time-slot-field eventer-remove-time-slot" value="<?php esc_html_e('Remove', 'eventer'); ?>">
              </td>
            </tr>
          <?php
              }
            } else {
              ?>
          <tr class="eventer-time-slot-section">
            <td>
              <input type="text" class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-title][]" value="">
            </td>
            <td>
              <select class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-start][]">
                <?php
                    foreach (eventer_time_loop() as $time) {
                      $selected = ($time_start == $time) ? 'selected' : '';
                      echo '<option ' . $selected . ' value="' . $time . '">' . $time . '</option>';
                    }
                    ?>
              </select>
            </td>
            <td>
              <select class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-end][]">
                <?php
                    foreach (eventer_time_loop() as $time) {
                      $selected = ($time_end == $time) ? 'selected' : '';
                      echo '<option ' . $selected . ' value="' . $time . '">' . $time . '</option>';
                    }
                    ?>
              </select>
            </td>
            <td>
              <input type="text" class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-desc][]" value="">
            </td>
            <td>

            </td>
          </tr>
        <?php

          }
          ?>
        <tr class="eventer-time-slot-default" style="display: none;">
          <td>
            <input type="text" class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-title][]" value="">
          </td>
          <td>
            <select class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-start][]">
              <?php
                foreach (eventer_time_loop() as $time) {
                  $selected = ($time_start == $time) ? 'selected' : '';
                  echo '<option ' . $selected . ' value="' . $time . '">' . $time . '</option>';
                }
                ?>
            </select>
          </td>
          <td>
            <select class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-end][]">
              <?php
                foreach (eventer_time_loop() as $time) {
                  $selected = ($time_end == $time) ? 'selected' : '';
                  echo '<option ' . $selected . ' value="' . $time . '">' . $time . '</option>';
                }
                ?>
            </select>
          </td>
          <td>
            <input type="text" class="eventer-time-slot-field" name="eventer_slot[eventer-time-slot-desc][]" value="">
          </td>
          <td>
            <input type="button" class="eventer-time-slot-field eventer-remove-time-slot" value="<?php esc_html_e('Remove', 'eventer'); ?>">
          </td>
        </tr>
      </table>
      <p>
        <button class="button eventer-time-slot-add eventer-metabox-field"><?php esc_html_e('Add Slot', 'eventer'); ?></button>
      </p>
      <div class="eventer-metabox-section">
        <p><?php esc_html_e('Add time slot in between the set start and end date of the event.', 'eventer'); ?></p>
        <p><?php esc_html_e('This section will not work if event is not starting and ending on a same date.', 'eventer'); ?></p>
      </div>
    </div>
    <?php
    }
    /**
     * Save post action, process fields
     */
    function eventer_update_time_slot($post_id, $post_object)
    {
      if (!isset($_POST['eventer_time_slot_section_nonce'])) {
        return;
      }
      // Verify that the nonce is valid.
      if (!wp_verify_nonce($_POST['eventer_time_slot_section_nonce'], 'eventer_time_slot_section')) {
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
      /* OK, it's safe for us to save the data now. */

      // Make sure that it is set.
      if (!isset($_POST['eventer_slot'])) {
        return;
      }
      if ($_POST['eventer_slot']) {
        // Build array for saving post meta
        $slot_data = array();
        for ($i = 0; $i < count($_POST['eventer_slot']['eventer-time-slot-title']); $i++) {
          $title = (isset($_POST['eventer_slot']['eventer-time-slot-title'][$i])) ? $_POST['eventer_slot']['eventer-time-slot-title'][$i] : '';
          if ($title == '') continue;
          $start_time = (isset($_POST['eventer_slot']['eventer-time-slot-start'][$i])) ? $_POST['eventer_slot']['eventer-time-slot-start'][$i] : '';
          $end_time = (isset($_POST['eventer_slot']['eventer-time-slot-end'][$i])) ? $_POST['eventer_slot']['eventer-time-slot-end'][$i] : '';
          $desc = (isset($_POST['eventer_slot']['eventer-time-slot-desc'][$i])) ? $_POST['eventer_slot']['eventer-time-slot-desc'][$i] : '';
          $slot_data[] = array('title' => $title, 'desc' => $desc, 'start' => $start_time, 'end' => $end_time);
        }
        update_post_meta($post_id, 'eventer_time_slot', $slot_data);
      }
    }

    if ($woocommerce_ticketing == 'on') {
      include_once(ABSPATH . 'wp-admin/includes/plugin.php');
      if (is_plugin_active('woocommerce/woocommerce.php')) {
        require_once EVENTER__PLUGIN_PATH . '/WC/wc_meta_fields.php';
        return;
      }
    }
    add_action('add_meta_boxes_eventer', 'eventer_add_event_fields');
    add_action('save_post', 'eventer_update_event_tickets_data', 10, 2);
    /**
     * Add custom Meta Box to Posts post type
     */
    function eventer_add_event_fields()
    {
      add_meta_box('eventer_event_schedule', __('Event Tickets', 'eventer'), 'eventer_event_tickets_output', 'eventer', 'normal', 'core');
      add_meta_box('eventer_event_schedule_datewise', __('Date Wise Event Booking Record', 'eventer'), 'eventer_event_tickets_output_datewise', 'eventer', 'normal', 'core');
    }
    /**
     * Print the Meta Box content
     */
    function eventer_event_tickets_output()
    {
      global $post;
      // Add an nonce field so we can check for it later.
      wp_nonce_field('eventer_event_schedule_meta_box', 'eventer_event_tickets_meta_box_nonce');
      $original_event = eventer_wpml_original_post_id($post->ID);
      $tickets = get_post_meta($original_event, 'eventer_tickets', true);
      $tickets_count = ($tickets) ? count($tickets) : 3;
      $tickets_count = ($tickets_count <= 0) ? 3 : $tickets_count;
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
        <table class="eventer-normal-tickets-table eventer-metabox">
          <tr>
            <th><strong><?php esc_html_e('Ticket Type', 'eventer'); ?></strong></td>
            <th><strong><?php esc_html_e('No of Tickets', 'eventer'); ?></strong></td>
            <th><strong><?php esc_html_e('Price', 'eventer'); ?></strong></td>
            <th><strong><?php esc_html_e('Restriction', 'eventer'); ?></strong></td>
          </tr>
          <?php
              for ($field = 0; $field < $tickets_count; $field++) {
                $ticket_name = (isset($tickets[$field]['name'])) ? $tickets[$field]['name'] : '';
                $ticket_number = (isset($tickets[$field]['number'])) ? $tickets[$field]['number'] : '';
                $ticket_price = (isset($tickets[$field]['price'])) ? $tickets[$field]['price'] : '';
                $ticket_restriction = (isset($tickets[$field]['restrict'])) ? $tickets[$field]['restrict'] : '';
                $ticket_identification = (isset($tickets[$field]['id'])) ? $tickets[$field]['id'] : '';
                $restrict_checked = ($ticket_restriction == "1") ? 'checked' : '';
                $optional_tickets = get_post_meta($post->ID, 'eventer_optional_tickets', true);
                $optional_checked = ($optional_tickets == 'optional') ? 'checked' : '';
                ?>
            <tr class="eventer-normal-ticket-row<?php echo esc_attr(intval($field) + 1); ?>" data-tickets="<?php echo esc_attr($tickets_count); ?>">
              <td><input type="text" class="meta_feat_title" name="eventer_ticket_type[]" value="<?php echo esc_attr($ticket_name); ?>" placeholder="<?php esc_html_e('Name of Ticket', 'eventer'); ?>" style="width: 100%"></td>
              <td><input type="text" class="meta_feat_title" name="eventer_ticket_number[]" value="<?php echo esc_attr($ticket_number); ?>" placeholder="<?php esc_html_e('No of Tickets', 'eventer'); ?>" style="width: 100%"></td>
              <td>
                <input class="meta_sch_title" value="<?php echo esc_attr($ticket_price); ?>" type="text" name="eventer_event_price[]" placeholder="<?php esc_html_e('Price', 'eventer'); ?>" style="width: 100%">
                <input type="hidden" class="eventer-ticket-identification" value="<?php echo esc_attr($ticket_identification); ?>" name="eventer_ticket_identification[]">
              </td>
              <td><label><input class="meta_sch_title" value="1" <?php echo esc_attr($restrict_checked); ?> type="checkbox" name="eventer_event_restrict-<?php echo esc_attr($field); ?>" style="width: 100%"></label></td>
            </tr>
          <?php } ?>
        </table>
        <p><button class="eventer-add-normal-ticket eventer-metabox-field button"><?php esc_html_e('Add Ticket', 'eventer'); ?></button></p>
        <div id="add_field_row" class="eventer-metabox-section">

          <label class="eventer-metabox-field"><input type="checkbox" name="eventer-admin-modify-default_tickets" value="modify" /> <?php esc_html_e('Modify ticket info for all dates of this event, this will reset the remaining tickets info on all dates.', 'eventer'); ?></label>
          <label class="eventer-metabox-field"><input type="checkbox" name="eventer-admin-optional-tickets" <?php echo esc_attr($optional_checked); ?> value="optional" /> <?php esc_html_e('Set tickets optional, so that user can select only single ticket among of all.', 'eventer'); ?></label>
          <label><?php esc_html_e('Use this field to set common ticket count for all tickets.'); ?></label>
          <input type="text" placeholder="<?php esc_attr_e('Ticket count', 'eventer'); ?>" class="eventer-common-ticket-count eventer-metabox-field" value="<?php echo get_post_meta($post->ID, 'eventer_common_ticket_count', true); ?>" name="eventer_common_ticket_count">
        </div>
        <br />
        <div class="eventer-metabox-section">
          <p class="field-description"><?php echo esc_attr_e('Do not add currency in price field, currency should be selected from PayPal Configuration Settings', 'eventer'); ?></p>
          <p class="field-description"><?php echo esc_attr_e('Restriction will allow only one time registration(Any number of tickets at a time) to users for that respective ticket.', 'eventer'); ?></p>
        </div>
      </div>
    <?php
      }
    }

    function eventer_event_tickets_output_datewise()
    {
      global $post;
      // Add an nonce field so we can check for it later.
      wp_nonce_field('eventer_event_schedule_meta_box', 'eventer_event_tickets_meta_box_nonce');
      $original_event = eventer_wpml_original_post_id($post->ID);
      $all_dates = get_post_meta($original_event, 'eventer_all_dates', true);
      ?>
    <div id="field_group" class="booked_ticket_section eventer-booked-tickets-record">
      <div id="field_wrap" data-date="" data-time="00:00:00">
        <div class=" eventer-metabox-section">
          <label><?php esc_attr_e('Select date to load ticket info', 'eventer'); ?></label>
          <select name="event_date_wise_bookings" class="generate_eventer_bookings eventer-metabox-field" data-eventer="<?php echo esc_attr($post->ID); ?>">
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
            <select name="event_time_wise_bookings" class="generate_eventer_bookings_slot eventer-metabox-field" data-eventer="<?php echo esc_attr($post->ID); ?>">
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

        <div class="field_row">
          <table class="eventer-metabox">
            <tr>
              <th><strong><?php esc_html_e('Ticket Type', 'eventer'); ?></strong></td>
              <th><strong><?php esc_html_e('Available Tickets', 'eventer'); ?></strong></td>
              <th><strong><?php esc_html_e('Price', 'eventer'); ?></strong></td>
              <th><strong><?php esc_html_e('Restriction', 'eventer'); ?></strong></td>
            </tr>
            <?php

              $tickets = get_post_meta($original_event, 'eventer_tickets', true);
              $tickets = (!empty($tickets)) ? $tickets : array();
              $tickets_count = count($tickets);
              $tickets_count = ($tickets_count <= 0) ? 3 : $tickets_count;
              for ($field = 0; $field < $tickets_count; $field++) {
                $random_ticket_number = $post->ID + $field;
                $ticket_backend_generate = $random_ticket_number * 2648;
                ?>
              <tr class="booked_eventer_section">
                <td><input disabled type="text" class="meta_feat_title booked_record_event_title booked_fields" value="" placeholder="<?php esc_html_e('Name of Ticket', 'eventer'); ?>" style="width: 100%"></td>
                <td><input disabled type="text" class="meta_feat_title booked_record_event_number booked_fields" value="" placeholder="<?php esc_html_e('Available Tickets', 'eventer'); ?>" style="width: 100%"></td>
                <td><input disabled class="meta_sch_title booked_record_event_price booked_fields" value="" type="text" placeholder="<?php esc_html_e('Price', 'eventer'); ?>" style="width: 100%"></td>
                <td><label><input class="meta_sch_title booked_record_event_restrict booked_fields" value="1" type="checkbox" style="width: 100%"></label></td>
                <td style="display: none;"><input class="meta_sch_title booked_record_event_id booked_fields" type="hidden"></td>
              </tr>
            <?php } ?>
          </table>
        </div>
        <hr>
        <div class="eventer-metabox-section">
          <label><?php esc_html_e('Add custom label for the selected date event', 'eventer'); ?></label><br>
          <input type="text" class="eventer_admin_badge booked_record_event_badge eventer-metabox-field" placeholder="<?php esc_html_e('Add Label', 'eventer'); ?>">
          <label><?php esc_html_e('Make selected date event to be featured', 'eventer'); ?></label><br>
          <select class="eventer_admin_featured booked_record_event_featured eventer-metabox-field">
            <option value=""><?php esc_attr_e('No', 'eventer'); ?></option>
            <option value="1"><?php esc_attr_e('Yes', 'eventer'); ?></option>
          </select>
          <label><?php esc_html_e('Total remaining.'); ?></label>
          <input type="text" placeholder="<?php esc_attr_e('Ticket count', 'eventer'); ?>" class="eventer_admin_common_count eventer-metabox-field" name="eventer-common-ticket-count" value="">
        </div>
        <p>
          <button disabled data-eventer="<?php echo esc_attr($post->ID); ?>" data-position="save" type="button" class="button button-primary update_booked_tickets save_booked_btn eventer-metabox-field"><?php esc_html_e('Save', 'eventer'); ?></button>
        </p>
        <p>
          <button disabled data-eventer="<?php echo esc_attr($post->ID); ?>" data-position="reset" type="button" class="button update_booked_tickets reset_booked_btn eventer-metabox-field"><?php esc_html_e('Reset All Dates to Default', 'eventer'); ?></button>
        </p>
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
  function eventer_update_event_tickets_data($post_id, $post_object)
  {
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
    $booked_tickets = array();
    if (isset($_POST['eventer_ticket_type'])) {
      for ($i = 0; $i < count($_POST['eventer_ticket_type']); $i++) {
        if (!empty($_POST['eventer_ticket_type'][$i])) {
          $ticket_counting = (isset($_POST['eventer_common_ticket_count']) && $_POST['eventer_common_ticket_count'] != '') ? $_POST['eventer_common_ticket_count'] : $_POST['eventer_ticket_number'][$i];
          $ticket_backend_generate = (isset($_POST['eventer_ticket_identification'][$i]) && $_POST['eventer_ticket_identification'][$i] != '') ? $_POST['eventer_ticket_identification'][$i] : mt_rand();
          $restrict = (isset($_POST['eventer_event_restrict-' . $i]) && $_POST['eventer_event_restrict-' . $i] == "1") ? "1" : '0';
          $update_meta_box[] = array('name' => $_POST['eventer_ticket_type'][$i], 'number' => $ticket_counting, 'price' => $_POST['eventer_event_price'][$i], 'restrict' => $restrict, 'id' => $ticket_backend_generate);
          $booked_tickets[$_POST['eventer_ticket_type'][$i]] = $ticket_counting;
        }
      }
      if (isset($_POST['eventer-admin-modify-default_tickets']) && $_POST['eventer-admin-modify-default_tickets'] == 'modify') {
        global $wpdb;
        $table_name_tickets = $wpdb->prefix . "eventer_tickets";
        $wpdb->delete($table_name_tickets, array('event' => $post_id));
      }
      update_post_meta($post_id, 'eventer_tickets', $update_meta_box);
      update_post_meta($post_id, 'eventer_booked_tickets', $booked_tickets);
      update_post_meta($post_id, 'eventer_common_ticket_count', $_POST['eventer_common_ticket_count']);
      $ticket_optional = (isset($_POST['eventer-admin-optional-tickets']) && $_POST['eventer-admin-optional-tickets'] == 'optional') ? 'optional' : '';
      update_post_meta($post_id, 'eventer_optional_tickets', $ticket_optional);
    }
  }
