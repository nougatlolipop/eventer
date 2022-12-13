<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action('plugins_loaded', 'Eventer_VC_Activate');
function Eventer_VC_Activate() 
{
	
	if(!function_exists('is_plugin_active') )
	{
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	//if ( is_plugin_active( 'js_composer/js_composer.php' ) ) 
	//{
		require_once EVENTER__PLUGIN_PATH . 'VC/vc_init.php';
	//}
}