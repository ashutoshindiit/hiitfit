jQuery(function($){
	jQuery('.robwine-select-init').select2({
		allowClear: true,
		placeholder: "Select an value"
	}).on('change', function() {
	   jQuery('#value')
			.removeClass('select2-offscreen')
			.select2({
				allowClear: true,
				placeholder: "Select a value"
			});
	}).trigger('change'); 
	
	jQuery('.filter-select-init').select2();
	
});