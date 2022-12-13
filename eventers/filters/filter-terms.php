<label><?php esc_html_e('Sort Events', 'eventer'); ?></label>
<?php
if (!empty($params['term_filters'])) {
    foreach ($params['term_filters'] as $filter) {
        switch ($filter) {
            case 'category':
                $term_name = esc_html__('By Event Category', 'eventer');
                $term_atts = 'terms_cats';
                break;
            case 'organizer':
                $term_name = esc_html__('By Event Organizer', 'eventer');
                $term_atts = 'terms_organizer';
                break;
            case 'venue':
                $term_name = esc_html__('By Event Venue', 'eventer');
                $term_atts = 'terms_venue';
                break;
            case 'tag':
                $term_name = esc_html__('By Event Tags', 'eventer');
                $term_atts = 'terms_tags';
                break;
        }
        $term_selected = $params[$term_atts];
        $term_selected = explode(',', $term_selected);
        $terms = get_terms(array('taxonomy' => 'eventer-' . $filter, 'lang' => $params['lang']));
        if (empty($terms) || is_wp_error($terms)) continue;
        ?>
        <div class="eventer-filter-col">
            <a class="eventer-filter-trigger eventer-btn eventer-btn-basic" href="javascript:void(0)"><?php echo esc_attr($term_name); ?> <i class="eventer-icon-arrow-down"></i></a>
            <ul class="eventer-filter-select " data-taxonomy="<?php echo esc_attr($term_atts); ?>">
                <?php
                foreach ($terms as $term) {
                    $selected = (in_array($term->term_id, $term_selected)) ? 'checked' : '';
                    ?>
                    <li class="">
                        <label><input data-term="<?php echo esc_attr($term->term_id); ?>" <?php echo esc_attr($selected); ?> type="checkbox" class="eventer-term-filters eventers-filter-check " value="<?php echo esc_attr($term->term_id); ?>"> <?php echo esc_attr($term->name); ?></label>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php }
}
?>