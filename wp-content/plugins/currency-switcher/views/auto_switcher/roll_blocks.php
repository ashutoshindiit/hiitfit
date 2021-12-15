<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
global $WPCS;
$currencies = apply_filters('wpcs_currency_manipulation_before_show', $WPCS->get_currencies());
?>
<style>
    .wpcs_auto_switcher {
        top: <?php echo $top ?>;
    } 
    .wpcs_auto_switcher ul li:hover {
        background: <?php echo $hover_color ?>;
    }
    nav.wpcs_auto_switcher ul li {
        background: <?php echo $color ?>;
    }
    .wpcs_auto_switcher ul li.wpcs_auto_bg_wpcs_curr_curr {
        background: <?php echo $hover_color ?>;
    }

</style>
<nav class="wpcs_auto_switcher <?php echo $side ?>" data-view="roll_blocks">
    <ul>
        <?php
        foreach ($currencies as $key => $item):
            $current = "";
            if ($key == $WPCS->current_currency) {
                $current = "wpcs_curr_curr";
            }
            $base_text = $this->prepare_field_text($item, $basic_field);
            $add_text = $this->prepare_field_text($item, $add_field);
            ?>  
            <li class="wpcs_auto_bg_<?php echo $current ?>">
                <a data-currency="<?php echo $key ?>" class="  <?php echo $current ?> wpcs_auto_switcher_link" href="#"> 
                    <?php
                    $r_add_text = "";
                    if ($side == 'right') {
                        $r_add_text = $add_text;
                        $add_text = "";
                    }
                    ?>
                    <?php echo $add_text ?> 
                    <span class="wpcs_add_field"><?php echo $base_text ?> </span>
                    <?php echo $r_add_text ?> 
                </a> 
            </li>
        <?php endforeach; ?>
    </ul>
    <style type="text/css">
        .wpcs_auto_switcher ul li:hover {
            -webkit-transform: translate(<?php echo $roll_px ?>px, 0);
            -moz-transform: translate(<?php echo $roll_px ?>px, 0);
            -ms-transform: translate(<?php echo $roll_px ?>px, 0);
            -o-transform: translate(<?php echo $roll_px ?>px, 0);
            transform: translate(<?php echo $roll_px ?>px, 0);
        }

        .wpcs_auto_switcher.right ul li:hover {
            -webkit-transform: translate(-<?php echo $roll_px ?>px, 0);
            -moz-transform: translate(-<?php echo $roll_px ?>px, 0);
            -ms-transform: translate(-<?php echo $roll_px ?>px, 0);
            -o-transform: translate(-<?php echo $roll_px ?>px, 0);
            transform: translate(-<?php echo $roll_px ?>px, 0);
        }
    </style>
</nav>