/**
 * The photograph pickers on Appearance > Theme Options.
 *
 * Each image field is a hidden input holding an attachment ID, a preview, and
 * two buttons. The ID is what gets saved — never a URL — so the site keeps
 * working after a domain change or a move to a CDN, and WordPress can still
 * serve the right size for the viewport.
 *
 * One frame is created per field and reused. Creating a new wp.media frame on
 * every click leaks a modal each time, and after a few picks the browser is
 * holding several full media libraries in memory.
 */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.acreage-image-field__pick', function ( event ) {
		event.preventDefault();

		var $field = $( this ).closest( '.acreage-image-field' );
		var frame  = $field.data( 'frame' );

		if ( ! frame ) {
			frame = wp.media( {
				title: acreageOptions.title,
				button: { text: acreageOptions.button },
				library: { type: 'image' },
				multiple: false
			} );

			frame.on( 'select', function () {
				var image = frame.state().get( 'selection' ).first().toJSON();

				// Prefer a sized thumbnail for the preview; fall back to the
				// full file, which some uploads (SVG, tiny images) only have.
				var preview = image.sizes && image.sizes.medium
					? image.sizes.medium.url
					: image.url;

				$field.find( 'input[type="hidden"]' ).val( image.id );
				$field.find( '.acreage-image-field__preview' ).html(
					$( '<img>' ).attr( 'src', preview ).css( { maxWidth: '260px', height: 'auto' } )
				);
				$field.find( '.acreage-image-field__clear' ).show();
			} );

			$field.data( 'frame', frame );
		}

		frame.open();
	} );

	$( document ).on( 'click', '.acreage-image-field__clear', function ( event ) {
		event.preventDefault();

		var $field = $( this ).closest( '.acreage-image-field' );

		// Zero, not empty: the sanitiser reads this through absint() and treats
		// 0 as "use the theme's bundled photograph".
		$field.find( 'input[type="hidden"]' ).val( 0 );
		$field.find( '.acreage-image-field__preview' ).empty();
		$( this ).hide();
	} );
}( jQuery ) );
