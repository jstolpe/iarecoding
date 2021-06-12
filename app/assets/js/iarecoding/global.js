/**
 * Handle iarecoding page javascript functionality.
 *
 * @author Justin Stolpe
 */
var iAreCoding = {
	/**
	 * Initialize javascript for iarecoding functionality.
	 *
	 * @return void
	 */
	initialize : function () {
		// check console!
		console.log( 'iarecoding' );

		$( '.info-text-toggle' ).on( 'click', function() { // on click for the show/hide toggle
			if ( $( '.info-text' ).is( ':visible' ) ) { // text is visible so hide it
				// show the text container
				$( '.info-text' ).hide();

				// update the toggle text to say show
				$( '.info-text-toggle' ).html( 'show' );
			} else { // text is not visible so show it
				// hide the text container
				$( '.info-text' ).show();

				// update the toggle text to say hide
				$( '.info-text-toggle' ).html( 'hide' );
			}
		} );
	}
}

$( function() { // doc is ready
	// initialize iarecoding javascript
	iAreCoding.initialize();
} );