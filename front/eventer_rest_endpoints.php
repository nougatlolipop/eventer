<?php
function eventer_get_dynamic_meta($data, $post, $request)
{

  if (isset($_GET['custom_fields_get'])) {
    $custom_keys = (!empty($_GET['custom_fields_get'])) ? $_GET['custom_fields_get'] : array();
    if (!empty($custom_keys)) {
      $_data = $data->data;
      foreach ($custom_keys as $key) {
        $_data[$key] = get_post_meta($post->ID, $key, true);
      }
    }
    $data->data = $_data;
  }

  return $data;
}
add_filter('rest_prepare_eventer', 'eventer_get_dynamic_meta', 10, 3);
add_action('rest_api_init', 'eventer_application_event_list');
function eventer_application_event_list($request)
{
  register_rest_route('imi', 'eventer/', array(
    'methods' => 'GET',
    'callback' => 'eventer_application_get_events',
    'permission_callback' => '__return_true'
  ));
}
function eventer_application_get_events($request = null)
{
  update_post_meta(1, 'eventer_application', "starting1");
  $eventers = array();
  $message = "";
  $event_args = array('post_type' => 'eventer', 'posts_per_page' => -1);
  $event_list = new WP_Query($event_args);
  if ($event_list->have_posts()) : while ($event_list->have_posts()) : $event_list->the_post();
      $eventers[] = get_the_title() . '::' . get_the_ID();
    endwhile;
  else :
    $message = "Sorry, no event found.";
  endif;
  wp_reset_postdata();
  $response = array("ID" => $eventers, "msg" => $message);

  $parameters = $request->get_json_params();

  return rest_ensure_response($response);
}
add_action('rest_api_init', 'eventer_application_event_scan');
function eventer_application_event_scan($request)
{
  register_rest_route('imi', 'scan/', array(
    'methods' => 'POST',
    'callback' => 'eventer_application_scan_events',
    'permission_callback' => '__return_true'
  ));
}
function eventer_application_scan_events($request = null)
{
  $parameters = $request->get_json_params();
  $event = (isset($parameters['event'])) ? $parameters['event'] : '';
  $date = (isset($parameters['date'])) ? $parameters['date'] : '';
  $code = (isset($parameters['code'])) ? $parameters['code'] : '';
  if ($code != '') {
    $codes = explode("-", $code);
    $code = $codes[0];
  }
  $message = '';
  if ($event == "") {
    $message = "Sorry, no event found.";
  }
  if (date_i18n('Y-m-d', strtotime($date)) < date_i18n('Y-m-d')) {
    $message = "please select date in future";
  }
  if ($code == "") {
    $message = "No barcode found";
  }
  $registrant = eventer_get_registrant_details("id", $code);
  $eventers = array('ID' => "", 'Title' => "", 'Date' => "", 'name' => "", 'email' => "", "status" => "", "amount" => "");
  //$message = "Sorry, no details found";
  if ($registrant) {
    $registrant_email = $registrant->email;
    $ticket_id = $registrant->id;
    $amount = $registrant->amount;
    $username = $registrant->username;
    $status = $registrant->status;
    $event_date = $registrant->eventer_date;
    $event_id = $registrant->eventer;
    $user = unserialize($registrant->user_system);
    $tickets = (isset($user['tickets'])) ? $user['tickets'] : '';
    $woo = "";
    if (!empty($tickets)) {
      foreach ($tickets as $ticket) {
        $event_woo = $ticket['event'];
        $date_woo = $ticket['date'];
        if ($event_woo == $event && date_i18n("Y-m-d", strtotime($date)) == date_i18n("Y-m-d", $date_woo)) {
          $woo = "1";
          break;
        }
      }
    }
    if ($woo == "1") {
      $eventers = array('ID' => $ticket_id, 'Title' => get_the_title($event), 'Date' => date_i18n("Y-m-d", strtotime($date)), 'name' => $username, 'email' => $registrant_email, "status" => $status, "amount" => $amount);
    } elseif ($event_date == date_i18n('Y-m-d', strtotime($date)) && $event_id == $event) {
      $eventers = array('ID' => $ticket_id, 'Title' => get_the_title($event), 'Date' => date_i18n("Y-m-d", strtotime($date)), 'name' => $username, 'email' => $registrant_email, "status" => $status, "amount" => $amount);
    } else {
      $eventers = array('ID' => "", 'Title' => "", 'Date' => "", 'name' => "", 'email' => "", "status" => "", "amount" => "");
      $message = "Sorry, ticket do not mach with the selected event";
    }
  } else {
    $eventers = array('ID' => "abcd", 'Title' => "", 'Date' => "", 'name' => "", 'email' => "", "status" => "", "amount" => "");
    $message = "Sorry, no details found";
  }
  $response = array("scan" => $eventers, "msg" => $message);

  return rest_ensure_response($response);
}

