jQuery(document).ready(function($){
// Only show the "remove image" button when needed
	if ( ! jQuery( '#robwines_thumbnail_id' ).val() ) {
		jQuery( '.remove_image_button' ).hide();
	}

	// Uploading files
	var file_frame;

	var file_type;

	jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame && file_type == jQuery(this).attr('datatype') ) {
			file_frame.open();
			return;
		}

		file_type = jQuery(this).attr('datatype');
		// Create the media frame.
		file_frame = wp.media.frames.downloadable_file = wp.media({
			title: 'Choose an image',
			button: {
				text: 'Use image'
			},
			multiple: false
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
			var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

			jQuery( '#robwines_thumbnail_id' ).val( attachment_thumbnail.url );
			jQuery( '#robwines_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
			jQuery( '.remove_image_button' ).show();
			file_frame = '';
		});

		// Finally, open the modal.
		file_frame.open();
	});

	jQuery( document ).on( 'click', '.remove_image_button', function() {
		jQuery( '#robwines_thumbnail' ).find( 'img' ).attr( 'src', jQuery( '#robwines_thumbnail' ).find( 'img' ).attr( 'data-placeholder'));
		jQuery( '#robwines_thumbnail_id' ).val( '' );
		jQuery( '.remove_image_button' ).hide();
		return false;
	});


	jQuery( document ).on( 'click', '.upload_path_button', function( event ) {

		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame && file_type == jQuery(this).attr('datatype')) {
			file_frame.open();
			return;
		}

		file_type = jQuery(this).attr('datatype');
		// Create the media frame.
		file_frame = wp.media.frames.downloadable_file = wp.media({
			title: 'Choose an file',
			button: {
				text: 'Use file'
			},
			multiple: false
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
			jQuery( '#hitfit_path_file' ).val( attachment.url );
			jQuery( '#nutrition_path_thumb' ).find( 'img' ).attr( 'src', attachment.icon );
			jQuery( '.nutri_file_name' ).text(attachment.title);
			jQuery( '.remove_path_button' ).show();
		});

		// Finally, open the modal.
		file_frame.open();
	});

	jQuery( document ).on( 'click', '.remove_path_button', function() {
		jQuery( '#nutrition_path_thumb' ).find( 'img' ).attr( 'src', jQuery( '#robwines_thumbnail' ).find( 'img' ).attr( 'data-placeholder'));
		jQuery( '#hitfit_path_file' ).val( '' );
		jQuery( '.nutri_file_name' ).empty();
		jQuery( '.remove_path_button' ).hide();
		return false;
	});

});