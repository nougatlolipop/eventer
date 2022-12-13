<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$woocommerce_ticketing = eventer_get_settings( 'eventer_enable_woocommerce_ticketing' );
get_header();
if(is_tax('eventer-category')||is_tax('eventer-tag')||is_tax('eventer-venue')||is_tax('eventer-organizer')) 
{
 eventer_get_template_part('eventers/taxonomies');
}
elseif(is_singular('eventer'))
{
		eventer_get_template_part('eventers/single', 'loop');
		eventer_get_template_part('eventers/single', 'options');
		eventer_get_template_part('eventers/single', 'modal');
}
elseif(is_archive('eventer'))
{
	eventer_get_template_part('eventers/eventers', 'archive');
}
elseif(is_search('eventer'))
{
	eventer_get_template_part('eventers/eventers', 'search');
}
else
{
	//eventer_get_template_part('eventers/eventers', 'default');
}
get_footer(); ?>