add_action('rest_api_init', 'eventer_application_event_checkin');
function eventer_application_event_checkin($request)
{
  register_rest_route('imi', 'checkin/', array(
    'methods' => 'POST',
    'callback' => 'eventer_application_checkin_events',
    'permission_callback' => '__return_true'
  ));
}
function eventer_application_checkin_events($request = null)
{
  $parameters = $request->get_json_params();
  $registrant = (isset($parameters['registrant'])) ? $parameters['registrant'] : '';
  $woocommerce_events = eventer_get_settings('eventer_enable_woocommerce_ticketing');
  $registrants = eventer_get_registrant_details('id', $registrant);
  if ($woocommerce_events == 'on') {
    $tickets_updated = array();
    $ticket_exist = $date_verify = $proceed_further = '';
    $user_system = unserialize($registrants->user_system);
    $tickets = (isset($user_system['tickets'])) ? $user_system['tickets'] : array();
    if (!empty($tickets)) {
      foreach ($tickets as $ticket) {
        $check_checkin_status = (isset($ticket['checkin'])) ? $ticket['checkin'] : '';
        $ticket['checkin'] = $ticket['checkin_date'] = '';
        $ticket_exist = '1';
        $date_verify = '1';
        if ($ticket_exist != '' && $date_verify != '') {
          $proceed_further = '1';
          $ticket['checkin'] = '1';
          $ticket['checkin_date'] = date_i18n('Y-m-d H:i:s');
          $tickets_updated[] = $ticket;
        }
      }
      if ($proceed_further != '' && $check_checkin_status == '') {
        $user_system['tickets'] = $tickets_updated;
        eventer_update_registrant_details(array('user_system' => serialize($user_system)), $registrant, array("%s", "%s"));
        $msg = "Successfully check-in";
      } elseif ($check_checkin_status != '') {
        $msg = "This ticket is already checked in";
      }
    } else {
      $msg = "It seems the ticket is not related to the details you submiited above.";
    }
  } else {
    $user_system = unserialize($registrants->user_system);
    if (isset($user_system['checkin']) && $user_system['checkin'] == '1') {
      $msg = "This ticket is already checked in";
    } else {
      $user_system['checkin'] = "1";
      $user_system['checkin_date'] = date_i18n('Y-m-d H:i:s');
      eventer_update_registrant_details(array('user_system' => serialize($user_system)), $registrant, array("%s", "%s"));
      $msg = "Successfully check-in";
    }
  }
  $response = array("scan" => "", "msg" => $msg);

  return rest_ensure_response($response);
}

//add_filter( "rest_prepare_post_type", "eventer_new_function", 10, 2);
function eventer_get_terms_data($object, $field_name, $request)
{
  $array =  (array) $object;
  $values = array();

  if ($field_name == 'eventer_organizer' || $field_name == 'eventer_venue') {
    $new_taxonomy = ($field_name == 'eventer_organizer') ? 'eventer-organizer' : 'eventer-venue';
    $terms = wp_get_object_terms($array['id'], $new_taxonomy);
    if (is_wp_error($terms)) return '';
    foreach ($terms as $term) {
      $name = $term->name;
      $all_metas = get_term_meta($term->term_id);
      $metas = array();
      if (!empty($all_metas)) {
        foreach ($all_metas as $key => $value) { //print_r($value[0]);
          $metas[$key] = get_term_meta($term->term_id, $key, true);
        }
      }
      $values = array_merge(array('term' => $name), $metas);
      break;
    }
  }
  return $values;
}
add_action('rest_api_init', function () {
  $term_values = array('eventer_organizer', 'eventer_venue');
  foreach ($term_values as $field) {
    register_rest_field(
      'eventer',
      $field,
      array(
        'get_callback'    => 'eventer_get_terms_data',
        'update_callback' => null,
        'schema'          => null,
      )
    );
  }
});

add_action('rest_api_init', function () {
  register_rest_route('imithemes', 'form_settings/', array(
    'methods' => 'POST',
    'callback' => 'eventer_update_form_settings',
    'permission_callback' => '__return_true'
  ));
});

function eventer_update_form_settings($request)
{
  if (isset($_POST['form_id'])) {
    $form_options = (get_option('eventer_forms_data') == '') ? array() : get_option('eventer_forms_data');
    $dynamic = (isset($_POST['dynamic'])) ? array_map('stripslashes_deep', $_POST['dynamic']) : '';
    $sections = (isset($_POST['sections'])) ? $_POST['sections'] : '';
    $number = (isset($_POST['number'])) ? $_POST['number'] : '';
    $status = (isset($_POST['status'])) ? $_POST['status'] : '';
    $name = (isset($_POST['name'])) ? $_POST['name'] : '';
    unset($form_options[$_POST['form_id']]);
    $form_options[$_POST['form_id']] = array('dynamic' => $dynamic, 'sections' => $sections, 'number' => $number, 'status' => $status, 'name' => $name);
    update_option('eventer_forms_data', $form_options);
  }
}

/*add_action('rest_api_init', function(){
   foreach( array( 'eventer_add_form_disabled_fields' ) as $field ) {
      register_rest_field( 'page',
         $field,
            array(
               'get_callback'    => 'eventer_get_meta_fields_rest',
               'update_callback' => 'eventer_update_meta_fields_rest',
               'schema'          => null,
            )
        );
    }
});*/

add_action("rest_insert_eventer", function ($post, $request, $creating) {

  $terms = $request->get_param('terms');
  $add_new_event = $request->get_param('add_new_event');
  $custom_fields = $request->get_param('fields');
  if (!empty($custom_fields)) {
    $already_added_data_tickets = get_user_meta(get_current_user_id(), 'tickets', true);
    $already_added_data_tickets = (empty($already_added_data_tickets)) ? array() : $already_added_data_tickets;
    $tickets = '';
    foreach ($custom_fields as $key => $value) {
      if ($key == 'eventer_tickets') {
        $tickets = $value;
      }
      update_post_meta($post->ID, $key, $value);
    }
    if (!empty($tickets)) {
      foreach ($tickets as $ticket) {
        $pid = (isset($ticket['pid'])) ? $ticket['pid'] : wp_rand(100, 100000000000000000);
        $already_added_data_tickets[$pid] = $ticket;
      }
      update_user_meta(get_current_user_id(), 'tickets', $already_added_data_tickets);
    }
  }
  if ($add_new_event == '1') {
    global $wpdb;
    $table_name_tickets = $wpdb->prefix . "eventer_tickets";
    $wpdb->delete($table_name_tickets, array('event' => $post->ID));
  }
  if (!empty($terms)) {
    foreach ($terms as $term) {
      if (!isset($term['term']) || $term['term'] == '') continue;
      $term_id = wp_set_object_terms($post->ID, $term['term'], $term['taxonomy']);
      if (is_numeric($term['term']) || !isset($term['meta'])) continue;
      foreach ($term['meta'] as $key => $value) {
        (isset($term_id[0])) ? update_term_meta($term_id[0], $key, $value) : '';
      }
      $term['meta'][$term['taxonomy']] = $term['term'];
      $term['meta']['taxonomy'] = $term['taxonomy'];
      $already_added_data = array();
      $already_added_data = get_user_meta(get_current_user_id(), $term['taxonomy'], true);
      $already_added_data = (empty($already_added_data)) ? array() : $already_added_data;
      $already_added_data[$term_id[0]] = $term['meta'];
      update_user_meta(get_current_user_id(), $term['taxonomy'], $already_added_data);
    }
  }
  if ($creating == 1) { }
  do_action('eventer_create_action_rest_api', $post->ID);
}, 99, 3);
add_action("rest_insert_product", function ($post, $request, $creating) {
  $custom_fields = $request->get_param('metas');
  if (!empty($custom_fields)) {
    foreach ($custom_fields as $key => $value) {
      update_post_meta($post->ID, $key, $value);
    }
  }
  if (function_exists('icl_object_id') && class_exists('SitePress') && function_exists('wpml_add_translatable_content')) {
    wpml_add_translatable_content('post_product', $post->ID, EVENTER__LANGUAGE_CODE);
  }
  wp_set_object_terms($post->ID, 'eventer', 'product_cat');
  update_post_meta($post->ID, '_virtual', 'yes');
}, 99, 3);

