jQuery('.chosen_select_geo').chosen({width: "95%"});
(function ($, window) {

    'use strict';

    $.fn.wpcsTabs = function (options) {

        if (!this.length)
            return;

        return this.each(function () {

            var $this = $(this);

            ({
                init: function () {
                    this.tabsNav = $this.children('nav');
                    this.items = $this.children('.content-wrap').children('section');
                    this._show();
                    this._initEvents();
                },
                _initEvents: function () {
                    var self = this;
                    this.tabsNav.on('click', 'a', function (e) {
                        e.preventDefault();
                        self._show($(this));
                    });
                },
                _show: function (element) {

                    if (element == undefined) {
                        this.firsTab = this.tabsNav.find('li').first();
                        this.firstSection = this.items.first();

                        if (!this.firsTab.hasClass('tab-current')) {
                            this.firsTab.addClass('tab-current');
                        }

                        if (!this.firstSection.hasClass('content-current')) {
                            this.firstSection.addClass('content-current');
                        }
                    }

                    var $this = $(element),
                            $to = $($this.attr('href'));

                    if ($to.length) {
                        $this.parent('li').siblings().removeClass().end().addClass('tab-current');
                        $to.siblings().removeClass().end().addClass('content-current');
                    }

                }

            }).init();

        });
    }

})(jQuery, window);


/*	Popup
 /* --------------------------------------------- */

/**
 * wpcsPopupPrepare v1.0.0
 */
(function ($) {

    $.wpcs_popup_prepare = function (el, options) {
        this.el = el;
        this.options = $.extend({}, $.wpcs_popup_prepare.DEFAULTS, options);
        this.init();
    };

    $.wpcs_popup_prepare.DEFAULTS = {};
    $.wpcs_popup_prepare.openInstance = [];

    $.wpcs_popup_prepare.prototype = {
        init: function () {

            $.wpcs_popup_prepare.openInstance.unshift(this);

            var base = this;
            base.scope = false;
            base.body = $('body');
            base.wrap = $('#wpwrap');
            base.modal = $('<div class="wpcs-modal wpcs-style"></div>');
            base.overlay = $('<div class="wpcs-modal-backdrop"></div>');
            base.container = $('.wpcs-tabs');
            base.instance = $.wpcs_popup_prepare.openInstance.length;
            base.namespace = '.popup_modal_' + base.instance;

            base.support = {
                touch: Modernizr.touch
            };
            base.eventtype = base.support.touch ? 'touchstart' : 'click';
            base.loadPopup();
        },
        loadPopup: function () {
            this.container.on(this.eventtype, this.el, $.proxy(function (e) {
                if (!this.scope) {
                    this.body.addClass('wpcs-noscroll');
                    this.openPopup(e);
                }
                this.scope = true;
            }, this));
        },
        openPopup: function (e) {
            e.preventDefault();

            var base = this,
                    el = $(e.target),
                    data = el.data();

            if (el.hasClass('js_wpcs_options')) {
                //for 'by-' items
                var key = data['key'],
                        name = data['name'],
                        type = false,
                        info = $("#wpcs-modal-content-" + key),
                        content = info.html();
            } else {
                //for taxonomies
                var type = el.parent().find('.wpcs_select_tax_type').val();
                var key = data['taxonomy'];
                var name = data['taxonomyName'];
                var info = $("#wpcs-modal-content");
                info.find('.wpcs_option_container').hide();
                info.find('.wpcs_option_all').show();
                info.find('.wpcs_option_' + type).show();
                var content = info.html();
            }

            base.create_html(key, name, content, info, type);
            base.add_behavior(key, name, content, info, type);
        },
        create_html: function (key, name, content, info, type) {

            var base = this,
                    title = name ? '<h3 class="wpcs-modal-title"> ' + name + '</h3>' : '',
                    loading = ' preloading ',
                    output = '<div class="wpcs-modal-inner">';
            output += '<div class="wpcs-modal-inner-header">' + title + '<a href="javascript:void(0)" class="wpcs-modal-close"></a></div>';
            output += '<div class="wpcs-modal-inner-content ' + loading + '">' + content + '</div>';
            output += '<div class="wpcs-modal-inner-footer">';
            output += '<a href="javascript:void(0)" class="wpcs-modal-save button button-primary button-large">Apply</a>';
            output += '</div>';
            output += '</div>';

            base.wrap.append(base.modal).append(base.overlay);
            base.modal.html(output);
            base.modal.find('.wpcs-modal-inner-content').removeClass('preloading');

            var multiplier = base.instance - 1,
                    old = parseInt(base.modal.css('zIndex'), 10);
            base.modal.css({margin: (30 * multiplier), zIndex: (old + multiplier + 1)});
            base.overlay.css({zIndex: (old + multiplier)});

            base.on_load_callback(key, name, content, info, type);
        },
        closeModal: function () {
            var base = this;

            $.wpcs_popup_prepare.openInstance.shift();

            base.modal.remove();
            base.overlay.remove();

            base.body.removeClass('wpcs-noscroll');
            base.scope = false;
        },
        add_behavior: function (key, name, content, info, type) {
            var base = this;

            base.modal.on(base.eventtype + base.namespace, '.wpcs-modal-save', function (e) {
                e.preventDefault();
                base.on_close_callback(key, name, content, info, type);
                base.closeModal();
            });

            base.modal.on(base.eventtype + base.namespace, '.wpcs-modal-close', function (e) {
                e.preventDefault();
                base.closeModal();
            });

            base.overlay.on(base.eventtype + base.namespace, function (e) {
                e.preventDefault();
                base.closeModal();
            });

        },
        on_load_callback: function (key, name, content, info, type) {

            if (type) {

                info.find('.wpcs_option_container').hide();
                info.find('.wpcs_option_all').show();
                info.find('.wpcs_option_' + type).show();

                $.each($('.wpcs_popup_option', this.modal), function () {
                    var option = $(this).data('option'),
                            val = $('input[name="wpcs_settings[' + option + '][' + key + ']"]').val();
                    $(this).val(val);
                });

            } else {

                $.each($('.wpcs_popup_option', this.modal), function () {
                    var option = $(this).data('option'),
                            val = $('input[name="wpcs_settings[' + key + '][' + option + ']"]').val();
                    $(this).val(val);
                });

            }

        },
        on_close_callback: function (key, name, content, info, type) {

            if (type) {

                $.each($('.wpcs_popup_option', this.modal), function () {
                    var option = $(this).data('option'), val = $(this).val();
                    $('input[name="wpcs_settings[' + option + '][' + key + ']"]').val(val);
                });

            } else {

                $.each($('.wpcs_popup_option', this.modal), function () {
                    var option = $(this).data('option'), val = $(this).val();
                    $('input[name="wpcs_settings[' + key + '][' + option + ']"]').val(val);
                });

            }

        }
    };

})(jQuery);

