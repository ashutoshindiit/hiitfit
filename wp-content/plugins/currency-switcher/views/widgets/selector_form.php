<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>


<p>
    <label for="<?php echo $widget->get_field_id('title'); ?>"><?php _e('Title', 'currency-switcher') ?>:</label>
    <input class="widefat" type="text" id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
</p>


<p>
    <label for="<?php echo $widget->get_field_id('width'); ?>"><?php _e('Width', 'currency-switcher') ?>:</label>
    <input class="widefat" type="text" id="<?php echo $widget->get_field_id('width'); ?>" name="<?php echo $widget->get_field_name('width'); ?>" value="<?php echo $instance['width']; ?>" />
    <br /><i><?php _e('Examples: 300px,100%,auto', 'currency-switcher') ?></i>
</p>


<p>
    <?php
    $checked = "";
    if ($instance['show_flags'] == 'true')
    {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo $widget->get_field_id('show_flags'); ?>" name="<?php echo $widget->get_field_name('show_flags'); ?>" value="true" <?php echo $checked; ?> />
    <label for="<?php echo $widget->get_field_id('show_flags'); ?>"><?php _e('Show flags', 'currency-switcher') ?>:</label>
</p>



<p>
    <label for="<?php echo $widget->get_field_id('flag_position'); ?>"><?php _e('Flag position', 'currency-switcher') ?>:</label>
    <?php
    $sett = array(
        'right' => __('right', 'currency-switcher'),
        'left' => __('left', 'currency-switcher'),
    );
    ?>
    <select class="widefat" id="<?php echo $widget->get_field_id('flag_position') ?>" name="<?php echo $widget->get_field_name('flag_position') ?>">
        <?php foreach ($sett as $k => $val) : ?>
            <option <?php selected($instance['flag_position'], $k) ?> value="<?php echo $k ?>" class="level-0"><?php echo $val ?></option>
        <?php endforeach; ?>
    </select>
    <i><?php _e('For ddslick skin only!', 'currency-switcher') ?></i>
</p>



<p>
    <label for="<?php echo $widget->get_field_id('txt_type'); ?>"><?php _e('Drop-down options text type', 'currency-switcher') ?>:</label>
    <?php
    $sett = array(
        'code' => __('code', 'currency-switcher'),
        'desc' => __('description', 'currency-switcher'),
    );
    ?>
    <select class="widefat" id="<?php echo $widget->get_field_id('txt_type') ?>" name="<?php echo $widget->get_field_name('txt_type') ?>">
        <?php foreach ($sett as $k => $val) : ?>
            <option <?php selected($instance['txt_type'], $k) ?> value="<?php echo $k ?>" class="level-0"><?php echo $val ?></option>
        <?php endforeach; ?>
    </select>
    <i><?php _e('Which text display in the drop-down options - currency code OR description text. Looks good for all dropdowns except ddslick.', 'currency-switcher') ?></i>
</p>

