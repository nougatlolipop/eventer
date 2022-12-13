<?php
echo '<div class="eventer-save-share-wrap">';
	echo do_shortcode('[eventer_social_share]');
	echo do_shortcode('[eventer_save_events]');
echo '</div>';
if(have_posts()):while(have_posts()):the_post(); ?>
<div class="eventer-single-event-content clearfix">
		<?php the_content(); ?>
</div>
<?php endwhile; endif; ?>