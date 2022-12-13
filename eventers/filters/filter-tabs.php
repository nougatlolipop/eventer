<ul class="eventer-twelve-months">
    <?php
    $current_date = $params['current'];
    $view = $params['calview'];
    $time = strtotime($current_date);

    $steps = $params['steps'];
    for ($start = 1; $start <= $steps; $start++) {
        $final = ($view != 'yearly') ? date("Y-m-d", strtotime("+" . $start . " " . $params['type'], $time)) : date("Y-01-01", strtotime("+" . $start . " " . $params['type'], $time));
        if (strtotime($final) > strtotime($params['max'])) break;

        echo '<li data-calview="' . esc_attr($view) . '" data-arrow="' . esc_attr($final) . '" class="eventer-dynamic-call next-month">' . esc_attr(date_i18n($params['view_format'], strtotime($final))) . '</li>';
    }
    ?>
</ul>