add_action('rest_api_init', 'wp_rest_user_endpoints');
/**
 * Register a new user
 *
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function wp_rest_user_endpoints($request)
{
  /**
   * Handle Register User request.
   */
  register_rest_route('imithemes', 'register/', array(
    'methods' => 'POST',
    'callback' => 'eventer_register_eventer_manager',
    'permission_callback' => '__return_true'
  ));
}
function eventer_register_eventer_manager($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $username = sanitize_text_field($parameters['username']);
  $email = sanitize_text_field($parameters['email']);
  $password = sanitize_text_field($parameters['password']);
  $error = new WP_Error();
  if (empty($username)) {
    $error->add(400, esc_html__("Username field 'username' is required.", 'eventer'), array('status' => 400));
    return $error;
  }
  if (empty($email)) {
    $error->add(401, esc_html__("Email field 'email' is required.", 'eventer'), array('status' => 400));
    return $error;
  }
  if (empty($password)) {
    $error->add(404, esc_html__("Password field 'password' is required.", 'eventer'), array('status' => 400));
    return $error;
  }
  $user_id = username_exists($username);
  if (!$user_id && email_exists($email) == false) {
    $user_id = wp_create_user($username, $password, $email);
    if (!is_wp_error($user_id)) {
      update_user_meta($user_id, 'show_admin_bar_front', false);
      $user = get_user_by('id', $user_id);
      $user->set_role('eventer_manager');
      $response['code'] = 200;
      $response['code'] = 1;
      $response['message'] = esc_html__("You are successfully registered, please login now.", "eventer");
    }
  } elseif ($user_id) {
    $response['code'] = 200;
    $response['state'] = 2;
    $response['message'] = esc_html__("Username already exist.", "eventer");
  } elseif (email_exists($email)) {
    $response['code'] = 200;
    $response['state'] = 4;
    $response['message'] = esc_html__("Email already registered with us.", "eventer");
  } else {
    $response['code'] = 200;
    $response['state'] = 5;
    $response['message'] = esc_html__("There is something went wrong.", "eventer");
  }
  return new WP_REST_Response($response, 123);
}

