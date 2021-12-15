<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
//*** hide if there is checkout page
global $post;

//***

$drop_down_view = $this->get_drop_down_view();
$all_currencies = apply_filters('wpcs_currency_manipulation_before_show', $this->get_currencies());

//***
if ($drop_down_view == 'flags')
{
    foreach ($all_currencies as $key => $currency)
    {
        if (!empty($currency['flag']))
        {
            ?>
            <a href="#" class="wpcs_flag_view_item <?php if ($this->current_currency == $key): ?>wpcs_flag_view_item_current<?php endif; ?>" data-currency="<?php echo $currency['name'] ?>" title="<?php echo $currency['name'] . ', ' . $currency['symbol'] . ' ' . $currency['description'] ?>"><img src="<?php echo $currency['flag'] ?>" alt="<?php echo $currency['name'] . ', ' . $currency['symbol'] ?>" /></a>
            <?php
        }
    }
} else
{
    $empty_flag = WPCS_LINK . 'img/no_flag.png';
    $show_money_signs = $this->get_option('wpcs_show_money_signs', 1);
//***
    if (!isset($show_flags))
    {
        $show_flags = $this->get_option('wpcs_show_flags', 1);
    }



    if (!isset($width))
    {
        $width = '100%';
    }

    if (!isset($flag_position))
    {
        $flag_position = 'right';
    }
    ?>


    <?php if ($drop_down_view == 'wselect'): ?>
        <style type="text/css">
            .currency-switcher-form .wSelect, .currency-switcher-form .wSelect-options-holder {
                width: <?php echo $width ?> !important;
            }
            <?php if (!$show_flags): ?>
                .currency-switcher-form .wSelect-option-icon{
                    padding-left: 5px !important;
                }
            <?php endif; ?>
        </style>
    <?php endif; ?>





    <form method="post" action="" class="currency-switcher-form <?php if ($show_flags): ?>wpcs_show_flags<?php endif; ?>">
        <input type="hidden" name="currency-switcher" value="<?php echo $this->current_currency ?>" />
        <select name="currency-switcher" style="width: <?php echo $width ?>;" data-width="<?php echo $width ?>" data-flag-position="<?php echo $flag_position ?>" class="currency-switcher" onchange="wpcs_redirect(this.value);
                void(0);">
                    <?php foreach ($all_currencies as $key => $currency) : ?>

                <?php
                $option_txt = $currency['name'];

                if ($show_money_signs)
                {
                    $option_txt.=', ' . $currency['symbol'];
                }
                //***
                if (isset($txt_type))
                {
                    if ($txt_type == 'desc')
                    {
                        if (!empty($currency['description']))
                        {
                            $option_txt = $currency['description'];
                        }
                    }
                }
                ?>

                <option <?php if ($show_flags) : ?>style="background: url('<?php echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>') no-repeat 99% 0; background-size: 30px 20px;"<?php endif; ?> value="<?php echo $key ?>" <?php selected($this->current_currency, $key) ?> data-imagesrc="<?php if ($show_flags) echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>" data-icon="<?php if ($show_flags) echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>" data-description="<?php echo $currency['description'] ?>"><?php echo $option_txt ?></option>
            <?php endforeach; ?>
        </select>
        <div style="display: none;">WPCS <?php echo WPCS_VERSION ?></div>
    </form>
    <?php
}
