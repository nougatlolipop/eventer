<?php
	$categories = $tags = $venues = $organizers = $eventslist = array();

	
	/* Event Countdown Shortcode
	=====================================================*/
	add_action( 'vc_before_init', 'eventer_counter_element' );
	function eventer_counter_element() 
	{
		$categories = eventer_get_terms('eventer-category');
		if(!is_wp_error($categories))
		{
			foreach($categories as $id => $label)
			{ 
				$catterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$tags = eventer_get_terms('eventer-tag');
		if(!is_wp_error($tags))
		{
			foreach($tags as $id => $label)
			{ 
				$tagterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$venues = eventer_get_terms('eventer-venue');
		if(!is_wp_error($venues))
		{
			foreach($venues as $id => $label)
			{ 
				$venueterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$organizers = eventer_get_terms('eventer-organizer');
		if(!is_wp_error($organizers))
		{
			foreach($organizers as $id => $label)
			{ 
				$organizerterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}

		// Get list of events
		$events = eventer_get_eventer_list();
		if(!is_wp_error($events))
		{
			foreach($events as $id => $label)
			{ 
				$eventslist[] = array('value'=>esc_attr($id), 'label'=>esc_attr($label)); 
			}
		}

		$eventer_icon = EVENTER__PLUGIN_URL.'images/vc_icon.png';
		vc_map( array(
			"name" => esc_html__( "Upcoming Event Countdown", "eventer" ),
			"base" => "eventer_counter",
			"category" => esc_html__( "Eventer", "eventer"),
			"class" => "",
			"icon" => $eventer_icon,
			"params" => array(
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event', 'eventer' ),
					'description' => esc_html('You can select a specific event to show at the upcoming event counter.','eventer'),
					'param_name' => 'ids',
					'value' => $eventslist,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Category', 'eventer' ),
					'description' => esc_html('Select event category from which events will be used in the upcoming event counter.','eventer'),
					'param_name' => 'terms_cats',
					'value' => $catterms,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Tag', 'eventer' ),
					'description' => esc_html('Select event tags from which events will be used in the upcoming event counter.','eventer'),
					'param_name' => 'terms_tags',
					'value' => $tagterms,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Venue', 'eventer' ),
					'description' => esc_html('Select event venue from which events will be used in the upcoming event counter.','eventer'),
					'param_name' => 'terms_venue',
					'value' => $venueterms,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Organizer', 'eventer' ),
					'description' => esc_html('Select event organizer from which events will be used in the upcoming event counter.','eventer'),
					'param_name' => 'terms_organizer',
					'value' => $organizerterms,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Event Venue', 'eventer' ),
					'description' => esc_html__('Select Yes if you want to show your event venue address in the upcoming event counter.','eventer'),
					'param_name' => 'venue',
					'value' => array( esc_html__( 'Yes', 'eventer' ) => '', esc_html__( 'No', 'eventer' ) => 'no'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Type', 'eventer' ),
					'description' => esc_html__('Select which event type you want to show in the upcoming event counter.','eventer'),
					'param_name' => 'type',
					'value' => array( esc_html__( 'WP', 'eventer' ) => '1', esc_html__( 'Google', 'eventer' ) => '2'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Counter Until', 'eventer' ),
					'description' => esc_html__('Select till what time an event will be shown in the upcoming event counter.','eventer'),
					'param_name' => 'event_until',
					'value' => array( esc_html__( 'Start Time', 'eventer' ) => '', esc_html__( 'End Time', 'eventer' ) => '2'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
			)
		));
	}

	/* Event List Shortcode
	=====================================================*/
	add_action( 'vc_before_init', 'eventer_list_element' );
	function eventer_list_element() 
	{
		$categories = eventer_get_terms('eventer-category');
		if(!is_wp_error($categories))
		{
			foreach($categories as $id => $label)
			{ 
				$catterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$tags = eventer_get_terms('eventer-tag');
		if(!is_wp_error($tags))
		{
			foreach($tags as $id => $label)
			{ 
				$tagterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$venues = eventer_get_terms('eventer-venue');
		if(!is_wp_error($venues))
		{
			foreach($venues as $id => $label)
			{ 
				$venueterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$organizers = eventer_get_terms('eventer-organizer');
		if(!is_wp_error($organizers))
		{
			foreach($organizers as $id => $label)
			{ 
				$organizerterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}

		// Get list of events
		$events = eventer_get_eventer_list();
		if(!is_wp_error($events))
		{
			foreach($events as $id => $label)
			{ 
				$eventslist[] = array('value'=>esc_attr($id), 'label'=>esc_attr($label)); 
			}
		}

		$eventer_icon = EVENTER__PLUGIN_URL.'images/vc_icon.png';
		vc_map( array(
			"name" => esc_html__( "Events List", "eventer" ),
			"base" => "eventer_list",
			"category" => esc_html__( "Eventer", "eventer"),
			"class" => "",
			"icon" => $eventer_icon,
			"params" => array(
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Event', 'eventer' ),
					'param_name' => 'ids',
					'description' => esc_html__( 'You can select specific events to show in the list. Start typing event title to add to the list. Multiple events can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $eventslist,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Categories', 'eventer' ),
					'param_name' => 'terms_cats',
					'description' => esc_html__( 'You can select category here to show selected events. Start typing category name to add to the list. Multiple categories can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $catterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Tags', 'eventer' ),
					'param_name' => 'terms_tags',
					'description' => esc_html__( 'You can select tag here to show selected events. Start typing tag nam\e to add to the list. Multiple tags can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $tagterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Venues', 'eventer' ),
					'param_name' => 'terms_venue',
					'description' => esc_html__( 'You can select venue here to show selected events. Start typing venue title to add to the list. Multiple venues can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $venueterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Organizers', 'eventer' ),
					'param_name' => 'terms_organizer',
					'description' => esc_html__( 'You can select organizer here to show selected events. Start typing organizer title to add to the list. Multiple organizers can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $organizerterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Type', 'eventer' ),
					'description' => esc_html__('Select event type for the list. You can choose All to show both WordPress and Google Calendar events in the list or WP/Google to show selected events only.','eventer'),
					'param_name' => 'type',
					'value' => array( esc_html__( 'All', 'eventer' ) => '', esc_html__( 'WP', 'eventer' ) => '1', esc_html__( 'Google', 'eventer' ) => '2'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Filters', 'eventer' ),
					'description' => esc_html__('Select Yes to show a month filter above the list of events, which allows users to go to next/prev months or to the next 12 months events.','eventer'),
					'param_name' => 'month_filter',
					'value' => array( esc_html__( 'No', 'eventer' ) => '', esc_html__( 'Yes', 'eventer' ) => '1'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__( 'View Options', 'eventer' ),
					'description' => esc_html__('Choose options for view of events list. You can check multiple views. For none check none option only.','eventer'),
					'param_name' => 'calview',
					'value' => array( esc_html__( 'None', 'eventer' ) => '', esc_html__( 'Yearly', 'eventer' ) => 'yearly', esc_html__( 'Monthly', 'eventer' ) => 'monthly', esc_html__( 'Weekly', 'eventer' ) => 'weekly', esc_html__( 'Daily', 'eventer' ) => 'daily', esc_html__( 'Today', 'eventer' ) => 'today', esc_html__( 'Date Range', 'eventer' ) => 'date_range'),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'month_filter',
						'value' => array('1'),
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => esc_html__( 'Filter Options', 'eventer' ),
					'description' => esc_html__('Choose type of filters to show for events. You can check multiple views. For none check none option only.','eventer'),
					'param_name' => 'filters',
					'value' => array( esc_html__( 'None', 'eventer' ) => '', esc_html__( 'Categories', 'eventer' ) => 'category', esc_html__( 'Tags', 'eventer' ) => 'tag', esc_html__( 'Venues', 'eventer' ) => 'venue', esc_html__( 'Organizers', 'eventer' ) => 'organizer'),
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'month_filter',
						'value' => array('1'),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Status', 'eventer' ),
					'description' => esc_html__('Select the default view of the events to show in the list.','eventer'),
					'param_name' => 'type',
					'value' => array( esc_html__( 'Future', 'eventer' ) => '', esc_html__( 'Past', 'eventer' ) => 'past', esc_html__( 'Yearly', 'eventer' ) => 'yearly', esc_html__( 'Monthly', 'eventer' ) => 'monthly', esc_html__( 'Weekly', 'eventer' ) => 'weekly', esc_html__( 'Daily', 'eventer' ) => 'daily'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'List Style', 'eventer' ),
					'description' => esc_html__('Select style of the list for the events.','eventer'),
					'param_name' => 'view',
					'value' => array( esc_html__( 'Compact', 'eventer' ) => '', esc_html__( 'Minimal', 'eventer' ) => 'minimal'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Event Venue', 'eventer' ),
					'description' => esc_html__('Select Yes to show event venue address for every event in the list.','eventer'),
					'param_name' => 'venue',
					'value' => array( esc_html__( 'Yes (Show full address)', 'eventer' ) => '', esc_html__( 'Yes (Show venue name)', 'eventer' ) => 'name', esc_html__( 'No', 'eventer' ) => 'no'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Events Per Page', 'eventer' ),
					'description' => esc_html__('Select the number of events to show per page when event month filter is active.','eventer'),
					'param_name' => 'count',
					'value' => array( esc_html__( 'Default', 'eventer' ) => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20', '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31', '32' => '32', '33' => '33', '34' => '34', '35' => '35', '36' => '36', '37' => '37', '38' => '38', '39' => '39', '40' => '40', '41' => '41', '42' => '42', '43' => '43', '44' => '44', '45' => '45', '46' => '46', '47' => '47', '48' => '48', '49' => '49', '50' => '50'),
					'admin_label' => true,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Pagination', 'eventer' ),
					'description' => esc_html__('Select Yes to show pagination below the events list. This will use events per page option.','eventer'),
					'param_name' => 'pagination',
					'value' => array( esc_html__( 'No', 'eventer' ) => '', esc_html__( 'Yes', 'eventer' ) => 'yes'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
			)
		));
	}

	/* Event Grid Shortcode
	=====================================================*/
	add_action( 'vc_before_init', 'eventer_grid_element' );
	function eventer_grid_element() 
	{
		$categories = eventer_get_terms('eventer-category');
		if(!is_wp_error($categories))
		{
			foreach($categories as $id => $label)
			{ 
				$catterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$tags = eventer_get_terms('eventer-tag');
		if(!is_wp_error($tags))
		{
			foreach($tags as $id => $label)
			{ 
				$tagterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$venues = eventer_get_terms('eventer-venue');
		if(!is_wp_error($venues))
		{
			foreach($venues as $id => $label)
			{ 
				$venueterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$organizers = eventer_get_terms('eventer-organizer');
		if(!is_wp_error($organizers))
		{
			foreach($organizers as $id => $label)
			{ 
				$organizerterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}

		// Get list of events
		$events = eventer_get_eventer_list();
		if(!is_wp_error($events))
		{
			foreach($events as $id => $label)
			{ 
				$eventslist[] = array('value'=>esc_attr($id), 'label'=>esc_attr($label)); 
			}
		}

		$eventer_icon = EVENTER__PLUGIN_URL.'images/vc_icon.png';
		vc_map( array(
			"name" => esc_html__( "Events Grid", "eventer" ),
			"base" => "eventer_grid",
			"category" => esc_html__( "Eventer", "eventer"),
			"class" => "",
			"icon" => $eventer_icon,
			"params" => array(
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Event', 'eventer' ),
					'param_name' => 'ids',
					'description' => esc_html__( 'You can select specific events to show in the list. Start typing event title to add to the list. Multiple events can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $eventslist,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Categories', 'eventer' ),
					'param_name' => 'terms_cats',
					'description' => esc_html__( 'You can select category here to show selected events. Start typing category name to add to the list. Multiple categories can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $catterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Tags', 'eventer' ),
					'param_name' => 'terms_tags',
					'description' => esc_html__( 'You can select tag here to show selected events. Start typing tag nam\e to add to the list. Multiple tags can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $tagterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Venues', 'eventer' ),
					'param_name' => 'terms_venue',
					'description' => esc_html__( 'You can select venue here to show selected events. Start typing venue title to add to the list. Multiple venues can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $venueterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Organizers', 'eventer' ),
					'param_name' => 'terms_organizer',
					'description' => esc_html__( 'You can select organizer here to show selected events. Start typing organizer title to add to the list. Multiple organizers can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $organizerterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Events Type', 'eventer' ),
					'description' => esc_html__('Select event type for the list. You can choose All to show both WordPress and Google Calendar events in the list or WP/Google to show selected events only.','eventer'),
					'param_name' => 'type',
					'value' => array( esc_html__( 'All', 'eventer' ) => '', esc_html__( 'WP', 'eventer' ) => '1', esc_html__( 'Google', 'eventer' ) => '2'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Events Status', 'eventer' ),
					'description' => esc_html__('Select the default view of the events to show in the list.','eventer'),
					'param_name' => 'status',
					'value' => array( esc_html__( 'Future', 'eventer' ) => '', esc_html__( 'Past', 'eventer' ) => 'past'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Grid Background Style', 'eventer' ),
					'description' => esc_html__('Select the background option for the grid items. Default will show featured image if available else Category selected color as background if available else it will be plain white background.','eventer'),
					'param_name' => 'background',
					'value' => array( esc_html__( 'Default - Featured Image/Category Color/Plain', 'eventer' ) => '', esc_html__( 'Plain', 'eventer' ) => '3', esc_html__( 'Event Category Color', 'eventer' ) => '1', esc_html__( 'Featured Image', 'eventer' ) => '2'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Grid Columns', 'eventer' ),
					'description' => esc_html__('Select columns for the grid.','eventer'),
					'param_name' => 'column',
					'value' => array( esc_html__( 'Default(Three Columns)', 'eventer' ) => '', esc_html__( 'One Column', 'eventer' ) => '1', esc_html__( 'Two Columns', 'eventer' ) => '2', esc_html__( 'Four Columns', 'eventer' ) => '4'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Event Venue', 'eventer' ),
					'description' => esc_html__('Select Yes to show event venue address for every event in the list.','eventer'),
					'param_name' => 'venue',
					'value' => array( esc_html__( 'Yes (Show full address)', 'eventer' ) => '', esc_html__( 'Yes (Show venue name)', 'eventer' ) => 'name', esc_html__( 'No', 'eventer' ) => 'no'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Events Per Page', 'eventer' ),
					'description' => esc_html__('Select the number of events to show per page in grid.','eventer'),
					'param_name' => 'count',
					'value' => array( esc_html__( 'Default', 'eventer' ) => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20', '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31', '32' => '32', '33' => '33', '34' => '34', '35' => '35', '36' => '36', '37' => '37', '38' => '38', '39' => '39', '40' => '40', '41' => '41', '42' => '42', '43' => '43', '44' => '44', '45' => '45', '46' => '46', '47' => '47', '48' => '48', '49' => '49', '50' => '50'),
					'admin_label' => true,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Show Pagination', 'eventer' ),
					'description' => esc_html__('Select Yes to show pagination below the events grid. This will use events per page option.','eventer'),
					'param_name' => 'pagination',
					'value' => array( esc_html__( 'No', 'eventer' ) => '', esc_html__( 'Yes', 'eventer' ) => 'yes'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
			)
		));
	}

	/* Event Calendar Shortcode
	=====================================================*/
	add_action( 'vc_before_init', 'eventer_calendar_element' );
	function eventer_calendar_element() 
	{
		$categories = eventer_get_terms('eventer-category');
		if(!is_wp_error($categories))
		{
			foreach($categories as $id => $label)
			{ 
				$catterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$tags = eventer_get_terms('eventer-tag');
		if(!is_wp_error($tags))
		{
			foreach($tags as $id => $label)
			{ 
				$tagterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$venues = eventer_get_terms('eventer-venue');
		if(!is_wp_error($venues))
		{
			foreach($venues as $id => $label)
			{ 
				$venueterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}
		$organizers = eventer_get_terms('eventer-organizer');
		if(!is_wp_error($organizers))
		{
			foreach($organizers as $id => $label)
			{ 
				$organizerterms[] = array('value'=>$id, 'label'=>$label); 
			}
		}

		// Get list of events
		$events = eventer_get_eventer_list();
		if(!is_wp_error($events))
		{
			foreach($events as $id => $label)
			{ 
				$eventslist[] = array('value'=>esc_attr($id), 'label'=>esc_attr($label)); 
			}
		}

		$eventer_icon = EVENTER__PLUGIN_URL.'images/vc_icon.png';
		vc_map( array(
			"name" => esc_html__( "Events Calendar", "eventer" ),
			"base" => "eventer_calendar",
			"category" => esc_html__( "Eventer", "eventer"),
			"class" => "",
			"icon" => $eventer_icon,
			"params" => array(
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Categories', 'eventer' ),
					'param_name' => 'terms_cats',
					'description' => esc_html__( 'You can select category here to show selected events. Start typing category name to add to the list. Multiple categories can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $catterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Tags', 'eventer' ),
					'param_name' => 'terms_tags',
					'description' => esc_html__( 'You can select tag here to show selected events. Start typing tag nam\e to add to the list. Multiple tags can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $tagterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Venues', 'eventer' ),
					'param_name' => 'terms_venue',
					'description' => esc_html__( 'You can select venue here to show selected events. Start typing venue title to add to the list. Multiple venues can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $venueterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'autocomplete',
					'class' => '',
					'heading' => esc_html__( 'Organizers', 'eventer' ),
					'param_name' => 'terms_organizer',
					'description' => esc_html__( 'You can select organizer here to show selected events. Start typing organizer title to add to the list. Multiple organizers can be added here.', 'eventer' ),
					'settings'		=> array( 'values' => $organizerterms,'multiple' => true,
					'min_length' => 1,
					'groups' => true,
					'unique_values' => true,
					'display_inline' => false,
					'delay' => 500,
					'auto_focus' => true, ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Events Type', 'eventer' ),
					'description' => esc_html__('Select event type for the calendar. You can choose All to show both WordPress and Google Calendar events in the calendar or WP/Google to show selected events only.','eventer'),
					'param_name' => 'type',
					'value' => array( esc_html__( 'All', 'eventer' ) => '', esc_html__( 'WP', 'eventer' ) => '1', esc_html__( 'Google', 'eventer' ) => '2'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Event Preview', 'eventer' ),
					'description' => esc_html__('Select yes to show a quick preview of event details including tickets info on hover of events title in calendar.','eventer'),
					'param_name' => 'preview',
					'value' => array( esc_html__( 'Yes', 'eventer' ) => '', esc_html__( 'No', 'eventer' ) => 'no'),
					'param_holder_class' => 'vc_colored-dropdown',
				),
			)
		));
	}
?>