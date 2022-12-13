<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/* ==================================================
  Eventer Post Type Functions
  ================================================== */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
add_action('init', 'eventer_register_post_type', 0);
function eventer_register_post_type() {
	$event_categories = eventer_get_settings('eventer_enable_categories');
	$event_tags = eventer_get_settings('eventer_enable_tags');
	$event_venue = eventer_get_settings('eventer_enable_venue');
	$event_organizer = eventer_get_settings('eventer_enable_organizer');
	
	//Getting eventer custom post type custom slug from eventer permalinks settings page
	$event_permalinks = eventer_get_settings('eventer_event_permalink');
  $event_permalink = empty($event_permalinks) ? _x('eventer', 'slug', 'eventer') : $event_permalinks;
	//Custom slug for category
	$event_category_permalinks = eventer_get_settings('eventer_event_category_permalink');
  $event_category_permalink = empty($event_category_permalinks) ? _x('eventer-category', 'slug', 'eventer') : $event_category_permalinks;
	//Custom slug for tags
	$event_tag_permalinks = eventer_get_settings('eventer_event_tag_permalink');
  $event_tag_permalink = empty($event_tag_permalinks) ? _x('eventer-tag', 'slug', 'eventer') : $event_tag_permalinks;
	//Custom slug for venue
	$event_venue_permalinks = eventer_get_settings('eventer_event_venue_permalink');
  $event_venue_permalink = empty($event_venue_permalinks) ? _x('eventer-venue', 'slug', 'eventer') : $event_venue_permalinks;
	//Custom slug for venue
	$event_organizer_permalinks = eventer_get_settings('eventer_event_organizer_permalink');
  $event_organizer_permalink = empty($event_organizer_permalinks) ? _x('eventer-organizer', 'slug', 'eventer') : $event_organizer_permalinks;
	
	$eventer_archive_switch = eventer_get_settings('eventer_archive_switch');
  $eventer_archive_set = (empty($eventer_archive_switch) || $eventer_archive_switch=='on')?true:false;
	
		$labels = array(
			'name' => esc_html__('Eventer', 'eventer'),
			'singular_name' => esc_html__('Eventer', 'eventer'),
			'add_new' => esc_html__('Add New', 'eventer'),
			'all_items'=> esc_html__('All Events', 'eventer'),
			'add_new_item' => esc_html__('Add New', 'eventer'),
			'edit_item' => esc_html__('Edit', 'eventer'),
			'new_item' => esc_html__('New', 'eventer'),
			'view_item' => esc_html__('View', 'eventer'),
			'search_items' => esc_html__('Search', 'eventer'),
			'not_found' => esc_html__('Nothing found', 'eventer'),
			'not_found_in_trash' => esc_html__('Nothing found in Trash', 'eventer'),
			'parent_item_colon' => '',
		);
	$args_d = array(
		"label" => esc_html__('Event Categories', 'eventer'),
		"singular_label" => esc_html__('Event Categroy', "eventer"),
		'hierarchical' => true,
		'show_ui' => true,
        'show_in_rest' => true,
		'show_in_nav_menus' => true,
		'rewrite' => $event_category_permalink != "eventer-category" ? array(
            'slug' => untrailingslashit($event_category_permalink),
            'with_front' => false,
            'feeds' => true, 'ep_mask' => EP_ALL) : array('slug' => 'eventer-category', 'ep_mask' => EP_ALL),
		'query_var' => true,
		'show_admin_column' => true,
	);
	
	
	
	$tags = array(
		"label" => esc_html__('Event Tag', 'eventer'),
		"singular_label" => esc_html__('Event Tag','eventer'),
		'public' => true,
		'hierarchical' => false,
		'show_ui' => true,
        'show_in_rest' => true,
		'show_in_nav_menus' => true,
		'rewrite' => $event_tag_permalink != "eventer-tag" ? array(
            'slug' => untrailingslashit($event_tag_permalink),
            'with_front' => false,
            'feeds' => true, 'ep_mask' => EP_ALL) : array('slug' => 'eventer-tag', 'ep_mask' => EP_ALL),
	   'query_var' => true,
	   'show_admin_column' => true,
	);
	
	$labels_venue = array(
		'name'              => esc_html__( 'Venues', 'eventer' ),
		'singular_name'     => esc_html__( 'Venue', 'eventer' ),
		'search_items'      => esc_html__( 'Search Venues', 'eventer' ),
		'all_items'         => esc_html__( 'All Venues', 'eventer' ),
		'parent_item'       => esc_html__( 'Parent Venue', 'eventer' ),
		'parent_item_colon' => esc_html__( 'Parent Venue:', 'eventer' ),
		'edit_item'         => esc_html__( 'Edit Venue', 'eventer' ),
		'update_item'       => esc_html__( 'Update Venue', 'eventer' ),
		'add_new_item'      => esc_html__( 'Add New Venue', 'eventer' ),
		'new_item_name'     => esc_html__( 'New Venue Name', 'eventer' ),
		'menu_name'         => esc_html__( 'Venues', 'eventer' ),
	);
	$args_v = array(
		"labels" => $labels_venue,
		"singular_label" => esc_html__('Venue', "eventer"),
		'public' => true,
        'show_in_rest' => true,
		'hierarchical' => true,
		'show_ui' => true,
		'show_in_nav_menus' => true,
		'rewrite' => $event_venue_permalink != "eventer-venue" ? array(
            'slug' => untrailingslashit($event_venue_permalink),
						'with_front' => false,
            'feeds' => true, 'ep_mask' => EP_ALL) : array('slug' => 'eventer-venue', 'ep_mask' => EP_ALL),
		'query_var' => true,
		'show_admin_column' => true,
	);
	
	$labels_organizer = array(
		'name'              => esc_html__( 'Organizers', 'eventer' ),
		'singular_name'     => esc_html__( 'Organizer', 'eventer' ),
		'search_items'      => esc_html__( 'Search Organizers', 'eventer' ),
		'all_items'         => esc_html__( 'All Organizers', 'eventer' ),
		'parent_item'       => esc_html__( 'Parent Organizer', 'eventer' ),
		'parent_item_colon' => esc_html__( 'Parent Organizer:', 'eventer' ),
		'edit_item'         => esc_html__( 'Edit Organizer', 'eventer' ),
		'update_item'       => esc_html__( 'Update Organizer', 'eventer' ),
		'add_new_item'      => esc_html__( 'Add New Organizer', 'eventer' ),
		'new_item_name'     => esc_html__( 'New Organizer Name', 'eventer' ),
		'menu_name'         => esc_html__( 'Organizers', 'eventer' ),
	);
	$args_o = array(
		"labels" => $labels_organizer,
		"singular_label" => esc_html__('Organizer', "eventer"),
		'public' => true,
		'hierarchical' => true,
		'show_ui' => true,
        'show_in_rest' => true,
		'show_in_nav_menus' => true,
		'rewrite' => $event_organizer_permalink != "eventer-organizer" ? array(
            'slug' => untrailingslashit($event_organizer_permalink),
            'with_front' => false,
            'feeds' => true, 'ep_mask' => EP_ALL) : array('slug' => 'eventer-organizer', 'ep_mask' => EP_ALL),
		'query_var' => true,
		'show_admin_column' => true,
	);
	
	   $args = array(
			'labels' => $labels,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'hierarchical' => false,
			'rewrite' => $event_permalink != "eventer" ? array(
            'slug' => untrailingslashit($event_permalink),
            'with_front' => false,
            'feeds' => true, 'ep_mask' => EP_ALL,) : array(
            'slug' => 'eventer',
            'with_front' => false,
            'feeds' => true, 'ep_mask' => EP_ALL,),
      'show_in_rest'       => true,
  		'rest_base'          => 'eventer',
  		'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports' => array('title', 'thumbnail','editor','author','excerpt'),
			'has_archive' => $eventer_archive_set,
			'menu_icon' => 'dashicons-format-chat',
		   );
		add_rewrite_endpoint( 'edate', EP_PERMALINK );
		register_post_type('eventer', $args);
		if($event_categories=='on')
		{
			register_taxonomy('eventer-category', 'eventer', $args_d);
			register_taxonomy_for_object_type('eventer-category','eventer');
		}
		if($event_tags=='on')
		{
			register_taxonomy('eventer-tag', 'eventer',$tags);
			register_taxonomy_for_object_type('eventer-tag','eventer');
		}
		if($event_venue=='on')
		{
			register_taxonomy('eventer-venue', 'eventer', $args_v);
			register_taxonomy_for_object_type('eventer-venue','eventer');
		}
		if($event_organizer=='on')
		{
			register_taxonomy('eventer-organizer', 'eventer', $args_o);
			register_taxonomy_for_object_type('eventer-organizer','eventer');
		}
}
?>