<?php
//This template is used to show how your shortcode will look when creating from settings of Eventer plugin
get_header();
echo '<div class="eventer-preview-container" style="max-width:1140px; margin:0 auto;">';
if (isset($_POST['shortcode'])) {
    esc_html_e('This page is only used to show how your shortcode look like, some of the feature might not work on this page. You should use this shortcode to page where you would like to show the events.', 'eventer');
    echo do_shortcode(stripslashes($_POST['shortcode']));
} else {
    esc_html_e('Sorry, nothing to show here. It seems you are accessing page directly instead of calling from shortcodes settings of Eventer plugin.', 'eventer');
}
echo '</div>';
get_footer();