add_action('rest_api_init', 'eventer_wp_rest_endpoint_reset');
/**
 * Reset event manager password
 *
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function eventer_wp_rest_endpoint_reset($request)
{
  register_rest_route('imithemes', 'reset/', array(
    'methods' => 'POST',
    'callback' => 'eventer_reset_password_endpoint',
    'permission_callback' => '__return_true'
  ));
}
function eventer_reset_password_endpoint($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $username = sanitize_text_field($parameters['username']);
  $verification = (isset($parameters['verification']) && $parameters['verification'] != '') ? sanitize_text_field($parameters['verification']) : '';
  $password = (isset($parameters['password']) && $parameters['password'] != '') ? sanitize_text_field($parameters['password']) : '';
  $resend = (isset($parameters['resend']) && $parameters['resend'] != '' && $parameters['resend'] != 'undefined') ? sanitize_text_field($parameters['resend']) : '';
  $error = new WP_Error();
  $exists = email_exists($username);
  if ($exists) {
    $response['code'] = 200;
    $response['message'] = 1;
    $response['second'] = '<div class="eventer-verification-code-timer"><p class="eventer-form-info-links">' . esc_html__('Check your email for the verification code and enter it in the below field to reset the password.', 'eventer') . '</p>
	  <div class="eventer-spacer-10"></div>
	  <div class="eventer-row"><div class="eventer-col10"><div class="eventer-dynamic-counter"></div>
	  <span id="resend-reset" class="eventer-reset-resend" data-val="">' . esc_html__('Resend', 'eventer') . '</span>
	  </div></div><div class="eventer-spacer-10"></div></div>
	  <label>' . esc_html__('Verification code', 'eventer') . '</label>
      <input required type="text" name="eventer-reset-verification" class="eventer_reset_verification">';

    $verification_code = wp_rand(1000, 1000000000000);
    $already_verified = get_user_meta($exists, 'eventer_verification_code', true);
    $already_verified_timer = get_user_meta($exists, 'eventer_verification_code_timer', true);
    $remaining_time = date_i18n('U') - $already_verified_timer;
    $response['seconds'] = ($remaining_time > 300) ? 300 : 300 - $remaining_time;
    $response['random'] = wp_rand(1, 1000);
    if (empty($already_verified) || $remaining_time > 300 || $resend == '1') {
      update_user_meta($exists, 'eventer_verification_code', $verification_code);
      update_user_meta($exists, 'eventer_verification_code_timer', date_i18n('U'));
      $verification_code = $verification_code;
      $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>';
      $headers[] = "MIME-Version: 1.0" . "\r\n";
      $headers[] = "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
      $message = '<div style="background-color:#eb681f; padding:20px 48px;color:#ffffff;">';
      $message .= esc_html__('Please use this verification code to reset your password.', 'eventer');
      $message .= '<br/>';
      $message .= esc_html__('Verification code', 'eventer');
      $message .= ': ' . $verification_code;
      $message .= '</div>';
      wp_mail($username, esc_html__('Reset password verficiation code', 'eventer'), $message, $headers);
      $already_verified_timer = get_user_meta($exists, 'eventer_verification_code_timer', true);
      $remaining_time = date_i18n('U') - $already_verified_timer;
      $response['seconds'] = ($remaining_time > 300) ? 300 : 300 - $remaining_time;
    }
    $verification_code = get_user_meta($exists, 'eventer_verification_code', true);
    if ($verification != '' && $verification == $verification_code) {
      $response['message'] = 2;
      $response['second'] = '<label>' . esc_html__('Enter Password', 'eventer') . '</label>
                              <input required type="password" name="eventer-reset-pass1" class="eventer_reset_pass1">
                              <label>' . esc_html__('Repeat Password', 'eventer') . '</label>
                              <input required type="password" name="eventer-reset-pass2" class="eventer_reset_pass2">';
      $response['button'] = esc_html__('Reset', 'eventer');
      if ($password != '') {
        wp_set_password($password, $exists);
        update_user_meta($exists, 'eventer_verification_code', '');
        $response['message'] = 3;
        $response['second'] = esc_html__('Password reset successfully', 'eventer');
      }
    } elseif ($verification != '') {
      $response['message'] = 4;
      $response['second'] = esc_html__('Please enter a valid verfication code', 'eventer');
    }
  } else {
    $response['message'] = 4;
    $response['second'] =  esc_html__("Sorry, email entered is not registered with us.", 'eventer');
  }
  return new WP_REST_Response($response, 123);
}

add_action('rest_api_init', 'eventer_wp_rest_endpoint_login');
/**
 * Login event manager
 *
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function eventer_wp_rest_endpoint_login($request)
{
  /**
   * Handle Register User request.
   */
  register_rest_route('imithemes', 'login/', array(
    'methods' => 'POST',
    'callback' => 'eventer_login_endpoint',
    'permission_callback' => '__return_true'
  ));
}
function eventer_login_endpoint($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $username = sanitize_text_field($parameters['username']);
  $password = sanitize_text_field($parameters['password']);
  if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
    $user = get_user_by('email', $username);
  } else {
    $user = get_user_by('login', $username);
  }
  $response['code'] = 200;
  $response['message'] = 4;
  $response['new'] = $user;
  //print_r($user);
  if ($user && wp_check_password($password, $user->data->user_pass, $user->ID)) {
    $creds = array('user_login' => $user->data->user_login, 'user_password' => $password);
    $user = wp_signon($creds, true);
    //wp_redirect('/members/'.$user->data->user_login.'/courses');
    $response['code'] = 200;
    $response['message'] = 1;
  } else {
    $response['code'] = 200;
    $response['message'] = esc_html__('Incorrect Username or Password', 'eventer');
  }
  return new WP_REST_Response($response, 123);
}

