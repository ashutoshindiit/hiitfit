jQuery(
	function ($) {
		// simple multiple select
		$('#mpmc_currencies').select2();
		$("select").on(
			"select2:select",
			function (evt) {
				var element = evt.params.data.element;
				var $element = $(element);
				$element.detach();
				$(this).append($element);
				$(this).trigger("change");
			}
		);

		if ($("#mpmc-service-provider select").val() == 'openx') {
			$("#mpmc-openx-api").show();
		}

		if ($("#mpmc-service-provider select").val() == 'exrate') {
			$("#mpmc-exrate-api").show();
		}

		$("#mpmc-service-provider select").change(
			function () {
				var val = $(this).val();
				if (val === "openx") {
					$("#mpmc-openx-api").slideDown();
					$("#mpmc-exrate-api").hide();
				}
				else if(val === "exrate") {
					$("#mpmc-exrate-api").slideDown();
					$("#mpmc-openx-api").hide();
				} else {
					$("#mpmc-openx-api").hide();
					$("#mpmc-exrate-api").hide();
				}
			}
		);
		$("#mpmc-activate-key").click(function () {
			var key = $('#mpmulticurrency-license-key').val();
			var action = $(this).data('action');
			var url = $(this).data('url');

			var url = url + "&action=" + action + "&key=" + key;
			document.location = url;
		});

		$("#mpmc-deactivate-key").click(function () {
			var key = $('#mpmulticurrency-license-key').val();
			var action = $(this).data('action');
			var url = $(this).data('url');

			var url = url + "&action=" + action + "&key=" + key;
			document.location = url;
		});

	}
);
