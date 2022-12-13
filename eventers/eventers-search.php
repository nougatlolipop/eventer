<?php
$template_design = eventer_get_settings('eventer_search_view');
$template_sidebar = eventer_get_settings('eventer_search_sidebar');
$template_design = ($template_design!='')?$template_design:"1";
switch($template_design)
{
	case "1":
	$content_output = '[eventer_list type="1" count="10" pagination="yes"]';
	break;
	case "2":
	$content_output = '[eventer_list type="1" status="month" month_filter="1"]';
	break;
	case "3":
	$content_output = '[eventer_list type="1" month_filter="1" view="minimal" count="10" pagination="yes"]';
	break;
	case "4":
	$content_output = '[eventer_list type="1" view="minimal" status="month" month_filter="1"]';
	break;
	case "5":
	$content_output = '[eventer_grid type="1" background="1" column="3" pagination="yes" count="10"]';
	break;
}
echo do_shortcode($content_output);