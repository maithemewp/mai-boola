( function() {
	const toggle = document.querySelector( '#maiboola-toggle' );

	toggle.addEventListener( 'click', event => {
		var maiBoola = event.target.closest( '.maiboola' );

		if ( ! maiBoola ) {
			return;
		}

		maiBoola.classList.remove( 'maiboola-excerpt' );
		maiBoola.classList.add( 'maiboola-excerpt-hidden' );
	});
} )();
