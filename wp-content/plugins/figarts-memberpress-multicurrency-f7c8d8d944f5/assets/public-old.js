jQuery(
	function ($) {

		// Run now
		let $currency = $("body").find("#mpmc_currency_switcher");
		mpmcUpdatePricing($currency);

		$('body').on(
			'change',
			'#mpmc_currency_switcher',
			function (e) {
				e.preventDefault();
				// Don't validate date fields here, wait til the push the submit button
				mpmcUpdatePricing(this);
				let $form = $('.mepr-signup-form');
				display_spc_invoice($form);
			}
		);

		$('body').on(
			'blur',
			'.mepr-form .mepr-form-input',
			function (e) {
				e.preventDefault();
				if ($(this).hasClass('mepr-coupon-code')) {
					if ($(this).val().match(/(\s|\S)/)) {
						// @TODO: Run async AJAX to update the price
						// $("#mpmc_currency_switcher").val($("#mpmc_currency_switcher option:first").val());
					}
				}
			}
		);

		$('body').ajaxComplete(
			function (e, xhr, settings) {
				if (settings.data.indexOf("mepr_update_price_string_with_coupon") >= 0) {
					mpmcUpdatePricing($('#mpmc_currency_switcher'));
					let $form = $('.mepr-signup-form');
					display_spc_invoice($form);
				}
			}
		);

		function mpmcUpdatePricing(obj) {

			if (!$(obj).val())
				return false;

			$('input[name=mpmc_currency]').val($(obj).val());
			$('#rolling').show();

			jQuery.ajax({
				type: "POST",
				url: mpmc_ajax.ajaxurl,
				dataType: "html",
				data: {
					action: 'update_product_price',
					prd_id: $(obj).data("prdid"),
					mpmc_currency: $(obj).val(),
					coupon: $('input[name=mepr_coupon_code]').val(),
					code: $(obj).val(),
					security: mpmc_ajax.security,
					async: false,
				},
				success: function (data) {
					var form = $('input[name=mpmc_currency]').closest('.mepr-signup-form');

					data = data.trim();
					res_match = /^free\s(forever\s)?with\scoupon/i.test(data);

					if (data.toString() != 'false') {
						var price_string = form.find('.mepr_price_cell');
						if (price_string.length) {
							price_string.text(data);
							$('body').animate({
								},
								200,
								function () {
									form.find('div.mepr_price_cell').parent().hide();
									form.find('div.mepr_price_cell').parent().fadeIn(1000);
								}
							);
						}
					}

					$('#rolling').hide();
					// 
				},
				error: function (jqXHR, textStatus, errorThrown) {
					// $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
				}

			});
		}


    // displays transaction invoice for single page forms
    var display_spc_invoice = function ($form) {

			// Flee if SPC Invoice is not enabled
      if (MeprSignup.spc_invoice != '1'){
        return false;
      }

      // Declare variables
      let pm = $form.find('input[name="mepr_payment_method"]:checked').val();
      let pid = $form.find('input[name="mepr_product_id"]').val();
      let address_one = $form.find('input[name="mepr-address-one"]').val();
      let address_two = $form.find('input[name="mepr-address-two"]').val();
      let city = $form.find('input[name="mepr-address-city"]').val();
      let state = $form.find('select[name="mepr-address-state"]').is(':visible') ? $form.find('select[name="mepr-address-state"]').val() : $form.find('input[name="mepr-address-state"]').val();
      let country = $form.find('select[name="mepr-address-country"]').val();
      let postcode = $form.find('input[name="mepr-address-zip"]').val();
      let coupon = $form.find('input[name="mepr_coupon_code"]').val();
      let mpmc_currency = $form.find('input[name="mpmc_currency"]').val();
			
      // UX effect
      $form.find('.mepr-invoice-loader').fadeIn();
      $form.find(".mepr-transaction-invoice-wrapper .mp_invoice").css({ opacity: 0.5 });

      $.ajax({
        url: MeprI18n.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'mepr_display_spc_invoice',
          mepr_product_id: pid,
          mepr_payment_method: pm,
          mepr_address_one: address_one,
          mepr_address_two: address_two,
          mepr_address_city: city,
          mepr_address_state: state,
          mepr_address_country: country,
          mepr_address_zip: postcode,
          mepr_coupon_code: coupon,
          coupon_nonce: MeprSignup.coupon_nonce,
          mpmc_currency: mpmc_currency
        }
      })
      .done(function (response) {
        if (response && typeof response == 'object' && response.status === 'success') {
          $('.mepr-transaction-invoice-wrapper > div').replaceWith(response.invoice);
        }
        $form.find('.mepr-invoice-loader').hide();
        $form.find(".mepr-transaction-invoice-wrapper .mp_invoice").css({ opacity: 1 });
      });
    };

	}
);
