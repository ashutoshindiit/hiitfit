jQuery(function ($) {
  $(document).ajaxComplete(function (e, xhr, settings) {
    if (settings.data.indexOf("mepr_update_price_string") >= 0) {
			mpmcUpdatePricing($("#mpmc_currency_switcher"));
    }

    if (settings.data.indexOf("mepr_update_spc_invoice_table") >= 0) {
      let $form = $('.mepr-signup-form');
      mpmcUpdateSpcInvoiceTable($form);
    }
  });

  $("body").on("change", "#mpmc_currency_switcher", function (e) {
		e.preventDefault();
		let $form = $('.mepr-signup-form');
		$("input[name=mpmc_currency]").val($(this).val());

		mpmcUpdatePricing(this);
		mpmcUpdateSpcInvoiceTable($form);
  });

  function mpmcUpdatePricing(obj) {
    if (!$(obj).val()) return false;

    var form = $("input[name=mpmc_currency]").closest(".mepr-signup-form");
    $("#rolling").show();

    jQuery.ajax({
      type: "POST",
      url: mpmc_ajax.ajaxurl,
      dataType: "html",
      data: {
        action: "update_product_price",
        mpmc_currency: $(obj).val(),
        currency: $(obj).val(),
        code: form.find('input[name="mepr_coupon_code"]').val(),
        prd_id: form.find('input[name="mepr_product_id"]').val(),
        mepr_address_one: form.find('input[name="mepr-address-one"]').val(),
        mepr_address_two: form.find('input[name="mepr-address-two"]').val(),
        mepr_address_city: form.find('input[name="mepr-address-city"]').val(),
        mepr_address_state: form
          .find('select[name="mepr-address-state"]')
          .is(":visible")
          ? form.find('select[name="mepr-address-state"]').val()
          : form.find('input[name="mepr-address-state"]').val(),
        mepr_address_country: form
          .find('select[name="mepr-address-country"]')
          .val(),
        mepr_address_zip: form.find('input[name="mepr-address-zip"]').val(),
        mepr_vat_number: form.find('input[name="mepr_vat_number"]').val(),
        mepr_vat_customer_type: form
          .find('input[name="mepr_vat_customer_type"]:checked')
          .val(),
        coupon_nonce: MeprSignup.coupon_nonce,
      },
      success: function (response) {
        var form = $("input[name=mpmc_currency]").closest(".mepr-signup-form");

        response = JSON.parse(response.trim());
        res_match = /^free\s(forever\s)?with\scoupon/i.test(response);

        if (response.toString() != "false") {
          var price_string = form.find(".mepr_price_cell");
          var price_string = form.find("div.mepr_price_cell");
          if (price_string.length) {
            price_string.text(response.price_string);
          }
        }

        $("#rolling").hide();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        // $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
      },
    });
  }

  // displays transaction invoice for single page forms
  function mpmcUpdateSpcInvoiceTable ($form) {

    // Flee if SPC Invoice is not enabled
    if (MeprSignup.spc_invoice != "1") {
      return false;
    }

    // Declare variables
    // let pm = $form.find('input[name="mepr_payment_method"]:checked').val();
    // let pid = $form.find('input[name="mepr_product_id"]').val();
    // let address_one = $form.find('input[name="mepr-address-one"]').val();
    // let address_two = $form.find('input[name="mepr-address-two"]').val();
    // let city = $form.find('input[name="mepr-address-city"]').val();
    // let state = $form.find('select[name="mepr-address-state"]').is(":visible")
    //   ? $form.find('select[name="mepr-address-state"]').val()
    //   : $form.find('input[name="mepr-address-state"]').val();
    // let country = $form.find('select[name="mepr-address-country"]').val();
    // let postcode = $form.find('input[name="mepr-address-zip"]').val();
    // let coupon = $form.find('input[name="mepr_coupon_code"]').val();
    // let mpmc_currency = $form.find('input[name="mpmc_currency"]').val();

    // UX effect
    $form.find(".mepr-invoice-loader").fadeIn();
    $form
      .find(".mepr-transaction-invoice-wrapper .mp_invoice")
      .css({ opacity: 0.5 });

    $.ajax({
      url: MeprI18n.ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "mpmc_display_spc_invoice",
        mpmc_currency: $form.find('input[name="mpmc_currency"]').val(),
        // currency: $(obj).val(),
        code: $form.find('input[name="mepr_coupon_code"]').val(),
        prd_id: $form.find('input[name="mepr_product_id"]').val(),
        mepr_address_one: $form.find('input[name="mepr-address-one"]').val(),
        mepr_address_two: $form.find('input[name="mepr-address-two"]').val(),
        mepr_address_city: $form.find('input[name="mepr-address-city"]').val(),
        mepr_address_state: $form
          .find('select[name="mepr-address-state"]')
          .is(":visible")
          ? $form.find('select[name="mepr-address-state"]').val()
          : $form.find('input[name="mepr-address-state"]').val(),
        mepr_address_country: $form
          .find('select[name="mepr-address-country"]')
          .val(),
        mepr_address_zip: $form.find('input[name="mepr-address-zip"]').val(),
        mepr_vat_number: $form.find('input[name="mepr_vat_number"]').val(),
        mepr_vat_customer_type: $form.find('input[name="mepr_vat_customer_type"]:checked').val(),
        coupon_nonce: MeprSignup.coupon_nonce,
      },
    }).done(function (response) {
      if (
        response &&
        typeof response == "object" &&
        response.status === "success"
      ) {
        $(".mepr-transaction-invoice-wrapper > div").replaceWith(
          response.invoice
        );
      }
      $form.find(".mepr-invoice-loader").hide();
      $form
        .find(".mepr-transaction-invoice-wrapper .mp_invoice")
        .css({ opacity: 1 });
    });
  };
});
