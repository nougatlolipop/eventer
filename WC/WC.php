<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action('plugins_loaded', 'Eventer_WC_Activate');
function Eventer_WC_Activate() 
{
	$woocommerce_ticketing = eventer_get_settings( 'eventer_enable_woocommerce_ticketing' );
	if($woocommerce_ticketing == 'on') 
	{
		if(!is_admin())
		{
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) 
		{
			require_once EVENTER__PLUGIN_PATH . 'WC/wc_functions.php';
		}
	}
}
