<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="wpcs-admin-preloader"></div>
<form method="post" action="" style="display: none;" id="wpcs_form">
    <div class="subsubsub_section">
        <br class="clear" />
        <?php
        $welcome_curr_options = array();
        if (!empty($currencies) AND is_array($currencies)) {
            foreach ($currencies as $key => $currency) {
                $welcome_curr_options[$currency['name']] = $currency['name'];
            }
        }

        if ($this->is_use_geo_rules()) {
            $gi = $this->get_geoip_object();
            //include_once WPCS_PATH .'lib/geo-ip/geoip.inc';
            //$gi = geoip_open(WPCS_PATH .'lib/GeoIP.dat', GEOIP_MEMORY_CACHE);
            $countries = array();
            foreach ($gi->GEOIP_COUNTRY_CODE_TO_NUMBER as $key => $var) {
                if ($var === 0 OR empty($key))
                    continue;
                $countries[$key] = $gi->GEOIP_COUNTRY_NAMES[$var];
            }
            geoip_close($gi);
        }

        $aggregators = array(
            'yahoo' => __('http://finance.yahoo.com', 'currency-switcher'),
            //'google' => __('http://google.com/finance', 'currency-switcher'),
            'free_ecb' => 'The Free Currency Converter by European Central Bank',
            'micro' => 'Micro pyramid',
            'rf' => __('http://www.cbr.ru - russian centrobank', 'currency-switcher'),
            'privatbank' => 'api.privatbank.ua - ukrainian privatbank',
            'bank_polski' => 'Narodowy Bank Polsky',
            'free_converter' => 'The Free Currency Converter',
            'fixer' => 'Fixer',
            'cryptocompare' => 'CryptoCompare',
            //'xe' => 'XE Currency Converter'
            'ron' => 'www.bnr.ro',
            'currencylayer' => 'Сurrencylayer',
            'openexchangerates' => 'Open exchange rates',
        );
        $aggregators = apply_filters('wpcs_announce_aggregator', $aggregators);
        //+++
        $options = array(
            array(
                'name' => __('Drop-down view', 'currency-switcher'),
                'desc' => __('How to display currency switcher drop-down on the front of your site', 'currency-switcher'),
                'id' => 'wpcs_drop_down_view',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    'ddslick' => __('ddslick', 'currency-switcher'),
                    'chosen' => __('chosen', 'currency-switcher'),
                    'chosen_dark' => __('chosen dark', 'currency-switcher'),
                    'wselect' => __('wSelect', 'currency-switcher'),
                    'no' => __('simple drop-down', 'currency-switcher'),
                    'flags' => __('show as flags', 'currency-switcher'),
                ),
                'default' => 'ddslick'
            ),
            array(
                'name' => __('Show flags by default', 'currency-switcher'),
                'desc' => __('Show/hide flags on the front drop-down', 'currency-switcher'),
                'id' => 'wpcs_show_flags',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'currency-switcher'),
                    1 => __('Yes', 'currency-switcher')
                ),
                'default' => 1
            ),
            array(
                'name' => __('Show money signs', 'currency-switcher'),
                'desc' => __('Show/hide money signs on the front drop-down', 'currency-switcher'),
                'id' => 'wpcs_show_money_signs',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'currency-switcher'),
                    1 => __('Yes', 'currency-switcher')
                ),
                'default' => 1
            ),
            array(
                'name' => __('Show price info icon', 'currency-switcher'),
                'desc' => __('Show info icon near the price of the product which while its under hover shows prices of products in all currencies', 'currency-switcher'),
                'id' => 'wpcs_price_info',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'currency-switcher'),
                    1 => __('Yes', 'currency-switcher')
                ),
                'default' => 0
            ),
            /*
              array(
              'name' => __('Is multiple allowed', 'currency-switcher'),
              'desc' => __('Customer will pay with selected currency or with default currency.', 'currency-switcher'),
              'id' => 'wpcs_is_multiple_allowed',
              'type' => 'select',
              'class' => 'chosen_select',
              'css' => 'min-width:300px;',
              'options' => array(
              0 => __('No', 'currency-switcher'),
              1 => __('Yes', 'currency-switcher')
              ),
              'default' => 0
              ),
             */
            array(
                'name' => __('Welcome currency', 'currency-switcher'),
                'desc' => __('In wich currency show prices for first visit of your customer on your site', 'currency-switcher'),
                'id' => 'wpcs_welcome_currency',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => $welcome_curr_options,
                'default' => 1
            ),
            array(
                'name' => __('Currency aggregator', 'currency-switcher'),
                'desc' => __('Currency aggregators. Note: XE Currency Converter doesnt work with crypto-currency such as BTC!', 'currency-switcher'),
                'id' => 'wpcs_currencies_aggregator',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => $aggregators,
                'default' => 'free_converter'
            ),
            array(
                'name' => esc_html__('Aggregator API key', 'currency-switcher'),
                'desc' => esc_html__('Some aggregators require an API key. See the hint below how to get it!', 'currency-switcher'),
                'id' => 'wpcs_aggregator_key',
                'type' => 'text',
                'std' => '', // WooCommerce < 2.0
                'default' => '', // WooCommerce >= 2.0
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            /*
              array(
              'name' => __('CURL for aggregators', 'currency-switcher'),
              'desc' => __('You can use it if aggregators doesn works with file_get_contents function because of security reasons. If all is ok leave it No!', 'currency-switcher'),
              'id' => 'wpcs_use_curl',
              'type' => 'select',
              'class' => 'chosen_select',
              'css' => 'min-width:300px;',
              'options' => array(
              0 => __('No', 'currency-switcher'),
              1 => __('Yes', 'currency-switcher')
              ),
              'default' => 1
              ),
             */
            array(
                'name' => __('Currency storage', 'currency-switcher'),
                'desc' => __('In some servers there is troubles with sessions, and after currency selecting its reset to welcome currency or geo ip currency. In such case use transient!', 'currency-switcher'),
                'id' => 'wpcs_storage',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    'session' => __('session', 'currency-switcher'),
                    'transient' => __('transient', 'currency-switcher')
                ),
                'default' => 'transient'
            ),
