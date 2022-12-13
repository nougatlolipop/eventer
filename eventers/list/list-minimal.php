<li class="eventer-event-item eventer-event-list-item <?php echo esc_attr($params['featured_class']); ?>" style="border-left-color:<?php echo esc_attr($params['color']); ?>">
    <?php echo $params['featured_span']; ?>
    <a href="<?php echo $params['details']; ?>" target="<?php echo esc_attr($params['target']); ?>" class="eventer-event-item-link equah">
        <?php
        if ($params['multi'] == '1') {
            ?>
            <span class="eventer-event-date">
                <span>
                    <span class="eventer-cell">
                        <span class="eventer-dater">
                            <span class="eventer-event-month"><?php echo date_i18n('d M', strtotime($params['start'])); ?>-<?php echo date_i18n('d M', strtotime($params['end'])); ?></span>
                            <span class="eventer-event-time"> <?php echo esc_attr($params['show_time']); ?></span>
                        </span>
                    </span>
                </span>
            </span>
        <?php } else { ?>
            <span class="eventer-event-date">
                <span>
                    <span class="eventer-cell">
                        <span class="eventer-event-day pull-left"><?php echo date_i18n('d', strtotime($params['date'])); ?></span>
                        <span class="eventer-dater">
                            <span class="eventer-event-month"><?php echo date_i18n('M', strtotime($params['date'])); ?></span>
                            <span class="eventer-event-year"> <?php echo date_i18n('Y', strtotime($params['date'])); ?></span>
                            <span class="eventer-event-time"> <?php echo esc_attr($params['show_time']); ?></span>
                        </span>
                    </span>
                </span>
            </span>
        <?php } ?>
        <span class="eventer-event-details">
            <span class="eventer-event-title"><?php echo $params['event']; ?> </span>
            <?php if (!empty($params['address'])) { ?>
                <span class="eventer-event-venue"> <?php echo $params['address']; ?></span>
            <?php } ?>
        </span>
    </a>
</li>