add_action('rest_api_init', 'eventer_wp_rest_endpoint_shortcode');
function eventer_wp_rest_endpoint_shortcode($request)
{
  /**
   * Handle Register User request.
   */
  register_rest_route('form', '/shortcode/', array(
    'methods' => 'POST',
    'callback' => 'eventer_shortcode_endpoint',
    'permission_callback' => '__return_true'
  ));
}
function eventer_shortcode_endpoint($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $field_id = (isset($parameters['field'])) ? sanitize_text_field($parameters['field']) : '';
  $shortcode = (isset($parameters['shortcode'])) ? sanitize_text_field($parameters['shortcode']) : '';
  $shortcode = ($shortcode != '') ? str_replace('eventer_fields', 'eventer_fields_display', $shortcode) : '';
  $shortcode = json_decode(do_shortcode($shortcode), true);
  $type = $text_row = $required = $class = $id = $name = $label = $meta_key = $param = $meta_key_custom = $featured_type = $textarea_type = '';
  $params = array();
  if (!empty($shortcode)) {
    foreach ($shortcode as $key => $value) {
      ${$key} = $value;
    }
  }
  $new_meta_key = $meta_key;
  $meta_key = ($meta_key_custom == 'custom') ? $meta_key_custom : $meta_key;
  if ($param != '') {
    $var = str_replace(', ', ',', $param);
    $rows = explode(',', $var); //print_r($rows);
    $array = [];
    foreach ($rows as $row) {
      $matches = explode("|", $row);
      if (!empty($matches)) {
        $array[$matches[0]] = $matches[1];
      }
    }
    $params = $array;
  }
  $hidden = ($type == 'select' || $type == 'checkbox' || $type == 'radio') ? '' : ' style="display:none;"';
  $row = ($type == 'textarea') ? '' : ' style="display:none;"';
  $row_featured = ($type == 'featured') ? '' : ' style="display:none;"';
  $meta = ($meta_key == 'custom') ? '' : ' style="display:none;"';
  $row_class = ($type == 'featured') ? '3' : '5';
  $form_output = '<div class="eventer eventer_create_shortcode"><a rel=emodal:open title="Contact" class="eventer_generate_shortcode" href="#eventer-contact-form"></a>
   <div class="eventer-modal-static" id="eventer-contact-form">
		<div class="eventer-modal-body">
			<div class="eventer eventer-event-single">
				<h3>' . esc_html__('Add Field', 'eventer') . '</h3>
				<form data-target="' . esc_attr($field_id) . '">
            <div class="eventer-row">
               <div class="eventer-col5">
                  <label>' . esc_html__('Field Type', 'eventer') . '</label>
                  <select class="eventer_terms eventer_select_val eventer_add_new_values" data-sattr="type" id="form_field_type" data-action="hide">
										<option ' . ((esc_attr($type) == 'text') ? 'selected' : '') . ' value="text">' . esc_html__('Text', 'eventer') . '</option>
										<option ' . ((esc_attr($type) == 'textarea') ? 'selected' : '') . ' value="textarea">' . esc_html__('Textarea', 'eventer') . '</option>
										<option ' . ((esc_attr($type) == 'select') ? 'selected' : '') . ' value="select">' . esc_html__('Select dropdown', 'eventer') . '</option>
										<option ' . ((esc_attr($type) == 'checkbox') ? 'selected' : '') . ' value="checkbox">' . esc_html__('Checkbox', 'eventer') . '</option>
										<option ' . ((esc_attr($type) == 'radio') ? 'selected' : '') . ' value="radio">' . esc_html__('Radio', 'eventer') . '</option>
                              <option ' . ((esc_attr($type) == 'featured') ? 'selected' : '') . ' value="featured">' . esc_html__('Featured Image', 'eventer') . '</option>
										<option ' . ((esc_attr($type) == 'number') ? 'selected' : '') . ' value="number">' . esc_html__('Number', 'eventer') . '</option>
										<option ' . ((esc_attr($type) == 'email') ? 'selected' : '') . ' value="email">' . esc_html__('Email', 'eventer') . '</option>
                                        <option ' . ((esc_attr($type) == 'eventer-category') ? 'selected' : '') . ' value="eventer-category">' . esc_html__('Event Categories', 'eventer') . '</option>

                                        <option ' . ((esc_attr($type) == 'eventer-tag') ? 'selected' : '') . ' value="eventer-tag">' . esc_html__('Event Tags', 'eventer') . '</option>
                              <option ' . ((esc_attr($type) == 'date') ? 'selected' : '') . ' value="date">' . esc_html__('Date', 'eventer') . '</option>
                              <option ' . ((esc_attr($type) == 'div') ? 'selected' : '') . ' value="div">' . esc_html__('Div', 'eventer') . '</option>
						</select>
               </div>
               <div class="eventer-col' . esc_attr($row_class) . ' eventer_field_mandatory">
                  <label>' . esc_html__('Mandatory', 'eventer') . '</label>
                  <select class="eventer_terms eventer_select_val" data-sattr="required" id="form_field_required">
										<option ' . ((esc_attr($required) == 'no') ? 'selected' : '') . ' value="no">' . esc_html__('No', 'eventer') . '</option>
										<option ' . ((esc_attr($required) == 'yes') ? 'selected' : '') . ' value="yes">' . esc_html__('Yes', 'eventer') . '</option>
						</select>
               </div>
               <div class="eventer-col2 eventer_featured_type" ' . $row_featured . '>
                  <label>' . esc_html__('Type', 'eventer') . '</label>
                  <select class="eventer_terms eventer_select_val" data-sattr="featured_type" id="form_field_required">
										<option ' . ((esc_attr($featured_type) == '') ? 'selected' : '') . ' value="">' . esc_html__('Default', 'eventer') . '</option>
										<option ' . ((esc_attr($featured_type) == 'wp') ? 'selected' : '') . ' value="wp">' . esc_html__('WP', 'eventer') . '</option>
                </select>
               </div>
            </div>
            <div class="eventer-row eventer-textarea-required" ' . $row . '>
               <div class="eventer-col5">
                  <label>' . esc_html__('Textarea Rows', 'eventer') . '</label>
                  <select class="eventer_terms eventer_select_val" data-sattr="text_row" id="form_field_text_row">
										<option ' . ((esc_attr($text_row) == '4') ? 'selected' : '') . ' value="4">' . number_format_i18n(4) . '</option>
										<option ' . ((esc_attr($text_row) == '5') ? 'selected' : '') . ' value="5">' . number_format_i18n(5) . '</option>
										<option ' . ((esc_attr($text_row) == '6') ? 'selected' : '') . ' value="6">' . number_format_i18n(6) . '</option>
										<option ' . ((esc_attr($text_row) == '7') ? 'selected' : '') . ' value="7">' . number_format_i18n(7) . '</option>
										<option ' . ((esc_attr($text_row) == '8') ? 'selected' : '') . ' value="8">' . number_format_i18n(8) . '</option>
						</select>
               </div>
               <div class="eventer-col5">
                  <label>' . esc_html__('Type', 'eventer') . '</label>
                  <select class="eventer_terms eventer_select_val" data-sattr="textarea_type" id="form_field_text_row">
										<option ' . ((esc_attr($textarea_type) == '') ? 'selected' : '') . ' value="">' . esc_html__('Default', 'eventer') . '</option>
										<option ' . ((esc_attr($textarea_type) == 'wp') ? 'selected' : '') . ' value="wp">' . esc_html__('WP', 'eventer') . '</option>
						</select>
               </div>
            </div>
            <div class="eventer-row">
               <div class="eventer-col5">
                  <label>' . esc_html__('Field Class', 'eventer') . '</label>
                  <input type="text" class="eventer_select_val" value="' . esc_attr($class) . '" data-sattr="class" id="form_field_class">
               </div>
               <div class="eventer-col5">
                  <label>' . esc_html__('Field ID', 'eventer') . '</label>
                  <input type="text" class="eventer_select_val"" value="' . esc_attr($id) . '" data-sattr="id" id="form_field_id">
               </div>
            </div>
            
            <div class="eventer-row">
               <div class="eventer-col5">
                  <label>' . esc_html__('Field Name', 'eventer') . '</label>
                  <input type="text" class="eventer_select_val"" value="' . esc_attr($name) . '" data-sattr="name" id="form_field_name">
               </div>
               <div class="eventer-col5">
                  <label>' . esc_html__('Field Label', 'eventer') . '</label>
                  <input type="text" class="eventer_select_val"" value="' . esc_attr($label) . '" data-sattr="label" id="form_field_label">
               </div>
            </div>
            <div class="eventer-row">
               
               <div class="eventer-col5">
                  <label>' . esc_html__('Event Fields', 'eventer') . '</label>
                  <select class="eventer_terms eventer_select_val eventer_add_new_values" data-action="meta_field" data-sattr="meta_key" id="form_field_text_row">
                     <option ' . ((esc_attr($meta_key) == '') ? 'selected' : '') . ' value="">' . esc_html__('Select', 'eventer') . '</option>
										<option ' . ((esc_attr($meta_key) == 'title') ? 'selected' : '') . ' value="title">' . esc_html__('Title', 'eventer') . '</option>
										<option ' . ((esc_attr($meta_key) == 'content') ? 'selected' : '') . ' value="content">' . esc_html__('Content', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_start_dt') ? 'selected' : '') . ' value="eventer_event_start_dt">' . esc_html__('Event Start Date', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_end_dt') ? 'selected' : '') . ' value="eventer_event_end_dt">' . esc_html__('Event End Date', 'eventer') . '</option>
										<option ' . ((esc_attr($meta_key) == 'eventer_event_all_day') ? 'selected' : '') . ' value="eventer_event_all_day">' . esc_html__('All Day Event', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_frequency_type') ? 'selected' : '') . ' value="eventer_event_frequency_type">' . esc_html__('Recurring Event Switch', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_frequency') ? 'selected' : '') . ' value="eventer_event_frequency">' . esc_html__('Recurring', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_frequency_count') ? 'selected' : '') . ' value="eventer_event_frequency_count">' . esc_html__('Recurring Frequency', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_registration_swtich') ? 'selected' : '') . ' value="eventer_event_registration_swtich">' . esc_html__('Registration Switch', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_custom_registration_url') ? 'selected' : '') . ' value="eventer_event_custom_registration_url">' . esc_html__('Registration URL', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_event_registration_target') ? 'selected' : '') . ' value="eventer_event_registration_target">' . esc_html__('Registration Target', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_name0') ? 'selected' : '') . ' value="eventer_ticket_name0">' . esc_html__('Ticket Name1', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_quantity0') ? 'selected' : '') . ' value="eventer_ticket_quantity0">' . esc_html__('Ticket Quantity1', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_price0') ? 'selected' : '') . ' value="eventer_ticket_price0">' . esc_html__('Ticket Price1', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_name1') ? 'selected' : '') . ' value="eventer_ticket_name1">' . esc_html__('Ticket Name2', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_quantity1') ? 'selected' : '') . ' value="eventer_ticket_quantity1">' . esc_html__('Ticket Quantity2', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_price1') ? 'selected' : '') . ' value="eventer_ticket_price1">' . esc_html__('Ticket Price2', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_name2') ? 'selected' : '') . ' value="eventer_ticket_name2">' . esc_html__('Ticket Name3', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_quantity2') ? 'selected' : '') . ' value="eventer_ticket_quantity2">' . esc_html__('Ticket Quantity3', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer_ticket_price2') ? 'selected' : '') . ' value="eventer_ticket_price2">' . esc_html__('Ticket Price3', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer-organizer') ? 'selected' : '') . ' value="eventer-organizer">' . esc_html__('Event Organizer', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'organizer_phone') ? 'selected' : '') . ' value="organizer_phone">' . esc_html__('Organizer Phone', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'organizer_email') ? 'selected' : '') . ' value="organizer_email">' . esc_html__('Organizer Email', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'organizer_website') ? 'selected' : '') . ' value="organizer_website">' . esc_html__('Organizer Website', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'eventer-venue') ? 'selected' : '') . ' value="eventer-venue">' . esc_html__('Event Venue', 'eventer') . '</option>
                              <option ' . ((esc_attr($meta_key) == 'venue_address') ? 'selected' : '') . ' value="venue_address">' . esc_html__('Venue Address', 'eventer') . '</option>
                              
                              <option ' . ((esc_attr($meta_key) == 'custom') ? 'selected' : '') . ' value="custom">' . esc_html__('Custom Meta', 'eventer') . '</option>
						</select>
               </div>
               <div class="eventer-col5 eventer_custom_meta_field" ' . $meta . '>
                  <label>' . esc_html__('Field Meta Key', 'eventer') . '</label>
                  <input type="text" class="eventer_select_val eventer_meta_autocomplete" value="' . (($meta_key_custom == 'custom') ? esc_attr($new_meta_key) : esc_attr($meta_key_custom)) . '" data-sattr="meta_key_custom" id="form_field_metakey">
               </div>
            </div>';
  $params = (empty($params)) ? array('' => '') : $params;
  if (!empty($params)) {
    $start = 1;
    foreach ($params as $key => $value) {
      $class_add = ($start == 1) ? 'eventer_shortcode_values' : '';
      $form_output .= '<div class="eventer-row ' . esc_attr($class_add) . ' eventer_shortcode_value" ' . $hidden . '>
               <div class="eventer-col4">
                  <label>' . esc_html__('Field Value', 'eventer') . '</label>
                  <input type="text" value="' . esc_attr($key) . '" class="eventer_checked_fields" data-sattr="class" id="form_field_class">
               </div>
               <div class="eventer-col4">
                  <label>' . esc_html__('Field Text', 'eventer') . '</label>
                  <input type="text" value="' . esc_attr($value) . '" class="eventer_checked_fields" data-sattr="class" id="form_field_class">
               </div>
               <div class="eventer-col2">
               <label>&nbsp;</label>
               <a class="eventer_add_new_values eventer-fe-remove-field-value" data-action="delete"></a>
               </div>
               </div>';
      $start++;
    }
  }


  $form_output .= '<div class="eventer-row eventer_checked_field_section" ' . $hidden . '>
               <div class="eventer-col2">
                  <input type="button" value="' . esc_html__('+', 'eventer') . '" title="Add New" class="eventer_add_new_values eventer-fe-add-field-value">
               </div>
            </div>
   <div class="eventer-row">
               <div class="eventer-col4">
                  <input type="button" value="' . esc_html__('Insert Field', 'eventer') . '" class="eventer-btn eventer-btn-primary eventer_generate_shortcode_form">
               </div>
            </div>
				</form>
			</div>
		</div>
	</div>
   </div>';
  $response['form'] = $form_output;
  //$response['metas'] = array('title'=>'Title');
  return $response;
}