//              array(
//              'name' => __('Use GeoLocation', 'currency-switcher'),
//              'desc' => __('Use GeoLocation rules for your currencies.', 'currency-switcher'),
//              'id' => 'wpcs_use_geo_rules',
//              'type' => 'select',
//              'class' => 'chosen_select',
//              'css' => 'min-width:300px;',
//              'options' => array(
//              0 => __('No', 'currency-switcher'),
//              1 => __('Yes', 'currency-switcher')
//              ),
//              'default' => 0
//              ),
            array(
                'name' => __('Rate auto update', 'currency-switcher'),
                'desc' => __('Currencies rate auto update by wp cron', 'currency-switcher'),
                'id' => 'wpcs_currencies_rate_auto_update',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    'no' => __('no auto update', 'currency-switcher'),
                    'hourly' => __('hourly', 'currency-switcher'),
                    'twicedaily' => __('twicedaily', 'currency-switcher'),
                    'daily' => __('daily', 'currency-switcher'),
                    'week' => __('weekly', 'currency-switcher'),
                    'month' => __('monthly', 'currency-switcher')
                ),
                'default' => 'twicedaily'
            ),
            array(
                'name' => __('I am using cache plugin on my site', 'currency-switcher'),
                'desc' => __('Set Yes here ONLY if you are REALLY use cache plugin for your site, for example like Super cache or Hiper cache (doesn matter). After enabling this feature - clean your cache to make it works. It will allow show prices in selected currency on all pages of site. Fee for this feature - additional AJAX queries for products prices redrawing.', 'currency-switcher'),
                'id' => 'wpcs_shop_is_cached',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'currency-switcher'),
                    1 => __('Yes', 'currency-switcher'),
                ),
                'default' => 0
            ),
            array(
                'name' => __('Custom money signs. <!-- <span style="color: #ff0000;">This feature is disabled.</span> -->', 'currency-switcher'),
                'desc' => __('Add your money symbols in your shop.<br />Example: $USD,AAA,AUD$,DDD - separated by commas', 'currency-switcher'),
                'id' => 'wpcs_customer_signs',
                'type' => 'textarea',
                'css' => 'min-width:500px;',
                'default' => ''
            ),
            array(
                'name' => __('Custom price format', 'currency-switcher'),
                'desc' => __('Set your format how to display price on front.<br />Use keys: __CODE__,__PRICE__. Leave it empty to use default format. Example: __PRICE__ (__CODE__)', 'currency-switcher'),
                'id' => 'wpcs_customer_price_format',
                'type' => 'text',
                'css' => 'min-width:500px;',
                'default' => ''
            ),
            array(
                'name' => __('Decimal separator', 'currency-switcher'),
                'desc' => __('Decimal separator', 'currency-switcher'),
                'id' => 'wpcs_decimal_separator',
                'type' => 'text',
                'css' => 'min-width:500px;',
                'default' => '.'
            ),
            array(
                'name' => __('Thousandth separator', 'currency-switcher'),
                'desc' => __('Thousandth separator', 'currency-switcher'),
                'id' => 'wpcs_thousandth_separator',
                'type' => 'text',
                'css' => 'min-width:500px;',
                'default' => ','
            ),
            array(
                'name' => __('Show options button on top admin bar', 'currency-switcher'),
                'desc' => __('Show options button on top admin bar.', 'currency-switcher'),
                'id' => 'wpcs_show_top_button',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'currency-switcher'),
                    1 => __('Yes', 'currency-switcher')
                ),
                'default' => 0
            )
        );
        ?>


        <div class="wpcs-section">



            <div style="clear: both; overflow: hidden;">
                <div style="float: left;">
                    <h2 class="wpcs_settings_version"><?php printf(__('WordPress Currency Switcher v.%s', 'currency-switcher'), WPCS_VERSION) ?></h2>
                </div>

                <?php if (time() < strtotime('1st July 2020 14:00')): ?>
                    <div style="float: right;">
                        <a href="https://pluginus.net/affiliate/wordpress-currency-switcher" title="Envato Mid Year Sale 2020 - 30-50% discount. Sale ends 1st July 2020!" target="_blank"><img height="60" src="<?php echo WPCS_LINK ?>img/envato-mid-sale-2020.png" alt="" /></a>
                    </div>
                <?php endif; ?>
            </div>

            <svg class="hidden">
            <defs>
            <path id="tabshape" d="M80,60C34,53.5,64.417,0,0,0v60H80z"/>
            </defs>
            </svg>

            <div id="tabs" class="wpcs-tabs wpcs-tabs-style-shape">

                <nav>
                    <ul>
                        <li class="tab-current">
                            <a href="#tabs-1">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php _e("Currencies", 'currency-switcher') ?></span>
                            </a>
                        </li>

                        <li>
                            <a href="#tabs-2">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php _e("Options", 'currency-switcher') ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="#tabs-5">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php _e("Side switcher", 'currency-switcher') ?></span>
                            </a>
                        </li>
                        <?php if ($this->is_use_geo_rules()): ?>
                            <li>
                                <a href="#tabs-3">
                                    <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                    <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                    <span><?php _e("GeoIP rules", 'currency-switcher') ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="#tabs-4">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php _e("Info Help", 'currency-switcher') ?></span>
                            </a>
                        </li>

                    </ul>
                </nav>

                <div class="content-wrap">

                    <section id="tabs-1" class="content-current">

                        <div class="wpcs-control-section">


                            <a href="#" class="wpcs_button dashicons-before dashicons-plus" id="wpcs_add_currency">&nbsp;<?php _e("Add currency", 'currency-switcher') ?></a>&nbsp;
                            <a href="javascript: jQuery('.wpcs_is_etalon:checked').trigger('click');void(0);" class="wpcs_button dashicons-before dashicons-update">&nbsp;<?php _e("Update all rates", 'currency-switcher') ?></a>&nbsp;
                            <a href="javascript: wpcs_add_money_sign();void(0);" class="wpcs_button dashicons-before dashicons-plus">&nbsp;<?php _e("Add custom money sign", 'currency-switcher') ?></a><br />



                            <div style="display: none;">
                                <div id="wpcs_item_tpl"><?php
                                    $empty = array(
                                        'name' => '',
                                        'rate' => 0,
                                        'symbol' => '',
                                        'position' => '',
                                        'is_etalon' => 0,
                                        'description' => '',
                                        'hide_cents' => 0
                                    );
                                    wpcs_print_currency($this, $empty);
                                    ?>
                                </div>
                            </div>

                            <ul id="wpcs_list">
                                <?php
                                if (!empty($currencies) AND is_array($currencies)) {
                                    foreach ($currencies as $key => $currency) {
                                        wpcs_print_currency($this, $currency);
                                    }
                                }
                                ?>
                            </ul><br />




                            <b style="color:red;"><?php _e('Hint', 'currency-switcher'); ?>:</b>&nbsp;<?php _e('To update all currencies rates by one click - press radio button of the basic currency and then press "Save changes" button!', 'currency-switcher'); ?><br />
                            <b style="color:red;" ><?php esc_html_e('Hint', 'currency-switcher'); ?>:</b>&nbsp;<?php esc_html_e('To get free API key for:', 'currency-switcher'); ?>
                            &nbsp;<a href="https://free.currencyconverterapi.com/free-api-key"  target="_blank"><?php esc_html_e('The Free Currency Converter', 'currency-switcher'); ?></a>
                            <?php esc_html_e('OR', 'currency-switcher'); ?>
                            &nbsp;<a href="https://fixer.io/signup/free" target="_blank"><?php esc_html_e('Fixer', 'currency-switcher'); ?></a><br />
                        </div>

                        <br />
                        <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button button-primary"><?php _e("Read wiki about Currency Active codes  <-  Get right currencies codes here if you are not sure about it!", 'currency-switcher') ?></a><br />


                    </section>



                    <section id="tabs-2">


                        <?php foreach ($options as $option): ?>

                            <div class="wpcs-control-section">

                                <h4><?php echo $option['name'] ?></h4>

                                <div class="wpcs-control-container">
                                    <div class="wpcs-control">

                                        <?php
                                        switch ($option['type']) {
                                            case 'select':

                                                $val = $option['default'];
                                                if (isset($this->options[$option['id']])) {
                                                    $val = $this->options[$option['id']];
                                                }
                                                ?>
                                                <div class="select-wrap">
                                                    <select name="wpcs_settings[<?php echo $option['id'] ?>]" class="chosen_select">
                                                        <?php foreach ($option['options'] as $key => $name) : ?>
                                                            <option value="<?php echo $key; ?>" <?php if ($val == $key): ?>selected="selected"<?php endif; ?>><?php echo $name; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <?php
                                                break;

                                            case 'textarea':

                                                $val = $option['default'];
                                                if (isset($this->options[$option['id']])) {
                                                    $val = $this->options[$option['id']];
                                                }
                                                ?>

                                                <textarea class="wide" style="height: 200px; width: 100%;" name="wpcs_settings[<?php echo $option['id'] ?>]"><?php echo $val ?></textarea>


                                                <?php
                                                break;

                                            default:
                                                $val = $option['default'];
                                                if (isset($this->options[$option['id']])) {
                                                    $val = $this->options[$option['id']];
                                                }
                                                ?>

                                                <input type="text" class="wide" value="<?php echo $val ?>" name="wpcs_settings[<?php echo $option['id'] ?>]" />


                                                <?php
                                                break;
                                        }
                                        ?>




                                    </div>
                                    <div class="wpcs-description"><?php echo $option['desc'] ?></div>
                                </div>

                            </div><!--/ .wpcs-control-section-->

                        <?php endforeach; ?>



                        <?php do_action('wpcs_print_design_additional_options'); ?>

                    </section>
                    <section id="tabs-5">
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_is_auto_switcher"><?php _e('Show fixed switcher', 'currency-switcher') ?></label>                                     
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $opts = array(
                                            0 => __('No', 'currency-switcher'),
                                            1 => __('Yes', 'currency-switcher')
                                        );
                                        $wpcs_is_auto_switcher = (isset($this->options['is_auto_switcher'])) ? $this->options['is_auto_switcher'] : 0;
                                        ?>
                                        <select name="wpcs_settings[is_auto_switcher]" id="wpcs_is_auto_switcher" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Show/Hide', 'currency-switcher') ?>">

                                            <?php foreach ($opts as $val => $title): ?>
                                                <option value="<?php echo $val ?>" <?php echo selected($wpcs_is_auto_switcher, $val) ?>><?php echo $title ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                        <div class="wpcs-description"><?php _e('Show/Hide the side currency switcher on your page', 'currency-switcher') ?></div>
                                    </td>
                                </tr>

                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_skin"><?php _e('Skin', 'currency-switcher') ?></label>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $opts = array(
                                            'classic_blocks' => __('Classic blocks', 'currency-switcher'),
                                            'roll_blocks' => __('Roll blocks', 'currency-switcher'),
                                            'round_select' => __('Round select', 'currency-switcher'),
                                        );
                                        $wpcs_auto_switcher_skin = (isset($this->options['auto_switcher_skin'])) ? $this->options['auto_switcher_skin'] : 'classic_blocks';
                                        ?>
                                        <select name="wpcs_settings[auto_switcher_skin]" id="wpcs_auto_switcher_skin" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Choise skin', 'currency-switcher') ?>">

                                            <?php foreach ($opts as $val => $title): ?>
                                                <option value="<?php echo $val ?>" <?php echo selected($wpcs_auto_switcher_skin, $val) ?>><?php echo $title ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="wpcs-description"><?php _e("Style of the fixed switcher", 'currency-switcher') ?></div>
                                        <div class="wpcs_roll_blocks_wight" <?php if ($wpcs_auto_switcher_skin != 'roll_blocks'): ?>style="display: none;"<?php endif; ?>>
                                            <?php
                                            $wpcs_auto_switcher_roll_px = (isset($this->options['auto_switcher_roll_px'])) ? $this->options['auto_switcher_roll_px'] : 90;
                                            ?>
                                            <input type="text" name="wpcs_settings[auto_switcher_roll_px]"  id="wpcs_auto_switcher_roll_px" style="min-width: 100px;" value="<?php echo $wpcs_auto_switcher_roll_px ?>" >
                                            <label for="wpcs_auto_switcher_roll_px">px;<?php _e('How much to roll. ', 'currency-switcher') ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_side"><?php _e('Side', 'currency-switcher') ?></label>

                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $opts = array(
                                            'left' => __('Left', 'currency-switcher'),
                                            'right' => __('Right', 'currency-switcher'),
                                        );
                                        $wpcs_auto_switcher_side = (isset($this->options['auto_switcher_side'])) ? $this->options['auto_switcher_side'] : 'left';
                                        ?>
                                        <select name="wpcs_settings[auto_switcher_side]" id="wpcs_auto_switcher_side" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Choise side', 'currency-switcher') ?>">

                                            <?php foreach ($opts as $val => $title): ?>
                                                <option value="<?php echo $val ?>" <?php echo selected($wpcs_auto_switcher_side, $val) ?>><?php echo $title ?></option>
                                            <?php endforeach; ?>
                                        </select>      
                                        <div class="wpcs-description"><?php _e("The side where the switcher should be placed", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_top_margin"><?php _e('Top margin', 'currency-switcher') ?></label>

                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_top_margin = (isset($this->options['auto_switcher_top_margin'])) ? $this->options['auto_switcher_top_margin'] : '100px';
                                        ?>
                                        <input type="text" name="wpcs_settings[auto_switcher_top_margin]" id="wpcs_auto_switcher_top_margin" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_top_margin ?>" >
                                        <div class="wpcs-description"><?php _e("Distance from the top of the screen", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_color"><?php _e('Main color', 'currency-switcher') ?></label>

                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_color = (isset($this->options['auto_switcher_color'])) ? $this->options['auto_switcher_color'] : '#222222';
                                        ?>
                                        <input class="color-field" type="text" name="wpcs_settings[auto_switcher_color]" id="wpcs_auto_switcher_color" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_color ?>" >
                                        <div class="wpcs-description"><?php _e("Main color of the switcher", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_hover_color"><?php _e('Hover color', 'currency-switcher') ?></label>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_hover_color = (isset($this->options['auto_switcher_hover_color'])) ? $this->options['auto_switcher_hover_color'] : '#3b5998';
                                        ?>
                                        <input class="color-field" type="text" name="wpcs_settings[auto_switcher_hover_color]" id="wpcs_auto_switcher_hover_color" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_hover_color ?>" >
                                        <div class="wpcs-description"><?php _e("The switcher color when mouse hovering", 'currency-switcher') ?></div>
                                    </td>
                                </tr> 
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_basic_field"><?php _e('Basic field', 'currency-switcher') ?></label>

                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_basic_field = (isset($this->options['auto_switcher_basic_field'])) ? $this->options['auto_switcher_basic_field'] : '__CODE__ __SIGN__';
                                        ?>
                                        <input type="text" name="wpcs_settings[auto_switcher_basic_field]" id="wpcs_auto_switcher_basic_field" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_basic_field ?>" >
                                        <div class="wpcs-description"><?php _e("Variants:  __CODE__ __FLAG__ __SIGN__ __DESCR__", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_additional_field"><?php _e('Additional field', 'currency-switcher') ?></label>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_additional_field = (isset($this->options['auto_switcher_additional_field'])) ? $this->options['auto_switcher_additional_field'] : '__DESCR__';
                                        ?>
                                        <input type="text" name="wpcs_settings[auto_switcher_additional_field]" id="wpcs_auto_switcher_additional_field" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_additional_field ?>" >
                                        <div class="wpcs-description"><?php _e("Variants:  __CODE__ __FLAG__ __SIGN__ __DESCR__", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_show_page"><?php _e('Show on the pages', 'currency-switcher') ?></label>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_show_page = (isset($this->options['auto_switcher_show_page'])) ? $this->options['auto_switcher_show_page'] : '';
                                        ?>
                                        <input type="text" name="wpcs_settings[auto_switcher_show_page]" id="wpcs_auto_switcher_show_page" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_show_page ?>" >
                                        <div class="wpcs-description"><?php _e("Show switcher on the selected pages. Set IDs of these pages, example: 28,34,232 OR use special words: home,front_page", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_switcher_hide_page"><?php _e('Hide on the pages', 'currency-switcher') ?></label>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $wpcs_auto_switcher_hide_page = (isset($this->options['auto_switcher_hide_page'])) ? $this->options['auto_switcher_hide_page'] : '';
                                        ?>
                                        <input type="text" name="wpcs_settings[auto_switcher_hide_page]" id="wpcs_auto_switcher_hide_page" style="min-width: 300px;" value="<?php echo $wpcs_auto_switcher_hide_page ?>" >
                                        <div class="wpcs-description"><?php _e("Hide switcher on the selected pages. Set IDs of these pages, example: 28,34,232 OR use special words: home,front_page", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                                <tr valign="top" <?php if (!$wpcs_is_auto_switcher): ?>style="display: none;"<?php endif; ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="wpcs_auto_mobile_show"><?php _e('Behavior for devices', 'currency-switcher') ?></label>

                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $mobile = array(
                                            0 => __('Show on all devices', 'currency-switcher'),
                                            '1' => __('Show on mobile devices only', 'currency-switcher'),
                                            '2' => __('Hide on mobile devices', 'currency-switcher'),
                                        );
                                        $wpcs_auto_mobile_show = (isset($this->options['auto_mobile_show'])) ? $this->options['auto_mobile_show'] : 0;
                                        ?>
                                        <select name="wpcs_settings[auto_mobile_show]" id="wpcs_auto_mobile_show" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Choise side', 'currency-switcher') ?>">
                                            <?php foreach ($mobile as $val => $title): ?>
                                                <option value="<?php echo $val ?>" <?php echo selected($wpcs_auto_mobile_show, $val) ?>><?php echo $title ?></option>
                                            <?php endforeach; ?>
                                        </select>      
                                        <div class="wpcs-description"><?php _e("Show or hide on mobile device. (high priority)", 'currency-switcher') ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>                        
                    </section>

                    <?php
                    if ($this->is_use_geo_rules()) {
                        ?>
                        <section id="tabs-3">

                            <ul>
                                <?php
                                if (!empty($currencies) AND is_array($currencies)) {

                                    foreach ($currencies as $key => $currency) {

                                        $rules = array();
                                        if (isset($this->options['wpcs_geo_rules'][$key])) {
                                            $rules = $this->options['wpcs_geo_rules'][$key];
                                        }
                                        ?>
                                        <li>
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td>
                                                        <div style="width: 70px;<?php if ($currency['is_etalon']): ?>color: red;<?php endif; ?>"><strong><?php echo $key ?></strong>:</div>
                                                    </td>
                                                    <td style="width: 100%; padding-bottom: 7px;">

                                                        <select name="wpcs_settings[wpcs_geo_rules][<?php echo $currency['name'] ?>][]" data-placeholder="<?php _e("Choose a country...", 'currency-switcher') ?>" multiple="" size="1" style="width: 100%;" class="chosen_select_geo">
                                                            <option value="0"></option>
                                                            <?php foreach ($countries as $key => $value): ?>
                                                                <option <?php echo(in_array($key, $rules) ? 'selected=""' : '') ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                                                            <?php endforeach; ?>
                                                        </select><br />
                                                    </td>
                                                </tr>
                                            </table>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                            </ul>
                            <?php echo do_shortcode('[wpcs_check_country]'); ?>
                        </section>
                        <?php
                    } else {
                        ?><input type="hidden" name="wpcs_settings[wpcs_geo_rules]" value="" /><?php }
                    ?>
                    <section id="tabs-4">

                        <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button button-primary"><?php _e("Use currency codes from the next wiki-page to avoid rates auto update misfunctionality", 'currency-switcher') ?></a>&nbsp;
                        <a href="https://pluginus.net/support/forum/wpcs-wordpress-currency-switcher/" target="_blank" class="button button-primary"><?php _e("Support", 'currency-switcher') ?></a><br />

                        <ul>
                            <li>
                                <h3>Shortcodes</h3>
                                <ol>
                                    <li>
                                        <b>[wpcs show_flags=0 width='300px' txt_type='desc']</b> - displays currency switcher drop-down<br />
                                        <ul>
                                            <li>
                                                <i>show_flags</i> - show/hide flags. Values is 1 for show, 0 for hide
                                            </li>
                                            <li>
                                                <i>width</i> - width of the drop-down. Examples: 300px, 50%
                                            </li>
                                            <li>
                                                <i>txt_type</i> - what should be displayed in the options of the drop-down: currency code OR currency description! 2 possible values: code,desc
                                            </li>
                                        </ul>
                                        <br />
                                    </li>
                                    <li>
                                        <a href="https://wordpress.currency-switcher.com/shortcode/wpcs_price/" target="_blank" style="color:red; text-decoration: none; font-weight: bold;">[wpcs_price value=20]</a> - main shortcode, always use this if you want to display any price OR amount in the content of your site. value == price, or any amount by your logic<br />

                                        <ul>
                                            <li>
                                                <i>value</i> - price, amount or any by your logic. Decimal or integer.
                                            </li>
                                            <li>
                                                <i>meta_value</i> - price amount taken directly from a meta field, this field has more priority than attribute 'value' if they presented together in the shortcode. Decimal or integer. Example: <i>[wpcs_price meta_value=my_price_field]</i>. Use point for decimals, example: 100.55
                                            </li>
                                        </ul>
                                        <br />
                                    </li>
                                    <li>
                                        <b>[wpcs_code_rate code=USD]</b> - display currency rate related to the basic currency<br />
                                        <ul>
                                            <li>
                                                <i>code</i> - code of currency which rate you want to show
                                            </li>
                                        </ul>
                                        <br />
                                    </li>
                                    <li>
                                        <b>[wpcs_converter exclude="GBP,USD" precision=2]</b> - displays currency AJAX currency converter<br />
                                        <ul>
                                            <li>
                                                <i>exclude</i> - write here using comma currencies you want to exclude from converter
                                            </li>
                                            <li>
                                                <i>precision</i> - decimals, digits after comma in the converter amount
                                            </li>
                                        </ul>
                                        <br />
                                    </li>
                                    <li>
                                        <b>[wpcs_rates exclude="GBP,USD" precision=4]</b> - displays currency AJAX informer<br />
                                        <ul>
                                            <li>
                                                <i>exclude</i> - write here using comma currencies you want to exclude from the informer
                                            </li>
                                            <li>
                                                <i>precision</i> - decimals, digits after comma in the converter amount
                                            </li>
                                        </ul>
                                        <br />
                                    </li>
                                    <li>
                                        <b>[wpcs_current_currency text="" flag=1 code=1]</b> - displays current currency information<br />
                                        <ul>
                                            <li>
                                                <i>text</i> - write any text there or word 'none'
                                            </li>
                                            <li>
                                                <i>flag</i> - display or no (1/0) the flag of the currency
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>[wpcs_check_country]</b> - displays current country information, just for info<br />
                                        <ul>
                                            <li>
                                                no attributes
                                            </li>
                                        </ul>
                                    </li>
                                </ol>
                            </li>
                            <li>
                                <h3>FAQ</h3>
                                <ul>
                                    <li>

                                        R: Where can I get flags?<br />
                                        A: In google or <a href="http://www.free-country-flags.com/flag_packs.php" target="_blank">here</a>

                                    </li>

                                    <li>
                                        R: Documentation?<br />
                                        A: <a href="https://wordpress.currency-switcher.com/category/faq/" target="_blank">here</a>
                                    </li>

                                    <li>
                                        R: Demo page?<br />
                                        A: <a href="https://wordpress.currency-switcher.com/wordpress-currency-switcher-action/" target="_blank">here</a>
                                    </li>

                                    <li>
                                        <iframe width="560" height="315" src="https://www.youtube.com/embed/1CLRP_tDj0k" frameborder="0" allowfullscreen></iframe>
                                    </li>


                                </ul>
                            </li>

                        </ul>

                        <br />

                        <hr />

                        <br />


                        <a href="https://currency-switcher.com/a/buy" target="_blank"><img src="<?php echo WPCS_LINK ?>img/woocs_banner.png" width="250" /></a>
                        &nbsp;<a href="https://products-filter.com/a/buy" target="_blank"><img src="<?php echo WPCS_LINK ?>img/woof_banner.png" width="250" /></a>
                        &nbsp;<a href="https://bulk-editor.com/a/buy" target="_blank"><img src="<?php echo WPCS_LINK ?>/img/woobe_banner.png" alt="" width="250" /></a>
                        &nbsp;<a href="https://wp-filter.com/a/buy" target="_blank"><img src="<?php echo WPCS_LINK ?>/img/mdtf_banner.jpg" alt="" width="250" /></a>

                        </ul>



                    </section>



                </div>

                <div class="wpcs_settings_powered">
                    <a href="https://pluginus.net/" target="_blank">Powered by www.pluginus.net</a>
                </div>


            </div>



        </div>



        <?php if ($this->notes_for_free): ?>
            <hr />

            <div style="font-style: italic;">In the free version of the plugin <b style="color: red;">you can operate with 2 ANY currencies only</b>. If you need more currencies you can make <a href="https://wordpress.currency-switcher.com/a/buy" target="_blank">upgrade to the premium version of the plugin</a></div><br />

            <table style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 20%;">
                            <h3 style="color: red;">Get the full version:</h3>
                            <a href="https://wordpress.currency-switcher.com/a/buy" target="_blank" title=""><img width="200" src="<?php echo WPCS_LINK ?>img/wpcs_banner.png" alt="full version of the plugin"></a>
                        </td>
                        <td style="width: 20%;">
                            <h3 style="color: red;">&nbsp;</h3>
                            <a href="https://www.currency-switcher.com/a/buy" target="_blank" title="WOOCS - WooCommerce Currency Switcher"><img width="200" src="<?php echo WPCS_LINK ?>img/woocs_banner.png" alt="WooCommerce Currency Switcher"></a>
                        </td>

                        <td style="width: 20%;">
                            <h3 style="color: red;">&nbsp;</h3>
                            <a href="https://bulk-editor.com/a/buy" target="_blank" title="WOOBE - WooCommerce Bulk Editor Professional"><img width="200" src="<?php echo WPCS_LINK ?>img/woobe_banner.png" alt="WOOBE - WooCommerce Bulk Editor Professional" /></a>
                        </td>

                        <td style="width: 20%;">
                            <h3 style="color: red;">&nbsp;</h3>
                            <a href="https://bulk-editor.com/a/buy" target="_blank" title="WOOF - WooCommerce Products Filter"><img width="200" src="<?php echo WPCS_LINK ?>img/woof_banner.png" alt="WOOF - WooCommerce Products Filter" /></a>
                        </td>

                        <td style="width: 20%;">
                            <h3 style="color: red;">&nbsp;</h3>
                            <a href="https://bulk-editor.com/a/buy" target="_blank" title="MDTF - WordPress Meta Data Filter & Taxonomies Filter"><img width="200" src="<?php echo WPCS_LINK ?>img/mdtf_banner.jpg" alt="MDTF - WordPress Meta Data Filter & Taxonomies Filter" /></a>
                        </td>

                    </tr>
                </tbody>
            </table>

        <?php endif; ?>


        <div class="info_popup" style="display: none;"></div>

    </div>
    <br />
    <input type="submit" class="button button-primary button-large" value="<?php _e('Save options', 'currency-switcher') ?>" />
</form>

<?php

function wpcs_print_currency($_this, $currency) {
    global $WPCS;
    ?>
    <li>
        <label class="container">
            <input class="wpcs_is_etalon" title="<?php _e("Set etalon main currency. This should be the currency in which the price of goods exhibited!", 'currency-switcher') ?>" type="radio" <?php checked(1, $currency['is_etalon']) ?> />
            <input type="hidden" name="wpcs_is_etalon[]" value="<?php echo $currency['is_etalon'] ?>" />
            <span class="checkmark"></span>
        </label>

        <input type="text" value="<?php echo $currency['name'] ?>" name="wpcs_name[]" class="wpcs-text" style="width: 80px;" placeholder="<?php _e("Exmpl.: USD,EUR", 'currency-switcher') ?>" />
        <select class="wpcs-drop-down" name="wpcs_symbol[]">
            <?php foreach ($_this->currency_symbols as $symbol) : ?>
                <option value="<?php echo md5($symbol) ?>" <?php selected(md5($currency['symbol']), md5($symbol)) ?>><?php echo $symbol; ?></option>
            <?php endforeach; ?>
        </select>
        <select class="wpcs-drop-down" name="wpcs_position[]">
            <option value="0"><?php _e("Symbol pos.", 'currency-switcher'); ?></option>
            <?php foreach ($_this->currency_positions as $position) : ?>
                <option value="<?php echo $position ?>" <?php selected($currency['position'], $position) ?>><?php echo str_replace('_', ' ', $position); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" value="<?php echo $currency['rate'] ?>" name="wpcs_rate[]" class="wpcs-text" style="width: 80px;" placeholder="<?php _e("rate", 'currency-switcher') ?>" />
        <button class="button2 wpcs_finance_get" title="<?php _e("Press this button if you want get currency rate from the selected aggregator above!", 'currency-switcher') ?>"><span class="dashicons-before dashicons-update"></span></button>
        <select name="wpcs_hide_cents[]" class="wpcs-drop-down">
            <?php
            $wpcs_hide_cents = array(
                0 => __("Show cents", 'currency-switcher'),
                1 => __("Hide cents", 'currency-switcher')
            );

            $hide_cents = 0;
            if ($currency['hide_cents']) {
                $hide_cents = (int) $currency['hide_cents'];
                //$hide_cents = 0;
            }
            ?>
            <?php foreach ($wpcs_hide_cents as $v => $n): ?>
                <option <?php if ($hide_cents == $v): ?>selected=""<?php endif; ?> value="<?php echo $v ?>"><?php echo $n ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" value="<?php echo $currency['description'] ?>" name="wpcs_description[]" style="width: 250px;" class="wpcs-text" placeholder="<?php _e("description", 'currency-switcher') ?>" />
        <?php
        $flag = WPCS_LINK . 'img/no_flag.png';
        if (isset($currency['flag']) AND!empty($currency['flag'])) {
            $flag = $currency['flag'];
        }
        ?>
        <input type="hidden" value="<?php echo $flag ?>" class="wpcs_flag_input" name="wpcs_flag[]" />
        <a href="#" class="wpcs_flag" title="<?php _e("Click to select the flag", 'currency-switcher'); ?>"><img src="<?php echo $flag ?>" alt="<?php _e("Flag", 'currency-switcher'); ?>" /></a>
        &nbsp;<a href="#" title="<?php _e("drag and drope", 'currency-switcher'); ?>"><img style="width: 21px; vertical-align: middle;" src="<?php echo WPCS_LINK ?>img/move.png" alt="<?php _e("move", 'currency-switcher'); ?>" /></a>
    </li>
    <?php
}
?>
 