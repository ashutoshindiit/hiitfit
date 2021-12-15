jQuery(document).ready(function ($) {
  $('input.mepr-paystack-testmode').each(function () {
    var integration = $(this).data('integration');

    if ($(this).is(':checked')) {
      $('#mepr-paystack-test-keys-' + integration).show();
    }
    else {
      $('#mepr-paystack-live-keys-' + integration).show();
    }
  });

  $('div#integration').on('change', 'input.mepr-paystack-testmode', function () {
    var integration = $(this).data('integration');
    if ($(this).is(':checked')) {
      $('#mepr-paystack-live-keys-' + integration).hide();
      $('#mepr-paystack-test-keys-' + integration).show();
    }
    else {
      $('#mepr-paystack-live-keys-' + integration).show();
      $('#mepr-paystack-test-keys-' + integration).hide();
    }
  });
});

