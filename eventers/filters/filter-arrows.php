<div class="eventer-switcher-current-month">
            <div class="eventer-switcher-current-month-in"><?php echo esc_attr($params['view']); if($params['span']!='') { ?><span><?php echo esc_attr($params['span']); ?></span><?php } ?></div>
            <div class="eventer-switcher-actions">
                <?php
                if(strtotime($params['jump_date'])>strtotime($params['min']))
                {
                ?>
                <a class="eventer-dynamic-call single-run" title="<?php esc_html_e('Previous', 'eventer'); ?>" data-calview="<?php echo esc_attr($params['calview']); ?>" data-arrow="<?php echo esc_attr($params['prev']); ?>" href="javascript:void(0);"><i class="eventer-icon-arrow-left"></i></a>
                <?php
                }
                if(strtotime($params['jump_date'])<strtotime($params['max']))
                {
                ?>
                <a class="eventer-dynamic-call single-run" title="<?php esc_html_e('Next', 'eventer'); ?>" data-calview="<?php echo esc_attr($params['calview']); ?>" data-arrow="<?php echo esc_attr($params['next']); ?>" href="javascript:void(0);"><i class="eventer-icon-arrow-right"></i></a>
                <?php } ?>
            </div>
        </div>