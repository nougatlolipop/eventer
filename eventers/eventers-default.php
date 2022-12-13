<?php
$content_output = '[eventer_list type="1" count="10" pagination="yes"]';
echo '<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">';
echo do_shortcode($content_output);
echo '</main></div>';