var wpcs_sort_order = [];

(function ($) {

    $.wpcs_mod = $.wpcs_mod || {};

    $.wpcs_mod.popup_prepare = function () {
        new $.wpcs_popup_prepare('.js_wpcs_options');
        new $.wpcs_popup_prepare('.js_wpcs_add_options');
    };

    $(function () {

        $('.wpcs-tabs').wpcsTabs();

        $.wpcs_mod.popup_prepare();



        jQuery('body').append('<div id="wpcs_buffer" style="display: none;"></div>');

        try {
            jQuery("#wpcs_list").sortable();
        } catch (e) {
            console.log(e);
        }


        //jQuery("[name='wpcs_settings[wpcs_customer_signs]']").attr('readonly', 'readonly');

        jQuery('body').on('click', '#wpcs_add_currency', function () {

            if (confirm('Hi! In the free version of WPCS you can operate with 2 ANY currencies only! If you want to use more currencies you can make upgrade to the premium version of the plugin. Would you like to visit the plugin page on Codecanyon?')) {
                window.location.href = 'https://codecanyon.pluginus.net/item/wordpress-currency-switcher/17450674';
            }
            return false;
        });

        jQuery('body').on('click', '.wpcs_del_currency', function () {
            jQuery(this).parents('li').hide(220, function () {
                jQuery(this).remove();
            });
            return false;
        });

        jQuery('body').on('click', '.wpcs_is_etalon', function () {
            jQuery('.wpcs_is_etalon').next('input[type=hidden]').val(0);
            jQuery('.wpcs_is_etalon').prop('checked', 0);
            jQuery(this).next('input[type=hidden]').val(1);
            jQuery(this).prop('checked', 1);
            //instant save
            var currency_name = jQuery(this).parents('li').find('input[name="wpcs_name[]"]').val();
            if (currency_name.length) {
                wpcs_show_stat_info_popup('Loading ...');
                var data = {
                    action: "wpcs_save_etalon",
                    currency_name: currency_name
                };
                jQuery.post(ajaxurl, data, function (request) {
                    try {
                        request = jQuery.parseJSON(request);
                        jQuery.each(request, function (index, value) {
                            var elem = jQuery('input[name="wpcs_name[]"]').filter(function () {
                                return this.value.toUpperCase() == index;
                            });

                            if (elem) {
                                jQuery(elem).parent().find('input[name="wpcs_rate[]"]').val(value);
                                jQuery(elem).parent().find('input[name="wpcs_rate[]"]').text(value);
                            }
                        });

                        wpcs_hide_stat_info_popup();
                        wpcs_show_info_popup('Save changes please!', 1999);
                    } catch (e) {
                        wpcs_hide_stat_info_popup();
                        alert('Request error! Try later or another agregator!');
                    }
                });
            }

            return true;
        });


        jQuery('body').on('change', '.wpcs_flag_input', function ()
        {
            jQuery(this).next('a.wpcs_flag').find('img').attr('src', jQuery(this).val());
        });

        jQuery('body').on('click', '.wpcs_flag', function ()
        {
            var input_object = jQuery(this).prev('input[type=hidden]');
            window.send_to_editor = function (html)
            {
                wpcs_insert_html_in_buffer(html);
                var imgurl = jQuery('#wpcs_buffer').find('a').eq(0).attr('href');
                wpcs_insert_html_in_buffer("");
                jQuery(input_object).val(imgurl);
                jQuery(input_object).trigger('change');
                tb_remove();
            };
            tb_show('', 'media-upload.php?post_id=0&type=image&TB_iframe=true');

            return false;
        });

        jQuery('body').on('click', '.wpcs_finance_get', function () {
            var currency_name = jQuery(this).parent().find('input[name="wpcs_name[]"]').val();
            var _this = this;
            jQuery(_this).parent().find('input[name="wpcs_rate[]"]').val('loading ...');
            var data = {
                action: "wpcs_get_rate",
                currency_name: currency_name
            };
            jQuery.post(ajaxurl, data, function (value) {
                jQuery(_this).parent().find('input[name="wpcs_rate[]"]').val(value);
            });

            return false;
        });

        //loader
        jQuery(".wpcs-admin-preloader").fadeOut("slow");
        jQuery("#wpcs_form").fadeIn(200);

        /*
         $(window).load(function () {
         // Animate loader off screen
         $(".se-pre-con").fadeOut("slow");
         });
         */

    });

})(jQuery);