add_action('rest_api_init', 'eventer_wp_submissions_terms');

function eventer_wp_submissions_terms($request)
{
  register_rest_route('eventers', 'terms/', array(
    'methods' => 'POST',
    'callback' => 'eventer_get_submitted_terms',
    'permission_callback' => '__return_true'
  ));
}
function eventer_get_submitted_terms($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $organizer_offset = (isset($parameters['organizer'])) ? $parameters['organizer'] : 0;
  $venue_offset = (isset($parameters['venue'])) ? $parameters['venue'] : 0;
  $autocomplete_values = array('eventer-organizer', 'eventer-venue');
  $eventer_autocomplete_values = array();
  foreach ($autocomplete_values as $autocomplete) {
    $count = 2;
    $term_key = '';
    $actual = $counter = $skipped = 0;
    $offset = ($autocomplete == 'eventer-organizer') ? $organizer_offset : $venue_offset;
    $already_added_data = get_user_meta(get_current_user_id(), $autocomplete, true);
    if (!empty($already_added_data)) {
      $field_values = array();
      foreach ($already_added_data as $key => $value) {
        $counter++;
        if ($offset >= $counter) {
          $skipped++;
          continue;
        }
        if ($autocomplete == 'eventer-organizer' || $autocomplete == 'eventer-venue') {
          $term_key = ($autocomplete == 'eventer-organizer') ? esc_html__('Organizer', 'eventer') : esc_html__('Venue', 'eventer');
          $value['name'] = $value[$autocomplete];
          unset($value[$autocomplete]);
          unset($value['taxonomy']);
          $field_values[] = $value;
        } else {
          $term_key = $autocomplete;
          $field_values[] = array('label' => $value['name'], 'value' => $value);
        }
        $actual++;
        if ($actual == 2) break;
      }
      $skipped = $skipped - 2;
      $total_result = count($already_added_data);
      $eventer_autocomplete_values[$term_key] = array('newer' => ($counter), 'older' => $skipped, 'total' => $total_result, 'taxonomy' => $autocomplete, 'terms' => $field_values);
    }
  }
  $response = $eventer_autocomplete_values;
  return rest_ensure_response($response);
  //return $response;
}
add_action('rest_api_init', 'eventer_user_bookings');
function eventer_user_bookings($request)
{
  register_rest_route('eventers', 'bookings/', array(
    'methods' => 'POST',
    'callback' => 'eventer_get_user_bookings',
    'permission_callback' => '__return_true'
  ));
}
function eventer_get_user_bookings($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $status_booking = (isset($parameters['status'])) ? $parameters['status'] : 0;
  $user_info = get_user_by('id', get_current_user_id());
  $user_email = $user_info->user_email;
  $bookings_response = array();
  $parameters = $request->get_json_params();
  global $wpdb;
  $table_name = $wpdb->prefix . "eventer_registrant";
  $reg_details = $wpdb->get_results("SELECT * FROM $table_name WHERE email = '$user_email'", ARRAY_A);
  if ($reg_details) {
    foreach ($reg_details as $details) {
      $bookings = array();
      $user_details = unserialize($details['user_details']);
      if (empty($user_details)) //Bookings are of Woocommerce
      {
        $details_events = unserialize($details['user_system']);
        if (!empty($details_events) && isset($details_events['tickets']) && function_exists('wc_get_order')) {
          $order_id = $details['eventer'];

          $order = wc_get_order($order_id);
          if ($order == false) continue;
          $order_status = $order->get_status();
          if (get_post_type($order_id) != 'shop_order') continue;
          $this_order_events = $details_events['tickets'];

          foreach ($this_order_events as $this_event) {
            $address = '';
            $id = $this_event['event'];
            if (get_post_status($id) != 'publish') continue;
            $title = get_the_title($id);
            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $this_event['date']);
            $status = ($this_event['date'] > date_i18n('U')) ? esc_html__('Upcoming', 'eventer') : esc_html__('Passed', 'eventer');
            $status_check = ($this_event['date'] > date_i18n('U')) ? 1 : 0;
            $type = $this_event['type'];
            if ($status_booking != '') {
              if ($status_booking == 'upcoming' && $status_check != 1) {
                continue;
              } elseif ($status_booking == 'passed' && $status_check != 0) {
                continue;
              }
            }
            if ($type != 'ticket') continue;
            $ticket = $this_event['ticket'];
            $quantity = $this_event['quantity'];
            $venue = wp_get_object_terms($id, 'eventer-venue');
            if (!is_wp_error($venue) && !empty($venue)) {
              foreach ($venue as $address) {
                $address = get_term_meta($address->term_id, 'eventer_venue', true);
              }
            }
            $bookings[] = array('id' => $id, 'name' => $title, 'date' => $date, 'ticket' => array($quantity . ' X ' . $ticket), 'venue' => $address, 'status' => $status, 'status_check' => $status_check);
          }
          if (!empty($bookings)) {
            $bookings_response[] = array('order' => $order_id, 'status' => $order_status, 'events' => $bookings);
          }
        } else {
          if (get_post_status($details['eventer']) != 'publish') continue;
          $address = '';
          $tickets = array();
          $venue = wp_get_object_terms($details['eventer'], 'eventer-venue');
          if (!is_wp_error($venue) && !empty($venue)) {
            foreach ($venue as $address) {
              $address = get_term_meta($address->term_id, 'eventer_venue', true);
            }
          }
          if (!empty($details['tickets'])) {
            foreach (unserialize($details['tickets']) as $ticket) {
              $tickets[] = $ticket['number'] . ' X ' . $ticket['name'];
            }
          }
          $status = (strtotime($details['eventer_date']) > date_i18n('U')) ? esc_html__('Upcoming', 'eventer') : esc_html__('Passed', 'eventer');
          $status_check = ($details['eventer_date'] > date_i18n('U')) ? 1 : 0;
          if ($status_booking != '') {
            if ($status_booking == 'upcoming' && $status_check != 1) {
              continue;
            } elseif ($status_booking == 'passed' && $status_check != 0) {
              continue;
            }
          }
          $bookings[] = array('id' => $details['eventer'], 'name' => get_the_title($details['eventer']), 'date' => $details['eventer_date'], 'ticket' => $tickets, 'venue' => $address, 'status' => $status, 'status_check' => $status_check);
          if (!empty($bookings)) {
            $bookings_response[] = array('order' => $details['id'], 'status' => $details['status'], 'events' => $bookings);
          }
        }
      } else {
        if (get_post_status($details['eventer']) != 'publish') continue;
        $address = '';
        $tickets = array();
        $venue = wp_get_object_terms($details['eventer'], 'eventer-venue');
        if (!is_wp_error($venue) && !empty($venue)) {
          foreach ($venue as $address) {
            $address = get_term_meta($address->term_id, 'eventer_venue', true);
          }
        }
        if (!empty($details['tickets'])) {
          foreach (unserialize($details['tickets']) as $ticket) {
            $tickets[] = $ticket['number'] . ' X ' . $ticket['name'];
          }
        }
        $status = (strtotime($details['eventer_date']) > date_i18n('U')) ? esc_html__('Upcoming', 'eventer') : esc_html__('Passed', 'eventer');
        $status_check = ($details['eventer_date'] > date_i18n('U')) ? 1 : 0;
        if ($status_booking != '') { //echo $status_booking; echo $status_check;
          if ($status_booking == 'upcoming' && $status_check == 0) {
            continue;
          } elseif ($status_booking == 'passed' && $status_check == 1) {
            continue;
          }
        }
        $bookings[] = array('id' => $details['eventer'], 'name' => get_the_title($details['eventer']), 'date' => $details['eventer_date'], 'ticket' => $tickets, 'venue' => $address, 'status' => $status, 'status_check' => $status_check);
        if (!empty($bookings)) {
          $bookings_response[] = array('order' => $details['id'], 'status' => $details['status'], 'events' => $bookings);
        }
      }
    }
  }
  $response['bookings'] = $bookings_response;
  return rest_ensure_response($response);
  //return $response;
}
add_action('rest_api_init', 'eventer_manager_email');

