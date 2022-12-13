<?php while(have_posts()):the_post();
$eventer_loop_start = apply_filters('eventer_registration_data_collect', 1, get_the_ID());
$eventer_image_size = (eventer_get_settings('eventer_image_size_single')!='')?eventer_get_settings('eventer_image_size_single'):'full';
$registration_switch = get_post_meta(get_the_ID(), 'eventer_event_registration_swtich', true);
?>
<div class="eventer eventer-event-single" >
	<?php echo apply_filters('eventer_styled_single_title', $title = '', get_the_ID(), $eventer_loop_start); ?>
	<?php if(has_post_thumbnail()) { ?>
		<div class="eventer-single-image">
			<?php the_post_thumbnail($eventer_image_size); ?>
		</div>
	<?php }
echo '</div>';
if($registration_switch=="1"){
	echo '<div class="eventer-is-tickets-active">';
}
echo do_shortcode('[eventer_metas]');
echo do_shortcode('[eventer_tickets]');
if($registration_switch=="1"){
	echo '</div>';
}
endwhile;