function wpcs_insert_html_in_buffer(html) {
    jQuery('#wpcs_buffer').html(html);
}
function wpcs_get_html_from_buffer() {
    return jQuery('#wpcs_buffer').html();
}

function wpcs_show_info_popup(text, delay) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
    window.setTimeout(function () {
        jQuery(".info_popup").fadeOut(400);
    }, delay);
}

function wpcs_show_stat_info_popup(text) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
}


function wpcs_hide_stat_info_popup() {
    window.setTimeout(function () {
        jQuery(".info_popup").fadeOut(400);
    }, 500);
}


/*auto switcher*/
(function ($) {
    // Add Color Picker to all inputs that have 'color-field' class
    $(document).ready(function () {
        $('.color-field').wpColorPicker();
    });

    $('#wpcs_is_auto_switcher').change(function () {
        var wpcs_is_auto_switcher = parseInt($(this).val(), 10);
        //***
        if (wpcs_is_auto_switcher) {
            $('#wpcs_auto_switcher_skin').parents('tr').show(200);
            $('#wpcs_auto_switcher_side').parents('tr').show(200);
            $('#wpcs_auto_switcher_top_margin').parents('tr').show(200);
            $('#wpcs_auto_switcher_color').parents('tr').show(200);
            $('#wpcs_auto_switcher_hover_color').parents('tr').show(200);
            $('#wpcs_auto_switcher_basic_field').parents('tr').show(200);
            $('#wpcs_auto_switcher_additional_field').parents('tr').show(200);
            $('#wpcs_auto_switcher_show_page').parents('tr').show(200);
            $('#wpcs_auto_switcher_hide_page').parents('tr').show(200);
            $('#wpcs_auto_switcher_mobile_show').parents('tr').show(200);
        } else {
            $('#wpcs_auto_switcher_skin').parents('tr').hide(200);
            $('#wpcs_auto_switcher_side').parents('tr').hide(200);
            $('#wpcs_auto_switcher_top_margin').parents('tr').hide(200);
            $('#wpcs_auto_switcher_color').parents('tr').hide(200);
            $('#wpcs_auto_switcher_hover_color').parents('tr').hide(200);
            $('#wpcs_auto_switcher_basic_field').parents('tr').hide(200);
            $('#wpcs_auto_switcher_additional_field').parents('tr').hide(200);
            $('#wpcs_auto_switcher_show_page').parents('tr').hide(200);
            $('#wpcs_auto_switcher_hide_page').parents('tr').hide(200);
            $('#wpcs_auto_switcher_mobile_show').parents('tr').hide(200);
        }
    });
    $('#wpcs_auto_switcher_skin').change(function () {
        var wpcs_side_switcher_skin = $(this).val();
        if (wpcs_side_switcher_skin == 'roll_blocks') {
            $('.wpcs_roll_blocks_wight').show(200);
        } else {
            $('.wpcs_roll_blocks_wight').hide(200);
        }
    });

})(jQuery);


function wpcs_check_api_key_field() {
    var aggregator = jQuery("select[name='wpcs_settings[wpcs_currencies_aggregator]']").val();
    var is_api = ['free_converter', 'fixer', 'currencylayer', 'openexchangerates'];
    if (jQuery.inArray(aggregator, is_api) != -1) {
        jQuery("input[name='wpcs_settings[wpcs_aggregator_key]']").parents(".wpcs-control-section").show();
    } else {
        jQuery("input[name='wpcs_settings[wpcs_aggregator_key]']").parents(".wpcs-control-section").hide();
    }
}

wpcs_check_api_key_field();
jQuery("select[name='wpcs_settings[wpcs_currencies_aggregator]']").change(function () {
    wpcs_check_api_key_field();
});


function wpcs_add_money_sign() {
    jQuery('a[href=#tabs-2]').trigger('click');
    jQuery('#tabs-2').find('textarea[name="wpcs_settings[wpcs_customer_signs]"]').focus();
    jQuery('textarea[name="wpcs_settings[wpcs_customer_signs]"]').scroll();
}