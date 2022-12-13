<?php
/*
 * Plugin Name: Eventer
 * Plugin URI:  https://eventer.imithemes.com
 * Description: WordPress Event Manager Plugin
 * Author:      imithemes
 * Version:     3.2
 * Author URI:  http://www.imithemes.com
 * Licence:     GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Copyright:   (c) 2021 imithemes. All rights reserved
 * Text Domain: eventer
 * Domain Path: /languages
 */

// Do not allow direct access to this file.
defined('ABSPATH') or die('No script kiddies please!');

define('EVENTER__PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EVENTER__PLUGIN_URL', plugin_dir_url(__FILE__));
/* PARTIALS ATTACHMENTS
================================================== */
require_once EVENTER__PLUGIN_PATH . '/admin/admin_functions.php';
require_once EVENTER__PLUGIN_PATH . '/admin/eventer-type.php';
require_once EVENTER__PLUGIN_PATH . '/admin/settings_page.php';
require_once EVENTER__PLUGIN_PATH . '/admin/meta_fields.php';
if (!is_admin()) {
    require_once EVENTER__PLUGIN_PATH . '/front/eventer-shortcodes.php';

}
require_once EVENTER__PLUGIN_PATH . '/front/front_functions.php';
require_once EVENTER__PLUGIN_PATH . '/front/eventer_rest_endpoints.php';
require_once EVENTER__PLUGIN_PATH . '/front/eventer_actions.php';

require_once EVENTER__PLUGIN_PATH . '/front/shortcodes.php';
require_once EVENTER__PLUGIN_PATH . '/front/ipn.php';
require_once EVENTER__PLUGIN_PATH . 'WC/WC.php';
require_once EVENTER__PLUGIN_PATH . '/VC/VC.php';

/* SET LANGUAGE FILE FOLDER
=================================================== */
add_action('plugins_loaded', 'eventer_load_textdomain');
function eventer_load_textdomain()
{
    load_plugin_textdomain('eventer', false, basename(dirname(__FILE__)) . '/languages');
    $site_lang = substr(get_locale(), 0, 2);
    if (function_exists('icl_object_id') && class_exists('SitePress')) {
        $site_lang = ICL_LANGUAGE_CODE;
    }
    define('EVENTER__LANGUAGE_CODE', $site_lang);
    $woocommerce_switch = eventer_get_settings('eventer_enable_woocommerce_ticketing');
    $woocommerce_layout = eventer_get_settings('eventer_woo_layout');
    $eventer_slug = eventer_get_settings('eventer_event_permalink');
    $event_slug = (empty($eventer_slug)) ? 'eventer' : $eventer_slug;
    $event_link = $_SERVER['REQUEST_URI'];
    if ($woocommerce_switch == 'on' && $woocommerce_layout == 'on' && !defined('WOOCOMMERCE_CHECKOUT') && strpos($event_link, $event_slug)) {
        define('WOOCOMMERCE_CHECKOUT', true);
    }
}

/* GETTING EVENTER SETTING PAGE ID
=================================================== */
function eventer_get_settings($id)
{
    $options = get_option('eventer_options');
    if (isset($options[$id])) {
        return $options[$id];
    }
}

/* EVENTER DATE DIFFERENCE FUNCTION
=================================================== */
if (!function_exists('eventer_dateDiff')) {
    function eventer_dateDiff($start, $end)
    {
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);
        $diff = $end_ts - $start_ts;
        return floor($diff / 86400);
    }
}
function eventer_add_eventer_manager_role()
{
    add_role('eventer_manager', esc_html__('Event manager', 'eventer'), array('edit_post' => true, 'edit_published_posts' => true, 'edit_posts' => true, 'publish_posts' => true, 'read_post' => true, 'read' => true, 'delete_post' => true, 'edit_published_posts' => true, 'upload_files' => true, 'edit_product' => true, 'edit_published_products' => true, 'edit_products' => true, 'publish_products' => true, 'read_product' => true, 'delete_product' => true));
}
/* PLUGIN ACTIVATION HOOK
=================================================== */
register_activation_hook(__FILE__, 'eventer_add_eventer_manager_role');
add_action('admin_init', array('Eventer_Settings_Options', 'eventer_create_ticket_details_table'));
register_activation_hook(__FILE__, array('Eventer_Settings_Options', 'eventer_flush_rewrite_activate'));
register_activation_hook(__FILE__, array('Eventer_Settings_Options', 'eventer_flush_rewrite_deactivate'));
register_activation_hook(__FILE__, array('Eventer_Settings_Options', 'eventer_store_default_settings'));