/**
 * Sending email after event created successfully
 *
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function eventer_manager_email($request)
{
  /**
   * Handle Register User request.
   */
  register_rest_route('imithemes', 'email/', array(
    'methods' => 'POST',
    'callback' => 'eventer_email_endpoint',
    'permission_callback' => '__return_true'
  ));
}
function eventer_email_endpoint($request = null)
{
  $email_status = 0;
  $parameters = $request->get_params();
  $eventer_edit = (isset($parameters['event'])) ? $parameters['event'] : $_POST['ID'];
  $mail_status = get_post_meta($eventer_edit, 'eventer_rest_email_status', true);
  $status = get_post_status($eventer_edit);
  if (($status != 'publish' && $mail_status != '') || ($mail_status == 'publish')) {
    return rest_ensure_response("email failed.");
  }
  update_post_meta($eventer_edit, 'eventer_rest_email_status', get_post_status($eventer_edit));
  $current_user = get_post_field('post_author', $eventer_edit);
  $user_info = get_user_by('id', $current_user);
  $email_content = (isset($_POST['ID'])) ? 'add_new_event_published' : 'add_new_event_content';
  $email_content_switch = eventer_get_settings($email_content . '_switch');
  if ($user_info && $email_content_switch != '0') {
    $sender = get_option('admin_email');
    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $sender . '>';
    $headers[] = "MIME-Version: 1.0" . "\r\n";
    $headers[] = "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
    $message = apply_filters('the_content', eventer_get_settings($email_content));
    $new_message = apply_filters('eventer_set_email_content_format', $message, $eventer_edit, $user_info);
    $email_status = wp_mail($user_info->user_email, esc_html__('New event added', 'eventer'), $new_message, $headers);
  }
  return rest_ensure_response($email_status);
}

add_action('rest_api_init', 'eventer_load_dynamic_list');
/**
 * Login event manager
 *
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function eventer_load_dynamic_list($request)
{
  /**
   * Handle Register User request.
   */
  register_rest_route('eventer', 'dynamic/', array(
    'methods' => 'POST',
    'callback' => 'eventer_generate_dynamic_list',
    'permission_callback' => '__return_true'
  ));
}
function eventer_generate_dynamic_list($request = null)
{
  $response = array();
  $parameters = $request->get_json_params();
  $result = '';
  $shortcode_name = '';
  foreach ($parameters as $key => $value) {
    if ($key == 'name') {
      $shortcode_name = ($value);
      continue;
    }

    $result .= ' ' . $key . '="' . $value . '"';
  }
  $new_result = '[' . $shortcode_name . $result . ']';
  $response['shortcode'] = do_shortcode($new_result);
  return new WP_REST_Response($response, 123);
}
