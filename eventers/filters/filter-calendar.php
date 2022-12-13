<div class="eventer-switcher-actions eventer-switcher-actions-view">
    <?php
    $view_set = '';
    $calview = $params['calview_set'];
    $calview = explode(',', $calview);
    if(!empty($calview))
    {
        foreach($calview as $view)
        {
            $active = ($params['calview']==$view)?'active':'';
            if($view=='date_range')
            {
                echo '<a class="eventer-datewise-filter-trigger trigger-active" title="'.esc_html__('Date range selector', 'eventer').'"><i class="eventer-icon-calendar"></i></a>';
                continue;
            }
            elseif($view=='today')
            {
                echo '<a class="list_calendar_view eventer-dynamic-call today-btn" title="Return to current date" data-calview="'.esc_attr($params['calview']).'" data-arrow="'.esc_attr(date_i18n('Y-m-d')).'">'.esc_html__('Today', 'eventer').'</a>';
                continue;
            }
            switch($view)
            {
                case 'yearly':
                $view_set = esc_html__('Yearly', 'eventer');
                break;
                case 'monthly':
                $view_set = esc_html__('Monthly', 'eventer');
                break;
                case 'weekly':
                $view_set = esc_html__('Weekly', 'eventer');
                break;
                case 'daily':
                $view_set = esc_html__('Daily', 'eventer');
                break;
            }
            echo '<a class="list_calendar_view eventer-dynamic-call '.esc_attr($active).'" data-arrow="'.esc_attr($params['current']).'" data-calview="'.esc_attr($view).'">'.esc_attr($view_set).'</a>';
        }
        
    }
    
    ?>
        </div>