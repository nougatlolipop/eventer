<?php
$taxonomy = get_query_var( 'taxonomy' );
if($taxonomy=="eventer-category")
{
	$template_design = eventer_get_settings('eventer_category_view');
	$template_sidebar = eventer_get_settings('eventer_category_sidebar');
	$terms_arg = 'terms_cats="'.get_queried_object()->term_id.'"';
}
elseif($taxonomy=="eventer-tag")
{
	$template_design = eventer_get_settings('eventer_tag_view');
	$template_sidebar = eventer_get_settings('eventer_tag_sidebar');
	$terms_arg = 'terms_tags="'.get_queried_object()->term_id.'"';
}
elseif($taxonomy=="eventer-venue")
{
	$template_design = eventer_get_settings('eventer_venues_view');
	$template_sidebar = eventer_get_settings('eventer_venue_sidebar');
	$terms_arg = 'terms_venue="'.get_queried_object()->term_id.'"';
}
elseif($taxonomy=="eventer-organizer")
{
	$template_design = eventer_get_settings('eventer_organizer_view');
	$template_sidebar = eventer_get_settings('eventer_organizer_sidebar');
	$terms_arg = 'terms_organizer="'.get_queried_object()->term_id.'"';
}
$template_design = ($template_design!='')?$template_design:"1";
switch($template_design)
{
	case "1":
	$content_output = '[eventer_list '.$terms_arg.' type="1" count="10" pagination="yes"]';
	break;
	case "2":
	$content_output = '[eventer_list '.$terms_arg.' type="1" status="month" month_filter="1"]';
	break;
	case "3":
	$content_output = '[eventer_list '.$terms_arg.' type="1" month_filter="1" view="minimal" count="10" pagination="yes"]';
	break;
	case "4":
	$content_output = '[eventer_list '.$terms_arg.' type="1" view="minimal" status="month" month_filter="1"]';
	break;
	case "5":
	$content_output = '[eventer_grid '.$terms_arg.' type="1" background="" column="3" pagination="yes" count="10"]';
	break;
}
echo do_shortcode($content_output);