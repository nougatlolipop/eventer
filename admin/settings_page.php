<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

// Start Class
if (!class_exists('Eventer_Settings_Options')) {

  class Eventer_Settings_Options
  {

    /**
     * Start things up
     *
     * @since 1.0.0
     */
    public function __construct()
    {

      // We only need to register the admin panel on the back-end
      if (is_admin()) {
        add_action('admin_menu', array('Eventer_Settings_Options', 'add_bookings_menu'));
        add_action('admin_menu', array('Eventer_Settings_Options', 'add_admin_menu'));
        add_action('admin_init', array('Eventer_Settings_Options', 'register_settings'));
        //add_filter( 'option_page_capability_eventer_options', array( 'Eventer_Settings_Options', 'eventer_add_capabilit_settings' ));
        add_action('plugins_loaded', array('Eventer_Settings_Options', 'load_translations'));
        add_action('admin_init', array('Eventer_Settings_Options', 'eventer_add_thickbox'));
      }
    }

    public static function eventer_add_thickbox()
    {
      add_thickbox();
    }
    /**
     * Returns all theme options
     *
     * @since 1.0.0
     */

    //This function only run whenever the plugin has been activated
    //This will create new table in database for storing values of registrants of events
    public static function eventer_create_ticket_details_table()
    {
      global $wpdb;
      $table_name = $wpdb->prefix . "eventer_registrant";
      $table_name_tickets = $wpdb->prefix . "eventer_tickets";
      $eventer_coupon_table = $wpdb->prefix . "eventer_coupons";
      $plugin_data = get_plugin_data(__FILE__);
      $plugin_version = $plugin_data['Version'];
      if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				eventer mediumint(9) NOT NULL,
				ctime datetime NOT NULL,
				eventer_date date NOT NULL,
				transaction_id text NOT NULL,
				username text NOT NULL,
				email text NOT NULL,
				paymentmode text NOT NULL,
				status text NOT NULL,
				amount mediumint(9) NOT NULL,
				user_details text NOT NULL,
				tickets text NOT NULL,
				user_id mediumint(9) NOT NULL,
				paypal_details text NOT NULL,
				user_system text NOT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
      }
      if (get_option('eventer_table_column_updated') != '1') {
        $wpdb->query("ALTER TABLE " . $table_name . " MODIFY `amount` DECIMAL (10,2)");
        update_option('eventer_registrant_amount_column_updated', '1');
        $wpdb->query("ALTER TABLE " . $table_name_tickets . " MODIFY `price` DECIMAL (10,2)");
        update_option('eventer_table_column_updated', '1');
      }

      if ($wpdb->get_var("SHOW TABLES LIKE '$eventer_coupon_table'") != $eventer_coupon_table) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $eventer_coupon_table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				coupon_name text NOT NULL,
				coupon_code text NOT NULL,
				discounted text NOT NULL,
				valid_till datetime NOT NULL,
				coupon_status mediumint(1) NOT NULL,
				PRIMARY KEY (id)
				) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
      }
      if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_tickets'") != $table_name_tickets) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name_tickets (
                ticket_id bigint(90) NOT NULL AUTO_INCREMENT,
                                dynamic bigint(90) NOT NULL,
                                pid bigint(90) NOT NULL,
                                event bigint(90) NOT NULL,
                                name text NOT NULL,
                                date datetime NOT NULL,
                                type text NOT NULL,
                                tickets mediumint(9) NOT NULL,
                                price float NOT NULL,
                                restricts text NOT NULL,
                                featured text NOT NULL,
                                label text NOT NULL,
                                enabled datetime NOT NULL,
                                cust_val1 text NOT NULL,
                                cust_val2 text NOT NULL,
                PRIMARY KEY  (ticket_id)
                ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
      }
    }
    //This function runs whenever user activate eventer plugin
    //This will flush all the permalinks settings, so that new settings take effect
    public static function eventer_flush_rewrite_activate()
    {
      eventer_register_post_type();
      flush_rewrite_rules();
    }
    //This will flush all rewrite rules whenever user deactivate the plugin
    public static function eventer_flush_rewrite_deactivate()
    {
      flush_rewrite_rules();
    }

    public static function eventer_store_default_settings()
    {
      $settings_val = array('eventer_paypal_payment_type' => '0', 'eventer_paypal_business_email' => '', 'eventer_paypal_currency' => 'AUD', 'eventer_offline_payment_switch' => '1', 'eventer_offline_payment_desc' => 'Bank Name<br>Account No: 0000 1400 1211<br>IFSC Code: 00001321<br>Branch Address', 'event_default_color' => '#595fde', 'eventer_enable_categories' => 'on', 'eventer_enable_tags' => 'on', 'eventer_enable_venue' => 'on', 'eventer_enable_organizer' => 'on', 'eventer_show_single_occurance' => 'off', 'eventer_recurring_icon_yes' => 'on', 'eventer_save_options' => 'on', 'eventer_save_event' => 'off', 'start_time_format' => 'l, g:i A', 'time_separator' => 'to', 'end_time_format' => 'g:i A', 'eventer_date_format' => 'Y-m-d G:i', 'eventer_calendar_view' => 'month', 'eventer_calendar_header_left_view' => 'title', 'eventer_calendar_header_center_view' => '', 'eventer_calendar_header_right_view' => 'today prev,next', 'eventer_calendar_rtl' => '0', 'event_limit' => '100', 'google_apikey' => '', 'google_cal_apikey' => '', 'cal_id' => '', 'payment_confirmation_content' => '<h3>Payment verification Details</h3><p>Registrant ID: {reg_id}</p><p>Transaction ID: {tx_id}</p><p>Registrant Email: {reg_email}</p><p>Amount Paid: {amt_pd}</p><p>Status: {pmt_st}</p><p>Tickets: {tkt}</p><p>Event: {evt_url}</p>{user_details}[eventer_free data="<p>Thanks for registration.</p>"][eventer_offline data="<p>This is bank details.</p>"][eventer_paid data="<p>Thanks for the payment.</p>"][eventer_tkturl completed="1" pending="1" failed="1"]<p>We welcomes you for the Event.</p>', 'pre_registration_content' => '<h3>Pre Registration Details</h3><p>Registrant ID: {reg_id}</p><p>Registrant Email: {reg_email}</p><p>Amount Paid: {amt_pd}</p><p>Tickets: {tkt}</p><p>Event: {evt_url}</p>{user_details}', 'ticket_booking_fields' => '[eventer_field_row][eventer_field_halfcol]
[eventer_fields type="text" text_row="4" required="no" class="" id="" param="" name="LAST NAME" label="LAST NAME"][/eventer_field_halfcol][eventer_field_halfcol][eventer_fields type="number" text_row="4" required="no" class="" id="" param="" name="YOUR PHONE" label="YOUR PHONE"]
[/eventer_field_halfcol][/eventer_field_row][eventer_field_row][eventer_field_halfcol][eventer_fields type="textarea" text_row="6" required="no" class="" id="" param="" name="YOUR ADDRESS" label="YOUR ADDRESS"][/eventer_field_halfcol][eventer_field_halfcol][eventer_fields type="textarea" text_row="6" required="no" class="" id="" param="" name="ADDITIONAL NOTES" label="ADDITIONAL NOTES"][/eventer_field_halfcol][/eventer_field_row]', 'contact_organizer_fields' => '[eventer_fields type="text" text_row="4" required="yes" class="" id="" param="" name="YOUR NAME" label="YOUR NAME"]
[eventer_fields type="email" text_row="4" required="yes" class="" id="" param="" name="YOUR EMAIL" label="YOUR EMAIL"][eventer_fields type="number" text_row="4" required="no" class="" id="" param="" name="YOUR PHONE" label="YOUR PHONE"][eventer_fields type="checkbox" text_row="4" required="no" class="" id="" param="1,2,{3},4" name="" label="Food Choice"][eventer_fields type="textarea" text_row="4" required="no" class="" id="" param="" name="YOUR MESSAGE" label="YOUR MESSAGE"]', 'eventer_category_view' => '4', 'eventer_category_sidebar' => '', 'eventer_venues_view' => '2', 'eventer_venue_sidebar' => '', 'eventer_organizer_view' => '3', 'eventer_organizer_sidebar' => '', 'eventer_tag_view' => '4', 'eventer_tag_sidebar' => '', 'eventer_archive_view' => '5', 'eventer_archive_sidebar' => '', 'eventer_search_view' => '1', 'eventer_search_sidebar' => '', 'eventer_event_permalink' => '', 'eventer_event_category_permalink' => '', 'eventer_event_tag_permalink' => '', 'eventer_event_venue_permalink' => '', 'eventer_event_organizer_permalink' => '');
      $existing_settings = get_option('eventer_options');
      if (empty($existing_settings)) {
        update_option('eventer_options', $settings_val);
      }
    }

    public static function get_eventer_options()
    {
      return get_option('eventer_options');
    }

    /**
     * Returns single theme option value
     *
     * @since 1.0.0
     */
    public static function get_eventer_option($id)
    {
      $options = self::get_eventer_options();
      if (isset($options[$id])) {
        return $options[$id];
      }
    }

    /**
     * Add sub menu page
     *
     * @since 1.0.0
     */
    public static function add_admin_menu()
    {
      add_submenu_page('edit.php?post_type=eventer', esc_html__('Settings', 'eventer'), esc_html__('Settings', 'eventer'), 'manage_options', 'eventer_settings_options', array('Eventer_Settings_Options', 'create_admin_page'));
    }

    public static function add_bookings_menu()
    {
      add_submenu_page(
        'edit.php?post_type=eventer',
        esc_html__('Bookings', 'eventer'),
        esc_html__('Bookings', 'eventer'),
        'manage_options',
        'eventer_settings_options&tab=bookings',
        array('Eventer_Settings_Options', 'create_admin_page')
      );
    }

    /**
     * Register a setting and its sanitization callback.
     * @since 1.0.0
     */
    public static function register_settings()
    {
      register_setting('eventer_options', 'eventer_options', array('Eventer_Settings_Options', 'sanitize'));
    }

    public function eventer_add_capabilit_settings($cap)
    {
      //return 'edit_posts';
    }

    public static function load_translations()
    {
      load_plugin_textdomain('eventer', false, EVENTER__PLUGIN_PATH . '/language');
    }
    /**
     * Sanitization callback
     *
     * @since 1.0.0
     */
    public static function sanitize($options)
    {

      $options = (empty($options)) ? array() : $options;
      $sb = get_option('eventer_options');
      //if(empty($sb)) return;
      foreach ($sb as $key => $value) {
        if (array_key_exists($key, $options)) {
          if (is_array($options[$key])) {
            $options[$key] = $options[$key];
          } else {
            $options[$key] = $options[$key];
          }
        } else {
          if (is_array($value)) {
            $options[$key] = $value;
          } else {
            if ($key == "payment_confirmation_content" || $key == "pre_registration_content" || $key == "contact_organizer_fields" || $key == "ticket_booking_fields" || $key == "add_new_event_content" || $key == "add_new_event_published") {
              $options[$key] = $value;
            } else {
              $options[$key] = sanitize_text_field($value);
            }
          }
        }
      }
      return $options;
    }

    /**
     * Settings page output
     *
     * @since 1.0.0
     */
    public static function create_admin_page()
    { ?>

      <div class="wrap">

        <h1><?php esc_html_e('Event Options', 'eventer'); ?></h1>
        <?php
              $preview_id = get_option("eventer_shortcode_preview");
              $preview_area = '<form action="' . esc_url(get_permalink($preview_id)) . '" method="post" class="eventer-shortcode-preview" target="_blank">
			<input type="hidden" value=\'\' name="shortcode" class="eventer-shortcode-val">
			<input type="submit" class="button button-primary eventer-preview-submit" name="Preview" value="Preview">
			</form>';
              $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
              if (isset($_GET['tab'])) {
                $active_tab = $_GET['tab'];
              }

              $eventer_enable_categories = self::get_eventer_option('eventer_enable_categories');
              $eventer_enable_tags = self::get_eventer_option('eventer_enable_tags');
              $eventer_enable_venue = self::get_eventer_option('eventer_enable_venue');
              $eventer_enable_organizer = self::get_eventer_option('eventer_enable_organizer');
              ?>
        <h2 class="nav-tab-wrapper">

          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=general" class="nav-tab <?php echo ($active_tab == 'general') ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'eventer'); ?></a>
          <?php
                $woocommerce_ticketing = self::get_eventer_option('eventer_enable_woocommerce_ticketing');
                if ($woocommerce_ticketing != 'on') { ?>
            <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=payment" class="nav-tab <?php echo ($active_tab == 'payment') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Payment', 'eventer'); ?></a>
          <?php } ?>
          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=eventer_permalink" class="nav-tab <?php echo ($active_tab == 'eventer_permalink') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Permalinks', 'eventer'); ?></a>

          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=eventer_templates" class="nav-tab <?php echo ($active_tab == 'eventer_templates') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Templates', 'eventer'); ?></a>

          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=shortcode" class="nav-tab <?php echo ($active_tab == 'shortcode') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Shortcodes', 'eventer'); ?></a>

          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=bookings" class="nav-tab <?php echo ($active_tab == 'bookings') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Bookings', 'eventer'); ?></a>

          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=checkin" class="nav-tab <?php echo ($active_tab == 'checkin') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Check-in', 'eventer'); ?></a>

          <a href="edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=import" class="nav-tab <?php echo ($active_tab == 'import') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Import Events', 'eventer'); ?></a>



        </h2>
        <form method="post" action="options.php">

          <?php settings_fields('eventer_options'); ?>
          <?php if ($active_tab == 'general') { ?>
            <h3><?php
                        ?></h3>
            <div id="general-settings" class="ui-sortable meta-box-sortables">

              <div class="postbox">
                <div id="eventer-admin-sub-tabs-vertical">
                  <div id="eventer-admin-tab-container">
                    <ul>
                      <li><a href="javascript:void(0)" data-tab="#common-settings" class="general-settings-tab nav-tab-active"><?php esc_html_e('Common Settings', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#coupon-settings" class="general-settings-tab"><?php esc_html_e('Coupons', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#single-settings" class="general-settings-tab"><?php esc_html_e('Single Event', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#email-settings" class="general-settings-tab"><?php esc_html_e('Email Templates', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#date-settings" class="general-settings-tab"><?php esc_html_e('Date & Time', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#calendar-settings" class="general-settings-tab"><?php esc_html_e('Calendar', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#api-settings" class="general-settings-tab"><?php esc_html_e('API', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#form-settings" class="general-settings-tab"><?php esc_html_e('Form Fields', 'eventer'); ?></a></li>
                      <li><a href="javascript:void(0)" data-tab="#woocommerce-settings" class="general-settings-tab"><?php esc_html_e('Woocommerce', 'eventer'); ?></a></li>
                    </ul>
                  </div>
                  <div id="eventer-admin-main-container">



                    <div class="general-settings eventer-admin-tab-content" id="common-settings">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr>
                          <th>
                            <?php esc_html_e('Android App API Key', 'eventer'); ?>
                          </th>
                          <td>
                            <?php
                                    echo (int) get_option('eventer-android-app-api-key');
                                    ?>
                          </td>
                        </tr>
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Default color for events', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('event_default_color');
                                    $value_tab = self::get_eventer_option('event_general_tab');
                                    $value_tab = $value_tab ? $value_tab : '#common-settings'; ?>
                            <input type="hidden" name="eventer_options[event_general_tab]" class="eventer-general-tab-remember" value="<?php echo esc_attr($value_tab); ?>" />
                            <input type="text" class="eventer_default_color" name="eventer_options[event_default_color]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Select your desired color to be used for the highlighted parts of the full events plugin.', 'eventer'); ?></p>
                          </td>
                        </tr>



                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Enable event categories', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_enable_categories'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_categories]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_categories]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Enable event tags', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_enable_tags'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_tags]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_tags]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Enable event venue', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_enable_venue'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_venue]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_venue]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Enable event organizer', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_enable_organizer'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_organizer]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_organizer]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Shortcode preview page', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_shortcode_preview_page'); ?>
                            <input type="radio" name="eventer_options[eventer_shortcode_preview_page]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_shortcode_preview_page]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <br />
                            <p class="description"><?php esc_html_e('Allow to remove shortcode preview page from pages.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Taxonomy operator', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_taxonomy_search_operator'); ?>
                            <input type="radio" name="eventer_options[eventer_taxonomy_search_operator]" value="or" <?php checked($value, 'or'); ?>> <?php esc_html_e('or', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_taxonomy_search_operator]" value="and" <?php checked($value, 'and'); ?>> <?php esc_html_e('and', 'eventer'); ?>
                            <br />
                            <p class="description"><?php esc_html_e('Set operator for taxonomy filters. Ex: we have an event that does exist in category 1 and category 2, and we search for both of the categories then it is currently showing only the events that does exist in both of those categories, but now you can select the operator for search.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Disable carousel scripts', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_dequeue_carousel_scripts'); ?>
                            <input type="radio" name="eventer_options[eventer_dequeue_carousel_scripts]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_dequeue_carousel_scripts]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <br />
                            <p class="description"><?php esc_html_e('If you are having issue related to design while using plugin, please try this field.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Enable Event Archive', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_archive_switch');
                                    $value_archive_url = self::get_eventer_option('eventer_archive_template'); ?>
                            <input type="radio" name="eventer_options[eventer_archive_switch]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_archive_switch]" value="template" <?php checked($value, 'template'); ?>> <?php esc_html_e('Enable with custom template', 'eventer'); ?>

                            <input type="radio" name="eventer_options[eventer_archive_switch]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('Disable', 'eventer'); ?><br />
                            <input type="text" name="eventer_options[eventer_archive_template]" value="<?php echo esc_url($value_archive_url); ?>"> <?php esc_html_e('Enter URL for custom template', 'eventer'); ?><br />
                            <p class="description"><?php esc_html_e('Enable archive page for event.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Show Passed Badge', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_show_badges'); ?>
                            <input type="radio" name="eventer_options[eventer_show_badges]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_show_badges]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Show passed badge to event that are passed now.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Show single Occurrence of future event', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_show_single_occurance'); ?>
                            <input type="radio" name="eventer_options[eventer_show_single_occurance]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_show_single_occurance]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('This is for the recurring events, setting it Yes will always show a single event in your list of events or on calendar instead of multiple instances of repeated events.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Show recurring icon', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_recurring_icon_yes'); ?>
                            <input type="radio" name="eventer_options[eventer_recurring_icon_yes]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_recurring_icon_yes]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Select Yes to show a repeat icon with the name of your events in list, grid view.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Save event timezone', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_save_event'); ?>
                            <input type="radio" name="eventer_options[eventer_save_event]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('User/WP Timezone', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_save_event]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('Calendar Timezone', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('These options are for the saving event to your calendar like Google Calendar. With User/WP Timezone, the event will be saved in the Google Calendar in the timezone it is set in your website at WordPress general settings. With Calendar Timezone, event will be saved in the local timezone of your calendar like setting in your Google Calendar.', 'eventer'); ?></p>
                          </td>
                        </tr>


                      </table>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="single-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Links', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_details_links'); ?>
                            <label>
                              <input type="checkbox" name="eventer_options[eventer_details_links][]" value="print" <?php echo ((is_array($value) && in_array('print', $value) ? 'checked' : '')); ?>> <?php esc_html_e('Print', 'eventer'); ?>
                            </label>
                            <label>
                              <input type="checkbox" name="eventer_options[eventer_details_links][]" value="contact" <?php echo ((is_array($value) && in_array('contact', $value) ? 'checked' : '')); ?>> <?php esc_html_e('Contact', 'eventer'); ?></label>
                            <label>
                              <input type="checkbox" name="eventer_options[eventer_details_links][]" value="direction" <?php echo ((is_array($value) && in_array('direction', $value) ? 'checked' : '')); ?>> <?php esc_html_e('Get Direction', 'eventer'); ?>
                            </label>
                            <label>
                              <input type="checkbox" name="eventer_options[eventer_details_links][]" value="future" <?php echo ((is_array($value) && in_array('future', $value) ? 'checked' : '')); ?>> <?php esc_html_e('Future Events', 'eventer'); ?></label>
                            <input type="checkbox" style="display: none;" checked name="eventer_options[eventer_details_links][]" value="buttons" <?php echo ((is_array($value) && in_array('buttons', $value) ? 'checked' : '')); ?>> </label>
                            <p class="description"><?php esc_html_e('Check to enable links on details page.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Booking Calendar', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_booking_calendar'); ?>
                            <input type="radio" name="eventer_options[eventer_booking_calendar]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_booking_calendar]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Enable booking calendar on details page.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Organizer Link', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_organizer_link_target'); ?>
                            <input type="radio" name="eventer_options[eventer_organizer_link_target]" value="_blank" <?php checked($value, '_blank'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_organizer_link_target]" value="_self" <?php checked($value, '_self'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Open organizer website link in new tab?', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Registrants Fields', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_registrants_fields'); ?>
                            <input type="radio" name="eventer_options[eventer_registrants_fields]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_registrants_fields]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Enable individual registrants fields for booking.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Mandatory Registrants', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_registrants_fields_mandatory'); ?>
                            <input type="radio" name="eventer_options[eventer_registrants_fields_mandatory]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_registrants_fields_mandatory]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Make registrants field mandatory while booking for ticket.'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Coupon Field', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_coupon_field'); ?>
                            <input type="radio" name="eventer_options[eventer_coupon_field]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_coupon_field]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Show coupon field while payment.'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Show social sharing buttons', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_sharing_icons'); ?>
                            <input type="radio" name="eventer_options[eventer_sharing_icons]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_sharing_icons]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Select Yes to show social sharing options on the single event page.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Show save event options', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_save_options'); ?>
                            <input type="radio" name="eventer_options[eventer_save_options]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_save_options]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Select Yes to show several options on single event page to save event to Google Calendar, iCal and more.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Event image size', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_image_size_single'); ?>
                            <input type="text" name="eventer_options[eventer_image_size_single]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Enter image size for event details page.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Maximum Tickets', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_tickets_quantity_set'); ?>
                            <input type="text" name="eventer_options[eventer_tickets_quantity_set]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Enter number of tickets to set as maximum for single registration, Ex: 10.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Minimum Default Ticket', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_minimum_default_ticket'); ?>
                            <input type="text" name="eventer_options[eventer_minimum_default_ticket]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Enter minimum number of tickets to set as default. It will auto select the quantity while registering for event.', 'eventer'); ?></p>
                          </td>
                        </tr>

                      </table>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="email-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Event payment confirmation email', 'eventer'); ?>
                            <br />
                            <?php $value = self::get_eventer_option('payment_confirmation_content_switch'); ?>
                            <input type="radio" name="eventer_options[payment_confirmation_content_switch]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                            <input type="radio" name="eventer_options[payment_confirmation_content_switch]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('Disable', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('payment_confirmation_content'); ?>
                            <?php wp_editor($value, 'payment_confirmation_content', array('textarea_name' => 'eventer_options[payment_confirmation_content]')); ?>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the email which is sent when a user register for an event and completes the payment. There are several shortcodes available for you to use to send dynamic data in the email. List of shortcodes is below:', 'eventer'); ?></p>
                            <p>
                              <code>{reg_id}</code> -> <?php esc_html_e('Registrant ID', 'eventer'); ?><br>
                              <code>{reg_email}</code> -> <?php esc_html_e('Registrant Email', 'eventer'); ?><br>
                              <code>{tx_id}</code> -> <?php esc_html_e('Transaction ID', 'eventer'); ?><br>
                              <code>{pmt_st}</code> -> <?php esc_html_e('Payment Status', 'eventer'); ?><br>
                              <code>{amt_pd}</code> -> <?php esc_html_e('Amount Paid', 'eventer'); ?><br>
                              <code>{evt_url}</code> -> <?php esc_html_e('Event URL', 'eventer'); ?><br>
                              <code>{tkt}</code> -> <?php esc_html_e('Tickets Info', 'eventer'); ?><br>
                              <code>{user_details}</code> -> <?php esc_html_e('Registrant personal details', 'eventer'); ?><br>
                              <code>[eventer_free data="<?php esc_html_e('YOUR CUSTOM CONTENT OR ATTRIBUTES', 'eventer'); ?>"]</code> -> <?php esc_html_e('Content sent when the ticket for event is free', 'eventer'); ?><br>
                              <code>[eventer_offline data="<?php esc_html_e('YOUR CUSTOM CONTENT OR ATTRIBUTES', 'eventer'); ?>"]</code> -> <?php esc_html_e('Content sent when the user opt for offline payment option for registration', 'eventer'); ?><br>
                              <code>[eventer_paid data="<?php esc_html_e('YOUR CUSTOM CONTENT OR ATTRIBUTES', 'eventer'); ?>"]</code> -> <?php esc_html_e('Content sent when the user register for a ticket that has some price', 'eventer'); ?><br>
                            </p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Pre payment email for organizers', 'eventer'); ?><br />
                            <?php $value = self::get_eventer_option('pre_registration_content_switch'); ?>
                            <input type="radio" name="eventer_options[pre_registration_content_switch]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                            <input type="radio" name="eventer_options[pre_registration_content_switch]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('Disable', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('pre_registration_content'); ?>
                            <?php wp_editor($value, 'pre_registration_content', array('textarea_name' => 'eventer_options[pre_registration_content]')); ?>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the email which is sent to event organiser email when a user register for an event but not yet completed the payment. There are several shortcodes available for you to use to send dynamic data in the email. List of shortcodes is below:', 'eventer'); ?></p>
                            <p>
                              <code>{reg_id}</code> -> <?php esc_html_e('Registrant ID', 'eventer'); ?><br>
                              <code>{reg_email}</code> -> <?php esc_html_e('Registrant Email', 'eventer'); ?><br>
                              <code>{tx_id}</code> -> <?php esc_html_e('Transaction ID', 'eventer'); ?><br>
                              <code>{pmt_st}</code> -> <?php esc_html_e('Payment Status', 'eventer'); ?><br>
                              <code>{amt_pd}</code> -> <?php esc_html_e('Amount Paid', 'eventer'); ?><br>
                              <code>{evt_url}</code> -> <?php esc_html_e('Event URL', 'eventer'); ?><br>
                              <code>{tkt}</code> -> <?php esc_html_e('Tickets Info', 'eventer'); ?><br>
                              <code>{user_details}</code> -> <?php esc_html_e('Registrant personal details', 'eventer'); ?><br>
                              <code>{time_slot_title}</code> -> <?php esc_html_e('Time slot of event', 'eventer'); ?><br>
                            </p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Add new Event email content', 'eventer'); ?><br />
                            <?php $value = self::get_eventer_option('add_new_event_content_switch'); ?>
                            <input type="radio" name="eventer_options[add_new_event_content_switch]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                            <input type="radio" name="eventer_options[add_new_event_content_switch]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('Disable', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('add_new_event_content'); ?>
                            <?php wp_editor($value, 'add_new_event_content', array('textarea_name' => 'eventer_options[add_new_event_content]')); ?>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the email which is sent to event organiser email when a user add new event through front end.', 'eventer'); ?></p>
                            <p>
                              <code>{manager_email}</code> -> <?php esc_html_e('Event manager email address', 'eventer'); ?><br>
                              <code>{manager_name}</code> -> <?php esc_html_e('Event maanger name', 'eventer'); ?><br>
                              <code>{event_url}</code> -> <?php esc_html_e('Published event URL', 'eventer'); ?><br>
                              <code>{event_title}</code> -> <?php esc_html_e('Event title', 'eventer'); ?><br>
                              <code>{event_status}</code> -> <?php esc_html_e('Event status', 'eventer'); ?><br>
                            </p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Event published', 'eventer'); ?><br />
                            <?php $value = self::get_eventer_option('add_new_event_published_switch'); ?>
                            <input type="radio" name="eventer_options[add_new_event_published_switch]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                            <input type="radio" name="eventer_options[add_new_event_published_switch]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('Disable', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('add_new_event_published'); ?>
                            <?php wp_editor($value, 'add_new_event_published', array('textarea_name' => 'eventer_options[add_new_event_published]')); ?>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the email which is sent to event organiser email when a event status changed to publish.', 'eventer'); ?></p>
                            <p>
                              <code>{manager_email}</code> -> <?php esc_html_e('Event manager email address', 'eventer'); ?><br>
                              <code>{manager_name}</code> -> <?php esc_html_e('Event maanger name', 'eventer'); ?><br>
                              <code>{event_url}</code> -> <?php esc_html_e('Published event URL', 'eventer'); ?><br>
                              <code>{event_title}</code> -> <?php esc_html_e('Event title', 'eventer'); ?><br>
                              <code>{event_status}</code> -> <?php esc_html_e('Event status', 'eventer'); ?><br>
                            </p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Event ticket email with attachment', 'eventer'); ?><br />
                            <?php $value = self::get_eventer_option('email_tickets_attachment_switch'); ?>
                            <input type="radio" name="eventer_options[email_tickets_attachment_switch]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                            <input type="radio" name="eventer_options[email_tickets_attachment_switch]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('Disable', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('email_tickets_attachment'); ?>
                            <?php wp_editor($value, 'email_tickets_attachment', array('textarea_name' => 'eventer_options[email_tickets_attachment]')); ?>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the email which is sent to registrants with tickets as attachment.', 'eventer'); ?></p>
                            <!--<p>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{reg_id}</code> -> <?php esc_html_e('Registrant ID', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{reg_email}</code> -> <?php esc_html_e('Registrant Email', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{tx_id}</code> -> <?php esc_html_e('Transaction ID', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{pmt_st}</code> -> <?php esc_html_e('Payment Status', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{amt_pd}</code> -> <?php esc_html_e('Amount Paid', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{evt_url}</code> -> <?php esc_html_e('Event URL', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{tkt}</code> -> <?php esc_html_e('Tickets Info', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																												<code>{user_details}</code> -> <?php esc_html_e('Registrant personal details', 'eventer'); ?><br>
																																																																																																																																																																																																																																																																																																																																																																																																											</p>-->
                          </td>
                        </tr>
                      </table>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="date-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Time format', 'eventer'); ?></th>
                          <td>
                            <label class="eventer-label-block"><?php esc_html_e('Start time format', 'eventer'); ?></label>
                            <?php $value = self::get_eventer_option('start_time_format'); ?>
                            <input type="text" name="eventer_options[start_time_format]" value="<?php echo esc_attr($value); ?>">

                          </td>
                          <td>
                            <label class="eventer-label-block"><?php esc_html_e('Start-End time separator', 'eventer'); ?></label>
                            <?php $value = self::get_eventer_option('time_separator'); ?>
                            <input type="text" name="eventer_options[time_separator]" value="<?php echo esc_attr($value); ?>">
                          </td>
                          <td>
                            <label class="eventer-label-block"><?php esc_html_e('End time format', 'eventer'); ?></label>
                            <?php $value = self::get_eventer_option('end_time_format'); ?>
                            <input type="text" name="eventer_options[end_time_format]" value="<?php echo esc_attr($value); ?>">
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Date Format', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_date_format'); ?>
                            <input type="text" name="eventer_options[eventer_date_format]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('You can follow php date manual to set time and date format for event.', 'eventer'); ?> <a href="<?php echo esc_url('http://php.net/manual/en/function.date.php'); ?>" target="_blank"><?php echo esc_url('http://php.net/manual/en/function.date.php'); ?></a></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Date Format Big', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_date_format_big'); ?>
                            <input type="text" name="eventer_options[eventer_date_format_big]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Set date format for multi day event, that has different date in start and end date field.', 'eventer'); ?> <a href="<?php echo esc_url('http://php.net/manual/en/function.date.php'); ?>" target="_blank"><?php echo esc_url('http://php.net/manual/en/function.date.php'); ?></a></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Time format for datepicker', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_datepicker_format'); ?>
                            <label>
                              <input type="radio" name="eventer_options[eventer_datepicker_format]" value="12" <?php checked($value, "12"); ?>> <?php esc_html_e('12 Hour', 'eventer'); ?>
                            </label>
                            <label>
                              <input type="radio" name="eventer_options[eventer_datepicker_format]" value="24" <?php checked($value, "24"); ?>> <?php esc_html_e('24 Hour', 'eventer'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Set time format for the date picker used in back end and front end.', 'eventer'); ?></p>
                          </td>
                        </tr>
                      </table>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="coupon-settings" style="display: none;">
                      <img class="eventer-coupon-loading" src="<?php echo EVENTER__PLUGIN_URL; ?>images/loading.gif" style="display:none;">
                      <table class="coupon">
                        <tr class="eventer-coupons-heading">
                          <td>
                            <?php esc_html_e('Coupon Name', 'eventer'); ?>
                          </td>
                          <td>
                            <?php esc_html_e('Coupon Code', 'eventer'); ?>
                          </td>
                          <td>
                            <?php esc_html_e('Discounted Amount', 'eventer'); ?>
                          </td>
                          <td>
                            <?php esc_html_e('Valid Till', 'eventer'); ?>
                          </td>
                          <td>
                            <?php esc_html_e('Disable', 'eventer'); ?>
                          </td>
                          <td>
                            <?php esc_html_e('Remove', 'eventer'); ?>
                          </td>
                        </tr>
                        <tr class="eventer-coupon-clone" style="display:none;">
                          <td>
                            <input type="hidden" class="eventer-coupon-field eventer-coupon-id" value="" name="">
                            <input type="text" class="eventer-coupon-field eventer-coupon-title" value="" name="" placeholder="<?php esc_html_e('Enter name', 'eventer'); ?>">
                          </td>
                          <td>
                            <input type="text" class="eventer-coupon-field eventer-coupon-code" value="" name="" placeholder="<?php esc_html('Enter coupon code', 'eventer'); ?>">
                          </td>
                          <td>
                            <input type="text" class="eventer-coupon-field eventer-coupon-amount" value="" name="" placeholder="<?php esc_html_e('Enter amount or percent', 'eventer'); ?>">
                          </td>
                          <td>
                            <input type="text" class="eventer-coupon-field eventer-coupon-validity" value="" name="" placeholder="<?php esc_html('Select expiry date', 'eventer'); ?>">
                          </td>
                          <td>
                            <input type="checkbox" class="eventer-coupon-field eventer-coupon-status" value="1" name="eventer-coupon-status">
                          </td>
                          <td>
                            <input type="checkbox" class="eventer-coupon-field eventer-coupon-remove" value="1" name="eventer-coupon-remove">
                          </td>
                        </tr>
                      </table>
                      <br>
                      <button class="eventer-coupon-add-new"><?php esc_html_e('Add Coupon', 'eventer'); ?></button>
                      <button class="eventer-coupon-save"><?php esc_html_e('Save', 'eventer'); ?></button>
                      <p><?php esc_html_e('You can add percentage sign after amount to use percent for coupons', 'eventer'); ?></p>
                      <p><?php esc_html_e('Coupon functionality is currently available with default payment options only', 'eventer'); ?></p>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="calendar-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Calendar view', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_calendar_view'); ?>
                            <select name="eventer_options[eventer_calendar_view]">
                              <?php
                                      $options = array(
                                        'month' => esc_html__('Month View', 'eventer'),
                                        'basicWeek' => esc_html__('BasicWeek View', 'eventer'),
                                        'basicDay' => esc_html__('BasicDay View', 'eventer'),
                                        'agendaWeek' => esc_html__('AgendaWeek View', 'eventer'),
                                        'agendaDay' => esc_html__('AgendaDay View', 'eventer'),
                                        'listYear' => esc_html__('ListYear View', 'eventer'),
                                        'listMonth' => esc_html__('ListMonth View', 'eventer'),
                                        'listWeek' => esc_html__('ListWeek View', 'eventer'),
                                        'listDay' => esc_html__('ListDay View', 'eventer'),
                                      );
                                      foreach ($options as $id => $label) { ?>
                                <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                                  <?php echo strip_tags($label); ?>
                                </option>
                              <?php } ?>
                            </select>
                            <p class="description"><?php esc_html_e('Select your preferred view style for the events calendar.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Calendar weeks', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_calendar_weeks'); ?>
                            <select name="eventer_options[eventer_calendar_weeks]">
                              <?php
                                      $options = array(
                                        '6' => esc_html__('Six weeks', 'eventer'),
                                        '5' => esc_html__('Current month weeks', 'eventer'),
                                      );
                                      foreach ($options as $id => $label) { ?>
                                <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                                  <?php echo strip_tags($label); ?>
                                </option>
                              <?php } ?>
                            </select>
                            <p class="description"><?php esc_html_e('Select weeks for calendar.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Calendar header view', 'eventer'); ?></th>
                          <td>
                            <label class="eventer-label-block"><?php esc_html_e('Left', 'eventer'); ?></label>
                            <?php $value = self::get_eventer_option('eventer_calendar_header_left_view'); ?>
                            <input type="text" name="eventer_options[eventer_calendar_header_left_view]" value="<?php echo esc_attr($value); ?>">
                          </td>
                          <td>
                            <label class="eventer-label-block"><?php esc_html_e('Center', 'eventer'); ?></label>
                            <?php $value = self::get_eventer_option('eventer_calendar_header_center_view'); ?>
                            <input type="text" name="eventer_options[eventer_calendar_header_center_view]" value="<?php echo esc_attr($value); ?>">
                          </td>
                          <td>
                            <label class="eventer-label-block"><?php esc_html_e('Right', 'eventer'); ?></label>
                            <?php $value = self::get_eventer_option('eventer_calendar_header_right_view'); ?>
                            <input type="text" name="eventer_options[eventer_calendar_header_right_view]" value="<?php echo esc_attr($value); ?>">
                          </td>
                        </tr>

                        <tr>
                          <td></td>
                          <td colspan="3">
                            <p class="description"><?php esc_html_e('Enter your content options for the calendar header. Options available are: title(text containing the current month/week/day), prev(button for moving the calendar back one month/week/day), next(button for moving the calendar forward one month/week/day), prevYear(button for moving the calendar back on year), nextYear(button for moving the calendar forward one year), today(button for moving the calendar to the current month/week/day)', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Limit of Events', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('event_limit'); ?>
                            <input type="text" name="eventer_options[event_limit]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Limits the number of events displayed on a day on calendar.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Calendar RTL', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_calendar_rtl'); ?>
                            <input type="radio" name="eventer_options[eventer_calendar_rtl]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_calendar_rtl]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Check Yes if you need the calendar to follow Right to Left direction instead of default Left to Right direction.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Show End Time', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_calendar_end_time'); ?>
                            <input type="radio" name="eventer_options[eventer_calendar_end_time]" value="1" <?php checked($value, "1"); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_calendar_end_time]" value="0" <?php checked($value, "0"); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Check Yes if you need to show end time of event in calendar.', 'eventer'); ?></p>
                          </td>
                        </tr>

                      </table>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="api-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Google Maps API key', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('google_apikey'); ?>
                            <input type="text" name="eventer_options[google_apikey]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Enter Google maps API key here for the address field in the venue create page to show you suggested addresses. ', 'eventer'); ?><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php esc_html_e('How to get an API key for map'); ?></a></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Google Calendar API key', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('google_cal_apikey'); ?>
                            <input type="text" name="eventer_options[google_cal_apikey]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Enter Google calendar API key here to link your Google Calendars with your website. ', 'eventer'); ?><a href="https://support.imithemes.com/forums/topic/updatedsetting-up-google-calendar-api-for-events-calendar/" target="_blank"><?php esc_html_e('How to get an API key for calendar'); ?></a></p>
                            <p><?php esc_html_e('Please make sure that the API should not have the referer restrictions', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Google Calendar ID', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('cal_id'); ?>
                            <input type="text" name="eventer_options[cal_id]" value="<?php echo esc_attr($value); ?>">
                            <p class="description"><?php esc_html_e('Enter your Google Calendar ID here which you want to import in your website events.. ', 'eventer'); ?><a href="https://support.imithemes.com/forums/topic/updatedsetting-up-google-calendar-api-for-events-calendar/" target="_blank"><?php esc_html_e('How to get your calendar ID'); ?></a></p>
                          </td>
                        </tr>
                      </table>
                    </div>

                    <div class="general-settings eventer-admin-tab-content" id="form-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Ticket Booking Form', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('ticket_booking_fields'); ?>
                            <textarea rows="8" cols="" name="eventer_options[ticket_booking_fields]" style="width: 100%"><?php echo esc_attr($value); ?></textarea>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the ticket booking form available at the single event page. Fields can be created at Eventer => Settings => Shortcodes', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Contact Organizer Form', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('contact_organizer_fields'); ?>
                            <textarea rows="8" cols="" name="eventer_options[contact_organizer_fields]" style="width: 100%"><?php echo esc_attr($value); ?></textarea>
                            <p class="description"><?php esc_html_e('Enter content(Text/HTML) for the form available on single event page to contact the event organizer. Fields can be created at Eventer => Settings => Shortcodes', 'eventer'); ?></p>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="general-settings eventer-admin-tab-content" id="woocommerce-settings" style="display: none;">
                      <table class="form-table eventer-custom-admin-login-table">

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Enable woocommerce payment', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_enable_woocommerce_ticketing'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_woocommerce_ticketing]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_enable_woocommerce_ticketing]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Woocommerce Payment Layout', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_woo_layout'); ?>
                            <input type="radio" name="eventer_options[eventer_woo_layout]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Plugin', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_woo_layout]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('Cart', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_woo_layout]" value="checkout" <?php checked($value, 'checkout'); ?>> <?php esc_html_e('Popup with Woocommerce Checkout', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Use layout of Woocommerce payment.', 'eventer'); ?></p>
                          </td>
                        </tr>

                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('Thanks page redirect', 'eventer'); ?></th>
                          <td colspan="3">
                            <?php $value = self::get_eventer_option('eventer_thanks_redirect'); ?>
                            <input type="radio" name="eventer_options[eventer_thanks_redirect]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                            <input type="radio" name="eventer_options[eventer_thanks_redirect]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                            <p class="description"><?php esc_html_e('Redirect to single details page after order completed.', 'eventer'); ?></p>
                          </td>
                        </tr>
                        <th scope="row"><?php esc_html_e('Add order', 'eventer'); ?></th>
                        <td colspan="3">
                          <?php $value = self::get_eventer_option('eventer_woo_orders'); ?>
                          <input type="radio" name="eventer_options[eventer_woo_orders]" value="on" <?php checked($value, 'on'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                          <input type="radio" name="eventer_options[eventer_woo_orders]" value="off" <?php checked($value, 'off'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                          <p class="description"><?php esc_html_e('Add order for booking record.', 'eventer'); ?></p>
                        </td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
          <!--Payment Settings Tab-->
          <?php if ($active_tab == 'payment') { ?>
            <h3><?php _e('Payment Settings', 'eventer'); ?></h3>
            <div id="payment-settings" class="ui-sortable meta-box-sortables">
              <div class="postbox">

                <div class="inside">
                  <table class="form-table eventer-custom-admin-login-table">

                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Autocomplete Orders', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_order_autocomplete'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_order_autocomplete]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_order_autocomplete]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Disable', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Autocomplete orders in any case.', 'eventer'); ?></p>
                      </td>
                    </tr>

                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Currency', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_paypal_currency'); ?>
                        <select name="eventer_options[eventer_paypal_currency]">
                          <?php
                                  $options = array(
                                    'USD' => esc_html__('U.S. Dollar', 'eventer'),
                                    'AED' => esc_html__('United Arab Emirates dirham', 'eventer'),

                                    'AFN' => esc_html__('Afghan afghani', 'eventer'),
                                    'ALL' => esc_html__('Albanian lek', 'eventer'),
                                    'AMD' => esc_html__('Armenian dram', 'eventer'),
                                    'ANG' => esc_html__('Netherlands Antillean guilder', 'eventer'),
                                    'AOA' => esc_html__('Angolan kwanza', 'eventer'),
                                    'ARS' => esc_html__('Argentine peso', 'eventer'),
                                    'AWG' => esc_html__('Aruban florin', 'eventer'),
                                    'AUD' => esc_html__('Australian Dollar', 'eventer'),
                                    'AZN' => esc_html__('Azerbaijani manat', 'eventer'),
                                    'BAM' => esc_html__('Bosnia and Herzegovina convertible mark', 'eventer'),
                                    'BBD' => esc_html__('Barbadian dollar', 'eventer'),
                                    'BDT' => esc_html__('Bangladeshi taka', 'eventer'),
                                    'BGN' => esc_html__('Bulgarian lev', 'eventer'),
                                    'BHD' => esc_html__('Bahraini dinar', 'eventer'),
                                    'BIF' => esc_html__('Burundian franc', 'eventer'),
                                    'BMD' => esc_html__('Bermudian dollar', 'eventer'),
                                    'BND' => esc_html__('Brunei dollar', 'eventer'),
                                    'BOB' => esc_html__('Bolivian boliviano', 'eventer'),
                                    'BSD' => esc_html__('Bahamian dollar', 'eventer'),
                                    'BTC' => esc_html__('Bitcoin', 'eventer'),
                                    'BTN' => esc_html__('Bhutanese ngultrum', 'eventer'),
                                    'BWP' => esc_html__('Botswana pula', 'eventer'),
                                    'BYR' => esc_html__('Belarusian ruble', 'eventer'),
                                    'BYN' => esc_html__('Belarusian ruble', 'eventer'),
                                    'BZD' => esc_html__('Belize dollar', 'eventer'),
                                    'CDF' => esc_html__('Congolese franc', 'eventer'),
                                    'CHF' => esc_html__('Swiss franc', 'eventer'),
                                    'CAD' => esc_html__('Canadian dollar ($)', 'eventer'),
                                    "CLP" => esc_html__("Chilean peso ($)", "eventer"),
                                    "CNY" => esc_html__("Chinese yuan (¥)", "eventer"),
                                    "COP" => esc_html__("Colombian peso ($)", "eventer"),
                                    "CRC" => esc_html__("Costa Rican colón (₡)", "eventer"),
                                    "CUC" => esc_html__("Cuban convertible peso ($)", "eventer"),
                                    "CUP" => esc_html__("Cuban peso ($)", "eventer"),
                                    "CVE" => esc_html__("Cape Verdean escudo ($)", "eventer"),
                                    "CZK" => esc_html__("Czech koruna (Kč)", "eventer"),
                                    "DJF" => esc_html__("Djiboutian franc (Fr)", "eventer"),
                                    "DKK" => esc_html__("Danish krone (DKK)", "eventer"),
                                    "DOP" => esc_html__("Dominican peso (RD$)", "eventer"),
                                    "DZD" => esc_html__("Algerian dinar (د.ج)", "eventer"),
                                    "EGP" => esc_html__("Egyptian pound (EGP)", "eventer"),
                                    "ERN" => esc_html__("Eritrean nakfa (Nfk)", "eventer"),
                                    "ETB" => esc_html__("Ethiopian birr (Br)", "eventer"),
                                    "EUR" => esc_html__("Euro (€)", "eventer"),
                                    "FJD" => esc_html__("Fijian dollar ($)", "eventer"),
                                    "FKP" => esc_html__("Falkland Islands pound (£)", "eventer"),
                                    "GBP" => esc_html__("Pound sterling (£)", "eventer"),
                                    "GEL" => esc_html__("Georgian lari (₾)", "eventer"),
                                    "GGP" => esc_html__("Guernsey pound (£)", "eventer"),
                                    "GHS" => esc_html__("Ghana cedi (₵)", "eventer"),
                                    "GIP" => esc_html__("Gibraltar pound (£)", "eventer"),
                                    "GMD" => esc_html__("Gambian dalasi (D)", "eventer"),
                                    "GNF" => esc_html__("Guinean franc (Fr)", "eventer"),
                                    "GTQ" => esc_html__("Guatemalan quetzal (Q)", "eventer"),
                                    "GYD" => esc_html__("Guyanese dollar ($)", "eventer"),
                                    "HKD" => esc_html__("Hong Kong dollar ($)", "eventer"),
                                    "HNL" => esc_html__("Honduran lempira (L)", "eventer"),
                                    "HRK" => esc_html__("Croatian kuna (Kn)", "eventer"),
                                    "HTG" => esc_html__("Haitian gourde (G)", "eventer"),
                                    "HUF" => esc_html__("Hungarian forint (Ft)", "eventer"),
                                    "IDR" => esc_html__("Indonesian rupiah (Rp)", "eventer"),
                                    "ILS" => esc_html__("Israeli new shekel (₪)", "eventer"),
                                    "IMP" => esc_html__("Manx pound (£)", "eventer"),
                                    "INR" => esc_html__("Indian rupee (₹)", "eventer"),
                                    "IQD" => esc_html__("Iraqi dinar (ع.د)", "eventer"),
                                    "IRR" => esc_html__("Iranian rial (﷼)", "eventer"),
                                    "IRT" => esc_html__("Iranian toman (تومان)", "eventer"),
                                    "ISK" => esc_html__("Icelandic króna (kr.)", "eventer"),
                                    "JEP" => esc_html__("Jersey pound (£)", "eventer"),
                                    "JMD" => esc_html__("Jamaican dollar ($)", "eventer"),
                                    "JOD" => esc_html__("Jordanian dinar (د.ا)", "eventer"),
                                    "JPY" => esc_html__("Japanese yen (¥)", "eventer"),
                                    "KES" => esc_html__("Kenyan shilling (KSh)", "eventer"),
                                    "KGS" => esc_html__("Kyrgyzstani som (сом)", "eventer"),
                                    "KHR" => esc_html__("Cambodian riel (៛)", "eventer"),
                                    "KMF" => esc_html__("Comorian franc (Fr)", "eventer"),
                                    "KPW" => esc_html__("North Korean won (₩)", "eventer"),
                                    "KRW" => esc_html__("South Korean won (₩)", "eventer"),
                                    "KWD" => esc_html__("Kuwaiti dinar (د.ك)", "eventer"),
                                    "KYD" => esc_html__("Cayman Islands dollar ($)", "eventer"),
                                    "KZT" => esc_html__("Kazakhstani tenge (KZT)", "eventer"),
                                    "LAK" => esc_html__("Lao kip (₭)", "eventer"),
                                    "LBP" => esc_html__("Lebanese pound (ل.ل)", "eventer"),
                                    "LKR" => esc_html__("Sri Lankan rupee (රු)", "eventer"),
                                    "LRD" => esc_html__("Liberian dollar ($)", "eventer"),
                                    "LSL" => esc_html__("Lesotho loti (L)", "eventer"),
                                    "LYD" => esc_html__("Libyan dinar (ل.د)", "eventer"),
                                    "MAD" => esc_html__("Moroccan dirham (د.م.)", "eventer"),
                                    "MDL" => esc_html__("Moldovan leu (MDL)", "eventer"),
                                    "MGA" => esc_html__("Malagasy ariary (Ar)", "eventer"),
                                    "MKD" => esc_html__("Macedonian denar (ден)", "eventer"),
                                    "MMK" => esc_html__("Burmese kyat (Ks)", "eventer"),
                                    "MNT" => esc_html__("Mongolian tögrög (₮)", "eventer"),
                                    "MOP" => esc_html__("Macanese pataca (P)", "eventer"),
                                    "MRO" => esc_html__("Mauritanian ouguiya (UM)", "eventer"),
                                    "MUR" => esc_html__("Mauritian rupee (₨)", "eventer"),
                                    "MVR" => esc_html__("Maldivian rufiyaa (.ރ)", "eventer"),
                                    "MWK" => esc_html__("Malawian kwacha (MK)", "eventer"),
                                    "MXN" => esc_html__("Mexican peso ($)", "eventer"),
                                    "MYR" => esc_html__("Malaysian ringgit (RM)", "eventer"),
                                    "MZN" => esc_html__("Mozambican metical (MT)", "eventer"),
                                    "NAD" => esc_html__("Namibian dollar ($)", "eventer"),
                                    "NGN" => esc_html__("Nigerian naira (₦)", "eventer"),
                                    "NIO" => esc_html__("Nicaraguan córdoba (C$)", "eventer"),
                                    "NOK" => esc_html__("Norwegian krone (kr)", "eventer"),
                                    "NPR" => esc_html__("Nepalese rupee (₨)", "eventer"),
                                    "NZD" => esc_html__("New Zealand dollar ($)", "eventer"),
                                    "OMR" => esc_html__("Omani rial (ر.ع.)", "eventer"),
                                    "PAB" => esc_html__("Panamanian balboa (B/.)", "eventer"),
                                    "PEN" => esc_html__("Peruvian nuevo sol (S/.)", "eventer"),
                                    "PGK" => esc_html__("Papua New Guinean kina (K)", "eventer"),
                                    "PHP" => esc_html__("Philippine peso (₱)", "eventer"),
                                    "PKR" => esc_html__("Pakistani rupee (₨)", "eventer"),
                                    "PLN" => esc_html__("Polish złoty (zł)", "eventer"),
                                    "PRB" => esc_html__("Transnistrian ruble (р.)", "eventer"),
                                    "PYG" => esc_html__("Paraguayan guaraní (₲)", "eventer"),
                                    "QAR" => esc_html__("Qatari riyal (ر.ق)", "eventer"),
                                    "RON" => esc_html__("Romanian leu (lei)", "eventer"),
                                    "RSD" => esc_html__("Serbian dinar (дин.)", "eventer"),
                                    "RUB" => esc_html__("Russian ruble (₽)", "eventer"),
                                    "RWF" => esc_html__("Rwandan franc (Fr)", "eventer"),
                                    "SAR" => esc_html__("Saudi riyal (ر.س)", "eventer"),
                                    "SBD" => esc_html__("Solomon Islands dollar ($)", "eventer"),
                                    "SCR" => esc_html__("Seychellois rupee (₨)", "eventer"),
                                    "SDG" => esc_html__("Sudanese pound (ج.س.)", "eventer"),
                                    "SEK" => esc_html__("Swedish krona (kr)", "eventer"),
                                    "SGD" => esc_html__("Singapore dollar ($)", "eventer"),
                                    "SHP" => esc_html__("Saint Helena pound (£)", "eventer"),
                                    "SLL" => esc_html__("Sierra Leonean leone (Le)", "eventer"),
                                    "SOS" => esc_html__("Somali shilling (Sh)", "eventer"),
                                    "SRD" => esc_html__("Surinamese dollar ($)", "eventer"),
                                    "SSP" => esc_html__("South Sudanese pound (£)", "eventer"),
                                    "STD" => esc_html__("São Tomé and Príncipe dobra (Db)", "eventer"),
                                    "SYP" => esc_html__("Syrian pound (ل.س)", "eventer"),
                                    "SZL" => esc_html__("Swazi lilangeni (L)", "eventer"),
                                    "THB" => esc_html__("Thai baht (฿)", "eventer"),
                                    "TJS" => esc_html__("Tajikistani somoni (ЅМ)", "eventer"),
                                    "TMT" => esc_html__("Turkmenistan manat (m)", "eventer"),
                                    "TND" => esc_html__("Tunisian dinar (د.ت)", "eventer"),
                                    "TOP" => esc_html__("Tongan paʻanga (T$)", "eventer"),
                                    "TRY" => esc_html__("Turkish lira (₺)", "eventer"),
                                    "TTD" => esc_html__("Trinidad and Tobago dollar ($)", "eventer"),
                                    "TWD" => esc_html__("New Taiwan dollar (NT$)", "eventer"),
                                    "TZS" => esc_html__("Tanzanian shilling (Sh)", "eventer"),
                                    "UAH" => esc_html__("Ukrainian hryvnia (₴)", "eventer"),
                                    "UGX" => esc_html__("Ugandan shilling (UGX)", "eventer"),

                                    "UYU" => esc_html__("Uruguayan peso ($)", "eventer"),
                                    "UZS" => esc_html__("Uzbekistani som (UZS)", "eventer"),
                                    "VEF" => esc_html__("Venezuelan bolívar (Bs F)", "eventer"),
                                    "VND" => esc_html__("Vietnamese đồng (₫)", "eventer"),
                                    "VUV" => esc_html__("Vanuatu vatu (Vt)", "eventer"),
                                    "WST" => esc_html__("Samoan tālā (T)", "eventer"),
                                    "XAF" => esc_html__("Central African CFA franc (CFA)", "eventer"),
                                    "XCD" => esc_html__("East Caribbean dollar ($)", "eventer"),
                                    "XOF" => esc_html__("West African CFA franc (CFA)", "eventer"),
                                    "XPF" => esc_html__("CFP franc (Fr)", "eventer"),
                                    "YER" => esc_html__("Yemeni rial (﷼)", "eventer"),
                                    "ZAR" => esc_html__("South African rand (R)", "eventer"),
                                    "ZMW" => esc_html__("Zambian kwacha (ZK)", "eventer"),
                                  );
                                  foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select your preferred currency for payments through PayPal.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Currency Position', 'eventer'); ?></th>
                      <td colspan="3">
                        <?php $value = self::get_eventer_option('eventer_currency_position'); ?>
                        <select name="eventer_options[eventer_currency_position]">
                          <?php
                                  $options = array(
                                    'postfix' => esc_html__('10$', 'eventer'),
                                    'suffix' => esc_html__('$10', 'eventer'),
                                  );
                                  foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <h3><?php _e('Stripe Payment Settings', 'eventer'); ?></h3>
            <div id="payment-settings" class="ui-sortable meta-box-sortables">
              <div class="postbox">

                <div class="inside">
                  <table class="form-table eventer-custom-admin-login-table">
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Stripe payment', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_stripe_payment_switch'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_stripe_payment_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_stripe_payment_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Disable', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Select Yes to enable Stripe payments option for event registration.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Set as default', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_stripe_default_switch'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_stripe_default_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_stripe_default_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Select Yes to set Stripe as default payment option.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Stripe Publishable key', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_stripe_publishable_key'); ?>
                        <input type="text" name="eventer_options[eventer_stripe_publishable_key]" value="<?php echo esc_attr($value); ?>">
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Stripe Secret key', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_stripe_secret_key'); ?>
                        <input type="text" name="eventer_options[eventer_stripe_secret_key]" value="<?php echo esc_attr($value); ?>">
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <h3><?php _e('Event PayPal Settings', 'eventer'); ?></h3>
            <div id="payment-settings" class="ui-sortable meta-box-sortables">
              <div class="postbox">

                <div class="inside">
                  <table class="form-table eventer-custom-admin-login-table">

                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('PayPal payment', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_paypal_payment_switch'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_paypal_payment_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_paypal_payment_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Disable', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Select Yes to enable PayPal payments option for event registration.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Set as default', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_paypal_default_switch'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_paypal_default_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_paypal_default_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Select Yes to set PayPal as default payment option.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Paypal Mode', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_paypal_payment_type'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_paypal_payment_type]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Live', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_paypal_payment_type]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Sandbox', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Choose mode of PayPal payments. Select Live when you are ready with your business PayPal email/account. For test payments choose Sandbox.', 'eventer'); ?></p>
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Paypal Business Email', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_paypal_business_email'); ?>
                        <input type="text" name="eventer_options[eventer_paypal_business_email]" value="<?php echo esc_attr($value); ?>">
                        <p class="description"><?php esc_html_e('Enter your live PayPal business email for live payments or your PayPal sandbox email address for test payments.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('IPN URL', 'eventer'); ?></th>
                      <td>
                        <label><?php echo esc_url(add_query_arg('action', 'IPN_Handler', home_url('/'))); ?></label>
                        <p><?php esc_html_e('This URL is used to verify PayPal payments and to update details of registrants once they are verified.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <!--<h3><?php _e('Event dotpay Settings', 'eventer'); ?></h3>
						<div id="payment-settings" class="ui-sortable meta-box-sortables">
							<div class="postbox">

								<div class="inside">
									<table class="form-table eventer-custom-admin-login-table">

										<tr valign="top">
											<th scope="row"><?php esc_html_e('dotpay payment', 'eventer'); ?></th>
											<td>
												<?php $value = self::get_eventer_option('eventer_dotpay_payment_switch'); ?>
												<label>
													<input type="radio" name="eventer_options[eventer_dotpay_payment_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
												</label>
												<label>
													<input type="radio" name="eventer_options[eventer_dotpay_payment_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Disable', 'eventer'); ?>
												</label>
												<p class="description"><?php esc_html_e('Select Yes to enable dotpay payments option for event registration.', 'eventer'); ?></p>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?php esc_html_e('Set as default', 'eventer'); ?></th>
											<td>
												<?php $value = self::get_eventer_option('eventer_dotpay_default_switch'); ?>
												<label>
													<input type="radio" name="eventer_options[eventer_dotpay_default_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
												</label>
												<label>
													<input type="radio" name="eventer_options[eventer_dotpay_default_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('No', 'eventer'); ?>
												</label>
												<p class="description"><?php esc_html_e('Select Yes to set dotpay as default payment option.', 'eventer'); ?></p>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?php esc_html_e('dotpay Mode', 'eventer'); ?></th>
											<td>
												<?php $value = self::get_eventer_option('eventer_dotpay_payment_type'); ?>
												<label>
													<input type="radio" name="eventer_options[eventer_dotpay_payment_type]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Live', 'eventer'); ?>
												</label>
												<label>
													<input type="radio" name="eventer_options[eventer_dotpay_payment_type]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Test', 'eventer'); ?>
												</label>
												<p class="description"><?php esc_html_e('Choose mode of dotpay payments. Select Live when you are ready with your business PayPal email/account. For test payments choose Test.', 'eventer'); ?></p>
											</td>
										</tr>

										<tr valign="top">
											<th scope="row"><?php esc_html_e('dotpay Account ID', 'eventer'); ?></th>
											<td>
												<?php $value = self::get_eventer_option('eventer_dotpay_business_id'); ?>
												<input type="text" name="eventer_options[eventer_dotpay_business_id]" value="<?php echo esc_attr($value); ?>">
												<p class="description"><?php esc_html_e('Enter your live dotpay business id to make payment in your account.', 'eventer'); ?></p>
											</td>
										</tr>

										<tr valign="top">
											<th scope="row"><?php esc_html_e('dotpay Account PIN', 'eventer'); ?></th>
											<td>
												<?php $value = self::get_eventer_option('eventer_dotpay_business_pin'); ?>
												<input type="text" name="eventer_options[eventer_dotpay_business_pin]" value="<?php echo esc_attr($value); ?>">
												<p class="description"><?php esc_html_e('Enter your live dotpay business PIN to make payment in your account.', 'eventer'); ?></p>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>-->
            <h3><?php _e('Event Offline Payment Settings', 'eventer'); ?></h3>
            <div id="payment-settings" class="ui-sortable meta-box-sortables">
              <div class="postbox">

                <div class="inside">
                  <table class="form-table eventer-custom-admin-login-table">
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Offline payment', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_offline_payment_switch'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_offline_payment_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Enable', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_offline_payment_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('Disable', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Select Yes to enable offline payments option for event registration.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Set as default', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_offline_default_switch'); ?>
                        <label>
                          <input type="radio" name="eventer_options[eventer_offline_default_switch]" value="1" <?php checked($value, '1'); ?>> <?php esc_html_e('Yes', 'eventer'); ?>
                        </label>
                        <label>
                          <input type="radio" name="eventer_options[eventer_offline_default_switch]" value="0" <?php checked($value, '0'); ?>> <?php esc_html_e('No', 'eventer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Select Yes to set Offline as default payment option.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Offline payment info', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_offline_payment_desc'); ?>
                        <textarea name="eventer_options[eventer_offline_payment_desc]" style="width: 80%"><?php echo esc_attr($value); ?></textarea>
                        <p class="description"><?php esc_html_e('Enter some information here which will be shown to users while making payments for the events. You can add your bank details where users can send offline payments.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          <?php } ?>
          <!--Eventer Permalink Settings Tab-->
          <?php if ($active_tab == 'eventer_permalink') { ?>
            <h3><?php esc_html_e('Event Permalink Settings', 'eventer'); ?></h3>
            <div id="permalink-settings" class="ui-sortable meta-box-sortables">
              <div class="postbox">

                <div class="inside">
                  <table class="form-table eventer-custom-admin-login-table">

                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Event Posts', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_event_permalink'); ?>
                        <input type="text" name="eventer_options[eventer_event_permalink]" value="<?php echo esc_attr($value); ?>">
                        <p class="description"><?php esc_html_e('Use this field to change slug of event post type for front end.', 'eventer'); ?></p>
                      </td>
                    </tr>
                    <?php if ($eventer_enable_categories == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Category Permalink', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_event_category_permalink'); ?>
                          <input type="text" name="eventer_options[eventer_event_category_permalink]" value="<?php echo esc_attr($value); ?>">
                          <p class="description"><?php esc_html_e('Use this field to change slug of event categories taxonomy for front end.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php }
                            if ($eventer_enable_tags == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Tags Permalink', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_event_tag_permalink'); ?>
                          <input type="text" name="eventer_options[eventer_event_tag_permalink]" value="<?php echo esc_attr($value); ?>">
                          <p class="description"><?php esc_html_e('Use this field to change slug of event tags taxonomy for front end.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php }
                            if ($eventer_enable_venue == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Venue Permalink', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_event_venue_permalink'); ?>
                          <input type="text" name="eventer_options[eventer_event_venue_permalink]" value="<?php echo esc_attr($value); ?>">
                          <p class="description"><?php esc_html_e('Use this field to change slug of event venue taxonomy for front end.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php }
                            if ($eventer_enable_organizer == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Organizer Permalink', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_event_organizer_permalink'); ?>
                          <input type="text" name="eventer_options[eventer_event_organizer_permalink]" value="<?php echo esc_attr($value); ?>">
                          <p class="description"><?php esc_html_e('Use this field to change slug of event organizer taxonomy for front end.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php } ?>
                  </table>
                </div>
              </div>
            </div>
          <?php } ?>

          <!--Eventer Templates Settings Tab-->
          <?php if ($active_tab == 'eventer_templates') { ?>
            <h3><?php esc_html_e('Event Templates', 'eventer'); ?></h3>
            <div id="permalink-settings" class="ui-sortable meta-box-sortables">
              <div class="postbox">

                <div class="inside">
                  <table class="form-table eventer-custom-admin-login-table">

                    <?php
                            if ($eventer_enable_categories == 'on') {
                              ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Category View', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_category_view'); ?>
                          <select name="eventer_options[eventer_category_view]">
                            <?php
                                      $options = array(
                                        '1' => esc_html__('Compact with pagination', 'eventer'),
                                        '2' => esc_html__('Monthly view compact', 'eventer'),
                                        '3' => esc_html__('Minimal with pagination', 'eventer'),
                                        '4' => esc_html__('Monthly view minimal', 'eventer'),
                                        '5' => esc_html__('Grid', 'eventer'),
                                      );
                                      foreach ($options as $id => $label) { ?>
                              <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                                <?php echo strip_tags($label); ?>
                              </option>
                            <?php } ?>
                          </select>
                          <p class="description"><?php esc_html_e('Choose design view for event category archive pages.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php }
                            if ($eventer_enable_venue == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Venues View', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_venues_view'); ?>
                          <select name="eventer_options[eventer_venues_view]">
                            <?php
                                      $options = array(
                                        '1' => esc_html__('Compact with pagination', 'eventer'),
                                        '2' => esc_html__('Monthly view compact', 'eventer'),
                                        '3' => esc_html__('Minimal with pagination', 'eventer'),
                                        '4' => esc_html__('Monthly view minimal', 'eventer'),
                                        '5' => esc_html__('Grid', 'eventer'),
                                      );
                                      foreach ($options as $id => $label) { ?>
                              <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                                <?php echo strip_tags($label); ?>
                              </option>
                            <?php } ?>
                          </select>
                          <p class="description"><?php esc_html_e('Choose design view for event venues archive pages.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php }
                            if ($eventer_enable_organizer == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Organiser View', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_organizer_view'); ?>
                          <select name="eventer_options[eventer_organizer_view]">
                            <?php
                                      $options = array(
                                        '1' => esc_html__('Compact with pagination', 'eventer'),
                                        '2' => esc_html__('Monthly view compact', 'eventer'),
                                        '3' => esc_html__('Minimal with pagination', 'eventer'),
                                        '4' => esc_html__('Monthly view minimal', 'eventer'),
                                        '5' => esc_html__('Grid', 'eventer'),
                                      );
                                      foreach ($options as $id => $label) { ?>
                              <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                                <?php echo strip_tags($label); ?>
                              </option>
                            <?php } ?>
                          </select>
                          <p class="description"><?php esc_html_e('Choose design view for event organiser archive pages.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php }
                            if ($eventer_enable_tags == 'on') { ?>
                      <tr valign="top">
                        <th scope="row"><?php esc_html_e('Event Tags View', 'eventer'); ?></th>
                        <td>
                          <?php $value = self::get_eventer_option('eventer_tag_view'); ?>
                          <select name="eventer_options[eventer_tag_view]">
                            <?php
                                      $options = array(
                                        '1' => esc_html__('Compact with pagination', 'eventer'),
                                        '2' => esc_html__('Monthly view compact', 'eventer'),
                                        '3' => esc_html__('Minimal with pagination', 'eventer'),
                                        '4' => esc_html__('Monthly view minimal', 'eventer'),
                                        '5' => esc_html__('Grid', 'eventer'),
                                      );
                                      foreach ($options as $id => $label) { ?>
                              <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                                <?php echo strip_tags($label); ?>
                              </option>
                            <?php } ?>
                          </select>
                          <p class="description"><?php esc_html_e('Choose design view for event tag archive pages.', 'eventer'); ?></p>
                        </td>
                      </tr>
                    <?php } ?>
                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Event Archive View', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_archive_view'); ?>
                        <select name="eventer_options[eventer_archive_view]">
                          <?php
                                  $options = array(
                                    '1' => esc_html__('Compact with pagination', 'eventer'),
                                    '2' => esc_html__('Monthly view compact', 'eventer'),
                                    '3' => esc_html__('Minimal with pagination', 'eventer'),
                                    '4' => esc_html__('Monthly view minimal', 'eventer'),
                                    '5' => esc_html__('Grid', 'eventer'),
                                  );
                                  foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Choose design view for event archive pages.', 'eventer'); ?></p>
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row"><?php esc_html_e('Event Search View', 'eventer'); ?></th>
                      <td>
                        <?php $value = self::get_eventer_option('eventer_search_view'); ?>
                        <select name="eventer_options[eventer_search_view]">
                          <?php
                                  $options = array(
                                    '1' => esc_html__('Compact with pagination', 'eventer'),
                                    '2' => esc_html__('Monthly view compact', 'eventer'),
                                    '3' => esc_html__('Minimal with pagination', 'eventer'),
                                    '4' => esc_html__('Monthly view minimal', 'eventer'),
                                    '5' => esc_html__('Grid', 'eventer'),
                                  );
                                  foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>" <?php selected($value, $id, true); ?>>
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Choose design view for event search results page.', 'eventer'); ?></p>
                      </td>
                    </tr>

                  </table>
                </div>
              </div>
            </div>
          <?php } ?>

          <!--Shortcode Settings Tab-->
          <?php if ($active_tab != 'shortcode' && $active_tab != 'bookings' && $active_tab != 'import' && $active_tab != 'checkin') { ?>
            <?php submit_button(esc_html__('Save Changes', 'eventer')); ?>
          <?php } ?>
        </form>
        <?php if ($active_tab == 'shortcode') { ?>
          <select class="choose_shortcode" style="margin-top: 20px">
            <option value="counter"><?php esc_html_e('Counter', 'eventer'); ?></option>
            <option value="list"><?php esc_html_e('Event List', 'eventer'); ?></option>
            <option value="grid"><?php esc_html_e('Event Grid', 'eventer'); ?></option>
            <option value="slider"><?php esc_html_e('Event Slider', 'eventer'); ?></option>
            <option value="calendar"><?php esc_html_e('Event Calendar', 'eventer'); ?></option>
            <option value="field"><?php esc_html_e('Form Fields', 'eventer'); ?></option>
            <option value="form"><?php esc_html_e('Add event form', 'eventer'); ?></option>
            <option value="dashboard"><?php esc_html_e('Dashboard', 'eventer'); ?></option>
          </select>

          <div id="counter-settings" class="ui-sortable meta-box-sortables">
            <h3><?php _e('Event counter shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <table class="form-table eventer-custom-admin-login-table">
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Select Event', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_ids eventer_select_val" data-sattr="ids" id="counter_ids">
                        <?php
                                $options = eventer_get_eventer_list();
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('You can select a specific event to show at the upcoming event counter.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <?php
                          if ($eventer_enable_categories == 'on') {
                            ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Select event category', 'eventer'); ?></th>
                      <td>
                        <select class="eventer_terms eventer_select_val" data-sattr="terms_cats" id="counter_terms_cats">
                          <?php
                                    $options = eventer_get_terms('eventer-category');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event categories from which events will be used in the upcoming event counter.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_tags == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Select event tags', 'eventer'); ?></th>
                      <td>
                        <select class="eventer_terms eventer_select_val" data-sattr="terms_tags" id="counter_terms_tags">
                          <?php
                                    $options = eventer_get_terms('eventer-tag');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event tags, from which events will be used in the upcoming event counter.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_venue == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Select event venue', 'eventer'); ?></th>
                      <td>
                        <select class="eventer_terms eventer_select_val" data-sattr="terms_venue" id="counter_terms_venues">
                          <?php
                                    $options = eventer_get_terms('eventer-venue');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event venues, from which events will be used in the upcoming event counter.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_organizer == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Select event organizer', 'eventer'); ?></th>
                      <td>
                        <select class="eventer_terms eventer_select_val" data-sattr="terms_organizer" id="counter_terms_organizers">
                          <?php
                                    $options = eventer_get_terms('eventer-organizer');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event organisers, from which events will be used in the upcoming event counter.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php } ?>
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Show Event venue', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="venue" id="counter_venue">
                        <?php
                                $options = array(
                                  '' => esc_html__('Yes', 'eventer'),
                                  'no' => esc_html__('No', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes if you want to show your event venue address in the upcoming event counter.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event type', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="type" id="counter_type">
                        <?php
                                $options = array(
                                  '1' => esc_html__('WP', 'eventer'),
                                  '2' => esc_html__('Google', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select which event type you want to show in the upcoming event counter.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Show counter until', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="event_until" id="counter_event_until">
                        <?php
                                $options = array(
                                  '' => esc_html__('Start Time', 'eventer'),
                                  '2' => esc_html__('End Time', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select till what time an event will be shown in the upcoming event counter.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><button id="eventer_counter" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td><code>[eventer_counter]</code></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>


          <div id="list-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php _e('Event list shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <table class="form-table eventer-custom-admin-login-table">
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Select Event', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_ids eventer_select_val" data-sattr="ids" id="list_ids">
                        <?php
                                $options = eventer_get_eventer_list();
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('You can select specific events to show in the list. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                    </td>
                  </tr>
                  <?php if ($eventer_enable_categories == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Category', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_cats" id="list_terms_cats">
                          <?php
                                    $options = eventer_get_terms('eventer-category');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event category, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_tags == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Tags', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_tags" id="list_terms_tags">
                          <?php
                                    $options = eventer_get_terms('eventer-tag');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event tags, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_venue == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Venue', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_venue" id="list_terms_venues">
                          <?php
                                    $options = eventer_get_terms('eventer-venue');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event venues, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_organizer == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Organiser', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_organizer" id="list_terms_organizers">
                          <?php
                                    $options = eventer_get_terms('eventer-organizer');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event organisers, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php } ?>
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Type', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="type" id="list_type">
                        <?php
                                $options = array(
                                  '' => esc_html__('All', 'eventer'),
                                  '1' => esc_html__('WP', 'eventer'),
                                  '2' => esc_html__('Google', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select event type for the list. You can choose All to show both WordPress and Google Calendar events in the list or WP/Google to show selected events only.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Featured Events', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="featured" id="list_featured">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  '1' => esc_html__('Yes', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select yes to show featured events at the top of list view.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <!--<tr valign="top" class="eventer-custom-admin-screen-background-section">
																																																																																																																																																																																																																																																																																																																																																																																																										<th scope="row"><?php esc_html_e('Show Details under list', 'eventer'); ?></th>
																																																																																																																																																																																																																																																																																																																																																																																																										<td>
																																																																																																																																																																																																																																																																																																																																																																																																											<select class="eventer_select_val" data-sattr="details" id="list_details">
																																																																																																																																																																																																																																																																																																																																																																																																												<?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                $options = array(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  '' => esc_html__('No', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  '1' => esc_html__('Yes', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                );
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                foreach ($options as $id => $label) { ?>
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														<option value="<?php echo esc_attr($id); ?>">
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																															<?php echo strip_tags($label); ?>
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														</option>
																																																																																																																																																																																																																																																																																																																																																																																																												<?php } ?>
																																																																																																																																																																																																																																																																																																																																																																																																											</select>
																																																																																																																																																																																																																																																																																																																																																																																																											<p class="description"><?php esc_html_e('Select yes to show event meta and ticket details under list view.', 'eventer'); ?></p>
																																																																																																																																																																																																																																																																																																																																																																																																										</td>
																																																																																																																																																																																																																																																																																																																																																																																																									</tr>-->

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Filter Bar', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="month_filter" id="list_month_filter">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  '1' => esc_html__('Yes', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes to show a month filter above the list of events, which allows users to go to next/prev months or to the next 12 months events.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <!--<tr valign="top" class="eventer-custom-admin-screen-background-section">
																																																																																																																																																																																																																																																																																																																																																																																																										<th scope="row"><?php esc_html_e('Event status view', 'eventer'); ?></th>
																																																																																																																																																																																																																																																																																																																																																																																																										<td>
																																																																																																																																																																																																																																																																																																																																																																																																											<select multiple class="eventer_select_val" data-sattr="status" id="list_status">
																																																																																																																																																																																																																																																																																																																																																																																																												<?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                $options = array(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  '' => esc_html__('Future', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'yearly' => esc_html__('Yearly', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'monthly' => esc_html__('Monthly', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'weekly' => esc_html__('Weekly', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'daily' => esc_html__('Daily', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'past' => esc_html__('Past', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                );
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                foreach ($options as $id => $label) { ?>
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														<option value="<?php echo esc_attr($id); ?>">
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																															<?php echo strip_tags($label); ?>
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														</option>
																																																																																																																																																																																																																																																																																																																																																																																																												<?php } ?>
																																																																																																																																																																																																																																																																																																																																																																																																											</select>
																																																																																																																																																																																																																																																																																																																																																																																																											<p class="description"><?php esc_html_e('Select status to show event in list view.', 'eventer'); ?></p>
																																																																																																																																																																																																																																																																																																																																																																																																										</td>
																																																																																																																																																																																																																																																																																																																																																																																																									</tr>-->

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event calendar view', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_select_val" data-sattr="calview" id="list_calview">
                        <?php
                                $options = array(
                                  '' => esc_html__('None', 'eventer'),
                                  'yearly' => esc_html__('Year View', 'eventer'),
                                  'monthly' => esc_html__('Month View', 'eventer'),
                                  'weekly' => esc_html__('Week View', 'eventer'),
                                  'daily' => esc_html__('Day View', 'eventer'),
                                  'today' => esc_html__('Today', 'eventer'),
                                  'date_range' => esc_html__('Date Range', 'eventer'),
                                  //'eventkeys' => esc_html__( 'Keyword search in selected date', 'eventer' ),
                                  //'seventkeys' => esc_html__( 'Keyword search anywhere', 'eventer' ),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the calendar view tabs of the events to show in the list.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event status', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="status" id="list_status">
                        <?php
                                $options = array(
                                  '' => esc_html__('Future', 'eventer'),
                                  'past' => esc_html__('Past', 'eventer'),
                                  'yearly' => esc_html__('Yearly', 'eventer'),
                                  'monthly' => esc_html__('Monthly', 'eventer'),
                                  'weekly' => esc_html__('Weekly', 'eventer'),
                                  'daily' => esc_html__('Daily', 'eventer'),
                                  //'chronological' => esc_html__( 'Chronological', 'eventer' )
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the status of the events to show in the list.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Series', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val eventer-shortcode-series" data-sattr="series" id="list_series">
                      <p class="description"><?php esc_html_e('Select the series of events, this could work with pagination only.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Taxonomy Filters', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_select_val" data-sattr="filters" id="list_filters">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  'category' => esc_html__('Event Categories', 'eventer'),
                                  'tag' => esc_html__('Event Tags', 'eventer'),
                                  'venue' => esc_html__('Event Venue', 'eventer'),
                                  'organizer' => esc_html__('Event Organizer', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('List style', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="view" id="list_view">
                        <?php
                                $options = array(
                                  '' => esc_html__('Compact', 'eventer'),
                                  'minimal' => esc_html__('Minimal', 'eventer'),
                                  'classic' => esc_html__('Classic', 'eventer'),
                                  'native' => esc_html__('Native', 'eventer'),
                                  'detailed' => esc_html__('Detailed', 'eventer'),
                                  'modern' => esc_html__('Modern', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <!--<img id="compact-design" src="<?php echo EVENTER__PLUGIN_URL . 'images/compact.png'; ?>">
																																																																																																																																																																																																																																																																																																																																																																																																											<img id="minimal-design" src="<?php echo EVENTER__PLUGIN_URL . 'images/minimal.png'; ?>">-->
                      <p class="description"><?php esc_html_e('Select style of the list for the events.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Show Venue', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="venue" id="list_venue">
                        <?php
                                $options = array(
                                  '' => esc_html__('Yes (Show full address)', 'eventer'),
                                  'name' => esc_html__('Yes (Show venue name)', 'eventer'),
                                  'no' => esc_html__('No', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes to show event venue address for every event in the list.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Events per page', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="count" id="list_count">
                        <?php
                                $options = array('' => 'Default');
                                for ($i = 1; $i <= 50; $i++) {
                                  $options[$i] = $i;
                                }
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Enter number of events to show per page when event month filter is shown.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Pagination', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="pagination" id="list_pagination">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  'yes' => esc_html__('Yes', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes to show pagination below the events list. This will use events per page option.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><button id="eventer_list" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_list]</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>


          <div id="grid-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php _e('Event Grid Shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <table class="form-table eventer-custom-admin-login-table" id="eventer-grid-area-start">
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Layout', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="layout" id="grid_layout">
                        <?php
                                $options = array(
                                  '' => esc_html__('Default', 'eventer'),
                                  'clean' => esc_html__('Clean', 'eventer'),
                                  'featured' => esc_html__('Featured', 'eventer'),
                                  'hidden' => esc_html__('Featured Hidden', 'eventer'),
                                  'modern' => esc_html__('Modern', 'eventer'),
                                  'products' => esc_html__('Product', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the layout for grid view.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Select Event', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_ids eventer_select_val" data-sattr="ids" id="grid_ids">
                        <?php
                                $options = eventer_get_eventer_list();
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('You can select specific events to show in the grid. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                    </td>
                  </tr>
                  <?php if ($eventer_enable_categories == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Category', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_cats" id="grid_terms_cats">
                          <?php
                                    $options = eventer_get_terms('eventer-category');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event category, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_tags == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Tags', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_tags" id="grid_terms_tags">
                          <?php
                                    $options = eventer_get_terms('eventer-tag');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event tags, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_venue == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Venue', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_venue" id="grid_terms_venues">
                          <?php
                                    $options = eventer_get_terms('eventer-venue');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event venues, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_organizer == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Organizer', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_organizer" id="grid_terms_organizers">
                          <?php
                                    $options = eventer_get_terms('eventer-organizer');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event organisers, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php } ?>
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Type', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="type" id="grid_type">
                        <?php
                                $options = array(
                                  '' => esc_html__('All', 'eventer'),
                                  '1' => esc_html__('WP', 'eventer'),
                                  '2' => esc_html__('Google', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select event type for the grid. You can choose All to show both WordPress and Google Calendar events in the grid or WP/Google to show selected events only.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Featured Events', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="featured" id="grid_featured">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  '1' => esc_html__('Yes', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select yes to show featured events at the top of grid view.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Filter Bar', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="month_filter" id="grid_month_filter">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  '1' => esc_html__('Yes', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes to show a month filter above the list of events, which allows users to go to next/prev months or to the next 12 months events.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <!--<tr valign="top" class="eventer-custom-admin-screen-background-section">
																																																																																																																																																																																																																																																																																																																																																																																																										<th scope="row"><?php esc_html_e('Event status view', 'eventer'); ?></th>
																																																																																																																																																																																																																																																																																																																																																																																																										<td>
																																																																																																																																																																																																																																																																																																																																																																																																											<select multiple class="eventer_select_val" data-sattr="status" id="list_status">
																																																																																																																																																																																																																																																																																																																																																																																																												<?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                $options = array(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  '' => esc_html__('Future', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'yearly' => esc_html__('Yearly', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'monthly' => esc_html__('Monthly', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'weekly' => esc_html__('Weekly', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'daily' => esc_html__('Daily', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'past' => esc_html__('Past', 'eventer'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                );
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                foreach ($options as $id => $label) { ?>
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														<option value="<?php echo esc_attr($id); ?>">
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																															<?php echo strip_tags($label); ?>
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														</option>
																																																																																																																																																																																																																																																																																																																																																																																																												<?php } ?>
																																																																																																																																																																																																																																																																																																																																																																																																											</select>
																																																																																																																																																																																																																																																																																																																																																																																																											<p class="description"><?php esc_html_e('Select status to show event in grid view.', 'eventer'); ?></p>
																																																																																																																																																																																																																																																																																																																																																																																																										</td>
																																																																																																																																																																																																																																																																																																																																																																																																									</tr>-->

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event calendar view', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_select_val" data-sattr="calview" id="grid_calview">
                        <?php
                                $options = array(
                                  '' => esc_html__('None', 'eventer'),
                                  'yearly' => esc_html__('Year View', 'eventer'),
                                  'monthly' => esc_html__('Month View', 'eventer'),
                                  'weekly' => esc_html__('Week View', 'eventer'),
                                  'daily' => esc_html__('Day View', 'eventer'),
                                  'today' => esc_html__('Today', 'eventer'),
                                  'date_range' => esc_html__('Date Range', 'eventer'),
                                  //'eventkeys' => esc_html__( 'Keyword search in selected date', 'eventer' ),
                                  //'seventkeys' => esc_html__( 'Keyword search anywhere', 'eventer' ),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the calendar view tabs of the events to show in the grid, this will not work with the status of future or past.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event status', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="status" id="grid_status">
                        <?php
                                $options = array(
                                  '' => esc_html__('Future', 'eventer'),
                                  'past' => esc_html__('Past', 'eventer'),
                                  'yearly' => esc_html__('Yearly', 'eventer'),
                                  'monthly' => esc_html__('Monthly', 'eventer'),
                                  'weekly' => esc_html__('Weekly', 'eventer'),
                                  'daily' => esc_html__('Daily', 'eventer'),
                                  //'chronological' => esc_html__( 'Chronological', 'eventer' )
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the status of the events to show in the grid.', 'eventer'); ?></p>
                    </td>
                  </tr>
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Taxonomy Filters', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_select_val" data-sattr="filters" id="grid_filters">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  'category' => esc_html__('Event Categories', 'eventer'),
                                  'tag' => esc_html__('Event Tags', 'eventer'),
                                  'venue' => esc_html__('Event Venue', 'eventer'),
                                  'organizer' => esc_html__('Event Organizer', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Series', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val eventer-shortcode-series" data-sattr="series" id="grid_series">
                      <p class="description"><?php esc_html_e('Select the series of events, this could work with pagination only.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Grid Background', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="background" id="grid_background">
                        <?php
                                $options = array(
                                  '' => esc_html__('Default - Featured Image/Category Color/Plain', 'eventer'),
                                  '3' => esc_html__('Plain', 'eventer'),
                                  '1' => esc_html__('Event Category Color', 'eventer'),
                                  '2' => esc_html__('Featured Image', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the background option for the grid items. Default will show featured image if available else Category selected color as background if available else it will be plain white background.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Column', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="column" id="grid_column">
                        <?php
                                $options = array(
                                  '' => esc_html__('Default(Three Columns)', 'eventer'),
                                  '1' => esc_html__('One Column', 'eventer'),
                                  '2' => esc_html__('Two Columns', 'eventer'),
                                  '4' => esc_html__('Four Columns', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select columns for the grid.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Show Venue', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="venue" id="grid_venue">
                        <?php
                                $options = array(
                                  '' => esc_html__('Yes Address', 'eventer'),
                                  'name' => esc_html__('Yes Name', 'eventer'),
                                  'no' => esc_html__('No', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes to show event venue address for every event in the grid.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Per Page', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="count" id="grid_count">
                        <?php
                                $options = array('' => 'Default');
                                for ($i = 1; $i <= 50; $i++) {
                                  $options[$i] = $i;
                                }
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Enter number of events to show per page when event month filter is shown.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Pagination', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="pagination" id="grid_pagination">
                        <?php
                                $options = array(
                                  '' => esc_html__('No', 'eventer'),
                                  'yes' => esc_html__('Yes', 'eventer'),
                                  'carousel' => esc_html__('Carousel', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes to show pagination below the events grid. This will use events per page option.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><input alt="#TB_inline?height=400&amp;width=400&amp;inlineId=eventer_carousel_settings" title="<?php esc_html_e('Carousel Settings', 'eventer'); ?>" class="thickbox" type="button" value="<?php esc_html_e('Add Carousel Settings', 'eventer'); ?>" /></th>
                    <th><button id="eventer_grid" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_grid]</td>
                  </tr>
                </table>
                <div id="eventer_carousel_settings" style="display:none">
                  <div class="postbox">
                    <div class="inside">
                      <table class="form-table eventer-custom-admin-login-table">
                        <tr valign="top" class="eventer-custom-admin-screen-background-section">
                          <th scope="row"><?php esc_html_e('Autoplay', 'eventer'); ?></th>
                          <td>
                            <select class="eventer-carousel-params">
                              <option value="yes"><?php esc_html_e('Yes', 'eventer'); ?></option>
                              <option value="no"><?php esc_html_e('No', 'eventer'); ?></option>
                            </select>
                          </td>
                        </tr>
                        <tr valign="top" class="eventer-custom-admin-screen-background-section">
                          <th scope="row"><?php esc_html_e('Autoplay Timeout', 'eventer'); ?></th>
                          <td>
                            <input class="eventer-carousel-params" value="3000" />
                          </td>
                        </tr>
                        <tr valign="top" class="eveter-custom-admin-screen-background-section">
                          <th scope="row"><?php esc_html_e('Pagination', 'eventer'); ?></th>
                          <td>
                            <select class="eventer-carousel-params">
                              <option value="yes"><?php esc_html_e('Yes', 'eventer'); ?></option>
                              <option value="no"><?php esc_html_e('No', 'eventer'); ?></option>
                            </select>
                          </td>
                        </tr>
                        <tr valign="top" class="eventer-custom-admin-screen-background-section">
                          <th scope="row"><?php esc_html_e('Arrows', 'eventer'); ?></th>
                          <td>
                            <select class="eventer-carousel-params">
                              <option value="yes"><?php esc_html_e('Yes', 'eventer'); ?></option>
                              <option value="no"><?php esc_html_e('No', 'eventer'); ?></option>
                            </select>
                          </td>
                        </tr>
                        <tr valign="top" class="eventer-custom-admin-screen-background-section">
                          <th scope="row"><?php esc_html_e('RTL', 'eventer'); ?></th>
                          <td>
                            <select class="eventer-carousel-params">
                              <option value="no"><?php esc_html_e('No', 'eventer'); ?></option>
                              <option value="yes"><?php esc_html_e('Yes', 'eventer'); ?></option>
                            </select>
                          </td>
                        </tr>
                      </table>
                      <input type="button" class="eventer-add-carousel" value="<?php esc_html_e('Add Carousel Settings', 'eventer'); ?>"></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div id="slider-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php _e('Event Slider Shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <table class="form-table eventer-custom-admin-login-table" id="eventer-slider-area-start">
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Layout', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="layout" id="slider_layout">
                        <?php
                                $options = array(
                                  '' => esc_html__('Type1', 'eventer'),
                                  'type2' => esc_html__('Type2', 'eventer'),
                                  'type3' => esc_html__('Type3', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select the layout for slider view.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Select Event', 'eventer'); ?></th>
                    <td>
                      <select multiple class="eventer_ids eventer_select_val" data-sattr="ids" id="slider_ids">
                        <?php
                                $options = eventer_get_eventer_list();
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('You can select specific events to show in the slider. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                    </td>
                  </tr>
                  <?php if ($eventer_enable_categories == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Category', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_cats" id="slider_terms_cats">
                          <?php
                                    $options = eventer_get_terms('eventer-category');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event category, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_tags == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Tags', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_tags" id="slider_terms_tags">
                          <?php
                                    $options = eventer_get_terms('eventer-tag');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event tags, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_venue == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Venue', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_venue" id="slider_terms_venues">
                          <?php
                                    $options = eventer_get_terms('eventer-venue');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event venues, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_organizer == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Organizer', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="slider_organizer" id="grid_terms_organizers">
                          <?php
                                    $options = eventer_get_terms('eventer-organizer');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event organisers, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php } ?>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Per Page', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="count" id="slider_count">
                        <?php
                                $options = array('' => 'Default');
                                for ($i = 1; $i <= 50; $i++) {
                                  $options[$i] = $i;
                                }
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Enter number of events to show per page when event month filter is shown.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><input alt="#TB_inline?height=400&amp;width=400&amp;inlineId=eventer_carousel_settings" title="<?php esc_html_e('Carousel Settings', 'eventer'); ?>" class="thickbox" type="button" value="<?php esc_html_e('Add Carousel Settings', 'eventer'); ?>" /></th>
                    <th><button id="eventer_slider" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_slider]</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>


          <div id="calendar-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php esc_html_e('Event Calendar Shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <table class="form-table eventer-custom-admin-login-table">
                  <?php if ($eventer_enable_categories == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Category', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_cats" id="calendar_terms_cats">
                          <?php
                                    $options = eventer_get_terms('eventer-category');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event category, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_tags == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Event Tags', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_tags" id="calendar_terms_tags">
                          <?php
                                    $options = eventer_get_terms('eventer-tag');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event tags, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_venue == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Venue', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_venue" id="calendar_terms_venues">
                          <?php
                                    $options = eventer_get_terms('eventer-venue');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event venues, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php }
                          if ($eventer_enable_organizer == 'on') { ?>
                    <tr valign="top" class="eventer-custom-admin-screen-background-section">
                      <th scope="row"><?php esc_html_e('Organizer', 'eventer'); ?></th>
                      <td>
                        <select multiple class="eventer_terms eventer_select_val" data-sattr="terms_organizer" id="calendar_terms_organizers">
                          <?php
                                    $options = eventer_get_terms('eventer-organizer');
                                    foreach ($options as $id => $label) { ?>
                            <option value="<?php echo esc_attr($id); ?>">
                              <?php echo strip_tags($label); ?>
                            </option>
                          <?php } ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select event organisers, from which you want to show events specifically. You can use ctrl/command key to select/deselect multiple values.', 'eventer'); ?></p>
                      </td>
                    </tr>
                  <?php } ?>
                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Type', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="type" id="calendar_type">
                        <?php
                                $options = array(
                                  '' => esc_html__('Both', 'eventer'),
                                  '1' => esc_html__('WP', 'eventer'),
                                  '2' => esc_html__('Google', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select event type for the calendar. You can choose All to show both WordPress and Google Calendar events in the calendar or WP/Google to show selected events only.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Event Preview', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="preview" id="calendar_preview">
                        <?php
                                $options = array(
                                  '' => esc_html__('Yes', 'eventer'),
                                  'no' => esc_html__('No', 'eventer'),
                                );
                                foreach ($options as $id => $label) { ?>
                          <option value="<?php echo esc_attr($id); ?>">
                            <?php echo strip_tags($label); ?>
                          </option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><button id="eventer_calendar" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_calendar]</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>


          <div id="field-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php esc_html_e('Form Fields Shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <p class="description"><?php esc_html_e('These shortcodes are for your use in the event registration booking form and Contact event organizer form available on single event page.', 'eventer'); ?></p>
                <table class="form-table eventer-custom-admin-login-table">

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Select field yype', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_terms eventer_select_val" data-sattr="type" id="form_field_type">
                        <option value="text"><?php esc_html_e('Text', 'eventer'); ?></option>
                        <option value="textarea"><?php esc_html_e('Textarea', 'eventer'); ?></option>
                        <option value="select"><?php esc_html_e('Select dropdown', 'eventer'); ?></option>
                        <option value="checkbox"><?php esc_html_e('Checkbox', 'eventer'); ?></option>
                        <option value="radio"><?php esc_html_e('Radio', 'eventer'); ?></option>
                        <option value="number"><?php esc_html_e('Number', 'eventer'); ?></option>
                        <option value="email"><?php esc_html_e('Email', 'eventer'); ?></option>
                      </select>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Select textarea rows', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_terms eventer_select_val" data-sattr="text_row" id="form_field_text_row">
                        <option value="4"><?php echo number_format_i18n(4); ?></option>
                        <option value="5"><?php echo number_format_i18n(5); ?></option>
                        <option value="6"><?php echo number_format_i18n(6); ?></option>
                        <option value="7"><?php echo number_format_i18n(7); ?></option>
                        <option value="8"><?php echo number_format_i18n(8); ?></option>
                      </select>
                      <p class="description"><?php esc_html_e('Select the rows for the text area field. This is used to define the height of the textarea field in the form.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Mark field mandatory', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_terms eventer_select_val" data-sattr="required" id="form_field_required">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                      </select>
                      <p class="description"><?php esc_html_e('Select Yes if you want the user to fill this field before submitting the form.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Field classes', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="class" id="form_field_class">
                      <p class="description"><?php esc_html_e('Enter any additional class name you wish to add to this field. Add multiple class names with space between them.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Field ID', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="id" id="form_field_id">
                      <p class="description"><?php esc_html_e('Enter a unqiue ID name for this field. ID name must not have any special character or space in between.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Field values', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="param" id="form_field_param">
                      <p class="description"><?php esc_html_e('This field can be used for select/radio/checkbox field types. Insert comma separated multiple values. Wrap value with curly braces to auto select a value, For example:- {abc},def,ghi,{jkl}, now abc & jkl will be auto selected values of the field.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Field name', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="name" id="form_field_name">
                      <p class="description"><?php esc_html_e('Provide a unique name for your field.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Field Label', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="label" id="form_field_label">
                      <p class="description"><?php esc_html_e('Enter label of the field.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Field Meta Key', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="meta_key" id="form_field_metakey">
                      <p class="description"><?php esc_html_e('Enter meta key of custom field.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><button id="eventer_fields" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_fields]</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div id="dashboard-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php esc_html_e('Dashboard shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <p class="description"><?php esc_html_e('Add shortcode for dashboard.', 'eventer'); ?></p>
                <table class="form-table eventer-custom-admin-login-table">

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Active Tab', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="default" id="dashboard_default">
                        <option value="submissions"><?php esc_html_e('Submissions', 'eventer'); ?></option>
                        <option value="bookings"><?php esc_html_e('Bookings', 'eventer'); ?></option>
                        <option value="eventer_add_new"><?php esc_html_e('Add new event', 'eventer'); ?></option>
                      </select>
                      <p class="description"><?php esc_html_e('Select section from the above list which will open by default on visiting the dashboard page.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Add new event page', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="add_new" id="dashboard_add_new">
                        <option value=""><?php esc_html_e('Select', 'eventer'); ?></option>
                        <?php
                                $form_options = get_option('eventer_forms_data');
                                if (!empty($form_options)) {
                                  foreach ($form_options as $key => $value) {
                                    if ($key == 'eventer-default-form-settings') {
                                      continue;
                                    }

                                    $name = (isset($value['name']) && $value['name'] != '') ? $value['name'] : $key;
                                    echo '<option value="' . esc_attr($key) . '">' . esc_attr($name) . '</option>';
                                  }
                                }
                                ?>
                      </select>
                      <p class="description"><?php esc_html_e('Select page from the list of pages(containing add new event form shortcode) to be used as page for Add new event page link in dashboard.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><button id="eventer_dashboard" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_dashboard]</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div id="form-settings" class="ui-sortable meta-box-sortables" style="display: none;">
            <h3><?php esc_html_e('Add event shortcode', 'eventer'); ?></h3>
            <div class="postbox">
              <div class="inside">
                <p class="description"><?php esc_html_e('Add shortcode to add new event through front end.', 'eventer'); ?></p>
                <table class="form-table eventer-custom-admin-login-table">

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Sections', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="sections" id="form_sections">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                      </select>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Name', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="name" id="form_name">
                      <p class="description"><?php esc_html_e('Enter name for form, this will be shown while adding this form in dashboard.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Slug', 'eventer'); ?></th>
                    <td>
                      <input type="text" class="eventer_select_val" data-sattr="id" id="form_id">
                      <p class="description"><?php esc_html_e('Enter slug for form, should be unique for each form.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Default Fields', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="default" id="form_default">
                        <option value="no"><?php esc_html_e('No', 'eventer'); ?></option>
                        <option value="yes"><?php esc_html_e('Yes', 'eventer'); ?></option>
                      </select>
                      <p class="description"><?php esc_html_e('Start form with default fields.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top" class="eventer-custom-admin-screen-background-section">
                    <th scope="row"><?php esc_html_e('Default Status', 'eventer'); ?></th>
                    <td>
                      <select class="eventer_select_val" data-sattr="status" id="form_status">
                        <option value="draft"><?php esc_html_e('Draft', 'eventer'); ?></option>
                        <option value="pending"><?php esc_html_e('Pending', 'eventer'); ?></option>
                        <option value="publish"><?php esc_html_e('Publish', 'eventer'); ?></option>
                      </select>
                      <p class="description"><?php esc_html_e('Set default status for event.', 'eventer'); ?></p>
                    </td>
                  </tr>

                  <tr valign="top">
                    <th><button id="eventer_add_new" class="generate-shortcode button button-primary"> <?php esc_html_e('Generate & Copy Shortcode', 'eventer'); ?></button>
                      <?php
                              echo $preview_area;
                              ?>
                    </th>
                    <td>[eventer_add_new]</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        <?php } ?>
        <?php if ($active_tab == 'bookings') { ?>
          <h3><?php esc_html_e('Bookings', 'eventer'); ?></h3>
          <div id="payments-settings" class="ui-sortable meta-box-sortables">
            <div class="postbox">
              <div class="inside">
                <?php global $wpdb;
                        $table_name = $wpdb->prefix . "eventer_registrant";
                        $booking_status = (isset($_REQUEST['booking_status'])) ? $_REQUEST['booking_status'] : '';
                        $specific_event = (isset($_REQUEST['eventer_id'])) ? $_REQUEST['eventer_id'] : '';
                        $specific_event_date = (isset($_REQUEST['booking'])) ? $_REQUEST['booking'] : '';
                        $booking_search = (isset($_REQUEST['booking_search'])) ? $_REQUEST['booking_search'] : '';
                        $page_num = (isset($_REQUEST['pagenum']) && $_REQUEST['pagenum']) ? absint($_REQUEST['pagenum']) : 1;
                        $limit_result = 20; // Number of rows in page
                        $offset = ($page_num - 1) * $limit_result;
                        $total = $wpdb->get_results("SELECT * FROM $table_name");
                        $num_of_pages = ceil(count($total) / $limit_result);

                        if ($booking_status == '' && $specific_event == '' && $specific_event_date == '' && $booking_search == '') {
                          $reg_details = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ID DESC LIMIT $offset, $limit_result", OBJECT);
                        } elseif ($booking_search != '') {
                          $reg_details = $wpdb->get_results("SELECT * FROM $table_name WHERE `transaction_id` LIKE '%$booking_search%' OR `user_details` LIKE '%$booking_search%' OR `user_system` LIKE '%$booking_search%' ORDER BY ID DESC", OBJECT);
                        } else {
                          $reg_details = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ID DESC", OBJECT);
                        }

                        $all_registered_events = array();
                        $event_arg = array('post_type' => 'eventer', 'posts_per_page' => -1, 'orderby' => 'name', 'order' => 'ASC');
                        $event_list = new WP_Query($event_arg);
                        if ($event_list->have_posts()) : while ($event_list->have_posts()) : $event_list->the_post();
                            $all_registered_events[get_the_ID()] = get_the_title();
                          endwhile;
                        endif;
                        wp_reset_postdata();
                        $reg_all = $wpdb->get_results("SELECT * FROM $table_name");
                        $reg_completed = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'completed'");
                        $reg_pending = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'pending'");
                        $page_links = paginate_links(array(
                          'base' => add_query_arg('pagenum', '%#%'),
                          'format' => '',
                          'prev_text' => __('&laquo;', 'eventer'),
                          'next_text' => __('&raquo;', 'eventer'),
                          'total' => $num_of_pages,
                          'current' => $page_num,
                        ));

                        if (!empty($reg_details)) { ?>
                  <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
                    <input type="hidden" name="action" value="eventer_export_registrants">
                    <input type="hidden" name="date" value="<?php echo (isset($_REQUEST['booking'])) ? $_REQUEST['booking'] : ''; ?>">
                    <input type="hidden" name="status" value="<?php echo (isset($_REQUEST['booking_status'])) ? $_REQUEST['booking_status'] : ''; ?>">
                    <input type="hidden" name="eventer" value="<?php echo (isset($_REQUEST['eventer_id'])) ? $_REQUEST['eventer_id'] : ''; ?>">
                    <input type="hidden" name="eventer_all" value="<?php echo (isset($_REQUEST['eventer_id'])) ? $_REQUEST['eventer_id'] : ''; ?>">
                    <input type="submit" value="<?php esc_html_e('Download csv', 'eventer'); ?>" class="button">
                    <input type="button" value="<?php esc_html_e('Delete selected', 'eventer'); ?>" class="button eventer-delete-bulk-booking" style="color:red;">
                  </form>
                <?php } ?>
                <ul class="subsubsub">
                  <li class="all">
                    <a href="<?php echo add_query_arg('booking_status', '', 'edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=bookings'); ?>" class="current">
                      <?php _e('All'); ?>
                      <span class="count">(<?php echo count($reg_all); ?>)</span>
                    </a> |
                  </li>
                  <li class="publish">
                    <a href="<?php echo add_query_arg('booking_status', 'completed', 'edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=bookings'); ?>">
                      <?php _e('Completed'); ?>
                      <span class="count">(<?php echo count($reg_completed); ?>)</span>
                    </a>
                  </li>
                  <li class="publish">
                    <a href="<?php echo add_query_arg('booking_status', 'pending', 'edit.php?post_type=eventer&page=eventer_settings_options&amp;tab=bookings'); ?>">
                      <?php _e('Pending'); ?>
                      <span class="count">(<?php echo count($reg_pending); ?>)</span>
                    </a>
                  </li>
                </ul>

                <form action="<?php echo esc_url(admin_url('edit.php')); ?>" method="get">
                  <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php esc_html_e('Search', 'eventer'); ?>:</label>
                    <input name="booking_search" placeholder="<?php esc_html_e('Search here', 'eventer'); ?>" value="" type="search">
                    <input id="search-submit" class="button" value="<?php esc_html_e('Search', 'eventer'); ?>" type="submit"></p>
                  <input type="hidden" name="post_type" value="eventer">
                  <input type="hidden" name="page" value="eventer_settings_options">
                  <input type="hidden" name="tab" value="bookings">
                  <div class="tablenav top">
                    <div class="alignleft actions">
                      <label class="screen-reader-text" for="cat"><?php esc_html_e('Filter by event', 'eventer'); ?></label>
                      <select name="eventer_id" class="postform">
                        <option value=""><?php esc_html_e('All Events', 'eventer'); ?></option>
                        <?php
                                $order_recording_switch = eventer_get_settings('eventer_woo_orders');
                                $woocommerce_ticketing = eventer_get_settings('eventer_enable_woocommerce_ticketing');
                                foreach ($all_registered_events as $key => $value) {
                                  $selected = ($specific_event == $key) ? 'selected' : '';
                                  echo '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
                                }
                                $event_booking_date = (isset($_REQUEST['booking'])) ? $_REQUEST['booking'] : '';
                                ?>
                      </select>
                      <input type="text" class="eventer-bookings-date" name="booking" value="<?php echo $event_booking_date; ?>" placeholder="<?php esc_html_e('Select Date', 'eventer'); ?>">
                      <input type="submit" class="button" value="Filter"> </div>

                    <?php if ($page_links && !isset($_REQUEST['eventer_id'])) {
                              echo '<div class="tablenav-pages alignright" style="margin: 1em 0">' . $page_links . '</div>';
                            } ?>
                    <br class="clear">
                  </div>
                </form>

                <table class="wp-list-table widefat eventer-bookings">
                  <thead>
                    <tr>
                      <th></th>
                      <th><?php _e('ID', 'eventer'); ?></th>
                      <th><?php _e('Event', 'eventer'); ?></th>
                      <th><?php _e('Date', 'eventer'); ?></th>
                      <th><?php _e('Transaction Id', 'eventer'); ?></th>
                      <th><?php _e('Status', 'eventer'); ?></th>
                      <th><?php _e('Amount', 'eventer'); ?></th>
                      <th><?php _e('Mode', 'eventer'); ?></th>
                      <th><?php _e('Actions', 'eventer'); ?></th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th><?php _e('ID', 'eventer'); ?></td>
                      <th><?php _e('Event', 'eventer'); ?></td>
                      <th><?php _e('Date', 'eventer'); ?></td>
                      <th><?php _e('Transaction Id', 'eventer'); ?></td>
                      <th><?php _e('Status', 'eventer'); ?></th>
                      <th><?php _e('Amount', 'eventer'); ?></th>
                      <th><?php _e('Mode', 'eventer'); ?></th>
                      <th><?php _e('Actions', 'eventer'); ?></th>
                    </tr>
                  </tfoot>
                  <tbody>

                    <?php
                            if (!empty($reg_details)) {
                              $start_row = 1;

                              foreach ($reg_details as $registrant) {
                                $continue = 0;
                                if ($booking_status != '' && $registrant->status != $booking_status) {
                                  continue;
                                }
                                if ($specific_event != '' && $specific_event != $registrant->eventer && $woocommerce_ticketing != 'on') continue;
                                if ($specific_event_date != '' && $specific_event_date != $registrant->eventer_date && $woocommerce_ticketing != 'on') continue;
                                $user_system = unserialize($registrant->user_system);
                                if (isset($user_system['events'])) {
                                  $firstKey = array_key_first($user_system['events']);
                                  $event_date = $user_system['events'][$firstKey];
                                  if ($woocommerce_ticketing == 'on' && $specific_event_date != '' && $event_date != $specific_event_date) continue;
                                }
                                if ($specific_event != '' && $woocommerce_ticketing == 'on' && get_post_type($registrant->eventer) == 'shop_order') {
                                  $woo_order = $registrant->eventer;
                                  $order = wc_get_order($woo_order);
                                  foreach ($order->get_items() as $item_key => $item_values) :
                                    $item_id = $item_values->get_id();
                                    $eventer_id = wc_get_order_item_meta($item_id, '_wceventer_id', true);
                                    $eventer_date = wc_get_order_item_meta($item_id, '_wceventer_date', true);
                                    $eventer_date = ($specific_event_date != '') ? $eventer_date : '';
                                    if ($specific_event == $eventer_id && $specific_event_date == $eventer_date) {
                                      break;
                                    } else {
                                      $continue = 10;
                                      break;
                                    }
                                  endforeach;
                                } elseif ($specific_event != '' && $woocommerce_ticketing != 'on' && get_post_type($registrant->eventer) != 'shop_order') {
                                  //continue;
                                } elseif ($specific_event != '') {
                                  continue;
                                }
                                if ($continue > 0) {
                                  continue;
                                }

                                $row = 'woo';
                                $allow_download = (isset($_REQUEST['allow']) && $_REQUEST['allow'] == $registrant->id) ? 1 : 0;

                                $all_tickets_details = (isset($user_system['tickets'])) ? $user_system['tickets'] : array();

                                $all_tickets_path = '';
                                if (isset($user_system['tickets_created']) && !empty($user_system['tickets_created'])) {
                                  $count = 1;
                                  foreach ($user_system['tickets_created'] as $key => $value) {
                                    $separator = (count($user_system['tickets_created']) > $count) ? ',' : '';
                                    $all_tickets_path .= $value . $separator;
                                    $count++;
                                  }
                                }
                                if (get_post_type($registrant->eventer) == 'eventer') {
                                  $row = 'eventer';
                                  $services_normal = (isset($user_system['services']) && !empty($user_system['services'])) ? $user_system['services'] : '';

                                  $create_dynamic_reg = eventer_encode_security_registration($registrant->id, 8, 6);
                                  $event_link = get_permalink($registrant->eventer);
                                  $dated_event_url = eventer_generate_endpoint_url('edate', $registrant->eventer_date, $event_link);
                                  $calculated_event_url = add_query_arg(array('reg' => $create_dynamic_reg, 'recreate' => 1001, 'backorder' => '1'), $dated_event_url);
                                  $order_received_URL = $calculated_event_url;
                                  $tickets_normal = unserialize($registrant->tickets);
                                  $registrants_normal = $user_system['registrants'];
                                  if ($tickets_normal) {
                                    foreach ($tickets_normal as $normal) {
                                      if ($normal['number'] <= 0) {
                                        continue;
                                      }

                                      $new_section_normal = array();
                                      $new_section_normal['event'] = $registrant->eventer;
                                      $new_section_normal['date'] = strtotime($registrant->eventer_date);
                                      $new_section_normal['type'] = 'ticket';
                                      $new_section_normal['checkin'] = (isset($user_system['checkin']) && $user_system['checkin'] == '1') ? 'Yes-' . $user_system['checkin_date'] : '';
                                      $new_section_normal['ticket'] = $normal['name'];
                                      $new_section_normal['quantity'] = $normal['number'];
                                      if (isset($registrants_normal[$normal['name']])) {
                                        $new_section_normal['registrants'] = $registrants_normal[$normal['name']];
                                      }
                                      $all_tickets_details[] = $new_section_normal;
                                    }
                                  }
                                  if ($services_normal) {
                                    foreach ($services_normal as $serve) {
                                      if (isset($serve['value']) && !empty($serve['value'])) {
                                        $new_section_normal = array();
                                        $new_section_normal['event'] = $registrant->eventer;
                                        $new_section_normal['date'] = strtotime($registrant->eventer_date);
                                        $new_section_normal['type'] = 'service';
                                        $new_section_normal['ticket'] = $serve['name'];
                                        $new_section_normal['quantity'] = $serve['value'];
                                        $new_section_normal['registrants'] = '';
                                        $all_tickets_details[] = $new_section_normal;
                                      }
                                    }
                                  }
                                } elseif (function_exists('wc_get_order')) {
                                  $order_received_URL = eventer_woo_get_return_url(wc_get_order($registrant->eventer));
                                  $order_received_URL = add_query_arg('backorder', '1', $order_received_URL);
                                }
                                $row_class = ($start_row % 2 != '') ? 'alternate' : '';
                                $row_title = (get_post_type($registrant->eventer) == 'eventer') ? get_the_title($registrant->eventer) . '-' . date_i18n(get_option('date_format'), strtotime($registrant->eventer_date)) : $registrant->eventer . ' ' . get_the_title($registrant->eventer);
                                $time_slot = (isset($user_system['time_slot']) && $user_system['time_slot'] != '00:00:00') ? ' - ' . date_i18n(get_option('time_format'), strtotime($user_system['time_slot'])) : ''; ?>

                        <tr class="eventer-admin-registrant-details" id="registrant-<?php echo esc_attr($registrant->id); ?>" class="eventer-registrant-details-trigger <?php echo esc_attr($row_class); ?>">
                          <td><input type="checkbox" value="<?php echo $registrant->id; ?>" class="eventer-remove-bult-bookings" /></td>
                          <td><?php echo esc_attr($registrant->id . '-' . $registrant->eventer); ?></td>
                          <td><?php echo $row_title; ?></td>
                          <td><?php echo esc_attr(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($registrant->ctime))) . $time_slot; ?></td>
                          <td><?php echo esc_attr($registrant->transaction_id); ?></td>
                          <td><?php echo esc_attr($registrant->status); ?></td>
                          <td><?php echo esc_attr($registrant->amount); ?></td>
                          <td><?php echo esc_attr($registrant->paymentmode); ?></td>
                          <td align="center"><a title="<?php esc_html_e('Remove', 'eventer'); ?>" class="remove-reg" data-reg="<?php echo esc_attr($registrant->id); ?>" data-regemail="<?php echo esc_attr($registrant->email); ?>">
                              <div class="dashicons-before dashicons-no"></div>
                            </a>
                          </td>

                        </tr>
                        <tr class="eventer_woo_expandable" id="registrant-details-<?php echo esc_attr($registrant->id); ?>">
                          <?php //if(get_post_type($registrant->eventer)!='eventer') {
                                      ?>
                          <td colspan="8">
                            <table style="width:100%">
                              <thead>
                                <tr>
                                  <th style="font-weight: bold;"><?php esc_html_e('Type', 'eventer'); ?></th>
                                  <th style="font-weight: bold;"><?php esc_html_e('Name', 'eventer'); ?></th>
                                  <th style="font-weight: bold;"><?php esc_html_e('Quantity', 'eventer'); ?></th>
                                  <th style="font-weight: bold;"><?php esc_html_e('Event', 'eventer'); ?></th>
                                  <th style="font-weight: bold;"><?php esc_html_e('Name', 'eventer'); ?></th>
                                  <th style="font-weight: bold;"><?php esc_html_e('Email', 'eventer'); ?></th>
                                  <th style="font-weight: bold;"><?php esc_html_e('Check-in', 'eventer'); ?></th>
                                </tr>
                              </thead>
                              <?php
                                          if (!empty($all_tickets_details)) {
                                            foreach ($all_tickets_details as $ticket_detail) {
                                              $product_type = $ticket_detail['type'];
                                              $product = $ticket_detail['ticket'];
                                              $checkin_info = (isset($ticket_detail['checkin'])) ? $ticket_detail['checkin'] : '';
                                              $checkin_info = ($checkin_info == '1') ? 'Yes-' . $ticket_detail['checkin_date'] : $checkin_info;
                                              $quantity = $ticket_detail['quantity'];
                                              $event_name = get_the_title($ticket_detail['event']);
                                              $event_date = date_i18n(get_option('date_format'), $ticket_detail['date']);
                                              $registrants_ticket_wise = (isset($ticket_detail['registrants']) && !empty($ticket_detail['registrants'])) ? $ticket_detail['registrants'] : array(array('name' => $registrant->username, 'email' => $registrant->email));
                                              $registrant_name = $registrant_email = '';

                                              echo '<tr>
																		<td>' . $product_type . '</td>
																		<td>' . $product . '</td>
																		<td>' . $quantity . '</td>
																		<td>' . $event_name . '-' . $event_date . $time_slot . '</td>';
                                              foreach ($registrants_ticket_wise as $registrant_each) {
                                                $registrant_name .= $registrant_each['name'] . '<br/>';
                                                $registrant_email .= $registrant_each['email'] . '<br/>';
                                              }
                                              echo '<td>' . $registrant_name . '</td>';
                                              echo '<td>' . $registrant_email . '</td>';
                                              echo '<td>' . $checkin_info . '</td>';
                                              echo '</tr>';
                                            }
                                          }
                                          ?>
                              <tfoot>
                                <tr>
                                  <td>
                                    <?php
                                                if ($woocommerce_ticketing != 'on') {
                                                  ?>
                                      <a target="_blank" title="<?php esc_html_e('More Info', 'eventer'); ?>" href="<?php echo esc_url(get_admin_url() . 'admin.php?page=eventer-booking-info&registrant=' . $registrant->id); ?>"><span class="dashicons dashicons-info"></span>
                                      </a>
                                    <?php } ?>
                                  </td>
                                  <td>
                                    <?php if ($row == 'woo') { ?>
                                      <a target="_blank" title="<?php esc_html_e('View Order', 'eventer'); ?>" href="<?php echo esc_url(get_edit_post_link($registrant->eventer)); ?>"><span class="dashicons dashicons-admin-links"></span>
                                      </a>
                                    <?php } else { ?>

                                    <?php } ?>
                                  </td>
                                  <td>
                                    <a title="<?php esc_html_e('Create Tickets', 'eventer'); ?>" href="<?php echo esc_url($order_received_URL); ?>"><span class="dashicons dashicons-paperclip"></span>
                                    </a>
                                  </td>
                                  <td>
                                    <?php if ($allow_download == 1) { ?>
                                      <a data-registrantid="<?php echo esc_attr($registrant->id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('eventer_send_tickets_again')); ?>" title="<?php esc_html_e('Send Tickets', 'eventer'); ?>" href="javascript:void(0);" class="eventer-send-tickets-again"><span class="dashicons dashicons-email"></span>
                                      </a>
                                    <?php } ?>
                                  </td>
                                  <td>
                                    <?php if ($allow_download == 1) {
                                                  echo '<form action="' . esc_url(admin_url('admin-ajax.php')) . '" method="post" class="eventer-show-download-tickets-form" style="">';
                                                  echo '<input type="hidden" name="action" value="eventer_woo_download_tickets">';
                                                  echo '<input type="hidden" class="eventer-woo-tickets" name="tickets" value="' . esc_attr($all_tickets_path) . '">';
                                                  echo '<input type="hidden" name="captcha" value="' . wp_create_nonce('eventer-tickets-download') . '">';
                                                  echo '<a class="eventer-admin-woo-download-tickets-action" title="' . esc_html__('Download Tickets', 'eventer') . '"><span class="dashicons dashicons-download"></span></a></form>';
                                                } ?>
                                  </td>
                                  <td></td>
                                  <td>
                                    <?php if ($row == 'eventer') { ?>
                                      <select name="booking_status">
                                        <option <?php echo ($registrant->status == "completed") ? 'selected' : ''; ?> value="completed"><?php esc_html_e('Completed', 'eventer'); ?></option>
                                        <option <?php echo ($registrant->status == "Pending" || $registrant->status == "pending") ? 'selected' : ''; ?> value="Pending"><?php esc_html_e('Pending', 'eventer'); ?></option>
                                        <option <?php echo ($registrant->status == "Failed") ? 'selected' : ''; ?> value="Failed"><?php esc_html_e('Failed', 'eventer'); ?></option>
                                        <option <?php echo ($registrant->status == "Cancelled") ? 'selected' : ''; ?> value="Cancelled"><?php esc_html_e('Cancelled', 'eventer'); ?></option>
                                      </select>
                                      <?php wp_create_nonce('eventer_update_registrant_status'); ?>
                                      <button data-registrantid="<?php echo esc_attr($registrant->id); ?>" data-nonce="<?php echo wp_create_nonce('eventer_update_registrant_status'); ?>" type="button" class="button button-primary update_booking_status"><?php esc_html_e('Update', 'eventer'); ?></button>
                                    <?php } ?>
                                  </td>
                                </tr>
                              </tfoot>
                            </table>
                          </td>
                      <?php //}
                                }
                              } else {
                                echo '<tr><td>';
                                esc_html_e('There is no booking record available for your events as of now.', 'eventer');
                                echo '</td></tr>';
                              } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php } ?>
        <?php if ($active_tab == 'checkin') { ?>
          <h3><?php esc_attr_e('Ticket check-in', 'eventer'); ?></h3>
          <div id="checkin-settings" class="">
            <div class="postbox">
              <div class="inside">
                <div id="dashboard-widgets-wrap">
                  <div id="dashboard-widgets" class="metabox-holder">
                    <div id="" class="meta-box-sortables">
                      <div id="postbox-container-1" class="postbox-container">
                        <div id="" class="postbox ">
                          <h2 class="hndle ui-sortable-handle">
                            <span>
                              Checkin
                            </span>
                          </h2>
                          <div class="inside">
                            <div class="main">
                              <form>
                                <ul class="form-style-1">
                                  <li>
                                    <label><?php esc_html_e('Select event', 'eventer'); ?>
                                      <span class="required">*</span>
                                    </label>
                                    <select name="eventer-checkin-event" class="eventer-checkin-event field-select">
                                      <option value=""><?php esc_html_e('Select event', 'eventer'); ?></option>
                                      <?php
                                              $eventer_args = array('post_type' => 'eventer', 'posts_per_page' => -1);
                                              $eventer_list = new WP_Query($eventer_args);
                                              if ($eventer_list->have_posts()) : while ($eventer_list->have_posts()) : $eventer_list->the_post();
                                                  echo '<option value="' . esc_attr(get_the_ID()) . '">' . get_the_title() . '</option>';
                                                endwhile;
                                              endif;
                                              wp_reset_postdata();
                                              ?>
                                    </select>
                                  </li>
                                  <li>
                                    <label><?php esc_html_e('Select Date', 'eventer'); ?>
                                      <span class="required">*</span>
                                    </label>
                                    <input type="text" class="eventer-checkin-select-date field-long" />
                                  </li>
                                  <li>
                                    <label><?php esc_html_e('Scan here', 'eventer'); ?>
                                      <span class="required">*</span>
                                    </label>
                                    <input type="text" class="eventer-checkin-scan-here field-long" />
                                  </li>

                                  <li>
                                    <button type="button"><?php esc_html_e('Check-in', 'eventer'); ?></button>
                                  </li>
                                  <li class="eventer-checkin-info-message"></li>
                                </ul>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div id="postbox-container-2" class="postbox-container">
                        <div id="" class="postbox ">
                          <h2 class="hndle ui-sortable-handle">
                            <span>
                              Ticket Details
                            </span>
                          </h2>
                          <div class="inside">
                            <div class="main">

                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        <?php }
              if ($active_tab == 'import') { ?>
          <h3><?php esc_attr__('Import Events', 'eventer'); ?></h3>
          <div id="payments-settings" class="ui-sortable meta-box-sortables">
            <div class="postbox">
              <div class="inside">
                <?php echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
                        echo '<h2>' . esc_attr__('Import CSV file for Events', 'eventer') . '</h2>';
                        echo '</div>';

                        if (isset($_POST['save_values'])) {
                          $eventer_csv_data = array();
                          for ($i = 0; $i < count($_POST['csv_label']); $i++) {
                            if ($_POST['csv_label'][$i] != '') {
                              $eventer_csv_data[$_POST['eventer_csv_values'][$i]] = $_POST['csv_label'][$i];
                            }
                          }
                          update_option('eventer_csv_labels', $eventer_csv_data);
                          update_option('eventer_csv_attachment', $_POST['attachment-btn']);
                        }
                        if (isset($_POST['eventer_import_csv'])) {
                          $saved_options = get_option('eventer_csv_labels');
                          $file = $_FILES['upload_csv']['tmp_name'];
                          $array = $fields = array();
                          $i = 0;
                          $handle = @fopen($file, "r");
                          if ($handle) {
                            while (($row = fgetcsv($handle, 4096)) !== false) {
                              if (empty($fields)) {
                                $fields = $row;
                                continue;
                              }
                              foreach ($row as $k => $value) {
                                $array[$i][$fields[$k]] = $value;
                              }
                              $i++;
                            }
                            $save_val_ids = array();
                            foreach ($array as $single_array) {
                              $title = $status = $content = $sdate = $edate = $eventer_venue = $venue_location = $organizer_name = $organizer_email = $organizer_website = $featured_image = $eventer_category = $eventer_tags = $all_eventers = $featured_image_later = $organizer_phone = '';
                              $status = "draft";
                              $eventer_csv_data = $urls = array();
                              $content = '';
                              $i = 0;
                              foreach ($saved_options as $key => $value) {
                                $featured_image = ($key == 'featured_image' && isset($single_array[$value])) ? $single_array[$value] : $featured_image;
                                $featured_image_later = ($key == 'featured_image_later' && isset($single_array[$value])) ? $single_array[$value] : $featured_image_later;
                                $eventer_category = ($key == 'eventer_category' && isset($single_array[$value])) ? $single_array[$value] : $eventer_category;
                                $eventer_venue = ($key == 'eventer_venue' && isset($single_array[$value])) ? $single_array[$value] : $eventer_venue;
                                $organizer_name = ($key == 'organizer_name' && isset($single_array[$value])) ? $single_array[$value] : $organizer_name;
                                $eventer_tags = ($key == 'eventer_tags' && isset($single_array[$value])) ? $single_array[$value] : $eventer_tags;
                                $status = ($key == 'status' && isset($single_array[$value])) ? esc_attr($single_array[$value]) : esc_attr($status);
                                $all_eventers .= ($key == 'all_eventers' && isset($single_array[$value])) ? esc_attr($single_array[$value]) : esc_attr($all_eventers);
                                $organizer_phone = ($key == 'organizer_phone' && isset($single_array[$value])) ? esc_attr($single_array[$value]) : esc_attr($organizer_phone);
                                if ($key == 'featured_image' || $key == 'featured_image_later' || $key == 'eventer_category' || $key == 'eventer_venue' || $key == 'organizer_name' || $key == 'eventer_tags' || $key == 'status' || $key == 'all_eventers' || $key == 'organizer_phone') {
                                  continue;
                                }
                                $vals = explode(',', $value);
                                foreach ($vals as $newval) { //echo $single_array[$newval];
                                  switch ($key) {
                                    case 'title':
                                      $title .= (isset($single_array[$newval])) ? $single_array[$newval] . ' ' : $newval . ' ';
                                      break;
                                    case 'content':
                                      $content .= (isset($single_array[$newval])) ? $single_array[$newval] . ' ' : $newval . ' ';
                                      break;
                                    case 'sdate':
                                      $sdate_exploded = explode(' ', $newval);
                                      foreach ($sdate_exploded as $sd) {
                                        $sdate .= (isset($single_array[$sd])) ? $single_array[$sd] . ' ' : $sd . ' ';
                                      }
                                      break;
                                    case 'edate':
                                      $sdate_exploded = explode(' ', $newval);
                                      foreach ($sdate_exploded as $sd) {
                                        $edate .= (isset($single_array[$sd])) ? $single_array[$sd] . ' ' : $sd . ' ';
                                      }
                                      break;
                                    case 'venue_location':
                                      $venue_location .= (isset($single_array[$newval])) ? $single_array[$newval] . ' ' : $newval . ' ';
                                      break;
                                    case 'organizer_email':
                                      $organizer_email .= (isset($single_array[$newval])) ? $single_array[$newval] . ' ' : $newval . ' ';
                                      break;
                                    case 'organizer_website':
                                      $organizer_website .= (isset($single_array[$newval])) ? $single_array[$newval] . ' ' : $newval . ' ';
                                      break;
                                  }
                                }
                              }
                              $eventer_post = array(
                                'post_title' => $title,
                                'post_status' => $status,
                                'post_type' => 'eventer',
                                'post_content' => $content,
                              );
                              // Insert the specification into the database
                              $eventer_id = wp_insert_post($eventer_post);
                              if (!empty($eventer_category)) {
                                wp_set_object_terms($eventer_id, $eventer_category, 'eventer-category');
                              }
                              if (!empty($eventer_tags)) {
                                wp_set_object_terms($eventer_id, $eventer_tags, 'eventer-tag');
                              }
                              if (!empty($eventer_venue)) {
                                $venue_id = wp_set_object_terms($eventer_id, $eventer_venue, 'eventer-venue');
                                if (!empty($venue_id) && is_array($venue_id) && !empty($venue_location)) {
                                  foreach ($venue_id as $venue) {
                                    update_term_meta($venue, 'venue_address', $venue_location);
                                  }
                                }
                              }
                              if (!empty($organizer_name)) {
                                $organizer_id = wp_set_object_terms($eventer_id, $organizer_name, 'eventer-organizer');
                                if (!empty($organizer_id) && is_array($organizer_id)) {
                                  foreach ($organizer_id as $organizer) {
                                    update_term_meta($organizer, 'organizer_phone', $organizer_phone);
                                    update_term_meta($organizer, 'organizer_email', $organizer_email);
                                    update_term_meta($organizer, 'organizer_website', $organizer_website);
                                  }
                                }
                              }
                              if ($sdate != '' || $edate != '') {
                                $sdate = str_replace('/', '-', $sdate);
                                $edate = str_replace('/', '-', $edate);
                                $sdate = strtotime($sdate);
                                $sdate = date_i18n('Y-m-d H:i', $sdate);
                                $edate = ($edate == '') ? $sdate : $edate;
                                $edate = strtotime($edate);
                                $edate = date_i18n('Y-m-d H:i', $edate);
                                update_post_meta($eventer_id, 'eventer_event_start_dt', $sdate);
                                $st_date = strtotime($sdate);
                                $start_date_only = date_i18n('Y-m-d', $st_date);
                                update_post_meta($eventer_id, 'eventer_all_dates', array($start_date_only));
                                if (!empty($all_eventers)) {
                                  $all_dates = explode(',', $all_eventers);
                                  $first_date = get_post_meta($eventer_id, 'eventer_all_dates', true);
                                  $full_dates = array_merge($first_date, $all_dates);
                                  $full_dates = array_unique($full_dates);
                                  update_post_meta($eventer_id, 'eventer_all_dates', $full_dates);
                                }
                                update_post_meta($eventer_id, 'eventer_event_end_dt', $edate);
                                update_post_meta($eventer_id, 'eventer_featured_image_later', $featured_image_later);
                              }
                              if (get_option('eventer_csv_attachment') == "1") {
                                if (!empty($featured_image)) {
                                  $urls = explode(',', $featured_image);
                                  $counter = 1;
                                  foreach ($urls as $url) {
                                    $newurl = esc_url($url);
                                    $attachment_id = media_sideload_image($newurl, $eventer_id, '', 'id');
                                    if (!is_wp_error($attachment_id)) {
                                      set_post_thumbnail($eventer_id, $attachment_id);
                                    }
                                  }
                                }
                              }
                            }
                            if (!feof($handle)) {
                              echo "Error: unexpected fgets() fail\n";
                            }
                            fclose($handle);
                            esc_html_e('CSV file processed successfully', 'eventer');
                          }
                        } ?>

                  <!--<p>Please add specification for csv labels, please upload minimum csv files as per server load.</p>
            																																																																																																																																																																																																																																																																																																																																																																																																			<p>Select unique field of csv file, so that it could create only new listings.</p>-->
                  <form name="add_values_csv" method="post">
                    <div>
                      <p><label><?php esc_html_e('Upload images with data?', 'eventer'); ?></label>
                        <p>
                          <label><?php esc_html_e('Yes', 'eventer'); ?></label><input <?php echo (get_option('eventer_csv_attachment') == 1) ? 'checked' : ''; ?> type="radio" name="attachment-btn" value="1">
                          <label><?php esc_html_e('No', 'eventer'); ?></label><input <?php echo (get_option('eventer_csv_attachment') == 0) ? 'checked' : ''; ?> type="radio" name="attachment-btn" value="0">
                        </p>
                    </div>
                    <p><strong><?php esc_html_e('CSV Values', 'eventer'); ?></strong></p>
                    <div id="field_wrap">
                      <?php
                              $csv_vals = get_option('eventer_csv_labels');
                              if (!empty($csv_vals)) {
                                foreach ($csv_vals as $key => $value) {
                                  if (!empty($value[0])) { ?>
                            <div><label><?php esc_html_e('CSV Label', 'eventer'); ?></label>
                              <input type="text" name="csv_label[]" value="<?php echo esc_attr($value); ?>">
                              <label><?php esc_html_e('Select meta field for this label', 'eventer'); ?></label>
                              <select name="eventer_csv_values[]">
                                <option value="0">Select</option>
                                <option <?php echo ($key == "title") ? 'selected="selected"' : ''; ?> value="title"><?php esc_html_e('Title', 'eventer'); ?></option>
                                <option <?php echo ($key == "status") ? 'selected="selected"' : ''; ?> value="status"><?php esc_html_e('Status', 'eventer'); ?></option>
                                <option <?php echo ($key == "content") ? 'selected="selected"' : ''; ?> value="content"><?php esc_html_e('Content', 'eventer'); ?></option>
                                <option <?php echo ($key == "sdate") ? 'selected="selected"' : ''; ?> value="sdate"><?php esc_html_e('Event Start Date and Time', 'eventer'); ?></option>
                                <option <?php echo ($key == "edate") ? 'selected="selected"' : ''; ?> value="edate"><?php esc_html_e('Event End Date and Time', 'eventer'); ?></option>
                                <option <?php echo ($key == "eventer_venue") ? 'selected="selected"' : ''; ?> value="eventer_venue"><?php esc_html_e('Venue Title', 'eventer'); ?></option>
                                <option <?php echo ($key == "venue_location") ? 'selected="selected"' : ''; ?> value="venue_location"><?php esc_html_e('Venue Location', 'eventer'); ?></option>
                                <option <?php echo ($key == "organizer_name") ? 'selected="selected"' : ''; ?> value="organizer_name"><?php esc_html_e('Organizer Name', 'eventer'); ?></option>
                                <option <?php echo ($key == "organizer_phone") ? 'selected="selected"' : ''; ?> value="organizer_phone"><?php esc_html_e('Organizer Phone', 'eventer'); ?></option>
                                <option <?php echo ($key == "organizer_email") ? 'selected="selected"' : ''; ?> value="organizer_email"><?php esc_html_e('Organizer Email', 'eventer'); ?></option>
                                <option <?php echo ($key == "organizer_website") ? 'selected="selected"' : ''; ?> value="organizer_website"><?php esc_html_e('Organizer Website', 'eventer'); ?></option>
                                <option <?php echo ($key == "featured_image") ? 'selected="selected"' : ''; ?> value="featured_image"><?php esc_html_e('Featured Image', 'eventer'); ?></option>
                                <option <?php echo ($key == "featured_image_later") ? 'selected="selected"' : ''; ?> value="featured_image_later"><?php esc_html_e('Set Featured Image Later(Fast Import)', 'eventer'); ?></option>
                                <option <?php echo ($key == "eventer_category") ? 'selected="selected"' : ''; ?> value="eventer_category"><?php esc_html_e('Event Category', 'eventer'); ?></option>
                                <option <?php echo ($key == "eventer_tags") ? 'selected="selected"' : ''; ?> value="eventer_tags"><?php esc_html_e('Event Tags', 'eventer'); ?></option>
                                <option <?php echo ($key == "all_eventers") ? 'selected="selected"' : ''; ?> value="all_eventers"><?php esc_html_e('All Events Date', 'eventer'); ?></option>
                              </select>
                              <input class="button remove_import_field" type="button" value="<?php _e('Remove', 'eventer'); ?>" /></div>
                      <?php
                                  }
                                }
                              }
                              ?>
                    </div>
                    <div id="master-row" style="display:none;">

                      <div>
                        <label><?php esc_html_e('CSV Labels', 'eventer'); ?></label>
                        <input type="text" name="csv_label[]" value="">
                        <label><?php esc_html_e('Select Field for this label', 'eventer'); ?></label>
                        <select name="eventer_csv_values[]">
                          <option value="0"><?php esc_html_e('Select', 'eventer'); ?></option>
                          <option value="title"><?php esc_html_e('Title', 'eventer'); ?></option>
                          <option value="status"><?php esc_html_e('Status', 'eventer'); ?></option>
                          <option value="content"><?php esc_html_e('Content', 'eventer'); ?></option>
                          <option value="sdate"><?php esc_html_e('Event Start Date and Time', 'eventer'); ?></option>
                          <option value="edate"><?php esc_html_e('Event End Date and Time', 'eventer'); ?></option>
                          <option value="eventer_venue"><?php esc_html_e('Event Venue', 'eventer'); ?></option>
                          <option value="venue_location"><?php esc_html_e('Venue Location', 'eventer'); ?></option>
                          <option value="organizer_name"><?php esc_html_e('Organizer Name', 'eventer'); ?></option>
                          <option value="organizer_phone"><?php esc_html_e('Organizer Phone', 'eventer'); ?></option>
                          <option value="organizer_email"><?php esc_html_e('Organizer Email', 'eventer'); ?></option>
                          <option value="organizer_website"><?php esc_html_e('Organizer Website', 'eventer'); ?></option>
                          <option value="featured_image"><?php esc_html_e('Featured Image', 'eventer'); ?></option>
                          <option value="featured_image_later"><?php esc_html_e('Set Featured Image Later(Fast Import)', 'eventer'); ?></option>
                          <option value="eventer_category"><?php esc_html_e('Event Category', 'eventer'); ?></option>
                          <option value="eventer_tags"><?php esc_html_e('Event Tags', 'eventer'); ?></option>
                          <option value="all_eventers"><?php esc_html_e('All Events Date', 'eventer'); ?></option>
                        </select>
                        <input class="button remove_import_field" type="button" value="<?php esc_html_e('Remove', 'eventer'); ?>" />
                      </div>
                    </div>
                    <input type="button" class="add_import_field button" value="<?php esc_html_e('Add', 'eventer'); ?>">
                    <input type="submit" name="save_values" class="button" value="<?php esc_html_e('Save', 'eventer'); ?>">
                  </form>
                  <form name="submit_csv" action="" method="post" enctype="multipart/form-data">
                    <div>
                      <p>
                        <input type="file" name="upload_csv" id="upload_csv"><br><br>
                        <input name="eventer_import_csv" type="submit" class="button button-primary" value="<?php esc_html_e('Upload CSV File', 'eventer'); ?>">
                      </p>
                    </div>
                  </form>
              </div>
            </div>
          </div>
        <?php } ?>
      </div><!-- .wrap -->
<?php }
  }
}
new Eventer_Settings_Options();
