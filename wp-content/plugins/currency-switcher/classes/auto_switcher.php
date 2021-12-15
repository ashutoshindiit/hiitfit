<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

final class WPCS_AUTO_SWITCHER {

    public $data = array();
    public $show = true;

    public function __construct($options) {
        $this->data = array(
            'skin' => (isset($options['auto_switcher_skin'])) ? $options['auto_switcher_skin'] : 'classic_blocks',
            'side' => (isset($options['auto_switcher_side'])) ? $options['auto_switcher_side'] : 'left',
            'top' => (isset($options['auto_switcher_top_margin'])) ? $options['auto_switcher_top_margin'] : '100px',
            'color' => (isset($options['auto_switcher_color'])) ? $options['auto_switcher_color'] : '#b09595',
            'hover_color' => (isset($options['auto_switcher_hover_color'])) ? $options['auto_switcher_hover_color'] : '#623c3c',
            'basic_field' => (isset($options['auto_switcher_basic_field'])) ? $options['auto_switcher_basic_field'] : '__CODE__ __SIGN__',
            'add_field' => (isset($options['auto_switcher_additional_field'])) ? $options['auto_switcher_additional_field'] : '__DESCR__ __FLAG__',
            'show_page' => (isset($options['auto_switcher_show_page'])) ? $options['auto_switcher_show_page'] : '',
            'hide_page' => (isset($options['auto_switcher_hide_page'])) ? $options['auto_switcher_hide_page'] : '',
            'mobile_show' => (isset($options['auto_mobile_show'])) ? $options['auto_mobile_show'] : 0,
            'roll_px' => (isset($options['auto_switcher_roll_px'])) ? $options['auto_switcher_roll_px'] : 90,
        );
        $this->data = apply_filters('wpcs_auto_switcher_data', $this->data);
    }

    public function init() {
        add_action('wp_footer', array($this, 'draw_html'));
        add_action('wp_head', array($this, 'wp_head'));
    }

    public function wp_head() {
        global $WPCS;
        $this->show = $this->check_show_restrike($this->show);
        if ($this->show) {
            wp_enqueue_style('wpcs-auto-switcher', WPCS_LINK . 'css/auto_switcher/' . $this->data['skin'] . '.css', [], WPCS_VERSION);
            if ($this->data['skin'] == 'round_select') {
                wp_enqueue_script('wpcs-round_select', WPCS_LINK . 'js/auto_switcher/round_select.js', [], WPCS_VERSION);
            }
        }
    }

    public function draw_html() {
        if ($this->show) {
            echo $this->render_html(WPCS_PATH . 'views/auto_switcher/' . $this->data['skin'] . '.php', $this->data);
        }
    }

    public function prepare_field_text($currency, $string) {
        if (!$currency OR!is_array($currency)) {
            return"";
        }

        $patt_array = array('__CODE__', '__SIGN__', '__FLAG__', '__DESCR__');
        $values = array(
            $currency['name'],
            $currency['symbol'],
            "<img class='flag_auto_switcher' src='{$currency['flag']}' alt='{$currency['name']}'>",
            $currency['description'],
        );

        $string = str_replace($patt_array, $values, str_replace(' ', '&nbsp;', $string));

        if (empty($string)) {
            $string = $currency['name'];
        }

        if ($this->data['skin'] == 'classic_blocks') {
            //$string .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        return $string;
    }

    public function check_show_restrike($show) {
        $mobile_behavior = $this->data['mobile_show'];
        if ($mobile_behavior == 1) {
            if (!wp_is_mobile()) {
                return false;
            }
        } elseif ($mobile_behavior == 2) {
            if (wp_is_mobile()) {
                return false;
            }
        }
        $show_cond = $this->data['show_page'];
        $hide_cond = $this->data['hide_page'];
        if ($show_cond) {
            $show_array = explode(',', $show_cond);
            $show = (is_page($show_array));
            $specific_show = $this->check_special_page($show, $show_array);
            if ($specific_show) {
                $show = true;
            }
        }
        if ($hide_cond) {
            $hide_array = explode(',', $hide_cond);
            $hide = (is_page($hide_array));
            $specific_hide = $this->check_special_page($hide, $hide_array);
            if ($specific_hide) {
                $show = false;
            }
        }

        return $show;
    }

    public function check_special_page($show, $pages_names) {
        if (!is_array($pages_names)) {
            $pages_names = explode(',', $pages_names);
        }
        if (empty($pages_names)) {
            return $show;
        }
        $special_pages = array('home', 'category', 'front_page');
        $pages = array();
        $pages = array_intersect($special_pages, $pages_names);
        if (!$pages) {
            return $show;
        }
        foreach ($pages as $item) {
            $func = "is_" . $item;
            try {
                if ($func()) {
                    return true;
                }
            } catch (Exception $e) {
                // $e->getMessage()
            }
        }

        return $show;
    }

    public function render_html($pagepath, $data = array()) {
        if (isset($data['pagepath'])) {
            unset($data['pagepath']);
        }
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }

}
