<?php
	/*Front end view of eventer countdown element
	==================================*/
	function eventer_counter_element_output( $atts ) {

	/* These arguments are going to function like variables, allowing us to set new values in the front-end editor */
	extract(shortcode_atts(array(
		'ids' => '',
		'terms_cats' => '',
		'terms_tags' => '',
		'terms_venue' => '',
		'terms_organizer' => '',
		'venue' => '',
		'type' => '1',
		'event_until' => '',
	 ),$atts));
		
	 return '[eventer_counter ids="'.$ids.'" terms_cats="'.$terms_cats.'" terms_tags="'.$terms_tags.'" terms_venue="'.$terms_venue.'" terms_organizer="'.$terms_organizer.'" venue="'.$venue.'" type="'.$type.'" event_until="'.$event_until.'"]';

	}

	/*Front end view of eventer list element
	==================================*/
	function eventer_list_element_output( $atts ) {

	/* These arguments are going to function like variables, allowing us to set new values in the front-end editor */
	extract(shortcode_atts(array(
		'ids' => '',
		'terms_cats' => '',
		'terms_tags' => '',
		'terms_venue' => '',
		'terms_organizer' => '',
		'type' => '1',
		'month_filter' => '',
		'calview' => '',
		'filters' => '',
		'view' => '',
		'venue' => '',
		'pagination' => '',
		'count' => '',
	 ),$atts));
		
	 return '[eventer_list ids="'.$ids.'" terms_cats="'.$terms_cats.'" terms_tags="'.$terms_tags.'" terms_venue="'.$venue.'" terms_organizer="'.$terms_organizer.'" venue="'.$venue.'" type="'.$type.'" month_filter="'.$month_filter.'" calview="'.$calview.'" filters="'.$filters.'" view="'.$view.'" pagination="'.$pagination.'" count="'.$count.'"]';

	}

	/*Front end view of eventer grid element
	==================================*/
	function eventer_grid_element_output( $atts ) {

	/* These arguments are going to function like variables, allowing us to set new values in the front-end editor */
	extract(shortcode_atts(array(
		'ids' => '',
		'terms_cats' => '',
		'terms_tags' => '',
		'terms_venue' => '',
		'terms_organizer' => '',
		'type' => '1',
		'status' => '',
		'background' => '',
		'column' => '',
		'venue' => '',
		'pagination' => '',
		'count' => '',
	 ),$atts));
		
	 return '[eventer_grid ids="'.$ids.'" terms_cats="'.$terms_cats.'" terms_tags="'.$terms_tags.'" terms_venue="'.$venue.'" terms_organizer="'.$terms_organizer.'" venue="'.$venue.'" type="'.$type.'" status="'.$status.'" background="'.$background.'" column="'.$column.'" pagination="'.$pagination.'" count="'.$count.'"]';

	}

	/*Front end view of eventer calendar element
	==================================*/
	function eventer_calendar_element_output( $atts ) {

	/* These arguments are going to function like variables, allowing us to set new values in the front-end editor */
	extract(shortcode_atts(array(
		'terms_cats' => '',
		'terms_tags' => '',
		'terms_venue' => '',
		'terms_organizer' => '',
		'type' => '1',
		'preview' => '',
	 ),$atts));
		
	 return '[eventer_calendar terms_cats="'.$terms_cats.'" terms_tags="'.$terms_tags.'" terms_venue="'.$venue.'" terms_organizer="'.$terms_organizer.'" type="'.$type.'" preview="'.$preview.'"]';

	}
?>